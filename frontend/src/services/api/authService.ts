import apiClient from './apiClient';
import { User, LoginCredentials, ApiResponse } from '../../types';

export const authService = {
  login: (credentials: LoginCredentials) =>
    apiClient.post<ApiResponse<{ token: string; user: User }>>('/auth/login', credentials),

  logout: () => apiClient.post<void>('/auth/logout'),

  me: () => apiClient.get<ApiResponse<User>>('/auth/me'),

  refresh: () => apiClient.post<ApiResponse<{ token: string }>>('/auth/refresh'),
};
