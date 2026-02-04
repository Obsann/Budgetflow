const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
let API_BASE_URL = '/api'; // Default for Vercel
let CSRF_TOKEN = null; // Stores CSRF token for mutating requests

if (isLocal) {
    if (window.location.port === '8000') {
        // PHP Built-in Server (php -S localhost:8000)
        API_BASE_URL = 'http://localhost:8000/backend/api';
    } else {
        // XAMPP / WAMP (Standard folder structure)
        API_BASE_URL = 'http://localhost/BudgetFlow/backend/api';
    }
}

// Guest Mode Logic
function loginAsGuest() {
    localStorage.setItem('guest_mode', 'true');
    window.location.href = 'index.html';
}

function isGuest() {
    return localStorage.getItem('guest_mode') === 'true';
}

// Mock Data Generator
function getMockResponse(endpoint) {
    const mockDelay = 500; // Simulate network latency

    return new Promise((resolve) => {
        setTimeout(() => {
            console.log(`[Guest Mode] Mocking response for: ${endpoint}`);

            // 1. Auth Check - Always Valid
            if (endpoint === 'auth_check.php') {
                resolve({ ok: true, data: { authenticated: true, csrf_token: 'mock-csrf-token' } });
                return;
            }

            // 2. Dashboard Data
            if (endpoint === 'dashboard.php') {
                resolve({
                    ok: true,
                    data: {
                        success: true,
                        data: {
                            income: 15000, // Matches the 'Freelance Work' transaction
                            allocation_total: 8500,
                            allocations: [
                                { id: 1, name: 'Rent', amount: 5000, is_paid: 1, category_name: 'Housing' },
                                { id: 2, name: 'Groceries', amount: 2000, is_paid: 0, category_name: 'Food' },
                                { id: 3, name: 'Internet', amount: 1500, is_paid: 0, category_name: 'Utilities' }
                            ],
                            chart_data: [
                                { name: 'Housing', total: 5000 },
                                { name: 'Food', total: 1200 },
                                { name: 'Utilities', total: 1500 },
                                { name: 'Entertainment', total: 800 }
                            ],
                            categories: [
                                { id: 1, name: 'Housing' }, { id: 2, name: 'Food' },
                                { id: 3, name: 'Utilities' }, { id: 4, name: 'Entertainment' }
                            ]
                        }
                    }
                });
                return;
            }

            // 3. Transactions
            if (endpoint === 'transactions.php' || endpoint.startsWith('transactions.php?')) {
                resolve({
                    ok: true,
                    data: {
                        success: true,
                        data: [
                            { id: 101, description: 'Uber Ride', amount: 350, category_name: 'Transport', transaction_date: '2023-10-25' },
                            { id: 102, description: 'Netflix', amount: 400, category_name: 'Entertainment', transaction_date: '2023-10-24' },
                            { id: 103, description: 'Pizza', amount: 800, category_name: 'Food', transaction_date: '2023-10-23' },
                            { id: 104, description: 'Freelance Work', amount: 15000, category_name: 'Income', transaction_date: '2023-10-20' },
                            { id: 105, description: 'Rent Payment', amount: 5000, category_name: 'Housing', transaction_date: '2023-10-01' }
                        ]
                    }
                });
                return;
            }

            // 4. Report Data
            if (endpoint === 'report_data.php') {
                resolve({
                    ok: true,
                    data: {
                        success: true,
                        data: {
                            grand_total: 6550,
                            report: [
                                { cat_name: 'Housing', spent: 5000, planned: 5000, removed: 0 },
                                { cat_name: 'Food', spent: 800, planned: 2000, removed: 0 },
                                { cat_name: 'Entertainment', spent: 400, planned: 1000, removed: 0 },
                                { cat_name: 'Transport', spent: 350, planned: 500, removed: 0 }
                            ]
                        }
                    }
                });
                return;
            }

            // 5. Logout
            if (endpoint === 'logout.php') {
                localStorage.removeItem('guest_mode');
                resolve({ ok: true, data: { success: true } });
                return;
            }

            // Default for Writes (POST/PUT/DELETE)
            resolve({ ok: true, data: { success: true, message: '[Demo] Action simulated successfully' } });

        }, mockDelay);
    });
}

async function apiFetch(endpoint, options = {}) {
    // Intercept for Guest Mode
    if (isGuest()) {
        const mockResult = await getMockResponse(endpoint);

        // Handle Logout redirect specifically in the caller usually, but clear state here
        if (endpoint === 'logout.php') {
            // Let the caller handle redirect, but Mock response is just success
        }

        return mockResult;
    }

    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };

    // Add CSRF token to non-GET requests
    if (CSRF_TOKEN && options.method && options.method !== 'GET') {
        defaultHeaders['X-CSRF-Token'] = CSRF_TOKEN;
    }

    const config = {
        ...options,
        headers: { ...defaultHeaders, ...options.headers },
        credentials: 'include' // Important for PHP Sessions
    };

    try {
        const response = await fetch(`${API_BASE_URL}/${endpoint}`, config);
        const data = await response.json();

        // Update CSRF token if returned by server
        if (data.csrf_token) {
            CSRF_TOKEN = data.csrf_token;
        }

        return { ok: response.ok, data };
    } catch (error) {
        console.error('API Error:', error);
        return { ok: false, data: { error: 'Network or Server Error' } };
    }
}

async function checkAuth() {
    if (isGuest()) return; // Skip auth check if guest

    const result = await apiFetch('auth_check.php');
    if (!result.ok || !result.data.authenticated) {
        window.location.href = 'login.html';
    }
    // Token is auto-stored by apiFetch if returned
}
