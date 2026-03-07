import React from 'react';
import { Link, useLocation, Outlet } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';

const navItems = [
  { to: '/dashboard',  label: '🏠 Dashboard' },
  { to: '/users',      label: '👥 Users' },
  { to: '/products',   label: '📦 Products' },
  { to: '/inventory',  label: '🏭 Inventory' },
  { to: '/orders',     label: '🛒 Orders' },
  { to: '/tenant-config', label: '⚙️ Config' },
];

export default function Layout() {
  const { user, logout, tenantKey } = useAuth();
  const location = useLocation();

  return (
    <div className="flex min-h-screen bg-gray-100">
      {/* Sidebar */}
      <aside className="w-64 bg-indigo-800 text-white flex flex-col">
        <div className="p-6 border-b border-indigo-700">
          <h1 className="text-xl font-bold">SaaS Inventory</h1>
          <p className="text-xs text-indigo-300 mt-1">Tenant: <span className="font-semibold">{tenantKey}</span></p>
        </div>

        <nav className="flex-1 p-4 space-y-1">
          {navItems.map((item) => (
            <Link
              key={item.to}
              to={item.to}
              className={`flex items-center px-4 py-2 rounded-lg text-sm transition-colors ${
                location.pathname.startsWith(item.to)
                  ? 'bg-indigo-600 text-white'
                  : 'text-indigo-200 hover:bg-indigo-700'
              }`}
            >
              {item.label}
            </Link>
          ))}
        </nav>

        <div className="p-4 border-t border-indigo-700">
          <p className="text-xs text-indigo-300 truncate">{user?.email}</p>
          <p className="text-xs text-indigo-400 capitalize">{user?.roles?.[0]?.name ?? 'user'}</p>
          <button
            onClick={logout}
            className="mt-2 w-full text-xs bg-indigo-600 hover:bg-indigo-500 rounded px-3 py-1.5 transition-colors"
          >
            Logout
          </button>
        </div>
      </aside>

      {/* Main */}
      <main className="flex-1 overflow-auto">
        <Outlet />
      </main>
    </div>
  );
}
