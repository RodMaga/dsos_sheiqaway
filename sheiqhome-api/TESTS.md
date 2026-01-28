# Testes da API Sheiqhome

Todos os testes foram realizados no dia 28/01/2026.

## Status da API

✅ **Servidor iniciado**: http://localhost:3000

## Endpoints Testados

### 1. Health Check
**Endpoint:** `GET /api/health`
**Status:** ✅ Funcionando
**Resposta:**
```json
{
  "success": true,
  "message": "Sheiqhome API is running",
  "timestamp": "2026-01-28T20:30:00.000Z"
}
```

### 2. Root Endpoint
**Endpoint:** `GET /`
**Status:** ✅ Funcionando
**Resposta:**
```json
{
  "name": "Sheiqhome API",
  "version": "1.0.0",
  "description": "API REST para gerenciar hotéis e reservas do portal Sheiqhome",
  "endpoints": {
    "health": "/api/health",
    "hotels": "/api/hotels",
    "reservations": "/api/reservations"
  },
  "documentation": "/api/docs"
}
```

## Hotels Endpoints

### 3. Listar Todos os Hotéis
**Endpoint:** `GET /api/hotels`
**Status:** ✅ Funcionando
**Detalhes:**
- Total de hotéis retornados: 52
- Hotéis de diversas cidades europeias
- Classificação: 2 a 5 estrelas

**Exemplo de resposta:**
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
    },
    ...
  ],
  "count": 52
}
```

### 4. Obter Detalhes de um Hotel
**Endpoint:** `GET /api/hotels/:id`
**Status:** ✅ Funcionando
**Exemplo:** `/api/hotels/1` (Memmo Alfama Hotel)
**Resposta:** Hotel individual com todos os detalhes

### 5. Filtrar Hotéis por Cidade
**Endpoint:** `GET /api/hotels/filter/city?city=Lisboa`
**Status:** ✅ Funcionando
**Detalhes:**
- Retorna hotéis filtrados pela cidade especificada
- Exemplo: Lisboa retorna 3 hotéis

**Resposta:**
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
    },
    {
      "id": 2,
      "name": "The Independente Hostel & Suites",
      "city": "Lisboa",
      "stars": 3,
      ...
    },
    {
      "id": 3,
      "name": "Tejo Hotel",
      "city": "Lisboa",
      "stars": 4,
      ...
    }
  ],
  "count": 3,
  "filter": {
    "city": "Lisboa"
  }
}
```

### 6. Filtrar Hotéis por Classificação (Estrelas)
**Endpoint:** `GET /api/hotels/filter/stars?stars=4`
**Status:** ✅ Funcionando
**Detalhes:**
- Retorna hotéis com classificação >= aos valores especificados
- Exemplo: stars=4 retorna todos os hotéis com 4 ou 5 estrelas

**Cidades com 4+ estrelas:**
- Lisboa: 2 hotéis
- Porto: 1 hotel
- Madrid: 2 hotéis
- Barcelona: 2 hotéis
- Paris: 2 hotéis
- Roma: 1 hotel
- Berlim: 2 hotéis
- E mais...

### 7. Criar um Novo Hotel
**Endpoint:** `POST /api/hotels`
**Status:** ✅ Funcionando
**Método:** POST
**Headers:** `Content-Type: application/json`
**Body:**
```json
{
  "name": "Novo Hotel Test",
  "city": "Covilhã",
  "stars": 4,
  "description": "Hotel de teste",
  "address": "Rua de Teste 123",
  "phone": "+351 275 123 456"
}
```
**Resposta:** Hotel criado com ID atribuído automaticamente (HTTP 201)

### 8. Atualizar um Hotel
**Endpoint:** `PUT /api/hotels/:id`
**Status:** ✅ Funcionando
**Body (exemplo para atualizar apenas estrelas):**
```json
{
  "stars": 5
}
```
**Resposta:** Hotel atualizado com novos dados

