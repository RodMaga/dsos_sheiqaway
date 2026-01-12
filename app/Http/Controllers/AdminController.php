<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_reservations' => Reservation::count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'active_reservations' => Reservation::where('status', 'confirmado')->count(),
        ];

        $topClients = User::select('users.*', DB::raw('COUNT(reservas.id) as total_reservations'))
            ->join('reservas', 'users.id', '=', 'reservas.user_id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.email_verified_at', 'users.password', 'users.remember_token', 'users.created_at', 'users.updated_at', 'users.is_admin', 'users.phone', 'users.points')
            ->orderBy('total_reservations', 'desc')
            ->limit(10)
            ->get();

        // Buscar viagens da API para obter nomes dos destinos
        $viagens = \Cache::remember('viagens_api', 300, function () {
            $response = \Http::withoutVerifying()->timeout(10)->get("https://vs-gate.dei.isep.ipp.pt:10923/api/viagens");
            return collect($response->json())->keyBy('id');
        });

        $topDestinations = Reservation::select('trip_id', DB::raw('COUNT(*) as total'))
            ->where('status', 'confirmado')
            ->groupBy('trip_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) use ($viagens) {
                $viagem = $viagens->get($item->trip_id);
                $item->destino = $viagem ? $viagem['destino'] : 'N/A';
                $item->origem = $viagem ? $viagem['origem'] : 'N/A';
                return $item;
            });

        $recentReservations = Reservation::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'topClients', 'topDestinations', 'recentReservations'));
    }

    public function users()
    {
        $users = User::withCount(['reservations as reservations_count'])->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function reservations()
    {
        $reservations = Reservation::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.reservations', compact('reservations'));
    }

    public function toggleAdmin($id)
    {
        $user = User::findOrFail($id);
        $user->is_admin = !$user->is_admin;
        $user->save();
        return back()->with('success', 'Permissões atualizadas!');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Não pode eliminar a sua própria conta!');
        }
        $user->delete();
        return back()->with('success', 'Utilizador eliminado!');
    }

    public function deleteReservation($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();
        return back()->with('success', 'Reserva eliminada!');
    }

    public function editReservation($id)
    {
        $reservation = Reservation::with('user')->findOrFail($id);
        return view('admin.edit-reservation', compact('reservation'));
    }

    public function updateReservation(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        
        $request->validate([
            'passenger_name' => 'required|string|max:255',
            'status' => 'required|in:confirmado,cancelado',
        ]);

        $reservation->passenger_name = $request->passenger_name;
        $reservation->status = $request->status;
        $reservation->save();

        return redirect()->route('admin.reservations')->with('success', 'Reserva atualizada!');
    }
}
