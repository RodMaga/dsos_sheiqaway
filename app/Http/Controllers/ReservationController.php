<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ReservationController extends Controller
{
    private function getViagensFromAPI()
    {
        return Cache::remember('viagens_api', 300, function () {
            $response = \Http::withoutVerifying()->timeout(10)->get("https://vs-gate.dei.isep.ipp.pt:10923/api/viagens");
            return $response->json();
        });
    }

    private function getViagemById($tripId)
    {
        $viagens = $this->getViagensFromAPI();
        return collect($viagens)->firstWhere('id', (int)$tripId);
    }
    public function index()
    {
        try {
            $userId = Auth::id();
            $reservas = Reservation::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'reservas' => $reservas
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar reservas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar reservas.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            if (!$user) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Sessão inválida. Por favor, faça login novamente.'
                ], 401);
            }

            $request->validate([
                'trip_id' => 'required|integer',
                'passenger_name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'sometimes|integer|min:1',
            ]);

            $quantity = $request->quantity ?? 1;

            // Verificar lugares disponíveis
            $viagem = $this->getViagemById($request->trip_id);
            
            $capacidadeMaxima = $viagem['capacidade'] ?? 50;
            
            $lugaresOcupados = Reservation::where('trip_id', $request->trip_id)
                ->where('status', 'confirmado')
                ->count();
            
            $lugaresDisponiveis = $capacidadeMaxima - $lugaresOcupados;
            
            if ($quantity > $lugaresDisponiveis) {
                return response()->json([
                    'success' => false,
                    'message' => "Apenas {$lugaresDisponiveis} lugar(es) disponível(eis). Não é possível reservar {$quantity} lugar(es)."
                ], 400);
            }

            $reservation = new Reservation();
            $reservation->user_id = $userId;
            $reservation->trip_id = $request->trip_id;
            $reservation->passenger_name = $request->passenger_name;
            $reservation->price = $request->price;
            $reservation->status = 'confirmado';
            $reservation->save();

            return response()->json(['success' => true, 'message' => 'Reserva criada com sucesso!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos: ' . implode(', ', $errorMessages),
                'errors' => $errors
            ], 422);
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1] ?? null;
            $errorMessage = $e->getMessage();
            
            Log::error('QueryException ao criar reserva', [
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'user_id' => Auth::id(),
                'trip_id' => $request->trip_id ?? null,
                'request_data' => $request->all()
            ]);
            
            if ($errorCode == 1452) {
                if (str_contains($errorMessage, 'user_id')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro: Utilizador não encontrado. Por favor, faça login novamente.'
                    ], 401);
                } elseif (str_contains($errorMessage, 'trip_id')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro: Viagem não encontrada. Por favor, recarregue a página e tente novamente.'
                    ], 400);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erro: Referência inválida na base de dados. Por favor, tente novamente.'
                ], 400);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar reserva. Por favor, tente novamente.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Erro ao criar reserva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar reserva. Por favor, tente novamente.'
            ], 500);
        }
    }

    public function storeMultiple(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            if (!$user) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Sessão inválida. Por favor, faça login novamente.'
                ], 401);
            }

            $request->validate([
                'reservas' => 'required|array',
                'reservas.*.trip_id' => 'required|integer',
                'reservas.*.passenger_name' => 'required|string|max:255',
                'reservas.*.price' => 'required|numeric|min:0',
                'reservas.*.quantity' => 'required|integer|min:1',
                'usar_pontos' => 'boolean',
            ]);

            $reservasCriadas = [];
            $totalAmount = 0;
            $usarPontos = $request->input('usar_pontos', false);

            DB::beginTransaction();

            // Calculate total amount first
            foreach ($request->reservas as $reservaData) {
                // Buscar capacidade da API externa
                $viagem = $this->getViagemById($reservaData['trip_id']);
                
                $capacidadeMaxima = $viagem['capacidade'] ?? 50;
                
                // Verificar lugares disponíveis para esta viagem
                $lugaresOcupados = Reservation::where('trip_id', $reservaData['trip_id'])
                    ->where('status', 'confirmado')
                    ->count();
                
                $lugaresDisponiveis = $capacidadeMaxima - $lugaresOcupados;
                
                if ($reservaData['quantity'] > $lugaresDisponiveis) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Apenas {$lugaresDisponiveis} lugar(es) disponível(eis) para a viagem {$reservaData['trip_id']}."
                    ], 400);
                }
                
                $totalAmount += $reservaData['price'] * $reservaData['quantity'];
            }

            // Calculate points to use/receive
            $pontosGanhos = 0;
            $pontosUsados = 0;
            $totalDiscount = 0;

            if ($usarPontos) {
                // User wants to use points for discount
                // Maximum points that can be used = total price * 10
                $maxPointsToUse = $totalAmount * 10;
                
                // Cap by user's available points
                $pontosUsados = min($user->points, $maxPointsToUse);
                
                // Calculate total discount: points / 10 = discount
                $totalDiscount = $pontosUsados / 10;
                
                // Deduct points from user
                $user->points -= $pontosUsados;
                $user->save();
            } else {
                // User wants to earn points: 1 point per each 10€
                $pontosGanhos = floor($totalAmount / 10);
                $user->points += $pontosGanhos;
                $user->save();
            }

            // Process each reservation
            foreach ($request->reservas as $reservaData) {
                $originalPrice = $reservaData['price'];
                $quantity = $reservaData['quantity'];
                
                for ($i = 0; $i < $quantity; $i++) {
                    // Calculate discount per reservation if using points
                    $reservationPrice = $originalPrice;
                    $pointsSpentThisReservation = 0;
                    $pointsReceivedThisReservation = 0;
                    
                    if ($usarPontos && $totalAmount > 0) {
                        // Proportional discount for this reservation
                        $proportionalDiscount = ($originalPrice / $totalAmount) * $totalDiscount;
                        $reservationPrice = max(0, $originalPrice - $proportionalDiscount);
                        $pointsSpentThisReservation = round(($originalPrice / $totalAmount) * $pontosUsados);
                    } else {
                        // Points earned per reservation
                        $pointsReceivedThisReservation = floor($originalPrice / 10);
                    }
                    
                    // Create reservation with adjusted price if using points
                    $reservation = new Reservation();
                    $reservation->user_id = $userId;
                    $reservation->trip_id = $reservaData['trip_id'];
                    $reservation->passenger_name = $reservaData['passenger_name'];
                    $reservation->price = $reservationPrice;
                    $reservation->status = 'confirmado';
                    $reservation->save();
                    
                    // Call stored procedure to track points
                    DB::statement('CALL insert_reservas_pontos(?, ?, ?)', [
                        $reservation->id,
                        $pointsSpentThisReservation,
                        $pointsReceivedThisReservation
                    ]);
                    
                    // Create payment for each reservation
                    $payment = new Payment();
                    $payment->reservation_id = $reservation->id;
                    $payment->user_id = $userId;
                    $payment->amount = $reservationPrice;
                    $payment->currency = 'EUR';
                    $payment->payment_method = 'card';
                    $payment->status = 'completed';
                    $payment->transaction_id = 'TXN-' . strtoupper(uniqid());
                    $payment->paid_at = now();
                    $payment->save();
                    
                    $reservasCriadas[] = $reservation;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($reservasCriadas) . ' reserva(s) criada(s) com sucesso!',
                'reservas' => $reservasCriadas,
                'total_paid' => $totalAmount,
                'pontos_ganhos' => $usarPontos ? 0 : $pontosGanhos,
                'pontos_usados' => $usarPontos ? $pontosUsados : 0
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar reservas múltiplas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar reservas. Por favor, tente novamente.'
            ], 500);
        }
    }

    public function applyCampaign(Request $request)
    {
        try {
            $request->validate([
                'price' => 'required|numeric|min:0',
                'duration' => 'required|integer|min:0',
                'airline' => 'required|string',
                'date' => 'required|date',
            ]);

            $price = $request->price;
            $duration = $request->duration;
            $airline = $request->airline;
            $date = $request->date;

            
            // Convert ISO datetime to DATE format (YYYY-MM-DD)
            if (strpos($date, 'T') !== false) {
                $date = substr($date, 0, 10); // Extract date part only
            }

            // Log the parameters being sent to the stored procedure
            Log::info('Calling apply_campaign_to_trip with parameters:', [
                'price' => $price,
                'duration' => $duration,
                'airline' => $airline,
                'date' => $date
            ]);

            // Call the stored procedure
            $result = DB::select('CALL apply_campaign_to_trip(?, ?, ?, ?, @final_price)', [
                $price,
                $duration,
                $airline,
                $date
            ]);

            // Get the output parameter
            $finalPrice = DB::select('SELECT @final_price as final_price')[0]->final_price;

            // Log the result
            Log::info('apply_campaign_to_trip result:', [
                'original_price' => $price,
                'final_price' => $finalPrice,
                'discount_applied' => $price != $finalPrice
            ]);

            return response()->json([
                'success' => true,
                'original_price' => $price,
                'final_price' => $finalPrice,
                'discount_applied' => $price != $finalPrice
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao aplicar campanha: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aplicar campanha.',
                'original_price' => $request->price,
                'final_price' => $request->price,
                'discount_applied' => false
            ], 500);
        }
    }
    public function lugaresDisponiveisBulk()
    {
        try {
            $viagens = $this->getViagensFromAPI();
            $reservas = Reservation::where('status', 'confirmado')
                ->select('trip_id', DB::raw('count(*) as total'))
                ->groupBy('trip_id')
                ->pluck('total', 'trip_id');
            
            $resultado = [];
            foreach ($viagens as $viagem) {
                $tripId = $viagem['id'];
                $capacidade = $viagem['capacidade'] ?? 50;
                $ocupados = $reservas[$tripId] ?? 0;
                $disponiveis = max(0, $capacidade - $ocupados);
                
                $resultado[$tripId] = [
                    'lugares_disponiveis' => $disponiveis,
                    'lugares_ocupados' => $ocupados,
                    'capacidade_maxima' => $capacidade
                ];
            }
            
            return response()->json(['success' => true, 'data' => $resultado]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar lugares disponíveis em bulk: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao verificar disponibilidade.'], 500);
        }
    }

    public function lugaresDisponiveis($tripId)
    {
        try {
            $viagem = $this->getViagemById($tripId);
            
            if (!$viagem) {
                return response()->json(['success' => false, 'message' => 'Viagem não encontrada'], 404);
            }
            
            $capacidadeMaxima = $viagem['capacidade'] ?? 50;
            
            $lugaresOcupados = Reservation::where('trip_id', $tripId)
                ->where('status', 'confirmado')
                ->count();
            
            $lugaresDisponiveis = $capacidadeMaxima - $lugaresOcupados;
            
            return response()->json([
                'success' => true,
                'trip_id' => $tripId,
                'lugares_ocupados' => $lugaresOcupados,
                'lugares_disponiveis' => max(0, $lugaresDisponiveis),
                'capacidade_maxima' => $capacidadeMaxima
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar lugares disponíveis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar disponibilidade.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $reservation = Reservation::where('id', $id)
                ->where('user_id', Auth::id())
                ->with('payments')
                ->first();

            if (!$reservation) {
                return response()->json(['success' => false, 'message' => 'Reserva não encontrada'], 404);
            }

            return response()->json(['success' => true, 'reserva' => $reservation]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar reserva: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar reserva'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $reservation = Reservation::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$reservation) {
                return response()->json(['success' => false, 'message' => 'Reserva não encontrada'], 404);
            }

            if ($reservation->status === 'cancelado') {
                return response()->json(['success' => false, 'message' => 'Não é possível alterar uma reserva cancelada'], 400);
            }

            $request->validate([
                'passenger_name' => 'sometimes|string|max:255',
                'trip_id' => 'sometimes|integer',
            ]);

            if ($request->has('trip_id') && $request->trip_id != $reservation->trip_id) {
                $viagem = $this->getViagemById($request->trip_id);
                
                $capacidadeMaxima = $viagem['capacidade'] ?? 50;
                $lugaresOcupados = Reservation::where('trip_id', $request->trip_id)
                    ->where('status', 'confirmado')
                    ->count();
                
                if ($lugaresOcupados >= $capacidadeMaxima) {
                    return response()->json(['success' => false, 'message' => 'Viagem lotada'], 400);
                }
            }

            $reservation->fill($request->only(['passenger_name', 'trip_id']));
            $reservation->save();

            return response()->json(['success' => true, 'message' => 'Reserva atualizada com sucesso', 'reserva' => $reservation]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar reserva: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar reserva'], 500);
        }
    }

    public function cancel($id)
    {
        try {
            $reservation = Reservation::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$reservation) {
                return response()->json(['success' => false, 'message' => 'Reserva não encontrada'], 404);
            }

            if ($reservation->status === 'cancelado') {
                return response()->json(['success' => false, 'message' => 'Reserva já está cancelada'], 400);
            }

            $reservation->status = 'cancelado';
            $reservation->save();

            return response()->json(['success' => true, 'message' => 'Reserva cancelada com sucesso']);
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar reserva: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao cancelar reserva'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $reservation = Reservation::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$reservation) {
                return response()->json(['success' => false, 'message' => 'Reserva não encontrada'], 404);
            }

            DB::beginTransaction();
            Payment::where('reservation_id', $id)->delete();
            $reservation->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Reserva eliminada com sucesso']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao eliminar reserva: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao eliminar reserva'], 500);
        }
    }
}
