// carrinho.js - Lógica da página do carrinho
let userPoints = 0;
let enrichedCarrinho = []; // Store the enriched cart with campaign prices

async function carregarCarrinho() {
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
    
    // Show loading indicator
    container.innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <p style="color: #666; font-size: 1.1rem;">⏳ Verificando campanhas e descontos...</p>
        </div>
    `;
    
    try {
        // Fetch user points first
        const pointsRes = await fetch('/api/user-points', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        const pointsData = await pointsRes.json();
        userPoints = pointsData.points || 0;
        
        // Fetch all trips data from external API
        const tripsRes = await fetch('https://vs-gate.dei.isep.ipp.pt:10923/api/viagens');
        const allTrips = await tripsRes.json();
        
        // Enrich cart items with full trip data and apply campaigns
        const enrichedCart = await Promise.all(carrinho.map(async (item) => {
            const tripData = allTrips.find(t => t.id == item.tripId);
            if (!tripData) return item;
            
            // Call campaign API
            try {
                const campaignRes = await fetch('/api/apply-campaign', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        price: parseFloat(item.preco),
                        duration: parseInt(tripData.duracao_min) || 0,
                        airline: tripData.companhia || '',
                        date: tripData.data_partida || new Date().toISOString().split('T')[0]
                    })
                });

                
                if (!campaignRes.ok) {
                    console.warn('Campaign API returned error:', campaignRes.status);
                    throw new Error('Campaign API error');
                }
                
                const campaignData = await campaignRes.json();
                
                console.log(`Campaign applied for item ${item.tripId}:`, {
                    original: campaignData.original_price,
                    final: campaignData.final_price,
                    discount: campaignData.discount_applied
                });
                
                return {
                    ...item,
                    originalPrice: parseFloat(campaignData.original_price),
                    finalPrice: parseFloat(campaignData.final_price),
                    discountApplied: campaignData.discount_applied === true,
                    tripData: tripData
                };
            } catch (err) {
                console.error('Error applying campaign to item:', item.tripId, err);
                return {
                    ...item,
                    originalPrice: parseFloat(item.preco),
                    finalPrice: parseFloat(item.preco),
                    discountApplied: false,
                    tripData: tripData
                };
            }
        }));
        
        enrichedCarrinho = enrichedCart; // Store globally
        renderCarrinho(enrichedCart);
    } catch (error) {
        console.error('Error loading cart:', error);
        userPoints = 0;
        renderCarrinho(carrinho);
    }
}

function renderCarrinho(carrinho) {
    const container = document.getElementById('carrinho-container');
    let html = '';
    let total = 0;
    
    carrinho.forEach((item, index) => {
        // Use finalPrice if available, otherwise use preco
        const itemPrice = item.finalPrice !== undefined ? item.finalPrice : item.preco;
        const subtotal = itemPrice * item.quantidade;
        total += subtotal;
        
        // Build price display HTML
        let priceHTML = '';
        if (item.discountApplied && item.originalPrice !== item.finalPrice) {
            const originalSubtotal = item.originalPrice * item.quantidade;
            const discountPercent = Math.round(((item.originalPrice - item.finalPrice) / item.originalPrice) * 100);
            priceHTML = `
                <div class="item-preco" style="display: flex; flex-direction: column; align-items: flex-end;">
                    <span style="text-decoration: line-through; color: #999; font-size: 0.85em;">${originalSubtotal.toFixed(2)} ${item.moeda}</span>
                    <span style="color: #16a34a; font-weight: bold; font-size: 1.3rem;">${subtotal.toFixed(2)} ${item.moeda}</span>
                    <span style="background: #16a34a; color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; margin-top: 2px;">-${discountPercent}% CAMPANHA</span>
                </div>
            `;
        } else {
            priceHTML = `<div class="item-preco">${subtotal.toFixed(2)} ${item.moeda}</div>`;
        }
        
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
                ${priceHTML}
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
    // Use enriched cart if available, otherwise fall back to localStorage
    const carrinho = enrichedCarrinho.length > 0 ? enrichedCarrinho : JSON.parse(localStorage.getItem('carrinho') || '[]');
    const selectedOption = document.querySelector('input[name="pontos-option"]:checked')?.value;
    const descontoInfo = document.getElementById('desconto-info');
    const descontoLinha = document.getElementById('desconto-linha');
    const precoOriginalLinha = document.getElementById('preco-original-linha');
    const precoFinalLinha = document.getElementById('preco-final-linha');
    
    let total = 0;
    carrinho.forEach(item => {
        // Use finalPrice if available (with campaign discount), otherwise use preco
        const itemPrice = item.finalPrice !== undefined ? item.finalPrice : item.preco;
        total += itemPrice * item.quantidade;
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
    // Update both localStorage and enriched cart
    const carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    
    if (!carrinho[index]) return;
    
    carrinho[index].quantidade += delta;
    
    if (carrinho[index].quantidade <= 0) {
        carrinho.splice(index, 1);
        enrichedCarrinho.splice(index, 1);
    } else if (enrichedCarrinho[index]) {
        // Update quantity in enriched cart too
        enrichedCarrinho[index].quantidade = carrinho[index].quantidade;
    }
    
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    
    // Re-render with existing enriched data if available
    if (enrichedCarrinho.length > 0 && enrichedCarrinho.length === carrinho.length) {
        renderCarrinho(enrichedCarrinho);
    } else {
        carregarCarrinho();
    }
}

function removerItem(index) {
    const carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    
    if (!carrinho[index]) return;
    
    carrinho.splice(index, 1);
    
    // Also remove from enriched cart
    if (enrichedCarrinho[index]) {
        enrichedCarrinho.splice(index, 1);
    }
    
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    
    // Re-render with existing enriched data if available
    if (enrichedCarrinho.length > 0 && enrichedCarrinho.length === carrinho.length) {
        renderCarrinho(enrichedCarrinho);
    } else {
        carregarCarrinho();
    }
}

function finalizarCompra() {
    // Use enriched cart if available, otherwise fall back to localStorage
    const carrinho = enrichedCarrinho.length > 0 ? enrichedCarrinho : JSON.parse(localStorage.getItem('carrinho') || '[]');
    const selectedOption = document.querySelector('input[name="pontos-option"]:checked')?.value;
    
    const reservas = [];
    let todosPreenchidos = true;
    
    carrinho.forEach((item, itemIndex) => {
        for (let i = 0; i < item.quantidade; i++) {
            const input = document.getElementById(`passageiro-${itemIndex}-${i}`);
            const nome = input?.value.trim();
            
            if (!nome) {
                todosPreenchidos = false;
                input.style.borderColor = '#dc2626';
            } else {
                input.style.borderColor = '#e0e0e0';
                // Use finalPrice if available (with campaign discount), otherwise use preco
                const itemPrice = item.finalPrice !== undefined ? item.finalPrice : item.preco;
                reservas.push({
                    trip_id: parseInt(item.tripId),
                    passenger_name: nome,
                    price: parseFloat(itemPrice),
                    quantity: 1
                });
            }
        }
    });
    
    if (!todosPreenchidos) {
        const msg = document.createElement('div');
        msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#dc2626;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;';
        msg.textContent = 'Por favor, preencha o nome de todos os passageiros!';
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 3000);
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
        if (error && error.message && !error.message.includes('login')) {
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
window.atualizarResumo = atualizarResumo;

carregarCarrinho();
