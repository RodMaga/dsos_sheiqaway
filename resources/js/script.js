// script.js - PÁGINA DE PESQUISA (index.html) - MOSTRA TODAS INICIALMENTE
class SearchManager {
    constructor() {
        this.allTrips = [];
        this.allProviders = [];
        this.providerMap = {};
        this.allLocations = [];
        this.currentResults = [];
        this.init();
    }

    async init() {
        await this.loadData();
        this.setupSearchForm();
        this.setupFilters();
        this.setupEventListeners();
        this.showWelcomeMessage();
        
        // MOSTRAR TODAS AS VIAGENS INICIALMENTE
        this.showAllTripsInitially();
    }

    async loadData() {
        try {
            // Carregar viagens
            const tripsResponse = await fetch('/api/trips');
            if (!tripsResponse.ok) throw new Error('Falha ao carregar viagens');
            this.allTrips = await tripsResponse.json();

            // Carregar companhias
            const providersResponse = await fetch('/api/providers');
            if (providersResponse.ok) {
                this.allProviders = await providersResponse.json();
                this.allProviders.forEach(p => {
                    this.providerMap[p.id] = p.name;
                });
            }

            // Extrair localizações únicas
            const locations = new Set();
            this.allTrips.forEach(trip => {
                locations.add(trip.from);
                locations.add(trip.to);
            });
            this.allLocations = [...locations].sort();

            console.log(`Carregados ${this.allTrips.length} viagens e ${this.allLocations.length} localizações`);
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
            this.showError('Não foi possível carregar os dados. Por favor, recarregue a página.');
        }
    }

    showAllTripsInitially() {
        // Mostrar todas as viagens logo no início
        this.currentResults = [...this.allTrips];
        this.applyFiltersAndSort();
        
        // Mostrar mensagem informativa
        setTimeout(() => {
            if (window.GlobalManager) {
                window.GlobalManager.showToast(
                    `Mostrando todas as ${this.allTrips.length} viagens disponíveis. Filtre conforme necessário.`,
                    'info'
                );
            }
        }, 1000);
    }

