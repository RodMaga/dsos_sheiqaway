<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - sheiqaway</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/pages.css', 'resources/css/admin.css'])
</head>
<body>
    @include('navbar')

    <main class="admin-container">
        <div class="admin-header">
            <h1>🛡️ Painel de Administração</h1>
            <p>Gerir campanhas e descontos do sistema</p>
        </div>

        <div class="campaign-form">
            <h2>📢 Criar Nova Campanha</h2>
            <form id="campaignForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nome da Campanha <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required placeholder="Ex: Voos Longos">
                        <small>Nome identificador da campanha</small>
                    </div>

                    <div class="form-group">
                        <label for="discount_type">Tipo de Desconto <span class="required">*</span></label>
                        <select id="discount_type" name="discount_type" required>
                            <option value="">Selecione...</option>
                            <option value="PERCENTAGE">Percentagem (%)</option>
                            <option value="FIXED">Valor Fixo (€)</option>
                        </select>
                        <small>Como o desconto será aplicado</small>
                    </div>

                    <div class="form-group">
                        <label for="discount_value">Valor do Desconto <span class="required">*</span></label>
                        <input type="number" id="discount_value" name="discount_value" required min="0" step="0.01" placeholder="Ex: 15">
                        <small>Valor numérico do desconto</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Descrição <span class="required">*</span></label>
                        <textarea id="description" name="description" required placeholder="Descreva a campanha..."></textarea>
                        <small>Descrição detalhada da campanha</small>
                    </div>

                    <div class="form-group">
                        <label for="start_date">Data de Início <span class="required">*</span></label>
                        <input type="date" id="start_date" name="start_date" required>
                        <small>Quando a campanha começa</small>
                    </div>

                    <div class="form-group">
                        <label for="end_date">Data de Fim <span class="required">*</span></label>
                        <input type="date" id="end_date" name="end_date" required>
                        <small>Quando a campanha termina</small>
                    </div>

                    <div class="form-group full-width">
                        <label>Prioridade <span class="required">*</span></label>
                        <div class="priority-selector">
                            <input type="radio" id="priority1" name="priority" value="1" required>
                            <label for="priority1">1</label>
                            <input type="radio" id="priority2" name="priority" value="2">
                            <label for="priority2">2</label>
                            <input type="radio" id="priority3" name="priority" value="3" checked>
                            <label for="priority3">3</label>
                            <input type="radio" id="priority4" name="priority" value="4">
                            <label for="priority4">4</label>
                            <input type="radio" id="priority5" name="priority" value="5">
                            <label for="priority5">5</label>
                        </div>
                        <small>1 = Mais baixa, 5 = Mais alta (campanhas com maior prioridade são aplicadas primeiro)</small>
                    </div>

                    <div class="form-group">
                        <label for="attribute">Atributo <span class="required">*</span></label>
                        <select id="attribute" name="attribute" required>
                            <option value="">Selecione...</option>
                            <option value="PRICE">Preço</option>
                            <option value="DURATION">Duração (horas)</option>
                            <option value="AIRLINE">Companhia Aérea</option>
                        </select>
                        <small>Que característica da viagem será verificada</small>
                    </div>

                    <div class="form-group">
                        <label for="operator">Operador <span class="required">*</span></label>
                        <select id="operator" name="operator" required>
                            <option value="">Selecione...</option>
                            <option value=">">Maior que (>)</option>
                            <option value="<">Menor que (<)</option>
                            <option value="=">Igual a (=)</option>
                            <option value=">=">Maior ou igual (>=)</option>
                            <option value="<=">Menor ou igual (<=)</option>
                        </select>
                        <small>Condição de comparação</small>
                    </div>

                    <div class="form-group">
                        <label for="value">Valor da Condição <span class="required">*</span></label>
                        <input type="text" id="value" name="value" required placeholder="Ex: 6">
                        <select id="value_select" name="value_select" style="display: none;">
                            <option value="">Carregando companhias...</option>
                        </select>
                        <small id="value_help">Valor para comparar (para AIRLINE use código como 'TAP')</small>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    ✨ Criar Campanha
                </button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 sheiqaway · Painel de Administração</p>
    </footer>

    <div class="notification" id="notification"></div>

    <script>
        let airlines = [];

        // Fetch airlines from API on page load
        async function loadAirlines() {
            try {
                const response = await fetch('https://vs-gate.dei.isep.ipp.pt:10923/api/viagens');
                const trips = await response.json();
                
                // Extract unique airlines
                const uniqueAirlines = [...new Set(trips.map(trip => trip.companhia))];
                airlines = uniqueAirlines.sort();
                
                // Populate the select
                const select = document.getElementById('value_select');
                select.innerHTML = '<option value="">Selecione uma companhia...</option>';
                airlines.forEach(airline => {
                    const option = document.createElement('option');
                    option.value = airline;
                    option.textContent = airline;
                    select.appendChild(option);
                });
                
                console.log('Loaded airlines:', airlines);
            } catch (error) {
                console.error('Error loading airlines:', error);
            }
        }

        // Load airlines when page loads
        loadAirlines();

        // Handle attribute change
        document.getElementById('attribute').addEventListener('change', (e) => {
            const valueInput = document.getElementById('value');
            const valueSelect = document.getElementById('value_select');
            const operatorSelect = document.getElementById('operator');
            const valueHelp = document.getElementById('value_help');
            
            if (e.target.value === 'AIRLINE') {
                // Switch to select for airlines
                valueInput.style.display = 'none';
                valueInput.removeAttribute('required');
                valueSelect.style.display = 'block';
                valueSelect.setAttribute('required', 'required');
                valueSelect.name = 'value';
                valueInput.name = '';
                
                // Auto-select = operator but keep it enabled (don't disable)
                operatorSelect.value = '=';
                operatorSelect.setAttribute('readonly', 'readonly');
                operatorSelect.style.opacity = '0.6';
                operatorSelect.style.pointerEvents = 'none';
                
                // Update help text
                valueHelp.textContent = 'Selecione a companhia aérea';
            } else {
                // Switch back to input for other attributes
                valueInput.style.display = 'block';
                valueInput.setAttribute('required', 'required');
                valueInput.name = 'value';
                valueSelect.style.display = 'none';
                valueSelect.removeAttribute('required');
                valueSelect.name = '';
                
                // Enable operator select
                operatorSelect.removeAttribute('readonly');
                operatorSelect.style.opacity = '1';
                operatorSelect.style.pointerEvents = 'auto';
                
                // Update help text based on attribute
                if (e.target.value === 'DURATION') {
                    valueHelp.textContent = 'Duração em horas (ex: 6 para 6 horas)';
                } else if (e.target.value === 'PRICE') {
                    valueHelp.textContent = 'Valor do preço (ex: 100 para 100€)';
                } else {
                    valueHelp.textContent = 'Valor para comparar';
                }
            }
        });

        document.getElementById('campaignForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Criando...';
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            // Log the data being sent
            console.log('📤 Sending campaign data:', data);
            
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
                
                console.log('📥 Server response:', result);
                
                if (result.success) {
                    showNotification('success', result.message);
                    e.target.reset();
                    // Set default priority to 3
                    document.getElementById('priority3').checked = true;
                    // Reset to input mode
                    document.getElementById('value').style.display = 'block';
                    document.getElementById('value_select').style.display = 'none';
                    document.getElementById('operator').removeAttribute('readonly');
                    document.getElementById('operator').style.opacity = '1';
                    document.getElementById('operator').style.pointerEvents = 'auto';
                } else {
                    // Show detailed error message
                    let errorMsg = result.message || 'Erro desconhecido';
                    
                    // If there are validation errors, display them
                    if (result.errors) {
                        console.error('❌ Validation errors:', result.errors);
                        errorMsg += '\n\nErros de validação:\n';
                        for (const [field, messages] of Object.entries(result.errors)) {
                            errorMsg += `\n• ${field}: ${messages.join(', ')}`;
                        }
                    }
                    
                    showNotification('error', errorMsg);
                    alert('ERRO DE VALIDAÇÃO:\n\n' + errorMsg);
                }
            } catch (error) {
                console.error('❌ Error:', error);
                showNotification('error', 'Erro ao criar campanha. Verifique a consola.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = '✨ Criar Campanha';
            }
        });
        
        function showNotification(type, message) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').min = today;
        document.getElementById('end_date').min = today;

        // Update end date min when start date changes
        document.getElementById('start_date').addEventListener('change', (e) => {
            document.getElementById('end_date').min = e.target.value;
        });
    </script>
</body>
</html>

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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div class="card">
                <h2>✈ Top 5 Companhias</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Companhia</th>
                            <th>Total Reservas</th>
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
