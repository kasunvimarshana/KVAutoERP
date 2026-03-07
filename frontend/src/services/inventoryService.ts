import apiClient from './api'
import { Inventory, PaginatedResponse } from '../types'

export interface InventoryFilters {
  product_id?: number
  low_stock?: boolean
  per_page?: number
  page?: number
  sort_by?: string
  sort_dir?: 'asc' | 'desc'
}

export const inventoryService = {
  async list(filters: InventoryFilters = {}): Promise<PaginatedResponse<Inventory>> {
    const response = await apiClient.get<PaginatedResponse<Inventory>>('/inventory', { params: filters })
    return response.data
  },

  async get(id: number): Promise<{ data: Inventory }> {
    const response = await apiClient.get<{ data: Inventory }>(`/inventory/${id}`)
    return response.data
  },

  async update(id: number, data: Partial<Inventory>): Promise<{ data: Inventory }> {
    const response = await apiClient.put<{ data: Inventory }>(`/inventory/${id}`, data)
    return response.data
  },

  async adjust(id: number, adjustment: number, reason?: string): Promise<{ data: Inventory }> {
    const response = await apiClient.post<{ data: Inventory }>(`/inventory/${id}/adjust`, { adjustment, reason })
    return response.data
  },

  async delete(id: number): Promise<void> {
    await apiClient.delete(`/inventory/${id}`)
  },
}
