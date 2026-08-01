import { Section } from '@/components/layout/Section';
import { Container } from '@/components/layout/Container';

const trustItems = [
  {
    icon: (
      <svg
        width="32"
        height="32"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
        className="text-success"
        aria-hidden="true"
      >
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
    ),
    title: 'RERA-Verified Data',
    description:
      'All project details sourced directly from RERA registrations and verified developer information.',
  },
  {
    icon: (
      <svg
        width="32"
        height="32"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
        className="text-brand-primary"
        aria-hidden="true"
      >
        <path d="m18 16 4-4-4-4" />
        <path d="m6 8-4 4 4 4" />
        <path d="m14.5 4-5 16" />
      </svg>
    ),
    title: 'Unbiased AI Rankings',
    description:
      'Our scoring engine uses 20 objective categories. No paid placements, no developer influence.',
  },
  {
    icon: (
      <svg
        width="32"
        height="32"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
        className="text-accent-dark"
        aria-hidden="true"
      >
        <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
      </svg>
    ),
    title: 'No Spam Promise',
    description:
      'Your data is never sold to brokers. You control who contacts you, and when.',
  },
] as const;

export function TrustBar() {
  return (
    <Section variant="alt">
      <Container>
        <div className="grid grid-cols-1 gap-2xl md:grid-cols-3">
          {trustItems.map((item) => (
            <div key={item.title} className="flex flex-col items-center text-center">
              <div className="mb-lg flex h-[56px] w-[56px] items-center justify-center rounded-full bg-white shadow-card">
                {item.icon}
              </div>
              <h3 className="text-h4 text-gray-900">{item.title}</h3>
              <p className="mt-sm text-sm text-gray-500">{item.description}</p>
            </div>
          ))}
        </div>
      </Container>
    </Section>
  );
}
