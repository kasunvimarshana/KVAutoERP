import api from './api';
import type {
  User,
  CreateUserPayload,
  UpdateUserPayload,
  PaginatedResponse,
} from '../types';

export interface UserListParams {
  page?: number;
  per_page?: number;
  search?: string;
  role?: string;
  status?: string;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
}

export const userService = {
  list(params: UserListParams = {}) {
    return api.get<PaginatedResponse<User>>('/users', { params });
  },

  get(id: number) {
    return api.get<{ data: User }>(`/users/${id}`);
  },

  create(payload: CreateUserPayload) {
    return api.post<{ data: User }>('/users', payload);
  },

  update(id: number, payload: UpdateUserPayload) {
    return api.put<{ data: User }>(`/users/${id}`, payload);
  },

  delete(id: number) {
    return api.delete(`/users/${id}`);
  },
};
