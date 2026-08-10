import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { Breadcrumbs } from '@/components/layout/Breadcrumbs';
import { Badge } from '@/components/ui/Badge';
import { DeveloperStats } from '@/components/developer/DeveloperStats';
import { ProjectCard } from '@/components/project/ProjectCard';
import { JsonLd } from '@/components/seo/JsonLd';
import { developerMetadata } from '@/lib/seo/metadata';
import { breadcrumbJsonLd } from '@/lib/seo/json-ld';
import type { Developer } from '@/lib/types/developer';
import type { ProjectCard as ProjectCardType } from '@/lib/types/project';

const BASE_URL = process.env.NEXT_PUBLIC_SITE_URL || 'https://10projects.com';

/* ---------- Mock developer data ---------- */

const MOCK_DEVELOPERS: Record<string, Developer> = {
  'lodha-group': {
    id: 1,
    title: 'Lodha Group',
    slug: 'lodha-group',
    logo: null,
    description:
      'Lodha Group (now Macrotech Developers) is one of India\'s largest real estate developers, headquartered in Mumbai. Founded in 1995, the group has delivered over 50 million sq ft of residential and commercial spaces across India and London. Known for world-class construction quality, timely delivery, and premium amenities, Lodha is a publicly listed company with a strong balance sheet.',
    established_year: 1995,
    total_projects_completed: 120,
    total_projects_ongoing: 35,
    total_area_developed_sqft: 50_000_000,
    avg_delivery_delay_months: 3,
    rera_compliance_rate: 98,
    legal_cases_pending: 2,
    customer_rating: 4.2,
    financial_stability: 'strong',
    tier: 'tier_1',
    headquarters: 'Mumbai',
    website: 'https://www.lodhagroup.com',
  },
  'godrej-properties': {
    id: 2,
    title: 'Godrej Properties',
    slug: 'godrej-properties',
    logo: null,
    description:
      'Godrej Properties is the real estate arm of the 127-year-old Godrej Group, one of India\'s most trusted conglomerates. Listed on BSE and NSE, Godrej Properties has developed over 80 million sq ft across 12 cities. The company is known for sustainable design, innovation, and strong governance.',
    established_year: 1990,
    total_projects_completed: 95,
    total_projects_ongoing: 42,
    total_area_developed_sqft: 80_000_000,
    avg_delivery_delay_months: 2,
    rera_compliance_rate: 99,
    legal_cases_pending: 0,
    customer_rating: 4.3,
    financial_stability: 'strong',
    tier: 'tier_1',
    headquarters: 'Mumbai',
    website: 'https://www.godrejproperties.com',
  },
  'lt-realty': {
    id: 3,
    title: 'L&T Realty',
    slug: 'lt-realty',
    logo: null,
    description:
      'L&T Realty is the real estate development arm of Larsen & Toubro, one of India\'s largest engineering and construction companies. Leveraging L&T\'s 80+ years of construction expertise, L&T Realty delivers projects with unmatched construction quality and engineering precision across residential, commercial, and retail segments.',
    established_year: 2012,
    total_projects_completed: 45,
    total_projects_ongoing: 18,
    total_area_developed_sqft: 35_000_000,
    avg_delivery_delay_months: 1,
    rera_compliance_rate: 100,
    legal_cases_pending: 0,
    customer_rating: 4.4,
    financial_stability: 'strong',
    tier: 'tier_1',
    headquarters: 'Mumbai',
    website: 'https://www.lntrealty.com',
  },
  'paradise-group': {
    id: 4,
    title: 'Paradise Group',
    slug: 'paradise-group',
    logo: null,
    description:
      'Paradise Group is a prominent real estate developer in Navi Mumbai with over two decades of experience. Known for Sai World City and Sai World Empire in Kharghar, the group focuses on creating integrated townships with world-class amenities. Paradise Group has a strong presence in the Kharghar micro-market.',
    established_year: 1999,
    total_projects_completed: 30,
    total_projects_ongoing: 8,
    total_area_developed_sqft: 12_000_000,
    avg_delivery_delay_months: 6,
    rera_compliance_rate: 95,
    legal_cases_pending: 1,
    customer_rating: 4.0,
    financial_stability: 'moderate',
    tier: 'tier_2',
    headquarters: 'Navi Mumbai',
    website: 'https://www.paradisegroup.co.in',
  },
  'arihant-superstructures': {
    id: 5,
    title: 'Arihant Superstructures',
    slug: 'arihant-superstructures',
    logo: null,
    description:
      'Arihant Superstructures is a listed real estate developer focused on affordable and mid-segment housing in the Mumbai Metropolitan Region. With over 55 completed projects and presence across Navi Mumbai, Panvel, and Jodhpur, Arihant is known for value-for-money offerings and community-focused developments.',
    established_year: 1993,
    total_projects_completed: 55,
    total_projects_ongoing: 12,
    total_area_developed_sqft: 18_000_000,
    avg_delivery_delay_months: 8,
    rera_compliance_rate: 92,
    legal_cases_pending: 3,
    customer_rating: 3.9,
    financial_stability: 'moderate',
    tier: 'tier_2',
    headquarters: 'Navi Mumbai',
    website: 'https://www.arihantgroup.in',
  },
};

