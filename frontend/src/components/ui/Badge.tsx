import { type ReactNode } from 'react';
import { cn } from '@/lib/utils/cn';

const variantStyles = {
  default: 'bg-gray-100 text-gray-700 border-gray-200',
  primary: 'bg-brand-primary-light text-brand-primary border-brand-primary/20',
  accent: 'bg-accent-light text-amber-800 border-amber-200',
  success: 'bg-success-light text-green-800 border-green-200',
  warning: 'bg-warning-light text-amber-800 border-amber-200',
  danger: 'bg-danger-light text-red-800 border-red-200',
  outline: 'bg-transparent text-gray-700 border-gray-300',
} as const;

const sizeStyles = {
  sm: 'px-2 py-0.5 text-xs',
  md: 'px-2.5 py-1 text-sm',
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
