import React from 'react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import * as AuthContextModule from '../context/AuthContext';

// ─── Mocks ────────────────────────────────────────────────────────────────────

vi.mock('../context/AuthContext', () => ({
  AuthProvider: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  useAuthContext: vi.fn(() => ({
    user: { id: '1', email: 'admin@test.com', name: 'Admin User', roles: ['admin'], tenantId: 'tenant-1', tenantName: 'Acme Corp' },
    isAuthenticated: true,
    isLoading: false,
    token: 'mock-token',
    login: vi.fn(),
    logout: vi.fn(),
    hasRole: (role: string) => role === 'admin',
    hasAnyRole: (roles: string[]) => roles.includes('admin'),
  })),
}));

vi.mock('../context/TenantContext', () => ({
  TenantProvider: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  useTenantContext: vi.fn(() => ({
    tenant: { id: 'tenant-1', name: 'Acme Corp', slug: 'acme', plan: 'professional', status: 'active', created_at: '', updated_at: '' },
    tenantId: 'tenant-1',
    isLoading: false,
  })),
}));

vi.mock('../services/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
    interceptors: { request: { use: vi.fn() }, response: { use: vi.fn() } },
  },
}));

vi.mock('../services/userService', () => ({
  userService: {
    list: vi.fn().mockResolvedValue({
      data: {
        data: [
          { id: 1, name: 'Alice Johnson', email: 'alice@test.com', role: 'admin', status: 'active', tenant_id: 'tenant-1', created_at: '2024-01-15T10:00:00Z', updated_at: '2024-01-15T10:00:00Z' },
          { id: 2, name: 'Bob Smith', email: 'bob@test.com', role: 'manager', status: 'active', tenant_id: 'tenant-1', created_at: '2024-01-16T10:00:00Z', updated_at: '2024-01-16T10:00:00Z' },
        ],
        meta: { current_page: 1, last_page: 1, per_page: 10, total: 2, from: 1, to: 2 },
      },
    }),
    create: vi.fn().mockResolvedValue({ data: { data: { id: 3, name: 'New User' } } }),
    update: vi.fn().mockResolvedValue({ data: { data: {} } }),
    delete: vi.fn().mockResolvedValue({}),
  },
}));

vi.mock('../services/productService', () => ({
  productService: {
    list: vi.fn().mockResolvedValue({
      data: {
        data: [
          { id: 1, name: 'Widget A', sku: 'WGT-001', category: 'Electronics', unit_price: 29.99, cost_price: 15.0, status: 'active', tenant_id: 'tenant-1', created_at: '2024-01-01T00:00:00Z', updated_at: '2024-01-01T00:00:00Z' },
          { id: 2, name: 'Widget B', sku: 'WGT-002', category: 'Electronics', unit_price: 49.99, cost_price: 25.0, status: 'active', tenant_id: 'tenant-1', created_at: '2024-01-02T00:00:00Z', updated_at: '2024-01-02T00:00:00Z' },
        ],
        meta: { current_page: 1, last_page: 1, per_page: 10, total: 2, from: 1, to: 2 },
      },
    }),
    create: vi.fn().mockResolvedValue({ data: { data: {} } }),
    update: vi.fn().mockResolvedValue({ data: { data: {} } }),
    delete: vi.fn().mockResolvedValue({}),
  },
}));

vi.mock('../services/inventoryService', () => ({
  inventoryService: {
    listItems: vi.fn().mockResolvedValue({
      data: {
        data: [
          { id: 1, product_id: 1, product: { id: 1, name: 'Widget A', sku: 'WGT-001' }, warehouse: 'Main', quantity: 100, min_quantity: 10, max_quantity: 500, tenant_id: 'tenant-1', updated_at: '2024-01-15T00:00:00Z' },
        ],
        meta: { current_page: 1, last_page: 1, per_page: 10, total: 1, from: 1, to: 1 },
      },
    }),
    listTransactions: vi.fn().mockResolvedValue({
      data: {
        data: [],
        meta: { current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 },
      },
    }),
    createTransaction: vi.fn().mockResolvedValue({ data: { data: {} } }),
  },
}));

