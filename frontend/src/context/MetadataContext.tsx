import React, { createContext, useContext, useState, useEffect } from 'react';
import api from '../api/axios';

interface MetadataContextType {
  config: any;
  loading: boolean;
  refreshConfig: () => Promise<void>;
}

const MetadataContext = createContext<MetadataContextType | undefined>(undefined);

export const MetadataProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [config, setConfig] = useState<any>({
    modules: [],
    features: {},
    ui: {
      tables: {},
      forms: {}
    }
  });
  const [loading, setLoading] = useState(true);

  const refreshConfig = async () => {
    try {
      setLoading(true);
      // In a production system, this would fetch from a central config service or tenant manager
      // const response = await api.get('/metadata/config');
      // setConfig(response.data.data);
      
      // Simulating a highly dynamic, configuration-driven ERP response
      setConfig({
        modules: [
          { id: 'dashboard', name: 'Dashboard', icon: 'layout-dashboard', path: '/' },
          { id: 'products', name: 'Products', icon: 'package', path: '/products' },
          { id: 'inventory', name: 'Inventory', icon: 'box', path: '/inventory' },
          { id: 'orders', name: 'Orders', icon: 'shopping-cart', path: '/orders' }
        ],
        features: {
          pharma_compliance: true,
          multi_location_pricing: true,
          commission_calc: true
        },
        ui: {
          tables: {
            products: [
              { key: 'name', label: 'Product Name', type: 'text', sortable: true },
              { key: 'sku', label: 'SKU', type: 'text', sortable: true },
              { key: 'final_price', label: 'Price', type: 'currency', sortable: true },
              { key: 'status', label: 'Status', type: 'badge' }
            ],
            inventory: [
              { key: 'product_name', label: 'Product', type: 'text' },
              { key: 'warehouse_name', label: 'Warehouse', type: 'text' },
              { key: 'lot_number', label: 'Lot/Serial', type: 'text' },
              { key: 'available_quantity', label: 'Qty', type: 'number' },
              { key: 'status', label: 'Status', type: 'badge' }
            ],
            orders: [
              { key: 'order_number', label: 'Order ID', type: 'text' },
              { key: 'customer_name', label: 'Customer', type: 'text' },
              { key: 'total_amount', label: 'Total', type: 'currency' },
              { key: 'status', label: 'Status', type: 'badge' },
              { key: 'created_at', label: 'Date', type: 'date' }
            ]
          }
        }
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    refreshConfig();
  }, []);

  return (
    <MetadataContext.Provider value={{ config, loading, refreshConfig }}>
      {children}
    </MetadataContext.Provider>
  );
};

export const useMetadata = () => {
  const context = useContext(MetadataContext);
  if (context === undefined) {
    throw new Error('useMetadata must be used within a MetadataProvider');
  }
  return context;
};
