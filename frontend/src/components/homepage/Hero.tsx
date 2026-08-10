'use client';

import { useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import { cn } from '@/lib/utils/cn';
import { Chip } from '@/components/ui/Chip';
import { ROUTES } from '@/lib/constants/routes';
import { HeroChat } from './HeroChat';
import { AnalysingDots } from '@/components/assessment/AnalysingDots';

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

type ChatState = 'idle' | 'chatting' | 'analysing';

export function Hero() {
  const router = useRouter();
  const [selectedCity, setSelectedCity] = useState('navi-mumbai');
  const [chatState, setChatState] = useState<ChatState>('idle');

  const handleSearchClick = useCallback(() => {
    setChatState('chatting');
  }, []);

  const handleChatComplete = useCallback(
    (sessionUuid: string) => {
      setChatState('analysing');
      setTimeout(() => {
        router.push(ROUTES.RESULTS(sessionUuid));
      }, 2000);
    },
    [router]
  );

  const handleChatClose = useCallback(() => {
    setChatState('idle');
  }, []);

  return (
    <section className="relative overflow-hidden bg-gray-900 pb-3xl pt-4xl md:pb-4xl md:pt-5xl">
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

          {/* Interactive area — fixed min-height to prevent layout shift */}
          <div className="mx-auto mt-2xl min-h-[160px] max-w-[520px]">
            {/* Idle: Search bar + hint + city chips */}
            {chatState === 'idle' && (
              <div className="animate-[fadeUp_0.3s_ease-out]">
                {/* Search bar */}
                <button
                  type="button"
                  onClick={handleSearchClick}
                  className="flex w-full items-center overflow-hidden rounded-full bg-white shadow-hero transition-shadow hover:shadow-dropdown"
                >
                  <span className="flex-1 px-xl py-lg text-left text-base text-gray-400">
                    Tell us what you&apos;re looking for...
                  </span>
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
                </button>

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
            )}

            {/* Chatting: Inline assessment chat */}
            {chatState === 'chatting' && (
              <div className="animate-[fadeScale_0.3s_ease-out] text-left">
                <HeroChat
                  selectedCity={selectedCity}
                  onClose={handleChatClose}
                  onComplete={handleChatComplete}
                />
              </div>
            )}

            {/* Analysing: Loading animation before redirect */}
            {chatState === 'analysing' && (
              <div className="flex animate-[fadeUp_0.3s_ease-out] flex-col items-center justify-center gap-lg py-2xl">
                <AnalysingDots
                  text="Finding your best matches..."
                  className="[&_span]:text-gray-400"
                />
                <p className="text-sm text-gray-500">
                  Analysing 150+ projects against your preferences
                </p>
              </div>
            )}
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
