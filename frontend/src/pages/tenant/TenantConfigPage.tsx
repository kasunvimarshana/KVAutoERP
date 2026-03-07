import React, { useState, useEffect } from 'react';
import { tenantConfigApi } from '../../api/endpoints';
import type { TenantConfig } from '../../types';

const EXAMPLES = [
  { key: 'mail.driver',     value: 'smtp',         type: 'string' },
  { key: 'mail.host',       value: 'smtp.example.com', type: 'string' },
  { key: 'payment.gateway', value: 'stripe',       type: 'string' },
  { key: 'notification.sms', value: 'true',        type: 'boolean' },
];

export default function TenantConfigPage() {
  const [configs, setConfigs]   = useState<TenantConfig[]>([]);
  const [loading, setLoading]   = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm]         = useState({ key: '', value: '', type: 'string', is_encrypted: false });
  const [error, setError]       = useState('');

  const load = async () => {
    setLoading(true);
    try {
      const res = await tenantConfigApi.list();
      setConfigs(res.data);
    } catch (e: any) { setError(e.response?.data?.message ?? 'Load failed.'); }
    finally { setLoading(false); }
  };

  useEffect(() => { load(); }, []);

  const handleSave = async () => {
    try {
      await tenantConfigApi.upsert(form);
      setShowForm(false);
      setForm({ key: '', value: '', type: 'string', is_encrypted: false });
      load();
    } catch (e: any) { setError(e.response?.data?.message ?? 'Save failed.'); }
  };

  const handleDelete = async (key: string) => {
    if (!confirm(`Delete config key "${key}"?`)) return;
    await tenantConfigApi.remove(key); load();
  };

  return (
    <div className="p-8">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h2 className="text-2xl font-bold text-gray-800">Tenant Configuration</h2>
          <p className="text-sm text-gray-500">Per-tenant runtime settings (mail, payment, notifications, etc.)</p>
        </div>
        <button onClick={() => setShowForm(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ Add Config</button>
      </div>

      {error && <div className="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{error}</div>}

      <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        {EXAMPLES.map(ex => (
          <button key={ex.key} onClick={() => { setForm({ key: ex.key, value: ex.value, type: ex.type, is_encrypted: false }); setShowForm(true); }}
            className="text-left p-3 bg-indigo-50 border border-indigo-100 rounded-lg hover:bg-indigo-100 transition-colors">
            <p className="text-xs font-mono text-indigo-700">{ex.key}</p>
            <p className="text-xs text-gray-500 mt-0.5">{ex.value}</p>
          </button>
        ))}
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 border-b">
            <tr>{['Key', 'Type', 'Encrypted', 'Updated', 'Actions'].map(h =>
              <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{h}</th>)}</tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {loading ? <tr><td colSpan={5} className="px-4 py-8 text-center text-gray-400">Loading…</td></tr>
              : configs.length === 0
                ? <tr><td colSpan={5} className="px-4 py-8 text-center text-gray-400">No configuration keys yet.</td></tr>
                : configs.map(c => (
                  <tr key={c.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3 font-mono text-xs font-medium text-gray-800">{c.key}</td>
                    <td className="px-4 py-3"><span className="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">{c.type}</span></td>
                    <td className="px-4 py-3">{c.is_encrypted ? '🔒 Yes' : '—'}</td>
                    <td className="px-4 py-3 text-gray-500 text-xs">{new Date(c.updated_at ?? '').toLocaleDateString()}</td>
                    <td className="px-4 py-3 flex gap-2">
                      <button onClick={() => { setForm({ key: c.key, value: '', type: c.type, is_encrypted: c.is_encrypted }); setShowForm(true); }}
                        className="text-xs bg-gray-100 hover:bg-gray-200 rounded px-2 py-1">Edit</button>
                      <button onClick={() => handleDelete(c.key)}
                        className="text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded px-2 py-1">Delete</button>
                    </td>
                  </tr>
                ))}
          </tbody>
        </table>
      </div>

      {showForm && (
        <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 className="text-lg font-semibold mb-4">Set Configuration</h3>
            <div className="space-y-3">
              {([['key', 'Key (e.g. mail.host)'], ['value', 'Value']] as [string, string][]).map(([f, l]) => (
                <div key={f}>
                  <label className="block text-xs font-medium text-gray-600 mb-1">{l}</label>
                  <input value={(form as any)[f]} onChange={e => setForm(p => ({ ...p, [f]: e.target.value }))}
                    className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                </div>
              ))}
              <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select value={form.type} onChange={e => setForm(p => ({ ...p, type: e.target.value }))}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                  {['string', 'boolean', 'integer', 'json'].map(t => <option key={t}>{t}</option>)}
                </select>
              </div>
              <label className="flex items-center gap-2 text-sm">
                <input type="checkbox" checked={form.is_encrypted}
                  onChange={e => setForm(p => ({ ...p, is_encrypted: e.target.checked }))} />
                Encrypt value at rest
              </label>
            </div>
            <div className="flex gap-3 mt-6">
              <button onClick={handleSave} className="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm">Save</button>
              <button onClick={() => setShowForm(false)} className="flex-1 border border-gray-200 rounded-lg py-2 text-sm">Cancel</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
