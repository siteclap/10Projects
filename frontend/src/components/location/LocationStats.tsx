import type { Location } from '@/lib/types/location';

interface LocationStatsProps {
  location: Location;
}

export function LocationStats({ location }: LocationStatsProps) {
  return (
    <div className="flex flex-col gap-3xl">
      {/* Key stats grid */}
      <div className="grid grid-cols-2 gap-lg sm:grid-cols-4">
        <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
          <p className="text-caption uppercase tracking-wider text-gray-500">
            Projects
          </p>
          <p className="mt-xs text-h3 font-bold text-gray-900">
            {location.project_count}
          </p>
        </div>
        <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
          <p className="text-caption uppercase tracking-wider text-gray-500">
            Avg Price/Sq Ft
          </p>
          <p className="mt-xs text-h3 font-bold text-gray-900">
            {'\u20B9'}{location.avg_price_psf.toLocaleString('en-IN')}
          </p>
        </div>
        <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
          <p className="text-caption uppercase tracking-wider text-gray-500">
            Livability Score
          </p>
          <p className="mt-xs text-h3 font-bold text-success">
            {location.livability_score}/100
          </p>
        </div>
        <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
          <p className="text-caption uppercase tracking-wider text-gray-500">
            Connectivity Score
          </p>
          <p className="mt-xs text-h3 font-bold text-brand-primary">
            {location.connectivity_score}/100
          </p>
        </div>
      </div>

      {/* Infrastructure section */}
      <div>
        <h3 className="text-h3 text-gray-900">Infrastructure & Connectivity</h3>
        <div className="mt-xl grid grid-cols-1 gap-lg sm:grid-cols-3">
          {/* Railway */}
          <div className="flex items-start gap-md rounded-sm border border-gray-100 bg-white p-lg">
            <div className="flex h-[40px] w-[40px] shrink-0 items-center justify-center rounded-sm bg-brand-primary-pale">
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="text-brand-primary"
                aria-hidden="true"
              >
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                <line x1="4" x2="4" y1="22" y2="15" />
              </svg>
            </div>
            <div>
              <p className="text-caption uppercase tracking-wider text-gray-500">
                Nearest Railway
              </p>
              <p className="mt-xs text-base font-medium text-gray-900">
                {location.nearest_railway}
              </p>
              <p className="mt-xs text-sm text-gray-500">
                {location.railway_distance_km} km away
              </p>
            </div>
          </div>

          {/* Metro */}
          <div className="flex items-start gap-md rounded-sm border border-gray-100 bg-white p-lg">
            <div className="flex h-[40px] w-[40px] shrink-0 items-center justify-center rounded-sm bg-accent-pale">
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="text-accent-dark"
                aria-hidden="true"
              >
                <rect width="16" height="16" x="4" y="3" rx="2" />
                <path d="M4 11h16" />
                <path d="M12 3v8" />
                <path d="m8 19-2 3" />
                <path d="m18 22-2-3" />
                <path d="M8 15h0" />
                <path d="M16 15h0" />
              </svg>
            </div>
            <div>
              <p className="text-caption uppercase tracking-wider text-gray-500">
                Nearest Metro
              </p>
              <p className="mt-xs text-base font-medium text-gray-900">
                {location.nearest_metro ?? 'Upcoming'}
              </p>
              <p className="mt-xs text-sm text-gray-500">
                {location.metro_distance_km != null
                  ? `${location.metro_distance_km} km away`
                  : 'Under construction'}
              </p>
            </div>
          </div>

          {/* Highway */}
          <div className="flex items-start gap-md rounded-sm border border-gray-100 bg-white p-lg">
            <div className="flex h-[40px] w-[40px] shrink-0 items-center justify-center rounded-sm bg-success-light">
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="text-success"
                aria-hidden="true"
              >
                <path d="M4 19L20 19" />
                <path d="M4 15L20 15" />
                <path d="M6 11L18 11" />
                <path d="M8 7L16 7" />
                <path d="M10 3L14 3" />
              </svg>
            </div>
            <div>
              <p className="text-caption uppercase tracking-wider text-gray-500">
                Nearest Highway
              </p>
              <p className="mt-xs text-base font-medium text-gray-900">
                {location.nearest_highway}
              </p>
              <p className="mt-xs text-sm text-gray-500">
                {location.highway_distance_km} km away
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Upcoming infrastructure */}
      {location.upcoming_infra.length > 0 && (
        <div>
          <h3 className="text-h3 text-gray-900">Upcoming Infrastructure</h3>
          <ul className="mt-xl flex flex-col gap-md">
            {location.upcoming_infra.map((item) => (
              <li key={item} className="flex items-start gap-md">
                <div className="mt-[2px] flex h-[20px] w-[20px] shrink-0 items-center justify-center rounded-full bg-success-light">
                  <svg
                    width="12"
                    height="12"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="3"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="text-success"
                    aria-hidden="true"
                  >
                    <path d="M20 6 9 17l-5-5" />
                  </svg>
                </div>
                <span className="text-base text-gray-700">{item}</span>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
