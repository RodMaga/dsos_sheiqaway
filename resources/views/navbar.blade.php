<header>
    <button type="button" id="logo-button" class="header-logo-button">
        <h1>sheiqaway</h1>
    </button>
    <nav>
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'current-page' : '' }}">Viajar</a>
        <a href="{{ route('carrinho') }}" id="cart-link" class="{{ request()->routeIs('carrinho') ? 'current-page' : '' }}">Carrinho (0)</a>
        @auth
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'current-page' : '' }}">Dashboard</a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer; font: inherit; padding: 0;">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'current-page' : '' }}">Login</a>
            <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'current-page' : '' }}">Registar</a>
        @endauth
    </nav>
</header>
