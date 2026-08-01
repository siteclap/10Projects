'use client';

import type { RecommendationResult } from '@/lib/types/recommendation';
import { cn } from '@/lib/utils/cn';
import { Button } from '@/components/ui/Button';
import { FitScoreBadge } from '@/components/project/FitScoreBadge';
import { ROUTES } from '@/lib/constants/routes';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface ResultsPreviewProps {
  recommendations: RecommendationResult[];
  accuracy: number;
  phase: number;
  onContinue?: () => void;
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const NEXT_ACCURACY: Record<number, number> = {
  1: 89,
  2: 96,
};

const QUESTIONS_REMAINING: Record<number, number> = {
  1: 4,
  2: 4,
};

// ---------------------------------------------------------------------------
// Component
// ---------------------------------------------------------------------------

export function ResultsPreview({
  recommendations,
  accuracy,
  phase,
  onContinue,
}: ResultsPreviewProps) {
  const topCount = phase === 1 ? 5 : 10;
  const previewProjects = recommendations.slice(0, 3);
  const hasMore = phase < 3;
  const nextAccuracy = NEXT_ACCURACY[phase];
  const questionsLeft = QUESTIONS_REMAINING[phase];

  return (
    <div className="animate-[fadeUp_0.3s_ease-out]">
      <div className="rounded-md border border-gray-200 bg-white p-lg shadow-card">
        {/* Header */}
        <div className="mb-lg flex items-center justify-between">
          <h3 className="text-h4 text-gray-900">
            Your Top {topCount} matches are ready!
          </h3>
          <span
            className={cn(
              'inline-flex items-center rounded-full px-md py-xs text-caption font-bold tabular-nums',
              accuracy >= 90
                ? 'bg-success-light text-green-800'
                : accuracy >= 80
                  ? 'bg-brand-primary-bg text-brand-primary'
                  : 'bg-accent-pale text-amber-800'
            )}
          >
            {accuracy}% accuracy
          </span>
        </div>

        {/* Top 3 preview */}
        <div className="mb-lg flex flex-col gap-sm">
          {previewProjects.map((project) => (
            <div
              key={project.project_id}
              className="flex items-center gap-md rounded-sm border border-gray-100 bg-gray-50 p-sm"
            >
              <FitScoreBadge score={project.final_score} size="sm" />
              <div className="flex min-w-0 flex-1 flex-col">
                <span className="truncate text-sm font-medium text-gray-900">
                  {project.title}
                </span>
                <span className="text-caption text-gray-500">
                  #{project.rank} match
                </span>
              </div>
            </div>
          ))}

          {recommendations.length > 3 && (
            <p className="text-center text-caption text-gray-400">
              +{recommendations.length - 3} more projects
            </p>
          )}
        </div>

        {/* Actions */}
        <div className="flex flex-col gap-sm">
          {hasMore && onContinue ? (
            <>
              <Button onClick={onContinue} className="w-full">
                Improve Accuracy (Answer {questionsLeft} more)
              </Button>
              <p className="text-center text-caption text-gray-500">
                Answer more to improve from{' '}
                <span className="font-medium">{accuracy}%</span> to{' '}
                <span className="font-medium text-brand-primary">
                  {nextAccuracy}%
                </span>
              </p>
            </>
          ) : (
            <Button asChild className="w-full">
              <a href={ROUTES.ASSESSMENT}>See Full Results</a>
            </Button>
          )}
        </div>
      </div>
    </div>
  );
}
