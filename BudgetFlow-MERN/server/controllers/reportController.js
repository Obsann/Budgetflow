const Transaction = require('../models/Transaction');

// @desc    Get report data with filters
// @route   GET /api/reports
// @access  Private
exports.getReport = async (req, res) => {
    try {
        const { startDate, endDate, category, type } = req.query;

        let matchQuery = { user: req.user._id };

        // Date filter
        if (startDate || endDate) {
            matchQuery.transactionDate = {};
            if (startDate) matchQuery.transactionDate.$gte = new Date(startDate);
            if (endDate) matchQuery.transactionDate.$lte = new Date(endDate);
        }

        // Category filter
        if (category) matchQuery.category = category;

        // Type filter
        if (type) matchQuery.type = type;

        // Get filtered transactions
        const transactions = await Transaction.find(matchQuery)
            .populate('category', 'name')
            .sort({ transactionDate: -1 });

        // Calculate summary
        const summary = transactions.reduce(
            (acc, t) => {
                if (t.type === 'income') {
                    acc.totalIncome += t.amount;
                } else {
                    acc.totalExpense += t.amount;
                }
                return acc;
            },
            { totalIncome: 0, totalExpense: 0 }
        );

        summary.netBalance = summary.totalIncome - summary.totalExpense;

        // Group by category
        const byCategory = {};
        transactions.forEach(t => {
            const catName = t.category?.name || 'Uncategorized';
            if (!byCategory[catName]) {
                byCategory[catName] = { income: 0, expense: 0 };
            }
            if (t.type === 'income') {
                byCategory[catName].income += t.amount;
            } else {
                byCategory[catName].expense += t.amount;
            }
        });

        res.status(200).json({
            success: true,
            data: {
                transactions,
                summary,
                byCategory,
                count: transactions.length
            }
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// @desc    Export transactions as CSV
// @route   GET /api/reports/export
// @access  Private
exports.exportCSV = async (req, res) => {
    try {
        const { startDate, endDate, category, type } = req.query;

        let matchQuery = { user: req.user._id };

        if (startDate || endDate) {
            matchQuery.transactionDate = {};
            if (startDate) matchQuery.transactionDate.$gte = new Date(startDate);
            if (endDate) matchQuery.transactionDate.$lte = new Date(endDate);
        }

        if (category) matchQuery.category = category;
        if (type) matchQuery.type = type;

        const transactions = await Transaction.find(matchQuery)
            .populate('category', 'name')
            .sort({ transactionDate: -1 });

        // Build CSV
        const headers = ['Date', 'Type', 'Category', 'Amount', 'Description'];
        const rows = transactions.map(t => [
            new Date(t.transactionDate).toISOString().split('T')[0],
            t.type,
            t.category?.name || 'Uncategorized',
            t.amount.toFixed(2),
            `"${(t.description || '').replace(/"/g, '""')}"`
        ]);

        const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');

        res.setHeader('Content-Type', 'text/csv');
        res.setHeader('Content-Disposition', 'attachment; filename=budgetflow_export.csv');
        res.status(200).send(csv);
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};
