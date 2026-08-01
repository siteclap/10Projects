import Link from 'next/link';
import { cn } from '@/lib/utils/cn';
import { Container } from '@/components/layout/Container';
import { LocationCard } from './LocationCard';
import type { LocationCard as LocationCardType } from '@/lib/types/location';

interface LocationGridProps {
  locations: LocationCardType[];
  title?: string;
  subtitle?: string;
  seeAllHref?: string;
  className?: string;
}

export function LocationGrid({
  locations,
  title,
  subtitle,
  seeAllHref,
  className,
}: LocationGridProps) {
  return (
    <div className={cn('py-3xl', className)}>
      <Container>
        {title && (
          <div className="mb-xl flex items-end justify-between">
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
                View All Locations
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
        )}

        <div className="grid grid-cols-1 gap-lg sm:grid-cols-2 lg:grid-cols-4">
          {locations.map((location) => (
            <LocationCard key={location.id} location={location} />
          ))}
        </div>
      </Container>
    </div>
  );
}
