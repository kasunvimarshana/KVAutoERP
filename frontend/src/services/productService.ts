import apiClient from './api'
import { PaginatedResponse, Product } from '../types'

export interface ProductFilters {
  search?: string
  category?: string
  is_active?: boolean
  min_price?: number
  max_price?: number
  per_page?: number
  page?: number
  sort_by?: string
  sort_dir?: 'asc' | 'desc'
}

export const productService = {
  async list(filters: ProductFilters = {}): Promise<PaginatedResponse<Product>> {
    const response = await apiClient.get<PaginatedResponse<Product>>('/products', { params: filters })
    return response.data
  },

  async get(id: number): Promise<{ data: Product }> {
    const response = await apiClient.get<{ data: Product }>(`/products/${id}`)
    return response.data
  },

  async create(data: Partial<Product>): Promise<{ data: Product }> {
    const response = await apiClient.post<{ data: Product }>('/products', data)
    return response.data
  },

  async update(id: number, data: Partial<Product>): Promise<{ data: Product }> {
    const response = await apiClient.put<{ data: Product }>(`/products/${id}`, data)
    return response.data
  },

  async delete(id: number): Promise<void> {
    await apiClient.delete(`/products/${id}`)
  },
}
