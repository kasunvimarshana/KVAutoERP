import React from 'react';
import { Package, Box, ShoppingCart, TrendingUp } from 'lucide-react';

const Dashboard: React.FC = () => {
  const stats = [
    { label: 'Total Products', value: '1,234', icon: <Package size={24} />, color: 'bg-blue-500' },
    { label: 'Stock Items', value: '45,678', icon: <Box size={24} />, color: 'bg-green-500' },
    { label: 'Pending Orders', value: '89', icon: <ShoppingCart size={24} />, color: 'bg-yellow-500' },
    { label: 'Total Revenue', value: '$123,456', icon: <TrendingUp size={24} />, color: 'bg-purple-500' },
  ];

  return (
    <div className="space-y-8 text-black">
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((stat, index) => (
          <div key={index} className="bg-white p-6 rounded-lg shadow-sm flex items-center space-x-4">
            <div className={`${stat.color} p-3 rounded-lg text-white`}>
              {stat.icon}
            </div>
            <div>
              <p className="text-sm text-gray-500 font-medium">{stat.label}</p>
              <p className="text-2xl font-bold text-gray-800">{stat.value}</p>
            </div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div className="bg-white p-6 rounded-lg shadow-sm">
          <h3 className="text-lg font-semibold mb-4">Recent Orders</h3>
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead>
                <tr className="border-b">
                  <th className="pb-3 font-semibold">Order ID</th>
                  <th className="pb-3 font-semibold">Customer</th>
                  <th className="pb-3 font-semibold">Status</th>
                  <th className="pb-3 font-semibold">Amount</th>
                </tr>
              </thead>
              <tbody className="divide-y">
                {[1, 2, 3, 4, 5].map((i) => (
                  <tr key={i} className="hover:bg-gray-50 transition-colors">
                    <td className="py-3 text-sm">#ORD-00{i}</td>
                    <td className="py-3 text-sm">Customer {i}</td>
                    <td className="py-3">
                      <span className="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                        Completed
                      </span>
                    </td>
                    <td className="py-3 text-sm">${(i * 123.45).toFixed(2)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm">
          <h3 className="text-lg font-semibold mb-4">Low Stock Alerts</h3>
          <div className="space-y-4">
            {[1, 2, 3].map((i) => (
              <div key={i} className="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                <div className="flex items-center space-x-3">
                  <div className="w-10 h-10 bg-white rounded flex items-center justify-center border">
                    <Package size={20} className="text-gray-400" />
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-800">Product {i}</p>
                    <p className="text-xs text-gray-500">SKU: PROD-00{i}</p>
                  </div>
                </div>
                <div className="text-right">
                  <p className="text-sm font-bold text-red-600">{i * 2} left</p>
                  <p className="text-xs text-gray-500">Min: 10</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
