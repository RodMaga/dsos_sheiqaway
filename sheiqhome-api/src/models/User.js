import { callProcedure } from '../db.js';
import bcrypt from 'bcrypt';

export class User {
  static async create(data) {
    try {
      const hashedPassword = await bcrypt.hash(data.password, 10);
      const results = await callProcedure('sp_user_insert', [
        data.name,
        data.email,
        data.phone || null,
        hashedPassword
      ]);
      
      if (results.length > 0 && results[0].id) {
        return this.findById(results[0].id);
      }
      return null;
    } catch (error) {
      console.error('Error creating user:', error);
      throw error;
    }
  }

  static async findById(id) {
    try {
      const results = await callProcedure('sp_user_get_by_id', [id]);
      return results.length > 0 ? results[0] : null;
    } catch (error) {
      console.error('Error finding user by id:', error);
      throw error;
    }
  }

  static async findByEmail(email) {
    try {
      const results = await callProcedure('sp_user_select_by_email', [email]);
      return results.length > 0 ? results[0] : null;
    } catch (error) {
      console.error('Error finding user by email:', error);
      throw error;
    }
  }

  static async getAll() {
    try {
      const { executeQuery } = await import('../db.js');
      const sql = 'SELECT * FROM user_hotel';
      return await executeQuery(sql);
    } catch (error) {
      console.error('Error getting all users:', error);
      throw error;
    }
  }

  static async getByStatus(statusId) {
    try {
      const results = await callProcedure('sp_user_select_by_status', [statusId]);
      return results;
    } catch (error) {
      console.error('Error getting users by status:', error);
      throw error;
    }
  }

  static async update(id, data) {
    try {
      const hashedPassword = data.password ? await bcrypt.hash(data.password, 10) : null;
      const results = await callProcedure('sp_user_update', [
        id,
        data.name,
        data.email,
        data.phone || null,
        hashedPassword || undefined
      ]);
      
      if (results.length > 0) {
        return results[0];
      }
      return null;
    } catch (error) {
      console.error('Error updating user:', error);
      throw error;
    }
  }

  static async delete(id) {
    try {
      await callProcedure('sp_user_delete', [id]);
      return true;
    } catch (error) {
      console.error('Error deleting user:', error);
      throw error;
    }
  }

}

export default User;
