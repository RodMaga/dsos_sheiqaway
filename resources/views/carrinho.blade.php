<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ Auth::check() ? 'true' : 'false' }}">
    @auth
    <meta name="user-name" content="{{ Auth::user()->name }}">
    @endauth
    <title>sheiqaway - Carrinho</title>
    @vite(['resources/css/style.css', 'resources/js/cart.js', 'resources/js/global.js'])
</head>
<body>
    @include('navbar')

    <main>
        <section class="content-card cart-box">
            <h2>O meu carrinho</h2>
            <div class="cart-container">
                
                <div class="cart-items">
                    <div id="cart-items-container">
                        <p>O seu carrinho está vazio.</p>
                    </div>
                </div>

                <div class="cart-summary">
                    <h4>Sumário da Compra</h4>
                    <div class="summary-line">
                        <span>Subtotal:</span>
                        <span id="cart-subtotal">€ 0.00</span>
                    </div>
                    <div class="summary-line">
                        <span>Taxas e Impostos:</span>
                        <span id="cart-taxes">€ 0.00</span>
                    </div>
                    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 15px 0;">
                    <div class="summary-line summary-total">
                        <strong>Total:</strong>
                        <strong id="cart-total">€ 0.00</strong>
                    </div>
                    <button class="checkout-button">Finalizar Compra</button>
                    <a href="index.html" class="back-link">Continuar a pesquisar</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>
</body>
</html>