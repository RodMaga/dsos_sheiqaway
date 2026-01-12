<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="true">
    <meta name="user-name" content="{{ Auth::user()->name }}">
    <title>Admin - Sheiqaway</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body class="admin-page">
    @include('navbar')
    
    <main style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>⚙ Backoffice</h1>
            <div style="display: flex; gap: 1rem;">
                <a href="/admin/users" class="btn" style="background: #0ea5e9; color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 500;">⚇ Utilizadores</a>
                <a href="/admin/reservations" class="btn" style="background: #10b981; color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 500;">✈ Reservas</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-icon">⚇</div>
                <div class="stat-value">{{ $stats['total_users'] }}</div>
                <div class="stat-label">Total Utilizadores</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✈</div>
                <div class="stat-value">{{ $stats['total_reservations'] }}</div>
                <div class="stat-label">Total Reservas</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">€</div>
                <div class="stat-value">€{{ number_format($stats['total_revenue'], 2) }}</div>
                <div class="stat-label">Receita Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✓</div>
                <div class="stat-value">{{ $stats['active_reservations'] }}</div>
                <div class="stat-label">Reservas Ativas</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div class="card">
                <h2>★ Top 10 Clientes</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Reservas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topClients as $client)
                        <tr>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->email }}</td>
                            <td><strong>{{ $client->total_reservations }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>◉ Top 10 Destinos</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Rota</th>
                            <th>Total Reservas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topDestinations as $dest)
                        <tr>
                            <td>{{ $dest->origem }} → {{ $dest->destino }}</td>
                            <td><strong>{{ $dest->total }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2>▤ Últimas Reservas</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Passageiro</th>
                        <th>Viagem</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentReservations as $res)
                    <tr>
                        <td>#{{ $res->booking_reference }}</td>
                        <td>{{ $res->user->name }}</td>
                        <td>{{ $res->passenger_name }}</td>
                        <td>{{ $res->trip_id }}</td>
                        <td>€{{ number_format($res->price, 2) }}</td>
                        <td><span class="badge badge-{{ $res->status }}">{{ $res->status }}</span></td>
                        <td>{{ $res->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.reservations.delete', $res->id) }}" style="display: inline;" onsubmit="return confirm('Eliminar esta reserva?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-danger">×</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
</body>
</html>
