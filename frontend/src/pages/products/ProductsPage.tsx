import React, { useState, useEffect } from 'react';
import { productsApi } from '../../api/endpoints';
import type { Product } from '../../types';

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [total, setTotal]       = useState(0);
  const [page, setPage]         = useState(1);
  const [perPage]               = useState(10);
  const [search, setSearch]     = useState('');
  const [loading, setLoading]   = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [editItem, setEditItem] = useState<Product | null>(null);
  const [form, setForm]         = useState({ sku: '', name: '', description: '', price: '', category: '', is_active: true });
  const [error, setError]       = useState('');

  const load = async () => {
    setLoading(true);
    try {
      const res = await productsApi.list({ per_page: perPage, page, search: search || undefined });
      setProducts(res.data.data);
      setTotal(res.data.meta.total);
    } catch (e: any) { setError(e.response?.data?.message ?? 'Load failed.'); }
    finally { setLoading(false); }
  };

  useEffect(() => { load(); }, [page, search]);

  const handleSave = async () => {
    try {
      const payload = { ...form, price: parseFloat(form.price) };
      editItem ? await productsApi.update(editItem.id, payload) : await productsApi.create(payload);
      setShowForm(false); setEditItem(null);
      setForm({ sku: '', name: '', description: '', price: '', category: '', is_active: true });
      load();
    } catch (e: any) { setError(e.response?.data?.message ?? 'Save failed.'); }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Delete product?')) return;
    await productsApi.remove(id);
    load();
  };

  const openEdit = (p: Product) => {
    setEditItem(p);
    setForm({ sku: p.sku, name: p.name, description: p.description ?? '', price: p.price, category: p.category ?? '', is_active: p.is_active });
    setShowForm(true);
  };

  const lastPage = Math.ceil(total / perPage);

  return (
    <div className="p-8">
      <div className="flex items-center justify-between mb-6">
        <div><h2 className="text-2xl font-bold text-gray-800">Products</h2><p className="text-sm text-gray-500">{total} total</p></div>
        <button onClick={() => { setShowForm(true); setEditItem(null); }}
          className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ New Product</button>
      </div>

      {error && <div className="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{error}</div>}

      <input value={search} onChange={e => { setSearch(e.target.value); setPage(1); }}
        placeholder="Search by name, SKU, description…"
        className="mb-4 w-full max-w-sm border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 border-b">
            <tr>{['SKU', 'Name', 'Category', 'Price', 'Active', 'Actions'].map(h =>
              <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{h}</th>)}</tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {loading ? <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">Loading…</td></tr>
              : products.map(p => (
                <tr key={p.id} className="hover:bg-gray-50">
                  <td className="px-4 py-3 font-mono text-xs text-gray-600">{p.sku}</td>
                  <td className="px-4 py-3 font-medium text-gray-800">{p.name}</td>
                  <td className="px-4 py-3 text-gray-500">{p.category ?? '—'}</td>
                  <td className="px-4 py-3 text-gray-700">${parseFloat(p.price).toFixed(2)}</td>
                  <td className="px-4 py-3">
                    <span className={`text-xs px-2 py-0.5 rounded-full ${p.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'}`}>
                      {p.is_active ? 'Active' : 'Inactive'}
                    </span>
                  </td>
                  <td className="px-4 py-3 flex gap-2">
                    <button onClick={() => openEdit(p)} className="text-xs bg-gray-100 hover:bg-gray-200 rounded px-2 py-1">Edit</button>
                    <button onClick={() => handleDelete(p.id)} className="text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded px-2 py-1">Delete</button>
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

      {showForm && (
        <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 className="text-lg font-semibold mb-4">{editItem ? 'Edit Product' : 'New Product'}</h3>
            <div className="space-y-3">
              {([['sku', 'SKU'], ['name', 'Name'], ['description', 'Description'], ['price', 'Price'], ['category', 'Category']] as [string, string][]).map(([field, label]) => (
                <div key={field}>
                  <label className="block text-xs font-medium text-gray-600 mb-1">{label}</label>
                  <input value={(form as any)[field]}
                    onChange={e => setForm(p => ({ ...p, [field]: e.target.value }))}
                    type={field === 'price' ? 'number' : 'text'}
                    className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                </div>
              ))}
              <label className="flex items-center gap-2 text-sm">
                <input type="checkbox" checked={form.is_active} onChange={e => setForm(p => ({ ...p, is_active: e.target.checked }))} />
                Active
              </label>
            </div>
            <div className="flex gap-3 mt-6">
              <button onClick={handleSave} className="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm">{editItem ? 'Update' : 'Create'}</button>
              <button onClick={() => { setShowForm(false); setEditItem(null); }} className="flex-1 border border-gray-200 rounded-lg py-2 text-sm hover:bg-gray-50">Cancel</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
