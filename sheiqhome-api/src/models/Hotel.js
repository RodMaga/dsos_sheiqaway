import { callProcedure } from '../db.js';

export class Hotel {
  static async findById(id) {
    try {
      const results = await callProcedure('sp_hotel_get_by_id', [id]);
      if (results.length > 0) {
        return {
          ...results[0],
          average_rating: parseFloat(results[0].average_rating) || 0
        };
      }
      return null;
    } catch (error) {
      console.error('Error finding hotel by id:', error);
      throw error;
    }
  }

  static async getAll() {
    try {
      const results = await callProcedure('sp_hotel_get_all', []);
      return results.map(hotel => ({
        ...hotel,
        average_rating: parseFloat(hotel.average_rating) || 0
      }));
    } catch (error) {
      console.error('Error getting all hotels:', error);
      throw error;
    }
  }

  static async create(data) {
    try {
      const results = await callProcedure('sp_hotel_insert', [
        data.name,
        data.description || null,
        data.address || null,
        data.phone || null
      ]);
      
      if (results.length > 0 && results[0].id) {
        return this.findById(results[0].id);
      }
      return null;
    } catch (error) {
      console.error('Error creating hotel:', error);
      throw error;
    }
  }

  static async update(id, data) {
    try {
      const results = await callProcedure('sp_hotel_update', [
        id,
        data.name,
        data.description || null,
        data.address || null,
        data.phone || null,
        data.hotel_status_id || 1
      ]);
      
      if (results.length > 0) {
        return {
          ...results[0],
          average_rating: parseFloat(results[0].average_rating) || 0
        };
      }
      return null;
    } catch (error) {
      console.error('Error updating hotel:', error);
      throw error;
    }
  }

  static async delete(id) {
    try {
      await callProcedure('sp_hotel_delete', [id]);
      return true;
    } catch (error) {
      console.error('Error deleting hotel:', error);
      throw error;
    }
  }

  static async getByStatus(statusId) {
    try {
      const results = await callProcedure('sp_get_hotels_by_status', [statusId]);
      return results.map(hotel => ({
        ...hotel,
        average_rating: parseFloat(hotel.average_rating) || 0
      }));
    } catch (error) {
      console.error('Error getting hotels by status:', error);
      throw error;
    }
  }

  static async getActiveByRating() {
    try {
      const results = await callProcedure('sp_get_active_hotels_by_rating', []);
      return results.map(hotel => ({
        ...hotel,
        average_rating: parseFloat(hotel.average_rating) || 0
      }));
    } catch (error) {
      console.error('Error getting active hotels by rating:', error);
      throw error;
    }
  }

  static async createRating(hotelId, userId, rating) {
    try {
      const results = await callProcedure('sp_hotel_rating_insert', [
        hotelId,
        userId,
        rating
      ]);
      
      if (results.length > 0 && results[0].id) {
        return results[0];
      }
      return null;
    } catch (error) {
      console.error('Error creating hotel rating:', error);
      throw error;
    }
  }
}

export default Hotel;
