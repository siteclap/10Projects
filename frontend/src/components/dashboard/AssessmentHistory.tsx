'use client';

import Link from 'next/link';
import { Card } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { ROUTES } from '@/lib/constants/routes';

// ---------------------------------------------------------------------------
// Mock data
// ---------------------------------------------------------------------------

interface AssessmentEntry {
  id: number;
  session_uuid: string;
  date: string;
  accuracy: number;
  matches: number;
  city: string;
  phases_completed: number;
}

const MOCK_ASSESSMENTS: AssessmentEntry[] = [
  {
    id: 1,
    session_uuid: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
    date: '2026-07-30T14:30:00Z',
    accuracy: 92,
    matches: 10,
    city: 'Navi Mumbai',
    phases_completed: 4,
  },
  {
    id: 2,
    session_uuid: 'b2c3d4e5-f6a7-8901-bcde-f12345678901',
    date: '2026-07-15T10:00:00Z',
    accuracy: 78,
    matches: 10,
    city: 'Navi Mumbai',
    phases_completed: 3,
  },
  {
    id: 3,
    session_uuid: 'c3d4e5f6-a7b8-9012-cdef-123456789012',
    date: '2026-06-20T16:45:00Z',
    accuracy: 65,
    matches: 10,
    city: 'Navi Mumbai',
    phases_completed: 2,
  },
];

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function formatDate(isoTimestamp: string): string {
  return new Date(isoTimestamp).toLocaleDateString('en-IN', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function getAccuracyVariant(accuracy: number): 'success' | 'primary' | 'warning' | 'default' {
  if (accuracy >= 90) return 'success';
  if (accuracy >= 75) return 'primary';
  if (accuracy >= 60) return 'warning';
  return 'default';
}

// ---------------------------------------------------------------------------
// Component
// ---------------------------------------------------------------------------

export function AssessmentHistory() {
  const assessments = MOCK_ASSESSMENTS;

  // Empty state
  if (assessments.length === 0) {
    return (
      <Card className="flex flex-col items-center justify-center px-lg py-16 text-center">
        <div className="flex h-[64px] w-[64px] items-center justify-center rounded-full bg-gray-100">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" className="text-gray-400" aria-hidden="true">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
            <path d="M14 2v6h6" />
            <path d="M16 13H8" />
            <path d="M16 17H8" />
            <path d="M10 9H8" />
          </svg>
        </div>
        <h3 className="mt-4 text-lg font-semibold text-gray-900">No assessments yet</h3>
        <p className="mt-2 max-w-sm text-sm text-gray-500">
          Take your first AI assessment to get personalised project recommendations
          matched to your preferences.
        </p>
        <Button variant="primary" size="md" className="mt-6" asChild>
          <Link href={ROUTES.ASSESSMENT}>Start Assessment</Link>
        </Button>
      </Card>
    );
  }

  return (
    <div className="space-y-4">
      {assessments.map((assessment) => (
        <Card key={assessment.id} className="p-lg">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            {/* Left: assessment info */}
            <div className="flex-1 space-y-2">
              <div className="flex flex-wrap items-center gap-2">
                <h3 className="text-base font-semibold text-gray-900">
                  {assessment.city} Assessment
                </h3>
                <Badge variant={getAccuracyVariant(assessment.accuracy)} size="sm">
                  {assessment.accuracy}% Accuracy
                </Badge>
              </div>

              <div className="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                <span className="flex items-center gap-1.5">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                    <line x1="16" x2="16" y1="2" y2="6" />
                    <line x1="8" x2="8" y1="2" y2="6" />
                    <line x1="3" x2="21" y1="10" y2="10" />
                  </svg>
                  {formatDate(assessment.date)}
                </span>
                <span className="flex items-center gap-1.5">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <line x1="19" x2="19" y1="8" y2="14" />
                    <line x1="22" x2="16" y1="11" y2="11" />
                  </svg>
                  {assessment.matches} matches found
                </span>
                <span className="flex items-center gap-1.5">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                    <path d="M12 2v20" />
                    <path d="M2 12h20" />
                  </svg>
                  {assessment.phases_completed} phases completed
                </span>
              </div>
            </div>

            {/* Right: CTA */}
            <Button variant="secondary" size="sm" asChild>
              <Link href={ROUTES.RESULTS(assessment.session_uuid)}>
                View Results
              </Link>
            </Button>
          </div>
        </Card>
      ))}
    </div>
  );
}
