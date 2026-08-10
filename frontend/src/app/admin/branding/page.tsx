'use client';

import { useState, useEffect, type FormEvent, type ChangeEvent } from 'react';
import { AdminTopbar } from '@/components/admin/AdminTopbar';
import { cn } from '@/lib/utils/cn';

/* ------------------------------------------------------------------ */
/*  Types                                                              */
/* ------------------------------------------------------------------ */

interface BrandingData {
  /* Brand Logo / Colors */
  logoUrl: string;
  primaryColor: string;
  primaryDark: string;
  primaryLight: string;
  accentColor: string;
  tagline: string;
  heroHeading: string;
  ctaText: string;

  /* Contact Details */
  phone: string;
  whatsapp: string;
  email: string;
  address: string;
  city: string;
  state: string;

  /* Social Links */
  facebook: string;
  instagram: string;
  linkedin: string;
  youtube: string;
  twitter: string;

  /* SEO */
  siteTitle: string;
  metaDescription: string;
  ogImageUrl: string;
  gaTrackingId: string;
}

const defaultBranding: BrandingData = {
  logoUrl: '',
  primaryColor: '#4B1CB0',
  primaryDark: '#3B1490',
  primaryLight: '#7C3AED',
  accentColor: '#F59E0B',
  tagline: '',
  heroHeading: '',
  ctaText: '',
  phone: '',
  whatsapp: '',
  email: '',
  address: '',
  city: '',
  state: '',
  facebook: '',
  instagram: '',
  linkedin: '',
  youtube: '',
  twitter: '',
  siteTitle: '',
  metaDescription: '',
  ogImageUrl: '',
  gaTrackingId: '',
};

type TabKey = 'brand' | 'contact' | 'social' | 'seo';

const tabs: { key: TabKey; label: string }[] = [
  { key: 'brand', label: 'Brand Logo / Colors' },
  { key: 'contact', label: 'Contact Details' },
  { key: 'social', label: 'Social Links' },
  { key: 'seo', label: 'SEO' },
];

/* ------------------------------------------------------------------ */
/*  Component                                                          */
/* ------------------------------------------------------------------ */

