import Link from 'next/link';
import { cn } from '@/lib/utils/cn';
import { Badge } from '@/components/ui/Badge';
import type { DeveloperCard as DeveloperCardType } from '@/lib/types/developer';

interface DeveloperCardProps {
  developer: DeveloperCardType;
  className?: string;
}

function getTierLabel(tier: string): string {
  switch (tier) {
    case 'tier_1':
      return 'Tier 1';
    case 'tier_2':
      return 'Tier 2';
    case 'tier_3':
      return 'Tier 3';
    default:
      return '';
  }
}

function getTierVariant(tier: string): 'accent' | 'primary' | 'default' {
  switch (tier) {
    case 'tier_1':
      return 'accent';
    case 'tier_2':
      return 'primary';
    default:
      return 'default';
  }
}

function renderStars(rating: number) {
  const fullStars = Math.floor(rating);
  const hasHalf = rating - fullStars >= 0.3;
  const emptyStars = 5 - fullStars - (hasHalf ? 1 : 0);

  return (
    <div className="flex items-center gap-xs" aria-label={`Rating: ${rating} out of 5`}>
      {Array.from({ length: fullStars }).map((_, i) => (
        <svg
          key={`full-${i}`}
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="currentColor"
          className="text-accent"
          aria-hidden="true"
        >
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
        </svg>
      ))}
      {hasHalf && (
        <svg
          width="16"
          height="16"
          viewBox="0 0 24 24"
          className="text-accent"
          aria-hidden="true"
        >
          <defs>
            <linearGradient id={`half-star`}>
              <stop offset="50%" stopColor="currentColor" />
              <stop offset="50%" stopColor="#E5E7EB" />
            </linearGradient>
          </defs>
          <path
            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
            fill="url(#half-star)"
          />
        </svg>
      )}
      {Array.from({ length: emptyStars }).map((_, i) => (
        <svg
          key={`empty-${i}`}
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="#E5E7EB"
          aria-hidden="true"
        >
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
        </svg>
      ))}
      <span className="ml-xs text-sm font-medium text-gray-700">
        {rating.toFixed(1)}
      </span>
    </div>
  );
}

export function DeveloperCard({ developer, className }: DeveloperCardProps) {
  const tierLabel = getTierLabel(developer.tier);
  const tierVariant = getTierVariant(developer.tier);
  const initials = developer.title
    .split(' ')
    .map((w) => w[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();

  return (
    <Link
      href={`/developers/${developer.slug}`}
      className={cn(
        'group flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white shadow-card transition-shadow duration-200 no-underline hover:shadow-hover hover:no-underline',
        className
      )}
    >
      <div className="flex flex-col items-center gap-md p-xl">
        {/* Logo / Initials */}
        <div className="flex h-[64px] w-[64px] shrink-0 items-center justify-center rounded-md bg-gray-100">
          {developer.logo ? (
            <img
              src={developer.logo}
              alt={developer.title}
              className="h-[48px] max-w-full object-contain"
              loading="lazy"
            />
          ) : (
            <span className="text-h4 font-bold text-gray-500">{initials}</span>
          )}
        </div>

        {/* Name */}
        <h3 className="text-center text-h4 text-gray-900 group-hover:text-brand-primary">
          {developer.title}
        </h3>

        {/* Tier badge */}
        {tierLabel && (
          <Badge variant={tierVariant} size="sm">
            {tierLabel}
          </Badge>
        )}

        {/* Rating */}
        {renderStars(developer.customer_rating)}

        {/* Completed projects */}
        <p className="text-sm text-gray-500">
          {developer.total_projects_completed} projects completed
        </p>
      </div>
    </Link>
  );
}
