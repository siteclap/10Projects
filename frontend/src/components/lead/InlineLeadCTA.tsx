'use client';

import { useLeadForm } from './LeadFormContext';

interface InlineLeadCTAProps {
  projectTitle: string;
}

const benefits = [
  'Lowest Price Guarantee',
  'Free Cab Site Visit',
  'RERA Verified Project',
];

export function InlineLeadCTA({ projectTitle }: InlineLeadCTAProps) {
  const { openForm } = useLeadForm();

  return (
    <div className="relative overflow-hidden rounded-lg bg-brand-primary p-2xl md:p-3xl">
      {/* Subtle gradient overlay */}
      <div
        className="absolute inset-0"
        style={{
          background:
            'linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.05) 50%, transparent 100%)',
        }}
        aria-hidden="true"
      />

      <div className="relative flex flex-col gap-2xl md:flex-row md:items-center md:justify-between">
        {/* Left: Text content */}
        <div className="flex-1">
          <h3 className="text-h3 text-white">
            Interested in {projectTitle}?
          </h3>
          <p className="mt-sm text-sm text-white/70">
            Get exclusive pricing, floor plans & a free site visit
          </p>

          {/* Benefits */}
          <ul className="mt-xl flex flex-col gap-sm">
            {benefits.map((benefit) => (
              <li key={benefit} className="flex items-center gap-sm">
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2.5"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  className="shrink-0 text-white"
                  aria-hidden="true"
                >
                  <path d="M20 6 9 17l-5-5" />
                </svg>
                <span className="text-sm text-white/90">{benefit}</span>
              </li>
            ))}
          </ul>

          {/* CTA buttons */}
          <div className="mt-xl flex flex-wrap gap-sm">
            <button
              type="button"
              onClick={() => openForm('best_price')}
              className="inline-flex h-[44px] items-center justify-center rounded-sm bg-white px-xl text-sm font-semibold text-brand-primary transition-colors hover:bg-white/90"
            >
              Get Best Price
            </button>
            <button
              type="button"
              onClick={() => openForm('site_visit')}
              className="inline-flex h-[44px] items-center justify-center rounded-sm border border-white/30 px-xl text-sm font-medium text-white transition-colors hover:bg-white/10"
            >
              Book Site Visit
            </button>
          </div>

          {/* Social proof */}
          <div className="mt-xl flex items-center gap-sm">
            <div className="flex -space-x-2">
              {['PS', 'AK', 'RV'].map((initials, i) => (
                <div
                  key={i}
                  className="flex h-[24px] w-[24px] items-center justify-center rounded-full border-2 border-brand-primary bg-white/20 text-[10px] font-medium text-white"
                >
                  {initials}
                </div>
              ))}
            </div>
            <span className="text-caption text-white/60">
              48 buyers enquired today
            </span>
          </div>
        </div>

        {/* Right: Decorative building illustration (desktop only) */}
        <div className="hidden md:flex md:shrink-0 md:items-center md:justify-center">
          <div className="flex h-[180px] w-[180px] items-center justify-center rounded-full bg-white/10">
            <svg
              width="80"
              height="80"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="1"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-white/40"
              aria-hidden="true"
            >
              <path d="M3 21h18" />
              <path d="M5 21V7l8-4v18" />
              <path d="M19 21V11l-6-4" />
              <path d="M9 9v.01" />
              <path d="M9 12v.01" />
              <path d="M9 15v.01" />
              <path d="M9 18v.01" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  );
}
