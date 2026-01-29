import Reservation from '../models/Reservation.js';
import Hotel from '../models/Hotel.js';
import User from '../models/User.js';
import Bedroom from '../models/Bedroom.js';

export const getAllReservations = async (req, res) => {
  try {
    const reservations = await Reservation.getAll();
    res.json({
      success: true,
      data: reservations,
      count: reservations.length
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const getReservationById = async (req, res) => {
  try {
    const { id } = req.params;
    const reservation = await Reservation.findById(id);

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

export const getHotelReservations = async (req, res) => {
  try {
    const { hotelId } = req.params;
    
    const hotel = await Hotel.findById(hotelId);
    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    const reservations = await Reservation.getByHotelId(hotelId);
    res.json({
      success: true,
      data: reservations
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const createReservation = async (req, res) => {
  try {
    const { bedroom_id, user_id, hotel_id, quantity, check_in, check_out, price } = req.body;

    // Validations
    if (!bedroom_id || !user_id || !hotel_id || !quantity || !check_in || !check_out || !price) {
      return res.status(400).json({
        success: false,
        error: 'Missing required fields: bedroom_id, user_id, hotel_id, quantity, check_in, check_out, price'
      });
    }

    if (quantity < 1) {
      return res.status(400).json({
        success: false,
        error: 'Quantity must be at least 1'
      });
    }

    if (price < 0) {
      return res.status(400).json({
        success: false,
        error: 'Price must be non-negative'
      });
    }

    const user = await User.findById(user_id);
    if (!user) {
      return res.status(404).json({
        success: false,
        error: 'User not found'
      });
    }

    const hotel = await Hotel.findById(hotel_id);
    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    const bedroom = await Bedroom.findById(bedroom_id);
    if (!bedroom) {
      return res.status(404).json({
        success: false,
        error: 'Bedroom not found'
      });
    }

    // Check bedroom availability
    const availability = await Reservation.checkAvailability(bedroom_id, check_in, check_out);
    console.log('Availability check result:', availability);
    if (availability) {
      return res.status(400).json({
        success: false,
        error: 'Bedroom is not available for the selected dates'
      });
    }

    const reservation = await Reservation.create({
      bedroom_id,
      user_id,
      hotel_id,
      quantity,
      check_in,
      check_out,
      price
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

export const updateReservation = async (req, res) => {
  try {
    const { id } = req.params;
    const { bedroom_id, quantity, check_in, check_out, price, reservation_status_id } = req.body;

    const reservation = await Reservation.findById(id);
    if (!reservation) {
      return res.status(404).json({
        success: false,
        error: 'Reservation not found'
      });
    }

    if (reservation.reservation_status_id !== 3) {
      return res.status(400).json({
        success: false,
        error: 'Reservation invalid. Must be in pending status to update.'
      });
    }

    if (quantity !== undefined && quantity < 1) {
      return res.status(400).json({
        success: false,
        error: 'Quantity must be at least 1'
      });
    }

    if (price !== undefined && price < 0) {
      return res.status(400).json({
        success: false,
        error: 'Price must be non-negative'
      });
    }

    const updatedReservation = await Reservation.update(id, {
      bedroom_id,
      quantity,
      check_in,
      check_out,
      price,
      reservation_status_id
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

export const deleteReservation = async (req, res) => {
  try {
    const { id } = req.params;

    const reservation = await Reservation.findById(id);
    if (!reservation) {
      return res.status(404).json({
        success: false,
        error: 'Reservation not found'
      });
    }

    await Reservation.delete(id);
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
