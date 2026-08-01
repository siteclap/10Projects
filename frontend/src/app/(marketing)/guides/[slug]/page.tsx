import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { Breadcrumbs } from '@/components/layout/Breadcrumbs';
import { Badge } from '@/components/ui/Badge';
import { JsonLd } from '@/components/seo/JsonLd';
import { breadcrumbJsonLd } from '@/lib/seo/json-ld';
import Link from 'next/link';
import type { Guide } from '@/lib/types/guide';

export const revalidate = 7200;

const SITE_NAME = '10Projects';
const BASE_URL = process.env.NEXT_PUBLIC_SITE_URL || 'https://10projects.com';

/* ---------- Mock guide data ---------- */

const MOCK_GUIDES: Record<string, Guide> = {
  'first-time-buyers-guide-navi-mumbai': {
    id: 1,
    title: "First-Time Buyer's Guide to Navi Mumbai",
    slug: 'first-time-buyers-guide-navi-mumbai',
    excerpt:
      'Everything you need to know before buying your first home in Navi Mumbai.',
    content: `<h2 id="why-navi-mumbai">Why Navi Mumbai?</h2>
<p>Navi Mumbai has emerged as one of the most sought-after residential destinations in the Mumbai Metropolitan Region. With planned infrastructure, competitive pricing, and upcoming developments like the Navi Mumbai International Airport, the city offers excellent value for first-time buyers.</p>
<p>Compared to Mumbai, Navi Mumbai offers 40-60% lower prices per square foot while providing better infrastructure, wider roads, and more green spaces. The CIDCO-planned layout ensures systematic development with adequate social infrastructure.</p>

<h2 id="choosing-location">Choosing the Right Location</h2>
<p>Navi Mumbai comprises 15 major nodes, each with its own character and price point. For first-time buyers, we recommend considering these factors:</p>
<ul>
<li><strong>Budget alignment:</strong> Locations like Taloja and Panvel offer affordable 1 BHK options starting from Rs 30L, while Vashi and Nerul cater to premium buyers.</li>
<li><strong>Workplace proximity:</strong> If you work in BKC or Lower Parel, Airoli and Ghansoli offer the shortest commute via the harbour line.</li>
<li><strong>Future appreciation:</strong> Areas near the upcoming airport (Ulwe, Panvel) show the highest appreciation potential, with prices growing 15-22% year-on-year.</li>
<li><strong>Social infrastructure:</strong> Established nodes like Kharghar and Vashi have the best schools, hospitals, and entertainment options.</li>
</ul>

<h2 id="rera-verification">RERA Verification</h2>
<p>Before investing in any project, always verify its RERA registration. Visit the MahaRERA portal (maharera.mahaonline.gov.in) and search by project name or registration number. A RERA-registered project ensures:</p>
<ul>
<li>Carpet area transparency (no super built-up area tricks)</li>
<li>Builder accountability for delivery timelines</li>
<li>Escrow account protection for 70% of buyer funds</li>
<li>Standardised sale agreement with fair terms</li>
</ul>

<h2 id="financial-planning">Financial Planning</h2>
<p>As a first-time buyer, plan for these costs beyond the base property price:</p>
<ul>
<li><strong>Stamp duty:</strong> 5% of property value (3% for women buyers in Maharashtra)</li>
<li><strong>Registration charges:</strong> 1% of property value (capped at Rs 30,000)</li>
<li><strong>GST:</strong> 5% for under-construction projects (1% for affordable housing under Rs 45L)</li>
<li><strong>Maintenance deposit:</strong> Typically 12-24 months advance</li>
<li><strong>Interior and furnishing:</strong> Budget Rs 5-15L depending on size and finish level</li>
</ul>
<p>Most banks offer home loans up to 80% of property value. With current interest rates at 8.5-9.5%, a Rs 70L property would cost approximately Rs 55,000/month in EMI for a 20-year tenure.</p>

<h2 id="due-diligence">Due Diligence Checklist</h2>
<p>Before finalising any property, complete this checklist:</p>
<ul>
<li>Verify RERA registration and check for any complaints</li>
<li>Visit the construction site and assess actual progress</li>
<li>Check the developer track record on delivery timelines</li>
<li>Review the sale agreement with a property lawyer</li>
<li>Compare prices with 3-4 similar projects in the area</li>
<li>Use 10Projects AI scoring to get an objective analysis across 20 categories</li>
</ul>`,
    thumbnail: null,
    category: 'Buying Guide',
    published_at: '2026-06-15',
    reading_time: 12,
    author: '10Projects Editorial',
  },
  'understanding-rera-home-buyer-rights': {
    id: 2,
    title: 'Understanding RERA: Your Rights as a Home Buyer',
    slug: 'understanding-rera-home-buyer-rights',
    excerpt:
      'A complete breakdown of the RERA Act and how it protects home buyers.',
    content: `<h2 id="what-is-rera">What is RERA?</h2>
<p>The Real Estate (Regulation and Development) Act, 2016 is a landmark legislation designed to protect home buyers and bring transparency to the Indian real estate sector. Implemented in Maharashtra through MahaRERA, it has fundamentally changed how developers operate and how buyers can safeguard their interests.</p>

<h2 id="key-provisions">Key Provisions for Buyers</h2>
<p>RERA provides several critical protections:</p>
<ul>
<li><strong>Carpet area standardisation:</strong> Developers must sell based on carpet area (usable area within walls), eliminating the confusing super built-up area pricing that inflated costs by 30-40%.</li>
<li><strong>Escrow account:</strong> 70% of buyer payments must be deposited in a separate escrow account and can only be used for the specific project construction.</li>
<li><strong>Timely delivery:</strong> Developers must specify possession dates and pay interest to buyers for any delays beyond the committed timeline.</li>
<li><strong>Structural defects:</strong> Developers are liable for structural defects for 5 years after possession, covering repairs at their own cost.</li>
</ul>

<h2 id="how-to-verify">How to Verify RERA Registration</h2>
<p>Every project in Maharashtra must be registered with MahaRERA before marketing or selling any unit. To verify:</p>
<ul>
<li>Visit <strong>maharera.mahaonline.gov.in</strong></li>
<li>Search by project name, developer name, or registration number</li>
<li>Review the project details including approved plans, timeline, and financial disclosures</li>
<li>Check the complaint section for any pending issues</li>
</ul>
<p>On 10Projects, every listed project includes its RERA registration number, and our AI scoring system factors RERA compliance into the overall project score.</p>

<h2 id="filing-complaints">Filing Complaints</h2>
<p>If a developer violates RERA provisions, buyers can file complaints through the MahaRERA portal. Common grounds for complaints include possession delays, changes in approved plans, carpet area discrepancies, and misleading advertising. The tribunal typically resolves complaints within 60 days.</p>`,
    thumbnail: null,
    category: 'Legal',
    published_at: '2026-05-20',
    reading_time: 8,
    author: '10Projects Editorial',
  },
  'navi-mumbai-vs-mumbai-investment-comparison': {
    id: 3,
    title: 'Navi Mumbai vs Mumbai: Which Is Better for Investment?',
    slug: 'navi-mumbai-vs-mumbai-investment-comparison',
    excerpt:
      'A data-driven comparison of real estate investment potential in Navi Mumbai versus Mumbai.',
    content: `<h2 id="price-comparison">Price Comparison</h2>
<p>The pricing gap between Mumbai and Navi Mumbai remains substantial, making Navi Mumbai an attractive proposition for both end-users and investors:</p>
<ul>
<li><strong>South Mumbai:</strong> Rs 40,000 - 1,50,000 per sq ft</li>
<li><strong>Western Suburbs (Andheri-Borivali):</strong> Rs 18,000 - 35,000 per sq ft</li>
<li><strong>Navi Mumbai (Vashi-Nerul):</strong> Rs 12,000 - 16,000 per sq ft</li>
<li><strong>Navi Mumbai (Kharghar-Panvel):</strong> Rs 5,000 - 9,000 per sq ft</li>
</ul>
<p>This means a 2 BHK apartment that costs Rs 2 Cr in Andheri can be purchased for Rs 80L - 1.2 Cr in Kharghar, with better amenities and larger carpet area.</p>

<h2 id="appreciation-potential">Appreciation Potential</h2>
<p>Navi Mumbai has consistently outperformed Mumbai in price appreciation over the last 5 years. Key drivers include:</p>
<ul>
<li><strong>Navi Mumbai International Airport:</strong> Expected to be operational by 2027, this will transform Ulwe, Panvel, and surrounding areas</li>
<li><strong>Mumbai Trans Harbour Link (MTHL):</strong> India longest sea bridge connecting Sewri to Chirle, cutting travel time to South Mumbai from 2 hours to 20 minutes</li>
<li><strong>Navi Mumbai Metro:</strong> The metro network will connect major nodes, improving last-mile connectivity</li>
<li><strong>NAINA Smart City:</strong> A 600 sq km planned development around the airport area</li>
</ul>

<h2 id="rental-yield">Rental Yield Analysis</h2>
<p>While rental yields in Mumbai average 2-3%, Navi Mumbai offers slightly better returns at 3-4% for well-located properties near railway stations and IT parks. Key rental hotspots include Airoli, Ghansoli, and Vashi, driven by proximity to IT hubs and commercial centres.</p>

<h2 id="verdict">Our Verdict</h2>
<p>For investors seeking capital appreciation, Navi Mumbai locations near the upcoming airport (Ulwe, Panvel) offer the highest potential. For end-users seeking a balance of affordability and livability, Kharghar remains the top choice. Use the 10Projects AI assessment to find your perfect match based on your specific investment criteria.</p>`,
    thumbnail: null,
    category: 'Investment',
    published_at: '2026-04-10',
    reading_time: 10,
    author: '10Projects Editorial',
  },
  'home-loan-guide-2026': {
    id: 4,
    title: 'Home Loan Guide: Interest Rates, EMI & Eligibility in 2026',
    slug: 'home-loan-guide-2026',
    excerpt:
      'Navigate the home loan process with confidence. Compare rates, calculate EMI, and understand eligibility.',
    content: `<h2 id="current-rates">Current Interest Rates (2026)</h2>
<p>Home loan interest rates in India have stabilised after the RBI rate hikes. Here are current rates from major banks:</p>
<ul>
<li><strong>SBI:</strong> 8.50% - 9.15% (based on CIBIL score)</li>
<li><strong>HDFC Bank:</strong> 8.75% - 9.40%</li>
<li><strong>ICICI Bank:</strong> 8.60% - 9.25%</li>
<li><strong>Bank of Baroda:</strong> 8.40% - 9.10%</li>
<li><strong>Kotak Mahindra:</strong> 8.70% - 9.30%</li>
</ul>
<p>A CIBIL score above 750 typically qualifies you for the lowest rates. Check your CIBIL score for free at cibil.com before applying.</p>

<h2 id="emi-calculation">EMI Calculation</h2>
<p>Use this simple formula to estimate your monthly EMI: For a Rs 50L loan at 8.75% for 20 years, your EMI would be approximately Rs 44,200. For a Rs 1 Cr loan, double the EMI to Rs 88,400. Banks generally approve loans where EMI does not exceed 40-50% of your net monthly income.</p>

<h2 id="eligibility">Eligibility Criteria</h2>
<p>Banks evaluate home loan applications based on:</p>
<ul>
<li><strong>Income:</strong> Minimum Rs 25,000/month net income for most banks</li>
<li><strong>Age:</strong> 21-65 years (loan tenure cannot extend beyond age 65-70)</li>
<li><strong>CIBIL score:</strong> Minimum 650, but 750+ gets you the best rates</li>
<li><strong>Employment stability:</strong> 2+ years in current job (salaried) or 3+ years of ITR (self-employed)</li>
<li><strong>Existing EMIs:</strong> Total EMI obligations should not exceed 50-55% of income</li>
</ul>

<h2 id="tax-benefits">Tax Benefits</h2>
<p>Home loan borrowers enjoy significant tax benefits:</p>
<ul>
<li><strong>Section 80C:</strong> Up to Rs 1.5L deduction on principal repayment</li>
<li><strong>Section 24(b):</strong> Up to Rs 2L deduction on interest paid (self-occupied property)</li>
<li><strong>Section 80EEA:</strong> Additional Rs 1.5L deduction for first-time buyers (stamp value up to Rs 45L)</li>
</ul>
<p>For a property purchased at Rs 70L with a Rs 55L loan, you could save Rs 75,000 - 1,00,000 annually in taxes. Use the EMI calculator on 10Projects to estimate your complete financial commitment including tax savings.</p>`,
    thumbnail: null,
    category: 'Finance',
    published_at: '2026-03-25',
    reading_time: 15,
    author: '10Projects Editorial',
  },
};

