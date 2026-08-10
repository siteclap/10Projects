'use client';

import { cn } from '@/lib/utils/cn';

const statusColorMap: Record<string, string> = {
  NEW: 'bg-blue-100 text-blue-800',
  CONTACTED: 'bg-amber-100 text-amber-800',
  QUALIFIED: 'bg-green-100 text-green-800',
  SITE_VISIT: 'bg-purple-100 text-purple-800',
  CONVERTED: 'bg-emerald-100 text-emerald-800',
  LOST: 'bg-red-100 text-red-800',
  DRAFT: 'bg-gray-100 text-gray-600',
  PUBLISHED: 'bg-green-100 text-green-800',
};

export interface StatusBadgeProps {
  status: string;
  className?: string;
}

export function StatusBadge({ status, className }: StatusBadgeProps) {
  const safeStatus = status || 'DRAFT';
  const colors = statusColorMap[safeStatus.toUpperCase()] || 'bg-gray-100 text-gray-600';
  const label = safeStatus.replace(/_/g, ' ');

  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full px-sm py-xs text-caption font-medium',
        colors,
        className
      )}
    >
      {label}
    </span>
  );
}
