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
    <title>Minhas Reservas - Sheiqaway</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body class="reservas-page">
    @include('navbar')
    
    <main>
        <h1>Minhas Reservas</h1>
        <div id="reservas-container" class="loading">
            <div class="loading-spinner"></div>
            <p>A carregar reservas...</p>
        </div>
        <div id="pagination" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;"></div>
    </main>

    <!-- Modal Editar -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Editar Reserva</h3>
            <form id="editForm">
                <input type="hidden" id="edit-id">
                <div class="form-group">
                    <label>Nome do Passageiro</label>
                    <input type="text" id="edit-passenger-name" required>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn-modal-confirm">Guardar</button>
                    <button type="button" class="btn-modal-cancel" onclick="closeEditModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>

    <script>
        let reservas = [];
        let currentPage = 1;
        const perPage = 10;

        async function carregarReservas() {
            try {
                const response = await fetch('/api/reservas');
                const data = await response.json();
                
                if (data.success) {
                    reservas = data.reservas;
                    renderReservas();
                    renderPagination();
                }
            } catch (error) {
                console.error('Erro ao carregar reservas:', error);
                document.getElementById('reservas-container').innerHTML = '<p style="color: #ef4444;">Erro ao carregar reservas.</p>';
            }
        }

        function renderReservas() {
            const container = document.getElementById('reservas-container');
            
            if (reservas.length === 0) {
                container.innerHTML = '<div class="empty-state"><p>Não tem reservas.</p><a href="/viagens" class="btn">Explorar Viagens</a></div>';
                return;
            }

            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const paginatedReservas = reservas.slice(start, end);

            container.innerHTML = paginatedReservas.map(r => `
                <div class="reserva-card ${r.status}">
                    <div class="reserva-header">
                        <div class="reserva-info">
                            <strong>Reserva #${r.booking_reference}</strong>
                            <div class="reserva-date">${new Date(r.created_at).toLocaleDateString('pt-PT')}</div>
                        </div>
                        <span class="reserva-status status-${r.status}">${r.status.toUpperCase()}</span>
                    </div>
                    <div class="info-item">
                        <strong>Passageiro:</strong> ${r.passenger_name}
                    </div>
                    <div class="info-item">
                        <strong>Viagem ID:</strong> ${r.trip_id}
                    </div>
                    <div class="reserva-price">€${parseFloat(r.price).toFixed(2)}</div>
                    <div class="actions">
                        ${r.status !== 'cancelado' ? `
                            <button class="btn btn-secondary" onclick="editarReserva(${r.id})">Editar</button>
                            <button class="btn-remover" onclick="cancelarReserva(${r.id})">Cancelar</button>
                        ` : ''}
                        <button class="btn-remover" onclick="eliminarReserva(${r.id})">Eliminar</button>
                    </div>
                </div>
            `).join('');
        }

        function renderPagination() {
            const totalPages = Math.ceil(reservas.length / perPage);
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let html = '';
            
            if (currentPage > 1) {
                html += `<button class="btn btn-secondary" onclick="changePage(${currentPage - 1})">← Anterior</button>`;
            }
            
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    html += `<button class="btn ${i === currentPage ? 'btn' : 'btn-secondary'}" onclick="changePage(${i})">${i}</button>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    html += '<span style="padding: 0.5rem;">...</span>';
                }
            }
            
            if (currentPage < totalPages) {
                html += `<button class="btn btn-secondary" onclick="changePage(${currentPage + 1})">Seguinte →</button>`;
            }
            
            pagination.innerHTML = html;
        }

        function changePage(page) {
            currentPage = page;
            renderReservas();
            renderPagination();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function editarReserva(id) {
            const reserva = reservas.find(r => r.id === id);
            if (!reserva) return;

            document.getElementById('edit-id').value = reserva.id;
            document.getElementById('edit-passenger-name').value = reserva.passenger_name;
            document.getElementById('editModal').classList.add('show');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }

        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const id = document.getElementById('edit-id').value;
            const passengerName = document.getElementById('edit-passenger-name').value;

            try {
                const response = await fetch(`/api/reservas/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ passenger_name: passengerName })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Reserva atualizada com sucesso!');
                    closeEditModal();
                    carregarReservas();
                } else {
                    alert(data.message || 'Erro ao atualizar reserva');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao atualizar reserva');
            }
        });

        async function cancelarReserva(id) {
            if (!confirm('Tem certeza que deseja cancelar esta reserva?')) return;

            try {
                const response = await fetch(`/api/reservas/${id}/cancelar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Reserva cancelada com sucesso!');
                    carregarReservas();
                } else {
                    alert(data.message || 'Erro ao cancelar reserva');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao cancelar reserva');
            }
        }

        async function eliminarReserva(id) {
            if (!confirm('Tem certeza que deseja eliminar esta reserva? Esta ação não pode ser desfeita.')) return;

            try {
                const response = await fetch(`/api/reservas/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Reserva eliminada com sucesso!');
                    carregarReservas();
                } else {
                    alert(data.message || 'Erro ao eliminar reserva');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao eliminar reserva');
            }
        }

        carregarReservas();
    </script>
</body>
</html>
