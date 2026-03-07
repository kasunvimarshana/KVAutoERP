import api from './api';
import type {
  Order,
  CreateOrderPayload,
  UpdateOrderPayload,
  PaginatedResponse,
} from '../types';

export interface OrderListParams {
  page?: number;
  per_page?: number;
  search?: string;
  status?: string;
  payment_status?: string;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
}

export const orderService = {
  list(params: OrderListParams = {}) {
    return api.get<PaginatedResponse<Order>>('/orders', { params });
  },

  get(id: number) {
    return api.get<{ data: Order }>(`/orders/${id}`);
  },

  create(payload: CreateOrderPayload) {
    return api.post<{ data: Order }>('/orders', payload);
  },

  update(id: number, payload: UpdateOrderPayload) {
    return api.put<{ data: Order }>(`/orders/${id}`, payload);
  },

  cancel(id: number) {
    return api.post<{ data: Order }>(`/orders/${id}/cancel`);
  },

  delete(id: number) {
    return api.delete(`/orders/${id}`);
  },
};
