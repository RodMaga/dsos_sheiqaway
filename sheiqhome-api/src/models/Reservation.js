import { getDatabase, updateDatabase, getNextId } from '../database.js';
import { v4 as uuidv4 } from 'uuid';

function generateBookingReference() {
  const uuid = uuidv4().replace(/-/g, '').substring(0, 6).toUpperCase();
  return `SHQ-${uuid}`;
}

export class Reservation {
  static create(data) {
    const db = getDatabase();
    const id = getNextId('reservations');
    const bookingReference = generateBookingReference();
    
    const reservation = {
      id,
      user_id: parseInt(data.user_id),
      hotel_id: parseInt(data.hotel_id),
      passenger_name: data.passenger_name,
      check_in: data.check_in,
      check_out: data.check_out,
      price: parseFloat(data.price),
      status: data.status || 'pending',
      booking_reference: bookingReference,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };
    
    db.reservations.push(reservation);
    updateDatabase(db);
    return this.findById(id);
  }

  static findById(id) {
    const db = getDatabase();
    const reservation = db.reservations.find(r => r.id === parseInt(id));
    
    if (!reservation) return null;
    
    // Enriquecer com dados do usuário e hotel
    const user = db.users.find(u => u.id === reservation.user_id);
    const hotel = db.hotels.find(h => h.id === reservation.hotel_id);
    
    return {
      ...reservation,
      user_name: user ? user.name : null,
      user_email: user ? user.email : null,
      hotel_name: hotel ? hotel.name : null,
      hotel_city: hotel ? hotel.city : null,
      hotel_stars: hotel ? hotel.stars : null
    };
  }

  static getAll(filters = {}) {
    const db = getDatabase();
    let reservations = db.reservations;

    if (filters.user_id) {
      reservations = reservations.filter(r => r.user_id === parseInt(filters.user_id));
    }
    if (filters.hotel_id) {
      reservations = reservations.filter(r => r.hotel_id === parseInt(filters.hotel_id));
    }
    if (filters.status) {
      reservations = reservations.filter(r => r.status === filters.status);
    }

    // Enriquecer dados
    return reservations
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      .map(res => {
        const user = db.users.find(u => u.id === res.user_id);
        const hotel = db.hotels.find(h => h.id === res.hotel_id);
        return {
          ...res,
          user_name: user ? user.name : null,
          user_email: user ? user.email : null,
          hotel_name: hotel ? hotel.name : null,
          hotel_city: hotel ? hotel.city : null,
          hotel_stars: hotel ? hotel.stars : null
        };
      });
  }

  static getByUserId(userId) {
    const db = getDatabase();
    return db.reservations
      .filter(r => r.user_id === parseInt(userId))
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      .map(res => {
        const user = db.users.find(u => u.id === res.user_id);
        const hotel = db.hotels.find(h => h.id === res.hotel_id);
        return {
          ...res,
          user_name: user ? user.name : null,
          user_email: user ? user.email : null,
          hotel_name: hotel ? hotel.name : null,
          hotel_city: hotel ? hotel.city : null,
          hotel_stars: hotel ? hotel.stars : null
        };
      });
  }

  static getByHotelId(hotelId) {
    const db = getDatabase();
    return db.reservations
      .filter(r => r.hotel_id === parseInt(hotelId))
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      .map(res => {
        const user = db.users.find(u => u.id === res.user_id);
        const hotel = db.hotels.find(h => h.id === res.hotel_id);
        return {
          ...res,
          user_name: user ? user.name : null,
          user_email: user ? user.email : null,
          hotel_name: hotel ? hotel.name : null,
          hotel_city: hotel ? hotel.city : null,
          hotel_stars: hotel ? hotel.stars : null
        };
      });
  }

  static update(id, data) {
    const db = getDatabase();
    const reservation = db.reservations.find(r => r.id === parseInt(id));
    if (!reservation) return null;

    if (data.passenger_name !== undefined) reservation.passenger_name = data.passenger_name;
    if (data.check_in !== undefined) reservation.check_in = data.check_in;
    if (data.check_out !== undefined) reservation.check_out = data.check_out;
    if (data.price !== undefined) reservation.price = parseFloat(data.price);
    if (data.status !== undefined) reservation.status = data.status;
    reservation.updated_at = new Date().toISOString();

    updateDatabase(db);
    return this.findById(id);
  }

  static delete(id) {
    const db = getDatabase();
    db.reservations = db.reservations.filter(r => r.id !== parseInt(id));
    updateDatabase(db);
    return true;
  }
}

export default Reservation;