### 9. Deletar um Hotel
**Endpoint:** `DELETE /api/hotels/:id`
**Status:** ✅ Funcionando
**Resposta:** Confirmação de deleção

## Reservations Endpoints

### 10. Listar Todas as Reservas
**Endpoint:** `GET /api/reservations`
**Status:** ✅ Funcionando
**Detalhes:**
- Total de reservas: 5 (pré-populadas)
- Mostra informações do utilizador e hotel associados

**Estrutura de resposta:**
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
      "booking_reference": "SHQ-ABC123",
      "user_name": "João Silva",
      "user_email": "joao@example.com",
      "hotel_name": "Memmo Alfama Hotel",
      "hotel_city": "Lisboa",
      "hotel_stars": 5,
      "created_at": "2026-01-28T20:25:00.000Z",
      "updated_at": "2026-01-28T20:25:00.000Z"
    },
    ...
  ],
  "count": 5,
  "filters": {}
}
```

### 11. Obter Detalhes de uma Reserva
**Endpoint:** `GET /api/reservations/:id`
**Status:** ✅ Funcionando
**Exemplo:** `/api/reservations/1`
**Resposta:** Reserva individual com detalhes completos

### 12. Obter Reservas de um Utilizador
**Endpoint:** `GET /api/reservations/user/:userId`
**Status:** ✅ Funcionando
**Exemplo:** `/api/reservations/user/1`
**Detalhes:** Retorna todas as reservas do utilizador ID 1

**Resposta:** Array de reservas do utilizador

### 13. Obter Reservas de um Hotel
**Endpoint:** `GET /api/reservations/hotel/:hotelId`
**Status:** ✅ Funcionando
**Exemplo:** `/api/reservations/hotel/1`
**Detalhes:** Retorna todas as reservas do hotel ID 1

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "hotel_id": 1,
      "user_id": 1,
      "passenger_name": "João Silva",
      ...
    }
  ],
  "count": 1,
  "hotel_id": 1,
  "hotel_name": "Memmo Alfama Hotel"
}
```

### 14. Criar uma Nova Reserva
**Endpoint:** `POST /api/reservations`
**Status:** ✅ Funcionando
**Headers:** `Content-Type: application/json`
**Body (Obrigatório):**
```json
{
  "user_id": 1,
  "hotel_id": 5,
  "passenger_name": "Pedro Teste",
  "check_in": "2026-04-01",
  "check_out": "2026-04-10",
  "price": 750.50,
  "status": "pending"
}
```

**Validações:**
- ✅ user_id: Deve existir
- ✅ hotel_id: Deve existir
- ✅ passenger_name: 3-100 caracteres
- ✅ price: Valor ≥ 0
- ✅ status: 'pending', 'confirmed' ou 'cancelled'

**Resposta:** Reserva criada com booking_reference único (HTTP 201)

### 15. Atualizar uma Reserva
**Endpoint:** `PUT /api/reservations/:id`
**Status:** ✅ Funcionando
**Body (exemplo para atualizar status):**
```json
{
  "status": "confirmed",
  "price": 850.00
}
```
**Resposta:** Reserva atualizada com novos dados

### 16. Deletar uma Reserva
**Endpoint:** `DELETE /api/reservations/:id`
**Status:** ✅ Funcionando
**Resposta:** Confirmação de deleção

## Filtros de Reservas

### 17. Listar Reservas com Filtro por Utilizador
**Endpoint:** `GET /api/reservations?user_id=1`
**Status:** ✅ Funcionando
**Resposta:** Apenas reservas do utilizador 1

### 18. Listar Reservas com Filtro por Status
**Endpoint:** `GET /api/reservations?status=confirmed`
**Status:** ✅ Funcionando
**Resposta:** Apenas reservas com status 'confirmed'

### 19. Listar Reservas com Múltiplos Filtros
**Endpoint:** `GET /api/reservations?user_id=1&status=confirmed`
**Status:** ✅ Funcionando
**Resposta:** Reservas do utilizador 1 que estão confirmadas

## Validação de Dados

### Validações Implementadas

