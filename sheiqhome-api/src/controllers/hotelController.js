import Hotel from '../models/Hotel.js';

export const getAllHotels = (req, res) => {
  try {
    const hotels = Hotel.getAll();
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

export const getHotelById = (req, res) => {
  try {
    const { id } = req.params;
    const hotel = Hotel.findById(id);

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

export const filterHotelsByCity = (req, res) => {
  try {
    const { city } = req.query;

    if (!city) {
      return res.status(400).json({
        success: false,
        error: 'City parameter is required'
      });
    }

    const hotels = Hotel.filterByCity(city);
    res.json({
      success: true,
      data: hotels,
      count: hotels.length,
      filter: { city }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const filterHotelsByStars = (req, res) => {
  try {
    const { stars } = req.query;

    if (!stars || isNaN(stars) || stars < 1 || stars > 5) {
      return res.status(400).json({
        success: false,
        error: 'Stars parameter must be a number between 1 and 5'
      });
    }

    const hotels = Hotel.filterByStars(parseInt(stars));
    res.json({
      success: true,
      data: hotels,
      count: hotels.length,
      filter: { minStars: parseInt(stars) }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const createHotel = (req, res) => {
  try {
    const { name, city, stars, description, address, phone } = req.body;

    if (!name || !city) {
      return res.status(400).json({
        success: false,
        error: 'Name and city are required'
      });
    }

    const hotel = Hotel.create({
      name,
      city,
      stars: stars ? parseInt(stars) : null,
      description,
      address,
      phone
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

export const updateHotel = (req, res) => {
  try {
    const { id } = req.params;
    const { name, city, stars, description, address, phone } = req.body;

    const hotel = Hotel.findById(id);
    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    const updatedHotel = Hotel.update(id, {
      name,
      city,
      stars: stars ? parseInt(stars) : undefined,
      description,
      address,
      phone
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

export const deleteHotel = (req, res) => {
  try {
    const { id } = req.params;

    const hotel = Hotel.findById(id);
    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    Hotel.delete(id);
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
