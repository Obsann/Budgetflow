const mongoose = require('mongoose');

const categorySchema = new mongoose.Schema({
    name: {
        type: String,
        required: [true, 'Please provide a category name'],
        unique: true,
        trim: true,
        maxlength: [50, 'Category name cannot exceed 50 characters']
    }
});

module.exports = mongoose.model('Category', categorySchema);

// Default categories to seed
module.exports.defaultCategories = [
    'Food',
    'Transportation',
    'Housing',
    'Utilities',
    'Entertainment',
    'Healthcare',
    'Education',
    'Shopping',
    'Income',
    'Other'
];
