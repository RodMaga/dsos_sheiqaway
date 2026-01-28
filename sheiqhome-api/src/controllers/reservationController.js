import Reservation from '../models/Reservation.js';
import Hotel from '../models/Hotel.js';
import User from '../models/User.js';

export const getAllReservations = (req, res) => {
  try {
    const { user_id, hotel_id, status } = req.query;
    const filters = {};

    if (user_id) filters.user_id = parseInt(user_id);
    if (hotel_id) filters.hotel_id = parseInt(hotel_id);
    if (status) filters.status = status;

    const reservations = Reservation.getAll(filters);
    res.json({
      success: true,
      data: reservations,
      count: reservations.length,
      filters: filters
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const getReservationById = (req, res) => {
  try {
    const { id } = req.params;
    const reservation = Reservation.findById(id);

    if (!reservation) {
      return res.status(404).json({
        success: false,
        error: 'Reservation not found'
      });
    }

    res.json({
      success: true,
      data: reservation
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const getUserReservations = (req, res) => {
  try {
    const { userId } = req.params;
    
    const user = User.findById(userId);
    if (!user) {
      return res.status(404).json({
        success: false,
        error: 'User not found'
      });
    }

    const reservations = Reservation.getByUserId(userId);
    res.json({
      success: true,
      data: reservations,
      count: reservations.length,
      user_id: parseInt(userId)
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const getHotelReservations = (req, res) => {
  try {
    const { hotelId } = req.params;
    
    const hotel = Hotel.findById(hotelId);
    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    const reservations = Reservation.getByHotelId(hotelId);
    res.json({
      success: true,
      data: reservations,
      count: reservations.length,
      hotel_id: parseInt(hotelId),
      hotel_name: hotel.name
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const createReservation = (req, res) => {
  try {
    const { user_id, hotel_id, passenger_name, check_in, check_out, price, status } = req.body;

    // Validations
    if (!user_id || !hotel_id || !passenger_name || !check_in || !check_out || !price) {
      return res.status(400).json({
        success: false,
        error: 'Missing required fields: user_id, hotel_id, passenger_name, check_in, check_out, price'
      });
    }

    if (passenger_name.length < 3 || passenger_name.length > 100) {
      return res.status(400).json({
        success: false,
        error: 'Passenger name must be between 3 and 100 characters'
      });
    }

    if (price < 0) {
      return res.status(400).json({
        success: false,
        error: 'Price must be non-negative'
      });
    }

    const user = User.findById(user_id);
    if (!user) {
      return res.status(404).json({
        success: false,
        error: 'User not found'
      });
    }

    const hotel = Hotel.findById(hotel_id);
    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    if (status && !['pending', 'confirmed', 'cancelled'].includes(status)) {
      return res.status(400).json({
        success: false,
        error: 'Invalid status. Must be: pending, confirmed, or cancelled'
      });
    }

    const reservation = Reservation.create({
      user_id,
      hotel_id,
      passenger_name,
      check_in,
      check_out,
      price,
      status: status || 'pending'
    });

    res.status(201).json({
      success: true,
      data: reservation,
      message: 'Reservation created successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const updateReservation = (req, res) => {
  try {
    const { id } = req.params;
    const { passenger_name, check_in, check_out, price, status } = req.body;

    const reservation = Reservation.findById(id);
    if (!reservation) {
      return res.status(404).json({
        success: false,
        error: 'Reservation not found'
      });
    }

    if (passenger_name && (passenger_name.length < 3 || passenger_name.length > 100)) {
      return res.status(400).json({
        success: false,
        error: 'Passenger name must be between 3 and 100 characters'
      });
    }

    if (price !== undefined && price < 0) {
      return res.status(400).json({
        success: false,
        error: 'Price must be non-negative'
      });
    }

    if (status && !['pending', 'confirmed', 'cancelled'].includes(status)) {
      return res.status(400).json({
        success: false,
        error: 'Invalid status. Must be: pending, confirmed, or cancelled'
      });
    }

    const updatedReservation = Reservation.update(id, {
      passenger_name,
      check_in,
      check_out,
      price,
      status
    });

    res.json({
      success: true,
      data: updatedReservation,
      message: 'Reservation updated successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const deleteReservation = (req, res) => {
  try {
    const { id } = req.params;

    const reservation = Reservation.findById(id);
    if (!reservation) {
      return res.status(404).json({
        success: false,
        error: 'Reservation not found'
      });
    }

    Reservation.delete(id);
    res.json({
      success: true,
      message: 'Reservation deleted successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};
