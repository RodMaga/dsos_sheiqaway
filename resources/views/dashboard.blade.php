<!DOCTYPE html>
<html lang="pt" class="dashboard-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#0ea5e9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>sheiqaway - Dashboard</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js', 'resources/js/dashboard.js'])
</head>
<body class="dashboard-page">


























    @include('navbar')

    <main>
        <h1>Dashboard</h1>
        <p class="welcome">Bem-vindo, {{ Auth::user()->name }}!</p>

        <div class="dashboard-grid">
            <div class="card">
                <h2>Suas Informações</h2>
                <div class="info-item">
                    <strong>Nome:</strong> {{ Auth::user()->name }}
                </div>
                <div class="info-item">
                    <strong>Email:</strong> {{ Auth::user()->email }}
                </div>
                <div class="info-item">
                    <strong>Telefone:</strong> {{ Auth::user()->phone ?? 'Não informado' }}
                </div>
                <div class="info-item">
                    <strong>Pontos:</strong> {{ Auth::user()->points ?? 0 }}
                </div>
            </div>

            <div class="card">
                <h2>Ações Rápidas</h2>
                <div class="actions">
                    <a href="{{ route('viagens') }}" class="btn">Pesquisar Viagens</a>
                    <a href="{{ route('carrinho') }}" class="btn btn-secondary">Ver Carrinho</a>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Minhas Reservas Recentes</h2>
            <div id="dashboard-reservas" class="loading">A carregar reservas...</div>
            <a href="{{ route('profile.reservas') }}" style="display: inline-block; margin-top: 1rem; color: #2563eb; text-decoration: none;">Ver todas as reservas →</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
</body>
</html>
