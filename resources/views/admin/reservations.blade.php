<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reservas - Admin</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body class="admin-page">
    @include('navbar')
    
    <main style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>✈️ Gestão de Reservas</h1>
            <a href="/admin" class="btn-secondary">← Voltar ao Dashboard</a>
        </div>

        @if(session('success'))
        <div style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
        @endif

        <div class="card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ref</th>
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
                    @foreach($reservations as $res)
                    <tr>
                        <td>#{{ $res->booking_reference }}</td>
                        <td>{{ $res->user->name }}<br><small style="color: #6c757d;">{{ $res->user->email }}</small></td>
                        <td>{{ $res->passenger_name }}</td>
                        <td>{{ $res->trip_id }}</td>
                        <td>€{{ number_format($res->price, 2) }}</td>
                        <td><span class="badge badge-{{ $res->status }}">{{ $res->status }}</span></td>
                        <td>{{ $res->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.reservations.edit', $res->id) }}" class="btn-action btn-warning" title="Editar">✏️</a>
                            <form method="POST" action="{{ route('admin.reservations.delete', $res->id) }}" style="display: inline;" onsubmit="return confirm('Eliminar esta reserva?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-danger" title="Eliminar">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 2rem;">
                {{ $reservations->links() }}
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
</body>
</html>
