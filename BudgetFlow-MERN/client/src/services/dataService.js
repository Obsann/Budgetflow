import api from './api';

export const transactionService = {
    // Get all transactions with optional filters
    getAll: async (filters = {}) => {
        const params = new URLSearchParams();
        if (filters.startDate) params.append('startDate', filters.startDate);
        if (filters.endDate) params.append('endDate', filters.endDate);
        if (filters.category) params.append('category', filters.category);
        if (filters.type) params.append('type', filters.type);
        if (filters.search) params.append('search', filters.search);

        const res = await api.get(`/api/transactions?${params.toString()}`);
        return res.data;
    },

    // Get single transaction
    getById: async (id) => {
        const res = await api.get(`/api/transactions/${id}`);
        return res.data;
    },

    // Create transaction
    create: async (data) => {
        const res = await api.post('/api/transactions', data);
        return res.data;
    },

    // Update transaction
    update: async (id, data) => {
        const res = await api.put(`/api/transactions/${id}`, data);
        return res.data;
    },

    // Delete transaction
    delete: async (id) => {
        const res = await api.delete(`/api/transactions/${id}`);
        return res.data;
    }
};

export const allocationService = {
    getAll: async () => {
        const res = await api.get('/api/allocations');
        return res.data;
    },

    create: async (data) => {
        const res = await api.post('/api/allocations', data);
        return res.data;
    },

    update: async (id, data) => {
        const res = await api.put(`/api/allocations/${id}`, data);
        return res.data;
    },

    delete: async (id) => {
        const res = await api.delete(`/api/allocations/${id}`);
        return res.data;
    },

    togglePaid: async (id) => {
        const res = await api.patch(`/api/allocations/${id}/toggle`);
        return res.data;
    }
};

export const categoryService = {
    getAll: async () => {
        const res = await api.get('/api/categories');
        return res.data;
    },

    seed: async () => {
        const res = await api.post('/api/categories/seed');
        return res.data;
    }
};

export const dashboardService = {
    getData: async () => {
        const res = await api.get('/api/dashboard');
        return res.data;
    }
};

export const reportService = {
    getData: async (filters = {}) => {
        const params = new URLSearchParams();
        if (filters.startDate) params.append('startDate', filters.startDate);
        if (filters.endDate) params.append('endDate', filters.endDate);
        if (filters.category) params.append('category', filters.category);
        if (filters.type) params.append('type', filters.type);

        const res = await api.get(`/api/reports?${params.toString()}`);
        return res.data;
    },

    exportCSV: async (filters = {}) => {
        const params = new URLSearchParams();
        if (filters.startDate) params.append('startDate', filters.startDate);
        if (filters.endDate) params.append('endDate', filters.endDate);
        if (filters.category) params.append('category', filters.category);
        if (filters.type) params.append('type', filters.type);

        const res = await api.get(`/api/reports/export?${params.toString()}`, {
            responseType: 'blob'
        });

        // Trigger download
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'budgetflow_export.csv');
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    }
};
