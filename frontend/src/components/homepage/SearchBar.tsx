'use client';

import { useState, useRef, useEffect, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import { cn } from '@/lib/utils/cn';

/* ---------- Search Category Tabs ---------- */

interface SearchCategory {
  key: string;
  label: string;
  placeholder: string;
}

const ALL_CATEGORIES: SearchCategory[] = [
  { key: 'buy', label: 'Buy', placeholder: 'Search projects to buy...' },
  { key: 'rent', label: 'Rent', placeholder: 'Search rental properties...' },
  { key: 'commercial', label: 'Commercial', placeholder: 'Search commercial spaces...' },
  { key: 'pg', label: 'PG', placeholder: 'Search PG & co-living...' },
  { key: 'plots', label: 'Plots', placeholder: 'Search plots & land...' },
];

/* ---------- Search Index ---------- */

interface SearchItem {
  label: string;
  subtitle?: string;
  href: string;
  category: 'project' | 'location' | 'developer';
}

const SEARCH_INDEX: SearchItem[] = [
  // Projects
  { label: 'Lodha Palava Crown', subtitle: 'Kharghar · ₹92L–1.38Cr', href: '/navi-mumbai/kharghar/lodha-palava-crown/', category: 'project' },
  { label: 'Paradise Sai World Empire', subtitle: 'Kharghar · ₹1.05Cr', href: '/navi-mumbai/kharghar/paradise-sai-world-empire/', category: 'project' },
  { label: 'Balaji Symphony', subtitle: 'Panvel · ₹48L–75L', href: '/navi-mumbai/panvel/balaji-symphony/', category: 'project' },
  { label: 'Arihant Aspire', subtitle: 'Panvel · ₹42L–62L', href: '/navi-mumbai/panvel/arihant-aspire/', category: 'project' },
  { label: 'JERAI Elysium', subtitle: 'Ulwe · ₹79L', href: '/navi-mumbai/ulwe/jerai-elysium/', category: 'project' },
  { label: 'L&T Seawoods Residences', subtitle: 'Vashi · ₹1.58Cr–2.35Cr', href: '/navi-mumbai/vashi/lt-seawoods-residences/', category: 'project' },
  { label: 'Godrej Vihaa', subtitle: 'Airoli · ₹1.25Cr', href: '/navi-mumbai/airoli/godrej-vihaa/', category: 'project' },
  // Locations
  { label: 'Kharghar', subtitle: '42 projects · Avg ₹8,500/sqft', href: '/navi-mumbai/kharghar/', category: 'location' },
  { label: 'Panvel', subtitle: '38 projects · Avg ₹5,800/sqft', href: '/navi-mumbai/panvel/', category: 'location' },
  { label: 'Ulwe', subtitle: '35 projects · Avg ₹6,200/sqft', href: '/navi-mumbai/ulwe/', category: 'location' },
  { label: 'Vashi', subtitle: '18 projects · Avg ₹14,200/sqft', href: '/navi-mumbai/vashi/', category: 'location' },
  // Developers
  { label: 'Lodha Group', subtitle: '120 projects · 4.2★', href: '/developers/lodha-group/', category: 'developer' },
  { label: 'Godrej Properties', subtitle: '95 projects · 4.3★', href: '/developers/godrej-properties/', category: 'developer' },
  { label: 'L&T Realty', subtitle: '45 projects · 4.4★', href: '/developers/lt-realty/', category: 'developer' },
  { label: 'Paradise Group', subtitle: '30 projects · 4.0★', href: '/developers/paradise-group/', category: 'developer' },
  { label: 'Arihant Superstructures', subtitle: '55 projects · 3.9★', href: '/developers/arihant-superstructures/', category: 'developer' },
  { label: 'JERAI Group', subtitle: '18 projects · 3.8★', href: '/developers/jerai-group/', category: 'developer' },
  { label: 'Balaji Group', subtitle: '22 projects · 3.7★', href: '/developers/balaji-group/', category: 'developer' },
  { label: 'Haware Group', subtitle: '65 projects · 3.6★', href: '/developers/haware-group/', category: 'developer' },
];

const CATEGORY_LABELS: Record<SearchItem['category'], string> = {
  project: 'Projects',
  location: 'Locations',
  developer: 'Developers',
};

const CATEGORY_ICONS: Record<SearchItem['category'], React.ReactNode> = {
  project: (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
      <polyline points="9 22 9 12 15 12 15 22" />
    </svg>
  ),
  location: (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
      <circle cx="12" cy="10" r="3" />
    </svg>
  ),
  developer: (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4-4v2" />
      <circle cx="9" cy="7" r="4" />
      <path d="M22 21v-2a4 4 0 00-3-3.87" />
      <path d="M16 3.13a4 4 0 010 7.75" />
    </svg>
  ),
};

/* ---------- Component ---------- */

interface SearchBarProps {
  activeCategories?: string[];
}

export function SearchBar({ activeCategories = ['buy'] }: SearchBarProps) {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState(activeCategories[0] || 'buy');
  const [query, setQuery] = useState('');
  const [open, setOpen] = useState(false);
  const [activeIndex, setActiveIndex] = useState(-1);
  const containerRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  const currentCategory = ALL_CATEGORIES.find((c) => c.key === activeTab) || ALL_CATEGORIES[0];

  // Filter results
  const results = query.length >= 2
    ? SEARCH_INDEX.filter((item) =>
        item.label.toLowerCase().includes(query.toLowerCase())
      )
    : [];

  // Group by category
  const grouped = (['project', 'location', 'developer'] as const)
    .map((cat) => ({
      category: cat,
      items: results.filter((r) => r.category === cat),
    }))
    .filter((g) => g.items.length > 0);

  const flatResults = grouped.flatMap((g) => g.items);

  // Navigate to result
  const navigateTo = useCallback(
    (href: string) => {
      setOpen(false);
      setQuery('');
      router.push(href);
    },
    [router]
  );

  // Keyboard navigation
  const handleKeyDown = useCallback(
    (e: React.KeyboardEvent) => {
      if (e.key === 'Escape') {
        setOpen(false);
        inputRef.current?.blur();
        return;
      }
      if (!open || flatResults.length === 0) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        setActiveIndex((i) => (i < flatResults.length - 1 ? i + 1 : 0));
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        setActiveIndex((i) => (i > 0 ? i - 1 : flatResults.length - 1));
      } else if (e.key === 'Enter' && activeIndex >= 0) {
        e.preventDefault();
        navigateTo(flatResults[activeIndex].href);
      }
    },
    [open, flatResults, activeIndex, navigateTo]
  );

  // Close on outside click
  useEffect(() => {
    function handleClick(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

  // Reset active index when results change
  useEffect(() => {
    setActiveIndex(-1);
  }, [query]);

  const showDropdown = open && query.length >= 2;

  return (
    <div ref={containerRef} className="relative w-full">
      {/* Category Tabs */}
      <div className="mb-sm flex items-center justify-center gap-xs">
        {ALL_CATEGORIES.map((cat) => {
          const isActive = cat.key === activeTab;
          const isEnabled = activeCategories.includes(cat.key);

          return (
            <button
              key={cat.key}
              type="button"
              onClick={() => {
                if (isEnabled) {
                  setActiveTab(cat.key);
                  inputRef.current?.focus();
                }
              }}
              className={cn(
                'relative rounded-t-lg px-lg py-sm text-sm font-medium transition-all duration-200',
                isActive
                  ? 'bg-white text-brand-primary shadow-sm'
                  : isEnabled
                    ? 'bg-white/10 text-gray-300 hover:bg-white/20 hover:text-white'
                    : 'cursor-not-allowed bg-white/5 text-gray-500'
              )}
            >
              {cat.label}
              {!isEnabled && (
                <span className="ml-xs inline-block rounded-full bg-gray-700/80 px-[6px] py-[1px] text-[9px] font-medium text-gray-400">
                  Soon
                </span>
              )}
              {isActive && (
                <span className="absolute bottom-0 left-1/2 h-[2px] w-[60%] -translate-x-1/2 rounded-t-full bg-brand-primary" />
              )}
            </button>
          );
        })}
      </div>

      {/* Input */}
      <div className="flex w-full items-center overflow-hidden rounded-full bg-white shadow-hero transition-shadow hover:shadow-dropdown">
        <input
          ref={inputRef}
          type="text"
          value={query}
          onChange={(e) => {
            setQuery(e.target.value);
            setOpen(true);
          }}
          onFocus={() => setOpen(true)}
          onKeyDown={handleKeyDown}
          placeholder={currentCategory.placeholder}
          className="flex-1 bg-transparent px-xl py-lg text-base text-gray-900 placeholder:text-gray-400 focus:outline-none"
          role="combobox"
          aria-expanded={showDropdown}
          aria-haspopup="listbox"
          aria-autocomplete="list"
        />
        <span className="mr-xs flex h-[44px] w-[44px] shrink-0 items-center justify-center rounded-full bg-brand-primary text-white">
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
          >
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
          </svg>
        </span>
      </div>

      {/* Dropdown */}
      {showDropdown && (
        <div
          className="absolute left-0 right-0 top-full z-50 mt-sm overflow-hidden rounded-xl border border-gray-200 bg-white shadow-dropdown"
          role="listbox"
        >
          {grouped.length === 0 ? (
            <div className="px-xl py-lg text-sm text-gray-400">
              No results for &ldquo;{query}&rdquo;
            </div>
          ) : (
            grouped.map((group) => (
              <div key={group.category}>
                {/* Category header */}
                <div className="flex items-center gap-sm border-b border-gray-100 bg-gray-50 px-xl py-sm">
                  <span className="text-gray-400">{CATEGORY_ICONS[group.category]}</span>
                  <span className="text-xs font-semibold uppercase tracking-wider text-gray-500">
                    {CATEGORY_LABELS[group.category]}
                  </span>
                </div>
                {/* Items */}
                {group.items.map((item) => {
                  const idx = flatResults.indexOf(item);
                  return (
                    <button
                      key={item.href}
                      type="button"
                      role="option"
                      aria-selected={idx === activeIndex}
                      onClick={() => navigateTo(item.href)}
                      onMouseEnter={() => setActiveIndex(idx)}
                      className={`flex w-full items-center gap-md px-xl py-md text-left transition-colors ${
                        idx === activeIndex
                          ? 'bg-brand-primary/5 text-brand-primary'
                          : 'text-gray-700 hover:bg-gray-50'
                      }`}
                    >
                      <div className="min-w-0 flex-1">
                        <p className="truncate text-sm font-medium">{item.label}</p>
                        {item.subtitle && (
                          <p className="truncate text-xs text-gray-400">{item.subtitle}</p>
                        )}
                      </div>
                      <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className="shrink-0 text-gray-300"
                        aria-hidden="true"
                      >
                        <path d="m9 18 6-6-6-6" />
                      </svg>
                    </button>
                  );
                })}
              </div>
            ))
          )}
        </div>
      )}
    </div>
  );
}
