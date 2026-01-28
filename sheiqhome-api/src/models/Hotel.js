import { getDatabase, updateDatabase, getNextId } from '../database.js';

export class Hotel {
  static create(data) {
    const db = getDatabase();
    const id = getNextId('hotels');
    const hotel = {
      id,
      name: data.name,
      city: data.city,
      stars: data.stars || null,
      description: data.description || null,
      address: data.address || null,
      phone: data.phone || null,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };
    db.hotels.push(hotel);
    updateDatabase(db);
    return hotel;
  }

  static findById(id) {
    const db = getDatabase();
    return db.hotels.find(h => h.id === parseInt(id));
  }

  static getAll() {
    const db = getDatabase();
    return db.hotels.sort((a, b) => {
      if (a.city !== b.city) return a.city.localeCompare(b.city);
      return a.name.localeCompare(b.name);
    });
  }

  static filterByCity(city) {
    const db = getDatabase();
    return db.hotels.filter(h => h.city === city).sort((a, b) => a.name.localeCompare(b.name));
  }

  static filterByStars(stars) {
    const db = getDatabase();
    return db.hotels
      .filter(h => h.stars && h.stars >= stars)
      .sort((a, b) => {
        if (b.stars !== a.stars) return b.stars - a.stars;
        if (a.city !== b.city) return a.city.localeCompare(b.city);
        return a.name.localeCompare(b.name);
      });
  }

  static update(id, data) {
    const db = getDatabase();
    const hotel = db.hotels.find(h => h.id === parseInt(id));
    if (!hotel) return null;

    if (data.name !== undefined) hotel.name = data.name;
    if (data.city !== undefined) hotel.city = data.city;
    if (data.stars !== undefined) hotel.stars = data.stars;
    if (data.description !== undefined) hotel.description = data.description;
    if (data.address !== undefined) hotel.address = data.address;
    if (data.phone !== undefined) hotel.phone = data.phone;
    hotel.updated_at = new Date().toISOString();

    updateDatabase(db);
    return hotel;
  }

  static delete(id) {
    const db = getDatabase();
    db.hotels = db.hotels.filter(h => h.id !== parseInt(id));
    updateDatabase(db);
    return true;
  }
}

export default Hotel;
