import api from './axios';

// ---- Auth ----------------------------------------------------------------
export const authApi = {
  login:    (data: { email: string; password: string }) => api.post('/auth/login', data),
  register: (data: object) => api.post('/auth/register', data),
  logout:   () => api.post('/auth/logout'),
  me:       () => api.get('/auth/me'),
  ssoToken: () => api.post('/auth/sso-token'),
};

// ---- Users ---------------------------------------------------------------
export const usersApi = {
  list:       (params?: object) => api.get('/users', { params }),
  create:     (data: object)    => api.post('/users', data),
  get:        (id: number)      => api.get(`/users/${id}`),
  update:     (id: number, data: object) => api.put(`/users/${id}`, data),
  remove:     (id: number)      => api.delete(`/users/${id}`),
  assignRole: (id: number, role: string) => api.post(`/users/${id}/roles/assign`, { role }),
  revokeRole: (id: number, role: string) => api.post(`/users/${id}/roles/revoke`, { role }),
};

// ---- Products ------------------------------------------------------------
export const productsApi = {
  list:   (params?: object) => api.get('/products', { params }),
  create: (data: object)    => api.post('/products', data),
  get:    (id: number)      => api.get(`/products/${id}`),
  update: (id: number, data: object) => api.put(`/products/${id}`, data),
  remove: (id: number)      => api.delete(`/products/${id}`),
};

// ---- Inventory -----------------------------------------------------------
export const inventoryApi = {
  list:    (params?: object) => api.get('/inventory', { params }),
  create:  (data: object)    => api.post('/inventory', data),
  get:     (id: number)      => api.get(`/inventory/${id}`),
  update:  (id: number, data: object) => api.put(`/inventory/${id}`, data),
  remove:  (id: number)      => api.delete(`/inventory/${id}`),
  adjust:  (id: number, delta: number, reason?: string) =>
    api.patch(`/inventory/${id}/adjust`, { delta, reason }),
  reserve: (id: number, quantity: number) =>
    api.patch(`/inventory/${id}/reserve`, { quantity }),
  release: (id: number, quantity: number) =>
    api.patch(`/inventory/${id}/release`, { quantity }),
};

// ---- Orders --------------------------------------------------------------
export const ordersApi = {
  list:   (params?: object) => api.get('/orders', { params }),
  create: (data: object)    => api.post('/orders', data),
  get:    (id: number)      => api.get(`/orders/${id}`),
  cancel: (id: number)      => api.patch(`/orders/${id}/cancel`),
};

// ---- Tenant Config -------------------------------------------------------
export const tenantConfigApi = {
  list:   () => api.get('/tenant/config'),
  upsert: (data: object) => api.post('/tenant/config', data),
  remove: (key: string)  => api.delete(`/tenant/config/${key}`),
};
