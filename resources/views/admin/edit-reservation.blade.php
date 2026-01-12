<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Reserva - Admin</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body class="admin-page">
    @include('navbar')
    
    <main style="max-width: 800px; margin: 0 auto; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>✏️ Editar Reserva #{{ $reservation->booking_reference }}</h1>
            <a href="/admin/reservations" class="btn-secondary">← Voltar</a>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('admin.reservations.update', $reservation->id) }}">
                @csrf
                @method('PUT')

                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <p style="margin: 0.5rem 0;"><strong>Cliente:</strong> {{ $reservation->user->name }} ({{ $reservation->user->email }})</p>
                    <p style="margin: 0.5rem 0;"><strong>Viagem ID:</strong> {{ $reservation->trip_id }}</p>
                    <p style="margin: 0.5rem 0;"><strong>Preço:</strong> €{{ number_format($reservation->price, 2) }}</p>
                    <p style="margin: 0.5rem 0;"><strong>Data Criação:</strong> {{ $reservation->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">Nome do Passageiro</label>
                    <input type="text" name="passenger_name" value="{{ $reservation->passenger_name }}" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px; font-size: 1rem;">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">Status</label>
                    <select name="status" required 
                            style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px; font-size: 1rem;">
                        <option value="confirmado" {{ $reservation->status === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                        <option value="cancelado" {{ $reservation->status === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn" style="flex: 1;">💾 Guardar Alterações</button>
                    <a href="/admin/reservations" class="btn-secondary" style="flex: 1; text-align: center; padding: 0.75rem;">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
</body>
</html>
