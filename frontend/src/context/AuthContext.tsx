import React, { createContext, useContext, useEffect, useState, useCallback, ReactNode } from 'react';
import keycloak from '../keycloak';
import type { AuthUser, KeycloakTokenParsed } from '../types';

interface AuthContextValue {
  user: AuthUser | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  token: string | null;
  login: () => void;
  logout: () => void;
  hasRole: (role: string) => boolean;
  hasAnyRole: (roles: string[]) => boolean;
}

const AuthContext = createContext<AuthContextValue | null>(null);

function parseUserFromToken(parsed: KeycloakTokenParsed): AuthUser {
  return {
    id: parsed.sub,
    email: parsed.email ?? parsed.preferred_username ?? '',
    name: parsed.name ?? parsed.preferred_username ?? '',
    roles: [
      ...(parsed.realm_access?.roles ?? []),
      ...(parsed.resource_access?.['inventory-frontend']?.roles ?? []),
    ],
    tenantId: parsed.tenant_id ?? '',
    tenantName: parsed.tenant_name,
  };
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [token, setToken] = useState<string | null>(null);

  useEffect(() => {
    keycloak
      .init({
        onLoad: 'check-sso',
        silentCheckSsoRedirectUri: `${window.location.origin}/silent-check-sso.html`,
        pkceMethod: 'S256',
      })
      .then((authenticated) => {
        if (authenticated && keycloak.tokenParsed) {
          setUser(parseUserFromToken(keycloak.tokenParsed as KeycloakTokenParsed));
          setToken(keycloak.token ?? null);
        }
      })
      .catch(console.error)
      .finally(() => setIsLoading(false));

    keycloak.onTokenExpired = () => {
      keycloak
        .updateToken(30)
        .then(() => setToken(keycloak.token ?? null))
        .catch(() => keycloak.logout());
    };

    keycloak.onAuthSuccess = () => {
      if (keycloak.tokenParsed) {
        setUser(parseUserFromToken(keycloak.tokenParsed as KeycloakTokenParsed));
        setToken(keycloak.token ?? null);
      }
    };

    keycloak.onAuthLogout = () => {
      setUser(null);
      setToken(null);
    };
  }, []);

  const login = useCallback(() => keycloak.login(), []);
  const logout = useCallback(() => keycloak.logout({ redirectUri: window.location.origin }), []);

  const hasRole = useCallback(
    (role: string) => user?.roles.includes(role) ?? false,
    [user]
  );

  const hasAnyRole = useCallback(
    (roles: string[]) => roles.some((r) => user?.roles.includes(r)) ?? false,
    [user]
  );

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated: !!user,
        isLoading,
        token,
        login,
        logout,
        hasRole,
        hasAnyRole,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuthContext(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuthContext must be used within AuthProvider');
  return ctx;
}

export default AuthContext;
