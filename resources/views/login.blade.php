<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#0ea5e9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ Auth::check() ? 'true' : 'false' }}">
    @auth
    <meta name="user-name" content="{{ Auth::user()->name }}">
    @endauth
    <title>sheiqaway - Login</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body>
    @include('navbar')

    <div class="auth-container">
        <div class="auth-card">
            <h2>Bem-vindo de volta</h2>
            <p>Entre na sua conta para continuar</p>

            @if (session('status'))
                <div style="background-color: #dcfce7; color: #166534; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="seuemail@exemplo.com"
                        required 
                        autofocus 
                        autocomplete="username"
                    >
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Palavra-passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                        required 
                        autocomplete="current-password"
                    >
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember" id="remember">
                        Lembrar-me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Esqueceu a palavra-passe?</a>
                    @endif
                </div>

                <button type="submit" class="btn-primary">Entrar</button>
            </form>

            <div class="auth-footer">
                Não tem uma conta? <a href="{{ route('register') }}">Registar</a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>
</body>
</html>
