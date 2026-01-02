document.addEventListener('DOMContentLoaded', () => {
    initGlobal();
});

function initGlobal() {
    setupLogoNavigation();
    updateCartCount();
    // DON'T call updateHeader() - navbar is server-rendered by Laravel
    syncAuthWithLaravel();
    restoreTheme();
}

function setupLogoNavigation() {
    const logoBtn = document.getElementById('logo-button');
    if (logoBtn && !logoBtn.onclick) {
        logoBtn.onclick = () => window.location.href = '/';
    }
}

// Sync Laravel session auth with JavaScript
function syncAuthWithLaravel() {
    // Check if user is authenticated via Laravel (meta tag or data attribute)
    const isAuthenticated = document.querySelector('meta[name="user-authenticated"]')?.content === 'true';
    const userName = document.querySelector('meta[name="user-name"]')?.content;
    
    if (isAuthenticated && userName) {
        // Store in localStorage for JS access
        localStorage.setItem('user', JSON.stringify({
            loggedIn: true,
            name: userName
        }));
    } else {
        // Clear localStorage if not authenticated
        localStorage.removeItem('user');
    }
}

function updateHeader() {
    // This function is now disabled - navbar is server-rendered
    // Just update cart count
    updateCartCount();
}

function isCurrentPage(pageName, currentPath) {
    return currentPath === pageName || 
           (pageName === '/' && currentPath === '/');
}

// 3. GERENCIAMENTO DO CARRINHO
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('shoppingCart')) || [];
    const cartLinks = document.querySelectorAll('#cart-link');
    cartLinks.forEach(link => {
        if (link) {
            link.textContent = `Carrinho (${cart.length})`;
        }
    });
    return cart.length;
}

function addToCart(trip) {
    let cart = JSON.parse(localStorage.getItem('shoppingCart')) || [];
    
    // Verificar se já está no carrinho
    if (cart.some(item => item.id === trip.id)) {
        showToast('Esta viagem já está no seu carrinho.', 'warning');
        return false;
    }
    
    // Verificar se há lugares disponíveis
    if (trip.booked >= trip.capacity) {
        showToast('Lamentamos, esta viagem está esgotada.', 'error');
        return false;
    }
    
    // Adicionar ao carrinho
    cart.push(trip);
    localStorage.setItem('shoppingCart', JSON.stringify(cart));
    updateCartCount();
    showToast('Viagem adicionada ao carrinho!', 'success');
    return true;
}

function removeFromCart(tripId) {
    let cart = JSON.parse(localStorage.getItem('shoppingCart')) || [];
    cart = cart.filter(trip => trip.id !== tripId);
    localStorage.setItem('shoppingCart', JSON.stringify(cart));
    updateCartCount();
    showToast('Viagem removida do carrinho.', 'info');
    return cart;
}

function getCart() {
    return JSON.parse(localStorage.getItem('shoppingCart')) || [];
}

function clearCart() {
    localStorage.removeItem('shoppingCart');
    updateCartCount();
}

// 4. AUTENTICAÇÃO
function logout() {
    // Use Laravel's logout route
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }
    
    document.body.appendChild(form);
    form.submit();
}

function isLoggedIn() {
    const user = JSON.parse(localStorage.getItem('user'));
    return !!(user && user.loggedIn);
}

function getCurrentUser() {
    return JSON.parse(localStorage.getItem('user')) || null;
}

// 5. TEMA (DARK MODE)
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    showToast(`Modo ${isDark ? 'escuro' : 'claro'} ativado.`, 'info');
}

function restoreTheme() {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }
}

// 6. FEEDBACK VISUAL (TOASTS)
function showToast(message, type = 'info') {
    // Remover toast anterior se existir
    const existingToast = document.querySelector('.global-toast');
    if (existingToast) existingToast.remove();
    
    // Criar novo toast
    const toast = document.createElement('div');
    toast.className = `global-toast toast-${type}`;
    toast.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">×</button>
    `;
    
    // Estilos inline para o toast
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : 
                     type === 'error' ? '#dc3545' : 
                     type === 'warning' ? '#ffc107' : '#17a2b8'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remover após 3 segundos
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }
    }, 3000);
}

// 7. UTILITÁRIOS
function formatCurrency(amount) {
    return `€ ${parseFloat(amount).toFixed(2)}`;
}

function formatDuration(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}h ${mins}min`;
}

function generateTicketCode() {
    return `SHQ-${Math.random().toString(36).substr(2, 9).toUpperCase()}`;
}

// 8. EXPORTAR FUNÇÕES GLOBAIS
if (typeof window !== 'undefined') {
    window.GlobalManager = {
        addToCart,
        removeFromCart,
        getCart,
        clearCart,
        updateCartCount,
        toggleDarkMode,
        showToast,
        formatCurrency,
        formatDuration,
        generateTicketCode,
        logout,
        isLoggedIn,
        getCurrentUser
    };
}

// 9. ANIMAÇÕES CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .global-toast button {
        background: transparent;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        margin-left: 15px;
        padding: 0 5px;
    }
    
    .global-toast button:hover {
        opacity: 0.8;
    }
`;
document.head.appendChild(style);