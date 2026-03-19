import React, { useState, useEffect } from 'react';
import { Plus, Search, Filter } from 'lucide-react';
import api from '../api/axios';
import DynamicTable from '../components/DynamicTable';
import { useMetadata } from '../context/MetadataContext';

const Products: React.FC = () => {
  const [products, setProducts] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const { config } = useMetadata();

  useEffect(() => {
    // Mock data for demo
    setProducts([
      { name: 'Pharma Product A', sku: 'PHA-001', type: 'Physical', category: 'Medicine', status: 'Active' },
      { name: 'Consumable B', sku: 'CON-002', type: 'Consumable', category: 'Supplies', status: 'Active' },
      { name: 'Service C', sku: 'SRV-003', type: 'Service', category: 'Consultation', status: 'Active' },
      { name: 'Digital D', sku: 'DIG-004', type: 'Digital', category: 'Software', status: 'Active' },
    ]);
    setLoading(false);
  }, []);

  return (
    <div className="space-y-6 text-black">
      <div className="flex justify-between items-center">
        <div className="relative w-96">
          <span className="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
            <Search size={18} />
          </span>
          <input
            type="text"
            className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="Search products..."
          />
        </div>
        <button className="flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
          <Plus size={18} className="mr-2" />
          Add Product
        </button>
      </div>

      <DynamicTable 
        columns={config.ui.tables.products || []} 
        data={products} 
        loading={loading} 
      />
    </div>
  );
};

export default Products;
