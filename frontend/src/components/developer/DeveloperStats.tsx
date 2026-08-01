import { Badge } from '@/components/ui/Badge';
import type { Developer } from '@/lib/types/developer';

interface DeveloperStatsProps {
  developer: Developer;
}

function getStabilityVariant(
  stability: Developer['financial_stability']
): 'success' | 'warning' | 'danger' | 'default' {
  switch (stability) {
    case 'strong':
      return 'success';
    case 'moderate':
      return 'warning';
    case 'weak':
      return 'danger';
    default:
      return 'default';
  }
}

function getStabilityLabel(stability: Developer['financial_stability']): string {
  switch (stability) {
    case 'strong':
      return 'Strong';
    case 'moderate':
      return 'Moderate';
    case 'weak':
      return 'Weak';
    default:
      return 'N/A';
  }
}

export function DeveloperStats({ developer }: DeveloperStatsProps) {
  const stabilityVariant = getStabilityVariant(developer.financial_stability);
  const stabilityLabel = getStabilityLabel(developer.financial_stability);
  const avgDelay = developer.avg_delivery_delay_months;
  const deliveryLabel =
    avgDelay <= 0
      ? 'On Time'
      : avgDelay <= 3
        ? `Avg ${avgDelay}mo delay`
        : `Avg ${avgDelay}mo delay`;
  const deliveryColor =
    avgDelay <= 0
      ? 'text-success'
      : avgDelay <= 6
        ? 'text-accent-dark'
        : 'text-danger';

  return (
    <div className="flex flex-col gap-3xl">
      {/* Primary stats grid */}
      <div className="grid grid-cols-2 gap-lg sm:grid-cols-4">
        <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
          <p className="text-caption uppercase tracking-wider text-gray-500">
            Completed Projects
          </p>
          <p className="mt-xs text-h3 font-bold text-gray-900">
            {developer.total_projects_completed}
          </p>
        </div>
        <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
          <p className="text-caption uppercase tracking-wider text-gray-500">
            Ongoing Projects
          </p>
          <p className="mt-xs text-h3 font-bold text-gray-900">
            {developer.total_projects_ongoing}
          </p>
        </div>
        <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
          <p className="text-caption uppercase tracking-wider text-gray-500">
            Total Area Developed
          </p>
          <p className="mt-xs text-h3 font-bold text-gray-900">
            {(developer.total_area_developed_sqft / 1_000_000).toFixed(1)}M sq ft
          </p>
        </div>
        <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
          <p className="text-caption uppercase tracking-wider text-gray-500">
            Customer Rating
          </p>
          <p className="mt-xs text-h3 font-bold text-accent-dark">
            {developer.customer_rating}/5
          </p>
        </div>
      </div>

      {/* Secondary stats */}
      <div className="grid grid-cols-1 gap-lg sm:grid-cols-2 lg:grid-cols-4">
        {/* Delivery Track Record */}
        <div className="flex items-start gap-md rounded-sm border border-gray-100 bg-white p-lg">
          <div className="flex h-[40px] w-[40px] shrink-0 items-center justify-center rounded-sm bg-brand-primary-pale">
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
              <circle cx="12" cy="12" r="10" />
              <polyline points="12 6 12 12 16 14" />
            </svg>
          </div>
          <div>
            <p className="text-caption uppercase tracking-wider text-gray-500">
              Delivery Track Record
            </p>
            <p className={`mt-xs text-base font-semibold ${deliveryColor}`}>
              {deliveryLabel}
            </p>
          </div>
        </div>

        {/* RERA Compliance */}
        <div className="flex items-start gap-md rounded-sm border border-gray-100 bg-white p-lg">
          <div className="flex h-[40px] w-[40px] shrink-0 items-center justify-center rounded-sm bg-success-light">
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-success"
              aria-hidden="true"
            >
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
              <path d="m9 12 2 2 4-4" />
            </svg>
          </div>
          <div>
            <p className="text-caption uppercase tracking-wider text-gray-500">
              RERA Compliance
            </p>
            <p className="mt-xs text-base font-semibold text-success">
              {developer.rera_compliance_rate}%
            </p>
          </div>
        </div>

        {/* Financial Stability */}
        <div className="flex items-start gap-md rounded-sm border border-gray-100 bg-white p-lg">
          <div className="flex h-[40px] w-[40px] shrink-0 items-center justify-center rounded-sm bg-accent-pale">
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-accent-dark"
              aria-hidden="true"
            >
              <line x1="12" x2="12" y1="2" y2="22" />
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
          </div>
          <div>
            <p className="text-caption uppercase tracking-wider text-gray-500">
              Financial Stability
            </p>
            <div className="mt-xs">
              <Badge variant={stabilityVariant} size="sm">
                {stabilityLabel}
              </Badge>
            </div>
          </div>
        </div>

        {/* Legal Cases */}
        <div className="flex items-start gap-md rounded-sm border border-gray-100 bg-white p-lg">
          <div className="flex h-[40px] w-[40px] shrink-0 items-center justify-center rounded-sm bg-gray-100">
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-gray-500"
              aria-hidden="true"
            >
              <path d="m17 14 3 3.3a1 1 0 0 1-.7 1.7H4.7a1 1 0 0 1-.7-1.7L7 14" />
              <path d="M12 2v10" />
              <path d="m8 6 4-4 4 4" />
            </svg>
          </div>
          <div>
            <p className="text-caption uppercase tracking-wider text-gray-500">
              Legal Cases Pending
            </p>
            <p className={`mt-xs text-base font-semibold ${developer.legal_cases_pending === 0 ? 'text-success' : 'text-gray-900'}`}>
              {developer.legal_cases_pending === 0
                ? 'None'
                : developer.legal_cases_pending}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
