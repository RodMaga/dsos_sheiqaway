<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
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
            // O middleware 'auth' já garante que o usuário está autenticado
            $userId = Auth::id();
            $user = Auth::user();

            // Verificar se o utilizador realmente existe na base de dados
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
            ]);

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
            // Erro específico de base de dados
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
                // Foreign key constraint violation - usuário ou viagem não existe
                // Verificar qual foreign key falhou
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
            ]);

            $reservasCriadas = [];
            $totalAmount = 0;

            DB::beginTransaction();

            foreach ($request->reservas as $reservaData) {
                $totalAmount += $reservaData['price'] * $reservaData['quantity'];
                
                for ($i = 0; $i < $reservaData['quantity']; $i++) {
                    $reservation = new Reservation();
                    $reservation->user_id = $userId;
                    $reservation->trip_id = $reservaData['trip_id'];
                    $reservation->passenger_name = $reservaData['passenger_name'];
                    $reservation->price = $reservaData['price'];
                    $reservation->status = 'confirmado';
                    $reservation->save();
                    
                    // Criar pagamento para cada reserva
                    $payment = new Payment();
                    $payment->reservation_id = $reservation->id;
                    $payment->user_id = $userId;
                    $payment->amount = $reservaData['price'];
                    $payment->currency = 'EUR';
                    $payment->payment_method = 'card'; // Pode ser dinâmico depois
                    $payment->status = 'completed';
                    $payment->transaction_id = 'TXN-' . strtoupper(uniqid());
                    $payment->paid_at = now();
                    $payment->save();
                    
                    $reservasCriadas[] = $reservation;
                }
            }

            // Calcular e adicionar pontos: 1 ponto por cada 10€
            $pontosGanhos = floor($totalAmount / 10);
            $user->points += $pontosGanhos;
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($reservasCriadas) . ' reserva(s) criada(s) com sucesso!',
                'reservas' => $reservasCriadas,
                'total_paid' => $totalAmount,
                'pontos_ganhos' => $pontosGanhos
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
}
