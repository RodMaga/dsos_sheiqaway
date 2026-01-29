import express from 'express';
import * as bedroomController from '../controllers/bedroomController.js';

const router = express.Router();

/**
 * @swagger
 * /api/bedrooms:
 *   get:
 *     summary: Listar todos os quartos
 *     tags:
 *       - Quartos
 *     responses:
 *       200:
 *         description: Lista de quartos
 */
router.get('/', bedroomController.getAllBedrooms);

/**
 * @swagger
 * /api/bedrooms/{id}:
 *   get:
 *     summary: Obter quarto por ID
 *     tags:
 *       - Quartos
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Detalhes do quarto
 */
router.get('/:id', bedroomController.getBedroomById);

/**
 * @swagger
 * /api/bedrooms/hotel/{hotelId}:
 *   get:
 *     summary: Obter quartos de um hotel por status
 *     tags:
 *       - Quartos
 *     parameters:
 *       - in: path
 *         name: hotelId
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID do hotel
 *       - in: query
 *         name: status_id
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID do status do quarto
 *     responses:
 *       200:
 *         description: Quartos do hotel filtrados por status
 */
router.get('/hotel/:hotelId', bedroomController.getBedroomsByHotelAndStatus);

/**
 * @swagger
 * /api/bedrooms:
 *   post:
 *     summary: Criar novo quarto
 *     tags:
 *       - Quartos
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required:
 *               - name
 *             properties:
 *               name:
 *                 type: string
 *               hotel_id:
 *                 type: integer
 *               description:
 *                 type: string
 *     responses:
 *       201:
 *         description: Quarto criado
 */
router.post('/', bedroomController.createBedroom);

/**
 * @swagger
 * /api/bedrooms/{id}:
 *   put:
 *     summary: Atualizar quarto
 *     tags:
 *       - Quartos
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
 *             required:
 *               - name
 *             properties:
 *               name:
 *                 type: string
 *               description:
 *                 type: string
 *               bedroom_status_id:
 *                type: integer
 *     responses:
 *       200:
 *         description: Quarto atualizado
 */
router.put('/:id', bedroomController.updateBedroom);

/**
 * @swagger
 * /api/bedrooms/{id}:
 *   delete:
 *     summary: Deletar quarto
 *     tags:
 *       - Quartos
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Quarto deletado
 */
router.delete('/:id', bedroomController.deleteBedroom);

export default router;
