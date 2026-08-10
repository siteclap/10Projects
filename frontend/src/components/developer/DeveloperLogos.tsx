import { cn } from '@/lib/utils/cn';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import type { DeveloperCard } from '@/lib/types/developer';

interface DeveloperLogosProps {
  developers: DeveloperCard[];
  className?: string;
}

function getInitials(name: string): string {
  return name
    .split(' ')
    .map((w) => w[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();
}

export function DeveloperLogos({ developers, className }: DeveloperLogosProps) {
  return (
    <Section variant="white" className={className}>
      <Container>
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-h2 text-gray-900">Our Top Developers</h2>
            <p className="mt-sm text-base text-gray-500">
              Projects from India&apos;s most reputed real estate developers
            </p>
          </div>
          <a
            href="/developers"
            className="hidden items-center gap-xs text-sm font-medium text-brand-primary hover:text-brand-primary-dark sm:inline-flex"
          >
            View All
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="m9 18 6-6-6-6" />
            </svg>
          </a>
        </div>

        <div className="mt-xl grid grid-cols-2 gap-md sm:grid-cols-3 lg:grid-cols-4">
          {developers.map((dev) => (
            <a
              key={dev.id}
              href={`/developers/${dev.slug}`}
              className="group flex flex-col items-center rounded-lg border border-gray-200 bg-white p-xl text-center no-underline transition-all hover:border-brand-primary/30 hover:shadow-md hover:no-underline"
            >
              {/* Logo / Initials */}
              <div className="flex h-[56px] w-[56px] items-center justify-center rounded-full bg-gray-100 transition-colors group-hover:bg-brand-primary/10">
                {dev.logo ? (
                  <img
                    src={dev.logo}
                    alt={dev.title}
                    className="h-[32px] max-w-[48px] object-contain"
                    loading="lazy"
                  />
                ) : (
                  <span className="text-base font-bold text-gray-500 group-hover:text-brand-primary">
                    {getInitials(dev.title)}
                  </span>
                )}
              </div>

              {/* Name + tier */}
              <h3 className="mt-md text-sm font-semibold text-gray-900 group-hover:text-brand-primary">
                {dev.title}
              </h3>
              <span
                className={cn(
                  'mt-xs rounded-full px-sm py-[1px] text-[10px] font-semibold uppercase tracking-wide',
                  dev.tier === 'tier_1'
                    ? 'bg-accent/10 text-accent-dark'
                    : 'bg-gray-100 text-gray-500'
                )}
              >
                {dev.tier === 'tier_1' ? 'Premium' : 'Trusted'}
              </span>

              {/* Stats */}
              <div className="mt-md flex w-full items-center justify-center gap-lg border-t border-gray-100 pt-md">
                <div>
                  <p className="text-sm font-bold text-gray-900">{dev.total_projects_completed}+</p>
                  <p className="text-[10px] text-gray-400">Projects</p>
                </div>
                <div className="h-[24px] w-px bg-gray-100" />
                <div>
                  <p className="text-sm font-bold text-gray-900">
                    {dev.customer_rating}
                    <span className="text-accent"> &#9733;</span>
                  </p>
                  <p className="text-[10px] text-gray-400">Rating</p>
                </div>
              </div>
            </a>
          ))}
        </div>
      </Container>
    </Section>
  );
}
