import { cn } from '@/lib/utils/cn';
import { formatPrice } from '@/lib/utils/format-price';
import { calculateEMI } from '@/lib/utils/calculate-emi';
import type { Project } from '@/lib/types/project';

interface ProjectSidebarProps {
  project: Project;
}

const trustItems = [
  {
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-success" aria-hidden="true">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
        <path d="m9 12 2 2 4-4" />
      </svg>
    ),
    label: 'RERA Verified',
  },
  {
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-brand-primary" aria-hidden="true">
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.3-4.3" />
      </svg>
    ),
    label: 'Transparent Analysis',
  },
  {
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-brand-primary" aria-hidden="true">
        <path d="M18.36 6.64a9 9 0 1 1-12.73 0" />
        <line x1="12" x2="12" y1="2" y2="12" />
      </svg>
    ),
    label: 'No Spam Guarantee',
  },
  {
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-success" aria-hidden="true">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <path d="m9 11 3 3L22 4" />
      </svg>
    ),
    label: 'Verified Data',
  },
];

function StarRating({ rating }: { rating: number }) {
  const fullStars = Math.floor(rating);
  const hasHalf = rating - fullStars >= 0.5;
  const emptyStars = 5 - fullStars - (hasHalf ? 1 : 0);

  return (
    <div className="flex items-center gap-px" aria-label={`${rating} out of 5 stars`}>
      {Array.from({ length: fullStars }).map((_, i) => (
        <svg key={`full-${i}`} width="14" height="14" viewBox="0 0 24 24" fill="currentColor" className="text-accent" aria-hidden="true">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </svg>
      ))}
      {hasHalf && (
        <svg width="14" height="14" viewBox="0 0 24 24" className="text-accent" aria-hidden="true">
          <defs>
            <linearGradient id="halfStar">
              <stop offset="50%" stopColor="currentColor" />
              <stop offset="50%" stopColor="transparent" />
            </linearGradient>
          </defs>
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="url(#halfStar)" stroke="currentColor" strokeWidth="1" />
        </svg>
      )}
      {Array.from({ length: emptyStars }).map((_, i) => (
        <svg key={`empty-${i}`} width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1" className="text-gray-300" aria-hidden="true">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </svg>
      ))}
    </div>
  );
}

export function ProjectSidebar({ project }: ProjectSidebarProps) {
  const emi = project.price_min > 0 ? calculateEMI(project.price_min) : 0;
  const advisorName = 'Priya Sharma';
  const advisorRole = 'Property Advisor';
  const advisorRating = 4.8;
  const advisorInitials = 'PS';

  return (
    <aside className="sticky top-[140px] hidden w-[340px] shrink-0 flex-col gap-lg lg:flex">
      {/* Price anchor box */}
      <div className="rounded-md border border-gray-200 bg-white p-xl shadow-card">
        <p className="text-caption uppercase tracking-wider text-gray-500">Starting from</p>
        <p className="mt-xs text-price text-gray-900">
          {formatPrice(project.price_min)}
        </p>
        {emi > 0 && (
          <p className="mt-xs text-sm text-gray-500">
            EMI from {formatPrice(Math.round(emi))}/mo
          </p>
        )}

        {/* CTA buttons */}
        <div className="mt-xl flex flex-col gap-sm">
          <a
            href={`https://wa.me/919876543210?text=${encodeURIComponent(`Hi, I'm interested in ${project.title}. ${project.permalink}`)}`}
            target="_blank"
            rel="noopener noreferrer"
            className={cn(
              'inline-flex h-[44px] items-center justify-center gap-sm rounded-sm font-medium text-white no-underline transition-colors',
              'bg-[#25D366] hover:bg-[#20BD5A] hover:no-underline'
            )}
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
            </svg>
            WhatsApp
          </a>
          <button
            type="button"
            className="inline-flex h-[44px] items-center justify-center rounded-sm bg-brand-primary font-medium text-white transition-colors hover:bg-brand-primary-dark"
          >
            Get Best Price
          </button>
          <button
            type="button"
            className="inline-flex h-[44px] items-center justify-center rounded-sm border border-gray-300 bg-white font-medium text-gray-700 transition-colors hover:bg-gray-50"
          >
            Book Site Visit
          </button>
        </div>
      </div>

      {/* Advisor card */}
      <div className="rounded-md border border-gray-200 bg-white p-xl shadow-card">
        <div className="flex items-center gap-md">
          <div className="flex h-[48px] w-[48px] shrink-0 items-center justify-center rounded-full bg-brand-primary-pale text-base font-semibold text-brand-primary">
            {advisorInitials}
          </div>
          <div className="flex-1">
            <p className="text-base font-semibold text-gray-900">{advisorName}</p>
            <p className="text-caption text-gray-500">{advisorRole}</p>
            <StarRating rating={advisorRating} />
          </div>
        </div>
        <div className="mt-lg flex gap-sm">
          <button
            type="button"
            className="flex-1 rounded-sm border border-gray-300 bg-white px-md py-sm text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
          >
            Contact Advisor
          </button>
          <a
            href={`https://wa.me/919876543210?text=${encodeURIComponent(`Hi ${advisorName}, I'd like to know more about ${project.title}.`)}`}
            target="_blank"
            rel="noopener noreferrer"
            className="flex-1 rounded-sm bg-[#25D366] px-md py-sm text-center text-sm font-medium text-white no-underline transition-colors hover:bg-[#20BD5A] hover:no-underline"
          >
            WhatsApp
          </a>
        </div>
      </div>

      {/* Trust shield */}
      <div className="rounded-md border border-gray-200 bg-white p-xl shadow-card">
        <h4 className="text-sm font-semibold text-gray-900">Why 10Projects?</h4>
        <div className="mt-lg flex flex-col gap-md">
          {trustItems.map((item) => (
            <div key={item.label} className="flex items-center gap-md">
              {item.icon}
              <span className="text-sm text-gray-700">{item.label}</span>
            </div>
          ))}
        </div>
      </div>
    </aside>
  );
}
