<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>sheiqaway - Verificar Email</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body>
    @include('navbar')

    <div class="auth-container">
        <div class="auth-card">
            <h2>Verificar Email</h2>
            <p>Obrigado por se registar! Antes de começar, poderia verificar o seu endereço de email clicando no link que acabamos de enviar? Se não recebeu o email, teremos todo o gosto em enviar outro.</p>

            @if (session('status') == 'verification-link-sent')
                <div style="background-color: #dcfce7; color: #166534; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem;">
                    Um novo link de verificação foi enviado para o endereço de email fornecido durante o registo.
                </div>
            @endif

            @if(config('app.env') === 'local')
                <div style="background-color: #fef3c7; color: #92400e; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; border: 1px solid #f59e0b;">
                    <strong>Modo de Desenvolvimento:</strong>
                    <p style="margin-top: 0.5rem;">Para testar sem email real, clique no botão abaixo:</p>
                    <a href="{{ route('dev.verify') }}" style="display: inline-block; margin-top: 0.5rem; padding: 0.5rem 1rem; background-color: #059669; color: white; border-radius: 4px; text-decoration: none;">
                        ✅ Verificar Email (DEV)
                    </a>
                </div>
            @endif

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-primary">Reenviar Email de Verificação</button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: #6b7280; text-decoration: underline; cursor: pointer; font-size: 0.875rem;">
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>
</body>
</html>