# Sheiqhome API - Node.js

Uma API REST desenvolvida em Node.js para gerenciar hotéis e reservas do portal Sheiqhome.

## Requisitos

- Node.js 16+
- npm ou yarn

## Instalação

1. Instale as dependências:

```bash
cd sheiqhome-api
npm install
```

## Execução

### Desenvolvimento

```bash
npm run dev
```

O servidor iniciará em `http://localhost:3000`

### Produção

```bash
npm start
```

## Estrutura do Projeto

```
sheiqhome-api/
├── src/
│   ├── controllers/      # Lógica dos endpoints
│   ├── models/           # Modelos de dados (User, Hotel, Reservation)
│   ├── routes/           # Definição das rotas
│   ├── seeders/          # Scripts para popular a BD
│   ├── middleware/       # Middleware customizado
│   └── database.js       # Configuração do banco de dados
├── data/                 # Armazenamento da BD SQLite
├── server.js            # Servidor principal
├── package.json         # Dependências do projeto
└── README.md           # Este arquivo
```

## Base de Dados

A API utiliza SQLite para persistência de dados. O banco de dados é criado automaticamente em `data/sheiqhome.db`.

### Tabelas

- **users**: Utilizadores do sistema
- **hotels**: Hotéis disponíveis
- **reservations**: Reservas de hotéis
- **payments**: Pagamentos das reservas

## API Endpoints

### Hotéis (Públicos)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/hotels` | Listar todos os hotéis |
| GET | `/api/hotels/:id` | Obter detalhes de um hotel |
| GET | `/api/hotels/filter/city?city=Lisboa` | Filtrar hotéis por cidade |
| GET | `/api/hotels/filter/stars?stars=4` | Filtrar hotéis por estrelas mínimas |
| POST | `/api/hotels` | Criar novo hotel |
| PUT | `/api/hotels/:id` | Atualizar hotel |
| DELETE | `/api/hotels/:id` | Deletar hotel |

### Reservas

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/reservations` | Listar todas as reservas |
| GET | `/api/reservations/:id` | Obter detalhes de uma reserva |
| GET | `/api/reservations/user/:userId` | Listar reservas de um utilizador |
| GET | `/api/reservations/hotel/:hotelId` | Listar reservas de um hotel |
| POST | `/api/reservations` | Criar nova reserva |
| PUT | `/api/reservations/:id` | Atualizar reserva |
| DELETE | `/api/reservations/:id` | Deletar reserva |

### Endpoints Especiais

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Informações da API |
| GET | `/api/health` | Verificação de saúde |

## Exemplos de Uso

### Listar todos os hotéis

```bash
curl http://localhost:3000/api/hotels
```

### Filtrar hotéis por cidade

```bash
curl "http://localhost:3000/api/hotels/filter/city?city=Lisboa"
```

### Filtrar hotéis por classificação mínima

```bash
curl "http://localhost:3000/api/hotels/filter/stars?stars=4"
```

### Criar uma nova reserva

```bash
curl -X POST http://localhost:3000/api/reservations \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "hotel_id": 1,
    "passenger_name": "João Silva",
    "check_in": "2026-02-01",
    "check_out": "2026-02-05",
    "price": 450.00
  }'
```

### Listar reservas de um utilizador

```bash
curl http://localhost:3000/api/reservations/user/1
```

### Listar reservas de um hotel

```bash
curl http://localhost:3000/api/reservations/hotel/1
```

### Atualizar uma reserva

```bash
curl -X PUT http://localhost:3000/api/reservations/1 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "confirmed",
    "price": 500.00
  }'
```

### Deletar uma reserva

```bash
curl -X DELETE http://localhost:3000/api/reservations/1
```

## Validações

### Criação de Reserva

- `user_id`: Obrigatório, deve existir na tabela de utilizadores
- `hotel_id`: Obrigatório, deve existir na tabela de hotéis
- `passenger_name`: Obrigatório, entre 3 e 100 caracteres
- `check_in`: Obrigatório, formato data (YYYY-MM-DD)
- `check_out`: Obrigatório, formato data (YYYY-MM-DD)
- `price`: Obrigatório, valor numérico ≥ 0
- `status`: Opcional, deve ser: 'pending', 'confirmed' ou 'cancelled'

### Filtros de Hotéis

- `city`: Nome da cidade
- `stars`: Número entre 1 e 5 (estrelas mínimas)

## Dados de Exemplo

A API vem pré-populada com:

- **60 hotéis** de 27 cidades europeias
- **5 utilizadores** de exemplo
- **5 reservas** de exemplo

## Tratamento de Erros

A API retorna erros em formato JSON:

```json
{
  "success": false,
  "error": "Mensagem de erro"
}
```

Códigos de status HTTP:

- **200 OK**: Sucesso
- **201 Created**: Recurso criado
- **400 Bad Request**: Parâmetros inválidos
- **404 Not Found**: Recurso não encontrado
- **500 Internal Server Error**: Erro no servidor

## Referência Geração de Booking

Cada reserva recebe um `booking_reference` único no formato `SHQ-XXXXXX`, gerado automaticamente.

## Autor

Desenvolvido para o trabalho de DSOS - ISEP

## Licença

MIT
