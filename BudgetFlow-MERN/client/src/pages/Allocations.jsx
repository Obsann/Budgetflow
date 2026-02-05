import { useState, useEffect } from 'react';
import { allocationService } from '../services/dataService';
import './Allocations.css';

const Allocations = () => {
    const [allocations, setAllocations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const [formData, setFormData] = useState({
        name: '',
        amount: '',
        isPaid: false
    });

    useEffect(() => {
        fetchAllocations();
    }, []);

    const fetchAllocations = async () => {
        try {
            const result = await allocationService.getAll();
            if (result.success) setAllocations(result.data);
        } catch (err) {
            setError('Failed to load allocations');
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
                await allocationService.update(editingId, data);
                setSuccess('Allocation updated successfully');
            } else {
                await allocationService.create(data);
                setSuccess('Allocation created successfully');
            }

            resetForm();
            fetchAllocations();
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to save allocation');
        }
    };

    const handleEdit = (alloc) => {
        setFormData({
            name: alloc.name,
            amount: alloc.amount.toString(),
            isPaid: alloc.isPaid
        });
        setEditingId(alloc._id);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (!window.confirm('Are you sure you want to delete this allocation?')) return;

        try {
            await allocationService.delete(id);
            setSuccess('Allocation deleted');
            fetchAllocations();
        } catch (err) {
            setError('Failed to delete allocation');
        }
    };

    const handleTogglePaid = async (id) => {
        try {
            await allocationService.togglePaid(id);
            fetchAllocations();
        } catch (err) {
            setError('Failed to update allocation');
        }
    };

    const resetForm = () => {
        setFormData({ name: '', amount: '', isPaid: false });
        setEditingId(null);
        setShowForm(false);
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    const totalAllocated = allocations.reduce((sum, a) => sum + a.amount, 0);
    const totalPaid = allocations.filter(a => a.isPaid).reduce((sum, a) => sum + a.amount, 0);
    const remaining = totalAllocated - totalPaid;

    if (loading) {
        return <div className="loading-container"><div className="spinner"></div></div>;
    }

    return (
        <div className="allocations-page">
            <div className="page-header">
                <h1 className="page-title">Budget Allocations</h1>
                <button className="btn btn-primary" onClick={() => setShowForm(!showForm)}>
                    {showForm ? 'Cancel' : '+ Add Allocation'}
                </button>
            </div>

            {error && <div className="alert alert-error">{error}</div>}
            {success && <div className="alert alert-success">{success}</div>}

            {/* Summary */}
            <div className="grid grid-3">
                <div className="glass-card stat-card">
                    <div className="stat-value">{formatCurrency(totalAllocated)}</div>
                    <div className="stat-label">Total Allocated</div>
                </div>
                <div className="glass-card stat-card">
                    <div className="stat-value income">{formatCurrency(totalPaid)}</div>
                    <div className="stat-label">Paid</div>
                </div>
                <div className="glass-card stat-card">
                    <div className="stat-value expense">{formatCurrency(remaining)}</div>
                    <div className="stat-label">Remaining</div>
                </div>
            </div>

            {/* Form */}
            {showForm && (
                <div className="glass-card form-card mt-4">
                    <h3>{editingId ? 'Edit Allocation' : 'Add New Allocation'}</h3>
                    <form onSubmit={handleSubmit} className="allocation-form">
                        <div className="form-row">
                            <div className="form-group">
                                <label className="form-label">Name</label>
                                <input
                                    type="text"
                                    className="form-input"
                                    placeholder="e.g., Rent, Groceries, Utilities"
                                    value={formData.name}
                                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                    required
                                />
                            </div>
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
                        </div>

                        <div className="form-group checkbox-group">
                            <label className="checkbox-label">
                                <input
                                    type="checkbox"
                                    checked={formData.isPaid}
                                    onChange={(e) => setFormData({ ...formData, isPaid: e.target.checked })}
                                />
                                <span>Mark as Paid</span>
                            </label>
                        </div>

                        <div className="form-actions">
                            <button type="button" className="btn btn-secondary" onClick={resetForm}>
                                Cancel
                            </button>
                            <button type="submit" className="btn btn-primary">
                                {editingId ? 'Update' : 'Save'} Allocation
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Allocations List */}
            <div className="glass-card mt-4">
                <h3 className="card-title">Your Allocations</h3>
                {allocations.length > 0 ? (
                    <div className="allocations-list">
                        {allocations.map((alloc) => (
                            <div key={alloc._id} className={`allocation-item ${alloc.isPaid ? 'paid' : ''}`}>
                                <div className="allocation-info">
                                    <span className="allocation-name">{alloc.name}</span>
                                    <span className="allocation-amount">{formatCurrency(alloc.amount)}</span>
                                </div>
                                <div className="allocation-actions">
                                    <button
                                        className={`btn btn-sm ${alloc.isPaid ? 'btn-success' : 'btn-secondary'}`}
                                        onClick={() => handleTogglePaid(alloc._id)}
                                    >
                                        {alloc.isPaid ? '✓ Paid' : 'Mark Paid'}
                                    </button>
                                    <button
                                        className="btn btn-secondary btn-sm"
                                        onClick={() => handleEdit(alloc)}
                                    >
                                        Edit
                                    </button>
                                    <button
                                        className="btn btn-danger btn-sm"
                                        onClick={() => handleDelete(alloc._id)}
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <p className="empty-state">No allocations yet. Start planning your budget!</p>
                )}
            </div>
        </div>
    );
};

export default Allocations;
