import { callProcedure } from '../db.js';

export class Bedroom {
  static async findById(id) {
    try {
      const results = await callProcedure('sp_bedroom_get_by_id', [id]);
      if (results.length > 0) {
        return results[0];
      }
      return null;
    } catch (error) {
      console.error('Error finding bedroom by id:', error);
      throw error;
    }
  }

  static async create(data) {
    try {
      const results = await callProcedure('sp_bedroom_insert', [
        data.name,
        data.description || null,
        data.hotel_id
      ]);
      
      if (results.length > 0 && results[0].id) {
        return this.findById(results[0].id);
      }
      return null;
    } catch (error) {
      console.error('Error creating bedroom:', error);
      throw error;
    }
  }

  static async getAll() {
    try {
      const sql = 'SELECT * FROM hotel_bedroom';
      const { executeQuery } = await import('../db.js');
      return await executeQuery(sql);
    } catch (error) {
      console.error('Error getting all bedrooms:', error);
      throw error;
    }
  }

  static async getByHotelAndStatus(hotelId, statusId) {
    try {
      const results = await callProcedure('sp_get_bedrooms_by_hotel_and_status', [hotelId, statusId]);
      return results;
    } catch (error) {
      console.error('Error getting bedrooms by hotel and status:', error);
      throw error;
    }
  }

  static async update(id, data) {
    try {
      const results = await callProcedure('sp_bedroom_update', [
        id,
        data.name,
        data.description || null,
        data.hotel_bedroom_status_id || 1
      ]);
      
      if (results.length > 0) {
        return results[0];
      }
      return null;
    } catch (error) {
      console.error('Error updating bedroom:', error);
      throw error;
    }
  }

  static async delete(id) {
    try {
      await callProcedure('sp_bedroom_delete', [id]);
      return true;
    } catch (error) {
      console.error('Error deleting bedroom:', error);
      throw error;
    }
  }

}

export default Bedroom;