/* ---------- Mock projects per developer ---------- */

const MOCK_DEVELOPER_PROJECTS: Record<string, ProjectCardType[]> = {
  'lodha-group': [
    {
      id: 1,
      title: 'Lodha Palava Crown',
      slug: 'lodha-palava-crown',
      permalink: '/navi-mumbai/kharghar/lodha-palava-crown',
      thumbnail: '',
      developer: 'Lodha Group',
      location: 'Kharghar',
      construction_stage: 'Under Construction',
      expected_possession: 'Dec 2026',
      rera_number: 'P52000046631',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 650, base_price: 8500000, total_price: 9200000, inventory_total: 120, inventory_available: 45 },
        { config_type: '3 BHK', carpet_area_sqft: 950, base_price: 12500000, total_price: 13800000, inventory_total: 80, inventory_available: 22 },
      ],
      price_min: 9200000,
      price_max: 13800000,
      fit_score: 94,
    },
    {
      id: 20,
      title: 'Lodha Bel Air',
      slug: 'lodha-bel-air',
      permalink: '/navi-mumbai/airoli/lodha-bel-air',
      thumbnail: '',
      developer: 'Lodha Group',
      location: 'Airoli',
      construction_stage: 'Under Construction',
      expected_possession: 'Jun 2028',
      rera_number: 'P52000058934',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 720, base_price: 11500000, total_price: 12800000, inventory_total: 100, inventory_available: 60 },
        { config_type: '3 BHK', carpet_area_sqft: 1080, base_price: 16500000, total_price: 18200000, inventory_total: 60, inventory_available: 38 },
      ],
      price_min: 12800000,
      price_max: 18200000,
      fit_score: 89,
    },
    {
      id: 21,
      title: 'Lodha Palava City',
      slug: 'lodha-palava-city',
      permalink: '/navi-mumbai/kharghar/lodha-palava-city',
      thumbnail: '',
      developer: 'Lodha Group',
      location: 'Kharghar',
      construction_stage: 'Under Construction',
      expected_possession: 'Dec 2026',
      rera_number: 'P52100025432',
      configurations: [
        { config_type: '1 BHK', carpet_area_sqft: 450, base_price: 4500000, total_price: 5200000, inventory_total: 120, inventory_available: 35 },
        { config_type: '2 BHK', carpet_area_sqft: 720, base_price: 7500000, total_price: 8500000, inventory_total: 200, inventory_available: 68 },
      ],
      price_min: 5200000,
      price_max: 8500000,
      fit_score: 84,
    },
  ],
  'godrej-properties': [
    {
      id: 7,
      title: 'Godrej Vihaa',
      slug: 'godrej-vihaa',
      permalink: '/navi-mumbai/airoli/godrej-vihaa',
      thumbnail: '',
      developer: 'Godrej Properties',
      location: 'Airoli',
      construction_stage: 'Under Construction',
      expected_possession: 'Dec 2027',
      rera_number: 'P52000053447',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 700, base_price: 11200000, total_price: 12500000, inventory_total: 110, inventory_available: 12 },
      ],
      price_min: 12500000,
      price_max: 12500000,
      fit_score: 87,
    },
    {
      id: 13,
      title: 'Godrej Nirvaan',
      slug: 'godrej-nirvaan',
      permalink: '/navi-mumbai/panvel/godrej-nirvaan',
      thumbnail: '',
      developer: 'Godrej Properties',
      location: 'Panvel',
      construction_stage: 'Under Construction',
      expected_possession: 'Dec 2027',
      rera_number: 'P52000057893',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 620, base_price: 6500000, total_price: 7200000, inventory_total: 110, inventory_available: 55 },
        { config_type: '3 BHK', carpet_area_sqft: 900, base_price: 9500000, total_price: 10500000, inventory_total: 70, inventory_available: 35 },
      ],
      price_min: 7200000,
      price_max: 10500000,
      fit_score: 89,
    },
    {
      id: 22,
      title: 'Godrej Exquisite',
      slug: 'godrej-exquisite',
      permalink: '/navi-mumbai/ghansoli/godrej-exquisite',
      thumbnail: '',
      developer: 'Godrej Properties',
      location: 'Ghansoli',
      construction_stage: 'Ready to Move',
      expected_possession: '',
      rera_number: 'P52000039128',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 750, base_price: 12000000, total_price: 13200000, inventory_total: 80, inventory_available: 4 },
      ],
      price_min: 13200000,
      price_max: 13200000,
      fit_score: 91,
    },
  ],
  'lt-realty': [
    {
      id: 6,
      title: 'L&T Seawoods Residences',
      slug: 'lt-seawoods-residences',
      permalink: '/navi-mumbai/vashi/lt-seawoods-residences',
      thumbnail: '',
      developer: 'L&T Realty',
      location: 'Vashi',
      construction_stage: 'Ready to Move',
      expected_possession: '',
      rera_number: 'P52000032876',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 780, base_price: 14500000, total_price: 15800000, inventory_total: 60, inventory_available: 5 },
        { config_type: '3 BHK', carpet_area_sqft: 1100, base_price: 21000000, total_price: 23500000, inventory_total: 40, inventory_available: 3 },
      ],
      price_min: 15800000,
      price_max: 23500000,
      fit_score: 90,
    },
    {
      id: 23,
      title: 'L&T Emerald Isle',
      slug: 'lt-emerald-isle',
      permalink: '/navi-mumbai/nerul/lt-emerald-isle',
      thumbnail: '',
      developer: 'L&T Realty',
      location: 'Nerul',
      construction_stage: 'Under Construction',
      expected_possession: 'Sep 2027',
      rera_number: 'P52000055123',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 800, base_price: 15000000, total_price: 16500000, inventory_total: 70, inventory_available: 28 },
        { config_type: '3 BHK', carpet_area_sqft: 1150, base_price: 22000000, total_price: 24000000, inventory_total: 45, inventory_available: 20 },
      ],
      price_min: 16500000,
      price_max: 24000000,
      fit_score: 92,
    },
    {
      id: 24,
      title: 'L&T Crescent Bay',
      slug: 'lt-crescent-bay',
      permalink: '/navi-mumbai/nerul/lt-crescent-bay',
      thumbnail: '',
      developer: 'L&T Realty',
      location: 'Nerul',
      construction_stage: 'Ready to Move',
      expected_possession: '',
      rera_number: 'P52000028457',
      configurations: [
        { config_type: '3 BHK', carpet_area_sqft: 1200, base_price: 24000000, total_price: 26500000, inventory_total: 30, inventory_available: 2 },
      ],
      price_min: 26500000,
      price_max: 26500000,
      fit_score: 93,
    },
  ],
  'paradise-group': [
    {
      id: 2,
      title: 'Paradise Sai World Empire',
      slug: 'paradise-sai-world-empire',
      permalink: '/navi-mumbai/kharghar/paradise-sai-world-empire',
      thumbnail: '',
      developer: 'Paradise Group',
      location: 'Kharghar',
      construction_stage: 'Ready to Move',
      expected_possession: '',
      rera_number: 'P52000029541',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 720, base_price: 9800000, total_price: 10500000, inventory_total: 200, inventory_available: 15 },
      ],
      price_min: 10500000,
      price_max: 10500000,
      fit_score: 91,
    },
    {
      id: 25,
      title: 'Paradise Sai World City',
      slug: 'paradise-sai-world-city',
      permalink: '/navi-mumbai/kharghar/paradise-sai-world-city',
      thumbnail: '',
      developer: 'Paradise Group',
      location: 'Kharghar',
      construction_stage: 'Ready to Move',
      expected_possession: '',
      rera_number: 'P52000022145',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 680, base_price: 8500000, total_price: 9200000, inventory_total: 300, inventory_available: 8 },
        { config_type: '3 BHK', carpet_area_sqft: 1000, base_price: 12500000, total_price: 13800000, inventory_total: 150, inventory_available: 5 },
      ],
      price_min: 9200000,
      price_max: 13800000,
      fit_score: 88,
    },
    {
      id: 26,
      title: 'Paradise Sai Mannat',
      slug: 'paradise-sai-mannat',
      permalink: '/navi-mumbai/kharghar/paradise-sai-mannat',
      thumbnail: '',
      developer: 'Paradise Group',
      location: 'Kharghar',
      construction_stage: 'Under Construction',
      expected_possession: 'Mar 2028',
      rera_number: 'P52000059871',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 700, base_price: 9200000, total_price: 10000000, inventory_total: 120, inventory_available: 65 },
      ],
      price_min: 10000000,
      price_max: 10000000,
      fit_score: 85,
    },
  ],
  'arihant-superstructures': [
    {
      id: 4,
      title: 'Arihant Aspire',
      slug: 'arihant-aspire',
      permalink: '/navi-mumbai/panvel/arihant-aspire',
      thumbnail: '',
      developer: 'Arihant Superstructures',
      location: 'Panvel',
      construction_stage: 'Under Construction',
      expected_possession: 'Jun 2027',
      rera_number: 'P52000051203',
      configurations: [
        { config_type: '1 BHK', carpet_area_sqft: 390, base_price: 3800000, total_price: 4200000, inventory_total: 180, inventory_available: 95 },
        { config_type: '2 BHK', carpet_area_sqft: 580, base_price: 5600000, total_price: 6200000, inventory_total: 120, inventory_available: 67 },
      ],
      price_min: 4200000,
      price_max: 6200000,
      fit_score: 85,
    },
    {
      id: 27,
      title: 'Arihant Aangan',
      slug: 'arihant-aangan',
      permalink: '/navi-mumbai/taloja/arihant-aangan',
      thumbnail: '',
      developer: 'Arihant Superstructures',
      location: 'Taloja',
      construction_stage: 'Under Construction',
      expected_possession: 'Dec 2026',
      rera_number: 'P52000044987',
      configurations: [
        { config_type: '1 BHK', carpet_area_sqft: 350, base_price: 2800000, total_price: 3200000, inventory_total: 250, inventory_available: 120 },
        { config_type: '2 BHK', carpet_area_sqft: 520, base_price: 4200000, total_price: 4800000, inventory_total: 180, inventory_available: 88 },
      ],
      price_min: 3200000,
      price_max: 4800000,
      fit_score: 80,
    },
    {
      id: 28,
      title: 'Arihant Anaika',
      slug: 'arihant-anaika',
      permalink: '/navi-mumbai/taloja/arihant-anaika',
      thumbnail: '',
      developer: 'Arihant Superstructures',
      location: 'Taloja',
      construction_stage: 'Ready to Move',
      expected_possession: '',
      rera_number: 'P52000037654',
      configurations: [
        { config_type: '1 BHK', carpet_area_sqft: 340, base_price: 2500000, total_price: 2900000, inventory_total: 300, inventory_available: 10 },
      ],
      price_min: 2900000,
      price_max: 2900000,
      fit_score: 76,
    },
  ],
};

