'use client';

import { useState } from 'react';
import Link from 'next/link';
import { cn } from '@/lib/utils/cn';
import { Badge } from '@/components/ui/Badge';
import { FitScoreBadge } from './FitScoreBadge';
import { formatPriceRange, formatPrice } from '@/lib/utils/format-price';
import { calculateEMI } from '@/lib/utils/calculate-emi';
import type { ProjectCard as ProjectCardType } from '@/lib/types/project';

interface ProjectCardProps {
  project: ProjectCardType;
  showFitScore?: boolean;
  fitScore?: number;
  className?: string;
}

function getConfigSummary(project: ProjectCardType): string {
  const configs = project.configurations;
  if (!configs || configs.length === 0) return '';

  const types = [...new Set(configs.map((c) => c.config_type))];
  return types.join(', ');
}

function getEMIDisplay(priceMin: number): string {
  if (!priceMin || priceMin <= 0) return '';
  const emi = calculateEMI(priceMin);
  return `EMI from ${formatPrice(Math.round(emi))}/mo`;
}

export function ProjectCard({
  project,
  showFitScore = false,
  fitScore,
  className,
}: ProjectCardProps) {
  const [saved, setSaved] = useState(false);

  const displayScore = fitScore ?? project.fit_score;
  const configSummary = getConfigSummary(project);
  const emiText = getEMIDisplay(project.price_min);
  const projectHref = `/navi-mumbai/${project.location.toLowerCase().replace(/\s+/g, '-')}/${project.slug}`;

  const tags: Array<{ label: string; variant: 'default' | 'success' | 'outline' }> = [];

  if (project.rera_number) {
    tags.push({ label: 'RERA Verified', variant: 'success' });
  }

  if (project.expected_possession) {
    tags.push({ label: `Possession: ${project.expected_possession}`, variant: 'outline' });
  }

  // Advisor initials from developer name
  const initials = project.developer
    .split(' ')
    .map((w) => w[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();

  return (
    <div
      className={cn(
        'group flex w-[320px] min-w-[320px] flex-col overflow-hidden rounded-md border border-gray-200 bg-white shadow-card transition-shadow duration-200 hover:shadow-hover',
        className
      )}
    >
      {/* Image area */}
      <div className="relative h-[200px] w-full bg-gray-200">
        {project.thumbnail ? (
          <img
            src={project.thumbnail}
            alt={project.title}
            className="h-full w-full object-cover"
            loading="lazy"
          />
        ) : (
          <div className="flex h-full w-full items-center justify-center text-sm text-gray-400">
            Project Image
          </div>
        )}

        {/* Top-left badges */}
        <div className="absolute left-md top-md flex flex-col gap-xs">
          {project.construction_stage && (
            <Badge variant="default" size="sm" className="bg-white/90 backdrop-blur-sm">
              {project.construction_stage}
            </Badge>
          )}
        </div>

        {/* Top-right save button */}
        <button
          type="button"
          className={cn(
            'absolute right-md top-md flex h-[32px] w-[32px] items-center justify-center rounded-full bg-white/90 backdrop-blur-sm transition-colors',
            saved ? 'text-danger' : 'text-gray-500 hover:text-danger'
          )}
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            setSaved(!saved);
          }}
          aria-label={saved ? 'Remove from saved' : 'Save project'}
        >
          <svg
            width="18"
            height="18"
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
        </button>
      </div>

      {/* Body */}
      <Link href={projectHref} className="flex flex-1 flex-col no-underline hover:no-underline">
        <div className="relative flex flex-1 flex-col gap-sm p-lg">
          {/* Fit score badge positioned top-right of body */}
          {showFitScore && displayScore != null && displayScore > 0 && (
            <div className="absolute -top-[24px] right-lg">
              <FitScoreBadge score={displayScore} size="sm" />
            </div>
          )}

          {/* Configuration */}
          {configSummary && (
            <p className="text-caption uppercase tracking-wider text-gray-500">
              {configSummary}
            </p>
          )}

          {/* Price */}
          <p className="text-price text-gray-900">
            {formatPriceRange(project.price_min, project.price_max)}
          </p>

          {/* EMI */}
          {emiText && (
            <p className="text-caption text-gray-500">{emiText}</p>
          )}

          {/* Project name */}
          <h3 className="text-base font-semibold text-gray-900 group-hover:text-brand-primary">
            {project.title}
          </h3>

          {/* Location */}
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
            {project.location}
          </div>

          {/* Tags */}
          {tags.length > 0 && (
            <div className="mt-xs flex flex-wrap gap-xs">
              {tags.map((tag) => (
                <Badge key={tag.label} variant={tag.variant} size="sm">
                  {tag.label}
                </Badge>
              ))}
            </div>
          )}
        </div>

        {/* Advisor card */}
        <div className="flex items-center gap-md border-t border-gray-100 px-lg py-md">
          <div className="flex h-[36px] w-[36px] shrink-0 items-center justify-center rounded-full bg-brand-primary-pale text-caption font-semibold text-brand-primary">
            {initials}
          </div>
          <div className="flex-1">
            <p className="text-sm font-medium text-gray-900">{project.developer}</p>
            <p className="text-caption text-gray-500">Developer</p>
          </div>
          <span className="text-sm font-medium text-brand-primary">
            View Details
          </span>
        </div>
      </Link>
    </div>
  );
}
