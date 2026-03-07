import React, { useEffect, useState, useCallback } from 'react';
import DataTable from '../components/DataTable';
import { inventoryService } from '../services/inventoryService';
import { productService } from '../services/productService';
import type { InventoryItem, InventoryTransaction, Product, Column, PaginationMeta, CreateInventoryTransactionPayload } from '../types';
import { useAuth } from '../hooks/useAuth';

type ActiveTab = 'stock' | 'transactions';

const TRANSACTION_TYPE_COLORS: Record<string, string> = {
  in: 'bg-green-100 text-green-700',
  out: 'bg-red-100 text-red-700',
  adjustment: 'bg-yellow-100 text-yellow-700',
  return: 'bg-blue-100 text-blue-700',
};

function TransactionModal({
  onClose,
  onSubmit,
  products,
  isSubmitting,
}: {
  onClose: () => void;
  onSubmit: (p: CreateInventoryTransactionPayload) => void;
  products: Product[];
  isSubmitting: boolean;
}) {
  const [form, setForm] = useState<CreateInventoryTransactionPayload>({
    product_id: products[0]?.id ?? 0,
    warehouse: 'Main',
    type: 'in',
    quantity: 1,
    reference: '',
    notes: '',
  });

  const set = (field: keyof typeof form) =>
    (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) =>
      setForm((prev) => ({
        ...prev,
        [field]: field === 'quantity' || field === 'product_id' ? Number(e.target.value) : e.target.value,
      }));

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
      <div className="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h2 className="text-lg font-semibold text-gray-900">Record Transaction</h2>
          <button onClick={onClose} className="p-1 rounded-lg hover:bg-gray-100 transition-colors">
            <svg className="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form onSubmit={(e) => { e.preventDefault(); onSubmit(form); }} className="p-6 space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Product</label>
            <select value={form.product_id} onChange={set('product_id')} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
              {products.map((p) => (
                <option key={p.id} value={p.id}>{p.name} ({p.sku})</option>
              ))}
            </select>
          </div>
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
              <select value={form.type} onChange={set('type')} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                <option value="in">Stock In</option>
                <option value="out">Stock Out</option>
                <option value="adjustment">Adjustment</option>
                <option value="return">Return</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
              <input type="number" min="1" value={form.quantity} onChange={set('quantity')} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
            <input value={form.warehouse} onChange={set('warehouse')} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Reference (optional)</label>
            <input value={form.reference} onChange={set('reference')} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
            <textarea value={form.notes} onChange={set('notes')} rows={2} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none resize-none" />
          </div>
          <div className="flex gap-3 pt-2">
            <button type="button" onClick={onClose} className="flex-1 px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="submit" disabled={isSubmitting} className="flex-1 px-4 py-2 text-sm font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-60 transition-colors">
              {isSubmitting ? 'Saving…' : 'Record'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function Inventory() {
  const { hasAnyRole } = useAuth();
  const canEdit = hasAnyRole(['admin', 'manager', 'staff']);

  const [activeTab, setActiveTab] = useState<ActiveTab>('stock');
  const [items, setItems] = useState<InventoryItem[]>([]);
  const [transactions, setTransactions] = useState<InventoryTransaction[]>([]);
  const [itemMeta, setItemMeta] = useState<PaginationMeta | undefined>();
  const [txMeta, setTxMeta] = useState<PaginationMeta | undefined>();
  const [isLoading, setIsLoading] = useState(true);
  const [itemPage, setItemPage] = useState(1);
  const [txPage, setTxPage] = useState(1);
  const [search, setSearch] = useState('');
  const [allProducts, setAllProducts] = useState<Product[]>([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [toast, setToast] = useState<{ type: 'success' | 'error'; msg: string } | null>(null);

  const showToast = (type: 'success' | 'error', msg: string) => {
    setToast({ type, msg });
    setTimeout(() => setToast(null), 3000);
  };

  const loadItems = useCallback(async () => {
    setIsLoading(true);
    try {
      const res = await inventoryService.listItems({ page: itemPage, per_page: 10, search });
      setItems(res.data.data);
      setItemMeta(res.data.meta);
    } catch {
      showToast('error', 'Failed to load inventory');
    } finally {
      setIsLoading(false);
    }
  }, [itemPage, search]);

  const loadTransactions = useCallback(async () => {
    setIsLoading(true);
    try {
      const res = await inventoryService.listTransactions({ page: txPage, per_page: 10 });
      setTransactions(res.data.data);
      setTxMeta(res.data.meta);
    } catch {
      showToast('error', 'Failed to load transactions');
    } finally {
      setIsLoading(false);
    }
  }, [txPage]);

  useEffect(() => {
    productService.list({ per_page: 100 }).then((r) => setAllProducts(r.data.data)).catch(() => {});
  }, []);

  useEffect(() => {
    if (activeTab === 'stock') loadItems();
    else loadTransactions();
  }, [activeTab, loadItems, loadTransactions]);

  const handleTransaction = async (payload: CreateInventoryTransactionPayload) => {
    setIsSubmitting(true);
    try {
      await inventoryService.createTransaction(payload);
      showToast('success', 'Transaction recorded');
      setModalOpen(false);
      loadItems();
      if (activeTab === 'transactions') loadTransactions();
    } catch {
      showToast('error', 'Failed to record transaction');
    } finally {
      setIsSubmitting(false);
    }
  };

  const stockColumns: Column<Record<string, unknown>>[] = [
    {
      key: 'product',
      label: 'Product',
      render: (_, row) => {
        const item = row as unknown as InventoryItem;
        return (
          <div>
            <p className="font-medium text-gray-900 text-sm">{item.product?.name ?? `Product #${item.product_id}`}</p>
            <p className="text-xs text-gray-400 font-mono">{item.product?.sku}</p>
          </div>
        );
      },
    },
    { key: 'warehouse', label: 'Warehouse' },
    {
      key: 'quantity',
      label: 'Stock',
      render: (val, row) => {
        const item = row as unknown as InventoryItem;
        const qty = Number(val);
        const isLow = qty <= item.min_quantity;
        return (
          <div className="flex items-center gap-2">
            <span className={`font-semibold ${isLow ? 'text-red-600' : 'text-gray-900'}`}>{qty}</span>
            {isLow && (
              <span className="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Low</span>
            )}
          </div>
        );
      },
    },
    {
      key: 'min_quantity',
      label: 'Min / Max',
      render: (_, row) => {
        const item = row as unknown as InventoryItem;
        return <span className="text-gray-500 text-xs">{item.min_quantity} / {item.max_quantity}</span>;
      },
    },
    {
      key: 'updated_at',
      label: 'Last Updated',
      render: (val) => new Date(String(val)).toLocaleDateString(),
    },
  ];

  const txColumns: Column<Record<string, unknown>>[] = [
    {
      key: 'product',
      label: 'Product',
      render: (_, row) => {
        const tx = row as unknown as InventoryTransaction;
        return <span className="font-medium text-gray-900 text-sm">{tx.product?.name ?? `#${tx.product_id}`}</span>;
      },
    },
    {
      key: 'type',
      label: 'Type',
      render: (val) => (
        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize ${TRANSACTION_TYPE_COLORS[String(val)] ?? ''}`}>
          {String(val) === 'in' ? 'Stock In' : String(val) === 'out' ? 'Stock Out' : String(val)}
        </span>
      ),
    },
    {
      key: 'quantity',
      label: 'Qty',
      render: (val, row) => {
        const tx = row as unknown as InventoryTransaction;
        const sign = tx.type === 'out' ? '-' : '+';
        return <span className={tx.type === 'out' ? 'text-red-600 font-medium' : 'text-green-600 font-medium'}>{sign}{Number(val)}</span>;
      },
    },
    { key: 'warehouse', label: 'Warehouse' },
    { key: 'reference', label: 'Reference', render: (val) => val ? String(val) : <span className="text-gray-300">—</span> },
    { key: 'created_by', label: 'By' },
    {
      key: 'created_at',
      label: 'Date',
      render: (val) => new Date(String(val)).toLocaleString(),
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
          <h1 className="text-2xl font-bold text-gray-900">Inventory</h1>
          <p className="text-sm text-gray-500 mt-0.5">Track stock levels and transactions</p>
        </div>
        {canEdit && (
          <button
            onClick={() => setModalOpen(true)}
            className="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
          >
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Record Transaction
          </button>
        )}
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200">
        <nav className="flex gap-1">
          {(['stock', 'transactions'] as const).map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`px-4 py-2.5 text-sm font-medium border-b-2 transition-colors capitalize ${
                activeTab === tab
                  ? 'border-primary-600 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700'
              }`}
            >
              {tab === 'stock' ? 'Current Stock' : 'Transactions'}
            </button>
          ))}
        </nav>
      </div>

      {activeTab === 'stock' ? (
        <DataTable
          columns={stockColumns}
          data={items as unknown as Record<string, unknown>[]}
          meta={itemMeta}
          isLoading={isLoading}
          searchPlaceholder="Search products…"
          onPageChange={setItemPage}
          onSearchChange={(v) => { setSearch(v); setItemPage(1); }}
          emptyMessage="No inventory items found."
        />
      ) : (
        <DataTable
          columns={txColumns}
          data={transactions as unknown as Record<string, unknown>[]}
          meta={txMeta}
          isLoading={isLoading}
          searchPlaceholder="Search transactions…"
          onPageChange={setTxPage}
          emptyMessage="No transactions found."
        />
      )}

      {modalOpen && (
        <TransactionModal
          onClose={() => setModalOpen(false)}
          onSubmit={handleTransaction}
          products={allProducts}
          isSubmitting={isSubmitting}
        />
      )}
    </div>
  );
}