/* ---------- Organization JSON-LD for developer ---------- */

function developerOrgJsonLd(developer: Developer): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: developer.title,
    url: developer.website || `${BASE_URL}/developers/${developer.slug}`,
    description: developer.description,
    logo: developer.logo || undefined,
    foundingDate: developer.established_year
      ? `${developer.established_year}`
      : undefined,
    address: {
      '@type': 'PostalAddress',
      addressLocality: developer.headquarters,
      addressCountry: 'IN',
    },
    aggregateRating: {
      '@type': 'AggregateRating',
      ratingValue: developer.customer_rating,
      bestRating: 5,
      ratingCount: developer.total_projects_completed,
    },
  };
}

/* ---------- Static params ---------- */

type PageProps = {
  params: Promise<{ slug: string }>;
};

export async function generateStaticParams(): Promise<Array<{ slug: string }>> {
  return [
    { slug: 'lodha-group' },
    { slug: 'godrej-properties' },
    { slug: 'lt-realty' },
    { slug: 'paradise-group' },
    { slug: 'arihant-superstructures' },
  ];
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params;
  const developer = MOCK_DEVELOPERS[slug];
  if (!developer) {
    return { title: 'Developer Not Found' };
  }
  return developerMetadata(developer);
}

/* ---------- Page ---------- */

