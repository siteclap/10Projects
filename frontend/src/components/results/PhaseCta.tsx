import Link from 'next/link';
import { cn } from '@/lib/utils/cn';

interface PhaseCtaProps {
  currentPhase: number;
  currentAccuracy: number;
  sessionUuid: string;
}

/**
 * Accuracy targets by phase.
 * Phase 1 = 72%, Phase 2 = 89%, Phase 3 = 96%.
 */
const PHASE_ACCURACY: Record<number, number> = {
  1: 89,
  2: 96,
};

/**
 * Number of additional questions per phase advancement.
 */
const PHASE_QUESTIONS: Record<number, number> = {
  1: 5,
  2: 4,
};

export function PhaseCta({ currentPhase, currentAccuracy, sessionUuid }: PhaseCtaProps) {
  if (currentPhase >= 3) return null;

  const nextAccuracy = PHASE_ACCURACY[currentPhase] || 96;
  const additionalQuestions = PHASE_QUESTIONS[currentPhase] || 4;

  return (
    <div
      className={cn(
        'relative overflow-hidden rounded-md bg-gradient-to-r from-brand-primary to-brand-primary-dark p-lg md:p-xl'
      )}
    >
      {/* Background decoration */}
      <div className="absolute -right-[40px] -top-[40px] h-[120px] w-[120px] rounded-full bg-white/5" />
      <div className="absolute -bottom-[20px] -left-[20px] h-[80px] w-[80px] rounded-full bg-white/5" />

      <div className="relative flex flex-col items-start gap-lg md:flex-row md:items-center md:justify-between">
        <div className="flex flex-col gap-xs">
          <h3 className="text-base font-semibold text-white">
            Improve your match accuracy
          </h3>
          <p className="text-sm text-white/80">
            Answer {additionalQuestions} more questions to improve from{' '}
            <span className="font-semibold text-white">{currentAccuracy}%</span> to{' '}
            <span className="font-semibold text-white">{nextAccuracy}%</span> accuracy
          </p>
        </div>

        <Link
          href={`/start?resume=${sessionUuid}`}
          className={cn(
            'inline-flex h-10 shrink-0 items-center justify-center rounded-sm bg-white px-xl',
            'text-sm font-semibold text-brand-primary no-underline',
            'transition-colors hover:bg-gray-50 hover:no-underline'
          )}
        >
          Improve Accuracy
        </Link>
      </div>
    </div>
  );
}
