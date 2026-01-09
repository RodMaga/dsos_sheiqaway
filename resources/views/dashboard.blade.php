<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ Auth::check() ? 'true' : 'false' }}">
    @auth
    <meta name="user-name" content="{{ Auth::user()->name }}">
    @endauth
    <title>sheiqaway - Dashboard</title>
    @vite(['resources/css/style.css', 'resources/js/global.js'])
</head>
<body>

    <div style="background: #ffeeba; color: #856404; padding: 0.5rem; margin-bottom: 1rem; border-radius: 4px;">
        Sessão: {{ Auth::check() ? 'logado' : 'não logado' }}
    </div>
    @include('navbar')

    <main style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        <h1 style="color: #000000; margin-bottom: 1rem;">Dashboard</h1>
        <p style="color: #666666; margin-bottom: 2rem;">Bem-vindo, {{ Auth::user()->name }}!</p>

        <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 2rem; margin-bottom: 1rem;">
            <h2 style="color: #000000; margin-bottom: 1rem;">Suas Informações</h2>
            <p><strong>Nome:</strong> {{ Auth::user()->name }}</p>
            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
        </div>

        <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 2rem; margin-bottom: 1rem;">
            <h2 style="color: #000000; margin-bottom: 1rem;">Minhas Reservas Recentes</h2>
            <div id="dashboard-reservas"></div>
            <a href="{{ route('profile.reservas') }}" style="display: inline-block; margin-top: 1rem; color: #2563eb;">Ver todas as reservas</a>
        </div>

        <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 2rem;">
            <h2 style="color: #000000; margin-bottom: 1rem;">Ações Rápidas</h2>
            <a href="{{ route('viagens') }}" style="display: inline-block; padding: 0.75rem 1.5rem; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 4px; margin-right: 1rem;">Pesquisar Viagens</a>
            <a href="{{ route('carrinho') }}" style="display: inline-block; padding: 0.75rem 1.5rem; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 4px;">Ver Carrinho</a>
        </div>
    </main>

    <script>
    function renderDashboardReservas() {
        const history = JSON.parse(localStorage.getItem('purchaseHistory')) || [];
        const container = document.getElementById('dashboard-reservas');
        if (!history.length) {
            container.innerHTML = '<p style="color: #888;">Ainda não tem reservas. Reserve uma viagem para ver aqui!</p>';
            return;
        }
        // Agrupar por ticketCode
        const reservasPorTicket = {};
        history.forEach(reserva => {
            if (!reservasPorTicket[reserva.ticketCode]) {
                reservasPorTicket[reserva.ticketCode] = {
                    ticketCode: reserva.ticketCode,
                    purchaseDate: reserva.purchaseDate,
                    purchaseTime: reserva.purchaseTime,
                    status: reserva.status,
                    viagens: []
                };
            }
            reservasPorTicket[reserva.ticketCode].viagens.push(reserva);
        });
        // Ordenar por data
        const reservas = Object.values(reservasPorTicket).sort((a, b) => {
            return new Date(b.purchaseDate + ' ' + b.purchaseTime) - new Date(a.purchaseDate + ' ' + a.purchaseTime);
        });
        // Mostrar só as 2 mais recentes
        const recentes = reservas.slice(0, 2);
        container.innerHTML = recentes.map(reserva => `
            <div style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 1rem; margin-bottom: 1rem; background: #fafbfc;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>Reserva:</strong> ${reserva.ticketCode}<br>
                        <span style="font-size: 0.9em; color: #888;">${reserva.purchaseDate} às ${reserva.purchaseTime}</span>
                    </div>
                    <span style="padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600; background: #d4edda; color: #155724;">${reserva.status.charAt(0).toUpperCase() + reserva.status.slice(1)}</span>
                </div>
                <div style="margin-top: 0.5rem;">
                    <strong>Viagens:</strong>
                    <ul style="margin: 0; padding-left: 1.2em;">
                        ${reserva.viagens.map(v => `<li>${v.from} → ${v.to} (${v.date}) - €${v.price.base.toFixed(2)}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `).join('');
    }
    document.addEventListener('DOMContentLoaded', renderDashboardReservas);
    </script>

    <footer style="text-align: center; padding: 1.5rem; color: #666666;">
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>
</body>
</html>
