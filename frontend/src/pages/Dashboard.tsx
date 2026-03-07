import React, { useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';
import { Package, AlertTriangle, ShoppingCart, DollarSign, TrendingUp, TrendingDown, CheckCircle, Clock } from 'lucide-react';
import { inventoryService } from '../services/api/inventoryService';
import { orderService } from '../services/api/orderService';
import { InventoryItem, Order, PaginatedResponse } from '../types';
import LoadingSpinner from '../components/common/LoadingSpinner';
import Badge from '../components/common/Badge';

const isPaginated = <T,>(data: PaginatedResponse<T> | T[]): data is PaginatedResponse<T> =>
  !Array.isArray(data) && 'data' in data;

const KPICard: React.FC<{ title: string; value: string | number; subtitle?: string; icon: React.ReactNode; color: string; trend?: number }> = ({ title, value, subtitle, icon, color, trend }) => (
  <div className="card flex items-start gap-4">
    <div className={`h-12 w-12 rounded-xl flex items-center justify-center flex-shrink-0 ${color}`}>{icon}</div>
    <div className="flex-1 min-w-0">
      <p className="text-sm text-gray-500">{title}</p>
      <p className="text-2xl font-bold text-gray-900 mt-0.5">{value}</p>
      {subtitle && <p className="text-xs text-gray-500 mt-0.5">{subtitle}</p>}
      {trend !== undefined && (
        <div className={`flex items-center gap-1 text-xs mt-1 ${trend >= 0 ? 'text-green-600' : 'text-red-600'}`}>
          {trend >= 0 ? <TrendingUp className="h-3 w-3" /> : <TrendingDown className="h-3 w-3" />}
          {Math.abs(trend).toFixed(1)}% vs last month
        </div>
      )}
    </div>
  </div>
);

const Dashboard: React.FC = () => {
  const { data: inventoryData, isLoading: invLoading } = useQuery({ queryKey: ['inventory', 'dashboard'], queryFn: () => inventoryService.list({ perPage: 100 }) });
  const { data: ordersData, isLoading: ordLoading } = useQuery({ queryKey: ['orders', 'dashboard'], queryFn: () => orderService.list({ perPage: 100 }) });
  const { data: lowStockData } = useQuery({ queryKey: ['inventory', 'low-stock'], queryFn: () => inventoryService.getLowStock() });

  const inventoryItems: InventoryItem[] = useMemo(() => { if (!inventoryData) return []; return isPaginated(inventoryData) ? inventoryData.data : inventoryData; }, [inventoryData]);
  const orders: Order[] = useMemo(() => { if (!ordersData) return []; return isPaginated(ordersData) ? ordersData.data : ordersData; }, [ordersData]);
  const lowStockItems = Array.isArray(lowStockData) ? lowStockData : [];

  const kpis = useMemo(() => {
    const totalValue = inventoryItems.reduce((sum, i) => sum + i.unitCost * i.quantity, 0);
    const pendingOrders = orders.filter((o) => ['pending', 'confirmed', 'processing'].includes(o.status));
    const completedOrders = orders.filter((o) => o.status === 'completed');
    const revenue = completedOrders.reduce((sum, o) => sum + o.total, 0);
    return { totalItems: inventoryItems.length, totalValue, lowStock: lowStockItems.length, totalOrders: orders.length, pendingOrders: pendingOrders.length, completedOrders: completedOrders.length, revenue };
  }, [inventoryItems, orders, lowStockItems]);

  const chartData = useMemo(() => {
    const byCategory: Record<string, { items: number; value: number }> = {};
    inventoryItems.forEach((item) => { const cat = item.category || 'Uncategorized'; if (!byCategory[cat]) byCategory[cat] = { items: 0, value: 0 }; byCategory[cat].items += 1; byCategory[cat].value += item.quantity * item.unitCost; });
    return Object.entries(byCategory).map(([name, v]) => ({ name, items: v.items, value: Math.round(v.value) })).sort((a, b) => b.items - a.items).slice(0, 8);
  }, [inventoryItems]);

  const recentOrders = useMemo(() => [...orders].sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()).slice(0, 5), [orders]);

  if (invLoading || ordLoading) return <div className="flex items-center justify-center h-full"><LoadingSpinner size="lg" /></div>;

  return (
    <div className="p-6 space-y-6">
      <div><h1 className="text-2xl font-bold text-gray-900">Dashboard</h1><p className="text-gray-500 text-sm mt-1">Overview of your inventory and orders</p></div>
      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <KPICard title="Total Inventory Items" value={kpis.totalItems.toLocaleString()} subtitle={`Value: $${kpis.totalValue.toLocaleString(undefined, { maximumFractionDigits: 0 })}`} icon={<Package className="h-6 w-6 text-white" />} color="bg-blue-500" />
        <KPICard title="Low Stock Alerts" value={kpis.lowStock} subtitle={kpis.lowStock > 0 ? 'Items need restocking' : 'All items well-stocked'} icon={<AlertTriangle className="h-6 w-6 text-white" />} color={kpis.lowStock > 0 ? 'bg-red-500' : 'bg-green-500'} />
        <KPICard title="Total Orders" value={kpis.totalOrders.toLocaleString()} subtitle={`${kpis.pendingOrders} pending`} icon={<ShoppingCart className="h-6 w-6 text-white" />} color="bg-purple-500" />
        <KPICard title="Revenue" value={`$${kpis.revenue.toLocaleString(undefined, { maximumFractionDigits: 0 })}`} subtitle={`${kpis.completedOrders} completed orders`} icon={<DollarSign className="h-6 w-6 text-white" />} color="bg-emerald-500" trend={12.5} />
      </div>
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 card">
          <h2 className="text-base font-semibold text-gray-900 mb-4">Inventory by Category</h2>
          {chartData.length === 0 ? <div className="flex items-center justify-center h-48 text-gray-400">No data available</div> : (
            <ResponsiveContainer width="100%" height={260}>
              <BarChart data={chartData} margin={{ top: 5, right: 10, left: 0, bottom: 5 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                <XAxis dataKey="name" tick={{ fontSize: 11 }} />
                <YAxis tick={{ fontSize: 11 }} />
                <Tooltip contentStyle={{ fontSize: '12px', borderRadius: '8px', border: '1px solid #e5e7eb' }} />
                <Legend iconSize={10} wrapperStyle={{ fontSize: '12px' }} />
                <Bar dataKey="items" name="Items" fill="#3b82f6" radius={[3, 3, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          )}
        </div>
        <div className="card">
          <h2 className="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2"><AlertTriangle className="h-4 w-4 text-red-500" />Low Stock Items</h2>
          {lowStockItems.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-32 text-center text-gray-400"><CheckCircle className="h-8 w-8 text-green-400 mb-2" /><p className="text-sm">All items are well-stocked</p></div>
          ) : (
            <div className="space-y-3 max-h-64 overflow-y-auto">
              {lowStockItems.slice(0, 8).map((item) => (
                <div key={item.id} className="flex items-center justify-between text-sm">
                  <div className="min-w-0"><p className="font-medium text-gray-900 truncate">{item.name}</p><p className="text-xs text-gray-500 font-mono">{item.sku}</p></div>
                  <div className="text-right flex-shrink-0 ml-2"><p className="font-semibold text-red-600">{item.quantity}</p><p className="text-xs text-gray-400">min: {item.minStockLevel}</p></div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
      <div className="card">
        <h2 className="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2"><Clock className="h-4 w-4 text-gray-500" />Recent Orders</h2>
        {recentOrders.length === 0 ? <p className="text-sm text-gray-500 text-center py-8">No orders yet</p> : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead><tr className="border-b border-gray-100"><th className="table-header">Order #</th><th className="table-header">Customer</th><th className="table-header">Total</th><th className="table-header">Status</th><th className="table-header">Date</th></tr></thead>
              <tbody>
                {recentOrders.map((order) => (
                  <tr key={order.id} className="border-b border-gray-50 hover:bg-gray-50">
                    <td className="table-cell font-mono text-xs font-semibold">#{order.orderNumber}</td>
                    <td className="table-cell">{order.customerName}</td>
                    <td className="table-cell font-semibold">${order.total.toFixed(2)}</td>
                    <td className="table-cell"><Badge variant={order.status === 'completed' ? 'success' : order.status === 'failed' || order.status === 'cancelled' ? 'danger' : order.status === 'processing' ? 'purple' : 'warning'}>{order.status}</Badge></td>
                    <td className="table-cell text-gray-500">{new Date(order.createdAt).toLocaleDateString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
};

export default Dashboard;
