import { useState, useEffect } from 'react';
import { reportService, categoryService } from '../services/dataService';
import './Reports.css';

const Reports = () => {
    const [data, setData] = useState(null);
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [exporting, setExporting] = useState(false);

    const [filters, setFilters] = useState({
        startDate: '',
        endDate: '',
        category: '',
        type: ''
    });

    useEffect(() => {
        const fetchCategories = async () => {
            try {
                const result = await categoryService.getAll();
                if (result.success) setCategories(result.data);
            } catch (err) {
                console.error('Failed to load categories');
            }
        };
        fetchCategories();
    }, []);

    useEffect(() => {
        fetchReport();
    }, [filters]);

    const fetchReport = async () => {
        setLoading(true);
        try {
            const result = await reportService.getData(filters);
            if (result.success) setData(result.data);
        } catch (err) {
            console.error('Failed to load report');
        } finally {
            setLoading(false);
        }
    };

    const handleExport = async () => {
        setExporting(true);
        try {
            await reportService.exportCSV(filters);
        } catch (err) {
            console.error('Export failed');
        } finally {
            setExporting(false);
        }
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    const formatDate = (date) => {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    return (
        <div className="reports-page">
            <div className="page-header">
                <h1 className="page-title">Reports</h1>
                <button
                    className="btn btn-primary"
                    onClick={handleExport}
                    disabled={exporting || !data?.transactions?.length}
                >
                    {exporting ? 'Exporting...' : '📥 Export CSV'}
                </button>
            </div>

            {/* Filters */}
            <div className="glass-card filters-card">
                <div className="filters-row">
                    <div className="form-group">
                        <label className="form-label">Start Date</label>
                        <input
                            type="date"
                            className="form-input"
                            value={filters.startDate}
                            onChange={(e) => setFilters({ ...filters, startDate: e.target.value })}
                        />
                    </div>
                    <div className="form-group">
                        <label className="form-label">End Date</label>
                        <input
                            type="date"
                            className="form-input"
                            value={filters.endDate}
                            onChange={(e) => setFilters({ ...filters, endDate: e.target.value })}
                        />
                    </div>
                    <div className="form-group">
                        <label className="form-label">Type</label>
                        <select
                            className="form-select"
                            value={filters.type}
                            onChange={(e) => setFilters({ ...filters, type: e.target.value })}
                        >
                            <option value="">All Types</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div className="form-group">
                        <label className="form-label">Category</label>
                        <select
                            className="form-select"
                            value={filters.category}
                            onChange={(e) => setFilters({ ...filters, category: e.target.value })}
                        >
                            <option value="">All Categories</option>
                            {categories.map((cat) => (
                                <option key={cat._id} value={cat._id}>{cat.name}</option>
                            ))}
                        </select>
                    </div>
                </div>
            </div>

            {loading ? (
                <div className="loading-container"><div className="spinner"></div></div>
            ) : (
                <>
                    {/* Summary */}
                    <div className="grid grid-3 mt-4">
                        <div className="glass-card stat-card">
                            <div className="stat-value income">{formatCurrency(data?.summary?.totalIncome || 0)}</div>
                            <div className="stat-label">Total Income</div>
                        </div>
                        <div className="glass-card stat-card">
                            <div className="stat-value expense">{formatCurrency(data?.summary?.totalExpense || 0)}</div>
                            <div className="stat-label">Total Expenses</div>
                        </div>
                        <div className="glass-card stat-card">
                            <div className="stat-value balance">{formatCurrency(data?.summary?.netBalance || 0)}</div>
                            <div className="stat-label">Net Balance</div>
                        </div>
                    </div>

                    {/* By Category Breakdown */}
                    {data?.byCategory && Object.keys(data.byCategory).length > 0 && (
                        <div className="glass-card mt-4">
                            <h3 className="card-title">Breakdown by Category</h3>
                            <div className="category-breakdown">
                                {Object.entries(data.byCategory).map(([name, values]) => (
                                    <div key={name} className="category-row">
                                        <span className="category-name">{name}</span>
                                        <div className="category-values">
                                            {values.income > 0 && (
                                                <span className="income">+{formatCurrency(values.income)}</span>
                                            )}
                                            {values.expense > 0 && (
                                                <span className="expense">-{formatCurrency(values.expense)}</span>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Transactions Table */}
                    <div className="glass-card mt-4">
                        <h3 className="card-title">Transactions ({data?.count || 0} records)</h3>
                        <div className="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data?.transactions?.length > 0 ? (
                                        data.transactions.map((tx) => (
                                            <tr key={tx._id}>
                                                <td>{formatDate(tx.transactionDate)}</td>
                                                <td>
                                                    <span className={`badge badge-${tx.type}`}>{tx.type}</span>
                                                </td>
                                                <td>{tx.category?.name || 'Uncategorized'}</td>
                                                <td>{tx.description || '-'}</td>
                                                <td className={tx.type}>
                                                    {tx.type === 'income' ? '+' : '-'}{formatCurrency(tx.amount)}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="5" className="text-center">
                                                No transactions found for the selected filters
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
};

export default Reports;
