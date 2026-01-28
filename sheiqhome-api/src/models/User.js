import { getDatabase, updateDatabase, getNextId } from '../database.js';

export class User {
  static create(data) {
    const db = getDatabase();
    const id = getNextId('users');
    const user = {
      id,
      name: data.name,
      email: data.email,
      password: data.password,
      phone: data.phone || null,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };
    db.users.push(user);
    updateDatabase(db);
    return user;
  }

  static findById(id) {
    const db = getDatabase();
    return db.users.find(u => u.id === parseInt(id));
  }

  static findByEmail(email) {
    const db = getDatabase();
    return db.users.find(u => u.email === email);
  }

  static getAll() {
    const db = getDatabase();
    return db.users;
  }

  static update(id, data) {
    const db = getDatabase();
    const user = db.users.find(u => u.id === parseInt(id));
    if (!user) return null;

    if (data.name !== undefined) user.name = data.name;
    if (data.email !== undefined) user.email = data.email;
    if (data.phone !== undefined) user.phone = data.phone;
    user.updated_at = new Date().toISOString();

    updateDatabase(db);
    return user;
  }

  static delete(id) {
    const db = getDatabase();
    db.users = db.users.filter(u => u.id !== parseInt(id));
    updateDatabase(db);
    return true;
  }
}

export default User;
