# API Documentation - Sheiqhome

## Visão Geral

A API Sheiqhome é uma RESTful API desenvolvida em Node.js com Express que permite gerenciar hotéis e reservas. A API fornece endpoints para criar, ler, atualizar e deletar hotéis e reservas, com suporte a filtros avançados.

**Base URL:** `http://localhost:3000`
**Versão:** 1.0.0

## Requisitos de Sistema

- Node.js 16+
- npm ou yarn
- 50MB de espaço em disco

## Instalação Rápida

```bash
cd sheiqhome-api
npm install
node server.js
```

## Estrutura de Resposta Padrão

Todas as respostas são em formato JSON.

### Sucesso (HTTP 200, 201)
```json
{
  "success": true,
  "data": { ... },
  "count": 10,
  "message": "Operação concluída com sucesso"
}
```

### Erro (HTTP 400, 404, 500)
```json
{
  "success": false,
  "error": "Descrição do erro"
}
```

## Autenticação

Atualmente, a API não possui autenticação obrigatória. Todos os endpoints são públicos.

Para implementações futuras, pode-se adicionar autenticação JWT ou OAuth 2.0.

---

## HOTELS API

### 1. Listar Todos os Hotéis

```
GET /api/hotels
```

**Parâmetros:** Nenhum

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Memmo Alfama Hotel",
      "city": "Lisboa",
      "stars": 5,
      "description": "Luxury hotel in Alfama",
      "address": "Rua do Crucifixo 21",
      "phone": "+351 21 1000 000",
      "created_at": "2026-01-28T20:25:00.000Z",
      "updated_at": "2026-01-28T20:25:00.000Z"
    }
  ],
  "count": 52
}
```

**Exemplo com cURL:**
```bash
curl -X GET http://localhost:3000/api/hotels
```

**Exemplo com JavaScript:**
```javascript
fetch('http://localhost:3000/api/hotels')
  .then(res => res.json())
  .then(data => console.log(data));
```

---

### 2. Obter Detalhes de um Hotel

```
GET /api/hotels/{id}
```

**Parâmetros:**
- `id` (path parameter, obrigatório): ID do hotel

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Memmo Alfama Hotel",
    "city": "Lisboa",
    "stars": 5,
    "description": "Luxury hotel in Alfama",
    "address": "Rua do Crucifixo 21",
    "phone": "+351 21 1000 000",
    "created_at": "2026-01-28T20:25:00.000Z",
    "updated_at": "2026-01-28T20:25:00.000Z"
  }
}
```

**Respostas de Erro:**
- 404 Not Found: Hotel não encontrado

**Exemplo com cURL:**
```bash
curl -X GET http://localhost:3000/api/hotels/1
```

---

### 3. Filtrar Hotéis por Cidade

```
GET /api/hotels/filter/city?city={cityName}
```

**Parâmetros:**
- `city` (query parameter, obrigatório): Nome da cidade

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Memmo Alfama Hotel",
      "city": "Lisboa",
      "stars": 5,
      ...
    }
  ],
  "count": 3,
  "filter": {
    "city": "Lisboa"
  }
}
```

**Respostas de Erro:**
- 400 Bad Request: Parâmetro city não fornecido

**Exemplo com cURL:**
```bash
curl -X GET "http://localhost:3000/api/hotels/filter/city?city=Lisboa"
```

**Cidades disponíveis:**
Lisboa, Porto, Madrid, Barcelona, Paris, Lyon, Roma, Florença, Berlim, Hamburgo, Amesterdão, Bruxelas, Viena, Praga, Budapeste, Atenas, Copenhaga, Oslo, Estocolmo, etc.

---

### 4. Filtrar Hotéis por Classificação

```
GET /api/hotels/filter/stars?stars={minStars}
```

**Parâmetros:**
- `stars` (query parameter, obrigatório): Número de 1 a 5 (estrelas mínimas)

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Memmo Alfama Hotel",
      "city": "Lisboa",
      "stars": 5,
      ...
    }
  ],
  "count": 15,
  "filter": {
    "minStars": 4
  }
}
```

**Respostas de Erro:**
- 400 Bad Request: Parâmetro stars inválido ou fora do intervalo

**Exemplo com cURL:**
```bash
curl -X GET "http://localhost:3000/api/hotels/filter/stars?stars=4"
```

---

### 5. Criar um Novo Hotel

```
POST /api/hotels
```

**Headers:**
```
Content-Type: application/json
```

