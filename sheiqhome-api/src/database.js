import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dataDir = path.join(__dirname, '..', 'data');
const dbFile = path.join(dataDir, 'database.json');

// Criar pasta data se não existir
if (!fs.existsSync(dataDir)) {
  fs.mkdirSync(dataDir, { recursive: true });
}

// Estrutura padrão da base de dados
const defaultDatabase = {
  users: [],
  hotels: [],
  bedrooms: [],
  reservations: [],
  payments: [],
  _counters: {
    users: 0,
    hotels: 0,
    bedrooms: 0,
    reservations: 0,
    payments: 0
  }
};

// Carregar ou criar base de dados
function loadDatabase() {
  try {
    if (fs.existsSync(dbFile)) {
      const data = fs.readFileSync(dbFile, 'utf-8');
      return JSON.parse(data);
    }
  } catch (error) {
    console.error('Erro ao ler base de dados:', error);
  }
  return { ...defaultDatabase };
}

// Salvar base de dados
function saveDatabase(data) {
  try {
    fs.writeFileSync(dbFile, JSON.stringify(data, null, 2), 'utf-8');
  } catch (error) {
    console.error('Erro ao salvar base de dados:', error);
  }
}

// Inicializar base de dados
let db = loadDatabase();

// Se vazio, usar defaults
if (db.users === undefined) {
  db = { ...defaultDatabase };
  saveDatabase(db);
} else if (!db._counters) {
  // Se não tem counters, criar
  db._counters = {
    users: db.users ? db.users.length : 0,
    hotels: db.hotels ? db.hotels.length : 0,
    bedrooms: db.bedrooms ? db.bedrooms.length : 0,
    reservations: db.reservations ? db.reservations.length : 0,
    payments: db.payments ? db.payments.length : 0
  };
  saveDatabase(db);
}

export function initializeDatabase() {
  console.log('✅ Database initialized successfully');
}

export function getDatabase() {
  // Retorna a cópia em memória, NÃO recarrega sempre
  return db;
}

export function updateDatabase(newData) {
  db = newData;
  saveDatabase(newData);
  return db;
}

export function getNextId(table) {
  // Garantir que _counters existe
  if (!db._counters) {
    db._counters = {
      users: db.users ? db.users.length : 0,
      bedrooms: db.bedrooms ? db.bedrooms.length : 0,
      hotels: db.hotels ? db.hotels.length : 0,
      reservations: db.reservations ? db.reservations.length : 0,
      payments: db.payments ? db.payments.length : 0
    };
  }
  
  // Garantir que o contador da tabela existe
  if (!db._counters[table]) {
    db._counters[table] = 0;
  }
  
  // Incrementar e retornar o novo ID
  db._counters[table]++;
  saveDatabase(db);
  return db._counters[table];
}

export default {
  getDatabase,
  updateDatabase,
  getNextId,
  saveDatabase,
  loadDatabase
};

