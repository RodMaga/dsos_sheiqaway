<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Minhas Reservas - Sheiqaway</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .reserva-card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 15px; background: white; }
        .reserva-card.cancelado { opacity: 0.6; background: #f5f5f5; }
        .status-badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: bold; }
        .status-confirmado { background: #4caf50; color: white; }
        .status-cancelado { background: #f44336; color: white; }
        .btn { padding: 8px 16px; border-radius: 5px; border: none; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #2196f3; color: white; }
        .btn-danger { background: #f44336; color: white; }
        .btn-warning { background: #ff9800; color: white; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; padding: 30px; border-radius: 10px; width: 90%; max-width: 500px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    @include('navbar')
    
    <div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <h1 style="margin-bottom: 30px;">Minhas Reservas</h1>
        
        <div id="reservas-container"></div>
    </div>

    <!-- Modal Editar -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Editar Reserva</h2>
            <form id="editForm">
                <input type="hidden" id="edit-id">
                <div class="form-group">
                    <label>Nome do Passageiro</label>
                    <input type="text" id="edit-passenger-name" required>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn" onclick="closeEditModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let reservas = [];

        async function carregarReservas() {
            try {
                const response = await fetch('/api/reservas');
                const data = await response.json();
                
                if (data.success) {
                    reservas = data.reservas;
                    renderReservas();
                }
            } catch (error) {
                console.error('Erro ao carregar reservas:', error);
            }
        }

        function renderReservas() {
            const container = document.getElementById('reservas-container');
            
            if (reservas.length === 0) {
                container.innerHTML = '<p>Não tem reservas.</p>';
                return;
            }

            container.innerHTML = reservas.map(r => `
                <div class="reserva-card ${r.status}">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h3>Reserva #${r.booking_reference}</h3>
                            <p><strong>Passageiro:</strong> ${r.passenger_name}</p>
                            <p><strong>Viagem ID:</strong> ${r.trip_id}</p>
                            <p><strong>Preço:</strong> €${parseFloat(r.price).toFixed(2)}</p>
                            <p><strong>Data:</strong> ${new Date(r.created_at).toLocaleDateString('pt-PT')}</p>
                            <span class="status-badge status-${r.status}">${r.status.toUpperCase()}</span>
                        </div>
                        <div style="display: flex; gap: 10px; flex-direction: column;">
                            ${r.status !== 'cancelado' ? `
                                <button class="btn btn-warning" onclick="editarReserva(${r.id})">Editar</button>
                                <button class="btn btn-danger" onclick="cancelarReserva(${r.id})">Cancelar</button>
                            ` : ''}
                            <button class="btn btn-danger" onclick="eliminarReserva(${r.id})">Eliminar</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function editarReserva(id) {
            const reserva = reservas.find(r => r.id === id);
            if (!reserva) return;

            document.getElementById('edit-id').value = reserva.id;
            document.getElementById('edit-passenger-name').value = reserva.passenger_name;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
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
