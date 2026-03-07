import axios from 'axios';
import keycloak from './keycloak';

const api = axios.create({
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Automatically attach Keycloak token to requests
api.interceptors.request.use(async (config) => {
  if (keycloak.authenticated) {
    // Refresh token if it expires within the next 30 seconds
    try {
      await keycloak.updateToken(30);
    } catch (err) {
      console.error('Failed to refresh Keycloak token, redirecting to login', err);
      keycloak.login();
      return config;
    }
    config.headers.Authorization = `Bearer ${keycloak.token}`;
  }
  return config;
}, (error) => Promise.reject(error));

// Handle 401 by redirecting to login
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      keycloak.login();
    }
    return Promise.reject(error);
  }
);

export default api;
