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
    <title>sheiqaway - O seu portal de viagens</title>
    @vite(['resources/css/style.css', 'resources/js/script.js', 'resources/js/global.js'])
</head>
<body>
    @include('navbar')

    <main>
        <section class="content-card search-box">
            <h2>Encontre a sua próxima viagem</h2>
            <form id="search-form">
                
                <div class="form-group-wrapper">
                    <div class="form-group">
                        <label for="origin">Origem</label>
                        <input type="text" id="origin" placeholder="Ex: Porto" autocomplete="off">
                    </div>
                    <div id="origin-suggestions" class="suggestions-container"></div>
                </div>
                
                <div class="form-group-wrapper">
                    <div class="form-group">
                        <label for="destination">Destino</label>
                        <input type="text" id="destination" placeholder="Ex: Lisboa" autocomplete="off">
                    </div>
                    <div id="destination-suggestions" class="suggestions-container"></div>
                </div>

                <div class="form-group">
                    <label for="departure-date">Data de Partida</label>
                    <input type="date" id="departure-date">
                </div>
                
                <div class="form-group options">
                    <label>
                        <input type="checkbox" id="one-way"> Só Ida
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="search-button">Pesquisar</button>
                </div>
            </form>
            <div id="error-message" class="error-message"></div>
        </section>

        <section class="content-card results-box">
            <h3>Resultados</h3>
            
            <div class="filters">
                <div class="form-group">
                    <label for="sort-by">Ordenar por:</label>
                    <select id="sort-by">
                        <option value="price-asc">Preço (Mais baixo)</option>
                        <option value="price-desc">Preço (Mais alto)</option>
                        <option value="duration">Duração</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="filter-provider">Companhia:</label>
                    <select id="filter-provider">
                        <option value="all">Todas</option>
                        </select>
                </div>

                <div class="form-group">
                    <label for="filter-price">Preço Máximo: <span id="price-value">€300</span></label>
                    <input type="range" id="filter-price" min="0" max="300" value="300">
                </div>
            </div>

            <div id="loading-spinner" class="loading-spinner hidden"></div>

            <div id="results-container">
                <p>Por favor, efetue uma pesquisa para ver os resultados.</p>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>
</body>
</html>