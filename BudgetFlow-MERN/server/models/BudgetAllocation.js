const mongoose = require('mongoose');

const allocationSchema = new mongoose.Schema({
    user: {
        type: mongoose.Schema.Types.ObjectId,
        ref: 'User',
        required: true
    },
    name: {
        type: String,
        required: [true, 'Please provide an allocation name'],
        trim: true,
        maxlength: [100, 'Name cannot exceed 100 characters']
    },
    amount: {
        type: Number,
        required: [true, 'Please provide an amount'],
        min: [0.01, 'Amount must be greater than 0']
    },
    isPaid: {
        type: Boolean,
        default: false
    },
    createdAt: {
        type: Date,
        default: Date.now
    }
});

// Index for faster queries
allocationSchema.index({ user: 1, createdAt: -1 });

module.exports = mongoose.model('BudgetAllocation', allocationSchema);
