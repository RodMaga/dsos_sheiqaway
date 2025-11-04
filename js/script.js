document.addEventListener('DOMContentLoaded', () => {

    // 1. VARIÁVEIS GLOBAIS
    let allTrips = [];
    let allProviders = [];
    let currentResults = []; // NOVO: Guarda os resultados da última pesquisa
    let providerMap = {}; // NOVO: Guarda o mapa de providers

    // Elementos da DOM
    const searchForm = document.getElementById('search-form');
    const resultsContainer = document.getElementById('results-container');
    const loadingSpinner = document.getElementById('loading-spinner');
    const errorMessage = document.getElementById('error-message');
    
    // Elementos dos Filtros
    const sortSelect = document.getElementById('sort-by');
    const providerFilter = document.getElementById('filter-provider');
    const priceFilter = document.getElementById('filter-price');
    const priceValue = document.getElementById('price-value');

    // 2. CARREGAMENTO DOS DADOS (JSON)
    async function loadData() {
        try {
            const tripsResponse = await fetch('data/trips_com_lotacao.json');
            if (!tripsResponse.ok) throw new Error('Falha ao carregar trips_com_lotacao.json');
            allTrips = await tripsResponse.json();

            const providersResponse = await fetch('data/providers.json');
            if (!providersResponse.ok) throw new Error('Falha ao carregar providers.json');
            allProviders = await providersResponse.json();

            console.log("Dados carregados com sucesso!");
            
            // NOVO: Preenche o mapa de providers e o filtro
            populateProviderFilter();

        } catch (error) {
            console.error("Erro ao carregar dados:", error);
            resultsContainer.innerHTML = `<p class="error-message" style="display:block;">Erro fatal ao carregar os dados. Tente novamente mais tarde.</p>`;
        }
    }

    // NOVO: Função para preencher o filtro de companhias
    function populateProviderFilter() {
        allProviders.forEach(provider => {
            // Guarda no mapa para uso futuro
            providerMap[provider.id] = provider.name;
            
            // Adiciona ao <select>
            const option = document.createElement('option');
            option.value = provider.id;
            option.textContent = provider.name;
            providerFilter.appendChild(option);
        });
    }

    // 3. LÓGICA DE PESQUISA E VALIDAÇÃO
    
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault(); 
        console.log("EVENTO 'SUBMIT' DO FORMULÁRIO DETETADO!");

        if (!validateSearchForm()) {
            console.log("VALIDAÇÃO FALHOU. Pesquisa interrompida.");
            return; 
        }
        
        console.log("VALIDAÇÃO OK. A iniciar performSearch()...");
        performSearch();
    });

    // NOVO: Listeners para os filtros e ordenação
    sortSelect.addEventListener('change', applyFiltersAndSort);
    providerFilter.addEventListener('change', applyFiltersAndSort);
    priceFilter.addEventListener('input', () => { // 'input' atualiza em tempo real
        priceValue.textContent = `€${priceFilter.value}`;
        applyFiltersAndSort();
    });


    // Função de Validação
    function validateSearchForm() {
        const origin = document.getElementById('origin').value;
        const destination = document.getElementById('destination').value;
        const departureDate = document.getElementById('departure-date').value;

        errorMessage.textContent = '';
        errorMessage.style.display = 'none';

        if (origin.trim() === '' || destination.trim() === '' || departureDate === '') {
            errorMessage.textContent = 'Por favor, preencha a Origem, Destino e Data de Partida.';
            errorMessage.style.display = 'block';
            return false;
        }
        
        if (origin.trim().toLowerCase() === destination.trim().toLowerCase()) {
            errorMessage.textContent = 'A Origem e o Destino não podem ser iguais.';
            errorMessage.style.display = 'block';
            return false;
        }
        
        return true; 
    }

    // Função que executa a pesquisa (Passo 1: Encontrar Viagens)
    async function performSearch() {
        loadingSpinner.classList.remove('hidden');
        resultsContainer.innerHTML = ''; 
        errorMessage.style.display = 'none'; 
        
        await new Promise(resolve => setTimeout(resolve, 500));

        // Obtém valores
        const origin = document.getElementById('origin').value.toLowerCase().trim();
        const destination = document.getElementById('destination').value.toLowerCase().trim();
        const departureDate = document.getElementById('departure-date').value;

        console.log("--- INICIANDO FILTRO ---");
        console.log(`A PESQUISAR POR: Origem='${origin}', Destino='${destination}', Data='${departureDate}'`);

        // Lógica de Filtro
        currentResults = allTrips.filter(trip => { // Guarda os resultados
            const tripOrigin = trip.from.toLowerCase();
            const tripDestination = trip.to.toLowerCase();
            const tripDate = trip.date;

            const originMatch = tripOrigin === origin;
            const destinationMatch = tripDestination === destination;
            const dateMatch = tripDate === departureDate;

            return originMatch && destinationMatch && dateMatch;
        });

        console.log(`--- FILTRO TERMINADO ---: ${currentResults.length} viagens encontradas.`);

        loadingSpinner.classList.add('hidden');

        // NOVO: Chama a função para aplicar filtros e ordenação
        applyFiltersAndSort();
    }

    // NOVO: (Passo 2: Filtrar e Ordenar os resultados encontrados)
    function applyFiltersAndSort() {
        let filteredTrips = [...currentResults]; // Copia os resultados da pesquisa
        
        // Obtém valores dos filtros
        const selectedProvider = providerFilter.value;
        const maxPrice = parseFloat(priceFilter.value);
        const sortCriteria = sortSelect.value;

        // 1. Aplicar Filtro de Companhia
        if (selectedProvider !== 'all') {
            filteredTrips = filteredTrips.filter(trip => trip.providerId === selectedProvider);
        }

        // 2. Aplicar Filtro de Preço
        filteredTrips = filteredTrips.filter(trip => trip.price.base <= maxPrice);

        // 3. Aplicar Ordenação
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

        // 4. Exibir os resultados finais
        displayResults(filteredTrips);
    }


    // 4. EXIBIÇÃO DOS RESULTADOS
    function displayResults(trips) {
        resultsContainer.innerHTML = ''; // Limpa sempre antes de exibir
        
        console.log(`A função displayResults() foi chamada com ${trips.length} viagens.`);

        if (trips.length === 0) {
            resultsContainer.innerHTML = '<p>Nenhuma viagem encontrada para os critérios selecionados.</p>';
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
                    <p class="price">€ ${trip.price.base.toFixed(2)}</p>
                </div>
            `;
            
            resultsContainer.innerHTML += cardHTML;
        });
    }


    // 5. INICIALIZAÇÃO
    loadData();

});