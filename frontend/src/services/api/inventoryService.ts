import apiClient from './apiClient';
import {
  InventoryItem,
  CreateInventoryPayload,
  PaginatedResponse,
  ApiResponse,
  QueryParams,
  StockAdjustment,
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

export const inventoryService = {
  list: (params?: QueryParams) => {
    const qs = params ? buildQueryString(params) : '';
    return apiClient.get<PaginatedResponse<InventoryItem> | InventoryItem[]>(
      `/inventory${qs ? '?' + qs : ''}`
    );
  },

  get: (id: string) => apiClient.get<ApiResponse<InventoryItem>>(`/inventory/${id}`),

  create: (data: CreateInventoryPayload) =>
    apiClient.post<ApiResponse<InventoryItem>>('/inventory', data),

  update: (id: string, data: Partial<CreateInventoryPayload>) =>
    apiClient.put<ApiResponse<InventoryItem>>(`/inventory/${id}`, data),

  delete: (id: string) => apiClient.delete<void>(`/inventory/${id}`),

  adjustStock: (id: string, adjustment: StockAdjustment) =>
    apiClient.post<ApiResponse<InventoryItem>>(`/inventory/${id}/adjust-stock`, adjustment),

  getLowStock: () => apiClient.get<InventoryItem[]>('/inventory/reports/low-stock'),
};
