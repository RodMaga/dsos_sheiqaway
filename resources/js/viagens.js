// viagens.js - Lógica da página de viagens
let allViagens = [];
let viagemTemporaria = null;

function decodeHTML(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.innerHTML = text;
    return div.textContent || div.innerText || text;
}

const cityNames = {
    'OPO': 'Porto', 'LIS': 'Lisboa', 'FAR': 'Faro', 'POR': 'Ponta Delgada',
    'LON': 'Londres', 'PAR': 'Paris', 'MAD': 'Madrid', 'BER': 'Berlim',
    'BRU': 'Bruxelas', 'ROM': 'Roma', 'AMS': 'Amesterdão', 'DUB': 'Dublin',
    'NYC': 'Nova Iorque', 'MIA': 'Miami', 'LAX': 'Los Angeles', 'SFO': 'São Francisco',
    'RIO': 'Rio de Janeiro', 'SAO': 'São Paulo', 'BUE': 'Buenos Aires', 'MEX': 'Cidade do México',
    'TOK': 'Tóquio', 'BEI': 'Pequim', 'DUB': 'Dubai', 'SIN': 'Singapura'
};

const airlineNames = {
    'TP': 'TAP Air Portugal', 'RZ': 'Ryanair', 'FP': 'Fly Portugal',
    'AI': 'Air Iberia', 'EU': 'EuroWings', 'BA': 'British Airways',
    'AF': 'Air France', 'LH': 'Lufthansa', 'KL': 'KLM'
};

function getCityName(code) {
    return cityNames[code] || code;
}

function getAirlineName(code) {
    return airlineNames[code] || code;
}

fetch('https://vs-gate.dei.isep.ipp.pt:10923/api/viagens')
    .then(res => res.json())
    .then(data => {
        if (!Array.isArray(data)) {
            document.getElementById('viagens-list').innerHTML = '<p>Erro de API: formato inesperado</p>';
            return;
        }
        allViagens = data;
        setupAutocomplete();
        renderViagens(allViagens);
    })
    .catch(() => {
        document.getElementById('viagens-list').innerHTML = '<p style="color: #666;">Erro ao carregar viagens da API.</p>';
    });

function setupAutocomplete() {
    const origens = [...new Set(allViagens.map(v => getCityName(v.origem)))];
    const destinos = [...new Set(allViagens.map(v => getCityName(v.destino)))];
    
    autocomplete(document.getElementById('filter-origem'), origens);
    autocomplete(document.getElementById('filter-destino'), destinos);
}

function autocomplete(inp, arr) {
    let currentFocus;
    
    inp.addEventListener('input', function() {
        let val = this.value;
        closeAllLists();
        if (!val) { return; }
        currentFocus = -1;
        
        const list = document.createElement('div');
        list.setAttribute('id', this.id + '-autocomplete-list');
        list.setAttribute('class', 'autocomplete-items');
        this.parentNode.appendChild(list);
        
        arr.forEach(item => {
            if (item.toLowerCase().includes(val.toLowerCase())) {
                const div = document.createElement('div');
                div.innerHTML = item.replace(new RegExp(val, 'gi'), (match) => `<strong>${match}</strong>`);
                div.addEventListener('click', function() {
                    inp.value = item;
                    closeAllLists();
                    applyFilters();
                });
                list.appendChild(div);
            }
        });
    });
    
    inp.addEventListener('keydown', function(e) {
        let x = document.getElementById(this.id + '-autocomplete-list');
        if (x) x = x.getElementsByTagName('div');
        if (e.keyCode == 40) {
            currentFocus++;
            addActive(x);
        } else if (e.keyCode == 38) {
            currentFocus--;
            addActive(x);
        } else if (e.keyCode == 13) {
            e.preventDefault();
            if (currentFocus > -1) {
                if (x) x[currentFocus].click();
            } else {
                closeAllLists();
                applyFilters();
            }
        }
    });
    
    function addActive(x) {
        if (!x) return false;
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        x[currentFocus].classList.add('autocomplete-active');
    }
    
    function removeActive(x) {
        for (let i = 0; i < x.length; i++) {
            x[i].classList.remove('autocomplete-active');
        }
    }
    
    function closeAllLists(elmnt) {
        const items = document.getElementsByClassName('autocomplete-items');
        for (let i = 0; i < items.length; i++) {
            if (elmnt != items[i] && elmnt != inp) {
                items[i].parentNode.removeChild(items[i]);
            }
        }
    }
    
    document.addEventListener('click', function(e) {
        closeAllLists(e.target);
    });
}

function applyFilters() {
    const origemFilter = document.getElementById('filter-origem').value.toLowerCase();
    const destinoFilter = document.getElementById('filter-destino').value.toLowerCase();
    const dataFilter = document.getElementById('filter-data').value;
    const precoFilter = parseFloat(document.getElementById('filter-preco').value);
    
    let filtered = allViagens.filter(viagem => {
        const origemNome = getCityName(viagem.origem).toLowerCase();
        const destinoNome = getCityName(viagem.destino).toLowerCase();
        
        if (origemFilter && !origemNome.includes(origemFilter)) return false;
        if (destinoFilter && !destinoNome.includes(destinoFilter)) return false;
        if (dataFilter) {
            const viagemDate = new Date(viagem.data_partida).toISOString().split('T')[0];
            if (viagemDate !== dataFilter) return false;
        }
        if (viagem.preco > precoFilter) return false;
        return true;
    });
    
    renderViagens(filtered);
}

function clearFilters() {
    document.getElementById('filter-origem').value = '';
    document.getElementById('filter-destino').value = '';
    document.getElementById('filter-data').value = '';
    document.getElementById('filter-preco').value = 1000;
    document.getElementById('price-display').textContent = '1000€';
    renderViagens(allViagens);
}

