import { getDatabase, updateDatabase } from '../database.js';
import Hotel from '../models/Hotel.js';
import User from '../models/User.js';
import Reservation from '../models/Reservation.js';

const hotels = [
  // Portugal
  { name: 'Memmo Alfama Hotel', city: 'Lisboa', stars: 5, description: 'Luxury hotel in Alfama', address: 'Rua do Crucifixo 21', phone: '+351 21 1000 000' },
  { name: 'The Independente Hostel & Suites', city: 'Lisboa', stars: 3, description: 'Budget-friendly hostel', address: 'Rua de São Pedro de Alcântara 81', phone: '+351 21 2342 381' },
  { name: 'Tejo Hotel', city: 'Lisboa', stars: 4, description: 'Riverside hotel', address: 'Rua da Cintura do Porto', phone: '+351 21 8844 008' },
  { name: 'The Yeatman', city: 'Porto', stars: 5, description: 'Wine-themed luxury hotel', address: 'Rua do Choupelo 250', phone: '+351 22 0133 600' },
  { name: 'Hotel da Música', city: 'Porto', stars: 4, description: 'Modern design hotel', address: 'Av. da Boavista 1016', phone: '+351 22 6074 700' },
  { name: 'Ribeira Tejo Hotel', city: 'Porto', stars: 3, description: 'Historic neighborhood hotel', address: 'Rua da Ribeira 100', phone: '+351 22 2081 100' },

  // Spain
  { name: 'Plaza Mayor Hotel', city: 'Madrid', stars: 4, description: 'Central Madrid location', address: 'Plaza Mayor 1', phone: '+34 91 542 7600' },
  { name: 'Ritz Madrid', city: 'Madrid', stars: 5, description: 'Historic luxury hotel', address: 'Plaza de la Lealtad 5', phone: '+34 91 701 6767' },
  { name: 'Prado Hotel', city: 'Madrid', stars: 3, description: 'Near museums', address: 'Calle del Prado 20', phone: '+34 91 527 2800' },
  { name: 'Barcelona Princess Hotel', city: 'Barcelona', stars: 4, description: 'Modern Barcelona hotel', address: 'Av. Diagonal 1', phone: '+34 93 2272 760' },
  { name: 'Sixtyfive Hotel', city: 'Barcelona', stars: 5, description: 'Beachfront luxury', address: 'Passeig Marítim 65', phone: '+34 93 2216 500' },
  { name: 'Plaça Reial Hotel', city: 'Barcelona', stars: 3, description: 'Gothic Quarter location', address: 'Plaça Reial 1', phone: '+34 93 3188 300' },
  { name: 'Sevilla Plaza Hotel', city: 'Sevilha', stars: 4, description: 'Near Cathedral', address: 'Plaza de la Catedral 1', phone: '+34 95 4224 200' },
  { name: 'Valência Beach Resort', city: 'Valência', stars: 5, description: 'Beachfront resort', address: 'Av. del Saler 1', phone: '+34 96 3716 000' },

  // France
  { name: 'Le Marais Hotel', city: 'Paris', stars: 4, description: 'Historic Marais district', address: 'Rue de Turenne 50', phone: '+33 1 4142 7600' },
  { name: 'Ritz Paris', city: 'Paris', stars: 5, description: 'Iconic luxury hotel', address: 'Place Vendôme 15', phone: '+33 1 4316 3000' },
  { name: 'Latin Quarter Hotel', city: 'Paris', stars: 3, description: 'Student area location', address: 'Rue Mouffetard 80', phone: '+33 1 4354 5600' },
  { name: 'Sofitel Lyon', city: 'Lyon', stars: 4, description: 'Modern comfort hotel', address: 'Quai Saint-Antoine 1', phone: '+33 4 7242 7000' },
  { name: 'Marseille Port Hotel', city: 'Marselha', stars: 3, description: 'Waterfront location', address: 'Quai des Belges 1', phone: '+33 4 9191 1100' },

  // Italy
  { name: 'The Westin Excelsior', city: 'Roma', stars: 5, description: 'Luxury near Vatican', address: 'Via Veneto 125', phone: '+39 06 47 081' },
  { name: 'Hotel Artemide', city: 'Roma', stars: 4, description: 'Rooftop terrace hotel', address: 'Via Vittorio Colonna 34', phone: '+39 06 3837 3911' },
  { name: 'Roma Budget Inn', city: 'Roma', stars: 2, description: 'Budget accommodation', address: 'Via dei Coronari 100', phone: '+39 06 6880 2424' },
  { name: 'Continentale Firenze', city: 'Florença', stars: 5, description: 'Luxury overlooking Arno', address: 'Vicolo dell\'Oro 6', phone: '+39 55 27262' },
  { name: 'Firenze Hotel', city: 'Florença', stars: 4, description: 'Historic Florence hotel', address: 'Piazza della Signoria 15', phone: '+39 55 2398 111' },
  { name: 'Milano Marriott', city: 'Milão', stars: 4, description: 'Modern Milan hotel', address: 'Via Masaccio 19', phone: '+39 02 8841 0000' },

  // Germany
  { name: 'Adlon Kempinski', city: 'Berlim', stars: 5, description: 'Historic luxury hotel', address: 'Unter den Linden 77', phone: '+49 30 2261 0' },
  { name: 'Berlin Hilton', city: 'Berlim', stars: 4, description: 'Central Berlin location', address: 'Gendarmenmarkt 2', phone: '+49 30 2029 0' },
  { name: 'Estrel Hotel', city: 'Berlim', stars: 3, description: 'Entertainment complex hotel', address: 'Sonnenallee 225', phone: '+49 30 6838 0' },
  { name: 'Vier Jahreszeiten Hamburg', city: 'Hamburgo', stars: 5, description: 'Luxury on Alster Lake', address: 'Neuer Jungfernstieg 13', phone: '+49 40 3494 0' },
  { name: 'Main Tower Hotel', city: 'Frankfurt', stars: 4, description: 'Skyscraper hotel', address: 'Mainkai 52', phone: '+49 69 7300 0' },
  { name: 'München Marriott', city: 'Munique', stars: 4, description: 'Central Munich hotel', address: 'Karolinenplatz 4', phone: '+49 89 5486 0' },

  // Netherlands
  { name: 'Amsterdam Marriott', city: 'Amesterdão', stars: 4, description: 'Canal location', address: 'Stadhouderskade 12', phone: '+31 20 607 5555' },
  { name: 'Canal Luxe Amsterdam', city: 'Amesterdão', stars: 5, description: 'Premium canal hotel', address: 'Keizersgracht 384', phone: '+31 20 6244 661' },
  { name: 'Amsterdam Budget', city: 'Amesterdão', stars: 2, description: 'Affordable Amsterdam', address: 'Zeedijk 100', phone: '+31 20 6249 900' },

  // Belgium
  { name: 'Bruxelas Luxury Palace', city: 'Bruxelas', stars: 5, description: 'Luxury near Grand Place', address: 'Place Royale 1', phone: '+32 2 505 5511' },
  { name: 'Bruxelas Grand Hotel', city: 'Bruxelas', stars: 4, description: 'Grand Place location', address: 'Grand Place 1', phone: '+32 2 5017 811' },

  // Austria
  { name: 'Sacher Wien', city: 'Viena', stars: 5, description: 'Historic luxury hotel', address: 'Philharmonikerstraße 4', phone: '+43 1 51456 0' },
  { name: 'Viena Marriott', city: 'Viena', stars: 4, description: 'Modern Viena hotel', address: 'Parkring 12a', phone: '+43 1 515 180' },

  // Czech Republic
  { name: 'Praga Hilton', city: 'Praga', stars: 5, description: 'Luxury Prague hotel', address: 'Pobřežní 1', phone: '+420 2 2484 1111' },
  { name: 'Old Town Square Hotel', city: 'Praga', stars: 4, description: 'Medieval Prague location', address: 'Staroměstské náměstí 1', phone: '+420 2 2222 7111' },

  // Hungary
  { name: 'Thermal Hotel Rudas', city: 'Budapeste', stars: 4, description: 'Spa hotel with thermal baths', address: 'Döbrentei tér 9', phone: '+36 1 3560 333' },
  { name: 'Danubius Hotel', city: 'Budapeste', stars: 5, description: 'Danube riverfront luxury', address: 'Bem rakpart 16', phone: '+36 1 4891 200' },

  // Greece
  { name: 'Atenas Luxury Hotel', city: 'Atenas', stars: 5, description: 'Acropolis view hotel', address: 'Vassilisis Sofias Ave 1', phone: '+30 21 0728 0000' },
  { name: 'Hotel Grande Bretagne', city: 'Atenas', stars: 4, description: 'Historic Athens hotel', address: 'Vasileos Georgiou A 1', phone: '+30 21 0333 0000' },
  { name: 'Salonika Beach Hotel', city: 'Salónica', stars: 3, description: 'Beachfront hotel', address: 'Nikis Avenue 1', phone: '+30 2310 555 555' },

  // Nordic Countries
  { name: 'Copenhagen Royal Hotel', city: 'Copenhaga', stars: 4, description: 'Royal Copenhagen location', address: 'Nyhavn 1', phone: '+45 3393 3500' },
  { name: 'Oslo Grand Hotel', city: 'Oslo', stars: 5, description: 'Luxury Oslo hotel', address: 'Karl Johans gate 31', phone: '+47 2421 1500' },
  { name: 'Stockholm Palace Hotel', city: 'Estocolmo', stars: 4, description: 'Royal Stockholm', address: 'Norrmalm 1', phone: '+46 8 5063 1000' },
  { name: 'Helsinki Modern Hotel', city: 'Helsínquia', stars: 4, description: 'Modern Nordic design', address: 'Aleksanterinkatu 1', phone: '+358 9 1242 4242' },

  // Luxembourg
  { name: 'Luxembourg Grand Hotel', city: 'Luxemburgo', stars: 5, description: 'Luxury Luxembourg', address: 'Rue de la Ronde 1', phone: '+352 29 7575' },

  // Switzerland
  { name: 'Zurich Baur au Lac', city: 'Zurique', stars: 5, description: 'Lakeside luxury', address: 'Talstrasse 1', phone: '+41 44 220 50 20' },
  { name: 'Genebra Noga Hilton', city: 'Genebra', stars: 5, description: 'Lake Geneva luxury', address: 'Quai du Mont-Blanc 19', phone: '+41 22 908 5050' }
];

