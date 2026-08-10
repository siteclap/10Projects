import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';

const reasons = [
  {
    icon: (
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M12 2a4 4 0 0 0-4 4c0 2 1 3.5 2 4.5L12 12l2-1.5c1-1 2-2.5 2-4.5a4 4 0 0 0-4-4Z" />
        <path d="M12 12v10" />
        <path d="M8 22h8" />
      </svg>
    ),
    title: 'Smart Search',
    description: 'Search 150+ projects by name, location, or developer. Compare prices, configurations, and possession dates instantly.',
  },
  {
    icon: (
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
        <path d="m9 12 2 2 4-4" />
      </svg>
    ),
    title: 'RERA-Verified Data',
    description: 'Every project listing is verified against RERA records. No fake inventory or misleading prices.',
  },
  {
    icon: (
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M12 2v20" />
        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
      </svg>
    ),
    title: 'Zero Brokerage',
    description: 'Buy directly at builder price. No hidden fees, no broker commission — guaranteed lowest price.',
  },
  {
    icon: (
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
        <polyline points="14 2 14 8 20 8" />
        <line x1="16" x2="8" y1="13" y2="13" />
        <line x1="16" x2="8" y1="17" y2="17" />
      </svg>
    ),
    title: 'Unbiased Reports',
    description: 'Get pros, cons, and Fit Score breakdowns for every project. We show the full picture — not just the good side.',
  },
  {
    icon: (
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 10l-2-4H7L5 10l-2.5 1.1C1.7 11.3 1 12.1 1 13v3c0 .6.4 1 1 1h2" />
        <circle cx="7" cy="17" r="2" />
        <circle cx="17" cy="17" r="2" />
      </svg>
    ),
    title: 'Free Site Visits',
    description: 'Book a free site visit with cab pickup. Our advisor accompanies you — no pressure, no hard sell.',
  },
  {
    icon: (
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M18.36 6.64a9 9 0 1 1-12.73 0" />
        <line x1="12" x2="12" y1="2" y2="12" />
      </svg>
    ),
    title: 'No Spam Guarantee',
    description: 'Your data is never sold to brokers. We contact you only when you ask — and stop when you say stop.',
  },
];

export function WhyUs() {
  return (
    <Section variant="alt">
      <Container>
        <div className="text-center">
          <h2 className="text-h2 text-gray-900">Why 10Projects?</h2>
          <p className="mt-sm text-base text-gray-500">
            India&apos;s first transparent property platform — built for home buyers, not brokers
          </p>
        </div>

        <div className="mt-xl grid grid-cols-1 gap-lg sm:grid-cols-2 lg:grid-cols-3">
          {reasons.map((reason) => (
            <div
              key={reason.title}
              className="rounded-lg border border-gray-200 bg-white p-xl transition-shadow hover:shadow-md"
            >
              <div className="flex h-[48px] w-[48px] items-center justify-center rounded-lg bg-brand-primary/10 text-brand-primary">
                {reason.icon}
              </div>
              <h3 className="mt-lg text-base font-semibold text-gray-900">{reason.title}</h3>
              <p className="mt-sm text-sm leading-relaxed text-gray-500">{reason.description}</p>
            </div>
          ))}
        </div>
      </Container>
    </Section>
  );
}
