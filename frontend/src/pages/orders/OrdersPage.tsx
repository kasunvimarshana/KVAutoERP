import React, { useState, useEffect } from 'react';
import { ordersApi, productsApi } from '../../api/endpoints';
import type { Order, Product } from '../../types';

const STATUS_COLORS: Record<string, string> = {
  pending:   'bg-yellow-100 text-yellow-700',
  confirmed: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-600',
  failed:    'bg-gray-100 text-gray-500',
};

export default function OrdersPage() {
  const [orders, setOrders]     = useState<Order[]>([]);
  const [total, setTotal]       = useState(0);
  const [page, setPage]         = useState(1);
  const [perPage]               = useState(10);
  const [loading, setLoading]   = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [products, setProducts] = useState<Product[]>([]);
  const [items, setItems]       = useState([{ product_id: '', quantity: '1' }]);
  const [notes, setNotes]       = useState('');
  const [error, setError]       = useState('');
  const [detail, setDetail]     = useState<Order | null>(null);

  const load = async () => {
    setLoading(true);
    try {
      const res = await ordersApi.list({ per_page: perPage, page });
      setOrders(res.data.data);
      setTotal(res.data.meta.total);
    } catch (e: any) { setError(e.response?.data?.message ?? 'Load failed.'); }
    finally { setLoading(false); }
  };

  useEffect(() => { load(); }, [page]);
  useEffect(() => {
    productsApi.list({ per_page: 100 }).then(res => setProducts(res.data.data));
  }, []);

  const handlePlaceOrder = async () => {
    try {
      const payload = {
        items: items.map(i => ({ product_id: parseInt(i.product_id), quantity: parseInt(i.quantity) })),
        notes,
      };
      await ordersApi.create(payload);
      setShowForm(false); setItems([{ product_id: '', quantity: '1' }]); setNotes('');
      load();
    } catch (e: any) { setError(e.response?.data?.message ?? 'Order failed.'); }
  };

  const handleCancel = async (id: number) => {
    if (!confirm('Cancel this order?')) return;
    try { await ordersApi.cancel(id); load(); }
    catch (e: any) { setError(e.response?.data?.message ?? 'Cancel failed.'); }
  };

  const addItem = () => setItems(p => [...p, { product_id: '', quantity: '1' }]);
  const removeItem = (i: number) => setItems(p => p.filter((_, idx) => idx !== i));

  const lastPage = Math.ceil(total / perPage);

  return (
    <div className="p-8">
      <div className="flex items-center justify-between mb-6">
        <div><h2 className="text-2xl font-bold text-gray-800">Orders</h2><p className="text-sm text-gray-500">{total} total</p></div>
        <button onClick={() => setShowForm(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ Place Order</button>
      </div>

      {error && <div className="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{error}</div>}

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 border-b">
            <tr>{['Order #', 'Status', 'Total', 'Currency', 'Items', 'Actions'].map(h =>
              <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{h}</th>)}</tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {loading ? <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">Loading…</td></tr>
              : orders.map(o => (
                <tr key={o.id} className="hover:bg-gray-50">
                  <td className="px-4 py-3 font-mono text-xs font-medium text-gray-800">{o.order_number}</td>
                  <td className="px-4 py-3">
                    <span className={`text-xs px-2 py-0.5 rounded-full capitalize ${STATUS_COLORS[o.status] ?? ''}`}>{o.status}</span>
                  </td>
                  <td className="px-4 py-3 font-semibold">${parseFloat(o.total_amount).toFixed(2)}</td>
                  <td className="px-4 py-3 text-gray-500">{o.currency}</td>
                  <td className="px-4 py-3 text-gray-600">{o.items?.length ?? 0}</td>
                  <td className="px-4 py-3 flex gap-2">
                    <button onClick={() => setDetail(o)} className="text-xs bg-gray-100 hover:bg-gray-200 rounded px-2 py-1">View</button>
                    {['pending', 'confirmed'].includes(o.status) && (
                      <button onClick={() => handleCancel(o.id)} className="text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded px-2 py-1">Cancel</button>
                    )}
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

      {/* Place Order Modal */}
      {showForm && (
        <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[80vh] overflow-y-auto">
            <h3 className="text-lg font-semibold mb-4">Place New Order (Saga)</h3>
            <p className="text-xs text-indigo-600 bg-indigo-50 rounded p-2 mb-4">
              This order is processed via the Saga pattern: Validate → Reserve Inventory → Create → Confirm.
              Any failure triggers automatic compensating transactions.
            </p>

            <div className="space-y-3 mb-4">
              {items.map((item, idx) => (
                <div key={idx} className="flex gap-2 items-end">
                  <div className="flex-1">
                    <label className="block text-xs font-medium text-gray-600 mb-1">Product</label>
                    <select value={item.product_id} onChange={e => setItems(p => p.map((x, i) => i === idx ? { ...x, product_id: e.target.value } : x))}
                      className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                      <option value="">Select…</option>
                      {products.map(p => <option key={p.id} value={p.id}>{p.name} (${parseFloat(p.price).toFixed(2)})</option>)}
                    </select>
                  </div>
                  <div className="w-24">
                    <label className="block text-xs font-medium text-gray-600 mb-1">Qty</label>
                    <input type="number" min="1" value={item.quantity}
                      onChange={e => setItems(p => p.map((x, i) => i === idx ? { ...x, quantity: e.target.value } : x))}
                      className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                  </div>
                  {items.length > 1 && (
                    <button onClick={() => removeItem(idx)} className="text-xs text-red-500 hover:text-red-700 pb-2">✕</button>
                  )}
                </div>
              ))}
            </div>

            <button onClick={addItem} className="text-xs text-indigo-600 hover:underline mb-4">+ Add item</button>

            <div>
              <label className="block text-xs font-medium text-gray-600 mb-1">Notes</label>
              <textarea value={notes} onChange={e => setNotes(e.target.value)} rows={2}
                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
            </div>

            <div className="flex gap-3 mt-4">
              <button onClick={handlePlaceOrder} className="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm">Place Order</button>
              <button onClick={() => setShowForm(false)} className="flex-1 border border-gray-200 rounded-lg py-2 text-sm">Cancel</button>
            </div>
          </div>
        </div>
      )}

      {/* Order Detail Modal */}
      {detail && (
        <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[80vh] overflow-y-auto">
            <h3 className="text-lg font-semibold mb-1">Order {detail.order_number}</h3>
            <span className={`text-xs px-2 py-0.5 rounded-full capitalize ${STATUS_COLORS[detail.status]}`}>{detail.status}</span>
            <div className="mt-4 space-y-2">
              {detail.items?.map(item => (
                <div key={item.id} className="flex justify-between text-sm">
                  <span>{item.product?.name ?? `#${item.product_id}`} × {item.quantity}</span>
                  <span className="font-medium">${parseFloat(item.total_price).toFixed(2)}</span>
                </div>
              ))}
              <div className="border-t pt-2 flex justify-between font-bold">
                <span>Total</span>
                <span>${parseFloat(detail.total_amount).toFixed(2)}</span>
              </div>
            </div>
            {detail.notes && <p className="mt-3 text-xs text-gray-500">Note: {detail.notes}</p>}
            {detail.saga_id && <p className="mt-1 text-xs text-gray-400 font-mono">Saga: {detail.saga_id}</p>}
            <button onClick={() => setDetail(null)} className="mt-4 w-full border border-gray-200 rounded-lg py-2 text-sm">Close</button>
          </div>
        </div>
      )}
    </div>
  );
}