    setupSearchForm() {
        this.searchForm = document.getElementById('search-form');
        this.resultsContainer = document.getElementById('results-container');
        this.loadingSpinner = document.getElementById('loading-spinner');
        this.errorMessage = document.getElementById('error-message');
        
        if (!this.searchForm) return;

        // Configurar autocomplete
        this.setupAutocomplete('origin');
        this.setupAutocomplete('destination');
        
        // Definir data mínima como hoje
        const dateInput = document.getElementById('departure-date');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;
            // Não definir valor por padrão para não filtrar por data inicialmente
        }
    }

    setupAutocomplete(inputId) {
        const input = document.getElementById(inputId);
        const suggestionsContainer = document.getElementById(`${inputId}-suggestions`);
        
        if (!input || !suggestionsContainer) return;

        input.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            suggestionsContainer.innerHTML = '';
            
            if (!query) {
                // Se o campo estiver vazio, pesquisar todas as viagens
                this.performSearch();
                return;
            }
            
            // Filtrar localizações que contêm a query (não só que começam)
            const matches = this.allLocations
                .filter(loc => loc.toLowerCase().includes(query))
                .slice(0, 8);
            
            if (matches.length === 0) {
                const noResults = document.createElement('div');
                noResults.className = 'suggestion-item';
                noResults.textContent = 'Nenhuma localização encontrada';
                noResults.style.color = '#6c757d';
                noResults.style.fontStyle = 'italic';
                suggestionsContainer.appendChild(noResults);
                return;
            }
            
            matches.forEach(location => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.textContent = location;
                item.onclick = () => {
                    input.value = location;
                    suggestionsContainer.innerHTML = '';
                    this.performSearch();
                };
                suggestionsContainer.appendChild(item);
            });
            
            // Fazer pesquisa em tempo real enquanto digita
            this.performSearch();
        });

        // Fechar sugestões ao clicar fora
        document.addEventListener('click', (e) => {
            if (!e.target.closest(`#${inputId}-suggestions`) && 
                !e.target.closest(`#${inputId}`)) {
                suggestionsContainer.innerHTML = '';
            }
        });

        // Navegação com teclado
        input.addEventListener('keydown', (e) => {
            const suggestions = suggestionsContainer.querySelectorAll('.suggestion-item');
            if (suggestions.length === 0) return;
            
            const active = suggestionsContainer.querySelector('.suggestion-active');
            let index = Array.from(suggestions).indexOf(active);
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    index = (index + 1) % suggestions.length;
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    index = index <= 0 ? suggestions.length - 1 : index - 1;
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (active) {
                        input.value = active.textContent;
                        suggestionsContainer.innerHTML = '';
                        this.performSearch();
                    }
                    return;
                case 'Escape':
                    suggestionsContainer.innerHTML = '';
                    return;
            }
            
            suggestions.forEach(s => s.classList.remove('suggestion-active'));
            if (index >= 0) {
                suggestions[index].classList.add('suggestion-active');
            }
        });

        // Limpar campo e mostrar todas as viagens
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && input.value) {
                input.value = '';
                this.performSearch();
            }
        });
    }

    setupFilters() {
        const providerFilter = document.getElementById('filter-provider');
        const priceFilter = document.getElementById('filter-price');
        const priceValue = document.getElementById('price-value');
        
        // Popular filtro de companhias
        if (providerFilter) {
            // Adicionar opção "Todas"
            const allOption = document.createElement('option');
            allOption.value = 'all';
            allOption.textContent = 'Todas as companhias';
            providerFilter.appendChild(allOption);
            
            // Adicionar companhias
            Object.entries(this.providerMap).forEach(([id, name]) => {
                const option = document.createElement('option');
                option.value = id;
                option.textContent = name;
                providerFilter.appendChild(option);
            });
            
            providerFilter.addEventListener('change', () => this.applyFiltersAndSort());
        }
        
        // Configurar slider de preço
        if (priceFilter && priceValue) {
            // Encontrar preço máximo das viagens
            const maxPrice = this.allTrips.reduce((max, trip) => 
                Math.max(max, trip.price.base), 0);
            priceFilter.max = Math.ceil(maxPrice / 50) * 50; // Arredondar para múltiplo de 50
            priceFilter.value = maxPrice;
            priceValue.textContent = `€${maxPrice}`;
            
            priceFilter.addEventListener('input', () => {
                priceValue.textContent = `€${priceFilter.value}`;
                this.applyFiltersAndSort();
            });
        }
        
        // Configurar ordenação
        const sortSelect = document.getElementById('sort-by');
        if (sortSelect) {
            sortSelect.addEventListener('change', () => this.applyFiltersAndSort());
        }
        
        // Botão para limpar todos os filtros
        this.addClearFiltersButton();
    }

    addClearFiltersButton() {
        const filtersContainer = document.querySelector('.filters');
        if (!filtersContainer) return;
        
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'secondary-button';
        clearButton.textContent = 'Limpar Filtros';
        clearButton.style.marginLeft = 'auto';
        clearButton.style.marginTop = '25px';
        
        clearButton.addEventListener('click', () => {
            // Limpar campos de pesquisa
            document.getElementById('origin').value = '';
            document.getElementById('destination').value = '';
            document.getElementById('departure-date').value = '';
            
            // Resetar filtros
            document.getElementById('filter-provider').value = 'all';
            document.getElementById('filter-price').value = document.getElementById('filter-price').max;
            document.getElementById('price-value').textContent = `€${document.getElementById('filter-price').max}`;
            document.getElementById('sort-by').value = 'price-asc';
            
            // Limpar mensagens de erro
            this.hideError();
            
            // Mostrar todas as viagens
            this.currentResults = [...this.allTrips];
            this.applyFiltersAndSort();
            
            // Mostrar feedback
            if (window.GlobalManager) {
                window.GlobalManager.showToast('Filtros limpos. Mostrando todas as viagens.', 'info');
            }
        });
        
        filtersContainer.appendChild(clearButton);
    }

    setupEventListeners() {
        if (!this.searchForm) return;
        
        // Pesquisa ao submeter formulário
        this.searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.validateAndSearch();
        });
        
        // Pesquisa em tempo real enquanto digita
        ['origin', 'destination'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                // Pesquisar após pequeno delay para não sobrecarregar
                let timeout;
                input.addEventListener('input', () => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        this.performSearch();
                    }, 300); // Delay de 300ms
                });
            }
        });
        
        // Pesquisa ao mudar data
        const dateInput = document.getElementById('departure-date');
        if (dateInput) {
            dateInput.addEventListener('change', () => {
                dateInput.classList.remove('input-error-highlight');
                this.performSearch();
            });
        }
        
        // Validar origem/destino iguais
        ['origin', 'destination'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('change', () => this.validateSameLocation());
            }
        });
        
        // Adicionar botão de limpar em cada campo
        this.addClearFieldButtons();
    }

    addClearFieldButtons() {
        ['origin', 'destination'].forEach(id => {
            const input = document.getElementById(id);
            if (!input) return;
            
            const wrapper = input.parentElement;
            if (!wrapper) return;
            
            // Criar botão de limpar
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.innerHTML = '×';
            clearBtn.className = 'clear-field-btn';
            clearBtn.style.cssText = `
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                font-size: 1.5em;
                color: var(--text-secondary);
                cursor: pointer;
                display: none;
                padding: 0;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                line-height: 1;
            `;
            
            clearBtn.addEventListener('click', () => {
                input.value = '';
                input.focus();
                this.performSearch();
                clearBtn.style.display = 'none';
            });
            
            wrapper.style.position = 'relative';
            wrapper.appendChild(clearBtn);
            
            // Mostrar/ocultar botão conforme o conteúdo
            input.addEventListener('input', () => {
                clearBtn.style.display = input.value ? 'block' : 'none';
            });
        });
    }

    validateSameLocation() {
        const origin = document.getElementById('origin')?.value.trim();
        const destination = document.getElementById('destination')?.value.trim();
        
        if (origin && destination && origin.toLowerCase() === destination.toLowerCase()) {
            this.showError('A origem e o destino não podem ser iguais.');
            return false;
        }
        
        this.hideError();
        return true;
    }

    validateAndSearch() {
        if (!this.validateSameLocation()) return;
        this.performSearch();
    }

    performSearch() {
        // Obter valores da pesquisa
        const origin = document.getElementById('origin')?.value.trim().toLowerCase() || '';
        const destination = document.getElementById('destination')?.value.trim().toLowerCase() || '';
        const date = document.getElementById('departure-date')?.value || '';
        
        // Se todos os campos estiverem vazios, mostrar todas as viagens
        if (!origin && !destination && !date) {
            this.currentResults = [...this.allTrips];
        } else {
            // Filtrar viagens
            this.currentResults = this.allTrips.filter(trip => {
                const originMatch = !origin || trip.from.toLowerCase().includes(origin);
                const destMatch = !destination || trip.to.toLowerCase().includes(destination);
                const dateMatch = !date || trip.date === date;
                
                return originMatch && destMatch && dateMatch;
            });
        }
        
        // Aplicar filtros e ordenação
        this.applyFiltersAndSort();
        
        // Mostrar feedback se não houver resultados
        if (this.currentResults.length === 0 && (origin || destination || date)) {
            this.showNoResultsMessage(origin, destination, date);
        }
    }

    applyFiltersAndSort() {
        let filteredTrips = [...this.currentResults];
        
        // Aplicar filtros
        const providerFilter = document.getElementById('filter-provider');
        const priceFilter = document.getElementById('filter-price');
        const sortSelect = document.getElementById('sort-by');
        
        // Filtrar por companhia
        if (providerFilter && providerFilter.value !== 'all') {
            filteredTrips = filteredTrips.filter(trip => 
                trip.providerId === providerFilter.value
            );
        }
        
        // Filtrar por preço máximo
        if (priceFilter) {
            const maxPrice = parseFloat(priceFilter.value);
            filteredTrips = filteredTrips.filter(trip => 
                trip.price.base <= maxPrice
            );
        }
        
        // Ordenar resultados
        if (sortSelect) {
            filteredTrips.sort((a, b) => {
                switch(sortSelect.value) {
                    case 'price-asc':
                        return a.price.base - b.price.base;
                    case 'price-desc':
                        return b.price.base - a.price.base;
                    case 'duration':
                        return a.durationMin - b.durationMin;
                    default:
                        return 0;
                }
            });
        }
        
        this.displayResults(filteredTrips);
    }

    displayResults(trips) {
        if (!this.resultsContainer) return;
        
        this.resultsContainer.innerHTML = '';
        
        if (trips.length === 0) {
            this.showNoResultsMessage();
            return;
        }
        
        // Mostrar contador de resultados
        const resultsCount = document.createElement('div');
        resultsCount.className = 'results-count';
        resultsCount.style.cssText = `
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: var(--bg-body);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        `;
        
        const countText = document.createElement('span');
        countText.style.cssText = 'color: var(--text-secondary); font-size: 0.95em;';
        
        const hasFilters = document.getElementById('origin')?.value || 
                          document.getElementById('destination')?.value || 
                          document.getElementById('departure-date')?.value ||
                          document.getElementById('filter-provider')?.value !== 'all' ||
                          document.getElementById('filter-price')?.value < document.getElementById('filter-price')?.max;
        
        if (hasFilters) {
            countText.textContent = `Encontradas ${trips.length} viagem${trips.length !== 1 ? 'ens' : ''} (de ${this.allTrips.length})`;
        } else {
            countText.textContent = `Mostrando todas as ${trips.length} viagens disponíveis`;
        }
        
        const sortInfo = document.createElement('span');
        sortInfo.style.cssText = 'color: var(--text-secondary); font-size: 0.85em;';
        
        const sortSelect = document.getElementById('sort-by');
        if (sortSelect) {
            const sortText = {
                'price-asc': 'Ordenado por: Preço (mais baixo primeiro)',
                'price-desc': 'Ordenado por: Preço (mais alto primeiro)',
                'duration': 'Ordenado por: Duração'
            }[sortSelect.value] || '';
            sortInfo.textContent = sortText;
        }
        
        resultsCount.appendChild(countText);
        resultsCount.appendChild(sortInfo);
        this.resultsContainer.appendChild(resultsCount);
        
        // Renderizar cada viagem
        trips.forEach(trip => {
            const ocupacaoPercent = (trip.booked / trip.capacity) * 100;
            const lugaresLivres = trip.capacity - trip.booked;
            const providerName = this.providerMap[trip.providerId] || trip.providerId;
            
            const card = document.createElement('div');
            card.className = 'trip-card';
            card.style.animation = 'fadeIn 0.4s ease-out';
            
            // Calcular horário de chegada (simplificado)
            const [departHour, departMin] = trip.depart.split(':').map(Number);
            const arriveTime = new Date();
            arriveTime.setHours(departHour, departMin + trip.durationMin);
            const arriveStr = `${arriveTime.getHours().toString().padStart(2, '0')}:${arriveTime.getMinutes().toString().padStart(2, '0')}`;
            
            card.innerHTML = `
                <div class="trip-header">
                    <h4>${trip.from} ➔ ${trip.to}</h4>
                    <span class="badge ${ocupacaoPercent > 80 ? 'danger' : 
                                     ocupacaoPercent > 60 ? 'warning' : 'success'}">
                        ${lugaresLivres} lugar${lugaresLivres !== 1 ? 'es' : ''} livre${lugaresLivres !== 1 ? 's' : ''}
                    </span>
                </div>
                
                <p class="provider">
                    <strong>${providerName}</strong> • ${trip.date} 
                    • ${trip.depart} → ${arriveStr} 
                    • ${Math.floor(trip.durationMin / 60)}h ${trip.durationMin % 60}min
                </p>
                
                <div class="occupancy-wrapper">
                    <div style="display: flex; justify-content: space-between; font-size: 0.85em; margin-bottom: 5px;">
                        <span>Lotação: ${Math.round(ocupacaoPercent)}%</span>
                        <span>${lugaresLivres}/${trip.capacity} lugares livres</span>
                    </div>
                    <div class="occupancy-bar">
                        <div class="occupancy-fill" style="width: ${ocupacaoPercent}%; 
                             background: ${ocupacaoPercent > 80 ? '#dc3545' : 
                                        ocupacaoPercent > 60 ? '#ffc107' : '#28a745'}"></div>
                    </div>
                </div>
                
                <div class="trip-footer">
                    <div class="price-section">
                        <span class="price">€ ${trip.price.base.toFixed(2)}</span>
                        <small style="color: var(--text-secondary); display: block;">
                            ${trip.bagsIncluded} mala${trip.bagsIncluded !== 1 ? 's' : ''} incluída${trip.bagsIncluded !== 1 ? 's' : ''}
                        </small>
                    </div>
                    <div class="actions-section">
                        <button class="secondary-button" onclick="window.location.href='/detalhes/${trip.id}'">
                            Ver detalhes
                        </button>
                        <button class="buy-button" data-trip-id="${trip.id}" 
                                ${lugaresLivres === 0 ? 'disabled style="opacity: 0.5;"' : ''}>
                            ${lugaresLivres === 0 ? 'Esgotado' : 'Reservar agora'}
                        </button>
                    </div>
                </div>
            `;
            
            this.resultsContainer.appendChild(card);
        });
        
        // Adicionar event listeners aos botões de reserva
        this.setupBuyButtons(trips);
    }

    showNoResultsMessage(origin = '', destination = '', date = '') {
        if (!this.resultsContainer) return;
        
        let message = 'Nenhuma viagem encontrada com os critérios atuais.';
        let suggestions = [];
        
        if (origin || destination || date) {
            suggestions.push('Verifique a ortografia dos locais');
            suggestions.push('Tente uma data diferente');
            suggestions.push('Altere o preço máximo nos filtros');
        }
        
        this.resultsContainer.innerHTML = `
            <div style="text-align: center; padding: 50px 20px; animation: fadeIn 0.5s ease-out;">
                <div style="font-size: 3em; margin-bottom: 20px; color: var(--border-color);">🔍</div>
                <h3 style="color: var(--text-secondary); margin-bottom: 15px;">${message}</h3>
                
                ${suggestions.length > 0 ? `
                    <div style="max-width: 400px; margin: 0 auto 25px; text-align: left;">
                        <p style="margin-bottom: 10px; color: var(--text-secondary);">Sugestões:</p>
                        <ul style="color: var(--text-secondary); padding-left: 20px; margin: 0;">
                            ${suggestions.map(s => `<li style="margin-bottom: 5px;">${s}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
                
                <button onclick="searchManager.clearAllFilters()" class="search-button" style="margin-right: 10px;">
                    Limpar todos os filtros
                </button>
                <button onclick="window.location.href='/'" class="secondary-button">
                    Recarregar página
                </button>
            </div>
        `;
    }

    clearAllFilters() {
        // Limpar campos de pesquisa
        document.getElementById('origin').value = '';
        document.getElementById('destination').value = '';
        document.getElementById('departure-date').value = '';
        
        // Resetar filtros
        document.getElementById('filter-provider').value = 'all';
        const priceFilter = document.getElementById('filter-price');
        if (priceFilter) {
            priceFilter.value = priceFilter.max;
            document.getElementById('price-value').textContent = `€${priceFilter.max}`;
        }
        document.getElementById('sort-by').value = 'price-asc';
        
        // Mostrar todas as viagens
        this.currentResults = [...this.allTrips];
        this.applyFiltersAndSort();
        
        // Mostrar feedback
        if (window.GlobalManager) {
            window.GlobalManager.showToast('Filtros limpos. Mostrando todas as viagens.', 'info');
        }
    }

    setupBuyButtons(trips) {
        this.resultsContainer.querySelectorAll('.buy-button').forEach(button => {
            button.addEventListener('click', (e) => {
                const tripId = e.target.dataset.tripId;
                const trip = trips.find(t => t.id === tripId);
                
                if (!trip) return;
                
                if (trip.booked >= trip.capacity) {
                    window.GlobalManager?.showToast('Esta viagem está esgotada.', 'error');
                    return;
                }
                
                // Allow adding to cart without login
                // User will be prompted to login at checkout
                
                // Adicionar ao carrinho
                if (window.GlobalManager?.addToCart(trip)) {
                    e.target.textContent = '✓ Adicionado';
                    e.target.disabled = true;
                    e.target.style.background = '#28a745';
                    
                    // Atualizar visualização da lotação
                    const card = e.target.closest('.trip-card');
                    if (card) {
                        const badge = card.querySelector('.badge');
                        const lugaresLivres = trip.capacity - (trip.booked + 1);
                        if (badge) {
                            badge.textContent = `${lugaresLivres} lugar${lugaresLivres !== 1 ? 'es' : ''} livre${lugaresLivres !== 1 ? 's' : ''}`;
                            if (lugaresLivres === 0) {
                                badge.className = 'badge danger';
                                card.querySelector('.buy-button').textContent = 'Esgotado';
                            }
                        }
                    }
                }
            });
        });
    }

    showError(message) {
        if (this.errorMessage) {
            this.errorMessage.textContent = message;
            this.errorMessage.style.display = 'block';
        }
    }

    hideError() {
        if (this.errorMessage) {
            this.errorMessage.style.display = 'none';
        }
    }

    showWelcomeMessage() {
        const user = window.GlobalManager?.getCurrentUser();
        if (user && (window.location.pathname === '/' || window.location.pathname === '')) {
            setTimeout(() => {
                window.GlobalManager?.showToast(`Bem-vindo de volta, ${user.name}!`, 'success');
            }, 1000);
        }
    }
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', () => {
    window.searchManager = new SearchManager();
});

// Adicionar estilos CSS dinâmicos
const dynamicStyles = document.createElement('style');
dynamicStyles.textContent = `
    .occupancy-bar {
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 15px;
    }
    
    .occupancy-fill {
        height: 100%;
        transition: width 0.3s ease;
    }
    
    .suggestion-active {
        background-color: var(--primary-light) !important;
        color: var(--primary-color) !important;
        font-weight: 500;
    }
    
    .badge.warning {
        background: #fff3cd;
        color: #856404;
    }
    
    .clear-field-btn:hover {
        background: var(--border-color);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .trip-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .trip-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
`;
document.head.appendChild(dynamicStyles);