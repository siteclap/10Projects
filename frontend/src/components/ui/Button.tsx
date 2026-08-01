'use client';

import { forwardRef, type ButtonHTMLAttributes, type ReactNode } from 'react';
import { Slot } from './Slot';
import { cn } from '@/lib/utils/cn';

const variantStyles = {
  primary:
    'bg-brand-primary text-white hover:bg-brand-primary-hover focus-visible:ring-brand-primary',
  secondary:
    'bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 focus-visible:ring-brand-primary',
  ghost:
    'bg-transparent text-gray-700 hover:bg-gray-100 focus-visible:ring-brand-primary',
  whatsapp:
    'bg-whatsapp text-white hover:bg-whatsapp-hover focus-visible:ring-whatsapp',
  danger:
    'bg-danger text-white hover:bg-danger-hover focus-visible:ring-danger',
} as const;

const sizeStyles = {
  sm: 'h-8 px-3 text-sm gap-1.5',
  md: 'h-10 px-4 text-sm gap-2',
  lg: 'h-12 px-6 text-base gap-2.5',
} as const;

type ButtonVariant = keyof typeof variantStyles;
type ButtonSize = keyof typeof sizeStyles;

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
  size?: ButtonSize;
  loading?: boolean;
  asChild?: boolean;
  children: ReactNode;
}

function Spinner({ className }: { className?: string }) {
  return (
    <svg
      className={cn('animate-spin', className)}
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      width="16"
      height="16"
      aria-hidden="true"
    >
      <circle
        className="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        strokeWidth="4"
      />
      <path
        className="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>
  );
}

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  function Button(
    {
      variant = 'primary',
      size = 'md',
      loading = false,
      disabled = false,
      asChild = false,
      className,
      children,
      ...props
    },
    ref
  ) {
    const isDisabled = disabled || loading;

    const classes = cn(
      'inline-flex items-center justify-center font-medium rounded-[var(--radius-sm)] transition-colors duration-150',
      'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
      'disabled:opacity-50 disabled:pointer-events-none',
      'cursor-pointer',
      variantStyles[variant],
      sizeStyles[size],
      isDisabled && 'opacity-50 pointer-events-none',
      className
    );

    if (asChild) {
      return (
        <Slot ref={ref} className={classes} {...props}>
          {loading && <Spinner />}
          {children}
        </Slot>
      );
    }

    return (
      <button
        ref={ref}
        className={classes}
        disabled={isDisabled}
        {...props}
      >
        {loading && <Spinner />}
        {children}
      </button>
    );
  }
);
