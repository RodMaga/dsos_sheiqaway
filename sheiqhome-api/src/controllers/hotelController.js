import Hotel from '../models/Hotel.js';

export const getAllHotels = async (req, res) => {
  try {
    const hotels = await Hotel.getAll();
    res.json({
      success: true,
      data: hotels,
      count: hotels.length
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const getHotelById = async (req, res) => {
  try {
    const { id } = req.params;
    const hotel = await Hotel.findById(id);

    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    res.json({
      success: true,
      data: hotel
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const createHotel = async (req, res) => {
  try {
    const { name, description, address, phone } = req.body;

    if (!name) {
      return res.status(400).json({
        success: false,
        error: 'Hotel name is required'
      });
    }

    const hotel = await Hotel.create({
      name,
      description: description || null,
      address: address || null,
      phone: phone || null
    });

    res.status(201).json({
      success: true,
      data: hotel,
      message: 'Hotel created successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const updateHotel = async (req, res) => {
  try {
    const { id } = req.params;
    const { name, description, address, phone, hotel_status_id, average_rating } = req.body;

    const hotel = await Hotel.findById(id);
    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    const updatedHotel = await Hotel.update(id, {
      name,
      description,
      address,
      phone,
      hotel_status_id,
      average_rating
    });

    res.json({
      success: true,
      data: updatedHotel,
      message: 'Hotel updated successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const deleteHotel = async (req, res) => {
  try {
    const { id } = req.params;

    const hotel = await Hotel.findById(id);
    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    await Hotel.delete(id);
    res.json({
      success: true,
      message: 'Hotel deleted successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const getHotelsByStatus = async (req, res) => {
  try {
    const { status_id } = req.query;

    if (!status_id || isNaN(status_id)) {
      return res.status(400).json({
        success: false,
        error: 'status_id parameter is required and must be a number'
      });
    }

    const hotels = await Hotel.getByStatus(parseInt(status_id));
    res.json({
      success: true,
      data: hotels,
      count: hotels.length,
      filter: { status_id: parseInt(status_id) }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const getActiveHotelsByRating = async (req, res) => {
  try {
    const hotels = await Hotel.getActiveByRating();
    res.json({
      success: true,
      data: hotels,
      count: hotels.length
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const createHotelRating = async (req, res) => {
  try {
    const { hotel_id, user_id, rating } = req.body;

    if (!hotel_id || !user_id || rating === undefined) {
      return res.status(400).json({
        success: false,
        error: 'Missing required fields: hotel_id, user_id, rating'
      });
    }

    if (rating < 1 || rating > 5 || !Number.isInteger(rating)) {
      return res.status(400).json({
        success: false,
        error: 'Rating must be an integer between 1 and 5'
      });
    }

    const hotel = await Hotel.findById(hotel_id);
    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    const ratingResult = await Hotel.createRating(hotel_id, user_id, rating);

    res.status(201).json({
      success: true,
      data: ratingResult,
      message: 'Hotel rating created successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};
