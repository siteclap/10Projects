'use client';

import { useState, useEffect, type FormEvent, type ChangeEvent } from 'react';
import { useRouter } from 'next/navigation';

/* ------------------------------------------------------------------ */
/*  Types                                                              */
/* ------------------------------------------------------------------ */

interface Configuration {
  id: string;
  configType: string;
  carpetArea: string;
  basePrice: string;
  totalPrice: string;
  available: string;
  total: string;
}

interface ProConItem {
  id: string;
  text: string;
}

interface TaxonomyItem {
  id: string;
  name: string;
  slug: string;
}

export interface ProjectFormData {
  id?: string;
  title: string;
  slug: string;
  status: 'DRAFT' | 'PUBLISHED';
  projectName: string;
  developer: string;
  location: string;
  landParcel: string;
  floors: string;
  possessionDate: string;
  reraNumber: string;
  qrCodeUrl: string;
  highlights: string;
  shortOverview: string;
  configurations: Configuration[];
  amenities: string;
  pros: ProConItem[];
  cons: ProConItem[];
  featuredImage: string;
  locationIds: string[];
  tagIds: string[];
}

interface ProjectFormProps {
  initialData?: Partial<ProjectFormData>;
  isEditing?: boolean;
}

/* ------------------------------------------------------------------ */
/*  Helpers                                                            */
/* ------------------------------------------------------------------ */

function generateId(): string {
  return Math.random().toString(36).substring(2, 9);
}

