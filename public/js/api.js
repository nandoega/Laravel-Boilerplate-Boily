const api = {
    baseUrl: '/api/v1',

    getToken() {
        return localStorage.getItem('auth_token');
    },

    setToken(token) {
        localStorage.setItem('auth_token', token);
    },

    clearToken() {
        localStorage.removeItem('auth_token');
    },

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...options.headers
        };

        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const config = {
            ...options,
            headers
        };

        if (config.body && typeof config.body === 'object') {
            config.body = JSON.stringify(config.body);
        }

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            // Auto logout if unauthenticated token
            if (response.status === 401 && window.location.pathname !== '/login') {
                this.clearToken();
                window.location.href = '/login';
                return null;
            }

            if (!response.ok) {
                let errorMsg = data.meta?.message || 'Something went wrong';
                if (data.errors) {
                    errorMsg += '\n' + Object.values(data.errors).map(e => e.join(', ')).join('\n');
                }
                throw new Error(errorMsg);
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    get(endpoint) { return this.request(endpoint, { method: 'GET' }); },
    post(endpoint, body) { return this.request(endpoint, { method: 'POST', body }); },
    put(endpoint, body) { return this.request(endpoint, { method: 'PUT', body }); },
    patch(endpoint, body) { return this.request(endpoint, { method: 'PATCH', body }); },
    delete(endpoint) { return this.request(endpoint, { method: 'DELETE' }); }
};

// Global intercept for page loads
document.addEventListener('DOMContentLoaded', () => {
    const token = api.getToken();
    const isLoginPage = window.location.pathname === '/' || window.location.pathname === '/login';

    if (!token && !isLoginPage) {
        window.location.href = '/login';
    } else if (token && isLoginPage) {
        window.location.href = '/dashboard';
    }
});
