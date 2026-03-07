// Tenant
export interface Tenant {
  id: string;
  name: string;
  slug: string;
  domain?: string;
  email: string;
  status: 'active' | 'inactive' | 'suspended' | 'trial';
  plan: 'free' | 'starter' | 'professional' | 'enterprise';
  settings?: Record<string, any>;
  trialEndsAt?: string;
  createdAt: string;
}

// User/Auth
export interface User {
  id: string;
  tenantId: string;
  name: string;
  email: string;
  roles: string[];
  permissions: string[];
  isActive: boolean;
  lastLoginAt?: string;
}

export interface AuthState {
  user: User | null;
  token: string | null;
  tenant: Tenant | null;
  isAuthenticated: boolean;
  isLoading: boolean;
}

export interface LoginCredentials {
  email: string;
  password: string;
  tenantId: string;
}

// Inventory
export interface InventoryItem {
  id: string;
  tenantId: string;
  sku: string;
  name: string;
  description?: string;
  quantity: number;
  reservedQuantity: number;
  availableQuantity: number;
  unitCost: number;
  unitPrice: number;
  category?: string;
  location?: string;
  minStockLevel: number;
  maxStockLevel: number;
  isLowStock: boolean;
  status: 'active' | 'inactive' | 'discontinued';
  metadata?: Record<string, any>;
  createdAt: string;
  updatedAt: string;
}

export interface CreateInventoryPayload {
  sku: string;
  name: string;
  description?: string;
  quantity: number;
  unitCost: number;
  unitPrice: number;
  category?: string;
  location?: string;
  minStockLevel?: number;
  maxStockLevel?: number;
  metadata?: Record<string, any>;
}

export interface StockAdjustment {
  quantity: number;
  operation: 'add' | 'subtract' | 'set';
  reason: string;
}

// Orders
export interface OrderItem {
  inventoryId: string;
  name: string;
  sku: string;
  quantity: number;
  unitPrice: number;
  total: number;
}

export interface Order {
  id: string;
  tenantId: string;
  orderNumber: string;
  customerId: string;
  customerName: string;
  customerEmail: string;
  items: OrderItem[];
  subtotal: number;
  tax: number;
  discount: number;
  total: number;
  status: 'pending' | 'confirmed' | 'processing' | 'completed' | 'cancelled' | 'failed';
  paymentStatus: 'pending' | 'paid' | 'failed' | 'refunded';
  paymentMethod?: string;
  shippingAddress?: Address;
  billingAddress?: Address;
  notes?: string;
  sagaId?: string;
  metadata?: Record<string, any>;
  createdAt: string;
  updatedAt: string;
}

export interface Address {
  street: string;
  city: string;
  state: string;
  country: string;
  zipCode: string;
}

export interface CreateOrderPayload {
  customerId: string;
  customerName: string;
  customerEmail: string;
  items: { inventoryId: string; quantity: number }[];
  shippingAddress?: Address;
  billingAddress?: Address;
  notes?: string;
  paymentMethod: string;
}

// API
export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    total: number;
    perPage: number;
    currentPage: number;
    lastPage: number;
  };
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

export interface QueryParams {
  page?: number;
  perPage?: number;
  search?: string;
  sortBy?: string;
  sortDirection?: 'asc' | 'desc';
  filters?: Record<string, any>;
}

export interface DashboardKPIs {
  totalInventoryItems: number;
  totalInventoryValue: number;
  lowStockItems: number;
  totalOrders: number;
  pendingOrders: number;
  completedOrders: number;
  revenue: number;
  revenueGrowth: number;
}

export interface SagaStatus {
  sagaId: string;
  status: 'pending' | 'running' | 'completed' | 'failed' | 'compensated';
  currentStep?: string;
  completedSteps: string[];
  error?: string;
  createdAt: string;
  updatedAt: string;
}
