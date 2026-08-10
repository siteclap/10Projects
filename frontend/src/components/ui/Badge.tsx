import { type ReactNode } from 'react';
import { cn } from '@/lib/utils/cn';

const variantStyles = {
  default: 'bg-gray-50 text-gray-600 border-gray-200',
  primary: 'bg-brand-primary-bg text-brand-primary-dark border-brand-primary-pale',
  accent: 'bg-accent-pale text-gray-700 border-accent-pale',
  success: 'bg-success-bg text-gray-700 border-success-light',
  warning: 'bg-warning-bg text-gray-700 border-warning-bg',
  danger: 'bg-danger-light text-gray-700 border-danger-light',
  outline: 'bg-transparent text-gray-600 border-gray-200',
} as const;

const sizeStyles = {
  sm: 'px-sm py-xs text-caption',
  md: 'px-md py-xs text-sm',
} as const;

type BadgeVariant = keyof typeof variantStyles;
type BadgeSize = keyof typeof sizeStyles;

export interface BadgeProps {
  variant?: BadgeVariant;
  size?: BadgeSize;
  className?: string;
  children: ReactNode;
}

export function Badge({
  variant = 'default',
  size = 'md',
  className,
  children,
}: BadgeProps) {
  return (
    <span
      className={cn(
        'inline-flex items-center font-medium border rounded-full whitespace-nowrap',
        variantStyles[variant],
        sizeStyles[size],
        className
      )}
    >
      {children}
    </span>
  );
}
