'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { AdminTopbar } from '@/components/admin/AdminTopbar';
import { StatusBadge } from '@/components/admin/StatusBadge';

interface DashboardStats {
  totalProjects: number;
  totalLeads: number;
  newLeadsToday: number;
  publishedPages: number;
}

interface RecentLead {
  id: string;
  name: string;
  phone: string;
  project: { title: string } | null;
  intent: string;
  status: string;
  createdAt: string;
}

function StatCard({
  icon,
  value,
  label,
  bgColor,
  iconColor,
  loading,
}: {
  icon: React.ReactNode;
  value: number;
  label: string;
  bgColor: string;
  iconColor: string;
  loading: boolean;
}) {
  return (
    <div className="rounded-md border border-gray-200 bg-white p-xl shadow-xs">
      <div className="flex items-center gap-lg">
        <div
          className={`flex h-[48px] w-[48px] flex-shrink-0 items-center justify-center rounded-md ${bgColor}`}
        >
          <span className={iconColor}>{icon}</span>
        </div>
        <div>
          {loading ? (
            <div className="mb-xs h-[28px] w-[60px] animate-pulse rounded-sm bg-gray-200" />
          ) : (
            <p className="text-h2 text-gray-900 tabular-nums">{value}</p>
          )}
          <p className="text-sm text-gray-500">{label}</p>
        </div>
      </div>
    </div>
  );
}

export default function AdminDashboardPage() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [leads, setLeads] = useState<RecentLead[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchDashboard() {
      try {
        const [statsRes, leadsRes] = await Promise.all([
          fetch('/api/admin/stats'),
          fetch('/api/admin/leads?limit=10&sort=date&order=desc'),
        ]);

        if (statsRes.ok) {
          const statsData = await statsRes.json();
          setStats(statsData);
        }
        if (leadsRes.ok) {
          const leadsData = await leadsRes.json();
          setLeads(leadsData.leads || []);
        }
      } catch {
        // API not available yet, use empty state
      } finally {
        setLoading(false);
      }
    }

    fetchDashboard();
  }, []);

  const statCards = [
    {
      icon: (
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
          <polyline points="14 2 14 8 20 8" />
        </svg>
      ),
      value: stats?.totalProjects ?? 0,
      label: 'Total Projects',
      bgColor: 'bg-brand-primary-bg',
      iconColor: 'text-brand-primary',
    },
    {
      icon: (
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
          <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
          <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        </svg>
      ),
      value: stats?.totalLeads ?? 0,
      label: 'Total Leads',
      bgColor: 'bg-accent-pale',
      iconColor: 'text-accent-dark',
    },
    {
      icon: (
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
        </svg>
      ),
      value: stats?.newLeadsToday ?? 0,
      label: 'New Leads (Today)',
      bgColor: 'bg-success-bg',
      iconColor: 'text-success',
    },
    {
      icon: (
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M12 20h9" />
          <path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838.838-2.872a2 2 0 0 1 .506-.855z" />
        </svg>
      ),
      value: stats?.publishedPages ?? 0,
      label: 'Published Pages',
      bgColor: 'bg-brand-primary-pale',
      iconColor: 'text-brand-primary-dark',
    },
  ];

  return (
    <>
      <AdminTopbar title="Dashboard" />

      <div className="p-2xl">
        {/* Stats grid */}
        <div className="mb-2xl grid grid-cols-1 gap-xl sm:grid-cols-2 lg:grid-cols-4">
          {statCards.map((card) => (
            <StatCard
              key={card.label}
              icon={card.icon}
              value={card.value}
              label={card.label}
              bgColor={card.bgColor}
              iconColor={card.iconColor}
              loading={loading}
            />
          ))}
        </div>

        {/* Quick actions */}
        <div className="mb-2xl flex items-center gap-md">
          <Link
            href="/admin/projects/new"
            className="inline-flex items-center gap-sm rounded-sm bg-brand-primary px-lg py-sm text-sm font-medium text-white no-underline transition-colors hover:bg-brand-primary-dark hover:no-underline"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M12 5v14" />
              <path d="M5 12h14" />
            </svg>
            Add New Project
          </Link>
          <Link
            href="/admin/leads"
            className="inline-flex items-center gap-sm rounded-sm border border-gray-300 bg-white px-lg py-sm text-sm font-medium text-gray-700 no-underline transition-colors hover:bg-gray-50 hover:no-underline"
          >
            View All Leads
          </Link>
        </div>

        {/* Recent Leads table */}
        <div className="rounded-md border border-gray-200 bg-white shadow-xs">
          <div className="border-b border-gray-200 px-xl py-lg">
            <h2 className="text-h4 text-gray-900">Recent Leads</h2>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-gray-200 bg-gray-50">
                  <th className="px-xl py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Name
                  </th>
                  <th className="px-xl py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Phone
                  </th>
                  <th className="px-xl py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Project
                  </th>
                  <th className="px-xl py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Intent
                  </th>
                  <th className="px-xl py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Status
                  </th>
                  <th className="px-xl py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Date
                  </th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  Array.from({ length: 5 }).map((_, i) => (
                    <tr key={i} className="border-b border-gray-100">
                      {Array.from({ length: 6 }).map((_, j) => (
                        <td key={j} className="px-xl py-md">
                          <div className="h-[16px] w-[80px] animate-pulse rounded-sm bg-gray-200" />
                        </td>
                      ))}
                    </tr>
                  ))
                ) : leads.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="px-xl py-3xl text-center text-sm text-gray-500">
                      No leads found. They will appear here once customers submit inquiries.
                    </td>
                  </tr>
                ) : (
                  leads.map((lead) => (
                    <tr
                      key={lead.id}
                      className="border-b border-gray-100 transition-colors hover:bg-gray-50"
                    >
                      <td className="px-xl py-md text-sm font-medium text-gray-900">
                        {lead.name}
                      </td>
                      <td className="px-xl py-md text-sm text-gray-600">
                        {lead.phone}
                      </td>
                      <td className="px-xl py-md text-sm text-gray-600">
                        {lead.project?.title || '—'}
                      </td>
                      <td className="px-xl py-md text-sm text-gray-600">
                        {lead.intent}
                      </td>
                      <td className="px-xl py-md">
                        <StatusBadge status={lead.status} />
                      </td>
                      <td className="px-xl py-md text-sm text-gray-500">
                        {new Date(lead.createdAt).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </>
  );
}
