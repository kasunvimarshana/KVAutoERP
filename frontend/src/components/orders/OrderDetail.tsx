import React from 'react';
import { Order } from '../../types';
import Badge from '../common/Badge';

interface OrderDetailProps {
  order: Order;
}

const fmt = (n: number) => `$${n.toFixed(2)}`;
const fmtDate = (s: string) => new Date(s).toLocaleString();

const OrderDetail: React.FC<OrderDetailProps> = ({ order }) => (
  <div className="space-y-5">
    <div className="grid grid-cols-2 gap-4 text-sm">
      <div>
        <span className="text-gray-500">Order Number:</span>
        <span className="ml-2 font-mono font-semibold">#{order.orderNumber}</span>
      </div>
      <div>
        <span className="text-gray-500">Status:</span>
        <span className="ml-2">
          <Badge variant={order.status === 'completed' ? 'success' : order.status === 'failed' ? 'danger' : 'warning'}>
            {order.status}
          </Badge>
        </span>
      </div>
      <div>
        <span className="text-gray-500">Customer:</span>
        <span className="ml-2 font-medium">{order.customerName}</span>
      </div>
      <div>
        <span className="text-gray-500">Email:</span>
        <span className="ml-2">{order.customerEmail}</span>
      </div>
      <div>
        <span className="text-gray-500">Payment:</span>
        <span className="ml-2">
          <Badge variant={order.paymentStatus === 'paid' ? 'success' : 'warning'}>
            {order.paymentStatus}
          </Badge>
        </span>
      </div>
      <div>
        <span className="text-gray-500">Method:</span>
        <span className="ml-2">{order.paymentMethod || '\u2014'}</span>
      </div>
      <div>
        <span className="text-gray-500">Created:</span>
        <span className="ml-2">{fmtDate(order.createdAt)}</span>
      </div>
      <div>
        <span className="text-gray-500">Updated:</span>
        <span className="ml-2">{fmtDate(order.updatedAt)}</span>
      </div>
    </div>

    {order.sagaId && (
      <div className="bg-purple-50 rounded-lg p-3 text-sm">
        <p className="font-medium text-purple-900">Saga ID</p>
        <p className="text-purple-700 font-mono text-xs mt-1">{order.sagaId}</p>
      </div>
    )}

    <div>
      <h3 className="font-semibold text-gray-900 mb-2">Order Items</h3>
      <table className="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
        <thead className="bg-gray-50">
          <tr>
            <th className="table-header">SKU</th>
            <th className="table-header">Item</th>
            <th className="table-header">Qty</th>
            <th className="table-header">Unit Price</th>
            <th className="table-header">Total</th>
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-100">
          {order.items.map((item, idx) => (
            <tr key={idx}>
              <td className="table-cell font-mono text-xs">{item.sku}</td>
              <td className="table-cell">{item.name}</td>
              <td className="table-cell text-center">{item.quantity}</td>
              <td className="table-cell">{fmt(item.unitPrice)}</td>
              <td className="table-cell font-medium">{fmt(item.total)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>

    <div className="border-t pt-3 space-y-1 text-sm">
      <div className="flex justify-between text-gray-600">
        <span>Subtotal</span><span>{fmt(order.subtotal)}</span>
      </div>
      <div className="flex justify-between text-gray-600">
        <span>Tax</span><span>{fmt(order.tax)}</span>
      </div>
      {order.discount > 0 && (
        <div className="flex justify-between text-green-600">
          <span>Discount</span><span>-{fmt(order.discount)}</span>
        </div>
      )}
      <div className="flex justify-between font-semibold text-gray-900 text-base border-t pt-2">
        <span>Total</span><span>{fmt(order.total)}</span>
      </div>
    </div>

    {order.notes && (
      <div>
        <h3 className="font-semibold text-gray-900 mb-1">Notes</h3>
        <p className="text-sm text-gray-600 bg-gray-50 rounded p-3">{order.notes}</p>
      </div>
    )}
  </div>
);

export default OrderDetail;
