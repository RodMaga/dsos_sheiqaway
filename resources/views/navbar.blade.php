<header>
    <div class="header-content">
        <button type="button" id="logo-button" class="header-logo-button">
            <h1>sheiqaway</h1>
        </button>
        <nav>
        @auth
            <a href="{{ route('viagens') }}">Viajar</a>
            <a href="{{ route('carrinho') }}">Carrinho</a>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            @if(Auth::user()->is_admin)
            <a href="{{ route('admin.dashboard') }}" style="color: #ef4444;">🔧 Admin</a>
            @endif
            <button type="button" id="theme-toggle" class="theme-toggle" title="Alternar tema">
                <span class="theme-icon">🌙</span>
            </button>
            @if(Auth::user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" style="color: #fbbf24; font-weight: 600;">🛡️ Admin</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" onclick="localStorage.removeItem('auth_token');">Logout</button>
            </form>
        @else
            <button type="button" id="theme-toggle" class="theme-toggle" title="Alternar tema">
                <span class="theme-icon">🌙</span>
            </button>
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Registar</a>
        @endauth
        </nav>
    </div>
</header>
