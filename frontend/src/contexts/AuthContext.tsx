import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { User, AuthState, LoginCredentials, Tenant } from '../types';
import { authService } from '../services/api/authService';

interface AuthContextType extends AuthState {
  login: (credentials: LoginCredentials) => Promise<void>;
  logout: () => Promise<void>;
  refreshToken: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [state, setState] = useState<AuthState>({
    user: null,
    token: localStorage.getItem('auth_token'),
    tenant: null,
    isAuthenticated: false,
    isLoading: true,
  });

  useEffect(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      authService
        .me()
        .then((response) => {
          setState((prev) => ({
            ...prev,
            user: response.data,
            isAuthenticated: true,
            isLoading: false,
          }));
        })
        .catch(() => {
          localStorage.removeItem('auth_token');
          localStorage.removeItem('tenant_id');
          setState((prev) => ({ ...prev, token: null, isLoading: false }));
        });
    } else {
      setState((prev) => ({ ...prev, isLoading: false }));
    }
  }, []);

  const login = useCallback(async (credentials: LoginCredentials) => {
    const response = await authService.login(credentials);
    const { token, user } = response.data;
    localStorage.setItem('auth_token', token);
    localStorage.setItem('tenant_id', credentials.tenantId);
    setState({ user, token, tenant: null, isAuthenticated: true, isLoading: false });
  }, []);

  const logout = useCallback(async () => {
    try {
      await authService.logout();
    } catch {}
    localStorage.removeItem('auth_token');
    localStorage.removeItem('tenant_id');
    setState({ user: null, token: null, tenant: null, isAuthenticated: false, isLoading: false });
    window.location.href = '/login';
  }, []);

  const refreshToken = useCallback(async () => {
    const response = await authService.refresh();
    const { token } = response.data;
    localStorage.setItem('auth_token', token);
    setState((prev) => ({ ...prev, token }));
  }, []);

  return (
    <AuthContext.Provider value={{ ...state, login, logout, refreshToken }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth must be used within AuthProvider');
  return context;
};
