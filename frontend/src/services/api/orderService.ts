import apiClient from './apiClient';
import {
  Order,
  CreateOrderPayload,
  PaginatedResponse,
  ApiResponse,
  QueryParams,
  SagaStatus,
} from '../../types';

const buildQueryString = (params: QueryParams): string => {
  const query = new URLSearchParams();
  if (params.page) query.set('page', String(params.page));
  if (params.perPage) query.set('per_page', String(params.perPage));
  if (params.search) query.set('search', params.search);
  if (params.sortBy) query.set('sort_by', params.sortBy);
  if (params.sortDirection) query.set('sort_direction', params.sortDirection);
  if (params.filters) {
    Object.entries(params.filters).forEach(([key, val]) => {
      if (val !== undefined && val !== null && val !== '')
        query.set(`filters[${key}]`, String(val));
    });
  }
  return query.toString();
};

export const orderService = {
  list: (params?: QueryParams) => {
    const qs = params ? buildQueryString(params) : '';
    return apiClient.get<PaginatedResponse<Order> | Order[]>(
      `/orders${qs ? '?' + qs : ''}`
    );
  },

  get: (id: string) => apiClient.get<ApiResponse<Order>>(`/orders/${id}`),

  create: (data: CreateOrderPayload) =>
    apiClient.post<ApiResponse<Order>>('/orders', data),

  cancel: (id: string, reason?: string) =>
    apiClient.post<ApiResponse<Order>>(`/orders/${id}/cancel`, { reason }),

  updateStatus: (id: string, status: Order['status']) =>
    apiClient.patch<ApiResponse<Order>>(`/orders/${id}/status`, { status }),

  getSagaStatus: (sagaId: string) =>
    apiClient.get<ApiResponse<SagaStatus>>(`/orders/saga/${sagaId}`),
};
