import React, { useState } from 'react';
import { Plus, Trash2 } from 'lucide-react';
import { CreateOrderPayload, InventoryItem } from '../../types';

interface OrderFormProps {
  inventoryItems: InventoryItem[];
  onSubmit: (data: CreateOrderPayload) => Promise<void>;
  onCancel: () => void;
  isSubmitting: boolean;
}

interface LineItem {
  inventoryId: string;
  quantity: number;
}

const OrderForm: React.FC<OrderFormProps> = ({ inventoryItems, onSubmit, onCancel, isSubmitting }) => {
  const [customerId, setCustomerId] = useState('');
  const [customerName, setCustomerName] = useState('');
  const [customerEmail, setCustomerEmail] = useState('');
  const [paymentMethod, setPaymentMethod] = useState('credit_card');
  const [notes, setNotes] = useState('');
  const [lineItems, setLineItems] = useState<LineItem[]>([{ inventoryId: '', quantity: 1 }]);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const addLine = () => setLineItems((p) => [...p, { inventoryId: '', quantity: 1 }]);
  const removeLine = (idx: number) => setLineItems((p) => p.filter((_, i) => i !== idx));
  const updateLine = (idx: number, field: keyof LineItem, value: string | number) =>
    setLineItems((p) => p.map((l, i) => (i === idx ? { ...l, [field]: value } : l)));

  const validate = () => {
    const e: Record<string, string> = {};
    if (!customerName.trim()) e.customerName = 'Customer name is required';
    if (!customerEmail.trim()) e.customerEmail = 'Customer email is required';
    if (lineItems.some((l) => !l.inventoryId)) e.lineItems = 'All line items must have an item selected';
    if (lineItems.some((l) => l.quantity <= 0)) e.lineItems = 'Quantity must be > 0';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validate()) return;
    await onSubmit({
      customerId: customerId || `cust-${Date.now()}`,
      customerName,
      customerEmail,
      items: lineItems.map((l) => ({ inventoryId: l.inventoryId, quantity: l.quantity })),
      notes: notes || undefined,
      paymentMethod,
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
          <input
            type="text"
            value={customerName}
            onChange={(e) => setCustomerName(e.target.value)}
            className={`input-field ${errors.customerName ? 'border-red-400' : ''}`}
          />
          {errors.customerName && <p className="text-red-500 text-xs mt-1">{errors.customerName}</p>}
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Customer Email *</label>
          <input
            type="email"
            value={customerEmail}
            onChange={(e) => setCustomerEmail(e.target.value)}
            className={`input-field ${errors.customerEmail ? 'border-red-400' : ''}`}
          />
          {errors.customerEmail && <p className="text-red-500 text-xs mt-1">{errors.customerEmail}</p>}
        </div>
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
        <select value={paymentMethod} onChange={(e) => setPaymentMethod(e.target.value)} className="input-field">
          <option value="credit_card">Credit Card</option>
          <option value="bank_transfer">Bank Transfer</option>
          <option value="cash">Cash</option>
          <option value="paypal">PayPal</option>
        </select>
      </div>

      <div>
        <div className="flex items-center justify-between mb-2">
          <label className="text-sm font-medium text-gray-700">Order Items *</label>
          <button type="button" onClick={addLine} className="btn-secondary text-xs py-1">
            <Plus className="h-3 w-3" /> Add Item
          </button>
        </div>
        {errors.lineItems && <p className="text-red-500 text-xs mb-2">{errors.lineItems}</p>}
        <div className="space-y-2">
          {lineItems.map((line, idx) => (
            <div key={idx} className="flex gap-2 items-center">
              <select
                value={line.inventoryId}
                onChange={(e) => updateLine(idx, 'inventoryId', e.target.value)}
                className="input-field flex-1"
              >
                <option value="">Select item\u2026</option>
                {inventoryItems.map((item) => (
                  <option key={item.id} value={item.id}>
                    [{item.sku}] {item.name} \u2014 ${item.unitPrice.toFixed(2)} (avail: {item.availableQuantity})
                  </option>
                ))}
              </select>
              <input
                type="number"
                min={1}
                value={line.quantity}
                onChange={(e) => updateLine(idx, 'quantity', Number(e.target.value))}
                className="input-field w-20"
              />
              {lineItems.length > 1 && (
                <button type="button" onClick={() => removeLine(idx)} className="text-red-500 hover:text-red-700">
                  <Trash2 className="h-4 w-4" />
                </button>
              )}
            </div>
          ))}
        </div>
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea
          value={notes}
          onChange={(e) => setNotes(e.target.value)}
          rows={2}
          className="input-field"
        />
      </div>

      <div className="flex justify-end gap-3 pt-2">
        <button type="button" onClick={onCancel} className="btn-secondary">Cancel</button>
        <button type="submit" disabled={isSubmitting} className="btn-primary">
          {isSubmitting ? 'Creating\u2026' : 'Create Order'}
        </button>
      </div>
    </form>
  );
};

export default OrderForm;
