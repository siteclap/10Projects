import { cn } from '@/lib/utils/cn';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import type { DeveloperCard } from '@/lib/types/developer';

interface DeveloperLogosProps {
  developers: DeveloperCard[];
  className?: string;
}

export function DeveloperLogos({ developers, className }: DeveloperLogosProps) {
  return (
    <Section variant="white" className={className}>
      <Container>
        <div className="text-center">
          <h2 className="text-h2 text-gray-900">Trusted Developer Partners</h2>
          <p className="mt-sm text-base text-gray-500">
            Projects from India&apos;s most reputed real estate developers
          </p>
        </div>

        <div className="mt-3xl grid grid-cols-2 gap-lg sm:grid-cols-4 lg:grid-cols-8">
          {developers.map((dev) => (
            <div
              key={dev.id}
              className={cn(
                'flex h-[80px] items-center justify-center rounded-md bg-gray-100 px-lg transition-colors hover:bg-gray-200'
              )}
            >
              {dev.logo ? (
                <img
                  src={dev.logo}
                  alt={dev.title}
                  className="h-[40px] max-w-full object-contain"
                  loading="lazy"
                />
              ) : (
                <span className="text-center text-caption font-medium text-gray-500">
                  {dev.title}
                </span>
              )}
            </div>
          ))}
        </div>
      </Container>
    </Section>
  );
}
