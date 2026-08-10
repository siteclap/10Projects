import Link from 'next/link';
import { Button } from '@/components/ui/Button';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { ROUTES } from '@/lib/constants/routes';

const steps = [
  {
    icon: '\u{1F50D}',
    title: 'Search Projects',
    description:
      'Search by project name, location, or developer. Filter by budget, BHK, and possession date.',
  },
  {
    icon: '\u{1F4CA}',
    title: 'Compare & Shortlist',
    description:
      'View RERA-verified details, pricing, floor plans, and pros & cons for every project.',
  },
  {
    icon: '\u{1F3E0}',
    title: 'Book a Free Visit',
    description:
      'Schedule a free site visit with cab pickup. Our advisor helps you — no pressure.',
  },
] as const;

export function HowItWorks() {
  return (
    <Section variant="white">
      <Container>
        <div className="text-center">
          <h2 className="text-h2 text-gray-900">How It Works</h2>
          <p className="mx-auto mt-sm max-w-narrow text-base text-gray-500">
            Three simple steps to find your best-fit home. No sign-up required,
            no spam calls.
          </p>
        </div>

        <div className="mt-xl grid grid-cols-1 gap-2xl md:grid-cols-3">
          {steps.map((step, index) => (
            <div
              key={step.title}
              className="flex flex-col items-center rounded-md bg-gray-50 p-2xl text-center"
            >
              <span
                className="text-[48px] leading-none"
                role="img"
                aria-label={step.title}
              >
                {step.icon}
              </span>
              <div className="mt-xs flex h-[24px] w-[24px] items-center justify-center rounded-full bg-brand-primary text-caption font-semibold text-white">
                {index + 1}
              </div>
              <h3 className="mt-lg text-h4 text-gray-900">{step.title}</h3>
              <p className="mt-sm text-sm text-gray-500">{step.description}</p>
            </div>
          ))}
        </div>

        <div className="mt-xl flex justify-center">
          <Button variant="primary" size="lg" asChild>
            <Link href={ROUTES.PROJECTS}>
              Explore Projects
              <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="ml-xs"
                aria-hidden="true"
              >
                <path d="M5 12h14" />
                <path d="m12 5 7 7-7 7" />
              </svg>
            </Link>
          </Button>
        </div>
      </Container>
    </Section>
  );
}
