import React from 'react';
import { Edit2, Trash2, TrendingUp, ArrowUp, ArrowDown } from 'lucide-react';
import { InventoryItem } from '../../types';
import Badge from '../common/Badge';
import LoadingSpinner from '../common/LoadingSpinner';

interface InventoryListProps {
  items: InventoryItem[];
  isLoading: boolean;
  onEdit: (item: InventoryItem) => void;
  onDelete: (item: InventoryItem) => void;
  onAdjustStock: (item: InventoryItem) => void;
  sortBy?: string;
  sortDirection?: 'asc' | 'desc';
  onSort: (column: string) => void;
  selectedIds: string[];
  onSelectId: (id: string) => void;
  onSelectAll: (checked: boolean) => void;
}

const statusVariant = (status: InventoryItem['status']) => {
  const map: Record<InventoryItem['status'], 'success' | 'warning' | 'danger'> = {
    active: 'success',
    inactive: 'warning',
    discontinued: 'danger',
  };
  return map[status];
};

const SortIcon: React.FC<{ column: string; sortBy?: string; sortDirection?: 'asc' | 'desc' }> = ({
  column,
  sortBy,
  sortDirection,
}) => {
  if (sortBy !== column) return <span className="ml-1 text-gray-300">\u2195</span>;
  return sortDirection === 'asc' ? (
    <ArrowUp className="inline ml-1 h-3 w-3 text-blue-600" />
  ) : (
    <ArrowDown className="inline ml-1 h-3 w-3 text-blue-600" />
  );
};

const columns = [
  { key: 'sku', label: 'SKU' },
  { key: 'name', label: 'Name' },
  { key: 'category', label: 'Category' },
  { key: 'quantity', label: 'Qty' },
  { key: 'availableQuantity', label: 'Available' },
  { key: 'unitPrice', label: 'Unit Price' },
  { key: 'status', label: 'Status' },
];

const InventoryList: React.FC<InventoryListProps> = ({
  items,
  isLoading,
  onEdit,
  onDelete,
  onAdjustStock,
  sortBy,
  sortDirection,
  onSort,
  selectedIds,
  onSelectId,
  onSelectAll,
}) => {
  const allSelected = items.length > 0 && items.every((i) => selectedIds.includes(i.id));

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-16">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  if (!items.length) {
    return (
      <div className="text-center py-16 text-gray-500">
        <p className="text-lg font-medium">No inventory items found</p>
        <p className="text-sm mt-1">Try adjusting your search or filters.</p>
      </div>
    );
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead className="bg-gray-50 border-b border-gray-200">
          <tr>
            <th className="table-header w-10">
              <input
                type="checkbox"
                checked={allSelected}
                onChange={(e) => onSelectAll(e.target.checked)}
                className="rounded border-gray-300"
              />
            </th>
            {columns.map((col) => (
              <th
                key={col.key}
                className="table-header cursor-pointer select-none hover:bg-gray-100"
                onClick={() => onSort(col.key)}
              >
                {col.label}
                <SortIcon column={col.key} sortBy={sortBy} sortDirection={sortDirection} />
              </th>
            ))}
            <th className="table-header">Actions</th>
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-100">
          {items.map((item) => (
            <tr key={item.id} className="hover:bg-gray-50 transition-colors">
              <td className="table-cell">
                <input
                  type="checkbox"
                  checked={selectedIds.includes(item.id)}
                  onChange={() => onSelectId(item.id)}
                  className="rounded border-gray-300"
                />
              </td>
              <td className="table-cell font-mono text-xs">{item.sku}</td>
              <td className="table-cell">
                <div className="font-medium text-gray-900">{item.name}</div>
                {item.description && (
                  <div className="text-xs text-gray-500 truncate max-w-xs">{item.description}</div>
                )}
              </td>
              <td className="table-cell text-gray-500">{item.category || '\u2014'}</td>
              <td className="table-cell">
                <div className="flex items-center gap-1.5">
                  <span className={item.isLowStock ? 'text-red-600 font-semibold' : ''}>
                    {item.quantity}
                  </span>
                  {item.isLowStock && (
                    <Badge variant="danger">Low Stock</Badge>
                  )}
                </div>
              </td>
              <td className="table-cell">{item.availableQuantity}</td>
              <td className="table-cell">${item.unitPrice.toFixed(2)}</td>
              <td className="table-cell">
                <Badge variant={statusVariant(item.status)}>{item.status}</Badge>
              </td>
              <td className="table-cell">
                <div className="flex items-center gap-1">
                  <button
                    onClick={() => onAdjustStock(item)}
                    title="Adjust Stock"
                    className="p-1.5 rounded hover:bg-blue-50 text-blue-600 transition-colors"
                  >
                    <TrendingUp className="h-4 w-4" />
                  </button>
                  <button
                    onClick={() => onEdit(item)}
                    title="Edit"
                    className="p-1.5 rounded hover:bg-gray-100 text-gray-600 transition-colors"
                  >
                    <Edit2 className="h-4 w-4" />
                  </button>
                  <button
                    onClick={() => onDelete(item)}
                    title="Delete"
                    className="p-1.5 rounded hover:bg-red-50 text-red-600 transition-colors"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default InventoryList;
