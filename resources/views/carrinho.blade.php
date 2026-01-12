<!DOCTYPE html>
<html lang="pt" class="carrinho-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sheiqaway - Carrinho</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/pages.css', 'resources/js/global.js', 'resources/js/carrinho.js'])
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="carrinho-page">
    @include('navbar')
    <main>
        <h1>Carrinho de Compras</h1>
        <div id="carrinho-container"></div>
    </main>
    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
</body>
</html>
