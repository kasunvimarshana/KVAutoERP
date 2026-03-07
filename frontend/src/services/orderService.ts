import api from './api';

const BASE = '/api/v1/orders';

export interface OrderItem {
  id: number;
  product_id: number;
  product_sku: string;
  product_name: string;
  quantity: number;
  unit_price: number;
  total_price: number;
}

export interface Order {
  id: number;
  order_number: string;
  user_id: number;
  status: 'pending' | 'confirmed' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'failed';
  total_amount: number;
  currency: string;
  shipping_address: Record<string, string>;
  billing_address?: Record<string, string>;
  notes?: string;
  items: OrderItem[];
  created_at: string;
  updated_at: string;
}

export interface CreateOrderPayload {
  user_id: number;
  items: Array<{
    product_id: number;
    product_sku: string;
    product_name: string;
    quantity: number;
    unit_price: number;
  }>;
  shipping_address: Record<string, string>;
  billing_address?: Record<string, string>;
  currency?: string;
  notes?: string;
}

export const orderService = {
  list: (params?: Record<string, unknown>) =>
    api.get(BASE, { params }).then((r) => r.data),

  get: (id: number) =>
    api.get(`${BASE}/${id}`).then((r) => r.data),

  create: (data: CreateOrderPayload) =>
    api.post(BASE, data).then((r) => r.data),

  cancel: (id: number, reason?: string) =>
    api.post(`${BASE}/${id}/cancel`, { reason }).then((r) => r.data),

  updateStatus: (id: number, status: string) =>
    api.patch(`${BASE}/${id}/status`, { status }).then((r) => r.data),
};
