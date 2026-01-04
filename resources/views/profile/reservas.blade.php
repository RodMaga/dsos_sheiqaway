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
    <title>sheiqaway - Minhas Reservas</title>
    @vite(['resources/css/style.css', 'resources/js/global.js'])
    <style>
        .reservas-container {
            margin-top: 20px;
        }
        
        .reserva-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.2s ease;
        }
        
        .reserva-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .reserva-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .reserva-code {
            background: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 600;
        }
        
        .reserva-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .status-confirmado {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pendente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-cancelado {
            background: #f8d7da;
            color: #721c24;
        }
        
        .reserva-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            padding: 10px;
            background: var(--bg-body);
            border-radius: 6px;
        }
        
        .detail-label {
            font-size: 0.85em;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-weight: 600;
        }
        
        .no-reservas {
            text-align: center;
            padding: 50px 20px;
        }
        
        .no-reservas-icon {
            font-size: 4em;
            margin-bottom: 20px;
            color: var(--border-color);
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    @include('navbar')
    
    <main>
        <section class="content-card">
            <h2>Minhas Reservas</h2>
            <p style="color: var(--text-secondary); margin-bottom: 25px;">
                Aqui pode visualizar todas as suas reservas e bilhetes.
            </p>
            
            <!-- Filtros -->
            <div class="filters">
                <button class="filter-btn active" data-filter="all">Todas</button>
                <button class="filter-btn" data-filter="confirmado">Confirmadas</button>
                <button class="filter-btn" data-filter="pendente">Pendentes</button>
                <button class="filter-btn" data-filter="cancelado">Canceladas</button>
                <button class="filter-btn" data-filter="recente">Recentes</button>
            </div>
            
            <div id="reservas-container" class="reservas-container">
                <!-- As reservas serão carregadas via JavaScript -->
                <div id="loading-reservas" style="text-align: center; padding: 40px;">
                    <div class="loading-spinner" style="width: 40px; height: 40px; margin: 0 auto 15px;"></div>
                    <p style="color: var(--text-secondary);">A carregar as suas reservas...</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>
    
    <script>
        // Carregar histórico do localStorage (simulação - em produção viria da API)
        function loadReservas() {
            const history = JSON.parse(localStorage.getItem('purchaseHistory')) || [];
            
            // Se não tiver reservas, mostrar mensagem
            if (history.length === 0) {
                document.getElementById('reservas-container').innerHTML = `
                    <div class="no-reservas">
                        <div class="no-reservas-icon">🎫</div>
                        <h3 style="color: var(--text-secondary); margin-bottom: 15px;">Ainda não tem reservas</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 25px; max-width: 500px; margin: 0 auto 30px;">
                            Assim que fizer uma reserva através do carrinho, ela aparecerá aqui.
                            Pode consultar os seus bilhetes e detalhes das viagens a qualquer momento.
                        </p>
                        <a href="{{ route('home') }}" class="search-button" style="display: inline-block; padding: 12px 24px;">
                            Explorar Viagens
                        </a>
                    </div>
                `;
                return;
            }
            
            // Agrupar por código de bilhete
            const reservasPorTicket = {};
            history.forEach(reserva => {
                if (!reservasPorTicket[reserva.ticketCode]) {
                    reservasPorTicket[reserva.ticketCode] = {
                        ticketCode: reserva.ticketCode,
                        purchaseDate: reserva.purchaseDate,
                        purchaseTime: reserva.purchaseTime,
                        status: reserva.status,
                        passengerName: reserva.passengerName,
                        bookingReference: reserva.bookingReference,
                        viagens: []
                    };
                }
                reservasPorTicket[reserva.ticketCode].viagens.push(reserva);
            });
            
            // Converter para array e ordenar por data (mais recente primeiro)
            const reservas = Object.values(reservasPorTicket).sort((a, b) => {
                return new Date(b.purchaseDate + ' ' + b.purchaseTime) - new Date(a.purchaseDate + ' ' + a.purchaseTime);
            });
            
            // Renderizar reservas
            renderReservas(reservas);
        }
        
        function renderReservas(reservas) {
            const container = document.getElementById('reservas-container');
            
            // Calcular totais
            const totalReservas = reservas.length;
            const totalViagens = reservas.reduce((sum, reserva) => sum + reserva.viagens.length, 0);
            const totalGasto = reservas.reduce((sum, reserva) => {
                return sum + reserva.viagens.reduce((sumViagem, viagem) => sumViagem + viagem.price.base, 0);
            }, 0);
            
            container.innerHTML = `
                <div style="background: var(--bg-body); padding: 15px; border-radius: 8px; margin-bottom: 25px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="text-align: center;">
                        <div style="font-size: 1.8em; font-weight: 700; color: var(--primary-color);">${totalReservas}</div>
                        <div style="font-size: 0.9em; color: var(--text-secondary);">Reservas</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.8em; font-weight: 700; color: var(--accent-color);">${totalViagens}</div>
                        <div style="font-size: 0.9em; color: var(--text-secondary);">Viagens</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.8em; font-weight: 700; color: #28a745;">€ ${totalGasto.toFixed(2)}</div>
                        <div style="font-size: 0.9em; color: var(--text-secondary);">Total gasto</div>
                    </div>
                </div>
                
                <div id="reservas-list">
                    ${reservas.map((reserva, index) => `
                        <div class="reserva-card" data-status="${reserva.status}" data-date="${reserva.purchaseDate}">
                            <div class="reserva-header">
                                <div>
                                    <h4 style="margin: 0 0 5px 0;">Reserva #${index + 1}</h4>
                                    <div style="font-size: 0.9em; color: var(--text-secondary);">
                                        ${reserva.purchaseDate} às ${reserva.purchaseTime}
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div class="reserva-code">${reserva.ticketCode}</div>
                                    <div class="reserva-status status-${reserva.status}" style="margin-top: 8px;">
                                        ${reserva.status.charAt(0).toUpperCase() + reserva.status.slice(1)}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="reserva-details">
                                <div class="detail-item">
                                    <div class="detail-label">Passageiro</div>
                                    <div class="detail-value">${reserva.passengerName}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Referência</div>
                                    <div class="detail-value">${reserva.bookingReference}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Número de Viagens</div>
                                    <div class="detail-value">${reserva.viagens.length}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Valor Total</div>
                                    <div class="detail-value" style="color: var(--accent-color);">
                                        € ${reserva.viagens.reduce((sum, v) => sum + v.price.base, 0).toFixed(2)}
                                    </div>
                                </div>
                            </div>
                            
                            <h5 style="margin: 15px 0 10px 0; color: var(--text-primary);">Viagens Incluídas:</h5>
                            <div style="background: var(--bg-body); border-radius: 6px; padding: 15px;">
                                ${reserva.viagens.map(viagem => `
                                    <div style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <strong>${viagem.from} → ${viagem.to}</strong>
                                                <div style="font-size: 0.9em; color: var(--text-secondary);">
                                                    ${viagem.date} • ${viagem.depart} • ${Math.floor(viagem.durationMin/60)}h ${viagem.durationMin%60}min
                                                </div>
                                            </div>
                                            <div style="text-align: right;">
                                                <div style="font-weight: 600; color: var(--accent-color);">
                                                    € ${viagem.price.base.toFixed(2)}
                                                </div>
                                                <div style="font-size: 0.85em; color: var(--text-secondary);">
                                                    ${viagem.providerId}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                            
                            <div style="display: flex; gap: 10px; margin-top: 20px;">
                                <button class="secondary-button" style="padding: 8px 15px; font-size: 0.9em;">
                                    📄 Ver Bilhete
                                </button>
                                <button class="secondary-button" style="padding: 8px 15px; font-size: 0.9em;">
                                    📧 Reenviar E-mail
                                </button>
                                ${reserva.status === 'confirmado' ? `
                                    <button class="secondary-button" style="padding: 8px 15px; font-size: 0.9em; color: #dc3545; border-color: #dc3545;">
                                        ❌ Cancelar
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
            
            // Configurar filtros
            setupFilters();
        }
        
        function setupFilters() {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const reservaCards = document.querySelectorAll('.reserva-card');
            
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Atualizar botão ativo
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const filter = this.dataset.filter;
                    
                    // Aplicar filtro
                    reservaCards.forEach(card => {
                        let show = true;
                        
                        switch(filter) {
                            case 'confirmado':
                                show = card.dataset.status === 'confirmado';
                                break;
                            case 'pendente':
                                show = card.dataset.status === 'pendente';
                                break;
                            case 'cancelado':
                                show = card.dataset.status === 'cancelado';
                                break;
                            case 'recente':
                                // Mostrar apenas dos últimos 30 dias
                                const reservaDate = new Date(card.dataset.date);
                                const thirtyDaysAgo = new Date();
                                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                                show = reservaDate >= thirtyDaysAgo;
                                break;
                            default: // 'all'
                                show = true;
                        }
                        
                        card.style.display = show ? 'block' : 'none';
                    });
                });
            });
        }
        
        // Carregar reservas quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(loadReservas, 500); // Pequeno delay para efeito visual
        });
    </script>
</body>
</html>
