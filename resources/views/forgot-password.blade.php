<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>sheiqaway - Recuperar Palavra-passe</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body>
    @include('navbar')

    <div class="auth-container">
        <div class="auth-card">
            <h2>Recuperar Palavra-passe</h2>
            <p>Esqueceu-se da sua palavra-passe? Não há problema. Indique-nos o seu endereço de email e enviaremos um link de redefinição que lhe permitirá escolher uma nova.</p>

            @if (session('status'))
                <div style="background-color: #dcfce7; color: #166534; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
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
                    >
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">Enviar Link de Redefinição</button>
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