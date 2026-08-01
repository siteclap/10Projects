import { cn } from '@/lib/utils/cn';

const variantStyles = {
  text: 'rounded-[var(--radius-sm)] h-4 w-full',
  circular: 'rounded-full',
  rectangular: 'rounded-[var(--radius-md)]',
} as const;

type SkeletonVariant = keyof typeof variantStyles;

export interface SkeletonProps {
  variant?: SkeletonVariant;
  className?: string;
}

export function Skeleton({ variant = 'text', className }: SkeletonProps) {
  return (
    <div
      role="status"
      aria-label="Loading"
      className={cn(
        'animate-pulse bg-gray-200',
        variantStyles[variant],
        className
      )}
    />
  );
}