function renderViagens(viagens) {
    let html = '';
    
    if (viagens.length === 0) {
        document.getElementById('viagens-list').innerHTML = '<p style="color: #666; text-align: center;">Nenhuma viagem encontrada com os filtros selecionados.</p>';
        return;
    }
    
    viagens.forEach(viagem => {
        const companhia = getAirlineName(decodeHTML(viagem.companhia || ''));
        const origem = getCityName(decodeHTML(viagem.origem || ''));
        const destino = getCityName(decodeHTML(viagem.destino || ''));
        
        fetch(`/api/viagens/${viagem.id}/lugares-disponiveis`)
            .then(r => r.json())
            .then(data => {
                const card = document.querySelector(`[data-trip-id="${viagem.id}"]`);
                if (card && data.success) {
                    const lugaresSpan = card.querySelector('.lugares-disponiveis');
                    const btnReservar = card.querySelector('.btn-reservar');
                    
                    lugaresSpan.textContent = `${data.lugares_disponiveis} disponíveis`;
                    
                    if (data.lugares_disponiveis === 0) {
                        lugaresSpan.style.color = '#dc3545';
                        btnReservar.disabled = true;
                        btnReservar.textContent = 'Esgotado';
                        btnReservar.style.opacity = '0.5';
                    } else if (data.lugares_disponiveis < 5) {
                        lugaresSpan.style.color = '#ffc107';
                    }
                }
            });
        
        html += `<div class="viagem-card" data-trip-id="${viagem.id}">
            <div class="card-header">
                <h3>${companhia}</h3>
                <div class="route">${origem} → ${destino}</div>
            </div>
            <div class="info-row">
                <span class="info-label">Partida:</span>
                <span class="info-value">${new Date(viagem.data_partida).toLocaleString('pt-PT', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Duração:</span>
                <span class="info-value">${Math.floor(viagem.duracao_min / 60)}h ${viagem.duracao_min % 60}min</span>
            </div>
            <div class="info-row">
                <span class="info-label">Lugares:</span>
                <span class="info-value lugares-disponiveis">Carregando...</span>
            </div>
            <div class="price-tag">${viagem.preco} ${viagem.moeda}</div>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn-reservar" style="flex: 1;" onclick="window.adicionarAoCarrinho(${viagem.id}, '${companhia}', '${origem}', '${destino}', ${viagem.preco}, '${viagem.moeda}')">Adicionar ao Carrinho</button>
                <a href="/detalhes/${viagem.id}" class="btn-detalhes">Ver Detalhes</a>
            </div>
        </div>`;
    });
    document.getElementById('viagens-list').innerHTML = html;
}

function adicionarAoCarrinho(tripId, companhia, origem, destino, preco, moeda) {
    fetch(`/api/viagens/${tripId}/lugares-disponiveis`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || data.lugares_disponiveis === 0) {
                alert('Não há lugares disponíveis para esta viagem.');
                return;
            }
            
            viagemTemporaria = { tripId, companhia, origem, destino, preco, moeda, lugaresDisponiveis: data.lugares_disponiveis };
            document.getElementById('input-quantidade').value = 1;
            document.getElementById('input-quantidade').max = data.lugares_disponiveis;
            document.getElementById('modal-quantidade').classList.add('show');
        })
        .catch(() => alert('Erro ao verificar disponibilidade.'));
}

function fecharModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

function confirmarQuantidade() {
    const quantidade = parseInt(document.getElementById('input-quantidade').value);
    if (!quantidade || quantidade < 1) return;
    
    if (quantidade > viagemTemporaria.lugaresDisponiveis) {
        alert(`Apenas ${viagemTemporaria.lugaresDisponiveis} lugar(es) disponível(eis).`);
        return;
    }
    
    let carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    
    const itemExistente = carrinho.find(item => item.tripId === viagemTemporaria.tripId);
    const quantidadeTotal = itemExistente ? parseInt(itemExistente.quantidade) + quantidade : quantidade;
    
    if (quantidadeTotal > viagemTemporaria.lugaresDisponiveis) {
        alert(`Você já tem ${itemExistente.quantidade} bilhete(s) no carrinho. Máximo disponível: ${viagemTemporaria.lugaresDisponiveis}`);
        return;
    }
    
    if (itemExistente) {
        itemExistente.quantidade = quantidadeTotal;
    } else {
        carrinho.push({
            ...viagemTemporaria,
            quantidade: quantidade
        });
    }
    
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    fecharModal('modal-quantidade');
    
    const msg = document.createElement('div');
    msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#2563eb;color:white;padding:1rem 1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;';
    msg.textContent = `${quantidade} bilhete(s) adicionado(s) ao carrinho!`;
    document.body.appendChild(msg);
    setTimeout(() => msg.remove(), 3000);
    
    atualizarContadorCarrinho();
}

function atualizarContadorCarrinho() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    const total = carrinho.reduce((sum, item) => sum + item.quantidade, 0);
    const badge = document.querySelector('.cart-badge');
    if (badge) badge.textContent = total;
}

window.clearFilters = clearFilters;
window.adicionarAoCarrinho = adicionarAoCarrinho;
window.fecharModal = fecharModal;
window.confirmarQuantidade = confirmarQuantidade;
window.atualizarContadorCarrinho = atualizarContadorCarrinho;

document.getElementById('filter-data').addEventListener('change', applyFilters);
document.getElementById('filter-preco').addEventListener('input', function() {
    document.getElementById('price-display').textContent = this.value + '€';
    applyFilters();
});

atualizarContadorCarrinho();
