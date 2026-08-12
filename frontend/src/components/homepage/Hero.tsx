'use client';

import { useState } from 'react';
import { cn } from '@/lib/utils/cn';
import { SearchBar } from './SearchBar';

const cities = [
  { label: 'Navi Mumbai', slug: 'navi-mumbai', active: true },
  { label: 'Mumbai', slug: 'mumbai', active: false },
  { label: 'Thane', slug: 'thane', active: false },
  { label: 'Pune', slug: 'pune', active: false },
  { label: 'Panvel', slug: 'panvel', active: false },
] as const;

const stats = [
  { value: '150+', label: 'Projects Listed' },
  { value: '12,847', label: 'Happy Buyers' },
  { value: '100%', label: 'RERA Verified' },
] as const;

interface HeroProps {
  activeCategories?: string[];
  heroDesktop?: string;
  heroMobile?: string;
}

export function Hero({ activeCategories, heroDesktop, heroMobile }: HeroProps) {
  const [selectedCity, setSelectedCity] = useState('navi-mumbai');

  return (
    <section
      className="relative overflow-hidden pb-3xl pt-4xl md:pb-4xl md:pt-5xl"
      style={{ backgroundColor: 'var(--hero-bg, #111827)' }}
    >
      {/* Background hero image */}
      <img
        src={heroDesktop || 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=80'}
        alt=""
        aria-hidden="true"
        className={cn(
          'absolute inset-0 h-full w-full object-cover opacity-20',
          heroMobile && 'hidden md:block'
        )}
      />
      {heroMobile && (
        <img
          src={heroMobile}
          alt=""
          aria-hidden="true"
          className="absolute inset-0 h-full w-full object-cover opacity-20 md:hidden"
        />
      )}

      {/* Background gradient effects */}
      <div
        className="absolute inset-0 opacity-30"
        style={{
          background:
            'radial-gradient(ellipse 80% 50% at 50% -20%, rgba(75, 28, 176, 0.3), transparent)',
        }}
        aria-hidden="true"
      />

      <div className="relative mx-auto max-w-container px-lg md:px-2xl">
        <div className="mx-auto max-w-narrow text-center">
          {/* Badge */}
          <div className="mb-xl inline-flex items-center gap-sm rounded-full border border-gray-700 bg-gray-800/50 px-lg py-xs backdrop-blur-sm">
            <span className="relative flex h-[8px] w-[8px]">
              <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
              <span className="relative inline-flex h-[8px] w-[8px] rounded-full bg-emerald-500" />
            </span>
            <span className="text-sm font-medium text-gray-300">
              Navi Mumbai&apos;s trusted property platform
            </span>
          </div>

          {/* Headline */}
          <h1 className="text-display tracking-display text-white">
            Find your dream home
            <br />
            <span className="text-brand-primary-light">in Navi Mumbai.</span>
          </h1>

          {/* Subtitle */}
          <p className="mx-auto mt-xl max-w-[560px] text-body-lg text-gray-400">
            Search 150+ verified projects by name, location, or developer.
            Compare prices, check RERA, and book free site visits.
          </p>

          {/* Search bar + city chips */}
          <div className="mx-auto mt-2xl max-w-[520px]">
            <SearchBar activeCategories={activeCategories} />

            {/* Hint */}
            <p className="mt-md text-sm text-gray-500">
              No sign-up required &bull; Free forever
            </p>

            {/* City chips */}
            <div className="mt-xl flex flex-wrap items-center justify-center gap-md">
              {cities.map((city) => (
                <button
                  key={city.slug}
                  type="button"
                  onClick={() => city.active && setSelectedCity(city.slug)}
                  disabled={!city.active}
                  className={cn(
                    'inline-flex h-[40px] items-center gap-sm rounded-full px-xl text-sm font-medium transition-all duration-200',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-900',
                    selectedCity === city.slug
                      ? 'bg-white text-gray-900 shadow-lg'
                      : 'border border-gray-600 bg-gray-800/50 text-gray-300 backdrop-blur-sm hover:border-gray-400 hover:bg-gray-700/60 hover:text-white',
                    !city.active && 'cursor-not-allowed opacity-30'
                  )}
                >
                  {selectedCity === city.slug && (
                    <span className="flex h-[6px] w-[6px] rounded-full bg-brand-primary" />
                  )}
                  {city.label}
                  {!city.active && (
                    <span className="rounded-full bg-gray-700 px-sm py-[1px] text-[10px] font-medium text-gray-400">
                      Soon
                    </span>
                  )}
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Stats row */}
        <div className="mx-auto mt-3xl grid max-w-narrow grid-cols-3 gap-md pt-2xl">
          {stats.map((stat) => (
            <div
              key={stat.label}
              className="flex flex-col items-center gap-xs rounded-lg border border-gray-700/50 bg-gray-800/40 px-lg py-xl backdrop-blur-sm"
            >
              <p className="text-h2 tabular-nums text-white">{stat.value}</p>
              <p className="text-caption text-gray-400">{stat.label}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
