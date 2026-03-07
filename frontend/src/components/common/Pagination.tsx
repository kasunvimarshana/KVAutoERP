import React from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface PaginationProps {
  currentPage: number;
  lastPage: number;
  total: number;
  perPage: number;
  onPageChange: (page: number) => void;
  onPerPageChange: (perPage: number) => void;
}

const PER_PAGE_OPTIONS = [10, 25, 50, 100];

const Pagination: React.FC<PaginationProps> = ({
  currentPage,
  lastPage,
  total,
  perPage,
  onPageChange,
  onPerPageChange,
}) => {
  if (lastPage <= 1 && total <= PER_PAGE_OPTIONS[0]) return null;

  const start = (currentPage - 1) * perPage + 1;
  const end = Math.min(currentPage * perPage, total);

  const pages: (number | 'ellipsis')[] = [];
  if (lastPage <= 7) {
    for (let i = 1; i <= lastPage; i++) pages.push(i);
  } else {
    pages.push(1);
    if (currentPage > 3) pages.push('ellipsis');
    for (let i = Math.max(2, currentPage - 1); i <= Math.min(lastPage - 1, currentPage + 1); i++) {
      pages.push(i);
    }
    if (currentPage < lastPage - 2) pages.push('ellipsis');
    pages.push(lastPage);
  }

  return (
    <div className="flex items-center justify-between px-4 py-3 border-t border-gray-200 bg-white">
      <div className="flex items-center gap-3 text-sm text-gray-600">
        <span>
          Showing {start}\u2013{end} of {total} results
        </span>
        <select
          value={perPage}
          onChange={(e) => onPerPageChange(Number(e.target.value))}
          className="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          {PER_PAGE_OPTIONS.map((opt) => (
            <option key={opt} value={opt}>
              {opt} / page
            </option>
          ))}
        </select>
      </div>

      <div className="flex items-center gap-1">
        <button
          onClick={() => onPageChange(currentPage - 1)}
          disabled={currentPage === 1}
          className="p-1.5 rounded hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <ChevronLeft className="h-4 w-4" />
        </button>

        {pages.map((page, idx) =>
          page === 'ellipsis' ? (
            <span key={`e-${idx}`} className="px-2 text-gray-400">
              \u2026
            </span>
          ) : (
            <button
              key={page}
              onClick={() => onPageChange(page as number)}
              className={`min-w-[32px] h-8 px-2 rounded text-sm font-medium transition-colors ${
                page === currentPage
                  ? 'bg-blue-600 text-white'
                  : 'hover:bg-gray-100 text-gray-700'
              }`}
            >
              {page}
            </button>
          )
        )}

        <button
          onClick={() => onPageChange(currentPage + 1)}
          disabled={currentPage === lastPage}
          className="p-1.5 rounded hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <ChevronRight className="h-4 w-4" />
        </button>
      </div>
    </div>
  );
};

export default Pagination;
