'use client';

import { type ReactNode, type HTMLAttributes } from 'react';
import { cn } from '@/lib/utils/cn';

export interface CardProps extends HTMLAttributes<HTMLDivElement> {
  hover?: boolean;
  children: ReactNode;
}

export function Card({
  hover = false,
  className,
  children,
  onClick,
  ...props
}: CardProps) {
  return (
    <div
      className={cn(
        'bg-white border border-gray-200 rounded-[var(--radius-md)] shadow-[var(--shadow-card)]',
        hover && 'transition-shadow duration-200 hover:shadow-[var(--shadow-hover)]',
        onClick && 'cursor-pointer',
        className
      )}
      onClick={onClick}
      {...props}
    >
      {children}
    </div>
  );
}