const users = [
  { name: 'João Silva', email: 'joao@example.com', password: 'pass123', phone: '91234567' },
  { name: 'Maria Santos', email: 'maria@example.com', password: 'pass123', phone: '92345678' },
  { name: 'Pedro Costa', email: 'pedro@example.com', password: 'pass123', phone: '93456789' },
  { name: 'Ana Oliveira', email: 'ana@example.com', password: 'pass123', phone: '94567890' },
  { name: 'Carlos Ferreira', email: 'carlos@example.com', password: 'pass123', phone: '95678901' }
];

function seedDatabase() {
  try {
    const db = getDatabase();
    
    // Check if already seeded
    if (db.hotels && db.hotels.length > 0) {
      console.log(`✅ Database already seeded with ${db.hotels.length} hotels`);
      return;
    }

    console.log('\n🌱 Seeding database...');

    // Seed hotels
    hotels.forEach(hotel => {
      Hotel.create(hotel);
    });
    console.log(`✅ Inserted ${hotels.length} hotels`);

    // Seed users
    users.forEach(user => {
      User.create(user);
    });
    console.log(`✅ Inserted ${users.length} users`);

    // Seed sample reservations
    const sampleReservations = [
      { user_id: 1, hotel_id: 1, passenger_name: 'João Silva', check_in: '2026-02-01', check_out: '2026-02-05', price: 450.00, status: 'confirmed' },
      { user_id: 2, hotel_id: 5, passenger_name: 'Maria Santos', check_in: '2026-02-10', check_out: '2026-02-15', price: 350.00, status: 'pending' },
      { user_id: 3, hotel_id: 10, passenger_name: 'Pedro Costa', check_in: '2026-03-01', check_out: '2026-03-08', price: 600.00, status: 'confirmed' },
      { user_id: 4, hotel_id: 15, passenger_name: 'Ana Oliveira', check_in: '2026-02-20', check_out: '2026-02-25', price: 280.00, status: 'confirmed' },
      { user_id: 5, hotel_id: 20, passenger_name: 'Carlos Ferreira', check_in: '2026-03-15', check_out: '2026-03-20', price: 520.00, status: 'pending' }
    ];

    sampleReservations.forEach(res => {
      Reservation.create(res);
    });
    console.log(`✅ Inserted ${sampleReservations.length} sample reservations`);

    console.log('\n✨ Database seeding completed successfully!\n');
  } catch (error) {
    console.error('❌ Error seeding database:', error.message);
  }
}

export default seedDatabase;
