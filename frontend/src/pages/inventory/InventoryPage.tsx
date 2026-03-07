import React, { useState, useEffect } from 'react';
import { inventoryApi, productsApi } from '../../api/endpoints';
import type { Inventory, Product } from '../../types';

export default function InventoryPage() {
  const [items, setItems]       = useState<Inventory[]>([]);
  const [total, setTotal]       = useState(0);
  const [page, setPage]         = useState(1);
  const [perPage]               = useState(10);
  const [productName, setProductName] = useState('');
  const [loading, setLoading]   = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [adjustItem, setAdjustItem] = useState<Inventory | null>(null);
  const [adjustDelta, setAdjustDelta] = useState('');
  const [products, setProducts] = useState<Product[]>([]);
  const [form, setForm]         = useState({ product_id: '', warehouse: '', quantity: '', min_quantity: '0', location: '' });
  const [error, setError]       = useState('');

  const load = async () => {
    setLoading(true);
    try {
      const res = await inventoryApi.list({ per_page: perPage, page, product_name: productName || undefined });
      setItems(res.data.data);
      setTotal(res.data.meta.total);
    } catch (e: any) { setError(e.response?.data?.message ?? 'Load failed.'); }
    finally { setLoading(false); }
  };

  useEffect(() => { load(); }, [page, productName]);
  useEffect(() => {
    productsApi.list({ per_page: 100 }).then(res => setProducts(res.data.data));
  }, []);

  const handleSave = async () => {
    try {
      await inventoryApi.create({ ...form, product_id: parseInt(form.product_id), quantity: parseInt(form.quantity), min_quantity: parseInt(form.min_quantity) });
      setShowForm(false);
      setForm({ product_id: '', warehouse: '', quantity: '', min_quantity: '0', location: '' });
      load();
    } catch (e: any) { setError(e.response?.data?.message ?? 'Save failed.'); }
  };

  const handleAdjust = async () => {
    if (!adjustItem) return;
    try {
      await inventoryApi.adjust(adjustItem.id, parseInt(adjustDelta));
      setAdjustItem(null); setAdjustDelta('');
      load();
    } catch (e: any) { setError(e.response?.data?.message ?? 'Adjust failed.'); }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Delete this inventory record?')) return;
    await inventoryApi.remove(id); load();
  };

  const lastPage = Math.ceil(total / perPage);

  return (
    <div className="p-8">
      <div className="flex items-center justify-between mb-6">
        <div><h2 className="text-2xl font-bold text-gray-800">Inventory</h2><p className="text-sm text-gray-500">{total} records</p></div>
        <button onClick={() => setShowForm(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ New Record</button>
      </div>

      {error && <div className="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{error}</div>}

      {/* Cross-service filter by product name */}
      <div className="mb-4 flex gap-3">
        <input value={productName} onChange={e => { setProductName(e.target.value); setPage(1); }}
          placeholder="Filter by product name (cross-service)…"
          className="w-full max-w-sm border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 border-b">
            <tr>{['Product', 'Warehouse', 'Qty', 'Reserved', 'Available', 'Min', 'Actions'].map(h =>
              <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{h}</th>)}</tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {loading ? <tr><td colSpan={7} className="px-4 py-8 text-center text-gray-400">Loading…</td></tr>
              : items.map(item => (
                <tr key={item.id} className={`hover:bg-gray-50 ${item.quantity <= item.min_quantity ? 'bg-red-50' : ''}`}>
                  <td className="px-4 py-3 font-medium text-gray-800">{item.product?.name ?? `#${item.product_id}`}</td>
                  <td className="px-4 py-3 text-gray-600">{item.warehouse}</td>
                  <td className="px-4 py-3 font-bold">{item.quantity}</td>
                  <td className="px-4 py-3 text-orange-600">{item.reserved_quantity}</td>
                  <td className="px-4 py-3 text-green-700 font-medium">{item.quantity - item.reserved_quantity}</td>
                  <td className="px-4 py-3 text-gray-500">{item.min_quantity}</td>
                  <td className="px-4 py-3 flex gap-1">
                    <button onClick={() => { setAdjustItem(item); setAdjustDelta(''); }}
                      className="text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 rounded px-2 py-1">Adjust</button>
                    <button onClick={() => handleDelete(item.id)}
                      className="text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded px-2 py-1">Del</button>
                  </td>
                </tr>
              ))}
          </tbody>
        </table>
        {lastPage > 1 && (
          <div className="flex items-center justify-between px-4 py-3 border-t text-sm">
            <span className="text-gray-500">Page {page} of {lastPage}</span>
            <div className="flex gap-2">
              <button disabled={page === 1} onClick={() => setPage(p => p - 1)} className="px-3 py-1 border rounded disabled:opacity-40">Prev</button>
              <button disabled={page === lastPage} onClick={() => setPage(p => p + 1)} className="px-3 py-1 border rounded disabled:opacity-40">Next</button>
            </div>
          </div>
        )}
      </div>

      {/* New Inventory Form */}
      {showForm && (
        <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 className="text-lg font-semibold mb-4">New Inventory Record</h3>
            <div className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">Product</label>
                <select value={form.product_id} onChange={e => setForm(p => ({ ...p, product_id: e.target.value }))}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                  <option value="">Select product…</option>
                  {products.map(p => <option key={p.id} value={p.id}>{p.name} ({p.sku})</option>)}
                </select>
              </div>
              {([['warehouse', 'Warehouse'], ['quantity', 'Quantity'], ['min_quantity', 'Min Quantity'], ['location', 'Location']] as [string, string][]).map(([field, label]) => (
                <div key={field}>
                  <label className="block text-xs font-medium text-gray-600 mb-1">{label}</label>
                  <input value={(form as any)[field]} onChange={e => setForm(p => ({ ...p, [field]: e.target.value }))}
                    type={['quantity', 'min_quantity'].includes(field) ? 'number' : 'text'}
                    className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                </div>
              ))}
            </div>
            <div className="flex gap-3 mt-6">
              <button onClick={handleSave} className="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm">Create</button>
              <button onClick={() => setShowForm(false)} className="flex-1 border border-gray-200 rounded-lg py-2 text-sm">Cancel</button>
            </div>
          </div>
        </div>
      )}

      {/* Adjust Quantity Modal */}
      {adjustItem && (
        <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
            <h3 className="text-lg font-semibold mb-1">Adjust Quantity</h3>
            <p className="text-sm text-gray-500 mb-4">{adjustItem.product?.name} · Current: {adjustItem.quantity}</p>
            <label className="block text-xs font-medium text-gray-600 mb-1">Delta (positive to add, negative to deduct)</label>
            <input type="number" value={adjustDelta} onChange={e => setAdjustDelta(e.target.value)}
              className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4" />
            <div className="flex gap-3">
              <button onClick={handleAdjust} className="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm">Apply</button>
              <button onClick={() => setAdjustItem(null)} className="flex-1 border border-gray-200 rounded-lg py-2 text-sm">Cancel</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
