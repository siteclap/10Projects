import { type ReactNode, type HTMLAttributes } from 'react';
import { cn } from '@/lib/utils/cn';

const variantStyles = {
  default: 'bg-white',
  alt: 'bg-section-alt',
  white: 'bg-white',
} as const;

type SectionVariant = keyof typeof variantStyles;

export interface SectionProps extends HTMLAttributes<HTMLElement> {
  variant?: SectionVariant;
  children: ReactNode;
}

export function Section({
  variant = 'default',
  className,
  children,
  id,
  ...props
}: SectionProps) {
  return (
    <section
      id={id}
      className={cn('py-16', variantStyles[variant], className)}
      {...props}
    >
      {children}
    </section>
  );
}
