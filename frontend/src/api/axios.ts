// Central Axios instance – reads base URL from env
import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
});

// Attach auth token and tenant header on every request
api.interceptors.request.use((config) => {
  const token  = localStorage.getItem('access_token');
  const tenant = localStorage.getItem('tenant_key');
  if (token)  config.headers['Authorization'] = `Bearer ${token}`;
  if (tenant) config.headers['X-Tenant']      = tenant;
  return config;
});

// Global 401 handler – redirect to login
api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem('access_token');
      window.location.href = '/login';
    }
    return Promise.reject(err);
  },
);

export default api;
