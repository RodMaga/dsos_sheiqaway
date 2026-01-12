<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="user-authenticated" content="{{ Auth::check() ? 'true' : 'false' }}">
    @auth
    <meta name="user-name" content="{{ Auth::user()->name }}">
    @endauth
    <title>Pagamento Concluído - Sheiqaway</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body class="success-page">
    @include('navbar')
    
    <main style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
        <div style="max-width: 600px; width: 100%; background: white; padding: 3rem; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); text-align: center; animation: slideUp 0.5s ease-out;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; animation: scaleIn 0.6s ease-out;">
                <svg style="width: 48px; height: 48px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 style="color: #10b981; font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">Pagamento Concluído!</h1>
            
            <p style="color: #6c757d; font-size: 1.1rem; line-height: 1.6; margin-bottom: 2rem;">
                Suas reservas foram confirmadas com sucesso.<br>
                Você receberá um email com todos os detalhes da sua viagem.
            </p>
            
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1rem; margin-bottom: 2rem;">
                <p style="color: #166534; font-size: 0.9rem; margin: 0;">
                    <strong>💳 Transação processada com segurança</strong><br>
                    <span style="font-size: 0.85rem; color: #15803d;">ID da sessão: {{ request()->session_id }}</span>
                </p>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="/perfil/reservas" class="btn" style="flex: 1; min-width: 180px; text-align: center; padding: 0.875rem 1.5rem; font-size: 1rem;">📋 Ver Minhas Reservas</a>
                <a href="/viagens" class="btn-secondary" style="flex: 1; min-width: 180px; text-align: center; padding: 0.875rem 1.5rem; font-size: 1rem;">✈️ Explorar Mais Viagens</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
    
    <script>
        // Limpar carrinho após pagamento bem-sucedido
        localStorage.removeItem('carrinho');
    </script>
    
    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        body.success-page { background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); }
        body.dark-mode.success-page { background: linear-gradient(135deg, #1a1a1a 0%, #0f2f1f 100%); }
        body.dark-mode.success-page main > div { background: #2d2d2d; border: 1px solid #404040; }
        body.dark-mode.success-page h1 { color: #10b981; }
        body.dark-mode.success-page p { color: #b0b0b0; }
    </style>
</body>
</html>
