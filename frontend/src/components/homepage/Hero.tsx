'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { cn } from '@/lib/utils/cn';
import { Chip } from '@/components/ui/Chip';
import { ROUTES } from '@/lib/constants/routes';

const cities = [
  { label: 'Navi Mumbai', slug: 'navi-mumbai', active: true },
  { label: 'Mumbai', slug: 'mumbai', active: false },
  { label: 'Thane', slug: 'thane', active: false },
  { label: 'Pune', slug: 'pune', active: false },
  { label: 'Panvel', slug: 'panvel', active: false },
] as const;

const stats = [
  { value: '156', label: 'Projects Analysed' },
  { value: '12,847', label: 'Buyers Matched' },
  { value: '94%', label: 'Said "Accurate"' },
] as const;

export function Hero() {
  const router = useRouter();
  const [query, setQuery] = useState('');
  const [selectedCity, setSelectedCity] = useState('navi-mumbai');

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    router.push(ROUTES.ASSESSMENT);
  }

  return (
    <section className="relative overflow-hidden bg-gray-900 pb-3xl pt-4xl md:pb-4xl md:pt-5xl">
      {/* Background gradient effects */}
      <div
        className="absolute inset-0 opacity-30"
        style={{
          background:
            'radial-gradient(ellipse 80% 50% at 50% -20%, rgba(26, 86, 219, 0.3), transparent)',
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
              AI-powered property matching
            </span>
          </div>

          {/* Headline */}
          <h1 className="text-display tracking-display text-white">
            Find your dream home,
            <br />
            <span className="text-brand-primary-light">intelligently.</span>
          </h1>

          {/* Subtitle */}
          <p className="mx-auto mt-xl max-w-[560px] text-body-lg text-gray-400">
            India&apos;s first AI-powered real estate platform that analyses 150+
            projects to find the 10 best-fit matches for you.
          </p>

          {/* Search bar */}
          <form
            onSubmit={handleSubmit}
            className="mx-auto mt-2xl flex max-w-[520px] items-center overflow-hidden rounded-full bg-white shadow-hero"
          >
            <input
              type="text"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Tell us what you're looking for..."
              className="flex-1 bg-transparent px-xl py-lg text-base text-gray-900 placeholder:text-gray-400 focus:outline-none"
              aria-label="Describe what you are looking for"
            />
            <button
              type="submit"
              className="mr-xs flex h-[44px] w-[44px] shrink-0 items-center justify-center rounded-full bg-brand-primary text-white transition-colors hover:bg-brand-primary-dark"
              aria-label="Start AI matching"
            >
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
            </button>
          </form>

          {/* Hint */}
          <p className="mt-md text-sm text-gray-500">
            No sign-up required &bull; Free forever
          </p>

          {/* City chips */}
          <div className="mt-xl flex flex-wrap items-center justify-center gap-sm">
            {cities.map((city) => (
              <Chip
                key={city.slug}
                selected={selectedCity === city.slug}
                onClick={() => setSelectedCity(city.slug)}
                disabled={!city.active}
                className={cn(
                  selectedCity === city.slug
                    ? 'bg-white text-gray-900 hover:bg-gray-100'
                    : 'border border-gray-700 bg-transparent text-gray-400 hover:bg-gray-800 hover:text-gray-300',
                  !city.active && 'cursor-not-allowed opacity-50'
                )}
              >
                {city.label}
                {!city.active && (
                  <span className="ml-xs text-caption text-gray-500">Soon</span>
                )}
              </Chip>
            ))}
          </div>
        </div>

        {/* Stats row */}
        <div className="mx-auto mt-3xl grid max-w-narrow grid-cols-3 gap-lg border-t border-gray-800 pt-2xl">
          {stats.map((stat) => (
            <div key={stat.label} className="text-center">
              <p className="text-h2 tabular-nums text-white">{stat.value}</p>
              <p className="mt-xs text-sm text-gray-400">{stat.label}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
