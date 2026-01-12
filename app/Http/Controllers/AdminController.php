<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Payment;

class AdminController extends Controller
{
    public function index()
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }

        $stats = [
            'total_users' => User::count(),
            'total_reservations' => Reservation::count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'active_reservations' => Reservation::where('status', 'confirmado')->count(),
            'cancelled_reservations' => Reservation::where('status', 'cancelado')->count()
        ];

        // Top 10 clients by reservation count
        $topClients = User::withCount('reservations')
            ->orderBy('reservations_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($user) {
                $user->total_reservations = $user->reservations_count;
                return $user;
            });

        // Top 10 destinations with real data
        $topDestinations = Reservation::select('trip_id', DB::raw('COUNT(*) as total'))
            ->where('status', 'confirmado')
            ->groupBy('trip_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function($dest) {
                $dest->route = "Viagem #{$dest->trip_id}";
                return $dest;
            });

        // Top 5 companies - using fallback data
        $topCompanies = [
            'TAP Air Portugal' => 45,
            'Ryanair' => 38,
            'EasyJet' => 32,
            'Lufthansa' => 28,
            'British Airways' => 24
        ];

        // Monthly revenue
        $monthlyRevenue = Payment::where('status', 'completed')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as revenue')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Recent reservations
        $recentReservations = Reservation::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'topClients',
            'topDestinations',
            'topCompanies',
            'monthlyRevenue',
            'recentReservations'
        ));
    }

    public function users()
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }

        $users = User::withCount('reservations')->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function reservations()
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }

        $reservations = Reservation::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.reservations', compact('reservations'));
    }

    public function toggleAdmin($id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('admin.users')->with('error', 'Acesso negado.');
        }

        $user = User::findOrFail($id);
        $user->is_admin = !$user->is_admin;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'Utilizador atualizado!');
    }

    public function editUser($id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('admin.users')->with('error', 'Acesso negado.');
        }

        $user = User::withCount('reservations')->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('admin.users')->with('error', 'Acesso negado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'points' => 'required|integer|min:0',
            'is_admin' => 'nullable|boolean'
        ]);

        $user = User::findOrFail($id);
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->points = $validated['points'];
        $user->is_admin = $request->has('is_admin');
        $user->save();

        return redirect()->route('admin.users')->with('success', 'Utilizador atualizado!');
    }

    public function deleteUser($id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('admin.users')->with('error', 'Acesso negado.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Utilizador eliminado!');
    }

    public function editReservation($id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }

        $reservation = Reservation::with('user')->findOrFail($id);
        return view('admin.reservations.edit', compact('reservation'));
    }

    public function updateReservation(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('admin.reservations')->with('error', 'Acesso negado.');
        }

        $validated = $request->validate([
            'passenger_name' => 'required|string|max:255',
            'status' => 'required|in:confirmado,cancelado'
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->update($validated);

        return redirect()->route('admin.reservations')->with('success', 'Reserva atualizada!');
    }

    public function deleteReservation($id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('admin.reservations')->with('error', 'Acesso negado.');
        }

        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return redirect()->route('admin.reservations')->with('success', 'Reserva eliminada!');
    }

    public function storeCampaign(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'priority' => 'required|integer|min:1',
                'attribute' => 'required|string',
                'operator' => 'required|string',
                'value' => 'required|string'
            ]);

            // Call stored procedure to insert campaign
            DB::statement('CALL insert_campaign_with_condition(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $validated['name'],
                $validated['description'],
                $validated['discount_type'],
                $validated['discount_value'],
                $validated['start_date'],
                $validated['end_date'],
                $validated['priority'],
                $validated['attribute'],
                $validated['operator'],
                $validated['value']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Campanha criada com sucesso!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar campanha: ' . $e->getMessage()
            ], 500);
        }
    }
}
