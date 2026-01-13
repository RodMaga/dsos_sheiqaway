<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#0ea5e9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="true">
    <meta name="user-name" content="{{ Auth::user()->name }}">
    <title>Admin - Sheiqaway</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
    <style>
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(200px, 100%), 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.25rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .stat-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .stat-value { font-size: 1.75rem; font-weight: bold; color: #1f2937; }
        .stat-label { color: #6b7280; font-size: 0.85rem; margin-top: 0.5rem; }
        .card { background: white; padding: 1.25rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
        .card h2 { margin: 0 0 1rem 0; font-size: 1.15rem; color: #1f2937; }
        .admin-table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0 -1.25rem; padding: 0 1.25rem; }
        .admin-table { width: 100%; border-collapse: collapse; min-width: 600px; }
        .admin-table th { text-align: left; padding: 0.625rem; background: #f9fafb; border-bottom: 2px solid #e5e7eb; font-weight: 600; font-size: 0.875rem; white-space: nowrap; }
        .admin-table td { padding: 0.625rem; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem; }
        .admin-table tr:hover { background: #f9fafb; }
        .badge { padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500; white-space: nowrap; }
        .badge-confirmado { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .btn-action { padding: 0.375rem 0.625rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; min-width: 36px; min-height: 36px; }
        .btn-danger { background: #fee2e2; color: #991b1b; }
        .btn-danger:hover { background: #fecaca; }
        .form-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        .form-grid label { display: block; margin-bottom: 0.25rem; font-weight: 500; color: #374151; font-size: 0.9rem; }
        .form-grid input, .form-grid select, .form-grid textarea { width: 100%; padding: 0.625rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 16px; }
        .form-grid textarea { min-height: 80px; resize: vertical; }
        .two-col-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .admin-header { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; }
        .admin-header h1 { margin: 0; font-size: 1.75rem; }
        .admin-header-buttons { display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .admin-header-buttons a { background: #0ea5e9; color: white; text-decoration: none; padding: 0.75rem 1.25rem; border-radius: 6px; font-size: 0.9rem; white-space: nowrap; text-align: center; min-height: 44px; display: flex; align-items: center; justify-content: center; }
        
        body.dark-mode .stat-card, body.dark-mode .card { background: #2d2d2d; }
        body.dark-mode .stat-value, body.dark-mode .card h2 { color: #f3f4f6; }
        body.dark-mode .admin-table th { background: #1f1f1f; color: #f3f4f6; }
        body.dark-mode .admin-table td { color: #d1d5db; border-bottom-color: #404040; }
        body.dark-mode .admin-table tr:hover { background: #1f1f1f; }
        body.dark-mode .form-grid input, body.dark-mode .form-grid select, body.dark-mode .form-grid textarea { background: #1a1a1a; border-color: #404040; color: #e0e0e0; }
        
        @media (min-width: 640px) {
            .form-grid { grid-template-columns: 1fr 1fr; }
            .full-width { grid-column: 1 / -1; }
        }
        
        @media (min-width: 768px) {
            .admin-header { flex-direction: row; justify-content: space-between; align-items: center; }
            .two-col-grid { grid-template-columns: 1fr 1fr; }
            .card { padding: 1.5rem; }
            .admin-table-wrapper { margin: 0; padding: 0; }
        }
        
        @media (min-width: 1024px) {
            .admin-grid { grid-template-columns: repeat(5, 1fr); gap: 1.5rem; }
        }
    </style>
</head>
<body class="admin-page">
    @include('navbar')
    
    <main style="max-width: 1400px; margin: 0 auto; padding: 1.5rem 1rem;">
        <div class="admin-header">
            <h1>⚙ Painel Admin</h1>
            <div class="admin-header-buttons">
                <a href="/admin/users">⚇ Utilizadores</a>
                <a href="/admin/reservations" style="background: #10b981;">✈ Reservas</a>
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

        <div class="two-col-grid">
            <div class="card">
                <h2>★ Top 10 Clientes</h2>
                <div class="admin-table-wrapper">
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
            </div>

            <div class="card">
                <h2>◉ Top 10 Destinos</h2>
                <div class="admin-table-wrapper">
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
        </div>

        <div class="two-col-grid">
            <div class="card">
                <h2>✈ Top 5 Companhias</h2>
                <div class="admin-table-wrapper">
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
            </div>

            <div class="card">
                <h2>€ Receita Mensal</h2>
                <div class="admin-table-wrapper">
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
        </div>

        <div class="card">
            <h2>▤ Últimas 20 Reservas</h2>
            <div class="admin-table-wrapper">
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
