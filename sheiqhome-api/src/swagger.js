import swaggerJsdoc from 'swagger-jsdoc';

const options = {
  definition: {
    openapi: '3.0.0',
    info: {
      title: 'Sheiqhome API',
      version: '1.0.0',
      description: 'API REST para gerenciar hotéis e reservas do portal Sheiqhome',
      contact: {
        name: 'DSOS - ISEP',
        email: 'suporte@sheiqhome.pt'
      }
    },
    servers: [
      {
        url: 'http://localhost:3001',
        description: 'Servidor de desenvolvimento'
      }
    ],
    components: {
      schemas: {
        Hotel: {
          type: 'object',
          properties: {
            id: {
              type: 'integer',
              description: 'ID único do hotel'
            },
            name: {
              type: 'string',
              description: 'Nome do hotel'
            },
            city: {
              type: 'string',
              description: 'Cidade onde o hotel está localizado'
            },
            country: {
              type: 'string',
              description: 'País do hotel'
            },
            stars: {
              type: 'integer',
              minimum: 1,
              maximum: 5,
              description: 'Classificação em estrelas (1-5)'
            },
            price_per_night: {
              type: 'number',
              description: 'Preço por noite em EUR'
            },
            address: {
              type: 'string',
              description: 'Endereço completo'
            },
            phone: {
              type: 'string',
              description: 'Telefone do hotel'
            },
            email: {
              type: 'string',
              description: 'Email do hotel'
            },
            description: {
              type: 'string',
              description: 'Descrição do hotel'
            }
          },
          required: ['name', 'city', 'country', 'stars', 'price_per_night']
        },
        User: {
          type: 'object',
          properties: {
            id: {
              type: 'integer',
              description: 'ID único do utilizador'
            },
            name: {
              type: 'string',
              description: 'Nome completo'
            },
            email: {
              type: 'string',
              description: 'Email'
            },
            phone: {
              type: 'string',
              description: 'Telefone'
            },
            country: {
              type: 'string',
              description: 'País de residência'
            }
          },
          required: ['name', 'email']
        },
        Reservation: {
          type: 'object',
          properties: {
            id: {
              type: 'integer',
              description: 'ID único da reserva'
            },
            user_id: {
              type: 'integer',
              description: 'ID do utilizador'
            },
            hotel_id: {
              type: 'integer',
              description: 'ID do hotel'
            },
            passenger_name: {
              type: 'string',
              description: 'Nome do hóspede'
            },
            check_in: {
              type: 'string',
              format: 'date',
              description: 'Data de entrada (YYYY-MM-DD)'
            },
            check_out: {
              type: 'string',
              format: 'date',
              description: 'Data de saída (YYYY-MM-DD)'
            },
            price: {
              type: 'number',
              description: 'Preço total da reserva'
            },
            status: {
              type: 'string',
              enum: ['pending', 'confirmed', 'cancelled'],
              description: 'Estado da reserva'
            },
            booking_reference: {
              type: 'string',
              description: 'Referência única de reserva (SHQ-XXXXXX)'
            },
            user_name: {
              type: 'string',
              description: 'Nome do utilizador (preenchido automaticamente)'
            },
            hotel_name: {
              type: 'string',
              description: 'Nome do hotel (preenchido automaticamente)'
            }
          },
          required: ['user_id', 'hotel_id', 'passenger_name', 'check_in', 'check_out', 'price', 'status']
        },
        Error: {
          type: 'object',
          properties: {
            success: {
              type: 'boolean',
              example: false
            },
            message: {
              type: 'string'
            },
            error: {
              type: 'string'
            }
          }
        }
      }
    },
    tags: [
      {
        name: 'Hotéis',
        description: 'Operações relacionadas com hotéis'
      },
      {
        name: 'Reservas',
        description: 'Operações relacionadas com reservas'
      },
      {
        name: 'Sistema',
        description: 'Endpoints de sistema'
      }
    ]
  },
  apis: ['./src/routes/*.js', './server.js']
};

const swaggerSpec = swaggerJsdoc(options);

export default swaggerSpec;
