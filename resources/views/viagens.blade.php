<!DOCTYPE html>
<html lang="pt" class="viagens-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sheiqaway - Viagens</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/pages.css', 'resources/js/global.js', 'resources/js/viagens.js'])
</head>
<body class="viagens-page">


















































    @include('navbar')
    <main>
        <h1>Viagens Disponíveis</h1>
        
        <div class="filters">
            <h2>Filtros</h2>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1rem;">
                <div class="filter-group">
                    <label>Origem</label>
                    <div class="autocomplete">
                        <input type="text" id="filter-origem" placeholder="Ex: Porto, Lisboa..." autocomplete="off">
                    </div>
                </div>
                <div class="filter-group">
                    <label>Destino</label>
                    <div class="autocomplete">
                        <input type="text" id="filter-destino" placeholder="Ex: Paris, Londres..." autocomplete="off">
                    </div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
                <div class="filter-group">
                    <label>Companhia</label>
                    <select id="filter-companhia">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Data de Partida</label>
                    <input type="date" id="filter-data">
                </div>
                <div class="filter-group">
                    <label>Tipo de Viagem</label>
                    <select id="filter-tipo">
                        <option value="all">Todas</option>
                        <option value="direta">Voo Direto</option>
                        <option value="escala">Com Escala</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Preço Máximo</label>
                    <div class="price-range">
                        <input type="range" id="filter-preco" min="0" max="1000" value="1000" step="10">
                        <span class="price-value" id="price-display">1000€</span>
                    </div>
                </div>
            </div>
            <div style="margin-top: 1rem;">
                <button class="btn-clear" onclick="clearFilters()">Limpar Filtros</button>
            </div>
        </div>

        <div id="viagens-list" class="loading">A carregar viagens...</div>
    </main>

    <!-- Modal Quantidade -->
    <div id="modal-quantidade" class="modal">
        <div class="modal-content">
            <h3>Quantos bilhetes?</h3>
            <label>Número de bilhetes:</label>
            <input type="number" id="input-quantidade" min="1" value="1">
            <div class="modal-buttons">
                <button class="btn-modal-cancel" onclick="fecharModal('modal-quantidade')">Cancelar</button>
                <button class="btn-modal-confirm" onclick="confirmarQuantidade()">Adicionar</button>
            </div>
        </div>
    </div>
</body>
</html>
