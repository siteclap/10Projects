'use client';

import { useLeadForm } from '@/components/lead/LeadFormContext';
import { formatPrice } from '@/lib/utils/format-price';

interface Configuration {
  config_type: string;
  carpet_area_sqft: number;
  total_price: number;
}

interface FloorPlanSectionProps {
  configurations: Configuration[];
  projectTitle: string;
}

export function FloorPlanSection({ configurations, projectTitle }: FloorPlanSectionProps) {
  const { openForm } = useLeadForm();

  return (
    <section id="floor-plans" className="mt-3xl border-t border-gray-100 pt-3xl">
      <h2 className="text-h2 text-gray-900">Floor Plans</h2>
      <div className="mt-xl grid grid-cols-1 gap-md sm:grid-cols-3">
        {configurations.map((config) => (
          <div
            key={config.config_type}
            className="group flex flex-col items-center rounded-lg border border-gray-200 bg-white p-xl text-center transition-shadow hover:shadow-card"
          >
            {/* Floor plan placeholder */}
            <div className="flex h-[140px] w-full items-center justify-center rounded-md bg-gray-50">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" className="text-gray-300" aria-hidden="true">
                <rect width="18" height="18" x="3" y="3" rx="2" />
                <path d="M3 9h18" />
                <path d="M9 21V9" />
              </svg>
            </div>
            <h3 className="mt-lg text-base font-semibold text-gray-900">
              {config.config_type}
            </h3>
            <p className="mt-xs text-sm text-gray-500">
              {config.carpet_area_sqft} sq ft carpet
            </p>
            <p className="mt-sm text-h4 text-gray-900">
              {formatPrice(config.total_price)}
            </p>
            <button
              type="button"
              onClick={() => openForm('floor_plan')}
              className="mt-lg inline-flex h-[36px] w-full items-center justify-center rounded-sm border border-brand-primary text-sm font-medium text-brand-primary transition-colors hover:bg-brand-primary hover:text-white"
            >
              Download Floor Plan
            </button>
          </div>
        ))}
      </div>

      {/* CTA banner */}
      <div className="mt-xl rounded-lg bg-brand-primary p-xl">
        <div className="flex flex-col gap-lg sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 className="text-base font-semibold text-white">
              Want detailed floor plans for {projectTitle}?
            </h3>
            <p className="mt-xs text-sm text-white/70">
              Get high-resolution floor plans with dimensions, vastu direction & pricing
            </p>
          </div>
          <button
            type="button"
            onClick={() => openForm('floor_plan')}
            className="inline-flex h-[44px] shrink-0 items-center justify-center rounded-sm bg-white px-xl text-sm font-semibold text-brand-primary transition-colors hover:bg-white/90"
          >
            Get Floor Plans Free
          </button>
        </div>
      </div>
    </section>
  );
}
