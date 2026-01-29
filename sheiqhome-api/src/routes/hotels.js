import express from 'express';
import * as hotelController from '../controllers/hotelController.js';

const router = express.Router();

/**
 * @swagger
 * /api/hotels:
 *   get:
 *     summary: Listar todos os hotéis
 *     tags:
 *       - Hotéis
 *     responses:
 *       200:
 *         description: Lista de hotéis
 */
router.get('/', hotelController.getAllHotels);

/**
 * @swagger
 * /api/hotels/{id}:
 *   get:
 *     summary: Obter hotel por ID
 *     tags:
 *       - Hotéis
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Detalhes do hotel
 */
router.get('/:id', hotelController.getHotelById);

/**
 * @swagger
 * /api/hotels/status/filter:
 *   get:
 *     summary: Filtrar hotéis por estado/status
 *     tags:
 *       - Hotéis
 *     parameters:
 *       - in: query
 *         name: status_id
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID do estado do hotel
 *     responses:
 *       200:
 *         description: Hotéis filtrados por estado
 */
router.get('/status/filter', hotelController.getHotelsByStatus);

/**
 * @swagger
 * /api/hotels/active/rating:
 *   get:
 *     summary: Obter hotéis ativos ordenados por avaliação
 *     tags:
 *       - Hotéis
 *     responses:
 *       200:
 *         description: Hotéis ativos ordenados por avaliação (maior para menor)
 */
router.get('/active/rating', hotelController.getActiveHotelsByRating);

/**
 * @swagger
 * /api/hotels:
 *   post:
 *     summary: Criar novo hotel
 *     tags:
 *       - Hotéis
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               name:
 *                 type: string
 *               description:
 *                 type: string
 *               address:
 *                 type: string
 *               phone:
 *                 type: string
 *     responses:
 *       201:
 *         description: Hotel criado
 */
router.post('/', hotelController.createHotel);

/**
 * @swagger
 * /api/hotels/{id}:
 *   put:
 *     summary: Atualizar hotel
 *     tags:
 *       - Hotéis
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               name:
 *                 type: string
 *               description:
 *                 type: string
 *               address:
 *                 type: string
 *               phone:
 *                 type: string
 *     responses:
 *       200:
 *         description: Hotel atualizado
 */
router.put('/:id', hotelController.updateHotel);

/**
 * @swagger
 * /api/hotels/{id}:
 *   delete:
 *     summary: Deletar hotel
 *     tags:
 *       - Hotéis
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Hotel deletado
 */
router.delete('/:id', hotelController.deleteHotel);

/**
 * @swagger
 * /api/hotels/rating/create:
 *   post:
 *     summary: Criar avaliação de hotel
 *     tags:
 *       - Hotéis
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required:
 *               - hotel_id
 *               - user_id
 *               - rating
 *             properties:
 *               hotel_id:
 *                 type: integer
 *               user_id:
 *                 type: integer
 *               rating:
 *                 type: integer
 *                 minimum: 1
 *                 maximum: 5
 *     responses:
 *       201:
 *         description: Avaliação criada
 */
router.post('/rating/create', hotelController.createHotelRating);

export default router;
