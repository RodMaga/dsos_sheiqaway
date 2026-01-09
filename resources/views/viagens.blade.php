<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sheiqaway - Viagens</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/style.css', 'resources/js/global.js'])
    <style>
        body {
            background: #f6f8fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2563eb;
        }
        #viagens-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
        }
        .viagem-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 1.5rem 1.2rem;
            transition: box-shadow 0.2s, transform 0.2s;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        .viagem-card:hover {
            box-shadow: 0 4px 24px rgba(37,99,235,0.13);
            transform: translateY(-4px) scale(1.02);
            border-color: #2563eb;
        }
        .viagem-card h3 {
            font-size: 1.2rem;
            color: #2563eb;
            margin-bottom: 0.3rem;
        }
        .viagem-card p {
            margin: 0;
            color: #333;
            font-size: 1rem;
        }
        .viagem-card .btn-reservar {
            margin-top: 0.7rem;
            padding: 0.5rem 1.2rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .viagem-card .btn-reservar:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
    @include('navbar')
    <main style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        <h1 style="color: #000000; margin-bottom: 1rem;">Viagens Disponíveis</h1>
        <div id="viagens-list"></div>
    </main>
    <script>
    fetch('https://vs-gate.dei.isep.ipp.pt:10923/api/viagens')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (!Array.isArray(data)) {
                document.getElementById('viagens-list').innerHTML = '<p>Erro de API: formato inesperado</p>';
                return;
            }
            data.forEach(viagem => {
                html += `<div class="viagem-card">
                    <h3>${viagem.companhia} - ${viagem.origem} → ${viagem.destino}</h3>
                    <p><strong>Partida:</strong> ${new Date(viagem.data_partida).toLocaleString()}</p>
                    <p><strong>Chegada:</strong> ${new Date(viagem.data_chegada).toLocaleString()}</p>
                    <p><strong>Tipo:</strong> ${viagem.tipo}</p>
                    <p><strong>Preço:</strong> ${viagem.preco} ${viagem.moeda}</p>
                    <p><strong>Duração:</strong> ${viagem.duracao_min} min</p>
                    <p><strong>Lugares disponíveis:</strong> ${viagem.lugares_disponiveis}</p>
                    <p><strong>Escala:</strong> ${viagem.escala ? 'Sim' : 'Não'}</p>
                    <button class="btn-reservar" onclick="reservarViagem(${viagem.id}, '${viagem.preco}')">Reservar</button>
                </div>`;
            });
            document.getElementById('viagens-list').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('viagens-list').innerHTML = '<p style="color: red;">Erro ao carregar viagens da API.</p>';
        });

    function reservarViagem(tripId, preco) {
        const nome = prompt('Nome do passageiro:');
        if (!nome) return;
        fetch('/reservar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                trip_id: tripId,
                passenger_name: nome,
                price: preco
            })
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                alert('Reserva criada com sucesso!');
            } else {
                alert('Erro ao reservar: ' + (resp.message || ''));
            }
        })
        .catch(() => alert('Erro ao reservar.'));
    }
    </script>
    <footer style="text-align: center; padding: 1.5rem; color: #666666;">
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>
</body>
</html>
