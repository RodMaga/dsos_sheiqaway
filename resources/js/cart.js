// cart.js - PÁGINA DO CARRINHO (carrinho.html)
class CartManager {
    constructor() {
        this.cart = [];
        this.init();
    }

    init() {
        this.loadCart();
        this.setupEventListeners();
        this.setupUI();
        this.setupAnimations();
    }

    loadCart() {
        this.cart = JSON.parse(localStorage.getItem('shoppingCart')) || [];
        this.renderCart();
        this.updateTotals();
    }

    setupUI() {
        // Atualizar contador no header
        if (window.GlobalManager) {
            window.GlobalManager.updateCartCount();
        }
    }

    setupAnimations() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInUp {
                from { transform: translateY(20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
            
            .cart-item-enter {
                animation: slideInUp 0.4s ease-out;
            }
            
            .cart-item-exit {
                animation: slideInUp 0.4s ease-out reverse;
            }
            
            .checkout-loading {
                position: relative;
                pointer-events: none;
            }
            
            .checkout-loading::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 20px;
                height: 20px;
                margin: -10px 0 0 -10px;
                border: 2px solid rgba(255,255,255,0.3);
                border-top-color: white;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    }

    renderCart() {
        const container = document.getElementById('cart-items-container');
        if (!container) return;
        
        if (this.cart.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 50px 20px; animation: fadeIn 0.5s ease-out;">
                    <div style="font-size: 3em; margin-bottom: 20px; color: var(--border-color);">🛒</div>
                    <h3 style="color: var(--text-secondary); margin-bottom: 15px;">O seu carrinho está vazio</h3>
                    <p style="margin-bottom: 25px; color: var(--text-secondary); max-width: 400px; margin: 0 auto;">
                        Ainda não adicionou nenhuma viagem ao carrinho. Explore as nossas opções e comece a planear a sua próxima aventura!
                    </p>
                    <button onclick="window.location.href='/' || window.location.href='{{ route('home') }}'" class="search-button" style="min-width: 200px;">
                        Explorar Viagens Disponíveis
                    </button>
                </div>
            `;
            
            const summary = document.querySelector('.cart-summary');
            if (summary) summary.style.display = 'none';
            return;
        }
        
        const summary = document.querySelector('.cart-summary');
        if (summary) summary.style.display = 'block';
        
        container.innerHTML = '';
        
        this.cart.forEach((trip, index) => {
            const providerName = window.searchManager?.providerMap?.[trip.providerId] || trip.providerId;
            const durationStr = window.GlobalManager?.formatDuration(trip.durationMin) || 
                               `${Math.floor(trip.durationMin / 60)}h ${trip.durationMin % 60}min`;
            const lugaresLivres = trip.capacity - trip.booked;
            const isLowAvailability = lugaresLivres < 5;
            
            const item = document.createElement('div');
            item.className = 'cart-item cart-item-enter';
            item.style.animationDelay = `${index * 0.05}s`;
            item.setAttribute('data-trip-id', trip.id);
            
            item.innerHTML = `
                <div class="item-details">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                        <div>
                            <h4 style="margin: 0 0 5px 0; color: var(--text-primary);">${trip.from} ➔ ${trip.to}</h4>
                            <p class="item-provider" style="margin: 0; color: var(--text-secondary); font-size: 0.9em;">
                                Operado por: <strong>${providerName}</strong>
                            </p>
                        </div>
                        <span class="badge ${lugaresLivres === 0 ? 'danger' : isLowAvailability ? 'warning' : 'success'}" 
                              style="font-size: 0.75em; padding: 4px 10px;">
                            ${lugaresLivres === 0 ? 'Esgotado' : `${lugaresLivres} ${lugaresLivres === 1 ? 'lugar' : 'lugares'} livre${lugaresLivres !== 1 ? 's' : ''}`}
                        </span>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="color: var(--text-secondary);">📅</span>
                            <span style="font-size: 0.9em; color: var(--text-primary);">${trip.date}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="color: var(--text-secondary);">🕐</span>
                            <span style="font-size: 0.9em; color: var(--text-primary);">${trip.depart} - ${trip.arrive}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="color: var(--text-secondary);">⏱️</span>
                            <span style="font-size: 0.9em; color: var(--text-primary);">${durationStr}</span>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 0.85em; color: var(--text-secondary); display: flex; align-items: center; gap: 5px;">
                            🛄 ${trip.bagsIncluded} mala${trip.bagsIncluded !== 1 ? 's' : ''} incluída${trip.bagsIncluded !== 1 ? 's' : ''}
                        </span>
                        ${lugaresLivres < 3 && lugaresLivres > 0 ? `
                            <span style="font-size: 0.75em; color: #dc3545; background: #f8d7da; padding: 2px 8px; border-radius: 10px;">
                                ⚡ Poucos lugares!
                            </span>
                        ` : ''}
                    </div>
                </div>
                
                <div class="item-actions">
                    <div style="text-align: right; margin-bottom: 10px;">
                        <span class="item-price" style="font-size: 1.4em; font-weight: 700; color: var(--accent-color);">
                            € ${trip.price.base.toFixed(2)}
                        </span>
                        ${trip.price.discount ? `
                            <div style="font-size: 0.8em; color: #28a745;">
                                <s style="color: var(--text-secondary);">€ ${trip.price.original.toFixed(2)}</s>
                                <span style="margin-left: 5px;">-${Math.round((1 - trip.price.base/trip.price.original) * 100)}%</span>
                            </div>
                        ` : ''}
                    </div>
                    <button class="remove-btn" data-trip-id="${trip.id}" title="Remover do carrinho"
                            style="display: flex; align-items: center; gap: 5px; padding: 8px 15px;">
                        <span>🗑️</span>
                        <span>Remover</span>
                    </button>
                </div>
            `;
            
            container.appendChild(item);
        });
    }

    updateTotals() {
        if (this.cart.length === 0) return;
        
        const subtotal = this.cart.reduce((sum, trip) => sum + trip.price.base, 0);
        const taxes = subtotal * 0.05;
        const total = subtotal + taxes;
        
        const subtotalEl = document.getElementById('cart-subtotal');
        const taxesEl = document.getElementById('cart-taxes');
        const totalEl = document.getElementById('cart-total');
        
        if (subtotalEl) subtotalEl.textContent = `€ ${subtotal.toFixed(2)}`;
        if (taxesEl) taxesEl.textContent = `€ ${taxes.toFixed(2)}`;
        if (totalEl) totalEl.textContent = `€ ${total.toFixed(2)}`;
    }

    setupEventListeners() {
        // Remover itens
        document.addEventListener('click', (e) => {
            if (e.target.closest('.remove-btn')) {
                const tripId = e.target.closest('.remove-btn').dataset.tripId;
                this.removeItem(tripId);
            }
        });
        
        // Finalizar compra - AQUI ESTAVA O ERRO!
        const checkoutBtn = document.querySelector('.checkout-button');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => this.processCheckout());
        }
        
        // Continuar a pesquisar
        const continueBtn = document.querySelector('.back-link');
        if (continueBtn) {
            continueBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = '/';
            });
        }
        
        window.addEventListener('storage', (e) => {
            if (e.key === 'shoppingCart') {
                this.loadCart();
            }
        });
    }

    removeItem(tripId) {
        if (!confirm('Tem a certeza que deseja remover esta viagem do carrinho?')) {
            return;
        }
        
        const item = document.querySelector(`[data-trip-id="${tripId}"]`)?.closest('.cart-item');
        if (item) {
            item.classList.remove('cart-item-enter');
            item.classList.add('cart-item-exit');
            
            setTimeout(() => {
                this.cart = this.cart.filter(trip => trip.id !== tripId);
                localStorage.setItem('shoppingCart', JSON.stringify(this.cart));
                this.loadCart();
                
                if (window.GlobalManager) {
                    window.GlobalManager.showToast('Viagem removida do carrinho.', 'info');
                }
            }, 400);
        }
    }

    // FUNÇÃO QUE ESTAVA FALTANDO!
    processCheckout() {
        // Validar autenticação
        if (!window.GlobalManager?.isLoggedIn()) {
            if (confirm('Para finalizar a compra, precisa de fazer login. Deseja ir para a página de login?')) {
                window.location.href = '/login';
            }
            return;
        }
        
        // Validar carrinho vazio
        if (this.cart.length === 0) {
            window.GlobalManager?.showToast('O seu carrinho está vazio!', 'warning');
            return;
        }
        
        // Validar disponibilidade
        const unavailableTrips = this.cart.filter(trip => trip.booked >= trip.capacity);
        if (unavailableTrips.length > 0) {
            window.GlobalManager?.showToast(
                `${unavailableTrips.length} viagem${unavailableTrips.length !== 1 ? 'ens' : ''} no seu carrinho já não estão disponíveis. Por favor, remova-as.`,
                'error'
            );
            return;
        }

        // Confirmar compra
        const total = this.calculateTotal();
        const numViagens = this.cart.length;
        
        if (!confirm(`Confirmar compra de ${numViagens} viagem${numViagens !== 1 ? 'ens' : ''} por € ${total.toFixed(2)}?\n\nEsta ação não pode ser desfeita.`)) {
            return;
        }
        
        // Mostrar loading
        this.showLoading();
        
        // Processar checkout (simulado)
        setTimeout(() => {
            this.completeCheckout();
        }, 2000);
    }

    // FUNÇÃO AUXILIAR
    calculateTotal() {
        const subtotal = this.cart.reduce((sum, trip) => sum + trip.price.base, 0);
        const taxes = subtotal * 0.05;
        return subtotal + taxes;
    }

    showLoading() {
        const container = document.getElementById('cart-items-container');
        const checkoutBtn = document.querySelector('.checkout-button');
        const summary = document.querySelector('.cart-summary');
        
        if (container) {
            container.innerHTML = `
                <div style="text-align: center; padding: 60px 20px;">
                    <div class="loading-spinner" style="width: 60px; height: 60px; margin: 0 auto 20px;"></div>
                    <h3 style="color: var(--text-primary); margin-bottom: 15px;">A processar a sua compra...</h3>
                    <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto;">
                        Estamos a confirmar a disponibilidade dos seus lugares e a processar o pagamento seguro.
                    </p>
                    <div style="margin-top: 30px; font-size: 0.9em; color: var(--text-secondary);">
                        ⏳ Isto pode demorar alguns segundos
                    </div>
                </div>
            `;
        }
        
        if (checkoutBtn) {
            checkoutBtn.disabled = true;
            checkoutBtn.classList.add('checkout-loading');
            checkoutBtn.textContent = 'Processando...';
        }
        
        if (summary) {
            summary.style.opacity = '0.5';
            summary.style.pointerEvents = 'none';
        }
    }

    hideLoading() {
        const checkoutBtn = document.querySelector('.checkout-button');
        const summary = document.querySelector('.cart-summary');
        
        if (checkoutBtn) {
            checkoutBtn.disabled = false;
            checkoutBtn.classList.remove('checkout-loading');
            checkoutBtn.textContent = 'Finalizar Compra';
        }
        
        if (summary) {
            summary.style.opacity = '';
            summary.style.pointerEvents = '';
        }
    }

    completeCheckout() {
        try {
            const totalAntes = this.calculateTotal();
            const numViagens = this.cart.length;
            const ticketCode = window.GlobalManager?.generateTicketCode() || `SHQ-${Date.now()}`;
            const history = JSON.parse(localStorage.getItem('purchaseHistory')) || [];
            const user = window.GlobalManager?.getCurrentUser();
            
            // Adicionar cada viagem ao histórico
            this.cart.forEach(trip => {
                history.push({
                    ...trip,
                    ticketCode: ticketCode,
                    purchaseDate: new Date().toLocaleDateString('pt-PT'),
                    purchaseTime: new Date().toLocaleTimeString('pt-PT'),
                    passengerName: user?.name || 'Utilizador',
                    status: 'confirmado',
                    bookingReference: `BR-${Math.random().toString(36).substr(2, 8).toUpperCase()}`
                });
            });
            
            // Guardar histórico
            localStorage.setItem('purchaseHistory', JSON.stringify(history));
            
            // Limpar carrinho
            const cartInfo = {
                total: totalAntes,
                numViagens: numViagens,
                ticketCode: ticketCode
            };
            
            this.cart = [];
            localStorage.removeItem('shoppingCart');
            
            // Atualizar contador global
            if (window.GlobalManager) {
                window.GlobalManager.updateCartCount();
            }
            
            // Esconder loading
            this.hideLoading();
            
            // Mostrar confirmação
            this.showConfirmation(cartInfo);
            
        } catch (error) {
            console.error('Erro ao processar checkout:', error);
            window.GlobalManager?.showToast('Ocorreu um erro ao processar a sua compra. Tente novamente.', 'error');
            this.hideLoading();
            this.loadCart();
        }
    }

    showConfirmation(cartInfo) {
        const container = document.getElementById('cart-items-container');
        const summary = document.querySelector('.cart-summary');
        
        if (!container) return;
        
        container.innerHTML = `
            <div style="text-align: center; padding: 40px 20px; animation: fadeIn 0.5s ease-out;">
                <div style="font-size: 4em; margin-bottom: 20px; color: #28a745;">🎉</div>
                <h2 style="color: #28a745; margin-bottom: 15px;">Compra Confirmada!</h2>
                <p style="color: var(--text-secondary); margin-bottom: 30px; max-width: 500px; margin: 0 auto 30px;">
                    A sua reserva foi processada com sucesso. Os seus bilhetes estão prontos para serem utilizados.
                </p>
                
                <div style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 12px; padding: 25px; margin: 30px auto; max-width: 500px; border: 2px dashed #28a745;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div style="text-align: left;">
                            <div style="font-size: 0.9em; color: var(--text-secondary);">Código do Bilhete</div>
                            <div style="font-size: 1.8em; font-weight: 700; letter-spacing: 2px; color: var(--text-primary);">
                                ${cartInfo.ticketCode}
                            </div>
                        </div>
                        <div style="background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <div style="width: 80px; height: 80px; background: #333;"></div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px;">
                        <div style="text-align: left;">
                            <div style="font-size: 0.85em; color: var(--text-secondary);">Número de Viagens</div>
                            <div style="font-size: 1.2em; font-weight: 600;">${cartInfo.numViagens}</div>
                        </div>
                        <div style="text-align: left;">
                            <div style="font-size: 0.85em; color: var(--text-secondary);">Total Pago</div>
                            <div style="font-size: 1.2em; font-weight: 600; color: var(--accent-color);">
                                € ${cartInfo.total.toFixed(2)}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 30px; color: var(--text-secondary); font-size: 0.9em; max-width: 500px; margin: 30px auto 0;">
                    <p>📧 Receberá um e-mail com os detalhes da sua reserva em breve.</p>
                    <p>📱 Pode consultar os seus bilhetes a qualquer momento na página "O Meu Perfil".</p>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: center; margin-top: 40px;">
                    <button onclick="window.location.href='/perfil/reservas'" class="search-button" style="min-width: 180px;">
                        Ver Minhas Reservas
                    </button>
                    <button onclick="window.location.href='/' || window.location.href='{{ route('home') }}'" class="secondary-button" style="min-width: 180px;">
                        Nova Viagem
                    </button>
                </div>
            </div>
        `;
        
        if (summary) {
            summary.style.display = 'none';
        }
        
        if (window.GlobalManager) {
            window.GlobalManager.showToast(`Compra realizada com sucesso! Código: ${cartInfo.ticketCode}`, 'success');
        }
    }
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', () => {
    window.cartManager = new CartManager();
});