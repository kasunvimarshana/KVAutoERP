import React from 'react';

interface LoadingSpinnerProps {
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

const sizeMap = { sm: 'h-4 w-4', md: 'h-8 w-8', lg: 'h-12 w-12' };

const LoadingSpinner: React.FC<LoadingSpinnerProps> = ({ size = 'md', className = '' }) => (
  <div
    className={`animate-spin rounded-full border-2 border-gray-300 border-t-blue-600 ${sizeMap[size]} ${className}`}
    role="status"
    aria-label="Loading"
  />
);

export default LoadingSpinner;
