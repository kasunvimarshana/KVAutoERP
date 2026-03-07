import React, { useEffect, useState, useCallback } from 'react';
import DataTable from '../components/DataTable';
import { productService } from '../services/productService';
import type { Product, CreateProductPayload, Column, PaginationMeta } from '../types';
import { useAuth } from '../hooks/useAuth';

const STATUS_COLORS: Record<string, string> = {
  active: 'bg-green-100 text-green-700',
  inactive: 'bg-gray-100 text-gray-600',
  discontinued: 'bg-red-100 text-red-700',
};

const EMPTY_FORM: CreateProductPayload = {
  name: '',
  sku: '',
  description: '',
  category: '',
  unit_price: 0,
  cost_price: 0,
  status: 'active',
};

interface ModalProps {
  onClose: () => void;
  onSubmit: (data: CreateProductPayload) => void;
  initial?: Product | null;
  isSubmitting: boolean;
}

function ProductModal({ onClose, onSubmit, initial, isSubmitting }: ModalProps) {
  const [form, setForm] = useState<CreateProductPayload>(
    initial
      ? {
          name: initial.name,
          sku: initial.sku,
          description: initial.description ?? '',
          category: initial.category,
          unit_price: initial.unit_price,
          cost_price: initial.cost_price,
          status: initial.status,
        }
      : { ...EMPTY_FORM }
  );

  const setField = (field: keyof CreateProductPayload) =>
    (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) =>
      setForm((prev) => ({
        ...prev,
        [field]: field === 'unit_price' || field === 'cost_price' ? parseFloat(e.target.value) || 0 : e.target.value,
      }));

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
      <div className="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">
        <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
          <h2 className="text-lg font-semibold text-gray-900">{initial ? 'Edit Product' : 'New Product'}</h2>
          <button onClick={onClose} className="p-1 rounded-lg hover:bg-gray-100 transition-colors">
            <svg className="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form
          onSubmit={(e) => { e.preventDefault(); onSubmit(form); }}
          className="p-6 space-y-4 overflow-y-auto"
        >
          <div className="grid grid-cols-2 gap-3">
            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
              <input value={form.name} onChange={setField('name')} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">SKU</label>
              <input value={form.sku} onChange={setField('sku')} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <input value={form.category} onChange={setField('category')} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Unit Price ($)</label>
              <input type="number" min="0" step="0.01" value={form.unit_price} onChange={setField('unit_price')} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Cost Price ($)</label>
              <input type="number" min="0" step="0.01" value={form.cost_price} onChange={setField('cost_price')} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
            </div>
            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select value={form.status} onChange={setField('status')} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="discontinued">Discontinued</option>
              </select>
            </div>
            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea value={form.description} onChange={setField('description')} rows={3} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none resize-none" />
            </div>
          </div>
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

export default function Products() {
  const { hasAnyRole } = useAuth();
  const canEdit = hasAnyRole(['admin', 'manager']);

  const [products, setProducts] = useState<Product[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | undefined>();
  const [isLoading, setIsLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [sortKey, setSortKey] = useState('created_at');
  const [sortDir, setSortDir] = useState<'asc' | 'desc'>('desc');
  const [modalOpen, setModalOpen] = useState(false);
  const [editTarget, setEditTarget] = useState<Product | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<Product | null>(null);
  const [toast, setToast] = useState<{ type: 'success' | 'error'; msg: string } | null>(null);

  const showToast = (type: 'success' | 'error', msg: string) => {
    setToast({ type, msg });
    setTimeout(() => setToast(null), 3000);
  };

  const load = useCallback(async () => {
    setIsLoading(true);
    try {
      const res = await productService.list({
        page,
        per_page: 10,
        search,
        status: statusFilter || undefined,
        sort_by: sortKey,
        sort_dir: sortDir,
      });
      setProducts(res.data.data);
      setMeta(res.data.meta);
    } catch {
      showToast('error', 'Failed to load products');
    } finally {
      setIsLoading(false);
    }
  }, [page, search, statusFilter, sortKey, sortDir]);

  useEffect(() => { load(); }, [load]);

  const handleSubmit = async (payload: CreateProductPayload) => {
    setIsSubmitting(true);
    try {
      if (editTarget) {
        await productService.update(editTarget.id, payload);
        showToast('success', 'Product updated');
      } else {
        await productService.create(payload);
        showToast('success', 'Product created');
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

  const handleDelete = async () => {
    if (!deleteTarget) return;
    try {
      await productService.delete(deleteTarget.id);
      showToast('success', 'Product deleted');
      setDeleteTarget(null);
      load();
    } catch {
      showToast('error', 'Delete failed');
    }
  };

  const columns: Column<Record<string, unknown>>[] = [
    {
      key: 'name',
      label: 'Product',
      sortable: true,
      render: (_, row) => {
        const p = row as unknown as Product;
        return (
          <div>
            <p className="font-medium text-gray-900 text-sm">{p.name}</p>
            <p className="text-xs text-gray-400 font-mono">{p.sku}</p>
          </div>
        );
      },
    },
    { key: 'category', label: 'Category', sortable: true },
    {
      key: 'unit_price',
      label: 'Price',
      sortable: true,
      render: (val) => <span className="font-medium">${Number(val).toFixed(2)}</span>,
    },
    {
      key: 'cost_price',
      label: 'Cost',
      render: (val) => `$${Number(val).toFixed(2)}`,
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
      key: 'created_at',
      label: 'Added',
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
          <h1 className="text-2xl font-bold text-gray-900">Products</h1>
          <p className="text-sm text-gray-500 mt-0.5">Manage your product catalogue</p>
        </div>
        {canEdit && (
          <button
            onClick={() => { setEditTarget(null); setModalOpen(true); }}
            className="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
          >
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Add Product
          </button>
        )}
      </div>

      <DataTable
        columns={columns}
        data={products as unknown as Record<string, unknown>[]}
        meta={meta}
        isLoading={isLoading}
        searchPlaceholder="Search products…"
        filterOptions={[
          {
            key: 'status',
            label: 'Status',
            options: [
              { label: 'Active', value: 'active' },
              { label: 'Inactive', value: 'inactive' },
              { label: 'Discontinued', value: 'discontinued' },
            ],
          },
        ]}
        onPageChange={setPage}
        onSearchChange={(v) => { setSearch(v); setPage(1); }}
        onFilterChange={(k, v) => { if (k === 'status') { setStatusFilter(v); setPage(1); } }}
        onSortChange={(k, d) => { setSortKey(k); setSortDir(d); }}
        sortKey={sortKey}
        sortDir={sortDir}
        emptyMessage="No products found."
        actions={
          canEdit
            ? (row) => {
                const p = row as unknown as Product;
                return (
                  <div className="flex items-center justify-end gap-2">
                    <button onClick={() => { setEditTarget(p); setModalOpen(true); }} className="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition-colors" title="Edit">
                      <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                    <button onClick={() => setDeleteTarget(p)} className="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                      <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                  </div>
                );
              }
            : undefined
        }
      />

      {modalOpen && (
        <ProductModal
          onClose={() => { setModalOpen(false); setEditTarget(null); }}
          onSubmit={handleSubmit}
          initial={editTarget}
          isSubmitting={isSubmitting}
        />
      )}

      {deleteTarget && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-2">Delete Product?</h3>
            <p className="text-sm text-gray-500 mb-5">Delete <strong>{deleteTarget.name}</strong>? This cannot be undone.</p>
            <div className="flex gap-3">
              <button onClick={() => setDeleteTarget(null)} className="flex-1 px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
              <button onClick={handleDelete} className="flex-1 px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">Delete</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
