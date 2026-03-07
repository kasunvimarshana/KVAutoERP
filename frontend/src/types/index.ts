export interface Tenant {
  id: number;
  name: string;
  key: string;
  domain?: string;
  is_active: boolean;
}

export interface Role {
  id: number;
  name: string;
}

export interface Permission {
  id: number;
  name: string;
}

export interface User {
  id: number;
  tenant_id: number;
  name: string;
  email: string;
  is_active: boolean;
  attributes?: Record<string, unknown>;
  roles: Role[];
  permissions: Permission[];
  tenant?: Tenant;
}

export interface Product {
  id: number;
  tenant_id: number;
  sku: string;
  name: string;
  description?: string;
  price: string;
  category?: string;
  attributes?: Record<string, unknown>;
  is_active: boolean;
}

export interface Inventory {
  id: number;
  tenant_id: number;
  product_id: number;
  warehouse: string;
  quantity: number;
  reserved_quantity: number;
  min_quantity: number;
  max_quantity?: number;
  location?: string;
  available_quantity: number;
  product?: Product;
}

export interface OrderItem {
  id: number;
  order_id: number;
  product_id: number;
  quantity: number;
  unit_price: string;
  total_price: string;
  product?: Product;
}

export interface Order {
  id: number;
  tenant_id: number;
  user_id: number;
  order_number: string;
  status: 'pending' | 'confirmed' | 'cancelled' | 'failed';
  total_amount: string;
  currency: string;
  notes?: string;
  saga_id?: string;
  items: OrderItem[];
  user?: User;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total: number;
    from?: number;
    to?: number;
  };
  links?: {
    first?: string;
    last?: string;
    prev?: string | null;
    next?: string | null;
  };
}

export interface TenantConfig {
  id: number;
  key: string;
  type: string;
  is_encrypted: boolean;
  updated_at?: string;
  created_at?: string;
}