**Body (obrigatório):**
```json
{
  "name": "Novo Hotel",
  "city": "Covilhã",
  "stars": 4,
  "description": "Descrição do hotel",
  "address": "Rua 123",
  "phone": "+351 123 456 789"
}
```

**Campos:**
- `name` (string, obrigatório): Nome do hotel
- `city` (string, obrigatório): Cidade
- `stars` (integer, opcional): 1-5 estrelas
- `description` (string, opcional): Descrição
- `address` (string, opcional): Endereço
- `phone` (string, opcional): Telefone

**Resposta de Sucesso (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 53,
    "name": "Novo Hotel",
    "city": "Covilhã",
    "stars": 4,
    "description": "Descrição do hotel",
    "address": "Rua 123",
    "phone": "+351 123 456 789",
    "created_at": "2026-01-28T20:30:00.000Z",
    "updated_at": "2026-01-28T20:30:00.000Z"
  },
  "message": "Hotel created successfully"
}
```

**Respostas de Erro:**
- 400 Bad Request: Nome ou cidade não fornecidos

**Exemplo com cURL:**
```bash
curl -X POST http://localhost:3000/api/hotels \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Novo Hotel",
    "city": "Covilhã",
    "stars": 4,
    "description": "Hotel de teste",
    "address": "Rua de Teste 123",
    "phone": "+351 275 123 456"
  }'
```

**Exemplo com JavaScript:**
```javascript
fetch('http://localhost:3000/api/hotels', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'Novo Hotel',
    city: 'Covilhã',
    stars: 4,
    description: 'Hotel de teste',
    address: 'Rua de Teste 123',
    phone: '+351 275 123 456'
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

---

### 6. Atualizar um Hotel

```
PUT /api/hotels/{id}
```

**Parâmetros:**
- `id` (path parameter, obrigatório): ID do hotel

**Headers:**
```
Content-Type: application/json
```

**Body (campos opcionais):**
```json
{
  "name": "Hotel Atualizado",
  "city": "Lisboa",
  "stars": 5,
  "description": "Descrição atualizada",
  "address": "Novo endereço",
  "phone": "+351 999 999 999"
}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Hotel Atualizado",
    "city": "Lisboa",
    "stars": 5,
    "description": "Descrição atualizada",
    "address": "Novo endereço",
    "phone": "+351 999 999 999",
    "created_at": "2026-01-28T20:25:00.000Z",
    "updated_at": "2026-01-28T20:35:00.000Z"
  },
  "message": "Hotel updated successfully"
}
```

**Respostas de Erro:**
- 404 Not Found: Hotel não encontrado

**Exemplo com cURL:**
```bash
curl -X PUT http://localhost:3000/api/hotels/1 \
  -H "Content-Type: application/json" \
  -d '{"stars": 5}'
```

---

### 7. Deletar um Hotel

```
DELETE /api/hotels/{id}
```

**Parâmetros:**
- `id` (path parameter, obrigatório): ID do hotel

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "message": "Hotel deleted successfully"
}
```

**Respostas de Erro:**
- 404 Not Found: Hotel não encontrado

**Exemplo com cURL:**
```bash
curl -X DELETE http://localhost:3000/api/hotels/53
```

---

## RESERVATIONS API

### 8. Listar Todas as Reservas

```
GET /api/reservations
```

**Parâmetros (query, opcionais):**
- `user_id`: Filtrar por ID do utilizador
- `hotel_id`: Filtrar por ID do hotel
- `status`: Filtrar por status (pending, confirmed, cancelled)

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "hotel_id": 1,
      "passenger_name": "João Silva",
      "check_in": "2026-02-01",
      "check_out": "2026-02-05",
      "price": 450.00,
      "status": "confirmed",
      "booking_reference": "SHQ-ABCD12",
      "user_name": "João Silva",
      "user_email": "joao@example.com",
      "hotel_name": "Memmo Alfama Hotel",
      "hotel_city": "Lisboa",
      "hotel_stars": 5,
      "created_at": "2026-01-28T20:25:00.000Z",
      "updated_at": "2026-01-28T20:25:00.000Z"
    }
  ],
  "count": 5,
  "filters": {}
}
```

**Exemplo com cURL:**
```bash
# Listar todas as reservas
curl -X GET http://localhost:3000/api/reservations

# Com filtro de utilizador
curl -X GET "http://localhost:3000/api/reservations?user_id=1"

# Com filtro de status
curl -X GET "http://localhost:3000/api/reservations?status=confirmed"

# Com múltiplos filtros
curl -X GET "http://localhost:3000/api/reservations?user_id=1&status=confirmed"
```

---

