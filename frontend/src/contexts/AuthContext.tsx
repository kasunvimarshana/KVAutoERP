import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { authApi } from '../api/endpoints';
import type { User } from '../types';

interface AuthContextValue {
  user: User | null;
  token: string | null;
  tenantKey: string | null;
  login: (email: string, password: string, tenant: string) => Promise<void>;
  logout: () => Promise<void>;
  isAuthenticated: boolean;
  isLoading: boolean;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser]         = useState<User | null>(null);
  const [token, setToken]       = useState<string | null>(() => localStorage.getItem('access_token'));
  const [tenantKey, setTenantKey] = useState<string | null>(() => localStorage.getItem('tenant_key'));
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (token) {
      authApi.me()
        .then((res) => setUser(res.data))
        .catch(() => {
          localStorage.removeItem('access_token');
          setToken(null);
        })
        .finally(() => setIsLoading(false));
    } else {
      setIsLoading(false);
    }
  }, [token]);

  const login = async (email: string, password: string, tenant: string) => {
    localStorage.setItem('tenant_key', tenant);
    setTenantKey(tenant);
    const res = await authApi.login({ email, password });
    const { access_token, user: loggedUser } = res.data;
    localStorage.setItem('access_token', access_token);
    setToken(access_token);
    setUser(loggedUser);
  };

  const logout = async () => {
    try { await authApi.logout(); } catch (_) { /* ignore */ }
    localStorage.removeItem('access_token');
    localStorage.removeItem('tenant_key');
    setToken(null);
    setTenantKey(null);
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, token, tenantKey, login, logout, isAuthenticated: !!token, isLoading }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
