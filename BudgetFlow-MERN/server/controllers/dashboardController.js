const Transaction = require('../models/Transaction');
const BudgetAllocation = require('../models/BudgetAllocation');

// @desc    Get dashboard data
// @route   GET /api/dashboard
// @access  Private
exports.getDashboard = async (req, res) => {
    try {
        const userId = req.user._id;

        // Get current month range
        const now = new Date();
        const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);

        // Total income and expenses
        const totals = await Transaction.aggregate([
            { $match: { user: req.user._id } },
            {
                $group: {
                    _id: '$type',
                    total: { $sum: '$amount' }
                }
            }
        ]);

        const totalIncome = totals.find(t => t._id === 'income')?.total || 0;
        const totalExpense = totals.find(t => t._id === 'expense')?.total || 0;
        const balance = totalIncome - totalExpense;

        // Monthly totals
        const monthlyTotals = await Transaction.aggregate([
            {
                $match: {
                    user: req.user._id,
                    transactionDate: { $gte: startOfMonth, $lte: endOfMonth }
                }
            },
            {
                $group: {
                    _id: '$type',
                    total: { $sum: '$amount' }
                }
            }
        ]);

        const monthlyIncome = monthlyTotals.find(t => t._id === 'income')?.total || 0;
        const monthlyExpense = monthlyTotals.find(t => t._id === 'expense')?.total || 0;

        // Expenses by category (current month)
        const expensesByCategory = await Transaction.aggregate([
            {
                $match: {
                    user: req.user._id,
                    type: 'expense',
                    transactionDate: { $gte: startOfMonth, $lte: endOfMonth }
                }
            },
            {
                $lookup: {
                    from: 'categories',
                    localField: 'category',
                    foreignField: '_id',
                    as: 'categoryInfo'
                }
            },
            { $unwind: '$categoryInfo' },
            {
                $group: {
                    _id: '$category',
                    name: { $first: '$categoryInfo.name' },
                    total: { $sum: '$amount' }
                }
            },
            { $sort: { total: -1 } }
        ]);

        // Recent transactions
        const recentTransactions = await Transaction.find({ user: userId })
            .populate('category', 'name')
            .sort({ transactionDate: -1 })
            .limit(5);

        // Budget allocations summary
        const allocations = await BudgetAllocation.find({ user: userId });
        const totalAllocated = allocations.reduce((sum, a) => sum + a.amount, 0);
        const totalPaid = allocations.filter(a => a.isPaid).reduce((sum, a) => sum + a.amount, 0);

        res.status(200).json({
            success: true,
            data: {
                totals: {
                    income: totalIncome,
                    expense: totalExpense,
                    balance
                },
                monthly: {
                    income: monthlyIncome,
                    expense: monthlyExpense
                },
                expensesByCategory,
                recentTransactions,
                allocations: {
                    total: totalAllocated,
                    paid: totalPaid,
                    remaining: totalAllocated - totalPaid
                }
            }
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};
