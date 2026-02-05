const Transaction = require('../models/Transaction');
const Category = require('../models/Category');

// @desc    Get all transactions for user
// @route   GET /api/transactions
// @access  Private
exports.getTransactions = async (req, res) => {
    try {
        const { startDate, endDate, category, type, search } = req.query;

        let query = { user: req.user.id };

        // Date filter
        if (startDate || endDate) {
            query.transactionDate = {};
            if (startDate) query.transactionDate.$gte = new Date(startDate);
            if (endDate) query.transactionDate.$lte = new Date(endDate);
        }

        // Category filter
        if (category) query.category = category;

        // Type filter
        if (type) query.type = type;

        // Search in description
        if (search) {
            query.description = { $regex: search, $options: 'i' };
        }

        const transactions = await Transaction.find(query)
            .populate('category', 'name')
            .sort({ transactionDate: -1 });

        res.status(200).json({
            success: true,
            count: transactions.length,
            data: transactions
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// @desc    Get single transaction
// @route   GET /api/transactions/:id
// @access  Private
exports.getTransaction = async (req, res) => {
    try {
        const transaction = await Transaction.findById(req.params.id)
            .populate('category', 'name');

        if (!transaction) {
            return res.status(404).json({
                success: false,
                message: 'Transaction not found'
            });
        }

        // Check ownership
        if (transaction.user.toString() !== req.user.id) {
            return res.status(403).json({
                success: false,
                message: 'Not authorized to access this transaction'
            });
        }

        res.status(200).json({
            success: true,
            data: transaction
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// @desc    Create transaction
// @route   POST /api/transactions
// @access  Private
exports.createTransaction = async (req, res) => {
    try {
        const { category, amount, type, description, transactionDate } = req.validatedBody;

        const transaction = await Transaction.create({
            user: req.user.id,
            category,
            amount,
            type,
            description,
            transactionDate
        });

        const populatedTransaction = await Transaction.findById(transaction._id)
            .populate('category', 'name');

        res.status(201).json({
            success: true,
            data: populatedTransaction
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// @desc    Update transaction
// @route   PUT /api/transactions/:id
// @access  Private
exports.updateTransaction = async (req, res) => {
    try {
        let transaction = await Transaction.findById(req.params.id);

        if (!transaction) {
            return res.status(404).json({
                success: false,
                message: 'Transaction not found'
            });
        }

        // Check ownership
        if (transaction.user.toString() !== req.user.id) {
            return res.status(403).json({
                success: false,
                message: 'Not authorized to update this transaction'
            });
        }

        transaction = await Transaction.findByIdAndUpdate(
            req.params.id,
            req.validatedBody,
            { new: true, runValidators: true }
        ).populate('category', 'name');

        res.status(200).json({
            success: true,
            data: transaction
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// @desc    Delete transaction
// @route   DELETE /api/transactions/:id
// @access  Private
exports.deleteTransaction = async (req, res) => {
    try {
        const transaction = await Transaction.findById(req.params.id);

        if (!transaction) {
            return res.status(404).json({
                success: false,
                message: 'Transaction not found'
            });
        }

        // Check ownership
        if (transaction.user.toString() !== req.user.id) {
            return res.status(403).json({
                success: false,
                message: 'Not authorized to delete this transaction'
            });
        }

        await transaction.deleteOne();

        res.status(200).json({
            success: true,
            message: 'Transaction deleted'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};
