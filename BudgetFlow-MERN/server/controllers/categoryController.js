const Category = require('../models/Category');

// @desc    Get all categories
// @route   GET /api/categories
// @access  Public
exports.getCategories = async (req, res) => {
    try {
        const categories = await Category.find().sort({ name: 1 });

        res.status(200).json({
            success: true,
            count: categories.length,
            data: categories
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// @desc    Seed default categories
// @route   POST /api/categories/seed
// @access  Public (should be protected in production)
exports.seedCategories = async (req, res) => {
    try {
        const existingCount = await Category.countDocuments();

        if (existingCount > 0) {
            return res.status(200).json({
                success: true,
                message: 'Categories already seeded'
            });
        }

        const { defaultCategories } = require('../models/Category');

        const categories = await Category.insertMany(
            defaultCategories.map(name => ({ name }))
        );

        res.status(201).json({
            success: true,
            message: 'Categories seeded successfully',
            count: categories.length,
            data: categories
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};
