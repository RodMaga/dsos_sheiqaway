document.addEventListener('DOMContentLoaded', () => {

    document.getElementById('logo-button').addEventListener('click', () => {
        window.location.href = 'index.html';
    });

    let allTrips = [];
    let allProviders = [];
    let currentResults = [];
    let providerMap = {};
    let allLocations = [];
    let allLocationsLower = new Set();

    const searchForm = document.getElementById('search-form');
    const resultsContainer = document.getElementById('results-container');
    const loadingSpinner = document.getElementById('loading-spinner');
    const errorMessage = document.getElementById('error-message');
    
    const sortSelect = document.getElementById('sort-by');
    const providerFilter = document.getElementById('filter-provider');
    const priceFilter = document.getElementById('filter-price');
    const priceValue = document.getElementById('price-value');
    const cartLink = document.getElementById('cart-link');

    const originInput = document.getElementById('origin');
    const destinationInput = document.getElementById('destination');
    const dateInput = document.getElementById('departure-date');
    const originSuggestions = document.getElementById('origin-suggestions');
    const destinationSuggestions = document.getElementById('destination-suggestions');

    async function loadData() {
        try {
            const tripsResponse = await fetch('data/trips_com_lotacao.json');
            if (!tripsResponse.ok) throw new Error('Falha ao carregar trips_com_lotacao.json');
            allTrips = await tripsResponse.json();

            const providersResponse = await fetch('data/providers.json');
            if (!providersResponse.ok) throw new Error('Falha ao carregar providers.json');
            allProviders = await providersResponse.json();

            populateProviderFilter();
            populateLocationSuggestions();

        } catch (error) {
            console.error("Erro ao carregar dados:", error);
            resultsContainer.innerHTML = `<p class="error-message" style="display:block;">Erro fatal ao carregar os dados. Tente novamente mais tarde.</p>`;
        }
    }

    function populateProviderFilter() {
        allProviders.forEach(provider => {
            providerMap[provider.id] = provider.name;
            
            const option = document.createElement('option');
            option.value = provider.id;
            option.textContent = provider.name;
            providerFilter.appendChild(option);
        });
    }

    function populateLocationSuggestions() {
        const locations = new Set();
        allTrips.forEach(trip => {
            locations.add(trip.from);
            locations.add(trip.to);
        });
        
        allLocations = [...locations].sort();
        allLocationsLower = new Set(allLocations.map(loc => loc.toLowerCase()));
    }
    
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        
        originInput.classList.remove('input-error-highlight');
        destinationInput.classList.remove('input-error-highlight');
        dateInput.classList.remove('input-error-highlight');
        errorMessage.style.display = 'none';

        const origin = originInput.value.trim();
        const dest = destinationInput.value.trim();
        const date = dateInput.value;

        if (origin === '') {
            errorMessage.textContent = 'Por favor, preencha a Origem.';
            errorMessage.style.display = 'block';
            originInput.classList.add('input-error-highlight');
            originInput.focus();
            return;
        }
        
        if (dest === '') {
            errorMessage.textContent = 'Por favor, preencha o Destino.';
            errorMessage.style.display = 'block';
            destinationInput.classList.add('input-error-highlight');
            destinationInput.focus();
            return;
        }

        if (date === '') {
            errorMessage.textContent = 'Por favor, selecione uma Data de Partida.';
            errorMessage.style.display = 'block';
            dateInput.classList.add('input-error-highlight');
            dateInput.focus();
            return;
        }
        
        document.activeElement.blur(); 
    });

    sortSelect.addEventListener('change', applyFiltersAndSort);
    providerFilter.addEventListener('change', applyFiltersAndSort);
    priceFilter.addEventListener('input', () => {
        priceValue.textContent = `€${priceFilter.value}`;
        applyFiltersAndSort();
    });

    resultsContainer.addEventListener('click', (event) => {
        if (event.target.classList.contains('buy-button')) {
            const button = event.target;
            const tripId = button.dataset.tripId;
            handlePurchase(tripId, button);
        }
    });

    originInput.addEventListener('input', () => {
        showSuggestions(originInput, originSuggestions);
        originInput.classList.remove('input-error-highlight');
    });
    destinationInput.addEventListener('input', () => {
        showSuggestions(destinationInput, destinationSuggestions);
        destinationInput.classList.remove('input-error-highlight');
    });
    
    originSuggestions.addEventListener('click', (e) => selectSuggestion(e, originInput, originSuggestions));
    destinationSuggestions.addEventListener('click', (e) => selectSuggestion(e, destinationInput, destinationSuggestions));

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.form-group-wrapper')) {
            originSuggestions.innerHTML = '';
            destinationSuggestions.innerHTML = '';
        }
    });

    originInput.addEventListener('change', runLiveSearch);
    destinationInput.addEventListener('change', runLiveSearch);
    dateInput.addEventListener('change', () => {
        runLiveSearch();
        dateInput.classList.remove('input-error-highlight');
    });

    originInput.addEventListener('keydown', (e) => handleInputKeydown(e, originInput, originSuggestions));
    destinationInput.addEventListener('keydown', (e) => handleInputKeydown(e, destinationInput, destinationSuggestions));
    
    originInput.addEventListener('blur', () => handleInputBlur(originInput, originSuggestions));
    destinationInput.addEventListener('blur', () => handleInputBlur(destinationInput, destinationSuggestions));

    function handleInputKeydown(event, input, suggestionsContainer) {
        if (event.key === 'Enter') {
            event.preventDefault();
            if (suggestionsContainer.children.length > 0) {
                const firstSuggestion = suggestionsContainer.firstChild.textContent;
                input.value = firstSuggestion;
                suggestionsContainer.innerHTML = '';
                runLiveSearch();
                input.blur();
            }
        }
    }

    function handleInputBlur(input, suggestionsContainer) {
        setTimeout(() => {
            const currentValue = input.value.trim();
            
            if (currentValue === '') {
                suggestionsContainer.innerHTML = '';
                runLiveSearch();
                return;
            }

            if (!allLocationsLower.has(currentValue.toLowerCase())) {
                const firstSuggestion = suggestionsContainer.firstChild?.textContent;
                if (firstSuggestion) {
                    input.value = firstSuggestion;
                } else {
                    input.value = '';
                }
                runLiveSearch();
            }
            
            suggestionsContainer.innerHTML = '';
        }, 200);
    }

    function showSuggestions(input, suggestionsContainer) {
        const query = input.value.toLowerCase().trim();
        suggestionsContainer.innerHTML = '';

        if (query.length === 0) {
            return;
        }

        const filteredLocations = allLocations.filter(location => 
            location.toLowerCase().startsWith(query)
        );

        filteredLocations.forEach(location => {
            const item = document.createElement('div');
            item.classList.add('suggestion-item');
            item.textContent = location;
            suggestionsContainer.appendChild(item);
        });
    }

    function selectSuggestion(event, input, suggestionsContainer) {
        if (event.target.classList.contains('suggestion-item')) {
            input.value = event.target.textContent;
            suggestionsContainer.innerHTML = '';
            runLiveSearch();
        }
    }

    function runLiveSearch() {
        loadingSpinner.classList.remove('hidden');
        
        const originQuery = originInput.value.toLowerCase().trim();
        const destQuery = destinationInput.value.toLowerCase().trim();
        const dateQuery = dateInput.value;

        errorMessage.textContent = '';
        errorMessage.style.display = 'none';
        if (originQuery.length > 0 && originQuery === destQuery) {
            errorMessage.textContent = 'A Origem e o Destino não podem ser iguais.';
            errorMessage.style.display = 'block';
        }

        originSuggestions.innerHTML = '';
        destinationSuggestions.innerHTML = '';
        
        currentResults = allTrips.filter(trip => {
            const originMatch = (originQuery.length === 0) 
                ? true 
                : trip.from.toLowerCase() === originQuery;
            
            const destMatch = (destQuery.length === 0) 
                ? true 
                : trip.to.toLowerCase() === destQuery;

            const dateMatch = (dateQuery.length === 0) 
                ? true 
                : trip.date === dateQuery;
            
            return originMatch && destMatch && dateMatch;
        });

        applyFiltersAndSort();
        loadingSpinner.classList.add('hidden');
    }

    function applyFiltersAndSort() {
        let filteredTrips = [...currentResults];
        
        const selectedProvider = providerFilter.value;
        const maxPrice = parseFloat(priceFilter.value);
        const sortCriteria = sortSelect.value;

        if (selectedProvider !== 'all') {
            filteredTrips = filteredTrips.filter(trip => trip.providerId === selectedProvider);
        }

        filteredTrips = filteredTrips.filter(trip => trip.price.base <= maxPrice);

        filteredTrips.sort((a, b) => {
            if (sortCriteria === 'price-asc') {
                return a.price.base - b.price.base;
            } else if (sortCriteria === 'price-desc') {
                return b.price.base - a.price.base;
            } else if (sortCriteria === 'duration') {
                return a.durationMin - b.durationMin;
            }
            return 0;
        });

        displayResults(filteredTrips);
    }

    function displayResults(trips) {
        resultsContainer.innerHTML = ''; 

        if (trips.length === 0) {
            if (originInput.value === '' && destinationInput.value === '' && dateInput.value === '') {
                 resultsContainer.innerHTML = '<p>Por favor, efetue uma pesquisa para ver os resultados.</p>';
            } else {
                 resultsContainer.innerHTML = '<p>Nenhuma viagem encontrada para os critérios selecionados.</p>';
            }
            return;
        }

        trips.forEach(trip => {
            const providerName = providerMap[trip.providerId] || 'Companhia Desconhecida';
            const durationHours = Math.floor(trip.durationMin / 60);
            const durationMinutes = trip.durationMin % 60;

            const cardHTML = `
                <div class="trip-card">
                    <h4>${trip.from} ➔ ${trip.to}</h4>
                    <p class="provider">Operado por: ${providerName}</p>
                    <p>
                        <strong>Partida:</strong> ${trip.date} às ${trip.depart}h | 
                        <strong>Chegada:</strong> ${trip.arrive}h
                    </p>
                    <p><strong>Duração:</strong> ${durationHours}h ${durationMinutes}min</p>
                    
                    <div class="trip-footer">
                        <span class="price">€ ${trip.price.base.toFixed(2)}</span>
                        <button class="buy-button" data-trip-id="${trip.id}">Comprar</button>
                    </div>
                </div>
            `;
            
            resultsContainer.innerHTML += cardHTML;
        });
    }

    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('shoppingCart')) || [];
        cartLink.textContent = `Carrinho (${cart.length})`;
    }

    function handlePurchase(tripId, buttonElement) {
        const tripToBuy = allTrips.find(trip => trip.id === tripId);
        if (!tripToBuy) return;

        let cart = JSON.parse(localStorage.getItem('shoppingCart')) || [];

        if (cart.some(trip => trip.id === tripId)) {
            alert('Esta viagem já está no seu carrinho.');
            return;
        }

        if (tripToBuy.booked >= tripToBuy.capacity) {
            alert('Lamentamos, essa viagem já não tem lugares disponíveis.');
            buttonElement.textContent = 'Esgotado';
            buttonElement.disabled = true;
            return;
        }

        cart.push(tripToBuy);
        localStorage.setItem('shoppingCart', JSON.stringify(cart));
        
        updateCartCount(); 

        buttonElement.textContent = 'Adicionado!';
        buttonElement.disabled = true;
    }

    loadData();
    updateCartCount();

});