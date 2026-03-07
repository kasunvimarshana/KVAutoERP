import React from 'react';
import { useAuth } from '../contexts/AuthContext';

export default function DashboardPage() {
  const { user, tenantKey } = useAuth();

  const cards = [
    { title: 'Users',     emoji: '👥', path: '/users',      desc: 'Manage users, roles & permissions' },
    { title: 'Products',  emoji: '📦', path: '/products',   desc: 'Catalog management with RBAC filters' },
    { title: 'Inventory', emoji: '🏭', path: '/inventory',  desc: 'Stock levels, reservations & adjustments' },
    { title: 'Orders',    emoji: '🛒', path: '/orders',     desc: 'Place orders via distributed Saga pattern' },
    { title: 'Config',    emoji: '⚙️', path: '/tenant-config', desc: 'Per-tenant runtime settings' },
  ];

  return (
    <div className="p-8">
      <div className="mb-8">
        <h2 className="text-2xl font-bold text-gray-800">
          Welcome, {user?.name}!
        </h2>
        <p className="text-sm text-gray-500 mt-1">
          Tenant: <span className="font-semibold text-indigo-600">{tenantKey}</span>
          {' · '}
          Role: <span className="font-semibold capitalize">{user?.roles?.[0]?.name ?? '—'}</span>
        </p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {cards.map((c) => (
          <a
            key={c.title}
            href={c.path}
            className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow"
          >
            <div className="text-4xl mb-3">{c.emoji}</div>
            <h3 className="text-lg font-semibold text-gray-800">{c.title}</h3>
            <p className="text-sm text-gray-500 mt-1">{c.desc}</p>
          </a>
        ))}
      </div>

      <div className="mt-10 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 className="text-lg font-semibold text-gray-800 mb-4">Architecture Overview</h3>
        <div className="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
          {[
            'Controller → Service → Repository',
            'Laravel Passport SSO',
            'RBAC + ABAC Authorization',
            'Multi-tenant TenantScope',
            'Saga Distributed Transactions',
            'MessageBroker (Null/RabbitMQ/Kafka)',
            'Conditional Pagination',
            'Tenant Runtime Config',
            'Cross-service Inventory Filters',
          ].map((f) => (
            <div key={f} className="flex items-center gap-2 bg-indigo-50 rounded-lg px-3 py-2">
              <span className="text-indigo-500">✓</span>
              <span className="text-gray-700">{f}</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
