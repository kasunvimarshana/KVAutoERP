import api from './api';

const BASE = '/api/v1/products';

export interface Product {
  id: number;
  name: string;
  sku: string;
  description: string;
  price: number;
  category: string;
  status: 'active' | 'inactive' | 'draft';
  weight?: number;
  dimensions?: { width: number; height: number; depth: number };
  metadata?: Record<string, unknown>;
  created_at: string;
  updated_at: string;
}

export interface ProductListParams {
  search?: string;
  category?: string;
  status?: string;
  min_price?: number;
  max_price?: number;
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
  per_page?: number;
  page?: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
  links: {
    first: string;
    last: string;
    prev?: string;
    next?: string;
  };
}

export const productService = {
  list: (params?: ProductListParams) =>
    api.get<PaginatedResponse<Product>>(BASE, { params }).then((r) => r.data),

  get: (id: number) =>
    api.get<Product>(`${BASE}/${id}`).then((r) => r.data),

  create: (data: Partial<Product>) =>
    api.post<Product>(BASE, data).then((r) => r.data),

  update: (id: number, data: Partial<Product>) =>
    api.put<Product>(`${BASE}/${id}`, data).then((r) => r.data),

  delete: (id: number) =>
    api.delete(`${BASE}/${id}`),
};
