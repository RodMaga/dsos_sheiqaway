// carrinho.js - Lógica da página do carrinho
let userPoints = 0;

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
    
    // Fetch user points first
    fetch('/api/user-points', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(res => res.json())
    .then(data => {
        userPoints = data.points || 0;
        renderCarrinho(carrinho);
    })
    .catch(() => {
        userPoints = 0;
        renderCarrinho(carrinho);
    });
}

function renderCarrinho(carrinho) {
    const container = document.getElementById('carrinho-container');
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
    const maxPointsToUse = Math.min(userPoints, total * 10);
    const maxDiscount = maxPointsToUse / 10;
    
    html += `
        <div class="carrinho-resumo">
            <div class="resumo-linha">
                <span>Total de itens:</span>
                <span>${carrinho.reduce((sum, item) => sum + item.quantidade, 0)}</span>
            </div>
            
            <div class="pontos-opcao" style="margin: 1rem 0; padding: 1rem; background: #f9fafb; border-radius: 8px; border: 1px solid #e0e0e0;">
                <h4 style="margin: 0 0 0.75rem 0; font-size: 0.95rem; color: #374151;">Opções de Pontos</h4>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <label style="display: flex; align-items: center; cursor: pointer; padding: 0.5rem; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                        <input type="radio" name="pontos-option" value="receber" checked onchange="atualizarResumo()" style="margin-right: 0.5rem;">
                        <span>🎁 Pontos a receber: <strong style="color: #16a34a;">${pontosReceber} pontos</strong></span>
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer; padding: 0.5rem; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                        <input type="radio" name="pontos-option" value="descontar" onchange="atualizarResumo()" style="margin-right: 0.5rem;">
                        <span>💰 Descontar pontos (Você tem: <strong>${userPoints}</strong> pontos)</span>
                    </label>
                </div>
                <div id="desconto-info" style="display: none; margin-top: 0.75rem; padding: 0.75rem; background: white; border-radius: 6px; border: 1px solid #d1d5db;">
                    <p style="margin: 0 0 0.5rem 0; font-size: 0.85rem; color: #6b7280;">Máximo de desconto disponível: <strong>${maxDiscount.toFixed(2)} ${carrinho[0].moeda}</strong></p>
                    <p style="margin: 0; font-size: 0.85rem; color: #6b7280;">Pontos a usar: <strong id="pontos-usar">${maxPointsToUse}</strong></p>
                </div>
            </div>
            
            <div class="resumo-linha resumo-total" id="preco-original-linha">
                <span>Total:</span>
                <span>${total.toFixed(2)} ${carrinho[0].moeda}</span>
            </div>
            <div class="resumo-linha" id="desconto-linha" style="display: none; color: #dc2626;">
                <span>Desconto:</span>
                <span id="desconto-valor">-0.00 ${carrinho[0].moeda}</span>
            </div>
            <div class="resumo-linha resumo-total" id="preco-final-linha" style="display: none; color: #16a34a; font-size: 1.2rem;">
                <span>Total Final:</span>
                <span id="preco-final">0.00 ${carrinho[0].moeda}</span>
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

function atualizarResumo() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    const selectedOption = document.querySelector('input[name="pontos-option"]:checked')?.value;
    const descontoInfo = document.getElementById('desconto-info');
    const descontoLinha = document.getElementById('desconto-linha');
    const precoOriginalLinha = document.getElementById('preco-original-linha');
    const precoFinalLinha = document.getElementById('preco-final-linha');
    
    let total = 0;
    carrinho.forEach(item => {
        total += item.preco * item.quantidade;
    });
    
    if (selectedOption === 'descontar') {
        // Calculate discount
        const maxPointsToUse = Math.min(userPoints, total * 10);
        const desconto = maxPointsToUse / 10;
        const finalPrice = Math.max(0, total - desconto);
        
        // Show discount information
        descontoInfo.style.display = 'block';
        descontoLinha.style.display = 'flex';
        precoFinalLinha.style.display = 'flex';
        
        document.getElementById('pontos-usar').textContent = maxPointsToUse;
        document.getElementById('desconto-valor').textContent = `-${desconto.toFixed(2)} ${carrinho[0].moeda}`;
        document.getElementById('preco-final').textContent = `${finalPrice.toFixed(2)} ${carrinho[0].moeda}`;
        
        precoOriginalLinha.style.textDecoration = 'line-through';
        precoOriginalLinha.style.opacity = '0.6';
    } else {
        // Hide discount information
        descontoInfo.style.display = 'none';
        descontoLinha.style.display = 'none';
        precoFinalLinha.style.display = 'none';
        precoOriginalLinha.style.textDecoration = 'none';
        precoOriginalLinha.style.opacity = '1';
    }
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
    const selectedOption = document.querySelector('input[name="pontos-option"]:checked')?.value;
    
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
                input.style.borderColor = '#f43f5e';
                erros.push('Preencha todos os nomes');
            } else if (nome.length < 3) {
                todosPreenchidos = false;
                input.style.borderColor = '#f43f5e';
                erros.push('Nome deve ter pelo menos 3 caracteres');
            } else if (!/^[a-zA-ZÀ-ſ\s]+$/.test(nome)) {
                todosPreenchidos = false;
                input.style.borderColor = '#f43f5e';
                erros.push('Nome deve conter apenas letras');
            } else if (nomes.includes(nome.toLowerCase())) {
                todosPreenchidos = false;
                input.style.borderColor = '#f43f5e';
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
        msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#f43f5e;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;max-width:400px;';
        msg.innerHTML = '<strong>Erros encontrados:</strong><br>' + [...new Set(erros)].join('<br>');
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 4000);
        return;
    }
    
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
        msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#f43f5e;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;';
        msg.textContent = 'Erro: ' + error.message;
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 3000);
    });
}

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
                input.style.borderColor = '#f43f5e';
                erros.push('Preencha todos os nomes');
            } else if (nome.length < 3) {
                todosPreenchidos = false;
                input.style.borderColor = '#f43f5e';
                erros.push('Nome deve ter pelo menos 3 caracteres');
            } else if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(nome)) {
                todosPreenchidos = false;
                input.style.borderColor = '#f43f5e';
                erros.push('Nome deve conter apenas letras');
            } else if (nomes.includes(nome.toLowerCase())) {
                todosPreenchidos = false;
                input.style.borderColor = '#f43f5e';
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
        msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#f43f5e;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;max-width:400px;';
        msg.innerHTML = '<strong>Erros encontrados:</strong><br>' + [...new Set(erros)].join('<br>');
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 4000);
        return;
    }
    
    const requestData = {
        reservas,
        usar_pontos: selectedOption === 'descontar'
    };
    
    fetch('/reservar-multiplas', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify(requestData)
    })
    .then(res => {
        if (!res.ok) {
            return res.json().then(data => {
                let errorMessage = data.message || `Erro ${res.status}: ${res.statusText}`;
                if (res.status === 401) {
                    if (confirm(errorMessage + '\n\nDeseja fazer login novamente?')) {
                        window.location.href = '/login';
                    }
                    throw new Error('login_redirect');
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
            
            if (selectedOption === 'descontar') {
                msg.innerHTML = `Reservas criadas com sucesso!<br>💰 Usou ${resp.pontos_usados} pontos (desconto de ${(resp.pontos_usados / 10).toFixed(2)}€)`;
            } else {
                msg.innerHTML = `Reservas criadas com sucesso!<br>🎁 Ganhou ${resp.pontos_ganhos} pontos!`;
            }
            
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
        
        if (error && error.message && !error.message.includes('login')) {
            const msg = document.createElement('div');
            msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#f43f5e;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;';
            msg.textContent = 'Erro ao finalizar compra: ' + error.message;
            document.body.appendChild(msg);
            setTimeout(() => msg.remove(), 3000);
        }
    });
}

window.alterarQuantidade = alterarQuantidade;
window.removerItem = removerItem;
window.finalizarCompra = finalizarCompra;
window.atualizarResumo = atualizarResumo;

carregarCarrinho();