vi.mock('../services/orderService', () => ({
  orderService: {
    list: vi.fn().mockResolvedValue({
      data: {
        data: [
          { id: 1, order_number: 'ORD-0001', customer_name: 'Carol White', customer_email: 'carol@test.com', status: 'pending', payment_status: 'pending', items: [], subtotal: 100, tax: 10, total: 110, tenant_id: 'tenant-1', created_at: '2024-01-20T10:00:00Z', updated_at: '2024-01-20T10:00:00Z' },
        ],
        meta: { current_page: 1, last_page: 1, per_page: 10, total: 1, from: 1, to: 1 },
      },
    }),
    create: vi.fn().mockResolvedValue({ data: { data: {} } }),
    update: vi.fn().mockResolvedValue({ data: { data: {} } }),
    cancel: vi.fn().mockResolvedValue({ data: { data: {} } }),
    delete: vi.fn().mockResolvedValue({}),
  },
}));

// ─── Helpers ─────────────────────────────────────────────────────────────────

import DataTable from '../components/DataTable';
import Dashboard from '../pages/Dashboard';
import Users from '../pages/Users';
import Products from '../pages/Products';
import Inventory from '../pages/Inventory';
import Orders from '../pages/Orders';
import ProtectedRoute from '../components/ProtectedRoute';
import Layout from '../components/Layout';

function Wrapper({ children }: { children: React.ReactNode }) {
  return <MemoryRouter>{children}</MemoryRouter>;
}

// ─── DataTable ────────────────────────────────────────────────────────────────

describe('DataTable', () => {
  const columns = [
    { key: 'name' as const, label: 'Name', sortable: true },
    { key: 'email' as const, label: 'Email' },
  ];

  const data = [
    { name: 'Alice', email: 'alice@test.com' },
    { name: 'Bob', email: 'bob@test.com' },
  ];

  it('renders column headers', () => {
    render(<Wrapper><DataTable columns={columns} data={data} /></Wrapper>);
    expect(screen.getByText('Name')).toBeInTheDocument();
    expect(screen.getByText('Email')).toBeInTheDocument();
  });

  it('renders data rows', () => {
    render(<Wrapper><DataTable columns={columns} data={data} /></Wrapper>);
    expect(screen.getByText('Alice')).toBeInTheDocument();
    expect(screen.getByText('Bob')).toBeInTheDocument();
  });

  it('renders empty message when data is empty', () => {
    render(<Wrapper><DataTable columns={columns} data={[]} emptyMessage="Nothing here." /></Wrapper>);
    expect(screen.getByText('Nothing here.')).toBeInTheDocument();
  });

  it('shows loading skeleton when isLoading is true', () => {
    const { container } = render(<Wrapper><DataTable columns={columns} data={[]} isLoading={true} /></Wrapper>);
    expect(container.querySelector('.animate-pulse')).toBeInTheDocument();
  });

  it('renders search input', () => {
    render(<Wrapper><DataTable columns={columns} data={data} searchPlaceholder="Find something" /></Wrapper>);
    expect(screen.getByPlaceholderText('Find something')).toBeInTheDocument();
  });

  it('calls onSearchChange when typing', async () => {
    const user = userEvent.setup();
    const onSearch = vi.fn();
    render(<Wrapper><DataTable columns={columns} data={data} onSearchChange={onSearch} /></Wrapper>);
    await user.type(screen.getByRole('textbox'), 'test');
    expect(onSearch).toHaveBeenCalled();
  });

  it('renders pagination when meta is provided', () => {
    const meta = { current_page: 1, last_page: 3, per_page: 10, total: 30, from: 1, to: 10 };
    render(<Wrapper><DataTable columns={columns} data={data} meta={meta} /></Wrapper>);
    expect(screen.getByText('Page 1 of 3')).toBeInTheDocument();
  });

  it('calls onPageChange when clicking next page', async () => {
    const user = userEvent.setup();
    const onPage = vi.fn();
    const meta = { current_page: 1, last_page: 3, per_page: 10, total: 30, from: 1, to: 10 };
    render(<Wrapper><DataTable columns={columns} data={data} meta={meta} onPageChange={onPage} /></Wrapper>);
    await user.click(screen.getByText('›'));
    expect(onPage).toHaveBeenCalledWith(2);
  });

  it('renders filter dropdown when filterOptions provided', () => {
    const filters = [{ key: 'status', label: 'Status', options: [{ label: 'Active', value: 'active' }] }];
    render(<Wrapper><DataTable columns={columns} data={data} filterOptions={filters} /></Wrapper>);
    expect(screen.getByText('Status: All')).toBeInTheDocument();
  });

  it('renders action buttons when actions prop provided', () => {
    render(
      <Wrapper>
        <DataTable
          columns={columns}
          data={data}
          actions={() => <button>Edit</button>}
        />
      </Wrapper>
    );
    expect(screen.getAllByText('Edit')).toHaveLength(2);
  });

  it('calls onSortChange when clicking sortable column header', async () => {
    const user = userEvent.setup();
    const onSort = vi.fn();
    render(<Wrapper><DataTable columns={columns} data={data} onSortChange={onSort} /></Wrapper>);
    await user.click(screen.getByText('Name'));
    expect(onSort).toHaveBeenCalledWith('name', 'asc');
  });
});

