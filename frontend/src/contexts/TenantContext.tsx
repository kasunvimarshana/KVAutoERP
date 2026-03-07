import React, { createContext, useContext, useState, useEffect } from 'react';
import { Tenant } from '../types';
import apiClient from '../services/api/apiClient';

interface TenantContextType {
  tenant: Tenant | null;
  tenantId: string | null;
  isLoading: boolean;
  setTenant: (tenant: Tenant | null) => void;
  refreshTenant: () => Promise<void>;
}

const TenantContext = createContext<TenantContextType | undefined>(undefined);

export const TenantProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [tenant, setTenant] = useState<Tenant | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const tenantId = localStorage.getItem('tenant_id');

  const refreshTenant = async () => {
    if (!tenantId) return;
    setIsLoading(true);
    try {
      const response = await apiClient.get<{ data: Tenant }>(`/tenants/${tenantId}`);
      setTenant(response.data);
    } catch {
      // tenant fetch failed — silently ignore
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    if (tenantId && localStorage.getItem('auth_token')) {
      refreshTenant();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [tenantId]);

  return (
    <TenantContext.Provider value={{ tenant, tenantId, isLoading, setTenant, refreshTenant }}>
      {children}
    </TenantContext.Provider>
  );
};

export const useTenant = (): TenantContextType => {
  const context = useContext(TenantContext);
  if (!context) throw new Error('useTenant must be used within TenantProvider');
  return context;
};
