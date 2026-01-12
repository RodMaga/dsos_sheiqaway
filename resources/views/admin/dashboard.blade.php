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
    <style>
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .stat-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .stat-value { font-size: 2rem; font-weight: bold; color: #1f2937; }
        .stat-label { color: #6b7280; font-size: 0.9rem; margin-top: 0.5rem; }
        .card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .card h2 { margin: 0 0 1rem 0; font-size: 1.25rem; color: #1f2937; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { text-align: left; padding: 0.75rem; background: #f9fafb; border-bottom: 2px solid #e5e7eb; font-weight: 600; }
        .admin-table td { padding: 0.75rem; border-bottom: 1px solid #e5e7eb; }
        .admin-table tr:hover { background: #f9fafb; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.85rem; font-weight: 500; }
        .badge-confirmado { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .btn-action { padding: 0.25rem 0.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .btn-danger { background: #fee2e2; color: #991b1b; }
        .btn-danger:hover { background: #fecaca; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-grid label { display: block; margin-bottom: 0.25rem; font-weight: 500; color: #374151; }
        .form-grid input, .form-grid select, .form-grid textarea { width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; }
        .form-grid textarea { min-height: 80px; resize: vertical; }
        .full-width { grid-column: 1 / -1; }
        body.dark-mode .stat-card, body.dark-mode .card { background: #2d2d2d; }
        body.dark-mode .stat-value, body.dark-mode .card h2 { color: #f3f4f6; }
        body.dark-mode .admin-table th { background: #1f1f1f; color: #f3f4f6; }
        body.dark-mode .admin-table td { color: #d1d5db; border-bottom-color: #404040; }
        body.dark-mode .admin-table tr:hover { background: #1f1f1f; }
    </style>
</head>
<body class="admin-page">
    @include('navbar')
    
    <main style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>⚙ Painel Admin</h1>
            <div style="display: flex; gap: 1rem;">
                <a href="/admin/users" class="btn" style="background: #0ea5e9; color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 6px;">⚇ Utilizadores</a>
                <a href="/admin/reservations" class="btn" style="background: #10b981; color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 6px;">✈ Reservas</a>
            </div>
        </div>

        <div class="admin-grid">
            <div class="stat-card">
                <div class="stat-icon">⚇</div>
                <div class="stat-value">{{ $stats['total_users'] }}</div>
                <div class="stat-label">Utilizadores</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✈</div>
                <div class="stat-value">{{ $stats['total_reservations'] }}</div>
                <div class="stat-label">Reservas</div>
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
            <div class="stat-card">
                <div class="stat-icon">×</div>
                <div class="stat-value">{{ $stats['cancelled_reservations'] }}</div>
                <div class="stat-label">Reservas Canceladas</div>
            </div>
        </div>

        <div class="card">
            <h2>📢 Criar Campanha</h2>
            <form id="campaignForm" class="form-grid">
                <div>
                    <label>Nome *</label>
                    <input type="text" name="name" required placeholder="Ex: Voos Longos">
                </div>
                <div>
                    <label>Tipo *</label>
                    <select name="discount_type" required>
                        <option value="">Selecione...</option>
                        <option value="percentage">Percentagem (%)</option>
                        <option value="fixed">Valor Fixo (€)</option>
                    </select>
                </div>
                <div>
                    <label>Valor *</label>
                    <input type="number" name="discount_value" required min="0" step="0.01" placeholder="Ex: 15">
                </div>
                <div>
                    <label>Prioridade *</label>
                    <select name="priority" required>
                        <option value="1">1 - Baixa</option>
                        <option value="2">2</option>
                        <option value="3" selected>3 - Média</option>
                        <option value="4">4</option>
                        <option value="5">5 - Alta</option>
                    </select>
                </div>
                <div class="full-width">
                    <label>Descrição *</label>
                    <textarea name="description" required placeholder="Descreva a campanha..."></textarea>
                </div>
                <div>
                    <label>Data Início *</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div>
                    <label>Data Fim *</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
                <div>
                    <label>Atributo *</label>
                    <select id="attribute" name="attribute" required>
                        <option value="">Selecione...</option>
                        <option value="PRICE">Preço</option>
                        <option value="DURATION">Duração (horas)</option>
                        <option value="AIRLINE">Companhia</option>
                    </select>
                </div>
                <div>
                    <label>Operador *</label>
                    <select id="operator" name="operator" required>
                        <option value="">Selecione...</option>
                        <option value=">">></option>
                        <option value="<"><</option>
                        <option value="=">=</option>
                        <option value=">=">>=</option>
                        <option value="<="><=</option>
                    </select>
                </div>
                <div class="full-width">
                    <label>Valor Condição *</label>
                    <input type="text" id="value" name="value" required placeholder="Ex: 50">
                    <select id="value_select" name="value_select" style="display: none;">
                        <option value="">Carregando...</option>
                    </select>
                </div>
                <div class="full-width">
                    <button type="submit" class="btn" id="submitBtn" style="width: 100%;">✨ Criar Campanha</button>
                </div>
            </form>
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
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topDestinations as $dest)
                        <tr>
                            <td>{{ $dest->route }}</td>
                            <td><strong>{{ $dest->total }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div class="card">
                <h2>✈ Top 5 Companhias</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Companhia</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCompanies as $company => $total)
                        <tr>
                            <td>{{ $company }}</td>
                            <td><strong>{{ $total }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>€ Receita Mensal</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mês</th>
                            <th>Receita</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyRevenue as $rev)
                        <tr>
                            <td>{{ $rev->month }}</td>
                            <td><strong>€{{ number_format($rev->revenue, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2>▤ Últimas 20 Reservas</h2>
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
                            <a href="{{ route('admin.reservations.edit', $res->id) }}" class="btn-action" style="background: #fef3c7; color: #92400e;" title="Editar">✎</a>
                            <form method="POST" action="{{ route('admin.reservations.delete', $res->id) }}" style="display: inline;" onsubmit="return confirm('Eliminar?');">
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

    <div id="notification" style="display: none; position: fixed; top: 20px; right: 20px; padding: 1rem 1.5rem; border-radius: 8px; background: #10b981; color: white; z-index: 9999;"></div>

    <script>
        async function loadAirlines() {
            try {
                const response = await fetch('https://vs-gate.dei.isep.ipp.pt:10923/api/viagens');
                const trips = await response.json();
                const airlines = [...new Set(trips.map(t => t.companhia))].sort();
                const select = document.getElementById('value_select');
                select.innerHTML = '<option value="">Selecione...</option>';
                airlines.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a;
                    opt.textContent = a;
                    select.appendChild(opt);
                });
            } catch (e) {
                console.error('Error loading airlines:', e);
            }
        }

        loadAirlines();

        document.getElementById('attribute').addEventListener('change', (e) => {
            const valueInput = document.getElementById('value');
            const valueSelect = document.getElementById('value_select');
            const operatorSelect = document.getElementById('operator');
            
            if (e.target.value === 'AIRLINE') {
                valueInput.style.display = 'none';
                valueInput.removeAttribute('required');
                valueSelect.style.display = 'block';
                valueSelect.setAttribute('required', 'required');
                valueSelect.name = 'value';
                valueInput.name = '';
                operatorSelect.value = '=';
                operatorSelect.style.opacity = '0.6';
                operatorSelect.style.pointerEvents = 'none';
            } else {
                valueInput.style.display = 'block';
                valueInput.setAttribute('required', 'required');
                valueInput.name = 'value';
                valueSelect.style.display = 'none';
                valueSelect.removeAttribute('required');
                valueSelect.name = '';
                operatorSelect.style.opacity = '1';
                operatorSelect.style.pointerEvents = 'auto';
            }
        });

        document.getElementById('campaignForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Criando...';
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('/admin/campaigns/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('✓ ' + result.message, '#10b981');
                    e.target.reset();
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('start_date').value = today;
                } else {
                    showNotification('✗ ' + (result.message || 'Erro'), '#ef4444');
                }
            } catch (error) {
                showNotification('✗ Erro ao criar campanha', '#ef4444');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = '✨ Criar Campanha';
            }
        });
        
        function showNotification(message, color) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.style.background = color;
            notification.style.display = 'block';
            setTimeout(() => notification.style.display = 'none', 3000);
        }

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').min = today;
        document.getElementById('start_date').value = today;
        document.getElementById('end_date').min = today;
        document.getElementById('start_date').addEventListener('change', (e) => {
            document.getElementById('end_date').min = e.target.value;
        });
    </script>
</body>
</html>
