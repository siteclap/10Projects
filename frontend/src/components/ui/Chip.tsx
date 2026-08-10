'use client';

import { type ReactNode, type ButtonHTMLAttributes } from 'react';
import { cn } from '@/lib/utils/cn';

export interface ChipProps
  extends Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'onClick'> {
  selected?: boolean;
  onClick?: () => void;
  disabled?: boolean;
  className?: string;
  children: ReactNode;
}

export function Chip({
  selected = false,
  onClick,
  disabled = false,
  className,
  children,
  ...props
}: ChipProps) {
  return (
    <button
      type="button"
      role="option"
      aria-selected={selected}
      disabled={disabled}
      onClick={onClick}
      className={cn(
        'inline-flex items-center justify-center rounded-full px-lg py-sm text-sm font-medium',
        'transition-all duration-150 cursor-pointer',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2',
        'disabled:opacity-50 disabled:pointer-events-none',
        selected
          ? 'bg-brand-primary text-white shadow-sm hover:bg-brand-primary-dark'
          : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
        className
      )}
      {...props}
    >
      {children}
    </button>
  );
}
