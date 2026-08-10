'use client';

import { useState, type ReactNode } from 'react';

interface AmenityGridProps {
  amenities: string[];
  initialCount?: number;
}

const amenityIconMap: Record<string, ReactNode> = {
  'Swimming Pool': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M2 20c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1 .6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1" />
      <path d="M2 16c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1 .6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1" />
      <path d="M15.4 12.6 14 10l-3.6 6" />
      <path d="M13.5 4.1a1 1 0 0 0-1 1.7" />
      <path d="M6 8a2 2 0 1 1 4 0c0 .6-.4 1.1-.8 1.5l-1.7 1.9h2.5" />
    </svg>
  ),
  'Gym': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M6.5 6.5h11" />
      <path d="M6.5 17.5h11" />
      <path d="M4 10V7a2 2 0 0 1 4 0v10a2 2 0 0 1-4 0v-3" />
      <path d="M20 10V7a2 2 0 0 0-4 0v10a2 2 0 0 0 4 0v-3" />
      <path d="M2 12h2" />
      <path d="M20 12h2" />
    </svg>
  ),
  'Parking': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <rect width="18" height="18" x="3" y="3" rx="2" />
      <path d="M9 17V7h4a3 3 0 0 1 0 6H9" />
    </svg>
  ),
  'Garden': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M12 10a6 6 0 0 0 6-6H6a6 6 0 0 0 6 6Z" />
      <path d="M12 10v12" />
      <path d="M7 22h10" />
    </svg>
  ),
  'Clubhouse': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
      <polyline points="9 22 9 12 15 12 15 22" />
    </svg>
  ),
  'Playground': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="1" />
      <path d="M20.2 20.2c2.04-2.03.02-7.36-4.5-11.9-4.54-4.52-9.87-6.54-11.9-4.5-2.04 2.03-.02 7.36 4.5 11.9 4.54 4.52 9.87 6.54 11.9 4.5Z" />
      <path d="M15.7 15.7c4.52-4.54 6.54-9.87 4.5-11.9-2.03-2.04-7.36-.02-11.9 4.5-4.52 4.54-6.54 9.87-4.5 11.9 2.03 2.04 7.36.02 11.9-4.5Z" />
    </svg>
  ),
  'Security': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
    </svg>
  ),
  'Power Backup': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z" />
    </svg>
  ),
  'Lift': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <rect width="18" height="18" x="3" y="3" rx="2" />
      <path d="M12 3v18" />
      <path d="m8 8-2 2 2 2" />
      <path d="m16 12 2 2-2 2" />
    </svg>
  ),
  'Jogging Track': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <circle cx="16.5" cy="3.5" r="1.5" />
      <path d="M7 21h2l2-5 3 1v-6l4.5-2.5L17 5l-4 2-3 1-2.5 3.5L9 14" />
    </svg>
  ),
  'Indoor Games': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
      <circle cx="8.5" cy="8.5" r="1.5" />
      <circle cx="15.5" cy="8.5" r="1.5" />
      <circle cx="15.5" cy="15.5" r="1.5" />
      <circle cx="8.5" cy="15.5" r="1.5" />
    </svg>
  ),
  'Landscaped Garden': (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M12 10a6 6 0 0 0 6-6H6a6 6 0 0 0 6 6Z" />
      <path d="M12 10v12" />
      <path d="M7 22h10" />
    </svg>
  ),
};

function DefaultIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="10" />
      <path d="m9 12 2 2 4-4" />
    </svg>
  );
}

export function AmenityGrid({ amenities, initialCount = 8 }: AmenityGridProps) {
  const [expanded, setExpanded] = useState(false);

  if (!amenities || amenities.length === 0) return null;

  const showToggle = amenities.length > initialCount;
  const visible = expanded ? amenities : amenities.slice(0, initialCount);

  return (
    <div>
      <div className="flex flex-wrap gap-sm">
        {visible.map((amenity) => (
          <div
            key={amenity}
            className="flex items-center gap-xs rounded-full border border-gray-200 bg-gray-50 px-md py-xs"
          >
            <span className="shrink-0 text-brand-primary">
              {amenityIconMap[amenity] ?? <DefaultIcon />}
            </span>
            <span className="text-caption text-gray-700">{amenity}</span>
          </div>
        ))}
      </div>

      {showToggle && (
        <button
          type="button"
          onClick={() => setExpanded(!expanded)}
          className="mt-md inline-flex items-center gap-xs text-sm font-medium text-brand-primary transition-colors hover:text-brand-primary-dark"
        >
          {expanded ? 'Show Less' : `See All ${amenities.length} Amenities`}
          <svg
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            className={`transition-transform ${expanded ? 'rotate-180' : ''}`}
            aria-hidden="true"
          >
            <path d="m6 9 6 6 6-6" />
          </svg>
        </button>
      )}
    </div>
  );
}
