// ─── Auth & User ────────────────────────────────────────────────────────────

export interface AuthUser {
  id: string;
  email: string;
  name: string;
  roles: string[];
  tenantId: string;
  tenantName?: string;
}

export interface KeycloakTokenParsed {
  sub: string;
  email: string;
  name: string;
  realm_access?: { roles: string[] };
  resource_access?: Record<string, { roles: string[] }>;
  tenant_id?: string;
  tenant_name?: string;
  preferred_username?: string;
}

// ─── Tenant ──────────────────────────────────────────────────────────────────

export interface Tenant {
  id: string;
  name: string;
  slug: string;
  plan: 'free' | 'starter' | 'professional' | 'enterprise';
  status: 'active' | 'inactive' | 'suspended';
  created_at: string;
  updated_at: string;
}

// ─── User ────────────────────────────────────────────────────────────────────

export type UserRole = 'admin' | 'manager' | 'staff' | 'viewer';

export interface User {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  tenant_id: string;
  status: 'active' | 'inactive';
  last_login?: string;
  created_at: string;
  updated_at: string;
}

export interface CreateUserPayload {
  name: string;
  email: string;
  role: UserRole;
  password: string;
  status: 'active' | 'inactive';
}

export interface UpdateUserPayload {
  name?: string;
  email?: string;
  role?: UserRole;
  status?: 'active' | 'inactive';
  password?: string;
}

// ─── Product ─────────────────────────────────────────────────────────────────

export type ProductStatus = 'active' | 'inactive' | 'discontinued';

export interface Product {
  id: number;
  name: string;
  sku: string;
  description?: string;
  category: string;
  unit_price: number;
  cost_price: number;
  status: ProductStatus;
  tenant_id: string;
  created_at: string;
  updated_at: string;
}

export interface CreateProductPayload {
  name: string;
  sku: string;
  description?: string;
  category: string;
  unit_price: number;
  cost_price: number;
  status: ProductStatus;
}

export interface UpdateProductPayload extends Partial<CreateProductPayload> {}

// ─── Inventory ────────────────────────────────────────────────────────────────

export type InventoryTransactionType = 'in' | 'out' | 'adjustment' | 'return';

export interface InventoryItem {
  id: number;
  product_id: number;
  product?: Product;
  warehouse: string;
  quantity: number;
  min_quantity: number;
  max_quantity: number;
  tenant_id: string;
  updated_at: string;
}

export interface InventoryTransaction {
  id: number;
  inventory_item_id: number;
  product_id: number;
  product?: Product;
  type: InventoryTransactionType;
  quantity: number;
  reference?: string;
  notes?: string;
  created_by: string;
  tenant_id: string;
  created_at: string;
}

export interface CreateInventoryTransactionPayload {
  product_id: number;
  warehouse: string;
  type: InventoryTransactionType;
  quantity: number;
  reference?: string;
  notes?: string;
}

// ─── Order ───────────────────────────────────────────────────────────────────

export type OrderStatus = 'pending' | 'confirmed' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded';
export type PaymentStatus = 'pending' | 'paid' | 'failed' | 'refunded';

export interface OrderItem {
  id: number;
  order_id: number;
  product_id: number;
  product?: Product;
  quantity: number;
  unit_price: number;
  total_price: number;
}

export interface Order {
  id: number;
  order_number: string;
  customer_name: string;
  customer_email: string;
  status: OrderStatus;
  payment_status: PaymentStatus;
  items: OrderItem[];
  subtotal: number;
  tax: number;
  total: number;
  notes?: string;
  tenant_id: string;
  created_at: string;
  updated_at: string;
}

export interface CreateOrderPayload {
  customer_name: string;
  customer_email: string;
  items: { product_id: number; quantity: number; unit_price: number }[];
  notes?: string;
}

export interface UpdateOrderPayload {
  status?: OrderStatus;
  payment_status?: PaymentStatus;
  notes?: string;
}

// ─── Pagination & API ─────────────────────────────────────────────────────────

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginationMeta;
  links?: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}

// ─── Dashboard ────────────────────────────────────────────────────────────────

export interface DashboardStats {
  total_products: number;
  total_orders: number;
  low_stock_items: number;
  revenue_this_month: number;
  orders_this_month: number;
  pending_orders: number;
  active_users: number;
  inventory_value: number;
}

export interface RecentOrder {
  id: number;
  order_number: string;
  customer_name: string;
  total: number;
  status: OrderStatus;
  created_at: string;
}

// ─── Table / UI ───────────────────────────────────────────────────────────────

export interface Column<T> {
  key: keyof T | string;
  label: string;
  sortable?: boolean;
  render?: (value: unknown, row: T) => React.ReactNode;
  className?: string;
}

export interface FilterOption {
  label: string;
  value: string;
}

export interface TableState {
  page: number;
  perPage: number;
  search: string;
  sortKey: string;
  sortDir: 'asc' | 'desc';
  filters: Record<string, string>;
}
