'use client';

import { useEffect, useState, type FormEvent } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { AdminTopbar } from '@/components/admin/AdminTopbar';
import { StatusBadge } from '@/components/admin/StatusBadge';

interface LeadDetail {
  id: string;
  name: string;
  phone: string;
  email: string;
  source: string;
  projectId: string;
  projectTitle: string;
  status: string;
  intent: string;
  notes: string;
  createdAt: string;
  updatedAt: string;
}

const STATUS_OPTIONS = [
  'NEW',
  'CONTACTED',
  'QUALIFIED',
  'SITE_VISIT',
  'CONVERTED',
  'LOST',
];

export default function LeadDetailPage() {
  const params = useParams();
  const leadId = params.id as string;

  const [lead, setLead] = useState<LeadDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');

  /* Editable fields */
  const [status, setStatus] = useState('');
  const [notes, setNotes] = useState('');

  useEffect(() => {
    async function fetchLead() {
      try {
        const res = await fetch(`/api/admin/leads/${leadId}`);
        if (!res.ok) throw new Error('Lead not found');
        const data = await res.json();
        setLead(data);
        setStatus(data.status || 'NEW');
        setNotes(data.notes || '');
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to load lead');
      } finally {
        setLoading(false);
      }
    }
    fetchLead();
  }, [leadId]);

  async function handleSave(e: FormEvent) {
    e.preventDefault();
    setSaving(true);
    setMessage('');

    try {
      const res = await fetch(`/api/admin/leads/${leadId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status, notes }),
      });

      if (res.ok) {
        const updated = await res.json();
        setLead((prev) => (prev ? { ...prev, ...updated } : prev));
        setMessage('Lead updated successfully.');
        setTimeout(() => setMessage(''), 3000);
      } else {
        setMessage('Failed to update lead.');
      }
    } catch {
      setMessage('Failed to update lead.');
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return (
      <>
        <AdminTopbar title="Lead Detail" />
        <div className="p-2xl">
          <div className="h-[400px] animate-pulse rounded-md bg-gray-200" />
        </div>
      </>
    );
  }

  if (error || !lead) {
    return (
      <>
        <AdminTopbar title="Lead Detail" />
        <div className="p-2xl">
          <div className="rounded-md border border-danger bg-danger-light px-xl py-lg text-sm text-danger">
            {error || 'Lead not found'}
          </div>
          <Link
            href="/admin/leads"
            className="mt-lg inline-flex text-sm text-brand-primary"
          >
            Back to Leads
          </Link>
        </div>
      </>
    );
  }

  return (
    <>
      <AdminTopbar title="Lead Detail" />

      <div className="p-2xl">
        {/* Back link */}
        <Link
          href="/admin/leads"
          className="mb-xl inline-flex items-center gap-xs text-sm text-gray-500 no-underline hover:text-gray-700 hover:no-underline"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <path d="m15 18-6-6 6-6" />
          </svg>
          Back to Leads
        </Link>

        {/* Success message */}
        {message && (
          <div
            className={`mb-xl rounded-sm px-lg py-md text-sm ${
              message.includes('success')
                ? 'border border-success bg-success-bg text-success'
                : 'border border-danger bg-danger-light text-danger'
            }`}
          >
            {message}
          </div>
        )}

        <form onSubmit={handleSave}>
          <div className="grid grid-cols-1 gap-xl lg:grid-cols-2">
            {/* Lead Info Card */}
            <div className="rounded-md border border-gray-200 bg-white shadow-xs overflow-hidden">
              <div className="bg-gray-800 px-xl py-md">
                <h3 className="text-sm font-semibold text-white">Lead Information</h3>
              </div>
              <div className="p-xl">
                <dl className="flex flex-col gap-lg">
                  <div className="flex justify-between">
                    <dt className="text-sm text-gray-500">Name</dt>
                    <dd className="text-sm font-medium text-gray-900">{lead.name}</dd>
                  </div>
                  <div className="flex justify-between">
                    <dt className="text-sm text-gray-500">Phone</dt>
                    <dd className="text-sm font-medium text-gray-900">{lead.phone}</dd>
                  </div>
                  <div className="flex justify-between">
                    <dt className="text-sm text-gray-500">Email</dt>
                    <dd className="text-sm font-medium text-gray-900">{lead.email || '---'}</dd>
                  </div>
                  <div className="flex justify-between">
                    <dt className="text-sm text-gray-500">Source</dt>
                    <dd className="text-sm font-medium text-gray-900">{lead.source || 'Direct'}</dd>
                  </div>
                  <div className="flex justify-between">
                    <dt className="text-sm text-gray-500">Intent</dt>
                    <dd className="text-sm font-medium text-gray-900">{lead.intent || '---'}</dd>
                  </div>
                </dl>
              </div>
            </div>

            {/* Project Info Card */}
            <div className="rounded-md border border-gray-200 bg-white shadow-xs overflow-hidden">
              <div className="bg-gray-800 px-xl py-md">
                <h3 className="text-sm font-semibold text-white">Project & Status</h3>
              </div>
              <div className="p-xl">
                <dl className="flex flex-col gap-lg">
                  <div className="flex justify-between">
                    <dt className="text-sm text-gray-500">Project</dt>
                    <dd className="text-sm font-medium">
                      {lead.projectId ? (
                        <Link
                          href={`/admin/projects/${lead.projectId}`}
                          className="text-brand-primary hover:underline"
                        >
                          {lead.projectTitle}
                        </Link>
                      ) : (
                        <span className="text-gray-900">{lead.projectTitle || '---'}</span>
                      )}
                    </dd>
                  </div>
                  <div className="flex justify-between items-center">
                    <dt className="text-sm text-gray-500">Current Status</dt>
                    <dd>
                      <StatusBadge status={lead.status} />
                    </dd>
                  </div>
                  <div className="flex justify-between">
                    <dt className="text-sm text-gray-500">Created</dt>
                    <dd className="text-sm text-gray-900">{lead.createdAt}</dd>
                  </div>
                  <div className="flex justify-between">
                    <dt className="text-sm text-gray-500">Last Updated</dt>
                    <dd className="text-sm text-gray-900">{lead.updatedAt}</dd>
                  </div>
                </dl>
              </div>
            </div>

            {/* Update Status */}
            <div className="rounded-md border border-gray-200 bg-white shadow-xs overflow-hidden">
              <div className="bg-gray-800 px-xl py-md">
                <h3 className="text-sm font-semibold text-white">Update Status</h3>
              </div>
              <div className="p-xl">
                <div className="flex flex-col gap-sm">
                  <label className="text-sm font-medium text-gray-700">
                    Status
                  </label>
                  <select
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                  >
                    {STATUS_OPTIONS.map((opt) => (
                      <option key={opt} value={opt}>
                        {opt.replace(/_/g, ' ')}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
            </div>

            {/* Notes */}
            <div className="rounded-md border border-gray-200 bg-white shadow-xs overflow-hidden">
              <div className="bg-gray-800 px-xl py-md">
                <h3 className="text-sm font-semibold text-white">Notes</h3>
              </div>
              <div className="p-xl">
                <textarea
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                  rows={5}
                  placeholder="Add notes about this lead..."
                  className="w-full rounded-sm border border-gray-300 px-md py-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                />
              </div>
            </div>
          </div>

          {/* Save button */}
          <div className="mt-xl">
            <button
              type="submit"
              disabled={saving}
              className="inline-flex items-center gap-sm rounded-sm bg-brand-primary px-xl py-sm text-sm font-semibold text-white transition-colors hover:bg-brand-primary-dark disabled:opacity-50 disabled:pointer-events-none"
            >
              {saving ? 'Saving...' : 'Save Changes'}
            </button>
          </div>
        </form>
      </div>
    </>
  );
}
