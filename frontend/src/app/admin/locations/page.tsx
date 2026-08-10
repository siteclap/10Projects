'use client';

import { useEffect, useState, type FormEvent } from 'react';
import { AdminTopbar } from '@/components/admin/AdminTopbar';

interface Location {
  id: string;
  name: string;
  slug: string;
  _count: { projects: number };
}

function slugify(text: string): string {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export default function LocationsPage() {
  const [locations, setLocations] = useState<Location[]>([]);
  const [loading, setLoading] = useState(true);
  const [name, setName] = useState('');
  const [slug, setSlug] = useState('');
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchLocations();
  }, []);

  async function fetchLocations() {
    try {
      const res = await fetch('/api/admin/locations');
      if (res.ok) {
        const data = await res.json();
        setLocations(data.items || data || []);
      }
    } catch {
      // API not available
    } finally {
      setLoading(false);
    }
  }

  function handleNameChange(value: string) {
    setName(value);
    setSlug(slugify(value));
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    if (!name.trim()) return;

    setSaving(true);
    setError('');

    try {
      const res = await fetch('/api/admin/locations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name.trim(), slug: slug.trim() }),
      });

      if (res.ok) {
        setName('');
        setSlug('');
        fetchLocations();
      } else {
        const data = await res.json().catch(() => null);
        setError(data?.message || 'Failed to add location');
      }
    } catch {
      setError('Failed to add location');
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete(id: string) {
    if (!confirm('Are you sure you want to delete this location?')) return;

    try {
      await fetch(`/api/admin/locations?id=${id}`, { method: 'DELETE' });
      fetchLocations();
    } catch {
      // Handle error
    }
  }

  return (
    <>
      <AdminTopbar title="Locations" />

      <div className="p-2xl">
        <div className="grid grid-cols-1 gap-2xl lg:grid-cols-3">
          {/* Left: Add New Location form */}
          <div>
            <div className="rounded-md border border-gray-200 bg-white shadow-xs overflow-hidden">
              <div className="bg-gray-800 px-xl py-md">
                <h3 className="text-sm font-semibold text-white">Add New Location</h3>
              </div>
              <div className="p-xl">
                <form onSubmit={handleSubmit} className="flex flex-col gap-lg">
                  {error && (
                    <div className="rounded-sm border border-danger bg-danger-light px-md py-sm text-sm text-danger">
                      {error}
                    </div>
                  )}

                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">Name</label>
                    <input
                      type="text"
                      value={name}
                      onChange={(e) => handleNameChange(e.target.value)}
                      placeholder="e.g. Kharghar"
                      required
                      className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                  </div>

                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">Slug</label>
                    <input
                      type="text"
                      value={slug}
                      onChange={(e) => setSlug(e.target.value)}
                      placeholder="kharghar"
                      required
                      className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                  </div>

                  <button
                    type="submit"
                    disabled={saving || !name.trim()}
                    className="flex h-[40px] items-center justify-center rounded-sm bg-brand-primary text-sm font-semibold text-white transition-colors hover:bg-brand-primary-dark disabled:opacity-50 disabled:pointer-events-none"
                  >
                    {saving ? 'Adding...' : 'Add Location'}
                  </button>
                </form>
              </div>
            </div>
          </div>

          {/* Right: Existing locations table */}
          <div className="lg:col-span-2">
            <div className="rounded-md border border-gray-200 bg-white shadow-xs">
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b border-gray-200 bg-gray-50">
                      <th className="px-xl py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                        Name
                      </th>
                      <th className="px-xl py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                        Slug
                      </th>
                      <th className="px-xl py-md text-left text-caption font-medium uppercase tracking-wider text-gray-500">
                        Projects
                      </th>
                      <th className="px-xl py-md text-right text-caption font-medium uppercase tracking-wider text-gray-500">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {loading ? (
                      Array.from({ length: 5 }).map((_, i) => (
                        <tr key={i} className="border-b border-gray-100">
                          {Array.from({ length: 4 }).map((_, j) => (
                            <td key={j} className="px-xl py-md">
                              <div className="h-[16px] w-[80px] animate-pulse rounded-sm bg-gray-200" />
                            </td>
                          ))}
                        </tr>
                      ))
                    ) : locations.length === 0 ? (
                      <tr>
                        <td colSpan={4} className="px-xl py-3xl text-center text-sm text-gray-500">
                          No locations yet. Add one using the form.
                        </td>
                      </tr>
                    ) : (
                      locations.map((location) => (
                        <tr
                          key={location.id}
                          className="border-b border-gray-100 transition-colors hover:bg-gray-50"
                        >
                          <td className="px-xl py-md text-sm font-medium text-gray-900">
                            {location.name}
                          </td>
                          <td className="px-xl py-md text-sm text-gray-600">
                            {location.slug}
                          </td>
                          <td className="px-xl py-md text-sm text-gray-600">
                            {location._count.projects}
                          </td>
                          <td className="px-xl py-md text-right">
                            <button
                              type="button"
                              onClick={() => handleDelete(location.id)}
                              className="text-sm font-medium text-danger transition-colors hover:text-red-700 hover:underline"
                            >
                              Delete
                            </button>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
