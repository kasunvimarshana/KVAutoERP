import React, { useState, useEffect } from 'react';
import { InventoryItem, CreateInventoryPayload } from '../../types';

interface InventoryFormProps {
  initialData?: InventoryItem;
  onSubmit: (data: CreateInventoryPayload) => Promise<void>;
  onCancel: () => void;
  isSubmitting: boolean;
}

const defaultValues: CreateInventoryPayload = {
  sku: '',
  name: '',
  description: '',
  quantity: 0,
  unitCost: 0,
  unitPrice: 0,
  category: '',
  location: '',
  minStockLevel: 5,
  maxStockLevel: 100,
};

const InventoryForm: React.FC<InventoryFormProps> = ({
  initialData,
  onSubmit,
  onCancel,
  isSubmitting,
}) => {
  const [form, setForm] = useState<CreateInventoryPayload>(defaultValues);
  const [errors, setErrors] = useState<Partial<Record<keyof CreateInventoryPayload, string>>>({});

  useEffect(() => {
    if (initialData) {
      setForm({
        sku: initialData.sku,
        name: initialData.name,
        description: initialData.description || '',
        quantity: initialData.quantity,
        unitCost: initialData.unitCost,
        unitPrice: initialData.unitPrice,
        category: initialData.category || '',
        location: initialData.location || '',
        minStockLevel: initialData.minStockLevel,
        maxStockLevel: initialData.maxStockLevel,
      });
    }
  }, [initialData]);

  const set = (field: keyof CreateInventoryPayload, value: string | number) => {
    setForm((prev) => ({ ...prev, [field]: value }));
    setErrors((prev) => ({ ...prev, [field]: undefined }));
  };

  const validate = (): boolean => {
    const e: Partial<Record<keyof CreateInventoryPayload, string>> = {};
    if (!form.sku.trim()) e.sku = 'SKU is required';
    if (!form.name.trim()) e.name = 'Name is required';
    if (form.quantity < 0) e.quantity = 'Quantity must be \u2265 0';
    if (form.unitCost < 0) e.unitCost = 'Unit cost must be \u2265 0';
    if (form.unitPrice < 0) e.unitPrice = 'Unit price must be \u2265 0';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validate()) return;
    await onSubmit(form);
  };

  const Field: React.FC<{
    label: string;
    field: keyof CreateInventoryPayload;
    type?: string;
    step?: string;
    min?: number;
  }> = ({ label, field, type = 'text', step, min }) => (
    <div>
      <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
      <input
        type={type}
        step={step}
        min={min}
        value={String(form[field] ?? '')}
        onChange={(e) => set(field, type === 'number' ? Number(e.target.value) : e.target.value)}
        className={`input-field ${errors[field] ? 'border-red-400' : ''}`}
      />
      {errors[field] && <p className="text-red-500 text-xs mt-1">{errors[field]}</p>}
    </div>
  );

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="grid grid-cols-2 gap-4">
        <Field label="SKU *" field="sku" />
        <Field label="Name *" field="name" />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea
          value={form.description}
          onChange={(e) => set('description', e.target.value)}
          rows={2}
          className="input-field"
        />
      </div>
      <div className="grid grid-cols-2 gap-4">
        <Field label="Category" field="category" />
        <Field label="Location" field="location" />
      </div>
      <div className="grid grid-cols-3 gap-4">
        <Field label="Quantity" field="quantity" type="number" min={0} />
        <Field label="Unit Cost ($)" field="unitCost" type="number" step="0.01" min={0} />
        <Field label="Unit Price ($)" field="unitPrice" type="number" step="0.01" min={0} />
      </div>
      <div className="grid grid-cols-2 gap-4">
        <Field label="Min Stock Level" field="minStockLevel" type="number" min={0} />
        <Field label="Max Stock Level" field="maxStockLevel" type="number" min={0} />
      </div>
      <div className="flex justify-end gap-3 pt-2">
        <button type="button" onClick={onCancel} className="btn-secondary">
          Cancel
        </button>
        <button type="submit" disabled={isSubmitting} className="btn-primary">
          {isSubmitting ? 'Saving\u2026' : initialData ? 'Update Item' : 'Create Item'}
        </button>
      </div>
    </form>
  );
};

export default InventoryForm;
