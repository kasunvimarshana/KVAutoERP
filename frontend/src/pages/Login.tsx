import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Building2, Lock, Mail, AlertCircle } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { LoginCredentials } from '../types';
import LoadingSpinner from '../components/common/LoadingSpinner';

const Login: React.FC = () => {
  const { login, isAuthenticated, isLoading } = useAuth();
  const navigate = useNavigate();

  const [form, setForm] = useState<LoginCredentials>({ email: '', password: '', tenantId: '' });
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!isLoading && isAuthenticated) navigate('/', { replace: true });
  }, [isAuthenticated, isLoading, navigate]);

  const handleChange = (field: keyof LoginCredentials, value: string) =>
    setForm((prev) => ({ ...prev, [field]: value }));

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (!form.email || !form.password || !form.tenantId) {
      setError('All fields are required.');
      return;
    }

    setSubmitting(true);
    try {
      await login(form);
      navigate('/', { replace: true });
    } catch (err: any) {
      const message =
        err?.response?.data?.message || 'Invalid credentials. Please try again.';
      setError(message);
    } finally {
      setSubmitting(false);
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-screen bg-gray-50">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-blue-950 to-gray-900 flex items-center justify-center px-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-blue-600 mb-4">
            <Building2 className="h-8 w-8 text-white" />
          </div>
          <h1 className="text-3xl font-bold text-white">Inventory Manager</h1>
          <p className="text-gray-400 mt-2">Multi-Tenant SaaS Platform</p>
        </div>

        <div className="bg-white rounded-2xl shadow-2xl p-8">
          <h2 className="text-xl font-semibold text-gray-900 mb-6">Sign in to your account</h2>

          {error && (
            <div className="flex items-center gap-2 bg-red-50 text-red-700 border border-red-200 rounded-lg p-3 mb-5 text-sm">
              <AlertCircle className="h-4 w-4 flex-shrink-0" />
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Tenant ID</label>
              <div className="relative">
                <Building2 className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  type="text"
                  value={form.tenantId}
                  onChange={(e) => handleChange('tenantId', e.target.value)}
                  placeholder="your-tenant-id"
                  className="input-field pl-9"
                  autoComplete="organization"
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
              <div className="relative">
                <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  type="email"
                  value={form.email}
                  onChange={(e) => handleChange('email', e.target.value)}
                  placeholder="you@example.com"
                  className="input-field pl-9"
                  autoComplete="email"
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  type="password"
                  value={form.password}
                  onChange={(e) => handleChange('password', e.target.value)}
                  placeholder="\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022"
                  className="input-field pl-9"
                  autoComplete="current-password"
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={submitting}
              className="w-full btn-primary justify-center py-2.5 mt-2"
            >
              {submitting ? <LoadingSpinner size="sm" /> : null}
              {submitting ? 'Signing in\u2026' : 'Sign In'}
            </button>
          </form>
        </div>

        <p className="text-center text-gray-500 text-sm mt-6">
          \u00a9 {new Date().getFullYear()} Inventory Management System
        </p>
      </div>
    </div>
  );
};

export default Login;
