import apiClient from './api'
import { Order, OrderStatus, PaginatedResponse } from '../types'

export interface OrderFilters {
  status?: OrderStatus
  user_id?: number
  per_page?: number
  page?: number
  sort_by?: string
  sort_dir?: 'asc' | 'desc'
}

export interface CreateOrderData {
  tenant_id: number
  user_id: number
  items: Array<{ product_id: number; quantity: number; unit_price: number }>
  notes?: string
}

export const orderService = {
  async list(filters: OrderFilters = {}): Promise<PaginatedResponse<Order>> {
    const response = await apiClient.get<PaginatedResponse<Order>>('/orders', { params: filters })
    return response.data
  },

  async get(id: number): Promise<{ data: Order }> {
    const response = await apiClient.get<{ data: Order }>(`/orders/${id}`)
    return response.data
  },

  async create(data: CreateOrderData): Promise<{ data: Order }> {
    const response = await apiClient.post<{ data: Order }>('/orders', data)
    return response.data
  },

  async updateStatus(id: number, status: OrderStatus): Promise<{ data: Order }> {
    const response = await apiClient.patch<{ data: Order }>(`/orders/${id}/status`, { status })
    return response.data
  },

  async delete(id: number): Promise<void> {
    await apiClient.delete(`/orders/${id}`)
  },
}
