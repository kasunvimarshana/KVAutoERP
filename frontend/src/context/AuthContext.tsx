import React, { createContext, useContext, useEffect, useState } from 'react';
import keycloak from '../services/keycloak';

interface AuthUser {
  id: string;
  username: string;
  email: string;
  firstName?: string;
  lastName?: string;
  roles: string[];
  token: string;
}

interface AuthContextType {
  user: AuthUser | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: () => void;
  logout: () => void;
  hasRole: (role: string) => boolean;
  hasAnyRole: (roles: string[]) => boolean;
}

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    keycloak
      .init({
        onLoad: 'check-sso',
        silentCheckSsoRedirectUri: window.location.origin + '/silent-check-sso.html',
      })
      .then((authenticated) => {
        if (authenticated && keycloak.tokenParsed) {
          const tokenParsed = keycloak.tokenParsed as Record<string, unknown>;
          const realmRoles = (tokenParsed.realm_access as { roles?: string[] })?.roles ?? [];
          const clientRoles =
            (tokenParsed.resource_access as Record<string, { roles?: string[] }>)?.[
              keycloak.clientId ?? ''
            ]?.roles ?? [];

          setUser({
            id:        tokenParsed.sub as string,
            username:  tokenParsed.preferred_username as string,
            email:     tokenParsed.email as string,
            firstName: tokenParsed.given_name as string,
            lastName:  tokenParsed.family_name as string,
            roles:     [...realmRoles, ...clientRoles],
            token:     keycloak.token ?? '',
          });
        }
        setIsLoading(false);
      })
      .catch(() => setIsLoading(false));
  }, []);

  const login  = () => keycloak.login();
  const logout = () => keycloak.logout({ redirectUri: window.location.origin });

  const hasRole     = (role: string)    => user?.roles.includes(role) ?? false;
  const hasAnyRole  = (roles: string[]) => roles.some((r) => hasRole(r));

  return (
    <AuthContext.Provider
      value={{ user, isAuthenticated: !!user, isLoading, login, logout, hasRole, hasAnyRole }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextType {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
