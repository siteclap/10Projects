import type { Metadata } from 'next';
import Link from 'next/link';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { Badge } from '@/components/ui/Badge';
import { JsonLd } from '@/components/seo/JsonLd';
import { breadcrumbJsonLd } from '@/lib/seo/json-ld';
import type { GuideCard } from '@/lib/types/guide';

const SITE_NAME = 'LeadMAAXX';

export function generateMetadata(): Metadata {
  const title = `Buyer Guides & Resources — ${SITE_NAME}`;
  const description =
    'Expert guides for home buyers in Navi Mumbai. Learn about RERA, home loans, location analysis, and how to evaluate real estate projects before buying.';

  return {
    title,
    description,
    openGraph: {
      title,
      description,
      url: 'https://leadmaaxx.com/guides',
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
    alternates: { canonical: '/guides' },
  };
}

/* ---------- Mock Data ---------- */

const MOCK_GUIDES: GuideCard[] = [
  {
    id: 1,
    title: "First-Time Buyer's Guide to Navi Mumbai",
    slug: 'first-time-buyers-guide-navi-mumbai',
    excerpt:
      'Everything you need to know before buying your first home in Navi Mumbai. From choosing the right location to understanding RERA compliance, this comprehensive guide covers all the essentials.',
    thumbnail: null,
    category: 'Buying Guide',
    published_at: '2026-06-15',
    reading_time: 12,
  },
  {
    id: 2,
    title: 'Understanding RERA: Your Rights as a Home Buyer',
    slug: 'understanding-rera-home-buyer-rights',
    excerpt:
      'A complete breakdown of the Real Estate Regulatory Authority (RERA) Act and how it protects home buyers. Learn how to verify RERA registration, file complaints, and exercise your legal rights.',
    thumbnail: null,
    category: 'Legal',
    published_at: '2026-05-20',
    reading_time: 8,
  },
  {
    id: 3,
    title: 'Navi Mumbai vs Mumbai: Which Is Better for Investment?',
    slug: 'navi-mumbai-vs-mumbai-investment-comparison',
    excerpt:
      'A data-driven comparison of real estate investment potential in Navi Mumbai versus Mumbai. Covering price trends, infrastructure development, rental yields, and appreciation potential.',
    thumbnail: null,
    category: 'Investment',
    published_at: '2026-04-10',
    reading_time: 10,
  },
  {
    id: 4,
    title: 'Home Loan Guide: Interest Rates, EMI & Eligibility in 2026',
    slug: 'home-loan-guide-2026',
    excerpt:
      'Navigate the home loan process with confidence. Compare current interest rates across banks, calculate your EMI, understand eligibility criteria, and learn tax benefits under Section 80C and 24(b).',
    thumbnail: null,
    category: 'Finance',
    published_at: '2026-03-25',
    reading_time: 15,
  },
];

function getCategoryVariant(category: string): 'primary' | 'accent' | 'success' | 'default' {
  switch (category) {
    case 'Buying Guide':
      return 'primary';
    case 'Legal':
      return 'success';
    case 'Investment':
      return 'accent';
    case 'Finance':
      return 'primary';
    default:
      return 'default';
  }
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('en-IN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

/* ---------- Page ---------- */

export default function GuidesArchivePage() {
  const breadcrumbLdItems = [
    { name: 'Home', url: '/' },
    { name: 'Guides', url: '/guides' },
  ];

  return (
    <>
      <JsonLd data={breadcrumbJsonLd(breadcrumbLdItems)} />

      {/* Hero */}
      <Section variant="alt" className="py-3xl">
        <Container>
          <h1 className="text-h1 text-gray-900">Buyer Guides & Resources</h1>
          <p className="mt-md max-w-[640px] text-body-lg text-gray-600">
            Expert guides to help you make informed real estate decisions.
            From financing to legal compliance, we cover everything a home
            buyer needs to know.
          </p>
        </Container>
      </Section>

      {/* Guide grid */}
      <Section>
        <Container>
          <div className="grid grid-cols-1 gap-xl sm:grid-cols-2">
            {MOCK_GUIDES.map((guide) => (
              <Link
                key={guide.id}
                href={`/guides/${guide.slug}`}
                className="group flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white shadow-card transition-shadow duration-200 no-underline hover:shadow-hover hover:no-underline"
              >
                {/* Thumbnail placeholder */}
                <div className="relative h-[200px] w-full bg-gray-200">
                  {guide.thumbnail ? (
                    <img
                      src={guide.thumbnail}
                      alt={guide.title}
                      className="h-full w-full object-cover"
                      loading="lazy"
                    />
                  ) : (
                    <div className="flex h-full w-full items-center justify-center">
                      <svg
                        width="40"
                        height="40"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="1.5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className="text-gray-400"
                        aria-hidden="true"
                      >
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" />
                      </svg>
                    </div>
                  )}
                </div>

                {/* Body */}
                <div className="flex flex-1 flex-col gap-md p-xl">
                  <div className="flex items-center gap-sm">
                    <Badge variant={getCategoryVariant(guide.category)} size="sm">
                      {guide.category}
                    </Badge>
                    <span className="text-caption text-gray-400">
                      {guide.reading_time} min read
                    </span>
                  </div>

                  <h3 className="text-h4 text-gray-900 group-hover:text-brand-primary">
                    {guide.title}
                  </h3>

                  <p className="line-clamp-2 text-sm text-gray-600">
                    {guide.excerpt}
                  </p>

                  <p className="mt-auto text-caption text-gray-400">
                    {formatDate(guide.published_at)}
                  </p>
                </div>
              </Link>
            ))}
          </div>
        </Container>
      </Section>
    </>
  );
}
