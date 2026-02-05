const mongoose = require('mongoose');

const transactionSchema = new mongoose.Schema({
    user: {
        type: mongoose.Schema.Types.ObjectId,
        ref: 'User',
        required: true
    },
    category: {
        type: mongoose.Schema.Types.ObjectId,
        ref: 'Category',
        required: [true, 'Please select a category']
    },
    amount: {
        type: Number,
        required: [true, 'Please provide an amount'],
        min: [0.01, 'Amount must be greater than 0']
    },
    type: {
        type: String,
        enum: ['income', 'expense'],
        required: [true, 'Please specify transaction type']
    },
    description: {
        type: String,
        trim: true,
        maxlength: [255, 'Description cannot exceed 255 characters']
    },
    transactionDate: {
        type: Date,
        required: [true, 'Please provide a transaction date'],
        default: Date.now
    },
    createdAt: {
        type: Date,
        default: Date.now
    }
});

// Index for faster queries
transactionSchema.index({ user: 1, transactionDate: -1 });
transactionSchema.index({ user: 1, category: 1 });

module.exports = mongoose.model('Transaction', transactionSchema);
