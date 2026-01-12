<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>sheiqaway - Redefinir Palavra-passe</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body>
    @include('navbar')

    <div class="auth-container">
        <div class="auth-card">
            <h2>Redefinir Palavra-passe</h2>
            <p>Introduza a sua nova palavra-passe abaixo</p>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $request->email) }}" 
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
                    <label for="password">Nova Palavra-passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                        required 
                        autocomplete="new-password"
                    >
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar Palavra-passe</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        placeholder="••••••••"
                        required 
                        autocomplete="new-password"
                    >
                    @error('password_confirmation')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">Redefinir Palavra-passe</button>
            </form>

            <div class="auth-footer">
                Lembrou-se da palavra-passe? <a href="{{ route('login') }}">Iniciar sessão</a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>
</body>
</html>