const RELATED_GUIDES = [
  { title: "First-Time Buyer's Guide", slug: 'first-time-buyers-guide-navi-mumbai' },
  { title: 'Understanding RERA', slug: 'understanding-rera-home-buyer-rights' },
  { title: 'NM vs Mumbai Investment', slug: 'navi-mumbai-vs-mumbai-investment-comparison' },
  { title: 'Home Loan Guide 2026', slug: 'home-loan-guide-2026' },
];

/* ---------- Article JSON-LD ---------- */

function articleJsonLd(guide: Guide): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: guide.title,
    description: guide.excerpt,
    url: `${BASE_URL}/guides/${guide.slug}`,
    datePublished: guide.published_at,
    author: {
      '@type': 'Organization',
      name: guide.author,
    },
    publisher: {
      '@type': 'Organization',
      name: '10Projects',
      url: BASE_URL,
      logo: {
        '@type': 'ImageObject',
        url: `${BASE_URL}/logo.png`,
      },
    },
    image: guide.thumbnail || `${BASE_URL}/og-guide.png`,
  };
}

/* ---------- Helper ---------- */

function extractHeadings(html: string): Array<{ id: string; text: string }> {
  const headings: Array<{ id: string; text: string }> = [];
  const regex = /<h2\s+id="([^"]+)"[^>]*>([^<]+)<\/h2>/g;
  let match;
  while ((match = regex.exec(html)) !== null) {
    headings.push({ id: match[1], text: match[2] });
  }
  return headings;
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('en-IN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

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

/* ---------- Static params ---------- */

type PageProps = {
  params: Promise<{ slug: string }>;
};

export async function generateStaticParams(): Promise<Array<{ slug: string }>> {
  return [
    { slug: 'first-time-buyers-guide-navi-mumbai' },
    { slug: 'understanding-rera-home-buyer-rights' },
    { slug: 'navi-mumbai-vs-mumbai-investment-comparison' },
    { slug: 'home-loan-guide-2026' },
  ];
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params;
  const guide = MOCK_GUIDES[slug];
  if (!guide) {
    return { title: 'Guide Not Found' };
  }

  const title = `${guide.title} — ${SITE_NAME}`;
  const url = `${BASE_URL}/guides/${guide.slug}`;

  return {
    title,
    description: guide.excerpt,
    openGraph: {
      title,
      description: guide.excerpt,
      url,
      siteName: SITE_NAME,
      type: 'article',
      locale: 'en_IN',
      publishedTime: guide.published_at,
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description: guide.excerpt,
    },
    robots: { index: true, follow: true },
    alternates: { canonical: url },
  };
}

/* ---------- Page ---------- */

export default async function GuideDetailPage({ params }: PageProps) {
  const { slug } = await params;
  const guide = MOCK_GUIDES[slug];

  if (!guide) {
    return (
      <Container>
        <div className="py-5xl text-center">
          <h1 className="text-h1 text-gray-900">Guide Not Found</h1>
          <p className="mt-md text-base text-gray-500">
            The guide you are looking for does not exist.
          </p>
        </div>
      </Container>
    );
  }

  const headings = extractHeadings(guide.content);
  const otherGuides = RELATED_GUIDES.filter((g) => g.slug !== slug);

  const breadcrumbItems = [
    { label: 'Guides', href: '/guides' },
    { label: guide.title },
  ];

  const breadcrumbLdItems = [
    { name: 'Home', url: '/' },
    { name: 'Guides', url: '/guides' },
    { name: guide.title, url: `/guides/${guide.slug}` },
  ];

  return (
    <>
      <JsonLd data={articleJsonLd(guide)} />
      <JsonLd data={breadcrumbJsonLd(breadcrumbLdItems)} />

      <Container>
        <Breadcrumbs items={breadcrumbItems} className="mt-lg" />
      </Container>

      <Section>
        <Container>
          <div className="flex gap-3xl">
            {/* Main content */}
            <article className="min-w-0 flex-1">
              {/* Header */}
              <header className="mb-3xl">
                <div className="flex items-center gap-sm">
                  <Badge variant={getCategoryVariant(guide.category)} size="md">
                    {guide.category}
                  </Badge>
                  <span className="text-sm text-gray-400">
                    {guide.reading_time} min read
                  </span>
                </div>

                <h1 className="mt-lg text-h1 text-gray-900">{guide.title}</h1>

                <div className="mt-lg flex items-center gap-md">
                  <div className="flex h-[36px] w-[36px] items-center justify-center rounded-full bg-brand-primary-pale text-caption font-semibold text-brand-primary">
                    10P
                  </div>
                  <div>
                    <p className="text-sm font-medium text-gray-900">
                      {guide.author}
                    </p>
                    <p className="text-caption text-gray-500">
                      Published {formatDate(guide.published_at)}
                    </p>
                  </div>
                </div>
              </header>

              {/* Article body */}
              <div
                className="prose max-w-none text-base leading-relaxed text-gray-700 [&_h2]:mb-lg [&_h2]:mt-3xl [&_h2]:text-h2 [&_h2]:text-gray-900 [&_li]:mb-sm [&_p]:mb-lg [&_strong]:text-gray-900 [&_ul]:mb-lg [&_ul]:list-disc [&_ul]:pl-xl"
                dangerouslySetInnerHTML={{ __html: guide.content }}
              />
            </article>

            {/* Sidebar */}
            <aside className="hidden w-[280px] shrink-0 lg:block">
              {/* Table of contents */}
              {headings.length > 0 && (
                <div className="sticky top-xl">
                  <div className="rounded-md border border-gray-200 bg-white p-xl">
                    <h3 className="text-caption font-semibold uppercase tracking-wider text-gray-500">
                      Table of Contents
                    </h3>
                    <nav className="mt-lg">
                      <ul className="flex flex-col gap-sm">
                        {headings.map((heading) => (
                          <li key={heading.id}>
                            <a
                              href={`#${heading.id}`}
                              className="text-sm text-gray-600 no-underline transition-colors hover:text-brand-primary hover:no-underline"
                            >
                              {heading.text}
                            </a>
                          </li>
                        ))}
                      </ul>
                    </nav>
                  </div>

                  {/* Related guides */}
                  <div className="mt-xl rounded-md border border-gray-200 bg-white p-xl">
                    <h3 className="text-caption font-semibold uppercase tracking-wider text-gray-500">
                      Related Guides
                    </h3>
                    <ul className="mt-lg flex flex-col gap-md">
                      {otherGuides.map((g) => (
                        <li key={g.slug}>
                          <Link
                            href={`/guides/${g.slug}`}
                            className="text-sm text-gray-600 no-underline transition-colors hover:text-brand-primary hover:no-underline"
                          >
                            {g.title}
                          </Link>
                        </li>
                      ))}
                    </ul>
                  </div>
                </div>
              )}
            </aside>
          </div>
        </Container>
      </Section>
    </>
  );
}
