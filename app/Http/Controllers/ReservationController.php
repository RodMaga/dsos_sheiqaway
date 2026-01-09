<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'trip_id' => 'required|integer',
            'passenger_name' => 'required|string',
            'price' => 'required|numeric',
        ]);

        $reservation = new Reservation();
        $reservation->user_id = Auth::id();
        $reservation->trip_id = $request->trip_id;
        $reservation->passenger_name = $request->passenger_name;
        $reservation->price = $request->price;
        $reservation->status = 'confirmado';
        $reservation->save();

        return response()->json(['success' => true, 'message' => 'Reserva criada com sucesso!']);
    }
}
