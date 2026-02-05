const BudgetAllocation = require('../models/BudgetAllocation');

// @desc    Get all allocations for user
// @route   GET /api/allocations
// @access  Private
exports.getAllocations = async (req, res) => {
    try {
        const allocations = await BudgetAllocation.find({ user: req.user.id })
            .sort({ createdAt: -1 });

        res.status(200).json({
            success: true,
            count: allocations.length,
            data: allocations
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// @desc    Create allocation
// @route   POST /api/allocations
// @access  Private
exports.createAllocation = async (req, res) => {
    try {
        const { name, amount, isPaid } = req.validatedBody;

        const allocation = await BudgetAllocation.create({
            user: req.user.id,
            name,
            amount,
            isPaid: isPaid || false
        });

        res.status(201).json({
            success: true,
            data: allocation
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// @desc    Update allocation
// @route   PUT /api/allocations/:id
// @access  Private
exports.updateAllocation = async (req, res) => {
    try {
        let allocation = await BudgetAllocation.findById(req.params.id);

        if (!allocation) {
            return res.status(404).json({
                success: false,
                message: 'Allocation not found'
            });
        }

        // Check ownership
        if (allocation.user.toString() !== req.user.id) {
            return res.status(403).json({
                success: false,
                message: 'Not authorized to update this allocation'
            });
        }

        allocation = await BudgetAllocation.findByIdAndUpdate(
            req.params.id,
            req.validatedBody,
            { new: true, runValidators: true }
        );

        res.status(200).json({
            success: true,
            data: allocation
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// @desc    Delete allocation
// @route   DELETE /api/allocations/:id
// @access  Private
exports.deleteAllocation = async (req, res) => {
    try {
        const allocation = await BudgetAllocation.findById(req.params.id);

        if (!allocation) {
            return res.status(404).json({
                success: false,
                message: 'Allocation not found'
            });
        }

        // Check ownership
        if (allocation.user.toString() !== req.user.id) {
            return res.status(403).json({
                success: false,
                message: 'Not authorized to delete this allocation'
            });
        }

        await allocation.deleteOne();

        res.status(200).json({
            success: true,
            message: 'Allocation deleted'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// @desc    Toggle allocation paid status
// @route   PATCH /api/allocations/:id/toggle
// @access  Private
exports.togglePaid = async (req, res) => {
    try {
        let allocation = await BudgetAllocation.findById(req.params.id);

        if (!allocation) {
            return res.status(404).json({
                success: false,
                message: 'Allocation not found'
            });
        }

        // Check ownership
        if (allocation.user.toString() !== req.user.id) {
            return res.status(403).json({
                success: false,
                message: 'Not authorized to update this allocation'
            });
        }

        allocation.isPaid = !allocation.isPaid;
        await allocation.save();

        res.status(200).json({
            success: true,
            data: allocation
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};
