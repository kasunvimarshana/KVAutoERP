import api from './api';

const BASE = '/api/v1/inventory';

export interface InventoryItem {
  id: number;
  product_id: number;
  product_sku: string;
  quantity: number;
  reserved_quantity: number;
  available_quantity: number;
  warehouse_location?: string;
  reorder_level: number;
  reorder_quantity: number;
  unit_cost?: number;
  needs_reorder: boolean;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export const inventoryService = {
  list: (params?: Record<string, unknown>) =>
    api.get(BASE, { params }).then((r) => r.data),

  get: (id: number) =>
    api.get(`${BASE}/${id}`).then((r) => r.data),

  getByProduct: (productId: number) =>
    api.get(`${BASE}/product/${productId}`).then((r) => r.data),

  create: (data: Partial<InventoryItem>) =>
    api.post(BASE, data).then((r) => r.data),

  update: (id: number, data: Partial<InventoryItem>) =>
    api.put(`${BASE}/${id}`, data).then((r) => r.data),

  adjust: (productId: number, delta: number, reason?: string) =>
    api.post(`${BASE}/product/${productId}/adjust`, { delta, reason }).then((r) => r.data),

  delete: (id: number) =>
    api.delete(`${BASE}/${id}`),
};
