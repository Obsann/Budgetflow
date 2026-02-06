import { useState, useEffect } from 'react';
import { dashboardService, categoryService } from '../services/dataService';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts';
import './Dashboard.css';

const COLORS = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#3b82f6', '#84cc16'];

const Dashboard = () => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        const fetchData = async () => {
            try {
                // Seed categories if needed
                await categoryService.seed();

                const result = await dashboardService.getData();
                if (result.success) {
                    setData(result.data);
                }
            } catch (err) {
                console.error(err);
                setError(err.response?.data?.message || err.message || 'Failed to load dashboard data');
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, []);

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    if (loading) {
        return (
            <div className="loading-container">
                <div className="spinner"></div>
            </div>
        );
    }

    if (error) {
        return <div className="alert alert-error">{error}</div>;
    }

    return (
        <div className="dashboard">
            <div className="page-header">
                <h1 className="page-title">Dashboard</h1>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-4">
                <div className="glass-card stat-card">
                    <div className="stat-value income">{formatCurrency(data?.totals?.income || 0)}</div>
                    <div className="stat-label">Total Income</div>
                </div>
                <div className="glass-card stat-card">
                    <div className="stat-value expense">{formatCurrency(data?.totals?.expense || 0)}</div>
                    <div className="stat-label">Total Expenses</div>
                </div>
                <div className="glass-card stat-card">
                    <div className="stat-value balance">{formatCurrency(data?.totals?.balance || 0)}</div>
                    <div className="stat-label">Balance</div>
                </div>
                <div className="glass-card stat-card">
                    <div className="stat-value">{formatCurrency(data?.monthly?.expense || 0)}</div>
                    <div className="stat-label">This Month</div>
                </div>
            </div>

            <div className="grid grid-2 mt-4">
                {/* Expense Chart */}
                <div className="glass-card">
                    <h3 className="card-title">Expenses by Category</h3>
                    {data?.expensesByCategory?.length > 0 ? (
                        <div className="chart-container">
                            <ResponsiveContainer width="100%" height={300}>
                                <PieChart>
                                    <Pie
                                        data={data.expensesByCategory}
                                        dataKey="total"
                                        nameKey="name"
                                        cx="50%"
                                        cy="50%"
                                        outerRadius={100}
                                        label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                                        labelLine={true}
                                    >
                                        {data.expensesByCategory.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip
                                        formatter={(value) => formatCurrency(value)}
                                        contentStyle={{
                                            background: 'rgba(30, 30, 60, 0.95)',
                                            border: '1px solid rgba(255,255,255,0.1)',
                                            borderRadius: '8px'
                                        }}
                                    />
                                </PieChart>
                            </ResponsiveContainer>
                        </div>
                    ) : (
                        <p className="empty-state">No expense data for this month</p>
                    )}
                </div>

                {/* Recent Transactions */}
                <div className="glass-card">
                    <h3 className="card-title">Recent Transactions</h3>
                    {data?.recentTransactions?.length > 0 ? (
                        <div className="recent-list">
                            {data.recentTransactions.map((tx) => (
                                <div key={tx._id} className="recent-item">
                                    <div className="recent-info">
                                        <span className="recent-category">{tx.category?.name || 'Uncategorized'}</span>
                                        <span className="recent-desc">{tx.description || 'No description'}</span>
                                    </div>
                                    <span className={`recent-amount ${tx.type}`}>
                                        {tx.type === 'income' ? '+' : '-'}{formatCurrency(tx.amount)}
                                    </span>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="empty-state">No transactions yet</p>
                    )}
                </div>
            </div>

            {/* Budget Allocations Summary */}
            <div className="glass-card mt-4">
                <h3 className="card-title">Budget Allocations</h3>
                <div className="allocations-summary">
                    <div className="allocation-stat">
                        <span className="allocation-label">Total Allocated</span>
                        <span className="allocation-value">{formatCurrency(data?.allocations?.total || 0)}</span>
                    </div>
                    <div className="allocation-stat">
                        <span className="allocation-label">Paid</span>
                        <span className="allocation-value paid">{formatCurrency(data?.allocations?.paid || 0)}</span>
                    </div>
                    <div className="allocation-stat">
                        <span className="allocation-label">Remaining</span>
                        <span className="allocation-value remaining">{formatCurrency(data?.allocations?.remaining || 0)}</span>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
