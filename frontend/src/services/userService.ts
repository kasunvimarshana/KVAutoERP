import api from './api';

const BASE = '/api/v1/users';

export interface User {
  id: number;
  keycloak_id?: string;
  username: string;
  email: string;
  first_name: string;
  last_name: string;
  full_name: string;
  phone?: string;
  roles: string[];
  attributes: Record<string, unknown>;
  is_active: boolean;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export const userService = {
  list: (params?: Record<string, unknown>) =>
    api.get(BASE, { params }).then((r) => r.data),

  get: (id: number) =>
    api.get(`${BASE}/${id}`).then((r) => r.data),

  create: (data: Partial<User> & { password: string }) =>
    api.post(BASE, data).then((r) => r.data),

  update: (id: number, data: Partial<User>) =>
    api.put(`${BASE}/${id}`, data).then((r) => r.data),

  delete: (id: number) =>
    api.delete(`${BASE}/${id}`),

  checkRole: (id: number, role: string) =>
    api.post(`${BASE}/${id}/check-role`, { role }).then((r) => r.data),

  checkAttribute: (id: number, key: string, value: unknown) =>
    api.post(`${BASE}/${id}/check-attribute`, { key, value }).then((r) => r.data),
};
