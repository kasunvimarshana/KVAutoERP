export interface User {
  id: number
  name: string
  email: string
  tenant_id: number
  roles: string[]
  permissions: string[]
  attributes?: Record<string, unknown>
  is_active: boolean
  email_verified_at?: string
  created_at: string
  updated_at: string
}

export interface Tenant {
  id: number
  name: string
  domain?: string
  is_active: boolean
  settings?: Record<string, unknown>
}

export interface Product {
  id: number
  name: string
  description?: string
  sku: string
  price: number
  category?: string
  tenant_id: number
  attributes?: Record<string, unknown>
  is_active: boolean
  inventory?: Inventory
  created_at: string
  updated_at: string
}

export interface Inventory {
  id: number
  product_id: number
  tenant_id: number
  quantity: number
  reserved_quantity: number
  available_quantity: number
  min_quantity: number
  max_quantity?: number
  location?: string
  notes?: string
  product?: Product
  created_at: string
  updated_at: string
}

export interface Order {
  id: number
  tenant_id: number
  user_id: number
  status: OrderStatus
  total_amount: number
  notes?: string
  metadata?: Record<string, unknown>
  items?: OrderItem[]
  user?: User
  created_at: string
  updated_at: string
}

export interface OrderItem {
  id: number
  order_id: number
  product_id: number
  quantity: number
  unit_price: number
  total_price: number
  product?: Product
}

export type OrderStatus = 'pending' | 'confirmed' | 'processing' | 'shipped' | 'delivered' | 'cancelled'

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    from: number
    last_page: number
    per_page: number
    to: number
    total: number
  }
  links: {
    first: string
    last: string
    next?: string
    prev?: string
  }
}

export interface AuthResponse {
  user: User
  token: string
  token_type: string
}