✅ **Criação de Reserva:**
- Email único para utilizadores
- Validação de limites de caracteres
- Validação de valores numéricos
- Validação de IDs referenciados

✅ **Filtros:**
- Validação de parâmetros de filtro
- Tratamento de valores inválidos
- Suporte a múltiplos filtros simultâneos

## Erros e Códigos HTTP

| Código | Situação | Exemplo |
|--------|----------|---------|
| 200 | Sucesso em GET/PUT/DELETE | Listagem de hotéis |
| 201 | Recurso criado | POST de hotel/reserva |
| 400 | Parâmetros inválidos | city vazio em filtro |
| 404 | Recurso não encontrado | GET /api/hotels/999 |
| 500 | Erro do servidor | Erro não tratado |

## Persistência de Dados

✅ **Todos os dados são mantidos em arquivo JSON:**
- Localização: `data/database.json`
- Formato: JSON estruturado
- Carregamento automático ao iniciar servidor
- Salvamento automático ao criar/atualizar/deletar dados

## Dados Pré-populados

### Hotéis: 52 hotéis
- **Portugal:** 6 hotéis (Lisboa, Porto)
- **Espanha:** 8 hotéis (Madrid, Barcelona, Sevilha, Valência)
- **França:** 5 hotéis (Paris, Lyon, Marselha)
- **Itália:** 6 hotéis (Roma, Florença, Milão)
- **Alemanha:** 6 hotéis (Berlim, Hamburgo, Frankfurt, Munique)
- **Países Baixos:** 3 hotéis (Amesterdão)
- **Bélgica:** 2 hotéis (Bruxelas)
- **Áustria:** 2 hotéis (Viena)
- **República Checa:** 2 hotéis (Praga)
- **Hungria:** 2 hotéis (Budapeste)
- **Grécia:** 3 hotéis (Atenas, Salónica)
- **Países Nórdicos:** 4 hotéis (Copenhaga, Oslo, Estocolmo, Helsinquia)
- **Luxemburgo:** 1 hotel
- **Suíça:** 2 hotéis (Zurique, Genebra)

### Utilizadores: 5 utilizadores
1. João Silva (joao@example.com)
2. Maria Santos (maria@example.com)
3. Pedro Costa (pedro@example.com)
4. Ana Oliveira (ana@example.com)
5. Carlos Ferreira (carlos@example.com)

### Reservas: 5 reservas pré-populadas
1. João Silva → Memmo Alfama Hotel (Lisboa) - Confirmada
2. Maria Santos → Hotel da Música (Porto) - Pendente
3. Pedro Costa → Barcelona Princess Hotel - Confirmada
4. Ana Oliveira → Le Marais Hotel (Paris) - Confirmada
5. Carlos Ferreira → Sofitel Lyon - Pendente

## Resumo de Conformidade com Enunciado

✅ **API em Node.js:** Desenvolvida com Express.js
✅ **Endpoints de Hotéis:** Implementados (GET, POST, PUT, DELETE, FILTROS)
✅ **Endpoints de Reservas:** Implementados (GET, POST, PUT, DELETE, FILTROS)
✅ **Persistência de Dados:** JSON com carregamento automático
✅ **Base de Dados Complementada:** Hotéis, utilizadores e reservas
✅ **Validações:** Implementadas em todos os endpoints
✅ **Documentação:** Completa com exemplos

## Pasta Separada

A API Node.js está em pasta separada conforme solicitado:
```
dsos_sheiqaway/
├── sheiqhome-api/          ← API Node.js (NOVA PASTA)
│   ├── src/
│   ├── data/
│   ├── server.js
│   ├── package.json
│   └── README.md
├── app/                     ← Projeto Laravel original
├── routes/
└── ... (resto do projeto Laravel)
```

## Conclusão

✅ **Todos os requisitos foram atendidos:**
- API funcional e testada
- Endpoints para gerenciar hotéis e reservas
- Dados persistentes
- Validações implementadas
- Documentação completa

**Pronta para entrega!**
