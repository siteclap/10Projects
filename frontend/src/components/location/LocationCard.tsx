import Link from 'next/link';
import { cn } from '@/lib/utils/cn';
import type { LocationCard as LocationCardType } from '@/lib/types/location';

interface LocationCardProps {
  location: LocationCardType;
  className?: string;
}

export function LocationCard({ location, className }: LocationCardProps) {
  const href = `/navi-mumbai/${location.slug}`;

  return (
    <Link
      href={href}
      className={cn(
        'group flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white shadow-card transition-shadow duration-200 no-underline hover:shadow-hover hover:no-underline',
        className
      )}
    >
      {/* Image placeholder */}
      <div className="relative h-[160px] w-full bg-gray-200">
        {location.thumbnail ? (
          <img
            src={location.thumbnail}
            alt={location.title}
            className="h-full w-full object-cover"
            loading="lazy"
          />
        ) : (
          <div className="flex h-full w-full items-center justify-center bg-gray-100 text-gray-300">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
          </div>
        )}
      </div>

      {/* Body */}
      <div className="flex flex-col gap-xs p-lg">
        <h3 className="text-h4 text-gray-900 group-hover:text-brand-primary">
          {location.title}
        </h3>

        <p className="text-sm text-gray-500">
          {location.project_count} projects &bull;{' '}
          {'\u20B9'}
          {location.avg_price_psf.toLocaleString('en-IN')}/sq ft avg
        </p>

        {location.livability_score > 0 && (
          <p className="text-sm font-medium text-success">
            Livability Score: {location.livability_score}/100
          </p>
        )}
      </div>
    </Link>
  );
}
