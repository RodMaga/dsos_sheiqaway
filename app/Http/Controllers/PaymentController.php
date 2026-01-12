<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $request->validate([
            'reservas' => 'required|array',
            'reservas.*.trip_id' => 'required|integer',
            'reservas.*.passenger_name' => 'required|string',
            'reservas.*.price' => 'required|numeric',
        ]);

        $lineItems = [];
        foreach ($request->reservas as $reserva) {
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

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => url('/payment/success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => url('/carrinho'),
                'metadata' => [
                    'user_id' => auth()->id(),
                    'reservas' => json_encode($request->reservas),
                ],
            ]);

            return response()->json(['id' => $session->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function success(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        try {
            $session = Session::retrieve($request->session_id);
            
            if ($session->payment_status === 'paid') {
                $reservas = json_decode($session->metadata->reservas, true);
                
                // Processar reservas
                $reservationController = new \App\Http\Controllers\ReservationController();
                $result = $reservationController->storeMultiple(new Request(['reservas' => $reservas]));
                
                return view('payment.success');
            }
        } catch (\Exception $e) {
            return redirect('/carrinho')->with('error', 'Erro ao processar pagamento');
        }
    }
}
