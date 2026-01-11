// detalhes.js - Lógica da página de detalhes
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

function getTripIdFromURL() {
    const path = window.location.pathname;
    const segments = path.split('/');
    return segments[segments.length - 1];
}

async function loadTripData() {
    const tripId = getTripIdFromURL();
    
    if (!tripId || tripId === 'detalhes') {
        showError('ID da viagem não especificado.');
        return null;
    }
    
    try {
        const response = await fetch('https://vs-gate.dei.isep.ipp.pt:10923/api/viagens');
        if (!response.ok) throw new Error('Erro ao carregar viagens');
        
        const viagens = await response.json();
        const viagem = viagens.find(v => v.id == tripId);
        
        if (!viagem) throw new Error('Viagem não encontrada');
        
        // Buscar lugares disponíveis da API local
        const lugaresResponse = await fetch(`/api/viagens/${tripId}/lugares-disponiveis`);
        const lugaresData = await lugaresResponse.json();
        
        if (lugaresData.success) {
            viagem.lugares_disponiveis = lugaresData.lugares_disponiveis;
        }
        
        return viagem;
    } catch (error) {
        console.error('Erro ao carregar viagem:', error);
        throw error;
    }
}

function showError(message) {
    const container = document.getElementById('details-container');
    container.innerHTML = `
        <div style="text-align: center; padding: 40px;">
            <div style="font-size: 3em; margin-bottom: 20px; color: #dee2e6;">😕</div>
            <h3 style="color: #6c757d; margin-bottom: 15px;">${message}</h3>
            <p style="color: #6c757d; margin-bottom: 25px;">
                A viagem que procura não foi encontrada ou ocorreu um erro.
            </p>
            <a href="/viagens" class="search-button" style="display: inline-block; padding: 12px 24px;">
                ← Voltar à pesquisa
            </a>
        </div>
    `;
}

function renderTripDetails(viagem) {
    const container = document.getElementById('details-container');
    const lugaresLivres = viagem.lugares_disponiveis;
    const duracaoHoras = Math.floor(viagem.duracao_min / 60);
    const duracaoMinutos = viagem.duracao_min % 60;
    const companhia = getAirlineName(viagem.companhia);
    const origem = getCityName(viagem.origem);
    const destino = getCityName(viagem.destino);
    
    let statusColor, statusText;
    if (lugaresLivres === 0) {
        statusColor = '#dc3545';
        statusText = 'ESGOTADO';
    } else if (lugaresLivres < 5) {
        statusColor = '#ffc107';
        statusText = 'POUCOS LUGARES';
    } else {
        statusColor = '#28a745';
        statusText = 'DISPONÍVEL';
    }

    container.innerHTML = `
        <div class="trip-details-grid">
            <div>
                <h2 style="margin-bottom: 5px;">${origem} → ${destino}</h2>
                <p style="color: #6c757d; margin-bottom: 25px;">
                    <strong>${companhia}</strong> • ${new Date(viagem.data_partida).toLocaleDateString('pt-PT')}
                </p>
                
                <div style="background: ${statusColor}15; border-left: 4px solid ${statusColor}; padding: 15px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5em;">${lugaresLivres === 0 ? '⛔' : lugaresLivres < 5 ? '⚡' : '✅'}</span>
                        <div>
                            <strong style="color: ${statusColor};">${statusText}</strong>
                            <div style="font-size: 0.9em; color: #6c757d;">
                                ${lugaresLivres} lugares disponíveis
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h4 style="margin-top: 0; margin-bottom: 15px;">Detalhes da Viagem</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <div style="font-size: 0.9em; color: #6c757d;">Partida</div>
                            <div style="font-weight: 600;">${new Date(viagem.data_partida).toLocaleString('pt-PT')}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.9em; color: #6c757d;">Chegada</div>
                            <div style="font-weight: 600;">${new Date(viagem.data_chegada).toLocaleString('pt-PT')}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.9em; color: #6c757d;">Duração</div>
                            <div style="font-weight: 600;">${duracaoHoras}h ${duracaoMinutos}min</div>
                        </div>
                        <div>
                            <div style="font-size: 0.9em; color: #6c757d;">Tipo</div>
                            <div style="font-weight: 600;">${viagem.tipo}</div>
                        </div>
                    </div>
                </div>
                
                <div class="price-highlight">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 0.9em; color: #6c757d;">Preço</div>
                            <div style="font-size: 2.5em; font-weight: 800; color: #3b82f6;">
                                ${viagem.preco} ${viagem.moeda}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button id="reservar-btn" class="search-button" 
                            style="flex: 1; padding: 15px; font-size: 1.1em;"
                            ${lugaresLivres === 0 ? 'disabled' : ''}>
                        ${lugaresLivres === 0 ? '⛔ Esgotado' : '✅ Adicionar ao Carrinho'}
                    </button>
                    <a href="/viagens" class="secondary-button" 
                       style="flex: 0.5; padding: 15px; text-align: center; display: flex; align-items: center; justify-content: center;">
                        ← Voltar
                    </a>
                </div>
            </div>
            
            <div>
                <div style="background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px;">
                    <h4 style="margin-top: 0;">Informações da Viagem</h4>
                    <div style="margin-bottom: 1rem;">
                        <strong>Origem:</strong> ${origem}
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Destino:</strong> ${destino}
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Companhia:</strong> ${companhia}
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Escala:</strong> ${viagem.escala ? 'Sim' : 'Não'}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    setupReservationButton(viagem, lugaresLivres, companhia, origem, destino);
}

function setupReservationButton(viagem, lugaresLivres, companhia, origem, destino) {
    const btn = document.getElementById('reservar-btn');
    if (!btn || lugaresLivres === 0) return;
    
    btn.addEventListener('click', function() {
        window.viagemTemporaria = { 
            tripId: viagem.id, 
            companhia, 
            origem, 
            destino, 
            preco: viagem.preco, 
            moeda: viagem.moeda 
        };
        document.getElementById('input-quantidade').value = 1;
        document.getElementById('modal-quantidade').classList.add('show');
    });
}

window.fecharModal = function(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

window.confirmarQuantidade = function() {
    const quantidade = parseInt(document.getElementById('input-quantidade').value);
    if (!quantidade || quantidade < 1) return;
    
    let carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    
    const itemExistente = carrinho.find(item => item.tripId === window.viagemTemporaria.tripId);
    if (itemExistente) {
        itemExistente.quantidade = parseInt(itemExistente.quantidade) + quantidade;
    } else {
        carrinho.push({
            ...window.viagemTemporaria,
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
}

async function initPage() {
    try {
        const viagem = await loadTripData();
        
        if (!viagem) {
            showError('Viagem não encontrada.');
            return;
        }
        
        renderTripDetails(viagem);
        
    } catch (error) {
        console.error('Erro ao inicializar página:', error);
        showError('Não foi possível carregar os dados da viagem.');
    }
}

document.addEventListener('DOMContentLoaded', initPage);
