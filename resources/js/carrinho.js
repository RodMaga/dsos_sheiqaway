// carrinho.js - Lógica da página do carrinho
function carregarCarrinho() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    const container = document.getElementById('carrinho-container');
    
    if (carrinho.length === 0) {
        container.innerHTML = `
            <div class="carrinho-vazio">
                <p>Seu carrinho está vazio</p>
                <a href="/viagens" class="btn-continuar">Continuar Comprando</a>
            </div>
        `;
        return;
    }
    
    let html = '';
    let total = 0;
    
    carrinho.forEach((item, index) => {
        const subtotal = item.preco * item.quantidade;
        total += subtotal;
        
        html += `
            <div class="carrinho-item">
                <div class="item-info">
                    <h3>${item.companhia}</h3>
                    <div class="route">${item.origem} → ${item.destino}</div>
                </div>
                <div class="item-quantidade">
                    <button onclick="alterarQuantidade(${index}, -1)">-</button>
                    <span>${item.quantidade}</span>
                    <button onclick="alterarQuantidade(${index}, 1)">+</button>
                </div>
                <div class="item-preco">${subtotal.toFixed(2)} ${item.moeda}</div>
                <button class="btn-remover" onclick="removerItem(${index})">Remover</button>
            </div>
        `;
    });
    
    const pontosReceber = Math.floor(total / 10);
    
    html += `
        <div class="carrinho-resumo">
            <div class="resumo-linha">
                <span>Total de itens:</span>
                <span>${carrinho.reduce((sum, item) => sum + item.quantidade, 0)}</span>
            </div>
            <div class="resumo-linha resumo-total">
                <span>Total:</span>
                <span>${total.toFixed(2)} ${carrinho[0].moeda}</span>
            </div>
            <div class="resumo-linha" style="color: #16a34a; font-weight: 600; border-top: 1px solid #e0e0e0; margin-top: 0.5rem; padding-top: 0.5rem;">
                <span>🎁 Pontos a receber:</span>
                <span>${pontosReceber} pontos</span>
            </div>
        </div>
        
        <div class="passageiros-form">
            <h3>Dados dos Passageiros</h3>
            <div id="passageiros-inputs"></div>
            <button class="btn-finalizar" onclick="finalizarCompra()">Finalizar Compra</button>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="/viagens" class="btn-continuar">Continuar Comprando</a>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    let passageirosHtml = '';
    let passageiroIndex = 1;
    carrinho.forEach((item, itemIndex) => {
        for (let i = 0; i < item.quantidade; i++) {
            passageirosHtml += `
                <div class="passageiro-input">
                    <label>Passageiro ${passageiroIndex} - ${item.companhia} (${item.origem} → ${item.destino})</label>
                    <input type="text" id="passageiro-${itemIndex}-${i}" placeholder="Nome completo" required>
                </div>
            `;
            passageiroIndex++;
        }
    });
    document.getElementById('passageiros-inputs').innerHTML = passageirosHtml;
}

function alterarQuantidade(index, delta) {
    const carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    carrinho[index].quantidade += delta;
    
    if (carrinho[index].quantidade <= 0) {
        carrinho.splice(index, 1);
    }
    
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    carregarCarrinho();
}

function removerItem(index) {
    const carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    carrinho.splice(index, 1);
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    carregarCarrinho();
}

const stripe = Stripe('pk_test_51SooNOIQBLR7czrRnsWeak4sfG9oz0M3PrxMBDmRqp7XvPiygViQPp4JTQWDfjHJhGtotGumVTMhaUohNC0M8BHm00BKmkeTmt');

function finalizarCompra() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    
    // Desabilitar botão para evitar cliques múltiplos
    const btnFinalizar = document.querySelector('.btn-finalizar');
    if (btnFinalizar.disabled) return;
    btnFinalizar.disabled = true;
    btnFinalizar.textContent = 'Processando...';
    btnFinalizar.style.opacity = '0.6';
    
    const reservas = [];
    const nomes = [];
    let todosPreenchidos = true;
    let erros = [];
    
    carrinho.forEach((item, itemIndex) => {
        for (let i = 0; i < item.quantidade; i++) {
            const input = document.getElementById(`passageiro-${itemIndex}-${i}`);
            const nome = input?.value.trim();
            
            if (!nome) {
                todosPreenchidos = false;
                input.style.borderColor = '#dc2626';
                erros.push('Preencha todos os nomes');
            } else if (nome.length < 3) {
                todosPreenchidos = false;
                input.style.borderColor = '#dc2626';
                erros.push('Nome deve ter pelo menos 3 caracteres');
            } else if (!/^[a-zA-ZÀ-ſ\s]+$/.test(nome)) {
                todosPreenchidos = false;
                input.style.borderColor = '#dc2626';
                erros.push('Nome deve conter apenas letras');
            } else if (nomes.includes(nome.toLowerCase())) {
                todosPreenchidos = false;
                input.style.borderColor = '#dc2626';
                erros.push(`Nome "${nome}" já foi usado`);
            } else {
                input.style.borderColor = '#e0e0e0';
                nomes.push(nome.toLowerCase());
                reservas.push({
                    trip_id: parseInt(item.tripId),
                    passenger_name: nome,
                    price: parseFloat(item.preco),
                    quantity: 1
                });
            }
        }
    });
    
    if (!todosPreenchidos) {
        btnFinalizar.disabled = false;
        btnFinalizar.textContent = 'Finalizar Compra';
        btnFinalizar.style.opacity = '1';
        
        const msg = document.createElement('div');
        msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#dc2626;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;max-width:400px;';
        msg.innerHTML = '<strong>Erros encontrados:</strong><br>' + [...new Set(erros)].join('<br>');
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 4000);
        return;
    }
    
    // Criar sessão de checkout do Stripe
    fetch('/payment/create-checkout-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ reservas })
    })
    .then(res => res.json())
    .then(data => {
        if (data.id) {
            // Redirecionar para Stripe Checkout
            return stripe.redirectToCheckout({ sessionId: data.id });
        } else {
            throw new Error(data.error || 'Erro ao criar sessão de pagamento');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        btnFinalizar.disabled = false;
        btnFinalizar.textContent = 'Finalizar Compra';
        btnFinalizar.style.opacity = '1';
        
        const msg = document.createElement('div');
        msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#dc2626;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;';
        msg.textContent = 'Erro: ' + error.message;
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 3000);
    });
}

// Função antiga (backup sem Stripe)
function finalizarCompraSemStripe() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    
    const btnFinalizar = document.querySelector('.btn-finalizar');
    if (btnFinalizar.disabled) return;
    btnFinalizar.disabled = true;
    btnFinalizar.textContent = 'Processando...';
    btnFinalizar.style.opacity = '0.6';
    
    const reservas = [];
    const nomes = [];
    let todosPreenchidos = true;
    let erros = [];
    
    carrinho.forEach((item, itemIndex) => {
        for (let i = 0; i < item.quantidade; i++) {
            const input = document.getElementById(`passageiro-${itemIndex}-${i}`);
            const nome = input?.value.trim();
            
            if (!nome) {
                todosPreenchidos = false;
                input.style.borderColor = '#dc2626';
                erros.push('Preencha todos os nomes');
            } else if (nome.length < 3) {
                todosPreenchidos = false;
                input.style.borderColor = '#dc2626';
                erros.push('Nome deve ter pelo menos 3 caracteres');
            } else if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(nome)) {
                todosPreenchidos = false;
                input.style.borderColor = '#dc2626';
                erros.push('Nome deve conter apenas letras');
            } else if (nomes.includes(nome.toLowerCase())) {
                todosPreenchidos = false;
                input.style.borderColor = '#dc2626';
                erros.push(`Nome "${nome}" já foi usado`);
            } else {
                input.style.borderColor = '#e0e0e0';
                nomes.push(nome.toLowerCase());
                reservas.push({
                    trip_id: parseInt(item.tripId),
                    passenger_name: nome,
                    price: parseFloat(item.preco),
                    quantity: 1
                });
            }
        }
    });
    
    if (!todosPreenchidos) {
        btnFinalizar.disabled = false;
        btnFinalizar.textContent = 'Finalizar Compra';
        btnFinalizar.style.opacity = '1';
        
        const msg = document.createElement('div');
        msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#dc2626;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;max-width:400px;';
        msg.innerHTML = '<strong>Erros encontrados:</strong><br>' + [...new Set(erros)].join('<br>');
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 4000);
        return;
    }
    
    fetch('/reservar-multiplas', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ reservas })
    })
    .then(res => {
        if (!res.ok) {
            return res.json().then(data => {
                let errorMessage = data.message || `Erro ${res.status}: ${res.statusText}`;
                if (res.status === 401) {
                    if (confirm(errorMessage + '\n\nDeseja fazer login novamente?')) {
                        window.location.href = '/login';
                    }
                    return;
                }
                throw new Error(errorMessage);
            });
        }
        return res.json();
    })
    .then(resp => {
        if (resp && resp.success) {
            const msg = document.createElement('div');
            msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#16a34a;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;';
            msg.innerHTML = `Reservas criadas com sucesso!<br>🎁 Ganhou ${resp.pontos_ganhos} pontos!`;
            document.body.appendChild(msg);
            
            localStorage.removeItem('carrinho');
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1500);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        btnFinalizar.disabled = false;
        btnFinalizar.textContent = 'Finalizar Compra';
        btnFinalizar.style.opacity = '1';
        
        if (!error.message.includes('login')) {
            const msg = document.createElement('div');
            msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#dc2626;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;';
            msg.textContent = 'Erro ao finalizar compra: ' + error.message;
            document.body.appendChild(msg);
            setTimeout(() => msg.remove(), 3000);
        }
    });
}

window.alterarQuantidade = alterarQuantidade;
window.removerItem = removerItem;
window.finalizarCompra = finalizarCompra;

carregarCarrinho();
