import { callProcedure } from '../db.js';


function formatDatetimeForMySQL(datetime) {
  // Convert ISO 8601 format (e.g., '2026-01-29T17:19:53.787Z') to MySQL format (YYYY-MM-DD HH:MM:SS)
  if (!datetime) return datetime;
  
  const date = new Date(datetime);
  if (isNaN(date.getTime())) {
    // If it's already in MySQL format or invalid, return as is
    return datetime;
  }
  
  const year = date.getUTCFullYear();
  const month = String(date.getUTCMonth() + 1).padStart(2, '0');
  const day = String(date.getUTCDate()).padStart(2, '0');
  const hours = String(date.getUTCHours()).padStart(2, '0');
  const minutes = String(date.getUTCMinutes()).padStart(2, '0');
  const seconds = String(date.getUTCSeconds()).padStart(2, '0');
  
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

export class Reservation {
  static async findById(id) {
    try {
      const results = await callProcedure('sp_reservation_get_by_id', [id]);
      if (results.length > 0) {
        return results[0];
      }
      return null;
    } catch (error) {
      console.error('Error finding reservation by id:', error);
      throw error;
    }
  }

  static async checkAvailability(bedroomId, checkIn, checkOut) {
    try {
      const results = await callProcedure('sp_check_bedroom_availability', [
        bedroomId,
        formatDatetimeForMySQL(checkIn),
        formatDatetimeForMySQL(checkOut)
      ]);
      return results.length > 0 ? results[0] : null;
    } catch (error) {
      console.error('Error checking bedroom availability:', error);
      throw error;
    }
  }

  static async create(data) {
    try {
      const results = await callProcedure('sp_reservation_insert', [
        data.bedroom_id,
        data.user_id,
        data.hotel_id,
        data.quantity,
        formatDatetimeForMySQL(data.check_in),
        formatDatetimeForMySQL(data.check_out),
        data.price
      ]);
      
      if (results.length > 0 && results[0].id) {
        return this.findById(results[0].id);
      }
      return null;
    } catch (error) {
      console.error('Error creating reservation:', error);
      throw error;
    }
  }

  static async getAll() {
    try {
      const { executeQuery } = await import('../db.js');
      const sql = 'SELECT * FROM hotel_reservation';
      return await executeQuery(sql);
    } catch (error) {
      console.error('Error getting all reservations:', error);
      throw error;
    }
  }

  static async getByHotelId(hotelId) {
    try {
      const results = await callProcedure('sp_get_reservations_by_hotel', [hotelId]);
      return results;
    } catch (error) {
      console.error('Error getting reservations by hotel id:', error);
      throw error;
    }
  }

  static async update(id, data) {
    try {
      // Fetch the current reservation to use existing values for undefined fields
      const currentReservation = await this.findById(id);
      if (!currentReservation) {
        throw new Error('Reservation not found');
      }

      console.log(data.bedroom_id);

      const results = await callProcedure('sp_reservation_update', [
        id,
        data.bedroom_id !== undefined ? data.bedroom_id : currentReservation.bedroom_id,
        data.quantity !== undefined ? data.quantity : currentReservation.quantity,
        formatDatetimeForMySQL(data.check_in !== undefined ? data.check_in : currentReservation.check_in),
        formatDatetimeForMySQL(data.check_out !== undefined ? data.check_out : currentReservation.check_out),
        data.price !== undefined ? data.price : currentReservation.price
      ]);
      
      if (results.length > 0) {
        return results[0];
      }
      return null;
    } catch (error) {
      console.error('Error updating reservation:', error);
      throw error;
    }
  }

  static async delete(id) {
    try {
      await callProcedure('sp_reservation_delete', [id]);
      return true;
    } catch (error) {
      console.error('Error deleting reservation:', error);
      throw error;
    }
  }
}

export default Reservation;
