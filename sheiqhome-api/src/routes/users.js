import express from 'express';
import * as userController from '../controllers/userController.js';

const router = express.Router();

/**
 * @swagger
 * /api/users:
 *   get:
 *     summary: Listar todos os utilizadores
 *     tags:
 *       - Utilizadores
 *     responses:
 *       200:
 *         description: Lista de utilizadores
 */
router.get('/', userController.getAllUsers);

/**
 * @swagger
 * /api/users/{id}:
 *   get:
 *     summary: Obter utilizador por ID
 *     tags:
 *       - Utilizadores
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Detalhes do utilizador
 */
router.get('/:id', userController.getUserById);

/**
 * @swagger
 * /api/users/search/email:
 *   get:
 *     summary: Procurar utilizador por email
 *     tags:
 *       - Utilizadores
 *     parameters:
 *       - in: query
 *         name: email
 *         required: true
 *         schema:
 *           type: string
 *     responses:
 *       200:
 *         description: Utilizador encontrado
 */
router.get('/search/email', userController.getUserByEmail);

/**
 * @swagger
 * /api/users/status/filter:
 *   get:
 *     summary: Filtrar utilizadores por status
 *     tags:
 *       - Utilizadores
 *     parameters:
 *       - in: query
 *         name: status_id
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID do status do utilizador
 *     responses:
 *       200:
 *         description: Utilizadores filtrados por status
 */
router.get('/status/filter', userController.getUsersByStatus);

/**
 * @swagger
 * /api/users:
 *   post:
 *     summary: Criar novo utilizador
 *     tags:
 *       - Utilizadores
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               name:
 *                 type: string
 *               email:
 *                 type: string
 *               phone:
 *                 type: string
 *               password:
 *                 type: string
 *     responses:
 *       201:
 *         description: Utilizador criado
 */
router.post('/', userController.createUser);

/**
 * @swagger
 * /api/users/{id}:
 *   put:
 *     summary: Atualizar utilizador
 *     tags:
 *       - Utilizadores
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
 *               email:
 *                 type: string
 *               phone:
 *                 type: string
 *               password:
 *                 type: string
 *     responses:
 *       200:
 *         description: Utilizador atualizado
 */
router.put('/:id', userController.updateUser);

/**
 * @swagger
 * /api/users/{id}:
 *   delete:
 *     summary: Deletar utilizador
 *     tags:
 *       - Utilizadores
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Utilizador deletado
 */
router.delete('/:id', userController.deleteUser);

export default router;
