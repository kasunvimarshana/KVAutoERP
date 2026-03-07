import React, { useState, useCallback, useMemo } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Plus, Search, Filter, RefreshCw, Trash2 } from 'lucide-react';
import { inventoryService } from '../services/api/inventoryService';
import { InventoryItem, CreateInventoryPayload, StockAdjustment, QueryParams, PaginatedResponse } from '../types';
import InventoryList from '../components/inventory/InventoryList';
import InventoryForm from '../components/inventory/InventoryForm';
import StockAdjustmentForm from '../components/inventory/StockAdjustmentForm';
import Pagination from '../components/common/Pagination';
import Modal from '../components/common/Modal';

const STATUS_OPTIONS = ['', 'active', 'inactive', 'discontinued'] as const;
const isPaginated = <T,>(data: PaginatedResponse<T> | T[]): data is PaginatedResponse<T> => !Array.isArray(data) && 'data' in data;

const Inventory: React.FC = () => {
  const qc = useQueryClient();
  const [queryParams, setQueryParams] = useState<QueryParams>({ page: 1, perPage: 25, search: '', sortBy: 'name', sortDirection: 'asc', filters: {} });
  const [searchInput, setSearchInput] = useState('');
  const [modalState, setModalState] = useState<{ type: 'create' | 'edit' | 'adjust' | 'delete' | null; item?: InventoryItem }>({ type: null });
  const [selectedIds, setSelectedIds] = useState<string[]>([]);
  const [notification, setNotification] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

  const notify = (message: string, type: 'success' | 'error' = 'success') => { setNotification({ message, type }); setTimeout(() => setNotification(null), 3000); };

  const { data, isLoading, isFetching, refetch } = useQuery({ queryKey: ['inventory', queryParams], queryFn: () => inventoryService.list(queryParams) });
  const items: InventoryItem[] = useMemo(() => { if (!data) return []; return isPaginated(data) ? data.data : data; }, [data]);
  const pagination = useMemo(() => { if (!data || !isPaginated(data)) return null; return data.meta; }, [data]);

  const createMutation = useMutation({ mutationFn: (p: CreateInventoryPayload) => inventoryService.create(p), onSuccess: () => { qc.invalidateQueries({ queryKey: ['inventory'] }); notify('Item created!'); setModalState({ type: null }); }, onError: (err: any) => notify(err?.response?.data?.message || 'Failed to create item', 'error') });
  const updateMutation = useMutation({ mutationFn: ({ id, data }: { id: string; data: Partial<CreateInventoryPayload> }) => inventoryService.update(id, data), onSuccess: () => { qc.invalidateQueries({ queryKey: ['inventory'] }); notify('Item updated!'); setModalState({ type: null }); }, onError: (err: any) => notify(err?.response?.data?.message || 'Failed to update item', 'error') });
  const deleteMutation = useMutation({ mutationFn: (id: string) => inventoryService.delete(id), onSuccess: () => { qc.invalidateQueries({ queryKey: ['inventory'] }); notify('Item deleted!'); setModalState({ type: null }); }, onError: (err: any) => notify(err?.response?.data?.message || 'Failed to delete item', 'error') });
  const adjustMutation = useMutation({ mutationFn: ({ id, adjustment }: { id: string; adjustment: StockAdjustment }) => inventoryService.adjustStock(id, adjustment), onSuccess: () => { qc.invalidateQueries({ queryKey: ['inventory'] }); notify('Stock adjusted!'); setModalState({ type: null }); }, onError: (err: any) => notify(err?.response?.data?.message || 'Failed to adjust stock', 'error') });

  const handleSearch = useCallback(() => setQueryParams((p) => ({ ...p, search: searchInput, page: 1 })), [searchInput]);
  const handleSort = useCallback((column: string) => setQueryParams((p) => ({ ...p, sortBy: column, sortDirection: p.sortBy === column && p.sortDirection === 'asc' ? 'desc' : 'asc', page: 1 })), []);
  const handleFilterStatus = useCallback((status: string) => setQueryParams((p) => ({ ...p, filters: { ...p.filters, status: status || undefined }, page: 1 })), []);
  const handleSelectId = useCallback((id: string) => setSelectedIds((prev) => prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]), []);
  const handleSelectAll = useCallback((checked: boolean) => setSelectedIds(checked ? items.map((i) => i.id) : []), [items]);

  return (
    <div className="p-6 space-y-5">
      {notification && <div className={`fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium ${notification.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`}>{notification.message}</div>}
      <div className="flex items-center justify-between">
        <div><h1 className="text-2xl font-bold text-gray-900">Inventory</h1><p className="text-sm text-gray-500 mt-0.5">{pagination ? `${pagination.total} total items` : `${items.length} items`}</p></div>
        <button onClick={() => setModalState({ type: 'create' })} className="btn-primary"><Plus className="h-4 w-4" />Add Item</button>
      </div>
      <div className="card !p-4 flex flex-wrap gap-3">
        <div className="flex gap-2 flex-1 min-w-0">
          <div className="relative flex-1"><Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" /><input type="text" value={searchInput} onChange={(e) => setSearchInput(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch()} placeholder="Search by name or SKU\u2026" className="input-field pl-9" /></div>
          <button onClick={handleSearch} className="btn-secondary">Search</button>
        </div>
        <div className="flex items-center gap-2"><Filter className="h-4 w-4 text-gray-400" /><select onChange={(e) => handleFilterStatus(e.target.value)} className="input-field !w-auto">{STATUS_OPTIONS.map((s) => <option key={s} value={s}>{s ? s.charAt(0).toUpperCase() + s.slice(1) : 'All Statuses'}</option>)}</select></div>
        <button onClick={() => refetch()} className="btn-secondary" disabled={isFetching}><RefreshCw className={`h-4 w-4 ${isFetching ? 'animate-spin' : ''}`} /></button>
        {selectedIds.length > 0 && <div className="flex items-center gap-2 text-sm text-gray-600"><span>{selectedIds.length} selected</span><button className="btn-danger text-xs" onClick={() => { if (window.confirm(`Delete ${selectedIds.length} item(s)?`)) Promise.all(selectedIds.map((id) => deleteMutation.mutateAsync(id))).then(() => setSelectedIds([])); }}><Trash2 className="h-3 w-3" />Delete Selected</button></div>}
      </div>
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <InventoryList items={items} isLoading={isLoading} onEdit={(item) => setModalState({ type: 'edit', item })} onDelete={(item) => setModalState({ type: 'delete', item })} onAdjustStock={(item) => setModalState({ type: 'adjust', item })} sortBy={queryParams.sortBy} sortDirection={queryParams.sortDirection} onSort={handleSort} selectedIds={selectedIds} onSelectId={handleSelectId} onSelectAll={handleSelectAll} />
        {pagination && <Pagination currentPage={pagination.currentPage} lastPage={pagination.lastPage} total={pagination.total} perPage={pagination.perPage} onPageChange={(page) => setQueryParams((p) => ({ ...p, page }))} onPerPageChange={(perPage) => setQueryParams((p) => ({ ...p, perPage, page: 1 }))} />}
      </div>
      <Modal isOpen={modalState.type === 'create' || modalState.type === 'edit'} onClose={() => setModalState({ type: null })} title={modalState.type === 'edit' ? 'Edit Inventory Item' : 'Add Inventory Item'} size="lg">
        <InventoryForm initialData={modalState.item} onSubmit={async (payload) => { if (modalState.type === 'edit' && modalState.item) await updateMutation.mutateAsync({ id: modalState.item.id, data: payload }); else await createMutation.mutateAsync(payload); }} onCancel={() => setModalState({ type: null })} isSubmitting={createMutation.isPending || updateMutation.isPending} />
      </Modal>
      <Modal isOpen={modalState.type === 'adjust'} onClose={() => setModalState({ type: null })} title="Adjust Stock">
        {modalState.item && <StockAdjustmentForm item={modalState.item} onSubmit={async (adjustment) => { await adjustMutation.mutateAsync({ id: modalState.item!.id, adjustment }); }} onCancel={() => setModalState({ type: null })} isSubmitting={adjustMutation.isPending} />}
      </Modal>
      <Modal isOpen={modalState.type === 'delete'} onClose={() => setModalState({ type: null })} title="Delete Item" size="sm">
        <div className="space-y-4">
          <p className="text-sm text-gray-600">Are you sure you want to delete <strong>{modalState.item?.name}</strong>? This action cannot be undone.</p>
          <div className="flex justify-end gap-3">
            <button onClick={() => setModalState({ type: null })} className="btn-secondary">Cancel</button>
            <button onClick={async () => { if (modalState.item) await deleteMutation.mutateAsync(modalState.item.id); }} disabled={deleteMutation.isPending} className="btn-danger">{deleteMutation.isPending ? 'Deleting\u2026' : 'Delete'}</button>
          </div>
        </div>
      </Modal>
    </div>
  );
};

export default Inventory;