function getTierLabel(tier: string): string {
  switch (tier) {
    case 'tier_1':
      return 'Tier 1 Developer';
    case 'tier_2':
      return 'Tier 2 Developer';
    case 'tier_3':
      return 'Tier 3 Developer';
    default:
      return '';
  }
}

function getTierVariant(tier: string): 'accent' | 'primary' | 'default' {
  switch (tier) {
    case 'tier_1':
      return 'accent';
    case 'tier_2':
      return 'primary';
    default:
      return 'default';
  }
}

export default async function DeveloperDetailPage({ params }: PageProps) {
  const { slug } = await params;
  const developer = MOCK_DEVELOPERS[slug];
  const projects = MOCK_DEVELOPER_PROJECTS[slug] ?? [];

  if (!developer) {
    return (
      <Container>
        <div className="py-5xl text-center">
          <h1 className="text-h1 text-gray-900">Developer Not Found</h1>
          <p className="mt-md text-base text-gray-500">
            The developer you are looking for does not exist.
          </p>
        </div>
      </Container>
    );
  }

  const tierLabel = getTierLabel(developer.tier);
  const tierVariant = getTierVariant(developer.tier);

  const initials = developer.title
    .split(' ')
    .map((w) => w[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();

  const breadcrumbItems = [
    { label: 'Developers', href: '/developers' },
    { label: developer.title },
  ];

  const breadcrumbLdItems = [
    { name: 'Home', url: '/' },
    { name: 'Developers', url: '/developers' },
    { name: developer.title, url: `/developers/${developer.slug}` },
  ];

  return (
    <>
      <JsonLd data={developerOrgJsonLd(developer)} />
      <JsonLd data={breadcrumbJsonLd(breadcrumbLdItems)} />

      <Container>
        <Breadcrumbs items={breadcrumbItems} className="mt-lg" />
      </Container>

      {/* Developer hero */}
      <Section variant="alt" className="py-xl">
        <Container>
          <div className="flex items-start gap-xl">
            {/* Logo */}
            <div className="flex h-[80px] w-[80px] shrink-0 items-center justify-center rounded-md bg-gray-100">
              {developer.logo ? (
                <img
                  src={developer.logo}
                  alt={developer.title}
                  className="h-[56px] max-w-full object-contain"
                />
              ) : (
                <span className="text-h2 font-bold text-gray-500">{initials}</span>
              )}
            </div>

            <div>
              <h1 className="text-h1 text-gray-900">{developer.title}</h1>
              <div className="mt-md flex flex-wrap items-center gap-sm">
                {tierLabel && (
                  <Badge variant={tierVariant} size="md">
                    {tierLabel}
                  </Badge>
                )}
                {developer.established_year && (
                  <Badge variant="default" size="md">
                    Est. {developer.established_year}
                  </Badge>
                )}
                {developer.headquarters && (
                  <Badge variant="outline" size="md">
                    {developer.headquarters}
                  </Badge>
                )}
              </div>
              <p className="mt-lg max-w-[640px] text-base text-gray-600">
                {developer.description}
              </p>
            </div>
          </div>
        </Container>
      </Section>

      {/* Stats */}
      <Section>
        <Container>
          <h2 className="text-h2 text-gray-900">Developer Profile</h2>
          <div className="mt-xl">
            <DeveloperStats developer={developer} />
          </div>
        </Container>
      </Section>

      {/* Projects */}
      <Section variant="alt">
        <Container>
          <div className="mb-xl">
            <h2 className="text-h2 text-gray-900">
              Projects by {developer.title}
            </h2>
            <p className="mt-xs text-base text-gray-500">
              {projects.length} projects in Navi Mumbai
            </p>
          </div>

          <div className="grid grid-cols-1 gap-xl sm:grid-cols-2 lg:grid-cols-3">
            {projects.map((project) => (
              <ProjectCard
                key={project.id}
                project={project}
                showFitScore
                fitScore={project.fit_score}
                className="w-full min-w-0"
              />
            ))}
          </div>
        </Container>
      </Section>
    </>
  );
}
