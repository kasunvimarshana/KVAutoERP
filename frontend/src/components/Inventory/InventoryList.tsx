import React from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { inventoryService } from '../../services/inventoryService';
import { useAuth } from '../../context/AuthContext';

export function InventoryList() {
  const { hasAnyRole }  = useAuth();
  const queryClient     = useQueryClient();
  const canWrite        = hasAnyRole(['admin', 'manager', 'warehouse-manager']);

  const { data, isLoading } = useQuery({
    queryKey: ['inventory'],
    queryFn:  () => inventoryService.list(),
  });

  if (isLoading) {
    return (
      <div className="flex justify-center p-8">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600" />
      </div>
    );
  }

  return (
    <div className="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
      <table className="min-w-full divide-y divide-gray-300">
        <thead className="bg-gray-50">
          <tr>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-200">
          {data?.data?.map((item: import('../../services/inventoryService').InventoryItem) => (
            <tr key={item.id} className={item.needs_reorder ? 'bg-yellow-50' : 'hover:bg-gray-50'}>
              <td className="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                {item.product_sku}
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {item.quantity}
                <span className="text-gray-400 text-xs ml-1">
                  ({item.reserved_quantity} reserved)
                </span>
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <span className={item.available_quantity <= item.reorder_level ? 'text-red-600' : 'text-green-600'}>
                  {item.available_quantity}
                </span>
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {item.warehouse_location || '—'}
              </td>
              <td className="px-6 py-4 whitespace-nowrap">
                {item.needs_reorder ? (
                  <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Reorder Needed
                  </span>
                ) : (
                  <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    In Stock
                  </span>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
