import { ResultCard } from './ResultCard';
import type { RecommendationResult } from '@/lib/types/recommendation';

interface ResultsListProps {
  results: RecommendationResult[];
  totalCandidates?: number;
}

export function ResultsList({ results, totalCandidates }: ResultsListProps) {
  const total = totalCandidates || results.length;

  return (
    <div className="flex flex-col gap-xl">
      {/* Count header */}
      <div className="flex items-center justify-between">
        <p className="text-sm text-gray-500">
          Showing <span className="font-semibold text-gray-900">{results.length}</span> of{' '}
          <span className="font-semibold text-gray-900">{total}</span> matches
        </p>
      </div>

      {/* Results */}
      <div className="flex flex-col gap-lg">
        {results.map((result, index) => (
          <ResultCard key={result.project_id} result={result} rank={index + 1} />
        ))}
      </div>
    </div>
  );
}
