import React, { useEffect, useState, useCallback } from 'react';
import DataTable from '../components/DataTable';
import { orderService } from '../services/orderService';
import { productService } from '../services/productService';
import type { Order, CreateOrderPayload, UpdateOrderPayload, OrderStatus, Product, Column, PaginationMeta } from '../types';
import { useAuth } from '../hooks/useAuth';

const STATUS_COLORS: Record<string, string> = {
  pending: 'bg-yellow-100 text-yellow-700',
  confirmed: 'bg-blue-100 text-blue-700',
  processing: 'bg-indigo-100 text-indigo-700',
  shipped: 'bg-purple-100 text-purple-700',
  delivered: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700',
  refunded: 'bg-gray-100 text-gray-600',
};

const PAYMENT_COLORS: Record<string, string> = {
  pending: 'bg-yellow-100 text-yellow-700',
  paid: 'bg-green-100 text-green-700',
  failed: 'bg-red-100 text-red-700',
  refunded: 'bg-gray-100 text-gray-600',
};

interface OrderItem {
  product_id: number;
  quantity: number;
  unit_price: number;
}

function OrderModal({
  onClose,
  onSubmit,
  initial,
  products,
  isSubmitting,
}: {
  onClose: () => void;
  onSubmit: (data: CreateOrderPayload | UpdateOrderPayload) => void;
  initial?: Order | null;
  products: Product[];
  isSubmitting: boolean;
}) {
  const [customerName, setCustomerName] = useState(initial?.customer_name ?? '');
  const [customerEmail, setCustomerEmail] = useState(initial?.customer_email ?? '');
  const [notes, setNotes] = useState(initial?.notes ?? '');
  const [status, setStatus] = useState<OrderStatus>(initial?.status ?? 'pending');
  const [items, setItems] = useState<OrderItem[]>(
    initial?.items.map((i) => ({ product_id: i.product_id, quantity: i.quantity, unit_price: i.unit_price })) ?? [
      { product_id: products[0]?.id ?? 0, quantity: 1, unit_price: products[0]?.unit_price ?? 0 },
    ]
  );

  const addItem = () => setItems((prev) => [...prev, { product_id: products[0]?.id ?? 0, quantity: 1, unit_price: products[0]?.unit_price ?? 0 }]);
  const removeItem = (idx: number) => setItems((prev) => prev.filter((_, i) => i !== idx));
  const updateItem = (idx: number, field: keyof OrderItem, value: number) => {
    setItems((prev) => {
      const next = [...prev];
      next[idx] = { ...next[idx], [field]: value };
      if (field === 'product_id') {
        const p = products.find((pr) => pr.id === value);
        if (p) next[idx].unit_price = p.unit_price;
      }
      return next;
    });
  };

  const total = items.reduce((sum, i) => sum + i.quantity * i.unit_price, 0);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (initial) {
      onSubmit({ status, notes });
    } else {
      onSubmit({ customer_name: customerName, customer_email: customerEmail, items, notes });
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
      <div className="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
        <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
          <h2 className="text-lg font-semibold text-gray-900">{initial ? `Edit Order ${initial.order_number}` : 'New Order'}</h2>
          <button onClick={onClose} className="p-1 rounded-lg hover:bg-gray-100 transition-colors">
            <svg className="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form onSubmit={handleSubmit} className="p-6 space-y-4 overflow-y-auto flex-1">
          {initial ? (
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select value={status} onChange={(e) => setStatus(e.target.value as OrderStatus)} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                  {(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as const).map((s) => (
                    <option key={s} value={s}>{s}</option>
                  ))}
                </select>
              </div>
              <div className="col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea value={notes} onChange={(e) => setNotes(e.target.value)} rows={3} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none resize-none" />
              </div>
            </div>
          ) : (
            <>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                  <input value={customerName} onChange={(e) => setCustomerName(e.target.value)} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Customer Email</label>
                  <input type="email" value={customerEmail} onChange={(e) => setCustomerEmail(e.target.value)} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
                </div>
              </div>

              <div>
                <div className="flex items-center justify-between mb-2">
                  <label className="text-sm font-medium text-gray-700">Items</label>
                  <button type="button" onClick={addItem} className="text-xs text-primary-600 hover:text-primary-700 font-medium">+ Add Item</button>
                </div>
                <div className="space-y-2">
                  {items.map((item, idx) => (
                    <div key={idx} className="flex gap-2 items-center">
                      <select
                        value={item.product_id}
                        onChange={(e) => updateItem(idx, 'product_id', Number(e.target.value))}
                        className="flex-1 border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none"
                      >
                        {products.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                      </select>
                      <input
                        type="number"
                        min="1"
                        value={item.quantity}
                        onChange={(e) => updateItem(idx, 'quantity', Number(e.target.value))}
                        className="w-16 border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-center"
                      />
                      <input
                        type="number"
                        min="0"
                        step="0.01"
                        value={item.unit_price}
                        onChange={(e) => updateItem(idx, 'unit_price', Number(e.target.value))}
                        className="w-24 border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none"
                      />
                      <button type="button" onClick={() => removeItem(idx)} disabled={items.length === 1} className="p-1.5 rounded text-gray-400 hover:text-red-500 disabled:opacity-30">
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
                      </button>
                    </div>
                  ))}
                </div>
                <div className="text-right text-sm font-semibold text-gray-900 mt-2">
                  Total: ${total.toFixed(2)}
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                <textarea value={notes} onChange={(e) => setNotes(e.target.value)} rows={2} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none resize-none" />
              </div>
            </>
          )}

          <div className="flex gap-3 pt-2">
            <button type="button" onClick={onClose} className="flex-1 px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="submit" disabled={isSubmitting} className="flex-1 px-4 py-2 text-sm font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-60 transition-colors">
              {isSubmitting ? 'Saving…' : 'Save'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function Orders() {
  const { hasAnyRole } = useAuth();
  const canEdit = hasAnyRole(['admin', 'manager']);

  const [orders, setOrders] = useState<Order[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | undefined>();
  const [isLoading, setIsLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [sortKey, setSortKey] = useState('created_at');
  const [sortDir, setSortDir] = useState<'asc' | 'desc'>('desc');
  const [allProducts, setAllProducts] = useState<Product[]>([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [editTarget, setEditTarget] = useState<Order | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [cancelTarget, setCancelTarget] = useState<Order | null>(null);
  const [toast, setToast] = useState<{ type: 'success' | 'error'; msg: string } | null>(null);

  const showToast = (type: 'success' | 'error', msg: string) => {
    setToast({ type, msg });
    setTimeout(() => setToast(null), 3000);
  };

  const load = useCallback(async () => {
    setIsLoading(true);
    try {
      const res = await orderService.list({
        page,
        per_page: 10,
        search,
        status: statusFilter || undefined,
        sort_by: sortKey,
        sort_dir: sortDir,
      });
      setOrders(res.data.data);
      setMeta(res.data.meta);
    } catch {
      showToast('error', 'Failed to load orders');
    } finally {
      setIsLoading(false);
    }
  }, [page, search, statusFilter, sortKey, sortDir]);

  useEffect(() => {
    productService.list({ per_page: 100 }).then((r) => setAllProducts(r.data.data)).catch(() => {});
  }, []);

  useEffect(() => { load(); }, [load]);

  const handleSubmit = async (payload: CreateOrderPayload | UpdateOrderPayload) => {
    setIsSubmitting(true);
    try {
      if (editTarget) {
        await orderService.update(editTarget.id, payload as UpdateOrderPayload);
        showToast('success', 'Order updated');
      } else {
        await orderService.create(payload as CreateOrderPayload);
        showToast('success', 'Order created');
      }
      setModalOpen(false);
      setEditTarget(null);
      load();
    } catch {
      showToast('error', 'Operation failed');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleCancel = async () => {
    if (!cancelTarget) return;
    try {
      await orderService.cancel(cancelTarget.id);
      showToast('success', 'Order cancelled');
      setCancelTarget(null);
      load();
    } catch {
      showToast('error', 'Cancel failed');
    }
  };

  const columns: Column<Record<string, unknown>>[] = [
    {
      key: 'order_number',
      label: 'Order #',
      sortable: true,
      render: (val) => <span className="font-medium text-primary-600 font-mono text-sm">{String(val)}</span>,
    },
    {
      key: 'customer_name',
      label: 'Customer',
      sortable: true,
      render: (_, row) => {
        const o = row as unknown as Order;
        return (
          <div>
            <p className="font-medium text-gray-900 text-sm">{o.customer_name}</p>
            <p className="text-xs text-gray-400">{o.customer_email}</p>
          </div>
        );
      },
    },
    {
      key: 'total',
      label: 'Total',
      sortable: true,
      render: (val) => <span className="font-semibold">${Number(val).toFixed(2)}</span>,
    },
    {
      key: 'status',
      label: 'Status',
      render: (val) => (
        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize ${STATUS_COLORS[String(val)] ?? ''}`}>
          {String(val)}
        </span>
      ),
    },
    {
      key: 'payment_status',
      label: 'Payment',
      render: (val) => (
        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize ${PAYMENT_COLORS[String(val)] ?? ''}`}>
          {String(val)}
        </span>
      ),
    },
    {
      key: 'created_at',
      label: 'Date',
      sortable: true,
      render: (val) => new Date(String(val)).toLocaleDateString(),
    },
  ];

  return (
    <div className="space-y-5">
      {toast && (
        <div className={`fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white ${toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'}`}>
          {toast.msg}
        </div>
      )}

      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Orders</h1>
          <p className="text-sm text-gray-500 mt-0.5">Manage customer orders</p>
        </div>
        {canEdit && (
          <button
            onClick={() => { setEditTarget(null); setModalOpen(true); }}
            className="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
          >
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            New Order
          </button>
        )}
      </div>

      <DataTable
        columns={columns}
        data={orders as unknown as Record<string, unknown>[]}
        meta={meta}
        isLoading={isLoading}
        searchPlaceholder="Search orders…"
        filterOptions={[
          {
            key: 'status',
            label: 'Status',
            options: ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'].map((s) => ({ label: s, value: s })),
          },
        ]}
        onPageChange={setPage}
        onSearchChange={(v) => { setSearch(v); setPage(1); }}
        onFilterChange={(k, v) => { if (k === 'status') { setStatusFilter(v); setPage(1); } }}
        onSortChange={(k, d) => { setSortKey(k); setSortDir(d); }}
        sortKey={sortKey}
        sortDir={sortDir}
        emptyMessage="No orders found."
        actions={
          canEdit
            ? (row) => {
                const o = row as unknown as Order;
                const canCancel = !['cancelled', 'delivered', 'refunded'].includes(o.status);
                return (
                  <div className="flex items-center justify-end gap-2">
                    <button onClick={() => { setEditTarget(o); setModalOpen(true); }} className="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition-colors" title="Edit">
                      <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                    {canCancel && (
                      <button onClick={() => setCancelTarget(o)} className="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Cancel">
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                      </button>
                    )}
                  </div>
                );
              }
            : undefined
        }
      />

      {modalOpen && (
        <OrderModal
          onClose={() => { setModalOpen(false); setEditTarget(null); }}
          onSubmit={handleSubmit}
          initial={editTarget}
          products={allProducts}
          isSubmitting={isSubmitting}
        />
      )}

      {cancelTarget && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-2">Cancel Order?</h3>
            <p className="text-sm text-gray-500 mb-5">Cancel order <strong>{cancelTarget.order_number}</strong>? This cannot be undone.</p>
            <div className="flex gap-3">
              <button onClick={() => setCancelTarget(null)} className="flex-1 px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Keep</button>
              <button onClick={handleCancel} className="flex-1 px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">Cancel Order</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