// ─── ProtectedRoute ───────────────────────────────────────────────────────────

describe('ProtectedRoute', () => {
  it('renders children when authenticated', () => {
    render(
      <Wrapper>
        <ProtectedRoute>
          <div>Protected Content</div>
        </ProtectedRoute>
      </Wrapper>
    );
    expect(screen.getByText('Protected Content')).toBeInTheDocument();
  });

  it('redirects when required role not satisfied', () => {
    vi.mocked(AuthContextModule.useAuthContext).mockReturnValue({
      user: { id: '1', email: 'staff@test.com', name: 'Staff User', roles: ['staff'], tenantId: 'tenant-1' },
      isAuthenticated: true,
      isLoading: false,
      token: 'mock-token',
      login: vi.fn(),
      logout: vi.fn(),
      hasRole: () => false,
      hasAnyRole: () => false,
    });

    render(
      <Wrapper>
        <ProtectedRoute requiredRoles={['admin']}>
          <div>Admin Only</div>
        </ProtectedRoute>
      </Wrapper>
    );
    expect(screen.queryByText('Admin Only')).not.toBeInTheDocument();
  });
});

// ─── Dashboard ────────────────────────────────────────────────────────────────

describe('Dashboard', () => {
  beforeEach(() => {
    vi.mocked(AuthContextModule.useAuthContext).mockReturnValue({
      user: { id: '1', email: 'admin@test.com', name: 'Admin User', roles: ['admin'], tenantId: 'tenant-1', tenantName: 'Acme Corp' },
      isAuthenticated: true,
      isLoading: false,
      token: 'mock-token',
      login: vi.fn(),
      logout: vi.fn(),
      hasRole: (role: string) => role === 'admin',
      hasAnyRole: (roles: string[]) => roles.includes('admin'),
    });
  });

  it('renders welcome message', async () => {
    render(<Wrapper><Dashboard /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText(/Welcome back/i)).toBeInTheDocument();
    });
  });

  it('renders stat cards', async () => {
    render(<Wrapper><Dashboard /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText('Revenue This Month')).toBeInTheDocument();
      expect(screen.getByText('Pending Orders')).toBeInTheDocument();
      expect(screen.getByText('Low Stock Items')).toBeInTheDocument();
      expect(screen.getByText('Inventory Value')).toBeInTheDocument();
    });
  });

  it('renders recent orders table heading', async () => {
    render(<Wrapper><Dashboard /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText('Recent Orders')).toBeInTheDocument();
    });
  });

  it('renders quick action links', async () => {
    render(<Wrapper><Dashboard /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText('Add Product')).toBeInTheDocument();
      expect(screen.getByText('Update Stock')).toBeInTheDocument();
    });
  });
});

// ─── Users Page ───────────────────────────────────────────────────────────────

