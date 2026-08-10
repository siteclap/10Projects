'use client';

import { useEffect, useState, type ChangeEvent } from 'react';
import Link from 'next/link';
import { AdminTopbar } from '@/components/admin/AdminTopbar';
import { StatusBadge } from '@/components/admin/StatusBadge';

interface Project {
  id: string;
  title: string;
  slug: string;
  published: boolean;
  createdAt: string;
  thumbnail: string | null;
  location: { name: string; slug: string } | null;
  tags: Array<{ tag: { name: string } }>;
}

interface PaginationMeta {
  page: number;
  perPage: number;
  total: number;
  totalPages: number;
}

export default function ProjectListPage() {
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [bulkAction, setBulkAction] = useState('');
  const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set());
  const [pagination, setPagination] = useState<PaginationMeta>({
    page: 1,
    perPage: 20,
    total: 0,
    totalPages: 1,
  });

  useEffect(() => {
    fetchProjects();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagination.page]);

  async function fetchProjects() {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page: String(pagination.page),
        perPage: String(pagination.perPage),
      });
      if (search) params.set('search', search);

      const res = await fetch(`/api/admin/projects?${params}`);
      if (res.ok) {
        const data = await res.json();
        // API returns a flat array of projects
        const items = Array.isArray(data) ? data : (data.items || []);
        setProjects(items);
        setPagination((prev) => ({ ...prev, total: items.length, totalPages: 1 }));
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
    fetchProjects();
  }

  function toggleSelect(id: string) {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  }

  function toggleSelectAll(e: ChangeEvent<HTMLInputElement>) {
    if (e.target.checked) {
      setSelectedIds(new Set(projects.map((p) => p.id)));
    } else {
      setSelectedIds(new Set());
    }
  }

  async function handleBulkApply() {
    if (!bulkAction || selectedIds.size === 0) return;

    if (bulkAction === 'trash') {
      if (!confirm(`Move ${selectedIds.size} project(s) to trash?`)) return;
      try {
        await fetch('/api/admin/projects/bulk', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'trash', ids: Array.from(selectedIds) }),
        });
        setSelectedIds(new Set());
        fetchProjects();
      } catch {
        // Handle error
      }
    }
  }

  async function handleTrash(id: string) {
    if (!confirm('Move this project to trash?')) return;
    try {
      await fetch(`/api/admin/projects/${id}`, { method: 'DELETE' });
      fetchProjects();
    } catch {
      // Handle error
    }
  }

  return (
    <>
      <AdminTopbar title="Landing Pages" />

      <div className="p-2xl">
        {/* Header row */}
        <div className="mb-xl flex items-center justify-between">
          <h2 className="text-h3 text-gray-900">Landing Pages</h2>
          <Link
            href="/admin/projects/new"
            className="inline-flex items-center gap-sm rounded-sm bg-brand-primary px-lg py-sm text-sm font-medium text-white no-underline transition-colors hover:bg-brand-primary-dark hover:no-underline"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M12 5v14" />
              <path d="M5 12h14" />
            </svg>
            Add New
          </Link>
        </div>

        {/* Filter row */}
        <div className="mb-lg flex flex-wrap items-center gap-md">
          {/* Bulk actions */}
          <div className="flex items-center gap-sm">
            <select
              value={bulkAction}
              onChange={(e) => setBulkAction(e.target.value)}
              className="h-[36px] rounded-sm border border-gray-300 px-md text-sm text-gray-700 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
            >
              <option value="">Bulk Actions</option>
              <option value="trash">Move to Trash</option>
            </select>
            <button
              type="button"
              onClick={handleBulkApply}
              disabled={!bulkAction || selectedIds.size === 0}
              className="h-[36px] rounded-sm border border-gray-300 bg-white px-md text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none"
            >
              Apply
            </button>
          </div>

          {/* Spacer */}
          <div className="flex-1" />

          {/* Search */}
          <form onSubmit={handleSearchSubmit} className="flex items-center gap-sm">
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search projects..."
              className="h-[36px] w-[240px] rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
            />
            <button
              type="submit"
              className="h-[36px] rounded-sm border border-gray-300 bg-white px-md text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
            >
              Search
            </button>
          </form>
        </div>

        {/* Table */}
        <div className="rounded-md border border-gray-200 bg-white shadow-xs">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-gray-200 bg-gray-50">
                  <th className="w-[40px] px-lg py-md">
                    <input
                      type="checkbox"
                      onChange={toggleSelectAll}
                      checked={projects.length > 0 && selectedIds.size === projects.length}
                      className="h-[16px] w-[16px] rounded border-gray-300 text-brand-primary focus:ring-brand-primary"
                    />
                  </th>
                  <th className="px-lg py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Title
                  </th>
                  <th className="px-lg py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Date
                  </th>
                  <th className="px-lg py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Slug
                  </th>
                  <th className="px-lg py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Location
                  </th>
                  <th className="px-lg py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Image
                  </th>
                  <th className="px-lg py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                    Tags
                  </th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  Array.from({ length: 5 }).map((_, i) => (
                    <tr key={i} className="border-b border-gray-100">
                      <td className="px-lg py-md">
                        <div className="h-[16px] w-[16px] animate-pulse rounded bg-gray-200" />
                      </td>
                      {Array.from({ length: 6 }).map((_, j) => (
                        <td key={j} className="px-lg py-md">
                          <div className="h-[16px] w-[100px] animate-pulse rounded-sm bg-gray-200" />
                        </td>
                      ))}
                    </tr>
                  ))
                ) : projects.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="px-lg py-3xl text-center text-sm text-gray-500">
                      No projects found.{' '}
                      <Link href="/admin/projects/new" className="text-brand-primary hover:underline">
                        Create your first project
                      </Link>
                    </td>
                  </tr>
                ) : (
                  projects.map((project) => (
                    <tr
                      key={project.id}
                      className="group border-b border-gray-100 transition-colors hover:bg-gray-50"
                    >
                      <td className="px-lg py-md">
                        <input
                          type="checkbox"
                          checked={selectedIds.has(project.id)}
                          onChange={() => toggleSelect(project.id)}
                          className="h-[16px] w-[16px] rounded border-gray-300 text-brand-primary focus:ring-brand-primary"
                        />
                      </td>
                      <td className="px-lg py-md">
                        <div>
                          <Link
                            href={`/admin/projects/${project.id}`}
                            className="text-sm font-medium text-gray-900 no-underline hover:text-brand-primary hover:underline"
                          >
                            {project.title}
                          </Link>
                          <div className="flex items-center gap-md">
                            <StatusBadge status={project.published ? 'PUBLISHED' : 'DRAFT'} className="mt-xs" />
                          </div>
                          {/* Row actions on hover */}
                          <div className="mt-xs flex items-center gap-md opacity-0 transition-opacity group-hover:opacity-100">
                            <Link
                              href={`/admin/projects/${project.id}`}
                              className="text-caption font-medium text-brand-primary no-underline hover:underline"
                            >
                              Edit
                            </Link>
                            <span className="text-gray-300">|</span>
                            <button
                              type="button"
                              onClick={() => handleTrash(project.id)}
                              className="text-caption font-medium text-danger hover:underline"
                            >
                              Trash
                            </button>
                            <span className="text-gray-300">|</span>
                            <a
                              href={`/navi-mumbai/${project.location?.slug || ''}/${project.slug}`}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="text-caption font-medium text-gray-500 no-underline hover:underline"
                            >
                              View
                            </a>
                          </div>
                        </div>
                      </td>
                      <td className="px-lg py-md text-sm text-gray-500">
                        {new Date(project.createdAt).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}
                      </td>
                      <td className="px-lg py-md text-sm text-gray-600">
                        {project.slug}
                      </td>
                      <td className="px-lg py-md text-sm text-gray-600">
                        {project.location?.name || '—'}
                      </td>
                      <td className="px-lg py-md">
                        {project.thumbnail ? (
                          <img
                            src={project.thumbnail}
                            alt=""
                            className="h-[40px] w-[60px] rounded-sm border border-gray-200 object-cover"
                          />
                        ) : (
                          <div className="flex h-[40px] w-[60px] items-center justify-center rounded-sm bg-gray-100">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="text-gray-400">
                              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                              <circle cx="8.5" cy="8.5" r="1.5" />
                              <polyline points="21 15 16 10 5 21" />
                            </svg>
                          </div>
                        )}
                      </td>
                      <td className="px-lg py-md">
                        <div className="flex flex-wrap gap-xs">
                          {(project.tags || []).map((pt) => (
                            <span
                              key={pt.tag.name}
                              className="rounded-full bg-gray-100 px-sm py-xs text-caption text-gray-600"
                            >
                              {pt.tag.name}
                            </span>
                          ))}
                        </div>
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
                Showing {(pagination.page - 1) * pagination.perPage + 1} to{' '}
                {Math.min(pagination.page * pagination.perPage, pagination.total)} of{' '}
                {pagination.total} items
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
                {Array.from({ length: pagination.totalPages }, (_, i) => i + 1).map(
                  (pageNum) => (
                    <button
                      key={pageNum}
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
                  )
                )}
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
