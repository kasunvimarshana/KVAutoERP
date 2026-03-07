import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { useAuthContext } from './AuthContext';
import type { Tenant } from '../types';

interface TenantContextValue {
  tenant: Tenant | null;
  tenantId: string;
  isLoading: boolean;
}

const TenantContext = createContext<TenantContextValue | null>(null);

export function TenantProvider({ children }: { children: ReactNode }) {
  const { user, isAuthenticated } = useAuthContext();
  const [tenant, setTenant] = useState<Tenant | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    if (!isAuthenticated || !user?.tenantId) {
      setTenant(null);
      return;
    }

    setTenant({
      id: user.tenantId,
      name: user.tenantName ?? user.tenantId,
      slug: user.tenantId,
      plan: 'professional',
      status: 'active',
      created_at: '',
      updated_at: '',
    });
    setIsLoading(false);
  }, [isAuthenticated, user]);

  return (
    <TenantContext.Provider value={{ tenant, tenantId: user?.tenantId ?? '', isLoading }}>
      {children}
    </TenantContext.Provider>
  );
}

export function useTenantContext(): TenantContextValue {
  const ctx = useContext(TenantContext);
  if (!ctx) throw new Error('useTenantContext must be used within TenantProvider');
  return ctx;
}

export default TenantContext;
