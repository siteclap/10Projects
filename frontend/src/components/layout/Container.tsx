import { type ReactNode } from 'react';
import { cn } from '@/lib/utils/cn';

const sizeStyles = {
  default: 'max-w-[var(--container-max)]',
  narrow: 'max-w-[var(--container-narrow)]',
  assessment: 'max-w-[var(--container-assessment)]',
} as const;

type ContainerSize = keyof typeof sizeStyles;

export interface ContainerProps {
  size?: ContainerSize;
  className?: string;
  children: ReactNode;
}

export function Container({
  size = 'default',
  className,
  children,
}: ContainerProps) {
  return (
    <div
      className={cn(
        'mx-auto w-full px-4 md:px-8',
        sizeStyles[size],
        className
      )}
    >
      {children}
    </div>
  );
}
