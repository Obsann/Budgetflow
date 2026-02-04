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

async function apiFetch(endpoint, options = {}) {
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
    const result = await apiFetch('auth_check.php');
    if (!result.ok || !result.data.authenticated) {
        window.location.href = 'login.html';
    }
    // Token is auto-stored by apiFetch if returned
}