function slugify(text: string): string {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

const emptyConfig: () => Configuration = () => ({
  id: generateId(),
  configType: '',
  carpetArea: '',
  basePrice: '',
  totalPrice: '',
  available: '',
  total: '',
});

const emptyProCon: () => ProConItem = () => ({
  id: generateId(),
  text: '',
});

/* ------------------------------------------------------------------ */
/*  Metabox wrapper                                                    */
/* ------------------------------------------------------------------ */

function Metabox({
  title,
  children,
  defaultOpen = true,
}: {
  title: string;
  children: React.ReactNode;
  defaultOpen?: boolean;
}) {
  const [open, setOpen] = useState(defaultOpen);

  return (
    <div className="rounded-md border border-gray-200 bg-white shadow-xs overflow-hidden">
      <button
        type="button"
        onClick={() => setOpen(!open)}
        className="flex w-full items-center justify-between bg-gray-800 px-xl py-md text-left"
      >
        <span className="text-sm font-semibold text-white">{title}</span>
        <svg
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="white"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
          className={`transition-transform duration-200 ${open ? 'rotate-180' : ''}`}
          aria-hidden="true"
        >
          <polyline points="6 9 12 15 18 9" />
        </svg>
      </button>
      {open && <div className="p-xl">{children}</div>}
    </div>
  );
}

/* ------------------------------------------------------------------ */
/*  ProjectForm                                                        */
/* ------------------------------------------------------------------ */

export function ProjectForm({ initialData, isEditing = false }: ProjectFormProps) {
  const router = useRouter();
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  /* Taxonomy data from API */
  const [availableLocations, setAvailableLocations] = useState<TaxonomyItem[]>([]);
  const [availableTags, setAvailableTags] = useState<TaxonomyItem[]>([]);

  /* Form state */
  const [form, setForm] = useState<ProjectFormData>({
    title: '',
    slug: '',
    status: 'DRAFT',
    projectName: '',
    developer: '',
    location: '',
    landParcel: '',
    floors: '',
    possessionDate: '',
    reraNumber: '',
    qrCodeUrl: '',
    highlights: '',
    shortOverview: '',
    configurations: [emptyConfig()],
    amenities: '',
    pros: [emptyProCon()],
    cons: [emptyProCon()],
    featuredImage: '',
    locationIds: [],
    tagIds: [],
    ...initialData,
  });

  /* Fetch available locations and tags */
  useEffect(() => {
    async function fetchTaxonomies() {
      try {
        const [locRes, tagRes] = await Promise.all([
          fetch('/api/admin/locations'),
          fetch('/api/admin/tags'),
        ]);
        if (locRes.ok) {
          const data = await locRes.json();
          setAvailableLocations(data.items || data || []);
        }
        if (tagRes.ok) {
          const data = await tagRes.json();
          setAvailableTags(data.items || data || []);
        }
      } catch {
        // Taxonomies not available yet
      }
    }
    fetchTaxonomies();
  }, []);

  /* Update form field */
  function updateField<K extends keyof ProjectFormData>(key: K, value: ProjectFormData[K]) {
    setForm((prev) => ({ ...prev, [key]: value }));
  }

  /* Auto-generate slug from title */
  function handleTitleChange(value: string) {
    updateField('title', value);
    if (!isEditing) {
      updateField('slug', slugify(value));
    }
  }

  /* Configuration repeater */
  function addConfig() {
    updateField('configurations', [...form.configurations, emptyConfig()]);
  }

  function removeConfig(id: string) {
    updateField(
      'configurations',
      form.configurations.filter((c) => c.id !== id)
    );
  }

  function updateConfig(id: string, field: keyof Configuration, value: string) {
    updateField(
      'configurations',
      form.configurations.map((c) => (c.id === id ? { ...c, [field]: value } : c))
    );
  }

  /* Pros & Cons */
  function addPro() {
    updateField('pros', [...form.pros, emptyProCon()]);
  }
  function removePro(id: string) {
    updateField('pros', form.pros.filter((p) => p.id !== id));
  }
  function updatePro(id: string, text: string) {
    updateField('pros', form.pros.map((p) => (p.id === id ? { ...p, text } : p)));
  }

  function addCon() {
    updateField('cons', [...form.cons, emptyProCon()]);
  }
  function removeCon(id: string) {
    updateField('cons', form.cons.filter((c) => c.id !== id));
  }
  function updateCon(id: string, text: string) {
    updateField('cons', form.cons.map((c) => (c.id === id ? { ...c, text } : c)));
  }

  /* Taxonomy checkboxes */
  function toggleLocation(id: string) {
    const current = form.locationIds;
    updateField(
      'locationIds',
      current.includes(id) ? current.filter((l) => l !== id) : [...current, id]
    );
  }

  function toggleTag(id: string) {
    const current = form.tagIds;
    updateField(
      'tagIds',
      current.includes(id) ? current.filter((t) => t !== id) : [...current, id]
    );
  }

  /* Featured image upload */
  async function handleImageUpload(e: ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await fetch('/api/admin/media/upload', {
        method: 'POST',
        body: formData,
      });
      if (res.ok) {
        const data = await res.json();
        updateField('featuredImage', data.url);
      }
    } catch {
      // Upload failed silently
    }
  }

  /* QR code upload */
  async function handleQrUpload(e: ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await fetch('/api/admin/media/upload', {
        method: 'POST',
        body: formData,
      });
      if (res.ok) {
        const data = await res.json();
        updateField('qrCodeUrl', data.url);
      }
    } catch {
      // Upload failed silently
    }
  }

  /* Submit */
  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setError('');
    setSaving(true);

    try {
      const url = isEditing
        ? `/api/admin/projects/${form.id}`
        : '/api/admin/projects';
      const method = isEditing ? 'PUT' : 'POST';

      // Transform form data to match API's expected shape
      const payload = {
        title: form.title,
        slug: form.slug,
        developer: form.developer,
        locationId: form.locationIds[0] || null,
        constructionStage: null,
        expectedPossession: form.possessionDate || null,
        reraNumber: form.reraNumber || null,
        landParcel: form.landParcel || null,
        floors: form.floors || null,
        description: form.shortOverview || null,
        highlights: form.highlights || null,
        thumbnail: form.featuredImage || null,
        priceMin: 0,
        priceMax: 0,
        amenities: form.amenities
          ? form.amenities.split(/[,\n]/).map((s) => s.trim()).filter(Boolean)
          : [],
        pros: form.pros.map((p) => p.text).filter(Boolean),
        cons: form.cons.map((c) => c.text).filter(Boolean),
        published: form.status === 'PUBLISHED',
        featured: false,
        configurations: form.configurations
          .filter((c) => c.configType)
          .map((c) => ({
            configType: c.configType,
            carpetAreaSqft: Number(c.carpetArea) || 0,
            basePrice: Number(c.basePrice) || 0,
            totalPrice: Number(c.totalPrice) || 0,
            inventoryTotal: Number(c.total) || 0,
            inventoryAvailable: Number(c.available) || 0,
          })),
        tagIds: form.tagIds,
      };

      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      if (!res.ok) {
        const data = await res.json().catch(() => null);
        throw new Error(data?.error || 'Failed to save project');
      }

      const data = await res.json();
      router.push('/admin/projects');
      router.refresh();
      return data;
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save project');
    } finally {
      setSaving(false);
    }
  }

  /* Trash */
  async function handleTrash() {
    if (!form.id) return;
    if (!confirm('Are you sure you want to move this project to trash?')) return;

    try {
      await fetch(`/api/admin/projects/${form.id}`, { method: 'DELETE' });
      router.push('/admin/projects');
      router.refresh();
    } catch {
      setError('Failed to delete project');
    }
  }

  return (
    <form onSubmit={handleSubmit}>
      {/* Error banner */}
      {error && (
        <div className="mb-xl rounded-sm border border-danger bg-danger-light px-lg py-md text-sm text-danger">
          {error}
        </div>
      )}

      <div className="flex gap-2xl">
        {/* ============================================================= */}
        {/*  LEFT COLUMN (70%)                                             */}
        {/* ============================================================= */}
        <div className="flex flex-1 flex-col gap-xl" style={{ flex: '7' }}>
          {/* Title */}
          <div>
            <input
              type="text"
              value={form.title}
              onChange={(e) => handleTitleChange(e.target.value)}
              placeholder="Enter project title"
              className="w-full rounded-sm border border-gray-300 px-lg py-md text-h3 text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
            />
            {form.slug && (
              <p className="mt-sm text-sm text-gray-500">
                Permalink: <span className="text-brand-primary">/navi-mumbai/.../</span>
                <span className="font-medium text-gray-700">{form.slug}</span>
              </p>
            )}
          </div>

          {/* Settings Metabox */}
          <Metabox title="Project Settings">
            <div className="flex flex-col gap-xl">
              {/* Row 1: Name, Developer, Location */}
              <div className="grid grid-cols-1 gap-lg md:grid-cols-3">
                <div className="flex flex-col gap-sm">
                  <label className="text-sm font-medium text-gray-700">
                    Project Name
                  </label>
                  <input
                    type="text"
                    value={form.projectName}
                    onChange={(e) => updateField('projectName', e.target.value)}
                    className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                  />
                </div>
                <div className="flex flex-col gap-sm">
                  <label className="text-sm font-medium text-gray-700">
                    Developer
                  </label>
                  <input
                    type="text"
                    value={form.developer}
                    onChange={(e) => updateField('developer', e.target.value)}
                    className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                  />
                </div>
                <div className="flex flex-col gap-sm">
                  <label className="text-sm font-medium text-gray-700">
                    Location
                  </label>
                  <input
                    type="text"
                    value={form.location}
                    onChange={(e) => updateField('location', e.target.value)}
                    className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                  />
                </div>
              </div>

              {/* Row 2: Land Parcel, Floors, Possession Date */}
              <div className="grid grid-cols-1 gap-lg md:grid-cols-3">
                <div className="flex flex-col gap-sm">
                  <label className="text-sm font-medium text-gray-700">
                    Land Parcel
                  </label>
                  <input
                    type="text"
                    value={form.landParcel}
                    onChange={(e) => updateField('landParcel', e.target.value)}
                    placeholder="e.g. 5 Acres"
                    className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                  />
                </div>
                <div className="flex flex-col gap-sm">
                  <label className="text-sm font-medium text-gray-700">
                    Floors
                  </label>
                  <input
                    type="text"
                    value={form.floors}
                    onChange={(e) => updateField('floors', e.target.value)}
                    placeholder="e.g. G + 40"
                    className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                  />
                </div>
                <div className="flex flex-col gap-sm">
                  <label className="text-sm font-medium text-gray-700">
                    Possession Date
                  </label>
                  <input
                    type="date"
                    value={form.possessionDate}
                    onChange={(e) => updateField('possessionDate', e.target.value)}
                    className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                  />
                </div>
              </div>

              {/* Row 3: RERA Number, QR Code */}
              <div className="grid grid-cols-1 gap-lg md:grid-cols-2">
                <div className="flex flex-col gap-sm">
                  <label className="text-sm font-medium text-gray-700">
                    RERA Number
                  </label>
                  <input
                    type="text"
                    value={form.reraNumber}
                    onChange={(e) => updateField('reraNumber', e.target.value)}
                    placeholder="e.g. P52100012345"
                    className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                  />
                </div>
                <div className="flex flex-col gap-sm">
                  <label className="text-sm font-medium text-gray-700">
                    QR Code
                  </label>
                  <div className="flex items-center gap-md">
                    <input
                      type="file"
                      accept="image/*"
                      onChange={handleQrUpload}
                      className="text-sm text-gray-600 file:mr-md file:rounded-sm file:border-0 file:bg-gray-100 file:px-md file:py-sm file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200"
                    />
                    {form.qrCodeUrl && (
                      <img
                        src={form.qrCodeUrl}
                        alt="QR Code"
                        className="h-[48px] w-[48px] rounded-sm border border-gray-200 object-contain"
                      />
                    )}
                  </div>
                </div>
              </div>

              {/* Highlights */}
              <div className="flex flex-col gap-sm">
                <label className="text-sm font-medium text-gray-700">
                  Project Highlights
                </label>
                <textarea
                  value={form.highlights}
                  onChange={(e) => updateField('highlights', e.target.value)}
                  rows={3}
                  placeholder="Key highlights of the project, one per line"
                  className="w-full rounded-sm border border-gray-300 px-md py-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                />
              </div>

              {/* Short Overview */}
              <div className="flex flex-col gap-sm">
                <label className="text-sm font-medium text-gray-700">
                  Short Overview
                </label>
                <textarea
                  value={form.shortOverview}
                  onChange={(e) => updateField('shortOverview', e.target.value)}
                  rows={4}
                  placeholder="A brief description of the project for SEO and previews"
                  className="w-full rounded-sm border border-gray-300 px-md py-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                />
              </div>
            </div>
          </Metabox>

          {/* Carpet Area & Price Metabox */}
          <Metabox title="Carpet Area & Price">
            <div className="flex flex-col gap-lg">
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="border-b border-gray-200">
                      <th className="pb-sm pr-md text-left text-caption font-medium text-gray-500">
                        Config Type
                      </th>
                      <th className="pb-sm pr-md text-left text-caption font-medium text-gray-500">
                        Carpet Area (sqft)
                      </th>
                      <th className="pb-sm pr-md text-left text-caption font-medium text-gray-500">
                        Base Price
                      </th>
                      <th className="pb-sm pr-md text-left text-caption font-medium text-gray-500">
                        Total Price
                      </th>
                      <th className="pb-sm pr-md text-left text-caption font-medium text-gray-500">
                        Available
                      </th>
                      <th className="pb-sm pr-md text-left text-caption font-medium text-gray-500">
                        Total
                      </th>
                      <th className="pb-sm text-left text-caption font-medium text-gray-500" />
                    </tr>
                  </thead>
                  <tbody>
                    {form.configurations.map((config) => (
                      <tr key={config.id} className="border-b border-gray-100">
                        <td className="py-sm pr-md">
                          <input
                            type="text"
                            value={config.configType}
                            onChange={(e) => updateConfig(config.id, 'configType', e.target.value)}
                            placeholder="e.g. 2 BHK"
                            className="h-[36px] w-full min-w-[100px] rounded-sm border border-gray-300 px-sm text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                          />
                        </td>
                        <td className="py-sm pr-md">
                          <input
                            type="text"
                            value={config.carpetArea}
                            onChange={(e) => updateConfig(config.id, 'carpetArea', e.target.value)}
                            placeholder="650"
                            className="h-[36px] w-full min-w-[80px] rounded-sm border border-gray-300 px-sm text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                          />
                        </td>
                        <td className="py-sm pr-md">
                          <input
                            type="text"
                            value={config.basePrice}
                            onChange={(e) => updateConfig(config.id, 'basePrice', e.target.value)}
                            placeholder="7500/sqft"
                            className="h-[36px] w-full min-w-[100px] rounded-sm border border-gray-300 px-sm text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                          />
                        </td>
                        <td className="py-sm pr-md">
                          <input
                            type="text"
                            value={config.totalPrice}
                            onChange={(e) => updateConfig(config.id, 'totalPrice', e.target.value)}
                            placeholder="48.75L"
                            className="h-[36px] w-full min-w-[80px] rounded-sm border border-gray-300 px-sm text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                          />
                        </td>
                        <td className="py-sm pr-md">
                          <input
                            type="text"
                            value={config.available}
                            onChange={(e) => updateConfig(config.id, 'available', e.target.value)}
                            placeholder="12"
                            className="h-[36px] w-[60px] rounded-sm border border-gray-300 px-sm text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                          />
                        </td>
                        <td className="py-sm pr-md">
                          <input
                            type="text"
                            value={config.total}
                            onChange={(e) => updateConfig(config.id, 'total', e.target.value)}
                            placeholder="50"
                            className="h-[36px] w-[60px] rounded-sm border border-gray-300 px-sm text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                          />
                        </td>
                        <td className="py-sm">
                          <button
                            type="button"
                            onClick={() => removeConfig(config.id)}
                            className="flex h-[32px] w-[32px] items-center justify-center rounded-sm text-gray-400 transition-colors hover:bg-danger-light hover:text-danger"
                            aria-label="Remove configuration"
                          >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                              <path d="M18 6 6 18" />
                              <path d="m6 6 12 12" />
                            </svg>
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              <button
                type="button"
                onClick={addConfig}
                className="inline-flex items-center gap-xs self-start rounded-sm border border-dashed border-gray-300 px-md py-sm text-sm font-medium text-gray-600 transition-colors hover:border-brand-primary hover:text-brand-primary"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M12 5v14" />
                  <path d="M5 12h14" />
                </svg>
                Add Configuration
              </button>
            </div>
          </Metabox>

          {/* Amenities Metabox */}
          <Metabox title="Amenities">
            <div className="flex flex-col gap-sm">
              <label className="text-sm text-gray-500">
                Enter amenities separated by commas or one per line
              </label>
              <textarea
                value={form.amenities}
                onChange={(e) => updateField('amenities', e.target.value)}
                rows={4}
                placeholder="Swimming Pool, Gymnasium, Clubhouse, Children's Play Area, Landscaped Gardens"
                className="w-full rounded-sm border border-gray-300 px-md py-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
              />
            </div>
          </Metabox>

          {/* Pros & Cons Metabox */}
          <Metabox title="Pros & Cons">
            <div className="grid grid-cols-1 gap-xl md:grid-cols-2">
              {/* Pros */}
              <div className="flex flex-col gap-md">
                <h4 className="flex items-center gap-sm text-sm font-semibold text-success">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  Pros
                </h4>
                {form.pros.map((pro) => (
                  <div key={pro.id} className="flex items-center gap-sm">
                    <input
                      type="text"
                      value={pro.text}
                      onChange={(e) => updatePro(pro.id, e.target.value)}
                      placeholder="Enter a pro..."
                      className="h-[36px] flex-1 rounded-sm border border-gray-300 px-md text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                    />
                    <button
                      type="button"
                      onClick={() => removePro(pro.id)}
                      className="flex h-[32px] w-[32px] items-center justify-center rounded-sm text-gray-400 transition-colors hover:bg-danger-light hover:text-danger"
                      aria-label="Remove pro"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                      </svg>
                    </button>
                  </div>
                ))}
                <button
                  type="button"
                  onClick={addPro}
                  className="inline-flex items-center gap-xs self-start text-sm font-medium text-brand-primary transition-colors hover:text-brand-primary-dark"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                  </svg>
                  Add Pro
                </button>
              </div>

              {/* Cons */}
              <div className="flex flex-col gap-md">
                <h4 className="flex items-center gap-sm text-sm font-semibold text-danger">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                  </svg>
                  Cons
                </h4>
                {form.cons.map((con) => (
                  <div key={con.id} className="flex items-center gap-sm">
                    <input
                      type="text"
                      value={con.text}
                      onChange={(e) => updateCon(con.id, e.target.value)}
                      placeholder="Enter a con..."
                      className="h-[36px] flex-1 rounded-sm border border-gray-300 px-md text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                    />
                    <button
                      type="button"
                      onClick={() => removeCon(con.id)}
                      className="flex h-[32px] w-[32px] items-center justify-center rounded-sm text-gray-400 transition-colors hover:bg-danger-light hover:text-danger"
                      aria-label="Remove con"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                      </svg>
                    </button>
                  </div>
                ))}
                <button
                  type="button"
                  onClick={addCon}
                  className="inline-flex items-center gap-xs self-start text-sm font-medium text-danger transition-colors hover:text-red-700"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                  </svg>
                  Add Con
                </button>
              </div>
            </div>
          </Metabox>
        </div>

        {/* ============================================================= */}
        {/*  RIGHT COLUMN (30%)                                            */}
        {/* ============================================================= */}
        <div className="flex flex-col gap-xl" style={{ flex: '3' }}>
          {/* Publish Metabox */}
          <Metabox title="Publish">
            <div className="flex flex-col gap-lg">
              <div className="flex flex-col gap-sm">
                <label className="text-sm font-medium text-gray-700">
                  Status
                </label>
                <select
                  value={form.status}
                  onChange={(e) => updateField('status', e.target.value as 'DRAFT' | 'PUBLISHED')}
                  className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                >
                  <option value="DRAFT">Draft</option>
                  <option value="PUBLISHED">Published</option>
                </select>
              </div>

              <button
                type="submit"
                disabled={saving}
                className="flex h-[40px] w-full items-center justify-center rounded-sm bg-brand-primary text-sm font-semibold text-white transition-colors hover:bg-brand-primary-dark disabled:opacity-50 disabled:pointer-events-none"
              >
                {saving ? 'Saving...' : isEditing ? 'Update' : 'Publish'}
              </button>

              {isEditing && (
                <button
                  type="button"
                  onClick={handleTrash}
                  className="text-sm text-danger transition-colors hover:text-red-700 hover:underline"
                >
                  Move to Trash
                </button>
              )}
            </div>
          </Metabox>

          {/* Featured Image Metabox */}
          <Metabox title="Featured Image">
            <div className="flex flex-col gap-md">
              {form.featuredImage ? (
                <div className="relative">
                  <img
                    src={form.featuredImage}
                    alt="Featured"
                    className="w-full rounded-sm border border-gray-200 object-cover"
                  />
                  <button
                    type="button"
                    onClick={() => updateField('featuredImage', '')}
                    className="absolute right-sm top-sm flex h-[28px] w-[28px] items-center justify-center rounded-full bg-white text-gray-500 shadow-card transition-colors hover:bg-danger-light hover:text-danger"
                    aria-label="Remove image"
                  >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M18 6 6 18" />
                      <path d="m6 6 12 12" />
                    </svg>
                  </button>
                </div>
              ) : (
                <label className="flex cursor-pointer flex-col items-center gap-md rounded-sm border-2 border-dashed border-gray-300 px-xl py-3xl text-center transition-colors hover:border-brand-primary hover:bg-brand-primary-bg">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" className="text-gray-400">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                    <circle cx="8.5" cy="8.5" r="1.5" />
                    <polyline points="21 15 16 10 5 21" />
                  </svg>
                  <span className="text-sm font-medium text-gray-600">
                    Click to upload
                  </span>
                  <span className="text-caption text-gray-400">
                    PNG, JPG, WebP
                  </span>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleImageUpload}
                    className="hidden"
                  />
                </label>
              )}
            </div>
          </Metabox>

          {/* Location Taxonomy Metabox */}
          <Metabox title="Location">
            <div className="flex flex-col gap-md">
              {availableLocations.length === 0 ? (
                <p className="text-sm text-gray-500">No locations available.</p>
              ) : (
                <div className="flex max-h-[200px] flex-col gap-sm overflow-y-auto">
                  {availableLocations.map((loc) => (
                    <label
                      key={loc.id}
                      className="flex items-center gap-sm text-sm text-gray-700 cursor-pointer"
                    >
                      <input
                        type="checkbox"
                        checked={form.locationIds.includes(loc.id)}
                        onChange={() => toggleLocation(loc.id)}
                        className="h-[16px] w-[16px] rounded border-gray-300 text-brand-primary focus:ring-brand-primary"
                      />
                      {loc.name}
                    </label>
                  ))}
                </div>
              )}
              <a
                href="/admin/locations"
                className="text-sm font-medium text-brand-primary hover:text-brand-primary-dark"
              >
                + Add New Location
              </a>
            </div>
          </Metabox>

          {/* Project Tags Metabox */}
          <Metabox title="Project Tags">
            <div className="flex flex-col gap-md">
              {availableTags.length === 0 ? (
                <p className="text-sm text-gray-500">No tags available.</p>
              ) : (
                <div className="flex max-h-[200px] flex-col gap-sm overflow-y-auto">
                  {availableTags.map((tag) => (
                    <label
                      key={tag.id}
                      className="flex items-center gap-sm text-sm text-gray-700 cursor-pointer"
                    >
                      <input
                        type="checkbox"
                        checked={form.tagIds.includes(tag.id)}
                        onChange={() => toggleTag(tag.id)}
                        className="h-[16px] w-[16px] rounded border-gray-300 text-brand-primary focus:ring-brand-primary"
                      />
                      {tag.name}
                    </label>
                  ))}
                </div>
              )}
              <a
                href="/admin/tags"
                className="text-sm font-medium text-brand-primary hover:text-brand-primary-dark"
              >
                + Add New Tag
              </a>
            </div>
          </Metabox>
        </div>
      </div>
    </form>
  );
}
