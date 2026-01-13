<header>
    <div class="header-content">
        <button type="button" id="logo-button" class="header-logo-button">
            <h1>sheiqaway</h1>
        </button>
        <button type="button" class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Menu">
            ☰
        </button>
        <nav id="main-nav">
        @auth
            <a href="{{ route('viagens') }}">Viajar</a>
            <a href="{{ route('carrinho') }}">Carrinho</a>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            @if(Auth::user()->is_admin)
            <a href="{{ route('admin.dashboard') }}" style="color: #ef4444;">⚙ Admin</a>
            @endif
            <button type="button" id="theme-toggle" class="theme-toggle" title="Alternar tema">
                <span class="theme-icon">🌙</span>
            </button>
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

<script>
// Mobile menu toggle
const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
const mainNav = document.getElementById('main-nav');

if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', function() {
        mainNav.classList.toggle('mobile-menu-open');
        this.textContent = mainNav.classList.contains('mobile-menu-open') ? '✕' : '☰';
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('header')) {
            mainNav.classList.remove('mobile-menu-open');
            mobileMenuToggle.textContent = '☰';
        }
    });
    
    // Close menu when clicking on a link
    mainNav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function() {
            mainNav.classList.remove('mobile-menu-open');
            mobileMenuToggle.textContent = '☰';
        });
    });
}
</script>
