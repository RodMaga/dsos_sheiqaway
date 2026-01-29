import mysql from 'mysql2/promise';
import dotenv from 'dotenv';

dotenv.config();

const pool = mysql.createPool({
  host: process.env.DB_HOST || 'localhost',
  port: process.env.DB_PORT || 3306,
  user: process.env.DB_USERNAME || 'root',
  password: process.env.DB_PASSWORD || 'password',
  database: process.env.DB_DATABASE || 'sheiqhome',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  enableKeepAlive: true
});

export async function executeQuery(sql, values = []) {
  try {
    const connection = await pool.getConnection();
    const [results] = await connection.execute(sql, values);
    connection.release();
    return results;
  } catch (error) {
    console.error('Database error:', error);
    throw error;
  }
}

export async function callProcedure(procedureName, params = []) {
  try {
    const connection = await pool.getConnection();
    const placeholders = params.map(() => '?').join(',');
    const sql = `CALL ${procedureName}(${placeholders})`;
    const [results] = await connection.execute(sql, params);
    connection.release();
    return results[0] || [];
  } catch (error) {
    console.error(`Error calling procedure ${procedureName}:`, error);
    throw error;
  }
}

export function getPool() {
  return pool;
}

export default {
  executeQuery,
  callProcedure,
  getPool
};
