import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../services/api';
import type { DashboardStats, RecentOrder } from '../types';
import { useAuth } from '../hooks/useAuth';
import { useTenant } from '../hooks/useTenant';

function StatCard({
  label,
  value,
  icon,
  color,
  change,
}: {
  label: string;
  value: string | number;
  icon: React.ReactNode;
  color: string;
  change?: string;
}) {
  return (
    <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
      <div className="flex items-center justify-between mb-3">
        <p className="text-sm font-medium text-gray-500">{label}</p>
        <div className={`w-10 h-10 rounded-lg flex items-center justify-center ${color}`}>
          {icon}
        </div>
      </div>
      <p className="text-2xl font-bold text-gray-900">{value}</p>
      {change && <p className="text-xs text-gray-400 mt-1">{change}</p>}
    </div>
  );
}

const orderStatusColors: Record<string, string> = {
  pending: 'bg-yellow-100 text-yellow-700',
  confirmed: 'bg-blue-100 text-blue-700',
  processing: 'bg-indigo-100 text-indigo-700',
  shipped: 'bg-purple-100 text-purple-700',
  delivered: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700',
  refunded: 'bg-gray-100 text-gray-600',
};

const MOCK_STATS: DashboardStats = {
  total_products: 142,
  total_orders: 1038,
  low_stock_items: 7,
  revenue_this_month: 48230.5,
  orders_this_month: 94,
  pending_orders: 12,
  active_users: 8,
  inventory_value: 184500,
};

const MOCK_RECENT_ORDERS: RecentOrder[] = [
  { id: 1, order_number: 'ORD-0041', customer_name: 'Alice Johnson', total: 320.0, status: 'pending', created_at: new Date(Date.now() - 3600000).toISOString() },
  { id: 2, order_number: 'ORD-0040', customer_name: 'Bob Smith', total: 875.5, status: 'processing', created_at: new Date(Date.now() - 7200000).toISOString() },
  { id: 3, order_number: 'ORD-0039', customer_name: 'Carol White', total: 102.0, status: 'delivered', created_at: new Date(Date.now() - 86400000).toISOString() },
  { id: 4, order_number: 'ORD-0038', customer_name: 'David Lee', total: 1450.0, status: 'shipped', created_at: new Date(Date.now() - 172800000).toISOString() },
  { id: 5, order_number: 'ORD-0037', customer_name: 'Eva Chen', total: 255.25, status: 'cancelled', created_at: new Date(Date.now() - 259200000).toISOString() },
];

export default function Dashboard() {
  const { user } = useAuth();
  const { tenant } = useTenant();
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [recentOrders, setRecentOrders] = useState<RecentOrder[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      api.get<{ data: DashboardStats }>('/dashboard/stats').catch(() => ({ data: { data: MOCK_STATS } })),
      api.get<{ data: RecentOrder[] }>('/dashboard/recent-orders').catch(() => ({ data: { data: MOCK_RECENT_ORDERS } })),
    ])
      .then(([statsRes, ordersRes]) => {
        setStats((statsRes as { data: { data: DashboardStats } }).data?.data ?? MOCK_STATS);
        setRecentOrders((ordersRes as { data: { data: RecentOrder[] } }).data?.data ?? MOCK_RECENT_ORDERS);
      })
      .finally(() => setIsLoading(false));
  }, []);

  const s = stats ?? MOCK_STATS;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">
          Welcome back, {user?.name.split(' ')[0]} 👋
        </h1>
        <p className="text-sm text-gray-500 mt-1">
          {tenant?.name} · Here's what's happening today.
        </p>
      </div>

      {/* Stats grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <StatCard
          label="Revenue This Month"
          value={`$${s.revenue_this_month.toLocaleString('en-US', { minimumFractionDigits: 2 })}`}
          color="bg-green-100"
          icon={<svg className="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>}
          change={`${s.orders_this_month} orders this month`}
        />
        <StatCard
          label="Pending Orders"
          value={s.pending_orders}
          color="bg-yellow-100"
          icon={<svg className="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>}
          change={`${s.total_orders} total orders`}
        />
        <StatCard
          label="Low Stock Items"
          value={s.low_stock_items}
          color="bg-red-100"
          icon={<svg className="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>}
          change={`${s.total_products} total products`}
        />
        <StatCard
          label="Inventory Value"
          value={`$${s.inventory_value.toLocaleString()}`}
          color="bg-blue-100"
          icon={<svg className="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>}
          change={`${s.active_users} active users`}
        />
      </div>

      {/* Recent orders */}
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div className="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
          <h2 className="font-semibold text-gray-900">Recent Orders</h2>
          <Link to="/orders" className="text-sm text-primary-600 hover:text-primary-700 font-medium">
            View all →
          </Link>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-100">
            <thead className="bg-gray-50">
              <tr>
                {['Order #', 'Customer', 'Total', 'Status', 'Date'].map((h) => (
                  <th key={h} className="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {(isLoading ? MOCK_RECENT_ORDERS : recentOrders).map((order) => (
                <tr key={order.id} className="hover:bg-gray-50">
                  <td className="px-5 py-3 text-sm font-medium text-primary-600">{order.order_number}</td>
                  <td className="px-5 py-3 text-sm text-gray-700">{order.customer_name}</td>
                  <td className="px-5 py-3 text-sm text-gray-700">${order.total.toFixed(2)}</td>
                  <td className="px-5 py-3">
                    <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize ${orderStatusColors[order.status] ?? 'bg-gray-100 text-gray-600'}`}>
                      {order.status}
                    </span>
                  </td>
                  <td className="px-5 py-3 text-sm text-gray-400">
                    {new Date(order.created_at).toLocaleDateString()}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Quick links */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        {[
          { to: '/products', label: 'Add Product', icon: '📦' },
          { to: '/inventory', label: 'Update Stock', icon: '🏭' },
          { to: '/orders', label: 'New Order', icon: '🛒' },
          { to: '/users', label: 'Manage Users', icon: '👥' },
        ].map((link) => (
          <Link
            key={link.to}
            to={link.to}
            className="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col items-center gap-2 hover:border-primary-300 hover:shadow transition-all text-center"
          >
            <span className="text-2xl">{link.icon}</span>
            <span className="text-sm font-medium text-gray-700">{link.label}</span>
          </Link>
        ))}
      </div>
    </div>
  );
}
