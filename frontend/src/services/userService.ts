import apiClient from './api'
import { PaginatedResponse, User } from '../types'

export interface UserFilters {
  search?: string
  role?: string
  per_page?: number
  page?: number
  sort_by?: string
  sort_dir?: 'asc' | 'desc'
}

export const userService = {
  async list(filters: UserFilters = {}): Promise<PaginatedResponse<User>> {
    const response = await apiClient.get<PaginatedResponse<User>>('/users', { params: filters })
    return response.data
  },

  async get(id: number): Promise<{ data: User }> {
    const response = await apiClient.get<{ data: User }>(`/users/${id}`)
    return response.data
  },

  async create(data: Partial<User> & { password: string; password_confirmation: string; role?: string }): Promise<{ data: User }> {
    const response = await apiClient.post<{ data: User }>('/users', data)
    return response.data
  },

  async update(id: number, data: Partial<User> & { password?: string; password_confirmation?: string; role?: string }): Promise<{ data: User }> {
    const response = await apiClient.put<{ data: User }>(`/users/${id}`, data)
    return response.data
  },

  async delete(id: number): Promise<void> {
    await apiClient.delete(`/users/${id}`)
  },
}
