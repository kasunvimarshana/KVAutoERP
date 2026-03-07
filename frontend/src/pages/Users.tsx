import React, { useEffect, useState, useCallback } from 'react';
import DataTable from '../components/DataTable';
import { userService } from '../services/userService';
import type { User, CreateUserPayload, UpdateUserPayload, Column, PaginationMeta } from '../types';
import { useAuth } from '../hooks/useAuth';

const ROLE_COLORS: Record<string, string> = {
  admin: 'bg-purple-100 text-purple-700',
  manager: 'bg-blue-100 text-blue-700',
  staff: 'bg-green-100 text-green-700',
  viewer: 'bg-gray-100 text-gray-600',
};

const STATUS_COLORS: Record<string, string> = {
  active: 'bg-green-100 text-green-700',
  inactive: 'bg-red-100 text-red-700',
};

const EMPTY_FORM: CreateUserPayload = {
  name: '',
  email: '',
  role: 'staff',
  password: '',
  status: 'active',
};

interface ModalProps {
  onClose: () => void;
  onSubmit: (data: CreateUserPayload | UpdateUserPayload) => void;
  initial?: User | null;
  isSubmitting: boolean;
}

function UserModal({ onClose, onSubmit, initial, isSubmitting }: ModalProps) {
  const [form, setForm] = useState<CreateUserPayload>(
    initial
      ? { name: initial.name, email: initial.email, role: initial.role, password: '', status: initial.status }
      : { ...EMPTY_FORM }
  );

  const set = (field: keyof typeof form) => (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) =>
    setForm((prev) => ({ ...prev, [field]: e.target.value }));

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const payload = { ...form };
    if (initial && !payload.password) delete (payload as Partial<typeof payload>).password;
    onSubmit(payload);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
      <div className="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h2 className="text-lg font-semibold text-gray-900">{initial ? 'Edit User' : 'New User'}</h2>
          <button onClick={onClose} className="p-1 rounded-lg hover:bg-gray-100 transition-colors">
            <svg className="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input value={form.name} onChange={set('name')} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" value={form.email} onChange={set('email')} required className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
          </div>
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Role</label>
              <select value={form.role} onChange={set('role')} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                {(['admin', 'manager', 'staff', 'viewer'] as const).map((r) => (
                  <option key={r} value={r}>{r}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select value={form.status} onChange={set('status')} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              {initial ? 'New Password (leave blank to keep)' : 'Password'}
            </label>
            <input type="password" value={form.password} onChange={set('password')} required={!initial} minLength={8} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none" />
          </div>
          <div className="flex gap-3 pt-2">
            <button type="button" onClick={onClose} className="flex-1 px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
              Cancel
            </button>
            <button type="submit" disabled={isSubmitting} className="flex-1 px-4 py-2 text-sm font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-60 transition-colors">
              {isSubmitting ? 'Saving…' : 'Save'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function Users() {
  const { hasRole } = useAuth();
  const isAdmin = hasRole('admin');

  const [users, setUsers] = useState<User[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | undefined>();
  const [isLoading, setIsLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [roleFilter, setRoleFilter] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [sortKey, setSortKey] = useState('created_at');
  const [sortDir, setSortDir] = useState<'asc' | 'desc'>('desc');
  const [modalOpen, setModalOpen] = useState(false);
  const [editTarget, setEditTarget] = useState<User | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<User | null>(null);
  const [toast, setToast] = useState<{ type: 'success' | 'error'; msg: string } | null>(null);

  const showToast = (type: 'success' | 'error', msg: string) => {
    setToast({ type, msg });
    setTimeout(() => setToast(null), 3000);
  };

  const load = useCallback(async () => {
    setIsLoading(true);
    try {
      const res = await userService.list({
        page,
        per_page: 10,
        search,
        role: roleFilter || undefined,
        status: statusFilter || undefined,
        sort_by: sortKey,
        sort_dir: sortDir,
      });
      setUsers(res.data.data);
      setMeta(res.data.meta);
    } catch {
      showToast('error', 'Failed to load users');
    } finally {
      setIsLoading(false);
    }
  }, [page, search, roleFilter, statusFilter, sortKey, sortDir]);

  useEffect(() => { load(); }, [load]);

  const handleSubmit = async (payload: CreateUserPayload | UpdateUserPayload) => {
    setIsSubmitting(true);
    try {
      if (editTarget) {
        await userService.update(editTarget.id, payload as UpdateUserPayload);
        showToast('success', 'User updated');
      } else {
        await userService.create(payload as CreateUserPayload);
        showToast('success', 'User created');
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
      await userService.delete(deleteTarget.id);
      showToast('success', 'User deleted');
      setDeleteTarget(null);
      load();
    } catch {
      showToast('error', 'Delete failed');
    }
  };

  const columns: Column<Record<string, unknown>>[] = [
    {
      key: 'name',
      label: 'Name',
      sortable: true,
      render: (_, row) => {
        const u = row as unknown as User;
        return (
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-semibold flex-shrink-0">
              {u.name.charAt(0).toUpperCase()}
            </div>
            <div>
              <p className="font-medium text-gray-900 text-sm">{u.name}</p>
              <p className="text-xs text-gray-400">{u.email}</p>
            </div>
          </div>
        );
      },
    },
    {
      key: 'role',
      label: 'Role',
      render: (val) => (
        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize ${ROLE_COLORS[String(val)] ?? 'bg-gray-100 text-gray-600'}`}>
          {String(val)}
        </span>
      ),
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
      key: 'last_login',
      label: 'Last Login',
      render: (val) => val ? new Date(String(val)).toLocaleDateString() : <span className="text-gray-400">Never</span>,
    },
    {
      key: 'created_at',
      label: 'Created',
      sortable: true,
      render: (val) => new Date(String(val)).toLocaleDateString(),
    },
  ];

  return (
    <div className="space-y-5">
      {/* Toast */}
      {toast && (
        <div className={`fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white transition-all ${toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'}`}>
          {toast.msg}
        </div>
      )}

      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Users</h1>
          <p className="text-sm text-gray-500 mt-0.5">Manage team members and their roles</p>
        </div>
        {isAdmin && (
          <button
            onClick={() => { setEditTarget(null); setModalOpen(true); }}
            className="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
          >
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Add User
          </button>
        )}
      </div>

      <DataTable
        columns={columns}
        data={users as unknown as Record<string, unknown>[]}
        meta={meta}
        isLoading={isLoading}
        searchPlaceholder="Search users…"
        filterOptions={[
          { key: 'role', label: 'Role', options: ['admin', 'manager', 'staff', 'viewer'].map((r) => ({ label: r, value: r })) },
          { key: 'status', label: 'Status', options: [{ label: 'Active', value: 'active' }, { label: 'Inactive', value: 'inactive' }] },
        ]}
        onPageChange={setPage}
        onSearchChange={(v) => { setSearch(v); setPage(1); }}
        onFilterChange={(k, v) => {
          if (k === 'role') { setRoleFilter(v); setPage(1); }
          if (k === 'status') { setStatusFilter(v); setPage(1); }
        }}
        onSortChange={(k, d) => { setSortKey(k); setSortDir(d); }}
        sortKey={sortKey}
        sortDir={sortDir}
        emptyMessage="No users found."
        actions={
          isAdmin
            ? (row) => {
                const u = row as unknown as User;
                return (
                  <div className="flex items-center justify-end gap-2">
                    <button
                      onClick={() => { setEditTarget(u); setModalOpen(true); }}
                      className="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition-colors"
                      title="Edit"
                    >
                      <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      onClick={() => setDeleteTarget(u)}
                      className="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                      title="Delete"
                    >
                      <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                );
              }
            : undefined
        }
      />

      {/* Modal */}
      {modalOpen && (
        <UserModal
          onClose={() => { setModalOpen(false); setEditTarget(null); }}
          onSubmit={handleSubmit}
          initial={editTarget}
          isSubmitting={isSubmitting}
        />
      )}

      {/* Delete confirm */}
      {deleteTarget && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-2">Delete User?</h3>
            <p className="text-sm text-gray-500 mb-5">
              Are you sure you want to delete <strong>{deleteTarget.name}</strong>? This action cannot be undone.
            </p>
            <div className="flex gap-3">
              <button onClick={() => setDeleteTarget(null)} className="flex-1 px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
              </button>
              <button onClick={handleDelete} className="flex-1 px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Delete
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
