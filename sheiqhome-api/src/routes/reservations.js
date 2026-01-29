import express from 'express';
import * as reservationController from '../controllers/reservationController.js';

const router = express.Router();

/**
 * @swagger
 * /api/reservations:
 *   get:
 *     summary: Listar todas as reservas
 *     tags:
 *       - Reservas
 *     responses:
 *       200:
 *         description: Lista de reservas
 */
router.get('/', reservationController.getAllReservations);

/**
 * @swagger
 * /api/reservations/{id}:
 *   get:
 *     summary: Obter reserva por ID
 *     tags:
 *       - Reservas
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Detalhes da reserva
 */
router.get('/:id', reservationController.getReservationById);

/**
 * @swagger
 * /api/reservations/hotel/{hotelId}:
 *   get:
 *     summary: Reservas de um hotel
 *     tags:
 *       - Reservas
 *     parameters:
 *       - in: path
 *         name: hotelId
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Reservas do hotel
 */
router.get('/hotel/:hotelId', reservationController.getHotelReservations);

/**
 * @swagger
 * /api/reservations:
 *   post:
 *     summary: Criar nova reserva
 *     tags:
 *       - Reservas
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               bedroom_id:
 *                 type: integer
 *               user_id:
 *                 type: integer
 *               hotel_id:
 *                 type: integer
 *               quantity:
 *                 type: integer
 *               check_in:
 *                 type: string
 *                 format: date-time
 *               check_out:
 *                 type: string
 *                 format: date-time
 *               price:
 *                 type: number
 *     responses:
 *       201:
 *         description: Reserva criada
 */
router.post('/', reservationController.createReservation);

/**
 * @swagger
 * /api/reservations/{id}:
 *   put:
 *     summary: Atualizar reserva
 *     tags:
 *       - Reservas
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
 *               bedroom_id:
 *                 type: integer
 *               quantity:
 *                 type: integer
 *               check_in:
 *                 type: string
 *                 format: date-time
 *               check_out:
 *                 type: string
 *                 format: date-time
 *               price:
 *                 type: number
 *     responses:
 *       200:
 *         description: Reserva atualizada
 */
router.put('/:id', reservationController.updateReservation);

/**
 * @swagger
 * /api/reservations/{id}:
 *   delete:
 *     summary: Deletar reserva
 *     tags:
 *       - Reservas
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Reserva deletada
 */
router.delete('/:id', reservationController.deleteReservation);

export default router;
