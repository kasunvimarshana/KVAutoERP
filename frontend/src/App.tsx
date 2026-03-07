import React from 'react';
import { BrowserRouter, Routes, Route, Navigate, NavLink } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import {
  LayoutDashboard,
  Package,
  ShoppingCart,
  LogOut,
  Building2,
  ChevronRight,
} from 'lucide-react';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { TenantProvider, useTenant } from './contexts/TenantContext';
import Dashboard from './pages/Dashboard';
import Inventory from './pages/Inventory';
import Orders from './pages/Orders';
import Login from './pages/Login';
import LoadingSpinner from './components/common/LoadingSpinner';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,
      retry: 2,
      refetchOnWindowFocus: false,
    },
  },
});

const ProtectedRoute: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { isAuthenticated, isLoading } = useAuth();
  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-screen bg-gray-50">
        <LoadingSpinner size="lg" />
      </div>
    );
  }
  if (!isAuthenticated) return <Navigate to="/login" replace />;
  return <>{children}</>;
};

const navItems = [
  { to: '/', icon: LayoutDashboard, label: 'Dashboard', end: true },
  { to: '/inventory', icon: Package, label: 'Inventory', end: false },
  { to: '/orders', icon: ShoppingCart, label: 'Orders', end: false },
];

const AppLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user, logout } = useAuth();
  const { tenant } = useTenant();

  return (
    <div className="flex h-screen bg-gray-100 overflow-hidden">
      <nav className="w-64 bg-gray-900 text-white flex flex-col flex-shrink-0">
        <div className="p-5 border-b border-gray-800">
          <div className="flex items-center gap-3">
            <div className="h-9 w-9 rounded-lg bg-blue-600 flex items-center justify-center">
              <Building2 className="h-5 w-5 text-white" />
            </div>
            <div className="min-w-0">
              <p className="font-semibold text-sm truncate">{tenant?.name || 'Inventory System'}</p>
              <p className="text-xs text-gray-400 capitalize">{tenant?.plan || 'Loading\u2026'}</p>
            </div>
          </div>
        </div>

        <div className="flex-1 py-4 px-3 space-y-1">
          {navItems.map(({ to, icon: Icon, label, end }) => (
            <NavLink
              key={to}
              to={to}
              end={end}
              className={({ isActive }) =>
                `flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                  isActive
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                }`
              }
            >
              {({ isActive }) => (
                <>
                  <Icon className="h-4 w-4 flex-shrink-0" />
                  <span className="flex-1">{label}</span>
                  {isActive && <ChevronRight className="h-3 w-3" />}
                </>
              )}
            </NavLink>
          ))}
        </div>

        <div className="p-4 border-t border-gray-800">
          <div className="flex items-center gap-3 mb-3">
            <div className="h-8 w-8 rounded-full bg-gray-700 flex items-center justify-center text-xs font-semibold">
              {user?.name?.charAt(0).toUpperCase() || 'U'}
            </div>
            <div className="min-w-0 flex-1">
              <p className="text-sm font-medium truncate">{user?.name || 'User'}</p>
              <p className="text-xs text-gray-400 truncate">{user?.email}</p>
            </div>
          </div>
          <button
            onClick={logout}
            className="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-400 hover:bg-gray-800 hover:text-white transition-colors"
          >
            <LogOut className="h-4 w-4" />
            Sign out
          </button>
        </div>
      </nav>

      <main className="flex-1 overflow-auto">
        {children}
      </main>
    </div>
  );
};

const App: React.FC = () => (
  <QueryClientProvider client={queryClient}>
    <AuthProvider>
      <TenantProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route
              path="/"
              element={
                <ProtectedRoute>
                  <AppLayout>
                    <Dashboard />
                  </AppLayout>
                </ProtectedRoute>
              }
            />
            <Route
              path="/inventory"
              element={
                <ProtectedRoute>
                  <AppLayout>
                    <Inventory />
                  </AppLayout>
                </ProtectedRoute>
              }
            />
            <Route
              path="/orders"
              element={
                <ProtectedRoute>
                  <AppLayout>
                    <Orders />
                  </AppLayout>
                </ProtectedRoute>
              }
            />
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </BrowserRouter>
      </TenantProvider>
    </AuthProvider>
  </QueryClientProvider>
);

export default App;
