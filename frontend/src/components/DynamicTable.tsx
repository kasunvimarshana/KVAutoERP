import React from 'react';

interface Column {
  key: string;
  label: string;
  type: 'text' | 'number' | 'badge' | 'date' | 'currency';
}

interface DynamicTableProps {
  columns: Column[];
  data: any[];
  loading: boolean;
}

const DynamicTable: React.FC<DynamicTableProps> = ({ columns, data, loading }) => {
  if (loading) return <div className="text-center p-8 text-indigo-600 animate-pulse" role="status">Loading module data...</div>;

  const formatValue = (value: any, type: Column['type']) => {
    if (value === null || value === undefined) return '-';
    
    switch (type) {
      case 'currency':
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
      case 'date':
        return new Date(value).toLocaleDateString();
      case 'number':
        return new Intl.NumberFormat('en-US').format(value);
      default:
        return value;
    }
  };

  const getBadgeClass = (value: string) => {
    const success = ['Active', 'Available', 'Completed', 'Confirmed', 'Success'];
    const warning = ['Pending', 'Processing', 'Trialing', 'Low Stock'];
    const danger = ['Cancelled', 'Suspended', 'Damaged', 'Expired', 'Failed'];

    if (success.includes(value)) return 'bg-green-100 text-green-800 border border-green-200';
    if (warning.includes(value)) return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
    if (danger.includes(value)) return 'bg-red-100 text-red-800 border border-red-200';
    return 'bg-gray-100 text-gray-800 border border-gray-200';
  };

  return (
    <div className="bg-white shadow-xl rounded-xl overflow-hidden text-black ring-1 ring-gray-200">
      <table className="min-w-full divide-y divide-gray-200" aria-label="Module Data Table">
        <thead className="bg-gray-50/50">
          <tr>
            {columns.map((col) => (
              <th 
                key={col.key} 
                scope="col"
                className="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest"
              >
                {col.label}
              </th>
            ))}
            <th scope="col" className="relative px-6 py-4">
              <span className="sr-only">Actions</span>
            </th>
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-100">
          {data.length > 0 ? (
            data.map((row, idx) => (
              <tr key={idx} className="hover:bg-indigo-50/30 transition-all duration-200">
                {columns.map((col) => (
                  <td key={col.key} className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                    {col.type === 'badge' ? (
                      <span className={`px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full shadow-sm ${getBadgeClass(row[col.key])}`}>
                        {row[col.key]}
                      </span>
                    ) : (
                      formatValue(row[col.key], col.type)
                    )}
                  </td>
                ))}
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-bold">
                  <button className="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition-colors">
                    Manage
                  </button>
                </td>
              </tr>
            ))
          ) : (
            <tr>
              <td colSpan={columns.length + 1} className="px-6 py-12 text-center">
                <div className="flex flex-col items-center justify-center space-y-2">
                  <p className="text-gray-400 text-lg font-medium">No results found</p>
                  <p className="text-gray-300 text-sm">Try adjusting your filters or search terms.</p>
                </div>
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
};

export default DynamicTable;
