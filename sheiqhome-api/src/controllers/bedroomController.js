import Bedroom from '../models/Bedroom.js';
import Hotel from '../models/Hotel.js';

export const getAllBedrooms = async (req, res) => {
  try {
    const bedrooms = await Bedroom.getAll();
    res.json({
      success: true,
      data: bedrooms,
      count: bedrooms.length
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const getBedroomById = async (req, res) => {
  try {
    const { id } = req.params;
    const bedroom = await Bedroom.findById(id);

    if (!bedroom) {
      return res.status(404).json({
        success: false,
        error: 'Bedroom not found'
      });
    }

    res.json({
      success: true,
      data: bedroom
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const getBedroomsByHotelAndStatus = async (req, res) => {
  try {
    const { hotelId } = req.params;
    const { status_id } = req.query;

    if (!status_id || isNaN(status_id)) {
      return res.status(400).json({
        success: false,
        error: 'status_id parameter is required and must be a number'
      });
    }

    const hotel = await Hotel.findById(hotelId);
    if (!hotel) {
      return res.status(404).json({
        success: false,
        error: 'Hotel not found'
      });
    }

    const bedrooms = await Bedroom.getByHotelAndStatus(hotelId, parseInt(status_id));
    res.json({
      success: true,
      data: bedrooms
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const createBedroom = async (req, res) => {
  try {
    const { name, description, hotel_id } = req.body;

    if (!name || !hotel_id) {
      return res.status(400).json({
        success: false,
        error: 'Missing required fields: name, hotel_id'
      });
    }

    const bedroom = await Bedroom.create({
      name,
      description: description || null,
      hotel_id
    });

    res.status(201).json({
      success: true,
      data: bedroom,
      message: 'Bedroom created successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const updateBedroom = async (req, res) => {
  try {
    const { id } = req.params;
    const { name, description, hotel_bedroom_status_id } = req.body;

    const bedroom = await Bedroom.findById(id);
    if (!bedroom) {
      return res.status(404).json({
        success: false,
        error: 'Bedroom not found'
      });
    }

    const updatedBedroom = await Bedroom.update(id, {
      name,
      description,
      hotel_bedroom_status_id
    });

    res.json({
      success: true,
      data: updatedBedroom,
      message: 'Bedroom updated successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

export const deleteBedroom = async (req, res) => {
  try {
    const { id } = req.params;

    const bedroom = await Bedroom.findById(id);
    if (!bedroom) {
      return res.status(404).json({
        success: false,
        error: 'Bedroom not found'
      });
    }

    await Bedroom.delete(id);
    res.json({
      success: true,
      message: 'Bedroom deleted successfully'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
};

