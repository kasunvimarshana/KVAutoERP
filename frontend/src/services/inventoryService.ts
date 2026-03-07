import api from './api';
import type {
  InventoryItem,
  InventoryTransaction,
  CreateInventoryTransactionPayload,
  PaginatedResponse,
} from '../types';

export interface InventoryListParams {
  page?: number;
  per_page?: number;
  search?: string;
  warehouse?: string;
  low_stock?: boolean;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
}

export interface TransactionListParams {
  page?: number;
  per_page?: number;
  product_id?: number;
  type?: string;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
}

export const inventoryService = {
  listItems(params: InventoryListParams = {}) {
    return api.get<PaginatedResponse<InventoryItem>>('/inventory', { params });
  },

  getItem(id: number) {
    return api.get<{ data: InventoryItem }>(`/inventory/${id}`);
  },

  listTransactions(params: TransactionListParams = {}) {
    return api.get<PaginatedResponse<InventoryTransaction>>('/inventory/transactions', { params });
  },

  createTransaction(payload: CreateInventoryTransactionPayload) {
    return api.post<{ data: InventoryTransaction }>('/inventory/transactions', payload);
  },

  adjustStock(productId: number, warehouse: string, quantity: number, notes?: string) {
    return api.post<{ data: InventoryTransaction }>('/inventory/transactions', {
      product_id: productId,
      warehouse,
      type: 'adjustment',
      quantity,
      notes,
    });
  },
};
