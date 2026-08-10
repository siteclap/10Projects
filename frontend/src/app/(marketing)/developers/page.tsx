import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { DeveloperCard } from '@/components/developer/DeveloperCard';
import { JsonLd } from '@/components/seo/JsonLd';
import { breadcrumbJsonLd } from '@/lib/seo/json-ld';
import type { DeveloperCard as DeveloperCardType } from '@/lib/types/developer';

const SITE_NAME = '10Projects';

export function generateMetadata(): Metadata {
  const title = `Trusted Developers in Navi Mumbai — Ratings & Reviews — ${SITE_NAME}`;
  const description =
    'Explore verified real estate developers in Navi Mumbai. View completed projects, customer ratings, RERA compliance, and delivery track record for every developer on 10Projects.';

  return {
    title,
    description,
    openGraph: {
      title,
      description,
      url: 'https://10projects.com/developers',
      siteName: SITE_NAME,
      type: 'website',
      locale: 'en_IN',
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description,
    },
    robots: { index: true, follow: true },
    alternates: { canonical: '/developers' },
  };
}

/* ---------- Mock Data ---------- */

const MOCK_DEVELOPERS: DeveloperCardType[] = [
  {
    id: 1,
    title: 'Lodha Group',
    slug: 'lodha-group',
    logo: null,
    tier: 'tier_1',
    total_projects_completed: 120,
    customer_rating: 4.2,
  },
  {
    id: 2,
    title: 'Godrej Properties',
    slug: 'godrej-properties',
    logo: null,
    tier: 'tier_1',
    total_projects_completed: 95,
    customer_rating: 4.3,
  },
  {
    id: 3,
    title: 'L&T Realty',
    slug: 'lt-realty',
    logo: null,
    tier: 'tier_1',
    total_projects_completed: 45,
    customer_rating: 4.4,
  },
  {
    id: 4,
    title: 'Paradise Group',
    slug: 'paradise-group',
    logo: null,
    tier: 'tier_2',
    total_projects_completed: 30,
    customer_rating: 4.0,
  },
  {
    id: 5,
    title: 'Arihant Superstructures',
    slug: 'arihant-superstructures',
    logo: null,
    tier: 'tier_2',
    total_projects_completed: 55,
    customer_rating: 3.9,
  },
  {
    id: 6,
    title: 'JERAI Group',
    slug: 'jerai-group',
    logo: null,
    tier: 'tier_2',
    total_projects_completed: 18,
    customer_rating: 3.8,
  },
  {
    id: 7,
    title: 'Haware Group',
    slug: 'haware-group',
    logo: null,
    tier: 'tier_2',
    total_projects_completed: 65,
    customer_rating: 3.6,
  },
  {
    id: 8,
    title: 'Sunteck Realty',
    slug: 'sunteck-realty',
    logo: null,
    tier: 'tier_1',
    total_projects_completed: 38,
    customer_rating: 4.1,
  },
];

/* ---------- Page ---------- */

export default function DevelopersArchivePage() {
  const breadcrumbLdItems = [
    { name: 'Home', url: '/' },
    { name: 'Developers', url: '/developers' },
  ];

  return (
    <>
      <JsonLd data={breadcrumbJsonLd(breadcrumbLdItems)} />

      {/* Hero */}
      <Section variant="alt" className="py-3xl">
        <Container>
          <h1 className="text-h1 text-gray-900">
            Trusted Developers in Navi Mumbai
          </h1>
          <p className="mt-md max-w-[640px] text-body-lg text-gray-600">
            Every developer on 10Projects is evaluated for delivery track record,
            RERA compliance, financial stability, and customer satisfaction. Choose
            with confidence.
          </p>
        </Container>
      </Section>

      {/* Developer grid */}
      <Section>
        <Container>
          <div className="grid grid-cols-1 gap-lg sm:grid-cols-2 lg:grid-cols-4">
            {MOCK_DEVELOPERS.map((developer) => (
              <DeveloperCard key={developer.id} developer={developer} />
            ))}
          </div>
        </Container>
      </Section>
    </>
  );
}
