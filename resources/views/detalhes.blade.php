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
    @vite(['resources/css/pages.css', 'resources/js/global.js', 'resources/js/detalhes.js'])
</head>
<body>
    @include('navbar')
    
    <main>
        <section class="content-card">
            <div id="details-container">
                <div id="loading" style="text-align: center; padding: 40px;">
                    <div class="loading-spinner" style="width: 60px; height: 60px; margin: 0 auto 20px;"></div>
                    <h3 style="color: #6c757d;">A carregar detalhes da viagem...</h3>
                </div>
            </div>
        </section>
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

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
</body>
</html>
