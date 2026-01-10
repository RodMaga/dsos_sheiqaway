// dashboard.js - Lógica da página do dashboard
function renderDashboardReservas() {
    const container = document.getElementById('dashboard-reservas');
    
    fetch('/api/reservas')
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.reservas || data.reservas.length === 0) {
                container.innerHTML = '<p class="empty-state">Ainda não tem reservas. Reserve uma viagem para ver aqui!</p>';
                return;
            }
            
            const recentes = data.reservas.slice(0, 5);
            
            container.innerHTML = recentes.map(reserva => `
                <div class="reserva-card">
                    <div class="reserva-header">
                        <div class="reserva-info">
                            <div><strong>Reserva:</strong> ${reserva.booking_reference || 'N/A'}</div>
                            <div><strong>Passageiro:</strong> ${reserva.passenger_name}</div>
                            <div class="reserva-date">${new Date(reserva.created_at).toLocaleString('pt-PT')}</div>
                        </div>
                        <div style="text-align: right;">
                            <div class="reserva-status">${reserva.status}</div>
                            <div class="reserva-price">€${parseFloat(reserva.price).toFixed(2)}</div>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Erro ao carregar reservas:', error);
            container.innerHTML = '<p style="color: #dc2626; text-align: center;">Erro ao carregar reservas.</p>';
        });
}

document.addEventListener('DOMContentLoaded', renderDashboardReservas);
