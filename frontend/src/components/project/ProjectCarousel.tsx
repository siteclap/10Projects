import { type ReactNode } from 'react';
import Link from 'next/link';
import { cn } from '@/lib/utils/cn';

interface ProjectCarouselProps {
  title: string;
  subtitle?: string;
  seeAllHref?: string;
  seeAllLabel?: string;
  children: ReactNode;
  className?: string;
}

export function ProjectCarousel({
  title,
  subtitle,
  seeAllHref,
  seeAllLabel = 'See All',
  children,
  className,
}: ProjectCarouselProps) {
  return (
    <div className={cn('py-3xl', className)}>
      {/* Section header */}
      <div className="mx-auto mb-xl flex max-w-container items-end justify-between px-lg md:px-2xl">
        <div>
          <h2 className="text-h2 text-gray-900">{title}</h2>
          {subtitle && (
            <p className="mt-xs text-base text-gray-500">{subtitle}</p>
          )}
        </div>
        {seeAllHref && (
          <Link
            href={seeAllHref}
            className="hidden items-center gap-xs text-sm font-medium text-brand-primary no-underline transition-colors hover:text-brand-primary-dark hover:no-underline md:flex"
          >
            {seeAllLabel}
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              aria-hidden="true"
            >
              <path d="m9 18 6-6-6-6" />
            </svg>
          </Link>
        )}
      </div>

      {/* Scrollable row */}
      <div className="relative">
        <div
          className="scrollbar-hide flex snap-x snap-mandatory gap-lg overflow-x-auto px-lg md:px-2xl"
          style={{
            scrollbarWidth: 'none',
            msOverflowStyle: 'none',
          }}
        >
          <div className="mx-auto flex max-w-container gap-lg">
            {children}
          </div>
        </div>
      </div>

      {/* Mobile see all link */}
      {seeAllHref && (
        <div className="mt-lg px-lg md:hidden">
          <Link
            href={seeAllHref}
            className="flex items-center justify-center gap-xs text-sm font-medium text-brand-primary no-underline hover:text-brand-primary-dark hover:no-underline"
          >
            {seeAllLabel}
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              aria-hidden="true"
            >
              <path d="m9 18 6-6-6-6" />
            </svg>
          </Link>
        </div>
      )}
    </div>
  );
}
