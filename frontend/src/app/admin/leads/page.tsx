'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { AdminTopbar } from '@/components/admin/AdminTopbar';
import { StatusBadge } from '@/components/admin/StatusBadge';

interface Lead {
  id: string;
  name: string;
  phone: string;
  email: string | null;
  intent: string;
  status: string;
  createdAt: string;
  project: { title: string } | null;
}

interface PaginationMeta {
  page: number;
  limit: number;
  total: number;
  totalPages: number;
}

const STATUS_OPTIONS = [
  { value: '', label: 'All Statuses' },
  { value: 'NEW', label: 'New' },
  { value: 'CONTACTED', label: 'Contacted' },
  { value: 'QUALIFIED', label: 'Qualified' },
  { value: 'SITE_VISIT', label: 'Site Visit' },
  { value: 'CONVERTED', label: 'Converted' },
  { value: 'LOST', label: 'Lost' },
];

export default function LeadListPage() {
  const [leads, setLeads] = useState<Lead[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [pagination, setPagination] = useState<PaginationMeta>({
    page: 1,
    limit: 20,
    total: 0,
    totalPages: 1,
  });

  useEffect(() => {
    fetchLeads();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagination.page, statusFilter]);

  async function fetchLeads() {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page: String(pagination.page),
        perPage: String(pagination.limit),
      });
      if (search) params.set('search', search);
      if (statusFilter) params.set('status', statusFilter);

      const res = await fetch(`/api/admin/leads?${params}`);
      if (res.ok) {
        const data = await res.json();
        setLeads(data.leads || []);
        if (data.pagination) {
          setPagination((prev) => ({ ...prev, ...data.pagination }));
        }
      }
    } catch {
      // API not available
    } finally {
      setLoading(false);
    }
  }

  function handleSearchSubmit(e: React.FormEvent) {
    e.preventDefault();
    setPagination((prev) => ({ ...prev, page: 1 }));
    fetchLeads();
  }

  async function handleExportCSV() {
    try {
      const params = new URLSearchParams();
      if (statusFilter) params.set('status', statusFilter);
      if (search) params.set('search', search);

      const res = await fetch(`/api/admin/leads/export?${params}`);
      if (res.ok) {
        const blob = await res.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `leads-export-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
      }
    } catch {
      // Export failed
    }
  }

  return (
    <>
      <AdminTopbar title="Leads" />

      <div className="p-2xl">
        {/* Filter row */}
        <div className="mb-lg flex flex-wrap items-center gap-md">
          <select
            value={statusFilter}
            onChange={(e) => {
              setStatusFilter(e.target.value);
              setPagination((prev) => ({ ...prev, page: 1 }));
            }}
            className="h-[36px] rounded-sm border border-gray-300 px-md text-sm text-gray-700 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
          >
            {STATUS_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </select>

          <form onSubmit={handleSearchSubmit} className="flex items-center gap-sm">
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by name, phone, or email..."
              className="h-[36px] w-[280px] rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
            />
            <button
              type="submit"
              className="h-[36px] rounded-sm border border-gray-300 bg-white px-md text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
            >
              Search
            </button>
          </form>

          <div className="flex-1" />

          <button
            type="button"
            onClick={handleExportCSV}
            className="inline-flex h-[36px] items-center gap-sm rounded-sm border border-gray-300 bg-white px-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" x2="12" y1="15" y2="3" />
            </svg>
            Export CSV
          </button>
        </div>

        {/* Table */}
        <div className="rounded-md border border-gray-200 bg-white shadow-xs">
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
                    Email
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
                  Array.from({ length: 8 }).map((_, i) => (
                    <tr key={i} className="border-b border-gray-100">
                      {Array.from({ length: 7 }).map((_, j) => (
                        <td key={j} className="px-xl py-md">
                          <div className="h-[16px] w-[80px] animate-pulse rounded-sm bg-gray-200" />
                        </td>
                      ))}
                    </tr>
                  ))
                ) : leads.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="px-xl py-3xl text-center text-sm text-gray-500">
                      No leads found.
                    </td>
                  </tr>
                ) : (
                  leads.map((lead) => (
                    <tr
                      key={lead.id}
                      className="border-b border-gray-100 transition-colors hover:bg-gray-50"
                    >
                      <td className="px-xl py-md text-sm font-medium text-gray-900">
                        <Link
                          href={`/admin/leads/${lead.id}`}
                          className="text-gray-900 no-underline hover:text-brand-primary hover:underline"
                        >
                          {lead.name}
                        </Link>
                      </td>
                      <td className="px-xl py-md text-sm text-gray-600">
                        {lead.phone}
                      </td>
                      <td className="px-xl py-md text-sm text-gray-600">
                        {lead.email || '—'}
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

          {/* Pagination */}
          {pagination.totalPages > 1 && (
            <div className="flex items-center justify-between border-t border-gray-200 px-xl py-md">
              <p className="text-sm text-gray-500">
                Showing {(pagination.page - 1) * pagination.limit + 1} to{' '}
                {Math.min(pagination.page * pagination.limit, pagination.total)} of{' '}
                {pagination.total} leads
              </p>
              <div className="flex items-center gap-xs">
                <button
                  type="button"
                  onClick={() =>
                    setPagination((prev) => ({ ...prev, page: prev.page - 1 }))
                  }
                  disabled={pagination.page <= 1}
                  className="flex h-[32px] items-center rounded-sm border border-gray-300 px-md text-sm text-gray-700 transition-colors hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none"
                >
                  Previous
                </button>
                {Array.from({ length: pagination.totalPages }, (_, i) => i + 1)
                  .filter((pageNum) => {
                    // Show first page, last page, and pages around current
                    return (
                      pageNum === 1 ||
                      pageNum === pagination.totalPages ||
                      Math.abs(pageNum - pagination.page) <= 1
                    );
                  })
                  .map((pageNum, idx, arr) => {
                    const showEllipsis = idx > 0 && pageNum - arr[idx - 1] > 1;
                    return (
                      <span key={pageNum} className="flex items-center gap-xs">
                        {showEllipsis && (
                          <span className="px-xs text-sm text-gray-400">...</span>
                        )}
                        <button
                          type="button"
                          onClick={() =>
                            setPagination((prev) => ({ ...prev, page: pageNum }))
                          }
                          className={`flex h-[32px] w-[32px] items-center justify-center rounded-sm text-sm font-medium transition-colors ${
                            pageNum === pagination.page
                              ? 'bg-brand-primary text-white'
                              : 'border border-gray-300 text-gray-700 hover:bg-gray-50'
                          }`}
                        >
                          {pageNum}
                        </button>
                      </span>
                    );
                  })}
                <button
                  type="button"
                  onClick={() =>
                    setPagination((prev) => ({ ...prev, page: prev.page + 1 }))
                  }
                  disabled={pagination.page >= pagination.totalPages}
                  className="flex h-[32px] items-center rounded-sm border border-gray-300 px-md text-sm text-gray-700 transition-colors hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none"
                >
                  Next
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </>
  );
}
