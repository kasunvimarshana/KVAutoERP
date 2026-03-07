import api from './api';
import type {
  Product,
  CreateProductPayload,
  UpdateProductPayload,
  PaginatedResponse,
} from '../types';

export interface ProductListParams {
  page?: number;
  per_page?: number;
  search?: string;
  category?: string;
  status?: string;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
}

export const productService = {
  list(params: ProductListParams = {}) {
    return api.get<PaginatedResponse<Product>>('/products', { params });
  },

  get(id: number) {
    return api.get<{ data: Product }>(`/products/${id}`);
  },

  create(payload: CreateProductPayload) {
    return api.post<{ data: Product }>('/products', payload);
  },

  update(id: number, payload: UpdateProductPayload) {
    return api.put<{ data: Product }>(`/products/${id}`, payload);
  },

  delete(id: number) {
    return api.delete(`/products/${id}`);
  },

  categories() {
    return api.get<{ data: string[] }>('/products/categories');
  },
};
