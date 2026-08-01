import { cn } from '@/lib/utils/cn';

interface FitScoreBadgeProps {
  score: number;
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

function getScoreColor(score: number): string {
  if (score >= 90) return 'from-amber-400 to-amber-500';
  if (score >= 80) return 'from-amber-400/80 to-amber-500/80';
  if (score >= 70) return 'from-amber-600 to-amber-700';
  return 'from-gray-400 to-gray-500';
}

const sizeStyles = {
  sm: 'w-[48px] h-[48px]',
  md: 'w-[64px] h-[64px]',
  lg: 'w-[80px] h-[80px]',
} as const;

const scoreFontStyles = {
  sm: 'text-base font-bold',
  md: 'text-h3 font-bold',
  lg: 'text-score font-bold',
} as const;

const labelFontStyles = {
  sm: 'text-[9px]',
  md: 'text-[10px]',
  lg: 'text-caption',
} as const;

export function FitScoreBadge({
  score,
  size = 'md',
  className,
}: FitScoreBadgeProps) {
  const clampedScore = Math.min(100, Math.max(0, Math.round(score)));

  return (
    <div
      className={cn(
        'flex flex-col items-center justify-center rounded-full bg-gradient-to-br shadow-xs',
        getScoreColor(clampedScore),
        sizeStyles[size],
        className
      )}
      role="img"
      aria-label={`Fit score: ${clampedScore} out of 100`}
    >
      <span className={cn('leading-none text-white tabular-nums', scoreFontStyles[size])}>
        {clampedScore}
      </span>
      <span className={cn('font-semibold uppercase tracking-wider text-white/90', labelFontStyles[size])}>
        Fit
      </span>
    </div>
  );
}
