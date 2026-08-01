import { cn } from '@/lib/utils/cn';

interface AnalysingDotsProps {
  text?: string;
  className?: string;
}

export function AnalysingDots({
  text = 'Analysing projects...',
  className,
}: AnalysingDotsProps) {
  return (
    <div
      className={cn('flex items-center gap-sm', className)}
      role="status"
      aria-label={text}
    >
      <div className="flex items-center gap-[4px]">
        <span
          className="inline-block h-[8px] w-[8px] rounded-full bg-brand-primary animate-[analysingBounce_1.4s_ease-in-out_infinite]"
          style={{ animationDelay: '0s' }}
        />
        <span
          className="inline-block h-[8px] w-[8px] rounded-full bg-brand-primary animate-[analysingBounce_1.4s_ease-in-out_infinite]"
          style={{ animationDelay: '0.2s' }}
        />
        <span
          className="inline-block h-[8px] w-[8px] rounded-full bg-brand-primary animate-[analysingBounce_1.4s_ease-in-out_infinite]"
          style={{ animationDelay: '0.4s' }}
        />
      </div>
      <span className="text-sm text-gray-500">{text}</span>
    </div>
  );
}
