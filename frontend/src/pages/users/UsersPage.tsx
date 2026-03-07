import React, { useState, useEffect } from 'react';
import { usersApi } from '../../api/endpoints';
import type { User } from '../../types';

const ROLES = ['admin', 'manager', 'user'];

export default function UsersPage() {
  const [users, setUsers]       = useState<User[]>([]);
  const [total, setTotal]       = useState(0);
  const [page, setPage]         = useState(1);
  const [perPage]               = useState(10);
  const [search, setSearch]     = useState('');
  const [loading, setLoading]   = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [editUser, setEditUser] = useState<User | null>(null);
  const [form, setForm]         = useState({ name: '', email: '', password: '', is_active: true, roles: ['user'] });
  const [error, setError]       = useState('');

  const loadUsers = async () => {
    setLoading(true);
    try {
      const res = await usersApi.list({ per_page: perPage, page, search: search || undefined });
      setUsers(res.data.data);
      setTotal(res.data.meta.total);
    } catch (e: any) {
      setError(e.response?.data?.message ?? 'Failed to load users.');
    } finally { setLoading(false); }
  };

  useEffect(() => { loadUsers(); }, [page, search]);

  const handleSave = async () => {
    try {
      if (editUser) {
        await usersApi.update(editUser.id, form);
      } else {
        await usersApi.create(form);
      }
      setShowForm(false); setEditUser(null);
      setForm({ name: '', email: '', password: '', is_active: true, roles: ['user'] });
      loadUsers();
    } catch (e: any) {
      setError(e.response?.data?.message ?? 'Save failed.');
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Delete this user?')) return;
    await usersApi.remove(id);
    loadUsers();
  };

  const openEdit = (u: User) => {
    setEditUser(u);
    setForm({ name: u.name, email: u.email, password: '', is_active: u.is_active, roles: u.roles.map(r => r.name) });
    setShowForm(true);
  };

  const lastPage = Math.ceil(total / perPage);

  return (
    <div className="p-8">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h2 className="text-2xl font-bold text-gray-800">Users</h2>
          <p className="text-sm text-gray-500">{total} total</p>
        </div>
        <button onClick={() => { setShowForm(true); setEditUser(null); }}
          className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
          + New User
        </button>
      </div>

      {error && <div className="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{error}</div>}

      {/* Search */}
      <input value={search} onChange={e => { setSearch(e.target.value); setPage(1); }}
        placeholder="Search by name or email…"
        className="mb-4 w-full max-w-sm border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />

      {/* Table */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 border-b border-gray-100">
            <tr>
              {['Name', 'Email', 'Role', 'Active', 'Actions'].map(h => (
                <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{h}</th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {loading ? (
              <tr><td colSpan={5} className="px-4 py-8 text-center text-gray-400">Loading…</td></tr>
            ) : users.map(u => (
              <tr key={u.id} className="hover:bg-gray-50">
                <td className="px-4 py-3 font-medium text-gray-800">{u.name}</td>
                <td className="px-4 py-3 text-gray-600">{u.email}</td>
                <td className="px-4 py-3">
                  <span className="inline-block bg-indigo-100 text-indigo-700 text-xs px-2 py-0.5 rounded-full capitalize">
                    {u.roles?.[0]?.name ?? '—'}
                  </span>
                </td>
                <td className="px-4 py-3">
                  <span className={`text-xs px-2 py-0.5 rounded-full ${u.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'}`}>
                    {u.is_active ? 'Active' : 'Inactive'}
                  </span>
                </td>
                <td className="px-4 py-3 flex gap-2">
                  <button onClick={() => openEdit(u)}
                    className="text-xs bg-gray-100 hover:bg-gray-200 rounded px-2 py-1">Edit</button>
                  <button onClick={() => handleDelete(u.id)}
                    className="text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded px-2 py-1">Delete</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {/* Pagination */}
        {lastPage > 1 && (
          <div className="flex items-center justify-between px-4 py-3 border-t border-gray-100 text-sm">
            <span className="text-gray-500">Page {page} of {lastPage}</span>
            <div className="flex gap-2">
              <button disabled={page === 1} onClick={() => setPage(p => p - 1)}
                className="px-3 py-1 border rounded disabled:opacity-40">Prev</button>
              <button disabled={page === lastPage} onClick={() => setPage(p => p + 1)}
                className="px-3 py-1 border rounded disabled:opacity-40">Next</button>
            </div>
          </div>
        )}
      </div>

      {/* Form Modal */}
      {showForm && (
        <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 className="text-lg font-semibold mb-4">{editUser ? 'Edit User' : 'New User'}</h3>
            <div className="space-y-3">
              {(['name', 'email'] as const).map(field => (
                <div key={field}>
                  <label className="block text-xs font-medium text-gray-600 mb-1 capitalize">{field}</label>
                  <input value={(form as any)[field]}
                    onChange={e => setForm(p => ({ ...p, [field]: e.target.value }))}
                    className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                </div>
              ))}
              <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">Password {editUser && '(leave blank to keep)'}</label>
                <input type="password" value={form.password}
                  onChange={e => setForm(p => ({ ...p, password: e.target.value }))}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
              </div>
              <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">Role</label>
                <select value={form.roles[0]} onChange={e => setForm(p => ({ ...p, roles: [e.target.value] }))}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                  {ROLES.map(r => <option key={r} value={r} className="capitalize">{r}</option>)}
                </select>
              </div>
              <label className="flex items-center gap-2 text-sm">
                <input type="checkbox" checked={form.is_active}
                  onChange={e => setForm(p => ({ ...p, is_active: e.target.checked }))} />
                Active
              </label>
            </div>
            <div className="flex gap-3 mt-6">
              <button onClick={handleSave}
                className="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm">
                {editUser ? 'Update' : 'Create'}
              </button>
              <button onClick={() => { setShowForm(false); setEditUser(null); }}
                className="flex-1 border border-gray-200 rounded-lg py-2 text-sm hover:bg-gray-50">
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
