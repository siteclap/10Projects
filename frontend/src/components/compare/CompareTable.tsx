'use client';

import { cn } from '@/lib/utils/cn';
import { FitScoreBadge } from '@/components/project/FitScoreBadge';
import { formatPriceRange, formatPrice } from '@/lib/utils/format-price';
import { calculateEMI } from '@/lib/utils/calculate-emi';
import type { ComparisonProject } from '@/lib/types/comparison';

interface CompareTableProps {
  projects: ComparisonProject[];
}

/**
 * Row group definition for the comparison table.
 * Each group has a label and a list of rows with accessor functions.
 */
interface TableRow {
  label: string;
  accessor: (project: ComparisonProject) => string | number | null;
  highlight?: 'highest' | 'lowest';
  renderAs?: 'badge' | 'list' | 'text';
}

interface TableGroup {
  heading: string;
  rows: TableRow[];
}

function getEmiEstimate(priceMin: number): string {
  if (!priceMin || priceMin <= 0) return '-';
  const emi = calculateEMI(priceMin);
  return `${formatPrice(Math.round(emi))}/mo`;
}

const TABLE_GROUPS: TableGroup[] = [
  {
    heading: 'Basic Info',
    rows: [
      { label: 'Project', accessor: (p) => p.title },
      { label: 'Developer', accessor: (p) => p.developer },
      { label: 'Location', accessor: (p) => p.location },
      { label: 'RERA No.', accessor: (p) => p.rera_number || '-' },
    ],
  },
  {
    heading: 'Price',
    rows: [
      {
        label: 'Price Range',
        accessor: (p) => formatPriceRange(p.price_min, p.price_max),
      },
      {
        label: 'EMI Estimate',
        accessor: (p) => getEmiEstimate(p.price_min),
      },
    ],
  },
  {
    heading: 'Specs',
    rows: [
      {
        label: 'Configurations',
        accessor: (p) => p.configurations.join(', ') || '-',
      },
      {
        label: 'Possession',
        accessor: (p) => p.possession || '-',
      },
      {
        label: 'Construction',
        accessor: (p) => p.construction_stage || '-',
      },
    ],
  },
  {
    heading: 'Features',
    rows: [
      {
        label: 'Amenities',
        accessor: (p) => {
          if (!p.amenities || p.amenities.length === 0) return '-';
          const display = p.amenities.slice(0, 5).join(', ');
          if (p.amenities.length > 5) {
            return `${display} +${p.amenities.length - 5} more`;
          }
          return display;
        },
      },
      {
        label: 'Railway Distance',
        accessor: (p) =>
          p.railway_distance_km
            ? `${p.railway_distance_km} km`
            : '-',
      },
    ],
  },
];

/**
 * Determine which project has the best (lowest) price_min value for highlighting.
 */
function getBestPriceIndex(projects: ComparisonProject[]): number {
  let bestIdx = -1;
  let bestPrice = Infinity;
  projects.forEach((p, i) => {
    if (p.price_min > 0 && p.price_min < bestPrice) {
      bestPrice = p.price_min;
      bestIdx = i;
    }
  });
  return bestIdx;
}

/**
 * Determine which project has the highest fit score for highlighting.
 */
function getBestScoreIndex(projects: ComparisonProject[]): number {
  let bestIdx = -1;
  let bestScore = -1;
  projects.forEach((p, i) => {
    if (p.fit_score != null && p.fit_score > bestScore) {
      bestScore = p.fit_score;
      bestIdx = i;
    }
  });
  return bestIdx;
}

export function CompareTable({ projects }: CompareTableProps) {
  const bestPriceIdx = getBestPriceIndex(projects);
  const bestScoreIdx = getBestScoreIndex(projects);
  const hasFitScores = projects.some((p) => p.fit_score != null && p.fit_score > 0);

  return (
    <div className="overflow-x-auto rounded-md border border-gray-200 bg-white">
      <table className="w-full min-w-[600px] border-collapse">
        {/* Header: project thumbnails and names */}
        <thead>
          <tr>
            <th className="sticky left-0 z-10 w-[160px] bg-gray-50 p-lg text-left text-sm font-semibold text-gray-500 md:w-[200px]">
              Compare
            </th>
            {projects.map((project, idx) => (
              <th
                key={project.id}
                className={cn(
                  'p-lg text-center',
                  idx === bestScoreIdx && hasFitScores && 'bg-success-bg'
                )}
              >
                <div className="flex flex-col items-center gap-sm">
                  <div className="h-[80px] w-[120px] overflow-hidden rounded-sm bg-gray-200">
                    {project.thumbnail ? (
                      <img
                        src={project.thumbnail}
                        alt={project.title}
                        className="h-full w-full object-cover"
                        loading="lazy"
                      />
                    ) : (
                      <div className="flex h-full w-full items-center justify-center text-caption text-gray-400">
                        No Image
                      </div>
                    )}
                  </div>
                  <span className="text-sm font-semibold text-gray-900">{project.title}</span>
                </div>
              </th>
            ))}
          </tr>
        </thead>

        <tbody>
          {/* Fit Score row (if any project has a score) */}
          {hasFitScores && (
            <>
              <tr>
                <td
                  colSpan={projects.length + 1}
                  className="bg-gray-50 px-lg py-sm text-caption font-semibold uppercase tracking-wider text-gray-500"
                >
                  Score
                </td>
              </tr>
              <tr className="border-t border-gray-100">
                <td className="sticky left-0 z-10 bg-white px-lg py-md text-sm font-medium text-gray-700">
                  Fit Score
                </td>
                {projects.map((project, idx) => (
                  <td
                    key={project.id}
                    className={cn(
                      'px-lg py-md text-center',
                      idx === bestScoreIdx && 'bg-success-bg'
                    )}
                  >
                    {project.fit_score != null && project.fit_score > 0 ? (
                      <div className="flex justify-center">
                        <FitScoreBadge score={project.fit_score} size="sm" />
                      </div>
                    ) : (
                      <span className="text-sm text-gray-400">-</span>
                    )}
                  </td>
                ))}
              </tr>
            </>
          )}

          {/* Table groups */}
          {TABLE_GROUPS.map((group) => (
            <>
              {/* Group heading */}
              <tr key={`heading-${group.heading}`}>
                <td
                  colSpan={projects.length + 1}
                  className="bg-gray-50 px-lg py-sm text-caption font-semibold uppercase tracking-wider text-gray-500"
                >
                  {group.heading}
                </td>
              </tr>

              {/* Group rows */}
              {group.rows.map((row) => {
                const values = projects.map((p) => row.accessor(p));

                return (
                  <tr key={row.label} className="border-t border-gray-100">
                    <td className="sticky left-0 z-10 bg-white px-lg py-md text-sm font-medium text-gray-700">
                      {row.label}
                    </td>
                    {projects.map((project, idx) => {
                      const value = values[idx];
                      const isHighlightedPrice =
                        row.label === 'Price Range' && idx === bestPriceIdx;

                      return (
                        <td
                          key={project.id}
                          className={cn(
                            'px-lg py-md text-center text-sm text-gray-900',
                            isHighlightedPrice && 'bg-success-bg font-semibold text-green-800'
                          )}
                        >
                          {value ?? '-'}
                        </td>
                      );
                    })}
                  </tr>
                );
              })}
            </>
          ))}
        </tbody>
      </table>
    </div>
  );
}
