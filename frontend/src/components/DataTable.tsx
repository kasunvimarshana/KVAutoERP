import React, { useState, useMemo, useCallback } from 'react';
import type { Column, FilterOption, PaginationMeta } from '../types';

interface Props<T extends Record<string, unknown>> {
  columns: Column<T>[];
  data: T[];
  meta?: PaginationMeta;
  isLoading?: boolean;
  searchPlaceholder?: string;
  filterOptions?: { key: string; label: string; options: FilterOption[] }[];
  onPageChange?: (page: number) => void;
  onSearchChange?: (search: string) => void;
  onFilterChange?: (key: string, value: string) => void;
  onSortChange?: (key: string, dir: 'asc' | 'desc') => void;
  actions?: (row: T) => React.ReactNode;
  emptyMessage?: string;
  sortKey?: string;
  sortDir?: 'asc' | 'desc';
}

function Skeleton() {
  return (
    <div className="animate-pulse">
      {Array.from({ length: 5 }).map((_, i) => (
        <div key={i} className="flex gap-4 py-3 border-b border-gray-100">
          {Array.from({ length: 4 }).map((_, j) => (
            <div key={j} className="h-4 bg-gray-200 rounded flex-1" />
          ))}
        </div>
      ))}
    </div>
  );
}

export default function DataTable<T extends Record<string, unknown>>({
  columns,
  data,
  meta,
  isLoading = false,
  searchPlaceholder = 'Search…',
  filterOptions = [],
  onPageChange,
  onSearchChange,
  onFilterChange,
  onSortChange,
  actions,
  emptyMessage = 'No records found.',
  sortKey,
  sortDir = 'asc',
}: Props<T>) {
  const [localSearch, setLocalSearch] = useState('');
  const [activeFilters, setActiveFilters] = useState<Record<string, string>>({});

  const handleSearch = useCallback(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      setLocalSearch(e.target.value);
      onSearchChange?.(e.target.value);
    },
    [onSearchChange]
  );

  const handleFilter = useCallback(
    (key: string, value: string) => {
      setActiveFilters((prev) => ({ ...prev, [key]: value }));
      onFilterChange?.(key, value);
    },
    [onFilterChange]
  );

  const handleSort = useCallback(
    (key: string) => {
      const newDir = sortKey === key && sortDir === 'asc' ? 'desc' : 'asc';
      onSortChange?.(key, newDir);
    },
    [sortKey, sortDir, onSortChange]
  );

  const pageNumbers = useMemo(() => {
    if (!meta) return [];
    const total = meta.last_page;
    const current = meta.current_page;
    const pages: (number | '…')[] = [];
    if (total <= 7) {
      for (let i = 1; i <= total; i++) pages.push(i);
    } else {
      pages.push(1);
      if (current > 3) pages.push('…');
      for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
        pages.push(i);
      }
      if (current < total - 2) pages.push('…');
      pages.push(total);
    }
    return pages;
  }, [meta]);

  function getCellValue(row: T, key: string): unknown {
    if (key.includes('.')) {
      return key.split('.').reduce<unknown>((acc, part) => {
        if (acc && typeof acc === 'object') return (acc as Record<string, unknown>)[part];
        return undefined;
      }, row);
    }
    return row[key as keyof T];
  }

  return (
    <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      {/* Toolbar */}
      <div className="px-4 py-3 border-b border-gray-200 flex flex-wrap gap-3 items-center">
        <div className="relative flex-1 min-w-[200px]">
          <span className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg className="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </span>
          <input
            type="text"
            value={localSearch}
            onChange={handleSearch}
            placeholder={searchPlaceholder}
            className="block w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none"
          />
        </div>

        {filterOptions.map((f) => (
          <select
            key={f.key}
            value={activeFilters[f.key] ?? ''}
            onChange={(e) => handleFilter(f.key, e.target.value)}
            className="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none"
          >
            <option value="">{f.label}: All</option>
            {f.options.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </select>
        ))}

        {meta && (
          <span className="text-xs text-gray-500 ml-auto whitespace-nowrap">
            {meta.from}–{meta.to} of {meta.total}
          </span>
        )}
      </div>

      {/* Table */}
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              {columns.map((col) => (
                <th
                  key={String(col.key)}
                  scope="col"
                  className={`px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider ${col.sortable ? 'cursor-pointer select-none hover:text-gray-700' : ''} ${col.className ?? ''}`}
                  onClick={() => col.sortable && handleSort(String(col.key))}
                >
                  <span className="flex items-center gap-1">
                    {col.label}
                    {col.sortable && (
                      <span className="inline-flex flex-col leading-none">
                        <svg
                          className={`w-3 h-3 ${sortKey === col.key && sortDir === 'asc' ? 'text-primary-600' : 'text-gray-300'}`}
                          viewBox="0 0 8 4" fill="currentColor"
                        >
                          <path d="M4 0L8 4H0z" />
                        </svg>
                        <svg
                          className={`w-3 h-3 ${sortKey === col.key && sortDir === 'desc' ? 'text-primary-600' : 'text-gray-300'}`}
                          viewBox="0 0 8 4" fill="currentColor"
                        >
                          <path d="M4 4L0 0h8z" />
                        </svg>
                      </span>
                    )}
                  </span>
                </th>
              ))}
              {actions && (
                <th scope="col" className="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              )}
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-100">
            {isLoading ? (
              <tr>
                <td colSpan={columns.length + (actions ? 1 : 0)} className="px-4 py-6">
                  <Skeleton />
                </td>
              </tr>
            ) : data.length === 0 ? (
              <tr>
                <td
                  colSpan={columns.length + (actions ? 1 : 0)}
                  className="px-4 py-12 text-center text-sm text-gray-400"
                >
                  <svg className="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4" />
                  </svg>
                  {emptyMessage}
                </td>
              </tr>
            ) : (
              data.map((row, rowIdx) => (
                <tr key={rowIdx} className="hover:bg-gray-50 transition-colors">
                  {columns.map((col) => {
                    const rawValue = getCellValue(row, String(col.key));
                    return (
                      <td
                        key={String(col.key)}
                        className={`px-4 py-3 text-sm text-gray-700 ${col.className ?? ''}`}
                      >
                        {col.render ? col.render(rawValue, row) : String(rawValue ?? '')}
                      </td>
                    );
                  })}
                  {actions && (
                    <td className="px-4 py-3 text-right text-sm">
                      {actions(row)}
                    </td>
                  )}
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      {meta && meta.last_page > 1 && (
        <div className="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
          <p className="text-xs text-gray-500">
            Page {meta.current_page} of {meta.last_page}
          </p>
          <nav className="flex gap-1">
            <button
              onClick={() => onPageChange?.(meta.current_page - 1)}
              disabled={meta.current_page === 1}
              className="px-2 py-1 rounded text-sm border border-gray-300 disabled:opacity-40 hover:bg-gray-100 transition-colors"
            >
              ‹
            </button>
            {pageNumbers.map((p, i) =>
              p === '…' ? (
                <span key={`ellipsis-${i}`} className="px-2 py-1 text-sm text-gray-400">…</span>
              ) : (
                <button
                  key={p}
                  onClick={() => onPageChange?.(p as number)}
                  className={`px-3 py-1 rounded text-sm border transition-colors ${
                    p === meta.current_page
                      ? 'bg-primary-600 border-primary-600 text-white'
                      : 'border-gray-300 hover:bg-gray-100'
                  }`}
                >
                  {p}
                </button>
              )
            )}
            <button
              onClick={() => onPageChange?.(meta.current_page + 1)}
              disabled={meta.current_page === meta.last_page}
              className="px-2 py-1 rounded text-sm border border-gray-300 disabled:opacity-40 hover:bg-gray-100 transition-colors"
            >
              ›
            </button>
          </nav>
        </div>
      )}
    </div>
  );
}
