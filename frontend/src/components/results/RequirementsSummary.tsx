'use client';

import Link from 'next/link';
import { Chip } from '@/components/ui/Chip';
import { Button } from '@/components/ui/Button';
import { ROUTES } from '@/lib/constants/routes';

interface RequirementsSummaryProps {
  requirements: Record<string, string | string[]>;
  onRerun?: () => void;
}

/**
 * Map of requirement keys to human-readable labels.
 */
const REQUIREMENT_LABELS: Record<string, string> = {
  city: 'City',
  budget_min: 'Min Budget',
  budget_max: 'Max Budget',
  budget: 'Budget',
  configuration: 'Config',
  configurations: 'Config',
  timeline: 'Timeline',
  purpose: 'Purpose',
  location: 'Location',
  locations: 'Locations',
  priorities: 'Priorities',
  possession: 'Possession',
  lifestyle: 'Lifestyle',
};

function formatRequirementValue(key: string, value: string | string[]): string {
  if (Array.isArray(value)) {
    return value.join(', ');
  }
  return value;
}

function getDisplayLabel(key: string): string {
  return REQUIREMENT_LABELS[key] || key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

export function RequirementsSummary({ requirements, onRerun }: RequirementsSummaryProps) {
  const entries = Object.entries(requirements).filter(
    ([, value]) => value && (typeof value === 'string' ? value.length > 0 : value.length > 0)
  );

  if (entries.length === 0) return null;

  return (
    <div className="flex flex-col gap-lg rounded-md border border-gray-200 bg-white p-lg">
      <div className="flex items-center justify-between">
        <h2 className="text-sm font-semibold text-gray-900">Your Requirements</h2>
        <Link
          href={ROUTES.ASSESSMENT}
          className="text-sm font-medium text-brand-primary no-underline transition-colors hover:text-brand-primary-dark hover:no-underline"
        >
          Edit Requirements
        </Link>
      </div>

      <div className="flex flex-wrap gap-sm">
        {entries.map(([key, value]) => (
          <Chip key={key} selected={false} disabled className="cursor-default opacity-100">
            <span className="font-semibold">{getDisplayLabel(key)}:</span>{' '}
            {formatRequirementValue(key, value)}
          </Chip>
        ))}
      </div>

      {onRerun && (
        <div className="flex">
          <Button variant="secondary" size="sm" onClick={onRerun}>
            <svg
              width="14"
              height="14"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              aria-hidden="true"
            >
              <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8" />
              <path d="M21 3v5h-5" />
            </svg>
            Re-run Analysis
          </Button>
        </div>
      )}
    </div>
  );
}
