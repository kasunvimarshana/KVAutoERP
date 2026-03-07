import React, { useState } from 'react';
import { InventoryItem, StockAdjustment } from '../../types';

interface StockAdjustmentFormProps {
  item: InventoryItem;
  onSubmit: (adjustment: StockAdjustment) => Promise<void>;
  onCancel: () => void;
  isSubmitting: boolean;
}

const StockAdjustmentForm: React.FC<StockAdjustmentFormProps> = ({
  item,
  onSubmit,
  onCancel,
  isSubmitting,
}) => {
  const [quantity, setQuantity] = useState(0);
  const [operation, setOperation] = useState<StockAdjustment['operation']>('add');
  const [reason, setReason] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (quantity <= 0) { setError('Quantity must be greater than 0'); return; }
    if (!reason.trim()) { setError('Reason is required'); return; }
    setError('');
    await onSubmit({ quantity, operation, reason });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="bg-blue-50 rounded-lg p-3 text-sm">
        <p className="font-medium text-blue-900">{item.name}</p>
        <p className="text-blue-700">Current stock: <span className="font-semibold">{item.quantity}</span></p>
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Operation</label>
        <select
          value={operation}
          onChange={(e) => setOperation(e.target.value as StockAdjustment['operation'])}
          className="input-field"
        >
          <option value="add">Add Stock</option>
          <option value="subtract">Remove Stock</option>
          <option value="set">Set Exact Quantity</option>
        </select>
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
        <input
          type="number"
          min={1}
          value={quantity}
          onChange={(e) => setQuantity(Number(e.target.value))}
          className="input-field"
        />
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
        <input
          type="text"
          value={reason}
          onChange={(e) => setReason(e.target.value)}
          placeholder="e.g. Stock count correction, Purchase order received\u2026"
          className="input-field"
        />
      </div>

      {error && <p className="text-red-500 text-sm">{error}</p>}

      <div className="flex justify-end gap-3">
        <button type="button" onClick={onCancel} className="btn-secondary">Cancel</button>
        <button type="submit" disabled={isSubmitting} className="btn-primary">
          {isSubmitting ? 'Adjusting\u2026' : 'Adjust Stock'}
        </button>
      </div>
    </form>
  );
};

export default StockAdjustmentForm;
