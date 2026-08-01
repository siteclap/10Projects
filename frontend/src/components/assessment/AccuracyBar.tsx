import { cn } from '@/lib/utils/cn';

interface AccuracyBarProps {
  accuracy: number;
  answeredCount: number;
  totalQuestions: number;
}

export function AccuracyBar({
  accuracy,
  answeredCount,
  totalQuestions,
}: AccuracyBarProps) {
  const progressPercent =
    totalQuestions > 0
      ? Math.min(100, Math.round((answeredCount / totalQuestions) * 100))
      : 0;

  return (
    <div className="flex flex-col gap-xs">
      <div className="flex items-center justify-between">
        <span className="text-caption font-medium text-gray-600">
          Match Accuracy:{' '}
          <span
            className={cn(
              'tabular-nums',
              accuracy >= 90
                ? 'text-success'
                : accuracy >= 80
                  ? 'text-brand-primary'
                  : 'text-gray-700'
            )}
          >
            {accuracy}%
          </span>
        </span>
        <span className="text-caption text-gray-400">
          {answeredCount}/{totalQuestions}
        </span>
      </div>

      <div
        className="h-[4px] w-full overflow-hidden rounded-full bg-gray-200"
        role="progressbar"
        aria-valuenow={progressPercent}
        aria-valuemin={0}
        aria-valuemax={100}
        aria-label={`Assessment progress: ${answeredCount} of ${totalQuestions} questions answered`}
      >
        <div
          className="h-full rounded-full bg-brand-primary transition-all duration-500 ease-out"
          style={{ width: `${progressPercent}%` }}
        />
      </div>
    </div>
  );
}
