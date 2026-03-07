import React, { useState, useCallback, useMemo } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Plus, Search, RefreshCw, Filter } from 'lucide-react';
import { orderService } from '../services/api/orderService';
import { inventoryService } from '../services/api/inventoryService';
import { Order, CreateOrderPayload, QueryParams, PaginatedResponse, InventoryItem } from '../types';
import OrderList from '../components/orders/OrderList';
import OrderDetail from '../components/orders/OrderDetail';
import OrderForm from '../components/orders/OrderForm';
import Pagination from '../components/common/Pagination';
import Modal from '../components/common/Modal';

type ModalType = 'create' | 'detail' | 'cancel' | null;
const ORDER_STATUSES = ['', 'pending', 'confirmed', 'processing', 'completed', 'cancelled', 'failed'] as const;
const isPaginatedOrders = (data: PaginatedResponse<Order> | Order[]): data is PaginatedResponse<Order> => !Array.isArray(data) && 'data' in data;
const isPaginatedItems = (data: PaginatedResponse<InventoryItem> | InventoryItem[]): data is PaginatedResponse<InventoryItem> => !Array.isArray(data) && 'data' in data;

const Orders: React.FC = () => {
  const qc = useQueryClient();
  const [queryParams, setQueryParams] = useState<QueryParams>({ page: 1, perPage: 25, search: '', sortBy: 'createdAt', sortDirection: 'desc', filters: {} });
  const [searchInput, setSearchInput] = useState('');
  const [modal, setModal] = useState<{ type: ModalType; order?: Order }>({ type: null });
  const [notification, setNotification] = useState<{ message: string; type: 'success' | 'error' } | null>(null);
  const [cancelReason, setCancelReason] = useState('');

  const notify = (message: string, type: 'success' | 'error' = 'success') => { setNotification({ message, type }); setTimeout(() => setNotification(null), 3000); };

  const { data, isLoading, isFetching, refetch } = useQuery({ queryKey: ['orders', queryParams], queryFn: () => orderService.list(queryParams) });
  const { data: inventoryData } = useQuery({ queryKey: ['inventory', 'all'], queryFn: () => inventoryService.list({ perPage: 200 }) });

  const orders: Order[] = useMemo(() => { if (!data) return []; return isPaginatedOrders(data) ? data.data : data; }, [data]);
  const pagination = useMemo(() => { if (!data || !isPaginatedOrders(data)) return null; return data.meta; }, [data]);
  const inventoryItems: InventoryItem[] = useMemo(() => { if (!inventoryData) return []; return isPaginatedItems(inventoryData) ? inventoryData.data : inventoryData; }, [inventoryData]);

  const createMutation = useMutation({ mutationFn: (p: CreateOrderPayload) => orderService.create(p), onSuccess: () => { qc.invalidateQueries({ queryKey: ['orders'] }); notify('Order created!'); setModal({ type: null }); }, onError: (err: any) => notify(err?.response?.data?.message || 'Failed to create order', 'error') });
  const cancelMutation = useMutation({ mutationFn: ({ id, reason }: { id: string; reason?: string }) => orderService.cancel(id, reason), onSuccess: () => { qc.invalidateQueries({ queryKey: ['orders'] }); notify('Order cancelled.'); setModal({ type: null }); }, onError: (err: any) => notify(err?.response?.data?.message || 'Failed to cancel order', 'error') });

  const handleSearch = useCallback(() => setQueryParams((p) => ({ ...p, search: searchInput, page: 1 })), [searchInput]);
  const handleFilterStatus = useCallback((status: string) => setQueryParams((p) => ({ ...p, filters: { ...p.filters, status: status || undefined }, page: 1 })), []);

  return (
    <div className="p-6 space-y-5">
      {notification && <div className={`fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium ${notification.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`}>{notification.message}</div>}
      <div className="flex items-center justify-between">
        <div><h1 className="text-2xl font-bold text-gray-900">Orders</h1><p className="text-sm text-gray-500 mt-0.5">{pagination ? `${pagination.total} total orders` : `${orders.length} orders`}</p></div>
        <button onClick={() => setModal({ type: 'create' })} className="btn-primary"><Plus className="h-4 w-4" />New Order</button>
      </div>
      <div className="card !p-4 flex flex-wrap gap-3">
        <div className="flex gap-2 flex-1 min-w-0">
          <div className="relative flex-1"><Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" /><input type="text" value={searchInput} onChange={(e) => setSearchInput(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch()} placeholder="Search by customer or order number\u2026" className="input-field pl-9" /></div>
          <button onClick={handleSearch} className="btn-secondary">Search</button>
        </div>
        <div className="flex items-center gap-2"><Filter className="h-4 w-4 text-gray-400" /><select onChange={(e) => handleFilterStatus(e.target.value)} className="input-field !w-auto">{ORDER_STATUSES.map((s) => <option key={s} value={s}>{s ? s.charAt(0).toUpperCase() + s.slice(1) : 'All Statuses'}</option>)}</select></div>
        <button onClick={() => refetch()} className="btn-secondary" disabled={isFetching}><RefreshCw className={`h-4 w-4 ${isFetching ? 'animate-spin' : ''}`} /></button>
      </div>
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <OrderList orders={orders} isLoading={isLoading} onCancel={(order) => { setModal({ type: 'cancel', order }); setCancelReason(''); }} onViewDetails={(order) => setModal({ type: 'detail', order })} />
        {pagination && <Pagination currentPage={pagination.currentPage} lastPage={pagination.lastPage} total={pagination.total} perPage={pagination.perPage} onPageChange={(page) => setQueryParams((p) => ({ ...p, page }))} onPerPageChange={(perPage) => setQueryParams((p) => ({ ...p, perPage, page: 1 }))} />}
      </div>
      <Modal isOpen={modal.type === 'create'} onClose={() => setModal({ type: null })} title="Create New Order" size="xl">
        <OrderForm inventoryItems={inventoryItems} onSubmit={async (payload) => { await createMutation.mutateAsync(payload); }} onCancel={() => setModal({ type: null })} isSubmitting={createMutation.isPending} />
      </Modal>
      <Modal isOpen={modal.type === 'detail'} onClose={() => setModal({ type: null })} title={`Order #${modal.order?.orderNumber || ''}`} size="lg">
        {modal.order && <OrderDetail order={modal.order} />}
      </Modal>
      <Modal isOpen={modal.type === 'cancel'} onClose={() => setModal({ type: null })} title="Cancel Order" size="sm">
        <div className="space-y-4">
          <p className="text-sm text-gray-600">Are you sure you want to cancel order <strong>#{modal.order?.orderNumber}</strong>?</p>
          <div><label className="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label><input type="text" value={cancelReason} onChange={(e) => setCancelReason(e.target.value)} placeholder="Customer request, out of stock\u2026" className="input-field" /></div>
          <div className="flex justify-end gap-3">
            <button onClick={() => setModal({ type: null })} className="btn-secondary">Back</button>
            <button onClick={async () => { if (modal.order) await cancelMutation.mutateAsync({ id: modal.order.id, reason: cancelReason || undefined }); }} disabled={cancelMutation.isPending} className="btn-danger">{cancelMutation.isPending ? 'Cancelling\u2026' : 'Cancel Order'}</button>
          </div>
        </div>
      </Modal>
    </div>
  );
};

export default Orders;
