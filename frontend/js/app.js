
const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
const API_BASE_URL = isLocal ? 'http://localhost/BudgetFlow/backend/api' : '/api'; // Production points to /api via Vercel rewrites

async function apiFetch(endpoint, options = {}) {
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };

    const config = {
        ...options,
        headers: { ...defaultHeaders, ...options.headers },
        credentials: 'include' // Important for PHP Sessions
    };

    try {
        const response = await fetch(`${API_BASE_URL}/${endpoint}`, config);
        const data = await response.json();
        return { ok: response.ok, data };
    } catch (error) {
        console.error('API Error:', error);
        return { ok: false, data: { error: 'Network or Server Error' } };
    }
}

function checkAuth() {
    apiFetch('auth_check.php').then(result => {
        if (!result.ok || !result.data.authenticated) {
            window.location.href = 'login.html';
        }
    });
}