### 9. Obter Detalhes de uma Reserva

```
GET /api/reservations/{id}
```

**Parâmetros:**
- `id` (path parameter, obrigatório): ID da reserva

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "hotel_id": 1,
    "passenger_name": "João Silva",
    "check_in": "2026-02-01",
    "check_out": "2026-02-05",
    "price": 450.00,
    "status": "confirmed",
    "booking_reference": "SHQ-ABCD12",
    "user_name": "João Silva",
    "user_email": "joao@example.com",
    "hotel_name": "Memmo Alfama Hotel",
    "hotel_city": "Lisboa",
    "hotel_stars": 5,
    "created_at": "2026-01-28T20:25:00.000Z",
    "updated_at": "2026-01-28T20:25:00.000Z"
  }
}
```

**Exemplo com cURL:**
```bash
curl -X GET http://localhost:3000/api/reservations/1
```

---

### 10. Obter Reservas de um Utilizador

```
GET /api/reservations/user/{userId}
```

**Parâmetros:**
- `userId` (path parameter, obrigatório): ID do utilizador

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "hotel_id": 1,
      "passenger_name": "João Silva",
      ...
    }
  ],
  "count": 1,
  "user_id": 1
}
```

**Exemplo com cURL:**
```bash
curl -X GET http://localhost:3000/api/reservations/user/1
```

---

### 11. Obter Reservas de um Hotel

```
GET /api/reservations/hotel/{hotelId}
```

**Parâmetros:**
- `hotelId` (path parameter, obrigatório): ID do hotel

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "hotel_id": 1,
      "passenger_name": "João Silva",
      ...
    }
  ],
  "count": 1,
  "hotel_id": 1,
  "hotel_name": "Memmo Alfama Hotel"
}
```

**Exemplo com cURL:**
```bash
curl -X GET http://localhost:3000/api/reservations/hotel/1
```

---

### 12. Criar uma Nova Reserva

```
POST /api/reservations
```

**Headers:**
```
Content-Type: application/json
```

**Body (obrigatório):**
```json
{
  "user_id": 1,
  "hotel_id": 5,
  "passenger_name": "Pedro Silva",
  "check_in": "2026-04-01",
  "check_out": "2026-04-10",
  "price": 750.50,
  "status": "pending"
}
```

**Campos:**
- `user_id` (integer, obrigatório): ID do utilizador
- `hotel_id` (integer, obrigatório): ID do hotel
- `passenger_name` (string, obrigatório): Nome do hóspede (3-100 caracteres)
- `check_in` (string, obrigatório): Data de check-in (YYYY-MM-DD)
- `check_out` (string, obrigatório): Data de check-out (YYYY-MM-DD)
- `price` (number, obrigatório): Valor da reserva (≥ 0)
- `status` (string, opcional): Status da reserva (pending, confirmed, cancelled)

**Resposta de Sucesso (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 6,
    "user_id": 1,
    "hotel_id": 5,
    "passenger_name": "Pedro Silva",
    "check_in": "2026-04-01",
    "check_out": "2026-04-10",
    "price": 750.50,
    "status": "pending",
    "booking_reference": "SHQ-XYZ789",
    "user_name": "João Silva",
    "user_email": "joao@example.com",
    "hotel_name": "Hotel da Música",
    "hotel_city": "Porto",
    "hotel_stars": 4,
    "created_at": "2026-01-28T20:35:00.000Z",
    "updated_at": "2026-01-28T20:35:00.000Z"
  },
  "message": "Reservation created successfully"
}
```

**Respostas de Erro:**
- 400 Bad Request: Parâmetros inválidos ou ausentes
- 404 Not Found: Utilizador ou hotel não encontrado

**Validações:**
- `passenger_name`: Mínimo 3 caracteres, máximo 100 caracteres
- `price`: Deve ser um número não-negativo
- `user_id`: Deve referir um utilizador existente
- `hotel_id`: Deve referir um hotel existente

**Exemplo com cURL:**
```bash
curl -X POST http://localhost:3000/api/reservations \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "hotel_id": 5,
    "passenger_name": "Pedro Silva",
    "check_in": "2026-04-01",
    "check_out": "2026-04-10",
    "price": 750.50,
    "status": "pending"
  }'
```

