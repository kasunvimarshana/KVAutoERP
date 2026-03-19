import React, { useState, useEffect } from 'react';
import { Box, Search, Filter, ArrowUpRight, ArrowDownLeft } from 'lucide-react';
import DynamicTable from '../components/DynamicTable';
import { useMetadata } from '../context/MetadataContext';

const Inventory: React.FC = () => {
  const [inventory, setInventory] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const { config } = useMetadata();

  useEffect(() => {
    // Mock data for demo
    setInventory([
      { product_name: 'Pharma Product A', warehouse_name: 'Main WH', lot_number: 'LOT-2024-001', available_quantity: 1200, status: 'Available' },
      { product_name: 'Consumable B', warehouse_name: 'Main WH', lot_number: 'LOT-2024-002', available_quantity: 450, status: 'Available' },
      { product_name: 'Product C', warehouse_name: 'Retail WH', lot_number: 'LOT-2024-003', available_quantity: 15, status: 'Low Stock' },
    ]);
    setLoading(false);
  }, []);

  return (
    <div className="space-y-6 text-black">
      <div className="flex justify-between items-center">
        <div className="flex space-x-4">
          <div className="relative w-96">
            <span className="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
              <Search size={18} />
            </span>
            <input
              type="text"
              className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Search inventory..."
            />
          </div>
          <button className="flex items-center px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 hover:bg-gray-50 transition-colors">
            <Filter size={18} className="mr-2" />
            Filters
          </button>
        </div>
        <div className="flex space-x-3">
          <button className="flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
            <ArrowDownLeft size={18} className="mr-2" />
            Stock In
          </button>
          <button className="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
            <ArrowUpRight size={18} className="mr-2" />
            Stock Out
          </button>
        </div>
      </div>

      <DynamicTable 
        columns={config.ui.tables.inventory || []} 
        data={inventory} 
        loading={loading} 
      />
    </div>
  );
};

export default Inventory;
