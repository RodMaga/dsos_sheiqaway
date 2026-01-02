<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sheiqaway - Detalhes da Viagem</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <button type="button" id="logo-button" class="header-logo-button">
            <h1>sheiqaway</h1>
        </button>
        <nav>
            <!-- Será preenchido pelo global.js -->
        </nav>
    </header>
    
    <main>
        <section class="content-card" id="details-container">
            <!-- Conteúdo será carregado por JavaScript -->
        </section>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>

    <script src="js/global.js"></script>
    
    <script>
        // Função para obter o ID da URL
        function getTripIdFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('id');
        }

        // Dados de teste - remover quando o JSON funcionar
        const tripsData = [
            {
                "id": "trip-001",
                "from": "Porto",
                "to": "Lisboa",
                "date": "2024-04-15",
                "depart": "08:00",
                "arrive": "11:30",
                "durationMin": 210,
                "providerId": "expressobus",
                "bagsIncluded": 1,
                "price": {
                    "base": 19.99,
                    "original": 24.99,
                    "discount": true
                },
                "capacity": 50,
                "booked": 35
            },
            {
                "id": "trip-002",
                "from": "Lisboa",
                "to": "Faro",
                "date": "2024-04-16",
                "depart": "09:00",
                "arrive": "15:30",
                "durationMin": 390,
                "providerId": "luso-bus",
                "bagsIncluded": 2,
                "price": {
                    "base": 29.99,
                    "original": 29.99,
                    "discount": false
                },
                "capacity": 45,
                "booked": 40
            },
            {
                "id": "trip-003",
                "from": "Coimbra",
                "to": "Porto",
                "date": "2024-04-17",
                "depart": "14:00",
                "arrive": "16:00",
                "durationMin": 120,
                "providerId": "rapida",
                "bagsIncluded": 1,
                "price": {
                    "base": 12.50,
                    "original": 15.00,
                    "discount": true
                },
                "capacity": 40,
                "booked": 25
            }
        ];

        // Mapa de providers
        const providers = {
            "expressobus": "Expresso Bus",
            "luso-bus": "Luso Bus",
            "rapida": "Rápida"
        };

        // Função para mostrar erro
        function showError(message) {
            const container = document.getElementById('details-container');
            container.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <h3 style="color: var(--text-secondary);">${message}</h3>
                    <p>Desculpe, não foi possível carregar a viagem solicitada.</p>
                    <button onclick="window.location.href='index.html'" class="search-button" style="margin-top: 20px;">
                        Voltar à pesquisa
                    </button>
                </div>
            `;
        }

        // Função para carregar dados
        async function loadTripData() {
            const tripId = getTripIdFromURL();
            
            if (!tripId) {
                showError('ID da viagem não especificado.');
                return null;
            }
            
            try {
                // Primeiro tenta usar os dados de teste
                const tripFromTestData = tripsData.find(t => t.id === tripId);
                if (tripFromTestData) {
                    return tripFromTestData;
                }
                
                // Se não encontrar nos dados de teste, tenta carregar do JSON
                const response = await fetch('data/trips_com_lotacao.json');
                if (!response.ok) {
                    throw new Error('Falha ao carregar dados do servidor');
                }
                
                const trips = await response.json();
                const trip = trips.find(t => t.id === tripId);
                
                if (!trip) {
                    throw new Error('Viagem não encontrada');
                }
                
                return trip;
                
            } catch (error) {
                console.log('Usando dados de teste devido a erro:', error.message);
                // Tenta novamente com dados de teste
                const tripFromTestData = tripsData.find(t => t.id === tripId);
                if (tripFromTestData) {
                    return tripFromTestData;
                }
                throw error;
            }
        }

        // Função principal
        async function initPage() {
            try {
                const trip = await loadTripData();
                if (!trip) return;
                
                // Renderizar os detalhes
                renderTripDetails(trip);
                
            } catch (error) {
                console.error('Erro ao inicializar página:', error);
                showError('Não foi possível carregar os dados da viagem.');
            }
        }

        function renderTripDetails(trip) {
            const container = document.getElementById('details-container');
            if (!container) return;
            
            const lugaresLivres = trip.capacity - trip.booked;
            const ocupacaoPercent = Math.round((trip.booked / trip.capacity) * 100);
            const duracaoHoras = Math.floor(trip.durationMin / 60);
            const duracaoMinutos = trip.durationMin % 60;
            const providerName = providers[trip.providerId] || trip.providerId;
            
            container.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <!-- Coluna esquerda - Informações -->
                    <div>
                        <h2>${trip.from} ➔ ${trip.to}</h2>
                        
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                            <p><strong>Operado por:</strong> ${providerName}</p>
                            <p><strong>Data:</strong> ${trip.date} às ${trip.depart}</p>
                            <p><strong>Chegada:</strong> ${trip.arrive} (Duração: ${duracaoHoras}h ${duracaoMinutos}min)</p>
                            <p><strong>Bagagem:</strong> ${trip.bagsIncluded} mala${trip.bagsIncluded !== 1 ? 's' : ''} incluída${trip.bagsIncluded !== 1 ? 's' : ''}</p>
                            <p><strong>Lugares disponíveis:</strong> 
                                <span style="color: ${lugaresLivres < 5 ? '#dc3545' : '#28a745'}; font-weight: bold;">
                                    ${lugaresLivres} de ${trip.capacity}
                                    ${lugaresLivres < 3 ? ' ⚡ Poucos lugares!' : ''}
                                </span>
                            </p>
                        </div>
                        
                        <div style="background: #e9ecef; padding: 20px; border-radius: 8px; margin: 20px 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <h3 style="color: var(--accent-color); margin: 0;">€ ${trip.price.base.toFixed(2)}</h3>
                                ${trip.price.discount ? `
                                    <div>
                                        <s style="color: #6c757d;">€ ${trip.price.original.toFixed(2)}</s>
                                        <span style="color: #28a745; margin-left: 8px; font-weight: 600;">
                                            -${Math.round((1 - trip.price.base/trip.price.original) * 100)}%
                                        </span>
                                    </div>
                                ` : ''}
                            </div>
                            <p style="color: #6c757d; font-size: 0.9em; margin: 0;">
                                Taxas incluídas: € ${(trip.price.base * 0.05).toFixed(2)}
                            </p>
                        </div>
                        
                        <button id="reservar-btn" class="search-button" 
                                style="width: 100%; padding: 15px; font-size: 1.1em; margin-bottom: 15px;"
                                ${lugaresLivres === 0 ? 'disabled' : ''}>
                            ${lugaresLivres === 0 ? '❌ Esgotado' : '✅ Reservar Agora'}
                        </button>
                        
                        <button onclick="window.location.href='index.html'" class="secondary-button" 
                                style="width: 100%; padding: 12px;">
                            ← Voltar à pesquisa
                        </button>
                    </div>
                    
                    <!-- Coluna direita - Visualizações -->
                    <div>
                        <h4>Disponibilidade de Lugares</h4>
                        
                        <!-- Barra de progresso -->
                        <div style="margin: 20px 0 30px 0;">
                            <div style="height: 10px; background: #e9ecef; border-radius: 5px; overflow: hidden;">
                                <div id="ocupacao-bar" style="width: ${ocupacaoPercent}%; height: 100%; 
                                     background: ${ocupacaoPercent > 80 ? '#dc3545' : 
                                                 ocupacaoPercent > 60 ? '#ffc107' : '#28a745'};">
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.9em; color: #6c757d;">
                                <span id="ocupado-text">Ocupado: ${trip.booked} lugares</span>
                                <span id="livre-text">Livre: ${lugaresLivres} lugares</span>
                            </div>
                        </div>
                        
                        <!-- Gráfico -->
                        <div style="height: 250px; margin: 30px 0;">
                            <canvas id="occupancyChart"></canvas>
                        </div>
                        
                        <!-- Informações adicionais -->
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            <h5 style="margin-top: 0;">Informações importantes</h5>
                            <ul style="color: #6c757d; font-size: 0.9em; padding-left: 20px; margin: 0;">
                                <li>Check-in online disponível 24h antes da partida</li>
                                <li>Apresente o código QR no embarque</li>
                                <li>Tolerância de 15 minutos para atrasos</li>
                                <li>Bagagem de mão: 1 peça até 10kg</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
            
            // Configurar gráfico
            setupChart(trip, lugaresLivres, ocupacaoPercent);
            
            // Configurar botão de reserva
            setupReservationButton(trip, lugaresLivres, ocupacaoPercent);
        }

        function setupChart(trip, lugaresLivres, ocupacaoPercent) {
    const ctx = document.getElementById('occupancyChart');
    if (!ctx) return;
    
    // Verificar se já existe um gráfico E se tem o método destroy
    if (window.occupancyChart && typeof window.occupancyChart.destroy === 'function') {
        window.occupancyChart.destroy();
    }
    
    try {
        window.occupancyChart = new Chart(ctx, {
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
                            padding: 20,
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
        // Se falhar, mostra uma alternativa
        ctx.parentElement.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <div style="display: inline-flex; align-items: center; gap: 20px;">
                    <div>
                        <div style="width: 80px; height: 80px; border-radius: 50%; 
                                    background: conic-gradient(
                                        ${ocupacaoPercent > 80 ? '#dc3545' : ocupacaoPercent > 60 ? '#ffc107' : '#28a745'} 0% ${ocupacaoPercent}%, 
                                        #e9ecef ${ocupacaoPercent}% 100%
                                    );">
                        </div>
                    </div>
                    <div style="text-align: left;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; border-radius: 50%; 
                                        background: ${ocupacaoPercent > 80 ? '#dc3545' : ocupacaoPercent > 60 ? '#ffc107' : '#28a745'};"></div>
                            <span>Ocupado: ${trip.booked} lugares</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; border-radius: 50%; background: #e9ecef;"></div>
                            <span>Livre: ${lugaresLivres} lugares</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

        function setupReservationButton(trip, lugaresLivres, ocupacaoPercent) {
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
                        window.location.href = 'login.html';
                    }
                    return;
                }

                // Adicionar ao carrinho
                if (window.GlobalManager.addToCart) {
                    const success = window.GlobalManager.addToCart(trip);
                    
                    if (success) {
                        // Atualizar botão
                        btn.textContent = '✓ Adicionado ao Carrinho';
                        btn.disabled = true;
                        btn.style.background = '#28a745';
                        
                        // Atualizar visualizações
                        updateVisualizations(trip, 1);
                    }
                } else {
                    alert('Erro: Não foi possível adicionar ao carrinho. Tente novamente.');
                }
            });
        }

        function updateVisualizations(trip, reservados = 1) {
            // Atualizar contadores
            const newBooked = trip.booked + reservados;
            const newLivres = trip.capacity - newBooked;
            const newOcupacao = Math.round((newBooked / trip.capacity) * 100);
            
            // Atualizar barra
            const bar = document.getElementById('ocupacao-bar');
            if (bar) {
                bar.style.width = `${newOcupacao}%`;
                bar.style.background = newOcupacao > 80 ? '#dc3545' : 
                                       newOcupacao > 60 ? '#ffc107' : '#28a745';
            }
            
            // Atualizar textos
            const ocupadoText = document.getElementById('ocupado-text');
            const livreText = document.getElementById('livre-text');
            if (ocupadoText) ocupadoText.textContent = `Ocupado: ${newBooked} lugares`;
            if (livreText) livreText.textContent = `Livre: ${newLivres} lugares`;
            
            // Atualizar gráfico
            if (window.occupancyChart) {
                window.occupancyChart.data.datasets[0].data = [newBooked, newLivres];
                window.occupancyChart.data.datasets[0].backgroundColor[0] = 
                    newOcupacao > 80 ? '#dc3545' : 
                    newOcupacao > 60 ? '#ffc107' : '#28a745';
                window.occupancyChart.update();
            }
        }

        // Iniciar quando a página carregar
        document.addEventListener('DOMContentLoaded', initPage);
    </script>
</body>
</html>