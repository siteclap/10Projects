'use client';

import { useState } from 'react';
import Link from 'next/link';
import { Card } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { ROUTES } from '@/lib/constants/routes';
import type { SiteVisitStatus } from '@/lib/api/site-visits';

// ---------------------------------------------------------------------------
// Mock data
// ---------------------------------------------------------------------------

interface SiteVisitEntry {
  id: number;
  project_id: number;
  project_title: string;
  project_location: string;
  scheduled_date: string;
  time_slot: string;
  status: SiteVisitStatus;
  created_at: string;
}

const MOCK_SITE_VISITS: SiteVisitEntry[] = [
  {
    id: 1,
    project_id: 102,
    project_title: 'Arihant Aspire',
    project_location: 'Panvel',
    scheduled_date: '2026-08-05',
    time_slot: '10:00 AM - 12:00 PM',
    status: 'confirmed',
    created_at: '2026-07-28T09:00:00Z',
  },
  {
    id: 2,
    project_id: 103,
    project_title: 'Paradise Sai Mannat',
    project_location: 'Kharghar',
    scheduled_date: '2026-08-10',
    time_slot: '2:00 PM - 4:00 PM',
    status: 'pending',
    created_at: '2026-07-30T14:30:00Z',
  },
  {
    id: 3,
    project_id: 104,
    project_title: 'Balaji Symphony',
    project_location: 'Ulwe',
    scheduled_date: '2026-07-20',
    time_slot: '11:00 AM - 1:00 PM',
    status: 'completed',
    created_at: '2026-07-15T10:00:00Z',
  },
  {
    id: 4,
    project_id: 105,
    project_title: 'Sai World Empire',
    project_location: 'Kharghar',
    scheduled_date: '2026-07-10',
    time_slot: '10:00 AM - 12:00 PM',
    status: 'cancelled',
    created_at: '2026-07-05T16:00:00Z',
  },
];

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function formatVisitDate(dateString: string): string {
  return new Date(dateString + 'T00:00:00').toLocaleDateString('en-IN', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function getStatusBadgeVariant(status: SiteVisitStatus): 'success' | 'warning' | 'primary' | 'default' {
  switch (status) {
    case 'confirmed':
      return 'success';
    case 'pending':
      return 'warning';
    case 'completed':
      return 'primary';
    case 'cancelled':
      return 'default';
  }
}

function getStatusLabel(status: SiteVisitStatus): string {
  switch (status) {
    case 'confirmed':
      return 'Confirmed';
    case 'pending':
      return 'Pending';
    case 'completed':
      return 'Completed';
    case 'cancelled':
      return 'Cancelled';
  }
}

function isUpcoming(visit: SiteVisitEntry): boolean {
  return (
    (visit.status === 'pending' || visit.status === 'confirmed') &&
    new Date(visit.scheduled_date + 'T23:59:59') >= new Date()
  );
}

function canCancel(status: SiteVisitStatus): boolean {
  return status === 'pending' || status === 'confirmed';
}

// ---------------------------------------------------------------------------
// Component
// ---------------------------------------------------------------------------

export function SiteVisitsList() {
  const [visits, setVisits] = useState(MOCK_SITE_VISITS);

  const upcomingVisits = visits.filter(isUpcoming);
  const pastVisits = visits.filter((v) => !isUpcoming(v));

  function handleCancel(visitId: number) {
    setVisits((prev) =>
      prev.map((v) =>
        v.id === visitId ? { ...v, status: 'cancelled' as SiteVisitStatus } : v
      )
    );
  }

  // Empty state
  if (visits.length === 0) {
    return (
      <Card className="flex flex-col items-center justify-center px-lg py-16 text-center">
        <div className="flex h-[64px] w-[64px] items-center justify-center rounded-full bg-gray-100">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" className="text-gray-400" aria-hidden="true">
            <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
            <line x1="16" x2="16" y1="2" y2="6" />
            <line x1="8" x2="8" y1="2" y2="6" />
            <line x1="3" x2="21" y1="10" y2="10" />
          </svg>
        </div>
        <h3 className="mt-4 text-lg font-semibold text-gray-900">No site visits scheduled</h3>
        <p className="mt-2 max-w-sm text-sm text-gray-500">
          Browse projects and schedule a free site visit to see them in person
          with an expert advisor.
        </p>
        <Button variant="primary" size="md" className="mt-6" asChild>
          <Link href={ROUTES.CITY}>Browse Projects</Link>
        </Button>
      </Card>
    );
  }

  return (
    <div className="space-y-8">
      {/* Upcoming visits */}
      {upcomingVisits.length > 0 && (
        <div className="space-y-4">
          <h2 className="text-lg font-semibold text-gray-900">
            Upcoming Visits ({upcomingVisits.length})
          </h2>
          {upcomingVisits.map((visit) => (
            <SiteVisitCard
              key={visit.id}
              visit={visit}
              onCancel={handleCancel}
            />
          ))}
        </div>
      )}

      {/* Past visits */}
      {pastVisits.length > 0 && (
        <div className="space-y-4">
          <h2 className="text-lg font-semibold text-gray-900">
            Past Visits ({pastVisits.length})
          </h2>
          {pastVisits.map((visit) => (
            <SiteVisitCard
              key={visit.id}
              visit={visit}
              onCancel={handleCancel}
            />
          ))}
        </div>
      )}
    </div>
  );
}

// ---------------------------------------------------------------------------
// Sub-component
// ---------------------------------------------------------------------------

interface SiteVisitCardProps {
  visit: SiteVisitEntry;
  onCancel: (id: number) => void;
}

function SiteVisitCard({ visit, onCancel }: SiteVisitCardProps) {
  const locationSlug = visit.project_location.toLowerCase().replace(/\s+/g, '-');

  return (
    <Card className="p-lg">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {/* Left: visit info */}
        <div className="flex-1 space-y-2">
          <div className="flex flex-wrap items-center gap-2">
            <Link
              href={ROUTES.PROJECT(locationSlug, visit.project_title.toLowerCase().replace(/\s+/g, '-'))}
              className="text-base font-semibold text-gray-900 no-underline hover:text-brand-primary hover:no-underline"
            >
              {visit.project_title}
            </Link>
            <Badge variant={getStatusBadgeVariant(visit.status)} size="sm">
              {getStatusLabel(visit.status)}
            </Badge>
          </div>

          <div className="flex flex-wrap items-center gap-4 text-sm text-gray-500">
            <span className="flex items-center gap-1.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
              {visit.project_location}
            </span>
            <span className="flex items-center gap-1.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                <line x1="16" x2="16" y1="2" y2="6" />
                <line x1="8" x2="8" y1="2" y2="6" />
                <line x1="3" x2="21" y1="10" y2="10" />
              </svg>
              {formatVisitDate(visit.scheduled_date)}
            </span>
            <span className="flex items-center gap-1.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
              {visit.time_slot}
            </span>
          </div>
        </div>

        {/* Right: actions */}
        {canCancel(visit.status) && (
          <Button
            variant="ghost"
            size="sm"
            className="text-danger hover:bg-danger-light hover:text-danger"
            onClick={() => onCancel(visit.id)}
          >
            Cancel Visit
          </Button>
        )}
      </div>
    </Card>
  );
}