describe('Users page', () => {
  it('renders users table after loading', async () => {
    render(<Wrapper><Users /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText('Alice Johnson')).toBeInTheDocument();
      expect(screen.getByText('Bob Smith')).toBeInTheDocument();
    });
  });

  it('renders Add User button for admin', async () => {
    render(<Wrapper><Users /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText('Add User')).toBeInTheDocument();
    });
  });

  it('opens modal on Add User click', async () => {
    const user = userEvent.setup();
    render(<Wrapper><Users /></Wrapper>);
    await waitFor(() => screen.getByText('Add User'));
    await user.click(screen.getByText('Add User'));
    expect(screen.getByText('New User')).toBeInTheDocument();
  });
});

// ─── Products Page ────────────────────────────────────────────────────────────

describe('Products page', () => {
  it('renders products after loading', async () => {
    render(<Wrapper><Products /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText('Widget A')).toBeInTheDocument();
      expect(screen.getByText('Widget B')).toBeInTheDocument();
    });
  });

  it('shows Add Product button for admin/manager', async () => {
    render(<Wrapper><Products /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText('Add Product')).toBeInTheDocument();
    });
  });
});

// ─── Inventory Page ───────────────────────────────────────────────────────────

describe('Inventory page', () => {
  it('renders page heading', () => {
    render(<Wrapper><Inventory /></Wrapper>);
    expect(screen.getByText('Inventory')).toBeInTheDocument();
  });

  it('renders tab navigation', () => {
    render(<Wrapper><Inventory /></Wrapper>);
    expect(screen.getByText('Current Stock')).toBeInTheDocument();
    expect(screen.getByText('Transactions')).toBeInTheDocument();
  });

  it('renders inventory items after loading', async () => {
    render(<Wrapper><Inventory /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText('Widget A')).toBeInTheDocument();
    });
  });

  it('switches to transactions tab', async () => {
    const user = userEvent.setup();
    render(<Wrapper><Inventory /></Wrapper>);
    await user.click(screen.getByText('Transactions'));
    await waitFor(() => {
      expect(screen.getByText('No transactions found.')).toBeInTheDocument();
    });
  });
});

// ─── Orders Page ─────────────────────────────────────────────────────────────

describe('Orders page', () => {
  it('renders orders after loading', async () => {
    render(<Wrapper><Orders /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText('ORD-0001')).toBeInTheDocument();
      expect(screen.getByText('Carol White')).toBeInTheDocument();
    });
  });

  it('shows New Order button for admin/manager', async () => {
    render(<Wrapper><Orders /></Wrapper>);
    await waitFor(() => {
      expect(screen.getByText('New Order')).toBeInTheDocument();
    });
  });

  it('opens new order modal', async () => {
    const user = userEvent.setup();
    render(<Wrapper><Orders /></Wrapper>);
    await waitFor(() => screen.getByText('New Order'));
    await user.click(screen.getByText('New Order'));
    expect(screen.getByText('New Order', { selector: 'h2' })).toBeInTheDocument();
  });
});

// ─── Layout ───────────────────────────────────────────────────────────────────

describe('Layout', () => {
  it('renders navigation links', () => {
    render(
      <Wrapper>
        <Layout>
          <div>Content</div>
        </Layout>
      </Wrapper>
    );
    expect(screen.getByText('Dashboard')).toBeInTheDocument();
    expect(screen.getByText('Products')).toBeInTheDocument();
    expect(screen.getByText('Inventory')).toBeInTheDocument();
    expect(screen.getByText('Orders')).toBeInTheDocument();
  });

  it('renders user name in sidebar', () => {
    render(
      <Wrapper>
        <Layout>
          <div>Content</div>
        </Layout>
      </Wrapper>
    );
    expect(screen.getByText('Admin User')).toBeInTheDocument();
  });

  it('renders sign out button', () => {
    render(
      <Wrapper>
        <Layout>
          <div>Content</div>
        </Layout>
      </Wrapper>
    );
    expect(screen.getByText('Sign out')).toBeInTheDocument();
  });

  it('renders children content', () => {
    render(
      <Wrapper>
        <Layout>
          <div>My Page Content</div>
        </Layout>
      </Wrapper>
    );
    expect(screen.getByText('My Page Content')).toBeInTheDocument();
  });
});
