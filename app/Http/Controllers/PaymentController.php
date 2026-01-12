<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        try {
            $stripeKey = env('STRIPE_SECRET_KEY');
            
            if (!$stripeKey || $stripeKey === '') {
                return response()->json([
                    'error' => 'Stripe não configurado. Adicione STRIPE_SECRET_KEY no arquivo .env'
                ], 500);
            }

            Stripe::setApiKey($stripeKey);

            $validated = $request->validate([
                'reservas' => 'required|array',
                'reservas.*.trip_id' => 'required|integer',
                'reservas.*.passenger_name' => 'required|string',
                'reservas.*.price' => 'required|numeric',
                'usar_pontos' => 'boolean',
                'pontos_usados' => 'integer|min:0'
            ]);

            $lineItems = [];
            foreach ($validated['reservas'] as $reserva) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => "Viagem #{$reserva['trip_id']} - {$reserva['passenger_name']}",
                        ],
                        'unit_amount' => (int)($reserva['price'] * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => url('/payment/success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => url('/carrinho'),
                'metadata' => [
                    'user_id' => auth()->id(),
                    'reservas' => json_encode($validated['reservas']),
                    'usar_pontos' => $request->input('usar_pontos', false) ? '1' : '0',
                    'pontos_usados' => $request->input('pontos_usados', 0)
                ],
            ]);

            return response()->json(['id' => $session->id]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Stripe checkout error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao criar sessão de pagamento. Verifique os logs.'
            ], 500);
        }
    }

    public function success(Request $request)
    {
        try {
            $stripeKey = env('STRIPE_SECRET_KEY');
            
            if (!$stripeKey || $stripeKey === '') {
                return redirect('/carrinho')->with('error', 'Stripe não configurado');
            }

            Stripe::setApiKey($stripeKey);

            if (!$request->session_id) {
                return redirect('/carrinho')->with('error', 'Sessão inválida');
            }

            $session = Session::retrieve($request->session_id);
            
            if ($session->payment_status === 'paid') {
                $reservas = json_decode($session->metadata->reservas, true);
                $userId = $session->metadata->user_id;
                $usarPontos = ($session->metadata->usar_pontos ?? '0') === '1';
                $pontosUsados = (int)($session->metadata->pontos_usados ?? 0);
                
                \Log::info('Payment success - Processing reservations', [
                    'user_id' => $userId,
                    'session_id' => $request->session_id,
                    'reservas_count' => count($reservas),
                    'reservas' => $reservas,
                    'usar_pontos' => $usarPontos,
                    'pontos_usados' => $pontosUsados
                ]);
                
                // Authenticate the user from session metadata
                $user = \App\Models\User::find($userId);
                if ($user) {
                    \Auth::login($user);
                } else {
                    \Log::error('User not found: ' . $userId);
                    return redirect('/carrinho')->with('error', 'Utilizador não encontrado');
                }
                
                // Deduct points if they were used for discount
                if ($usarPontos && $pontosUsados > 0) {
                    $user->points = max(0, $user->points - $pontosUsados);
                    $user->save();
                    \Log::info('Points deducted', [
                        'user_id' => $userId,
                        'pontos_usados' => $pontosUsados,
                        'new_balance' => $user->points
                    ]);
                }
                
                // Process reservations
                $reservationController = new \App\Http\Controllers\ReservationController();
                $reservationRequest = new Request([
                    'reservas' => $reservas,
                    'usar_pontos' => false // Points already deducted above
                ]);
                
                $result = $reservationController->storeMultiple($reservationRequest);
                $resultData = $result->getData();
                
                \Log::info('Reservation creation result', [
                    'success' => $resultData->success ?? false,
                    'message' => $resultData->message ?? 'no message',
                    'reservas_created' => count($resultData->reservas ?? [])
                ]);
                
                // Check if reservations were created successfully
                if ($resultData->success ?? false) {
                    return view('payment.success');
                } else {
                    \Log::error('Failed to create reservations: ' . json_encode($resultData));
                    return redirect('/carrinho')->with('error', 'Erro ao criar reservas. Contacte o suporte.');
                }
            } else {
                return redirect('/carrinho')->with('error', 'Pagamento não confirmado');
            }
        } catch (\Exception $e) {
            \Log::error('Payment success error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect('/carrinho')->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }
    }
}
