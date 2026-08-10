import { cn } from '@/lib/utils/cn';

interface ProsConsListProps {
  pros: string[];
  cons: string[];
}

function CheckIcon() {
  return (
    <svg
      width="18"
      height="18"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2.5"
      strokeLinecap="round"
      strokeLinejoin="round"
      className="shrink-0 text-success"
      aria-hidden="true"
    >
      <path d="M20 6 9 17l-5-5" />
    </svg>
  );
}

function AlertIcon() {
  return (
    <svg
      width="18"
      height="18"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
      className="shrink-0 text-accent-dark"
      aria-hidden="true"
    >
      <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
      <path d="M12 9v4" />
      <path d="M12 17h.01" />
    </svg>
  );
}

export function ProsConsList({ pros, cons }: ProsConsListProps) {
  const hasPros = pros.length > 0;
  const hasCons = cons.length > 0;

  if (!hasPros && !hasCons) return null;

  return (
    <div className="grid grid-cols-1 gap-3xl md:grid-cols-2">
      {/* Strengths */}
      {hasPros && (
        <div>
          <h4 className="flex items-center gap-md text-base font-semibold text-gray-900">
            <span className="flex h-[28px] w-[28px] items-center justify-center rounded-full bg-success-light">
              <CheckIcon />
            </span>
            Strengths
          </h4>
          <ul className="mt-xl flex flex-col gap-lg">
            {pros.map((item, index) => (
              <li key={index} className="flex items-start gap-md">
                <CheckIcon />
                <span className="text-sm leading-relaxed text-gray-700">{item}</span>
              </li>
            ))}
          </ul>
        </div>
      )}

      {/* Considerations */}
      {hasCons && (
        <div>
          <h4 className="flex items-center gap-md text-base font-semibold text-gray-900">
            <span className="flex h-[28px] w-[28px] items-center justify-center rounded-full bg-accent-pale">
              <AlertIcon />
            </span>
            Considerations
          </h4>
          <ul className="mt-xl flex flex-col gap-lg">
            {cons.map((item, index) => (
              <li key={index} className="flex items-start gap-md">
                <AlertIcon />
                <span className="text-sm leading-relaxed text-gray-700">{item}</span>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
