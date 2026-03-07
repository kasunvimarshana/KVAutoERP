import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';
import keycloak from '../keycloak';

const BASE_URL = import.meta.env.VITE_API_BASE_URL ?? '/api';

const api = axios.create({
  baseURL: BASE_URL,
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
  timeout: 15000,
});

// Attach Bearer token and X-Tenant-ID to every request
api.interceptors.request.use(async (config: InternalAxiosRequestConfig) => {
  if (keycloak.isTokenExpired(30)) {
    try {
      await keycloak.updateToken(30);
    } catch {
      keycloak.logout();
      return Promise.reject(new Error('Session expired'));
    }
  }

  if (keycloak.token) {
    config.headers.Authorization = `Bearer ${keycloak.token}`;
  }

  const parsed = keycloak.tokenParsed as Record<string, unknown> | undefined;
  const tenantId = (parsed?.['tenant_id'] as string | undefined) ?? '';
  if (tenantId) {
    config.headers['X-Tenant-ID'] = tenantId;
  }

  return config;
});

// Normalise error responses
api.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (error.response?.status === 401) {
      keycloak.login();
    }
    return Promise.reject(error);
  }
);

export default api;