**Exemplo com JavaScript:**
```javascript
fetch('http://localhost:3000/api/reservations', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    user_id: 1,
    hotel_id: 5,
    passenger_name: "Pedro Silva",
    check_in: "2026-04-01",
    check_out: "2026-04-10",
    price: 750.50,
    status: "pending"
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

---

### 13. Atualizar uma Reserva

```
PUT /api/reservations/{id}
```

**Parâmetros:**
- `id` (path parameter, obrigatório): ID da reserva

**Headers:**
```
Content-Type: application/json
```

**Body (campos opcionais):**
```json
{
  "status": "confirmed",
  "price": 850.00,
  "passenger_name": "Pedro Silva Updated"
}
```

**Campos atualizáveis:**
- `passenger_name`: Nome do hóspede
- `check_in`: Data de check-in
- `check_out`: Data de check-out
- `price`: Valor da reserva
- `status`: Status da reserva

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "hotel_id": 1,
    "passenger_name": "João Silva",
    "check_in": "2026-02-01",
    "check_out": "2026-02-05",
    "price": 500.00,
    "status": "confirmed",
    "booking_reference": "SHQ-ABCD12",
    "user_name": "João Silva",
    "user_email": "joao@example.com",
    "hotel_name": "Memmo Alfama Hotel",
    "hotel_city": "Lisboa",
    "hotel_stars": 5,
    "created_at": "2026-01-28T20:25:00.000Z",
    "updated_at": "2026-01-28T20:40:00.000Z"
  },
  "message": "Reservation updated successfully"
}
```

**Respostas de Erro:**
- 400 Bad Request: Dados inválidos
- 404 Not Found: Reserva não encontrada

**Exemplo com cURL:**
```bash
curl -X PUT http://localhost:3000/api/reservations/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "confirmed", "price": 500.00}'
```

---

### 14. Deletar uma Reserva

```
DELETE /api/reservations/{id}
```

**Parâmetros:**
- `id` (path parameter, obrigatório): ID da reserva

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "message": "Reservation deleted successfully"
}
```

**Respostas de Erro:**
- 404 Not Found: Reserva não encontrada

**Exemplo com cURL:**
```bash
curl -X DELETE http://localhost:3000/api/reservations/1
```

---

## Códigos de Status HTTP

| Código | Significado | Exemplo |
|--------|-------------|---------|
| 200 | OK | GET com sucesso |
| 201 | Created | POST com sucesso (recurso criado) |
| 400 | Bad Request | Parâmetros inválidos ou ausentes |
| 404 | Not Found | Recurso não encontrado |
| 500 | Internal Server Error | Erro não tratado no servidor |

---

## Tratamento de Erros

Todos os erros são retornados em formato JSON:

```json
{
  "success": false,
  "error": "Mensagem descritiva do erro"
}
```

**Exemplos de erros comuns:**

1. **Parâmetros ausentes:**
```json
{
  "success": false,
  "error": "Missing required fields: user_id, hotel_id, passenger_name, check_in, check_out, price"
}
```

2. **Recurso não encontrado:**
```json
{
  "success": false,
  "error": "Hotel not found"
}
```

3. **Validação falhou:**
```json
{
  "success": false,
  "error": "Passenger name must be between 3 and 100 characters"
}
```

---

## Booking Reference

Cada reserva recebe um `booking_reference` único no formato `SHQ-XXXXXX`:
- Prefixo: `SHQ` (Sheiqhome)
- 6 caracteres aleatórios alfanuméricos em maiúsculas
- Exemplo: `SHQ-ABC123`, `SHQ-XYZ789`

---

## Dados Pré-populados

### Utilizadores (5)
```
1. João Silva (joao@example.com)
2. Maria Santos (maria@example.com)
3. Pedro Costa (pedro@example.com)
4. Ana Oliveira (ana@example.com)
5. Carlos Ferreira (carlos@example.com)
```

### Hotéis (52)
Distribuídos por 14 países europeus, com classificações de 2 a 5 estrelas.

### Reservas (5)
Pré-populadas com dados de exemplo para teste.

---

## Limitações Conhecidas

1. Sem autenticação - Todos os endpoints são públicos
2. Sem paginação - Retorna todos os resultados
3. Sem rate limiting - Sem limite de requisições
4. Storage local - Dados armazenados em JSON, não em BD profissional

---

## Roadmap Futuro

- [ ] Autenticação JWT
- [ ] Paginação de resultados
- [ ] Cache com Redis
- [ ] Testes automatizados
- [ ] Integração com gateway de pagamento
- [ ] Notificações por email
- [ ] Logging detalhado

---

## Suporte e Feedback

Para dúvidas ou feedback, consulte a documentação no README.md ou TESTS.md.

---

**Versão:** 1.0.0  
**Última atualização:** 28/01/2026  
**Desenvolvimento:** Projeto DSOS - ISEP
