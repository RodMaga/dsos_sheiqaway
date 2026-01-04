
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
    <title>sheiqaway - Detalhes da Viagem</title>
    @vite(['resources/css/style.css', 'resources/js/global.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .trip-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .trip-details-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .availability-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .price-highlight {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: 12px;
            border: 2px solid var(--primary-color);
            margin: 20px 0;
        }
    </style>
</head>
<body>
    @include('navbar')
    
    <main>
        <section class="content-card">
            <div id="details-container">
                <!-- Loading spinner -->
                <div id="loading" style="text-align: center; padding: 40px;">
                    <div class="loading-spinner" style="width: 60px; height: 60px; margin: 0 auto 20px;"></div>
                    <h3 style="color: var(--text-secondary);">A carregar detalhes da viagem...</h3>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>

    <script>
        // Variáveis globais
        let currentTrip = null;
        let occupancyChart = null;

        // Função para obter ID da URL
        function getTripIdFromURL() {
            const path = window.location.pathname;
            const segments = path.split('/');
            return segments[segments.length - 1];
        }

        // Carregar dados da viagem
        async function loadTripData() {
            const tripId = getTripIdFromURL();
            
            if (!tripId || tripId === 'detalhes') {
                showError('ID da viagem não especificado.');
                return null;
            }
            
            try {
                const response = await fetch(`/api/trips/${tripId}`);
                if (!response.ok) {
                    throw new Error('Viagem não encontrada');
                }
                
                const trip = await response.json();
                return trip;
                
            } catch (error) {
                console.error('Erro ao carregar viagem:', error);
                
                // Tentar carregar da lista completa como fallback
                try {
                    const allTripsResponse = await fetch('/api/trips');
                    const allTrips = await allTripsResponse.json();
                    const trip = allTrips.find(t => t.id === tripId);
                    if (trip) return trip;
                } catch (fallbackError) {
                    console.error('Fallback também falhou:', fallbackError);
                }
                
                throw error;
            }
        }

        // Carregar providers
        async function loadProviders() {
            try {
                const response = await fetch('/api/providers');
                if (!response.ok) return {};
                
                const providers = await response.json();
                const providerMap = {};
                providers.forEach(p => {
                    providerMap[p.id] = p.name;
                });
                return providerMap;
            } catch (error) {
                console.error('Erro ao carregar providers:', error);
                return {};
            }
        }

        // Mostrar erro
        function showError(message) {
            const container = document.getElementById('details-container');
            container.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 3em; margin-bottom: 20px; color: var(--border-color);">😕</div>
                    <h3 style="color: var(--text-secondary); margin-bottom: 15px;">${message}</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 25px;">
                        A viagem que procura não foi encontrada ou ocorreu um erro.
                    </p>
                    <a href="{{ route('home') }}" class="search-button" style="display: inline-block; padding: 12px 24px;">
                        ← Voltar à pesquisa
                    </a>
                </div>
            `;
        }

        // Renderizar detalhes
        function renderTripDetails(trip, providerName) {
            const container = document.getElementById('details-container');
            const lugaresLivres = trip.capacity - trip.booked;
            const ocupacaoPercent = Math.round((trip.booked / trip.capacity) * 100);
            const duracaoHoras = Math.floor(trip.durationMin / 60);
            const duracaoMinutos = trip.durationMin % 60;
            
            // Calcular horário de chegada
            const [departHour, departMin] = trip.depart.split(':').map(Number);
            const arriveTime = new Date();
            arriveTime.setHours(departHour, departMin + trip.durationMin);
            const arriveStr = `${arriveTime.getHours().toString().padStart(2, '0')}:${arriveTime.getMinutes().toString().padStart(2, '0')}`;
            
            // Determinar cor baseado na ocupação
            let statusColor, statusText;
            if (lugaresLivres === 0) {
                statusColor = '#dc3545';
                statusText = 'ESGOTADO';
            } else if (lugaresLivres < 5) {
                statusColor = '#ffc107';
                statusText = 'POUCOS LUGARES';
            } else {
                statusColor = '#28a745';
                statusText = 'DISPONÍVEL';
            }

            container.innerHTML = `
                <div class="trip-details-grid">
                    <!-- Coluna esquerda - Informações -->
                    <div>
                        <h2 style="margin-bottom: 5px;">${trip.from} ➔ ${trip.to}</h2>
                        <p style="color: var(--text-secondary); margin-bottom: 25px;">
                            <strong>${providerName}</strong> • ${trip.date} • ${trip.depart} → ${arriveStr}
                        </p>
                        
                        <!-- Status de disponibilidade -->
                        <div style="background: ${statusColor}15; border-left: 4px solid ${statusColor}; padding: 15px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 1.5em;">${lugaresLivres === 0 ? '⛔' : lugaresLivres < 5 ? '⚡' : '✅'}</span>
                                <div>
                                    <strong style="color: ${statusColor};">${statusText}</strong>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">
                                        ${lugaresLivres} de ${trip.capacity} lugares disponíveis
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Detalhes da viagem -->
                        <div style="background: var(--bg-body); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                            <h4 style="margin-top: 0; margin-bottom: 15px;">Detalhes da Viagem</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">Origem</div>
                                    <div style="font-weight: 600;">${trip.from}</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">Destino</div>
                                    <div style="font-weight: 600;">${trip.to}</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">Data</div>
                                    <div style="font-weight: 600;">${trip.date}</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">Partida</div>
                                    <div style="font-weight: 600;">${trip.depart}</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">Duração</div>
                                    <div style="font-weight: 600;">${duracaoHoras}h ${duracaoMinutos}min</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">Bagagem</div>
                                    <div style="font-weight: 600;">${trip.bagsIncluded} mala${trip.bagsIncluded !== 1 ? 's' : ''}</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preço -->
                        <div class="price-highlight">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">Preço final</div>
                                    <div style="font-size: 2.5em; font-weight: 800; color: var(--accent-color);">
                                        € ${trip.price.base.toFixed(2)}
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">Taxas incluídas</div>
                                    <div>€ ${(trip.price.base * 0.05).toFixed(2)}</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botões de ação -->
                        <div style="display: flex; gap: 15px; margin-top: 30px;">
                            <button id="reservar-btn" class="search-button" 
                                    style="flex: 1; padding: 15px; font-size: 1.1em;"
                                    ${lugaresLivres === 0 ? 'disabled' : ''}>
                                ${lugaresLivres === 0 ? '⛔ Esgotado' : '✅ Reservar Agora'}
                            </button>
                            <a href="{{ route('home') }}" class="secondary-button" 
                               style="flex: 0.5; padding: 15px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                ← Voltar
                            </a>
                        </div>
                    </div>
                    
                    <!-- Coluna direita - Visualizações -->
                    <div>
                        <!-- Gráfico de ocupação -->
                        <div style="background: white; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                            <h4 style="margin-top: 0; margin-bottom: 15px;">Ocupação do Veículo</h4>
                            <div style="height: 200px; position: relative;">
                                <canvas id="occupancyChart"></canvas>
                            </div>
                            <div style="display: flex; justify-content: center; gap: 30px; margin-top: 20px;">
                                <div style="text-align: center;">
                                    <div style="font-size: 2em; font-weight: 700; color: ${ocupacaoPercent > 80 ? '#dc3545' : ocupacaoPercent > 60 ? '#ffc107' : '#28a745'}">
                                        ${ocupacaoPercent}%
                                    </div>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">Ocupação</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 2em; font-weight: 700; color: var(--text-primary);">
                                        ${lugaresLivres}
                                    </div>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">Lugares livres</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Barra de progresso -->
                        <div style="background: white; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                            <h4 style="margin-top: 0; margin-bottom: 15px;">Disponibilidade</h4>
                            <div style="height: 10px; background: #e9ecef; border-radius: 5px; overflow: hidden; margin-bottom: 10px;">
                                <div id="ocupacao-bar" style="width: ${ocupacaoPercent}%; height: 100%; 
                                     background: ${ocupacaoPercent > 80 ? '#dc3545' : 
                                                 ocupacaoPercent > 60 ? '#ffc107' : '#28a745'};">
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.9em; color: var(--text-secondary);">
                                <span>Lugares ocupados: ${trip.booked}</span>
                                <span>Capacidade total: ${trip.capacity}</span>
                            </div>
                        </div>
                        
                        <!-- Informações importantes -->
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px dashed var(--border-color);">
                            <h5 style="margin-top: 0; color: var(--text-primary);">📋 Informações importantes</h5>
                            <ul style="color: var(--text-secondary); font-size: 0.9em; padding-left: 20px; margin-bottom: 0;">
                                <li>Check-in online disponível 24h antes</li>
                                <li>Apresente o bilhete digital no embarque</li>
                                <li>Tolerância de 15 minutos para atrasos</li>
                                <li>Bagagem de mão: 1 peça até 10kg</li>
                                <li>Cancelamento gratuito até 24h antes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
            
            // Configurar gráfico
            setupChart(trip, lugaresLivres, ocupacaoPercent);
            
            // Configurar botão de reserva
            setupReservationButton(trip, lugaresLivres);
        }

        // Configurar gráfico
        function setupChart(trip, lugaresLivres, ocupacaoPercent) {
            const ctx = document.getElementById('occupancyChart');
            if (!ctx) return;
            
            // Destruir gráfico anterior se existir
            if (occupancyChart) {
                occupancyChart.destroy();
            }
            
            try {
                occupancyChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Ocupado', 'Livre'],
                        datasets: [{
                            data: [trip.booked, lugaresLivres],
                            backgroundColor: [
                                ocupacaoPercent > 80 ? '#dc3545' : 
                                ocupacaoPercent > 60 ? '#ffc107' : '#28a745',
                                '#e9ecef'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            } catch (error) {
                console.error('Erro ao criar gráfico:', error);
            }
        }

        // Configurar botão de reserva
        function setupReservationButton(trip, lugaresLivres) {
            const btn = document.getElementById('reservar-btn');
            if (!btn) return;
            
            btn.addEventListener('click', function() {
                if (lugaresLivres === 0) {
                    alert('Lamentamos, esta viagem está esgotada.');
                    return;
                }

                // Verificar se o utilizador está autenticado
                if (!window.GlobalManager || !window.GlobalManager.isLoggedIn()) {
                    if (confirm('Para reservar uma viagem, precisa de fazer login. Deseja ir para a página de login?')) {
                        window.location.href = '{{ route("login") }}';
                    }
                    return;
                }

                // Adicionar ao carrinho
                if (window.GlobalManager && window.GlobalManager.addToCart) {
                    const success = window.GlobalManager.addToCart(trip);
                    
                    if (success) {
                        // Atualizar botão
                        btn.textContent = '✓ Adicionado ao Carrinho';
                        btn.disabled = true;
                        btn.style.background = '#28a745';
                        
                        // Mostrar mensagem
                        if (window.GlobalManager.showToast) {
                            window.GlobalManager.showToast('Viagem adicionada ao carrinho!', 'success');
                        }
                    }
                } else {
                    alert('Erro: Não foi possível adicionar ao carrinho. Tente novamente.');
                }
            });
        }

        // Inicializar página
        async function initPage() {
            try {
                // Carregar dados em paralelo
                const [trip, providerMap] = await Promise.all([
                    loadTripData(),
                    loadProviders()
                ]);
                
                if (!trip) {
                    showError('Viagem não encontrada.');
                    return;
                }
                
                const providerName = providerMap[trip.providerId] || trip.providerId;
                currentTrip = trip;
                
                // Renderizar detalhes
                renderTripDetails(trip, providerName);
                
            } catch (error) {
                console.error('Erro ao inicializar página:', error);
                showError('Não foi possível carregar os dados da viagem.');
            }
        }

        // Iniciar quando a página carregar
        document.addEventListener('DOMContentLoaded', initPage);
    </script>
</body>
</html>