export default function BrandSettingsPage() {
  const [activeTab, setActiveTab] = useState<TabKey>('brand');
  const [data, setData] = useState<BrandingData>(defaultBranding);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  useEffect(() => {
    async function fetchBranding() {
      try {
        const res = await fetch('/api/admin/branding');
        if (res.ok) {
          const result = await res.json();
          setData((prev) => ({ ...prev, ...result }));
        }
      } catch {
        // API not available yet
      } finally {
        setLoading(false);
      }
    }
    fetchBranding();
  }, []);

  function updateField<K extends keyof BrandingData>(key: K, value: BrandingData[K]) {
    setData((prev) => ({ ...prev, [key]: value }));
  }

  async function handleLogoUpload(e: ChangeEvent<HTMLInputElement>) {
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
        const result = await res.json();
        updateField('logoUrl', result.url);
      }
    } catch {
      // Upload failed
    }
  }

  async function handleOgImageUpload(e: ChangeEvent<HTMLInputElement>) {
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
        const result = await res.json();
        updateField('ogImageUrl', result.url);
      }
    } catch {
      // Upload failed
    }
  }

  async function handleSave(e: FormEvent) {
    e.preventDefault();
    setSaving(true);
    setMessage('');

    try {
      const res = await fetch('/api/admin/branding', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });

      if (res.ok) {
        setMessage('Settings saved successfully.');
      } else {
        setMessage('Failed to save settings. Please try again.');
      }
    } catch {
      setMessage('Failed to save settings. Please try again.');
    } finally {
      setSaving(false);
      setTimeout(() => setMessage(''), 3000);
    }
  }

  if (loading) {
    return (
      <>
        <AdminTopbar title="Brand Settings" />
        <div className="p-2xl">
          <div className="h-[400px] animate-pulse rounded-md bg-gray-200" />
        </div>
      </>
    );
  }

  return (
    <>
      <AdminTopbar title="Brand Settings" />

      <div className="p-2xl">
        {/* Tabs */}
        <div className="mb-xl flex border-b border-gray-200">
          {tabs.map((tab) => (
            <button
              key={tab.key}
              type="button"
              onClick={() => setActiveTab(tab.key)}
              className={cn(
                'px-xl py-md text-sm font-medium transition-colors',
                activeTab === tab.key
                  ? 'border-b-2 border-brand-primary text-brand-primary'
                  : 'text-gray-500 hover:text-gray-700'
              )}
            >
              {tab.label}
            </button>
          ))}
        </div>

        {/* Success/Error message */}
        {message && (
          <div
            className={cn(
              'mb-xl rounded-sm px-lg py-md text-sm',
              message.includes('success')
                ? 'border border-success bg-success-bg text-success'
                : 'border border-danger bg-danger-light text-danger'
            )}
          >
            {message}
          </div>
        )}

        <form onSubmit={handleSave}>
          <div className="rounded-md border border-gray-200 bg-white shadow-xs">
            <div className="p-2xl">
              {/* Brand Logo / Colors Tab */}
              {activeTab === 'brand' && (
                <div className="flex flex-col gap-xl">
                  {/* Logo upload */}
                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">Logo</label>
                    <div className="flex items-center gap-xl">
                      {data.logoUrl ? (
                        <div className="relative">
                          <img
                            src={data.logoUrl}
                            alt="Logo"
                            className="h-[64px] rounded-sm border border-gray-200 object-contain"
                          />
                          <button
                            type="button"
                            onClick={() => updateField('logoUrl', '')}
                            className="absolute -right-sm -top-sm flex h-[24px] w-[24px] items-center justify-center rounded-full bg-white text-gray-500 shadow-card hover:text-danger"
                            aria-label="Remove logo"
                          >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                              <path d="M18 6 6 18" /><path d="m6 6 12 12" />
                            </svg>
                          </button>
                        </div>
                      ) : (
                        <label className="flex cursor-pointer items-center gap-md rounded-sm border-2 border-dashed border-gray-300 px-xl py-lg transition-colors hover:border-brand-primary hover:bg-brand-primary-bg">
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="text-gray-400">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" x2="12" y1="3" y2="15" />
                          </svg>
                          <span className="text-sm text-gray-600">Upload Logo</span>
                          <input type="file" accept="image/*" onChange={handleLogoUpload} className="hidden" />
                        </label>
                      )}
                    </div>
                  </div>

                  {/* Color pickers */}
                  <div className="grid grid-cols-1 gap-lg md:grid-cols-4">
                    <div className="flex flex-col gap-sm">
                      <label className="text-sm font-medium text-gray-700">Primary Color</label>
                      <div className="flex items-center gap-sm">
                        <input
                          type="color"
                          value={data.primaryColor}
                          onChange={(e) => updateField('primaryColor', e.target.value)}
                          className="h-[40px] w-[60px] cursor-pointer rounded-sm border border-gray-300"
                        />
                        <input
                          type="text"
                          value={data.primaryColor}
                          onChange={(e) => updateField('primaryColor', e.target.value)}
                          className="h-[40px] flex-1 rounded-sm border border-gray-300 px-md text-sm font-mono text-gray-700 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                        />
                      </div>
                    </div>
                    <div className="flex flex-col gap-sm">
                      <label className="text-sm font-medium text-gray-700">Primary Dark</label>
                      <div className="flex items-center gap-sm">
                        <input
                          type="color"
                          value={data.primaryDark}
                          onChange={(e) => updateField('primaryDark', e.target.value)}
                          className="h-[40px] w-[60px] cursor-pointer rounded-sm border border-gray-300"
                        />
                        <input
                          type="text"
                          value={data.primaryDark}
                          onChange={(e) => updateField('primaryDark', e.target.value)}
                          className="h-[40px] flex-1 rounded-sm border border-gray-300 px-md text-sm font-mono text-gray-700 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                        />
                      </div>
                    </div>
                    <div className="flex flex-col gap-sm">
                      <label className="text-sm font-medium text-gray-700">Primary Light</label>
                      <div className="flex items-center gap-sm">
                        <input
                          type="color"
                          value={data.primaryLight}
                          onChange={(e) => updateField('primaryLight', e.target.value)}
                          className="h-[40px] w-[60px] cursor-pointer rounded-sm border border-gray-300"
                        />
                        <input
                          type="text"
                          value={data.primaryLight}
                          onChange={(e) => updateField('primaryLight', e.target.value)}
                          className="h-[40px] flex-1 rounded-sm border border-gray-300 px-md text-sm font-mono text-gray-700 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                        />
                      </div>
                    </div>
                    <div className="flex flex-col gap-sm">
                      <label className="text-sm font-medium text-gray-700">Accent Color</label>
                      <div className="flex items-center gap-sm">
                        <input
                          type="color"
                          value={data.accentColor}
                          onChange={(e) => updateField('accentColor', e.target.value)}
                          className="h-[40px] w-[60px] cursor-pointer rounded-sm border border-gray-300"
                        />
                        <input
                          type="text"
                          value={data.accentColor}
                          onChange={(e) => updateField('accentColor', e.target.value)}
                          className="h-[40px] flex-1 rounded-sm border border-gray-300 px-md text-sm font-mono text-gray-700 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                        />
                      </div>
                    </div>
                  </div>

                  {/* Text inputs */}
                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">Tagline</label>
                    <input
                      type="text"
                      value={data.tagline}
                      onChange={(e) => updateField('tagline', e.target.value)}
                      placeholder="You need one right home -- not hundreds of listings."
                      className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                  </div>

                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">Hero Heading</label>
                    <input
                      type="text"
                      value={data.heroHeading}
                      onChange={(e) => updateField('heroHeading', e.target.value)}
                      placeholder="Find Your Perfect Home"
                      className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                  </div>

                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">CTA Text</label>
                    <input
                      type="text"
                      value={data.ctaText}
                      onChange={(e) => updateField('ctaText', e.target.value)}
                      placeholder="Find My 10"
                      className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                  </div>
                </div>
              )}

              {/* Contact Details Tab */}
              {activeTab === 'contact' && (
                <div className="flex flex-col gap-xl">
                  <div className="grid grid-cols-1 gap-lg md:grid-cols-2">
                    <div className="flex flex-col gap-sm">
                      <label className="text-sm font-medium text-gray-700">Phone</label>
                      <input
                        type="tel"
                        value={data.phone}
                        onChange={(e) => updateField('phone', e.target.value)}
                        placeholder="+91 98765 43210"
                        className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                      />
                    </div>
                    <div className="flex flex-col gap-sm">
                      <label className="text-sm font-medium text-gray-700">WhatsApp</label>
                      <input
                        type="tel"
                        value={data.whatsapp}
                        onChange={(e) => updateField('whatsapp', e.target.value)}
                        placeholder="+91 98765 43210"
                        className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                      />
                    </div>
                  </div>

                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">Email</label>
                    <input
                      type="email"
                      value={data.email}
                      onChange={(e) => updateField('email', e.target.value)}
                      placeholder="hello@10projects.com"
                      className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                  </div>

                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">Address</label>
                    <textarea
                      value={data.address}
                      onChange={(e) => updateField('address', e.target.value)}
                      rows={2}
                      placeholder="Full office address"
                      className="w-full rounded-sm border border-gray-300 px-md py-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                  </div>

                  <div className="grid grid-cols-1 gap-lg md:grid-cols-2">
                    <div className="flex flex-col gap-sm">
                      <label className="text-sm font-medium text-gray-700">City</label>
                      <input
                        type="text"
                        value={data.city}
                        onChange={(e) => updateField('city', e.target.value)}
                        placeholder="Navi Mumbai"
                        className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                      />
                    </div>
                    <div className="flex flex-col gap-sm">
                      <label className="text-sm font-medium text-gray-700">State</label>
                      <input
                        type="text"
                        value={data.state}
                        onChange={(e) => updateField('state', e.target.value)}
                        placeholder="Maharashtra"
                        className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                      />
                    </div>
                  </div>
                </div>
              )}

              {/* Social Links Tab */}
              {activeTab === 'social' && (
                <div className="flex flex-col gap-xl">
                  {[
                    { key: 'facebook' as const, label: 'Facebook', placeholder: 'https://facebook.com/10projects' },
                    { key: 'instagram' as const, label: 'Instagram', placeholder: 'https://instagram.com/10projects' },
                    { key: 'linkedin' as const, label: 'LinkedIn', placeholder: 'https://linkedin.com/company/10projects' },
                    { key: 'youtube' as const, label: 'YouTube', placeholder: 'https://youtube.com/@10projects' },
                    { key: 'twitter' as const, label: 'Twitter / X', placeholder: 'https://x.com/10projects' },
                  ].map((social) => (
                    <div key={social.key} className="flex flex-col gap-sm">
                      <label className="text-sm font-medium text-gray-700">
                        {social.label}
                      </label>
                      <input
                        type="url"
                        value={data[social.key]}
                        onChange={(e) => updateField(social.key, e.target.value)}
                        placeholder={social.placeholder}
                        className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                      />
                    </div>
                  ))}
                </div>
              )}

              {/* SEO Tab */}
              {activeTab === 'seo' && (
                <div className="flex flex-col gap-xl">
                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">Site Title</label>
                    <input
                      type="text"
                      value={data.siteTitle}
                      onChange={(e) => updateField('siteTitle', e.target.value)}
                      placeholder="10Projects - AI-Powered Real Estate Discovery"
                      className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                  </div>

                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">
                      Meta Description
                    </label>
                    <textarea
                      value={data.metaDescription}
                      onChange={(e) => updateField('metaDescription', e.target.value)}
                      rows={3}
                      placeholder="AI finds the 10 best-fit residential projects for you in Navi Mumbai. No spam, no cold calls."
                      className="w-full rounded-sm border border-gray-300 px-md py-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                    <p className="text-caption text-gray-400">
                      {data.metaDescription.length}/160 characters
                    </p>
                  </div>

                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">OG Image</label>
                    <div className="flex items-center gap-lg">
                      {data.ogImageUrl ? (
                        <div className="relative">
                          <img
                            src={data.ogImageUrl}
                            alt="OG Image"
                            className="h-[80px] rounded-sm border border-gray-200 object-cover"
                          />
                          <button
                            type="button"
                            onClick={() => updateField('ogImageUrl', '')}
                            className="absolute -right-sm -top-sm flex h-[24px] w-[24px] items-center justify-center rounded-full bg-white text-gray-500 shadow-card hover:text-danger"
                            aria-label="Remove OG image"
                          >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                              <path d="M18 6 6 18" /><path d="m6 6 12 12" />
                            </svg>
                          </button>
                        </div>
                      ) : (
                        <label className="flex cursor-pointer items-center gap-md rounded-sm border-2 border-dashed border-gray-300 px-xl py-lg transition-colors hover:border-brand-primary hover:bg-brand-primary-bg">
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="text-gray-400">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <polyline points="21 15 16 10 5 21" />
                          </svg>
                          <span className="text-sm text-gray-600">Upload OG Image</span>
                          <input type="file" accept="image/*" onChange={handleOgImageUpload} className="hidden" />
                        </label>
                      )}
                    </div>
                  </div>

                  <div className="flex flex-col gap-sm">
                    <label className="text-sm font-medium text-gray-700">
                      GA Tracking ID
                    </label>
                    <input
                      type="text"
                      value={data.gaTrackingId}
                      onChange={(e) => updateField('gaTrackingId', e.target.value)}
                      placeholder="G-XXXXXXXXXX"
                      className="h-[40px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    />
                  </div>
                </div>
              )}
            </div>

            {/* Save button */}
            <div className="border-t border-gray-200 px-2xl py-lg">
              <button
                type="submit"
                disabled={saving}
                className="inline-flex items-center gap-sm rounded-sm bg-brand-primary px-xl py-sm text-sm font-semibold text-white transition-colors hover:bg-brand-primary-dark disabled:opacity-50 disabled:pointer-events-none"
              >
                {saving ? 'Saving...' : 'Save Changes'}
              </button>
            </div>
          </div>
        </form>
      </div>
    </>
  );
}
