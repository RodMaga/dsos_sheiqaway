import express from 'express';
import cors from 'cors';
import swaggerUi from 'swagger-ui-express';
import { initializeDatabase } from './src/database.js';
import hotelRoutes from './src/routes/hotels.js';
import reservationRoutes from './src/routes/reservations.js';
import userRoutes from './src/routes/users.js';
import bedroomRoutes from './src/routes/bedrooms.js';
import seedDatabase from './src/seeders/seedDatabase.js';
import swaggerSpec from './src/swagger.js';

const app = express();
const PORT = process.env.PORT || 3000;

// CORS Configuration
const corsOptions = {
  origin: '*',
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization'],
  optionsSuccessStatus: 200
};

// Middleware
app.use(cors(corsOptions));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Initialize database
initializeDatabase();

// Seed database if needed
seedDatabase();

// Swagger documentation
app.use('/api/docs', swaggerUi.serve, swaggerUi.setup(swaggerSpec, {
  customCss: '.swagger-ui .topbar { display: none }',
  customSiteTitle: 'Sheiqhome API - Documentation',
  swaggerOptions: {
    url: `http://localhost:${PORT}/api/swagger.json`,
    urls: [
      {
        url: `http://localhost:${PORT}/api/swagger.json`,
        name: 'Sheiqhome API'
      }
    ]
  }
}));

// Swagger spec endpoint
app.get('/api/swagger.json', (req, res) => {
  res.json(swaggerSpec);
});

// Routes
app.use('/api/hotels', hotelRoutes);
app.use('/api/reservations', reservationRoutes);
app.use('/api/users', userRoutes);
app.use('/api/bedrooms', bedroomRoutes);

// Health check endpoint
app.get('/api/health', (req, res) => {
  res.json({
    success: true,
    message: 'Sheiqhome API is running',
    timestamp: new Date().toISOString()
  });
});

// Root endpoint
app.get('/', (req, res) => {
  res.json({
    name: 'Sheiqhome API',
    version: '1.0.0',
    description: 'API REST para gerenciar hotéis e reservas do portal Sheiqhome',
    endpoints: {
      health: '/api/health',
      hotels: '/api/hotels',
      reservations: '/api/reservations',
      docs: '/api/docs'
    },
    documentation: 'Acesse http://localhost:3000/api/docs para documentação interativa'
  });
});

// 404 handler
app.use((req, res) => {
  res.status(404).json({
    success: false,
    error: 'Endpoint not found'
  });
});

// Error handler
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({
    success: false,
    error: 'Internal server error',
    message: err.message
  });
});

app.listen(PORT, () => {
  console.log(`\n🚀 Sheiqhome API Server running at http://localhost:${PORT}`);
  console.log(`📍 API Documentation: http://localhost:${PORT}/api/docs`);
  console.log(`🏥 Health check: http://localhost:${PORT}/api/health\n`);
});

export default app;
