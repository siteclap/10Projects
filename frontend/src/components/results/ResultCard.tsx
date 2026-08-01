'use client';

import { useState } from 'react';
import Link from 'next/link';
import { cn } from '@/lib/utils/cn';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { FitScoreBadge } from '@/components/project/FitScoreBadge';
import { formatPrice, formatPriceRange } from '@/lib/utils/format-price';
import { calculateEMI } from '@/lib/utils/calculate-emi';
import { useCompare } from '@/lib/hooks/use-compare';
import type { RecommendationResult } from '@/lib/types/recommendation';

interface ResultCardProps {
  result: RecommendationResult;
  rank: number;
}

function getRankLabel(rank: number): string {
  if (rank === 1) return '#1';
  if (rank === 2) return '#2';
  if (rank === 3) return '#3';
  return `#${rank}`;
}

function getProjectHref(result: RecommendationResult): string {
  const meta = result.project_meta;
  const location = (meta.location as string) || '';
  const slug = result.permalink.split('/').filter(Boolean).pop() || '';
  const locationSlug = location.toLowerCase().replace(/\s+/g, '-');
  return `/navi-mumbai/${locationSlug}/${slug}`;
}

function getEmiFromMeta(meta: Record<string, unknown>): string {
  const priceMin = (meta.price_min as number) || 0;
  if (priceMin <= 0) return '';
  const emi = calculateEMI(priceMin);
  return formatPrice(Math.round(emi));
}

function getAllInCost(meta: Record<string, unknown>): string {
  const priceMin = (meta.price_min as number) || 0;
  const priceMax = (meta.price_max as number) || 0;
  if (!priceMin && !priceMax) return '';
  return formatPriceRange(priceMin, priceMax);
}

export function ResultCard({ result, rank }: ResultCardProps) {
  const [saved, setSaved] = useState(false);
  const { toggle, has } = useCompare();
  const isComparing = has(result.project_id);

  const meta = result.project_meta;
  const developer = (meta.developer as string) || '';
  const location = (meta.location as string) || '';
  const emiDisplay = getEmiFromMeta(meta);
  const allInCost = getAllInCost(meta);
  const projectHref = getProjectHref(result);

  const initials = developer
    .split(' ')
    .map((w) => w[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();

  return (
    <div
      className={cn(
        'group flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white shadow-card transition-shadow duration-200 hover:shadow-hover',
        'md:flex-row'
      )}
    >
      {/* Image area */}
      <div className="relative h-[200px] w-full shrink-0 bg-gray-200 md:h-auto md:w-[300px]">
        {result.thumbnail ? (
          <img
            src={result.thumbnail}
            alt={result.title}
            className="h-full w-full object-cover"
            loading="lazy"
          />
        ) : (
          <div className="flex h-full w-full items-center justify-center text-sm text-gray-400">
            Project Image
          </div>
        )}

        {/* Rank badge */}
        <div className="absolute left-md top-md">
          <span
            className={cn(
              'inline-flex h-[32px] min-w-[32px] items-center justify-center rounded-sm px-sm text-sm font-bold text-white',
              rank <= 3
                ? 'bg-brand-primary'
                : 'bg-gray-700'
            )}
          >
            {getRankLabel(rank)}
          </span>
        </div>
      </div>

      {/* Content area */}
      <div className="flex flex-1 flex-col">
        <Link href={projectHref} className="flex flex-1 flex-col no-underline hover:no-underline">
          <div className="relative flex flex-1 flex-col gap-sm p-lg">
            {/* Fit score badge - top right */}
            <div className="absolute right-lg top-lg">
              <FitScoreBadge score={result.final_score} size="md" />
            </div>

            {/* Project name */}
            <h3 className="pr-[80px] text-h4 text-gray-900 group-hover:text-brand-primary">
              {result.title}
            </h3>

            {/* Developer */}
            {developer && (
              <p className="text-sm text-gray-500">
                by {developer}
              </p>
            )}

            {/* Location */}
            {location && (
              <div className="flex items-center gap-xs text-sm text-gray-500">
                <svg
                  width="14"
                  height="14"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  className="shrink-0"
                  aria-hidden="true"
                >
                  <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
                {location}, Navi Mumbai
              </div>
            )}

            {/* EMI and price */}
            <div className="mt-xs flex flex-col gap-xs">
              {emiDisplay && (
                <p className="text-base font-semibold text-gray-900">
                  EMI from {emiDisplay}/month
                </p>
              )}
              {allInCost && (
                <p className="text-sm text-gray-500">
                  All-in cost: {allInCost}
                </p>
              )}
            </div>

            {/* Strengths and tradeoffs */}
            <div className="mt-sm flex flex-wrap gap-xs">
              {result.strengths.map((s) => (
                <Badge key={s.category} variant="success" size="sm">
                  {s.label}
                </Badge>
              ))}
              {result.tradeoffs.map((t) => (
                <Badge key={t.category} variant="warning" size="sm">
                  {t.label}
                </Badge>
              ))}
            </div>
          </div>
        </Link>

        {/* Bottom bar: advisor stub + actions */}
        <div className="flex flex-col gap-md border-t border-gray-100 px-lg py-md sm:flex-row sm:items-center sm:justify-between">
          {/* Advisor card stub */}
          <div className="flex items-center gap-md">
            <div className="flex h-[36px] w-[36px] shrink-0 items-center justify-center rounded-full bg-brand-primary-pale text-caption font-semibold text-brand-primary">
              {initials}
            </div>
            <div>
              <p className="text-sm font-medium text-gray-900">{developer}</p>
              <p className="text-caption text-gray-500">Developer</p>
            </div>
          </div>

          {/* Action buttons */}
          <div className="flex items-center gap-sm">
            <Button
              variant="secondary"
              size="sm"
              onClick={(e) => {
                e.preventDefault();
                toggle(result.project_id);
              }}
              className={cn(isComparing && 'border-brand-primary text-brand-primary')}
            >
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
                <line x1="18" x2="18" y1="20" y2="10" />
                <line x1="12" x2="12" y1="20" y2="4" />
                <line x1="6" x2="6" y1="20" y2="14" />
              </svg>
              {isComparing ? 'Comparing' : 'Compare'}
            </Button>

            <Button
              variant="ghost"
              size="sm"
              onClick={(e) => {
                e.preventDefault();
                setSaved(!saved);
              }}
              className={cn(saved && 'text-danger')}
            >
              <svg
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill={saved ? 'currentColor' : 'none'}
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
              </svg>
              {saved ? 'Saved' : 'Save'}
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}
