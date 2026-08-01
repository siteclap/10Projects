'use client';

import { type ReactElement } from 'react';
import Link from 'next/link';
import { Card } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { ROUTES } from '@/lib/constants/routes';

// ---------------------------------------------------------------------------
// Mock data
// ---------------------------------------------------------------------------

const MOCK_STATS = {
  totalAssessments: 3,
  savedProjects: 7,
  siteVisits: 2,
  leadsCreated: 5,
};

interface RecentActivity {
  id: number;
  type: 'assessment' | 'saved' | 'site_visit' | 'lead';
  description: string;
  timestamp: string;
}

const MOCK_RECENT_ACTIVITY: RecentActivity[] = [
  {
    id: 1,
    type: 'assessment',
    description: 'Completed assessment with 92% accuracy',
    timestamp: '2026-07-30T14:30:00Z',
  },
  {
    id: 2,
    type: 'saved',
    description: 'Saved "Lodha Palava Phase 3" to collection',
    timestamp: '2026-07-29T11:15:00Z',
  },
  {
    id: 3,
    type: 'site_visit',
    description: 'Site visit confirmed for "Arihant Aspire"',
    timestamp: '2026-07-28T09:00:00Z',
  },
  {
    id: 4,
    type: 'lead',
    description: 'Callback requested for "Paradise Sai Mannat"',
    timestamp: '2026-07-27T16:45:00Z',
  },
  {
    id: 5,
    type: 'saved',
    description: 'Saved "Balaji Symphony" to collection',
    timestamp: '2026-07-26T13:20:00Z',
  },
];

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function formatRelativeTime(isoTimestamp: string): string {
  const now = new Date();
  const date = new Date(isoTimestamp);
  const diffMs = now.getTime() - date.getTime();
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays} days ago`;
  if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
  return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
}

function getActivityIcon(type: RecentActivity['type']): ReactElement {
  switch (type) {
    case 'assessment':
      return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
          <path d="M14 2v6h6" />
          <path d="M16 13H8" />
          <path d="M16 17H8" />
          <path d="M10 9H8" />
        </svg>
      );
    case 'saved':
      return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
          <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
        </svg>
      );
    case 'site_visit':
      return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
          <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
          <line x1="16" x2="16" y1="2" y2="6" />
          <line x1="8" x2="8" y1="2" y2="6" />
          <line x1="3" x2="21" y1="10" y2="10" />
        </svg>
      );
    case 'lead':
      return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z" />
        </svg>
      );
  }
}

function getActivityBadgeVariant(type: RecentActivity['type']): 'primary' | 'accent' | 'success' | 'warning' {
  switch (type) {
    case 'assessment':
      return 'primary';
    case 'saved':
      return 'accent';
    case 'site_visit':
      return 'success';
    case 'lead':
      return 'warning';
  }
}

// ---------------------------------------------------------------------------
// Stat Card sub-component
// ---------------------------------------------------------------------------

interface StatCardProps {
  label: string;
  value: number;
  icon: ReactElement;
  href: string;
}

function StatCard({ label, value, icon, href }: StatCardProps) {
  return (
    <Link href={href} className="no-underline hover:no-underline">
      <Card hover className="p-lg">
        <div className="flex items-start justify-between">
          <div>
            <p className="text-sm text-gray-500">{label}</p>
            <p className="mt-1 text-2xl font-bold text-gray-900">{value}</p>
          </div>
          <div className="flex h-[40px] w-[40px] items-center justify-center rounded-lg bg-brand-primary-pale text-brand-primary">
            {icon}
          </div>
        </div>
      </Card>
    </Link>
  );
}

// ---------------------------------------------------------------------------
// DashboardSummary
// ---------------------------------------------------------------------------

export function DashboardSummary() {
  return (
    <div className="space-y-8">
      {/* Stat cards grid */}
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard
          label="Total Assessments"
          value={MOCK_STATS.totalAssessments}
          href={ROUTES.DASHBOARD_ASSESSMENTS}
          icon={
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
              <path d="M14 2v6h6" />
              <path d="M16 13H8" />
              <path d="M16 17H8" />
              <path d="M10 9H8" />
            </svg>
          }
        />
        <StatCard
          label="Saved Projects"
          value={MOCK_STATS.savedProjects}
          href={ROUTES.DASHBOARD_SAVED}
          icon={
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
            </svg>
          }
        />
        <StatCard
          label="Site Visits"
          value={MOCK_STATS.siteVisits}
          href={ROUTES.DASHBOARD_SITE_VISITS}
          icon={
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
              <line x1="16" x2="16" y1="2" y2="6" />
              <line x1="8" x2="8" y1="2" y2="6" />
              <line x1="3" x2="21" y1="10" y2="10" />
            </svg>
          }
        />
        <StatCard
          label="Leads Created"
          value={MOCK_STATS.leadsCreated}
          href={ROUTES.DASHBOARD}
          icon={
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
          }
        />
      </div>

      {/* Recent activity + Quick actions */}
      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {/* Recent activity */}
        <div className="lg:col-span-2">
          <Card className="p-lg">
            <h2 className="text-lg font-semibold text-gray-900">Recent Activity</h2>
            <div className="mt-4 divide-y divide-gray-100">
              {MOCK_RECENT_ACTIVITY.map((activity) => (
                <div key={activity.id} className="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                  <div className="mt-0.5 flex h-[32px] w-[32px] shrink-0 items-center justify-center rounded-full bg-gray-50 text-gray-500">
                    {getActivityIcon(activity.type)}
                  </div>
                  <div className="flex-1">
                    <p className="text-sm text-gray-900">{activity.description}</p>
                    <div className="mt-1 flex items-center gap-2">
                      <Badge variant={getActivityBadgeVariant(activity.type)} size="sm">
                        {activity.type === 'site_visit' ? 'Site Visit' : activity.type.charAt(0).toUpperCase() + activity.type.slice(1)}
                      </Badge>
                      <span className="text-xs text-gray-400">
                        {formatRelativeTime(activity.timestamp)}
                      </span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </Card>
        </div>

        {/* Quick actions */}
        <div>
          <Card className="p-lg">
            <h2 className="text-lg font-semibold text-gray-900">Quick Actions</h2>
            <div className="mt-4 flex flex-col gap-3">
              <Button variant="primary" size="md" asChild>
                <Link href={ROUTES.ASSESSMENT}>New Assessment</Link>
              </Button>
              <Button variant="secondary" size="md" asChild>
                <Link href={ROUTES.CITY}>Browse Projects</Link>
              </Button>
              <Button variant="secondary" size="md" asChild>
                <Link href={ROUTES.DASHBOARD_SAVED}>Saved Projects</Link>
              </Button>
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
}
