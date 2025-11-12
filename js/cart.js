document.addEventListener('DOMContentLoaded', () => {

    document.getElementById('logo-button').addEventListener('click', () => {
        window.location.href = 'index.html';
    });

    const itemsContainer = document.getElementById('cart-items-container');
    const subtotalEl = document.getElementById('cart-subtotal');
    const taxesEl = document.getElementById('cart-taxes');
    const totalEl = document.getElementById('cart-total');
    const cartLink = document.getElementById('cart-link');

    let cart = [];

    function loadCart() {
        cart = JSON.parse(localStorage.getItem('shoppingCart')) || [];
        
        updateCartCount();
        renderCartItems();
        calculateTotals();
    }

    function updateCartCount() {
        cartLink.textContent = `Carrinho (${cart.length})`;
    }

    function renderCartItems() {
        itemsContainer.innerHTML = '';

        if (cart.length === 0) {
            itemsContainer.innerHTML = '<p>O seu carrinho está vazio.</p>';
            return;
        }

        cart.forEach(trip => {
            const durationHours = Math.floor(trip.durationMin / 60);
            const durationMinutes = trip.durationMin % 60;
            
            const providerName = trip.providerId || "Companhia"; 

            const itemHTML = `
                <div class="cart-item">
                    <div class="item-details">
                        <h4>${trip.from} ➔ ${trip.to}</h4>
                        <p class="item-provider">Operado por: ${providerName}</p>
                        <p>Data: ${trip.date} (${trip.depart}h - ${trip.arrive}h)</p>
                        <p>Duração: ${durationHours}h ${durationMinutes}min</p>
                    </div>
                    <div class="item-actions">
                        <span class="item-price">€ ${trip.price.base.toFixed(2)}</span>
                        <button class="remove-btn" data-trip-id="${trip.id}">Remover</button>
                    </div>
                </div>
            `;
            itemsContainer.innerHTML += itemHTML;
        });
    }

    function calculateTotals() {
        let subtotal = 0;
        cart.forEach(trip => {
            subtotal += trip.price.base;
        });

        const taxes = 0.0;
        const total = subtotal + taxes;

        subtotalEl.textContent = `€ ${subtotal.toFixed(2)}`;
        taxesEl.textContent = `€ ${taxes.toFixed(2)}`;
        totalEl.textContent = `€ ${total.toFixed(2)}`;
    }

    itemsContainer.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-btn')) {
            const tripId = event.target.dataset.tripId;
            removeFromCart(tripId);
        }
    });

    function removeFromCart(tripId) {
        cart = cart.filter(trip => trip.id !== tripId);
        localStorage.setItem('shoppingCart', JSON.stringify(cart));
        
        loadCart(); 
    }

    loadCart();
});