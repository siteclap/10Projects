import { cn } from '@/lib/utils/cn';

interface FitScoreBreakdownProps {
  scores: Record<string, number>;
  weights?: Record<string, number>;
}

const categoryLabelMap: Record<string, string> = {
  value_for_money: 'Value for Money',
  location_connectivity: 'Location & Connectivity',
  construction_quality: 'Construction Quality',
  developer_reputation: 'Developer Reputation',
  rera_compliance: 'RERA Compliance',
  possession_timeline: 'Possession Timeline',
  amenities_lifestyle: 'Amenities & Lifestyle',
  floor_plan_design: 'Floor Plan Design',
  appreciation_potential: 'Appreciation Potential',
  rental_yield: 'Rental Yield',
  neighbourhood_safety: 'Neighbourhood Safety',
  water_supply: 'Water Supply',
  power_backup: 'Power Backup',
  natural_light_ventilation: 'Natural Light & Ventilation',
  parking_ratio: 'Parking Ratio',
  green_building: 'Green Building',
  school_proximity: 'School Proximity',
  hospital_proximity: 'Hospital Proximity',
  shopping_proximity: 'Shopping Proximity',
  public_transport: 'Public Transport',
};

function getBarColor(score: number): string {
  if (score >= 80) return 'bg-success';
  if (score >= 60) return 'bg-brand-primary';
  if (score >= 40) return 'bg-accent';
  return 'bg-danger';
}

function getTextColor(score: number): string {
  if (score >= 80) return 'text-success';
  if (score >= 60) return 'text-brand-primary';
  if (score >= 40) return 'text-accent-dark';
  return 'text-danger';
}

export function FitScoreBreakdown({ scores, weights }: FitScoreBreakdownProps) {
  // Sort entries by score descending
  const sortedEntries = Object.entries(scores)
    .filter(([key]) => key in categoryLabelMap)
    .sort(([, a], [, b]) => b - a);

  if (sortedEntries.length === 0) return null;

  return (
    <div className="flex flex-col gap-md">
      {sortedEntries.map(([key, score]) => {
        const label = categoryLabelMap[key] ?? key;
        const clampedScore = Math.min(100, Math.max(0, Math.round(score)));
        const weight = weights?.[key];

        return (
          <div key={key} className="flex flex-col gap-xs">
            <div className="flex items-center justify-between">
              <span className="text-sm text-gray-700">
                {label}
                {weight !== undefined && (
                  <span className="ml-xs text-caption text-gray-400">
                    ({Math.round(weight * 100)}%)
                  </span>
                )}
              </span>
              <span
                className={cn(
                  'text-sm font-semibold tabular-nums',
                  getTextColor(clampedScore)
                )}
              >
                {clampedScore}
              </span>
            </div>
            <div className="h-[6px] w-full overflow-hidden rounded-full bg-gray-100">
              <div
                className={cn(
                  'h-full rounded-full transition-all duration-500',
                  getBarColor(clampedScore)
                )}
                style={{ width: `${clampedScore}%` }}
                role="progressbar"
                aria-valuenow={clampedScore}
                aria-valuemin={0}
                aria-valuemax={100}
                aria-label={`${label}: ${clampedScore} out of 100`}
              />
            </div>
          </div>
        );
      })}
    </div>
  );
}
