<!DOCTYPE html>
<html lang="pt" class="reservas-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Minhas Reservas - Sheiqaway</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body class="reservas-page">
    @include('navbar')
    
    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Minhas Reservas</h1>
            <a href="/dashboard" class="btn-secondary">← Voltar ao Dashboard</a>
        </div>

        @if(session('success'))
        <div style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
        @endif

        @if($reservations->count() > 0)
            @foreach($reservations as $res)
            <div class="reserva-card {{ $res->status === 'cancelado' ? 'cancelado' : '' }}">
                <div class="reserva-header">
                    <div class="reserva-info">
                        <div><strong>Reserva:</strong> #{{ $res->booking_reference }}</div>
                        <div><strong>Passageiro:</strong> {{ $res->passenger_name }}</div>
                        <div><strong>Viagem ID:</strong> {{ $res->trip_id }}</div>
                        <div class="reserva-date">{{ $res->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div style="text-align: right;">
                        <div class="reserva-status status-{{ $res->status }}">{{ ucfirst($res->status) }}</div>
                        <div class="reserva-price">€{{ number_format($res->price, 2) }}</div>
                    </div>
                </div>
                
                @if($res->status === 'confirmado')
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #dee2e6;">
                    <a href="{{ route('profile.reservations.edit', $res->id) }}" class="btn-secondary" style="flex: 1; text-align: center;">✎ Editar</a>
                    <form method="POST" action="{{ route('reservations.cancel', $res->id) }}" style="flex: 1;" onsubmit="return confirm('Tem certeza que deseja cancelar esta reserva?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-remover" style="width: 100%;">× Cancelar</button>
                    </form>
                </div>
                @endif
            </div>
            @endforeach

            <div style="margin-top: 2rem;">
                {{ $reservations->links() }}
            </div>
        @else
            <div class="empty-state">
                <p>Ainda não tem reservas.</p>
                <a href="/viagens" class="btn">Pesquisar Viagens</a>
            </div>
        @endif
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
</body>
</html>
