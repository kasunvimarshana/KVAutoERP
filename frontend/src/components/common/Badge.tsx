import React from 'react';

type BadgeVariant = 'success' | 'warning' | 'danger' | 'info' | 'default' | 'purple';

interface BadgeProps {
  variant?: BadgeVariant;
  children: React.ReactNode;
  className?: string;
}

const variantClasses: Record<BadgeVariant, string> = {
  success: 'bg-green-100 text-green-800',
  warning: 'bg-yellow-100 text-yellow-800',
  danger: 'bg-red-100 text-red-800',
  info: 'bg-blue-100 text-blue-800',
  default: 'bg-gray-100 text-gray-800',
  purple: 'bg-purple-100 text-purple-800',
};

const Badge: React.FC<BadgeProps> = ({ variant = 'default', children, className = '' }) => (
  <span
    className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${variantClasses[variant]} ${className}`}
  >
    {children}
  </span>
);

export default Badge;
