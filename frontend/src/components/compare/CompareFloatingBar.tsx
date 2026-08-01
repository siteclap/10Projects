'use client';

import Link from 'next/link';
import { cn } from '@/lib/utils/cn';
import { useCompare } from '@/lib/hooks/use-compare';
import { ROUTES } from '@/lib/constants/routes';

export function CompareFloatingBar() {
  const { ids, clear } = useCompare();
  const count = ids.length;
  const isVisible = count >= 2;

  return (
    <div
      className={cn(
        'fixed bottom-0 left-0 right-0 z-50 transition-transform duration-300',
        isVisible ? 'translate-y-0' : 'translate-y-full'
      )}
    >
      <div className="border-t border-gray-200 bg-white shadow-sticky">
        <div className="mx-auto flex max-w-container items-center justify-between px-lg py-md md:px-2xl">
          <div className="flex items-center gap-md">
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-brand-primary"
              aria-hidden="true"
            >
              <line x1="18" x2="18" y1="20" y2="10" />
              <line x1="12" x2="12" y1="20" y2="4" />
              <line x1="6" x2="6" y1="20" y2="14" />
            </svg>
            <span className="text-sm font-medium text-gray-700">
              {count} project{count !== 1 ? 's' : ''} selected
            </span>
          </div>

          <div className="flex items-center gap-md">
            <button
              type="button"
              onClick={clear}
              className="text-sm font-medium text-gray-500 transition-colors hover:text-gray-700"
            >
              Clear
            </button>
            <Link
              href={ROUTES.COMPARE}
              className="inline-flex h-[40px] items-center justify-center rounded-sm bg-brand-primary px-xl text-sm font-medium text-white no-underline transition-colors hover:bg-brand-primary-dark hover:no-underline"
            >
              Compare Now
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
