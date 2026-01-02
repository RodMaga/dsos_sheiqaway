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
    <title>sheiqaway - Dashboard</title>
    @vite(['resources/css/style.css', 'resources/js/global.js'])
</head>
<body>
    @include('navbar')

    <main style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        <h1 style="color: #000000; margin-bottom: 1rem;">Dashboard</h1>
        <p style="color: #666666; margin-bottom: 2rem;">Bem-vindo, {{ Auth::user()->name }}!</p>

        <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 2rem; margin-bottom: 1rem;">
            <h2 style="color: #000000; margin-bottom: 1rem;">Suas Informações</h2>
            <p><strong>Nome:</strong> {{ Auth::user()->name }}</p>
            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
        </div>

        <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 2rem;">
            <h2 style="color: #000000; margin-bottom: 1rem;">Ações Rápidas</h2>
            <a href="{{ route('home') }}" style="display: inline-block; padding: 0.75rem 1.5rem; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 4px; margin-right: 1rem;">Pesquisar Viagens</a>
            <a href="{{ route('carrinho') }}" style="display: inline-block; padding: 0.75rem 1.5rem; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 4px;">Ver Carrinho</a>
        </div>
    </main>

    <footer style="text-align: center; padding: 1.5rem; color: #666666;">
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>
</body>
</html>
