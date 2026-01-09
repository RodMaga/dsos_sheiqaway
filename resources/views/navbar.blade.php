<header>
    <button type="button" id="logo-button" class="header-logo-button">
        <h1>sheiqaway</h1>
    </button>
    <nav>
    @auth
        {{-- Links para utilizadores LOGADOS --}}
        <a href="{{ route('viagens') }}">Viajar</a>
        <a href="{{ route('carrinho') }}">Carrinho</a>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" onclick="localStorage.removeItem('auth_token');">Logout</button>
        </form>
    @else
        {{-- Links para utilizadores DESLOGADOS --}}
        <a href="{{ route('login') }}">Login</a>
        <a href="{{ route('register') }}">Registar</a>
    @endauth
</nav>
</header>
