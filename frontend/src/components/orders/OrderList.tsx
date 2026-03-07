import React from 'react';
import { Eye, XCircle } from 'lucide-react';
import { Order } from '../../types';
import Badge from '../common/Badge';
import LoadingSpinner from '../common/LoadingSpinner';

interface OrderListProps {
  orders: Order[];
  isLoading: boolean;
  onCancel: (order: Order) => void;
  onViewDetails: (order: Order) => void;
}

type BadgeVariant = 'success' | 'warning' | 'danger' | 'info' | 'default' | 'purple';

const orderStatusVariant = (status: Order['status']): BadgeVariant => {
  const map: Record<Order['status'], BadgeVariant> = {
    pending: 'warning',
    confirmed: 'info',
    processing: 'purple',
    completed: 'success',
    cancelled: 'default',
    failed: 'danger',
  };
  return map[status];
};

const paymentStatusVariant = (status: Order['paymentStatus']): BadgeVariant => {
  const map: Record<Order['paymentStatus'], BadgeVariant> = {
    pending: 'warning',
    paid: 'success',
    failed: 'danger',
    refunded: 'default',
  };
  return map[status];
};

const formatDate = (iso: string) =>
  new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

const OrderList: React.FC<OrderListProps> = ({ orders, isLoading, onCancel, onViewDetails }) => {
  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-16">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  if (!orders.length) {
    return (
      <div className="text-center py-16 text-gray-500">
        <p className="text-lg font-medium">No orders found</p>
        <p className="text-sm mt-1">Try adjusting your filters.</p>
      </div>
    );
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead className="bg-gray-50 border-b border-gray-200">
          <tr>
            <th className="table-header">Order #</th>
            <th className="table-header">Customer</th>
            <th className="table-header">Items</th>
            <th className="table-header">Total</th>
            <th className="table-header">Status</th>
            <th className="table-header">Payment</th>
            <th className="table-header">Created</th>
            <th className="table-header">Actions</th>
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-100">
          {orders.map((order) => (
            <tr key={order.id} className="hover:bg-gray-50 transition-colors">
              <td className="table-cell font-mono text-xs font-semibold text-blue-700">
                #{order.orderNumber}
              </td>
              <td className="table-cell">
                <div className="font-medium text-gray-900">{order.customerName}</div>
                <div className="text-xs text-gray-500">{order.customerEmail}</div>
              </td>
              <td className="table-cell text-center">{order.items.length}</td>
              <td className="table-cell font-semibold">${order.total.toFixed(2)}</td>
              <td className="table-cell">
                <Badge variant={orderStatusVariant(order.status)}>{order.status}</Badge>
              </td>
              <td className="table-cell">
                <Badge variant={paymentStatusVariant(order.paymentStatus)}>
                  {order.paymentStatus}
                </Badge>
              </td>
              <td className="table-cell text-gray-500">{formatDate(order.createdAt)}</td>
              <td className="table-cell">
                <div className="flex items-center gap-1">
                  <button
                    onClick={() => onViewDetails(order)}
                    title="View Details"
                    className="p-1.5 rounded hover:bg-blue-50 text-blue-600 transition-colors"
                  >
                    <Eye className="h-4 w-4" />
                  </button>
                  {['pending', 'confirmed'].includes(order.status) && (
                    <button
                      onClick={() => onCancel(order)}
                      title="Cancel Order"
                      className="p-1.5 rounded hover:bg-red-50 text-red-600 transition-colors"
                    >
                      <XCircle className="h-4 w-4" />
                    </button>
                  )}
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default OrderList;
