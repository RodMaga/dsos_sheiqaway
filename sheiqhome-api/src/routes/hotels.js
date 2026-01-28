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
 * /api/hotels/filter/city:
 *   get:
 *     summary: Filtrar hotéis por cidade
 *     tags:
 *       - Hotéis
 *     parameters:
 *       - in: query
 *         name: city
 *         required: true
 *         schema:
 *           type: string
 *         description: Nome da cidade
 *     responses:
 *       200:
 *         description: Hotéis da cidade
 */
router.get('/filter/city', hotelController.filterHotelsByCity);

/**
 * @swagger
 * /api/hotels/filter/stars:
 *   get:
 *     summary: Filtrar hotéis por estrelas
 *     tags:
 *       - Hotéis
 *     parameters:
 *       - in: query
 *         name: stars
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Hotéis com classificação
 */
router.get('/filter/stars', hotelController.filterHotelsByStars);

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
 *               city:
 *                 type: string
 *               country:
 *                 type: string
 *               stars:
 *                 type: integer
 *               price_per_night:
 *                 type: number
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

export default router;
