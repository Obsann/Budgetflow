import { useState, useEffect } from 'react';
import { transactionService, categoryService } from '../services/dataService';
import './Transactions.css';

const Transactions = () => {
    const [transactions, setTransactions] = useState([]);
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const [formData, setFormData] = useState({
        category: '',
        amount: '',
        type: 'expense',
        description: '',
        transactionDate: new Date().toISOString().split('T')[0]
    });

    // Filters
    const [filters, setFilters] = useState({
        startDate: '',
        endDate: '',
        category: '',
        type: '',
        search: ''
    });

    useEffect(() => {
        fetchData();
    }, [filters]);

    const fetchData = async () => {
        try {
            const [txResult, catResult] = await Promise.all([
                transactionService.getAll(filters),
                categoryService.getAll()
            ]);

            if (txResult.success) setTransactions(txResult.data);
            if (catResult.success) setCategories(catResult.data);
        } catch (err) {
            setError('Failed to load data');
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setSuccess('');

        try {
            const data = {
                ...formData,
                amount: parseFloat(formData.amount)
            };

            if (editingId) {
                await transactionService.update(editingId, data);
                setSuccess('Transaction updated successfully');
            } else {
                await transactionService.create(data);
                setSuccess('Transaction created successfully');
            }

            resetForm();
            fetchData();
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to save transaction');
        }
    };

    const handleEdit = (tx) => {
        setFormData({
            category: tx.category?._id || '',
            amount: tx.amount.toString(),
            type: tx.type,
            description: tx.description || '',
            transactionDate: new Date(tx.transactionDate).toISOString().split('T')[0]
        });
        setEditingId(tx._id);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (!window.confirm('Are you sure you want to delete this transaction?')) return;

        try {
            await transactionService.delete(id);
            setSuccess('Transaction deleted');
            fetchData();
        } catch (err) {
            setError('Failed to delete transaction');
        }
    };

    const resetForm = () => {
        setFormData({
            category: '',
            amount: '',
            type: 'expense',
            description: '',
            transactionDate: new Date().toISOString().split('T')[0]
        });
        setEditingId(null);
        setShowForm(false);
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

    if (loading) {
        return <div className="loading-container"><div className="spinner"></div></div>;
    }

    return (
        <div className="transactions-page">
            <div className="page-header">
                <h1 className="page-title">Transactions</h1>
                <button className="btn btn-primary" onClick={() => setShowForm(!showForm)}>
                    {showForm ? 'Cancel' : '+ Add Transaction'}
                </button>
            </div>

            {error && <div className="alert alert-error">{error}</div>}
            {success && <div className="alert alert-success">{success}</div>}

            {/* Transaction Form */}
            {showForm && (
                <div className="glass-card form-card">
                    <h3>{editingId ? 'Edit Transaction' : 'Add New Transaction'}</h3>
                    <form onSubmit={handleSubmit} className="transaction-form">
                        <div className="form-row">
                            <div className="form-group">
                                <label className="form-label">Type</label>
                                <select
                                    className="form-select"
                                    value={formData.type}
                                    onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                                >
                                    <option value="expense">Expense</option>
                                    <option value="income">Income</option>
                                </select>
                            </div>
                            <div className="form-group">
                                <label className="form-label">Category</label>
                                <select
                                    className="form-select"
                                    value={formData.category}
                                    onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                                    required
                                >
                                    <option value="">Select category</option>
                                    {categories.map((cat) => (
                                        <option key={cat._id} value={cat._id}>{cat.name}</option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        <div className="form-row">
                            <div className="form-group">
                                <label className="form-label">Amount</label>
                                <input
                                    type="number"
                                    className="form-input"
                                    placeholder="0.00"
                                    step="0.01"
                                    min="0.01"
                                    value={formData.amount}
                                    onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
                                    required
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Date</label>
                                <input
                                    type="date"
                                    className="form-input"
                                    value={formData.transactionDate}
                                    onChange={(e) => setFormData({ ...formData, transactionDate: e.target.value })}
                                    required
                                />
                            </div>
                        </div>

                        <div className="form-group">
                            <label className="form-label">Description</label>
                            <input
                                type="text"
                                className="form-input"
                                placeholder="Enter description (optional)"
                                value={formData.description}
                                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                            />
                        </div>

                        <div className="form-actions">
                            <button type="button" className="btn btn-secondary" onClick={resetForm}>
                                Cancel
                            </button>
                            <button type="submit" className="btn btn-primary">
                                {editingId ? 'Update' : 'Save'} Transaction
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Filters */}
            <div className="glass-card filters-card">
                <div className="filters-row">
                    <input
                        type="text"
                        className="form-input"
                        placeholder="Search description..."
                        value={filters.search}
                        onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                    />
                    <select
                        className="form-select"
                        value={filters.type}
                        onChange={(e) => setFilters({ ...filters, type: e.target.value })}
                    >
                        <option value="">All Types</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
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
                    <input
                        type="date"
                        className="form-input"
                        value={filters.startDate}
                        onChange={(e) => setFilters({ ...filters, startDate: e.target.value })}
                    />
                    <input
                        type="date"
                        className="form-input"
                        value={filters.endDate}
                        onChange={(e) => setFilters({ ...filters, endDate: e.target.value })}
                    />
                </div>
            </div>

            {/* Transactions Table */}
            <div className="glass-card">
                <div className="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {transactions.length > 0 ? (
                                transactions.map((tx) => (
                                    <tr key={tx._id}>
                                        <td>{formatDate(tx.transactionDate)}</td>
                                        <td>
                                            <span className={`badge badge-${tx.type}`}>
                                                {tx.type}
                                            </span>
                                        </td>
                                        <td>{tx.category?.name || 'Uncategorized'}</td>
                                        <td>{tx.description || '-'}</td>
                                        <td className={tx.type}>
                                            {tx.type === 'income' ? '+' : '-'}{formatCurrency(tx.amount)}
                                        </td>
                                        <td>
                                            <div className="action-buttons">
                                                <button
                                                    className="btn btn-secondary btn-sm"
                                                    onClick={() => handleEdit(tx)}
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    className="btn btn-danger btn-sm"
                                                    onClick={() => handleDelete(tx._id)}
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="6" className="text-center">
                                        No transactions found
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default Transactions;
