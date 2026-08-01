import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';
import { Breadcrumbs } from '@/components/layout/Breadcrumbs';
import { Badge } from '@/components/ui/Badge';
import { FitScoreBadge } from '@/components/project/FitScoreBadge';
import { ProjectGallery } from '@/components/project/ProjectGallery';
import { ProjectTabs } from '@/components/project/ProjectTabs';
import { ProjectSidebar } from '@/components/project/ProjectSidebar';
import { FitScoreBreakdown } from '@/components/project/FitScoreBreakdown';
import { ProsConsList } from '@/components/project/ProsConsList';
import { AmenityGrid } from '@/components/project/AmenityGrid';
import { ProjectActionsWrapper } from './ProjectActionsWrapper';
import { MobileStickyBar } from './MobileStickyBar';
import { EmiCalculator } from '@/components/widgets/EmiCalculator';
import { JsonLd } from '@/components/seo/JsonLd';
import { projectMetadata } from '@/lib/seo/metadata';
import {
  realEstateListingJsonLd,
  breadcrumbJsonLd,
  faqJsonLd,
} from '@/lib/seo/json-ld';
import { formatPrice, formatPriceRange } from '@/lib/utils/format-price';
import type { Project, ProjectScore } from '@/lib/types/project';

// --- ISR: revalidate every 30 minutes ---
export const revalidate = 1800;

// --- Mock data ---

const MOCK_IMAGES = [
  'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&h=450&fit=crop',
  'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&h=450&fit=crop',
  'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=450&fit=crop',
  'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&h=450&fit=crop',
  'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=450&fit=crop',
  'https://images.unsplash.com/photo-1600566753376-12c8ab7c5a38?w=800&h=450&fit=crop',
];

const MOCK_SCORES: ProjectScore = {
  value_for_money: 82,
  location_connectivity: 88,
  construction_quality: 75,
  developer_reputation: 90,
  rera_compliance: 95,
  possession_timeline: 70,
  amenities_lifestyle: 85,
  floor_plan_design: 78,
  appreciation_potential: 72,
  rental_yield: 65,
  neighbourhood_safety: 80,
  water_supply: 88,
  power_backup: 92,
  natural_light_ventilation: 76,
  parking_ratio: 68,
  green_building: 55,
  school_proximity: 84,
  hospital_proximity: 79,
  shopping_proximity: 86,
  public_transport: 91,
};

const MOCK_PROJECT: Project = {
  id: 1,
  title: 'Lodha Palava City',
  slug: 'lodha-palava-city',
  permalink: '/navi-mumbai/kharghar/lodha-palava-city',
  thumbnail: MOCK_IMAGES[0],
  developer: 'Lodha Group',
  location: 'Kharghar',
  construction_stage: 'Under Construction',
  expected_possession: 'Dec 2026',
  rera_number: 'P52100025432',
  configurations: [
    {
      config_type: '1 BHK',
      carpet_area_sqft: 450,
      base_price: 4500000,
      total_price: 5200000,
      inventory_total: 120,
      inventory_available: 35,
    },
    {
      config_type: '2 BHK',
      carpet_area_sqft: 720,
      base_price: 7500000,
      total_price: 8500000,
      inventory_total: 200,
      inventory_available: 68,
    },
    {
      config_type: '3 BHK',
      carpet_area_sqft: 1050,
      base_price: 11000000,
      total_price: 12500000,
      inventory_total: 80,
      inventory_available: 22,
    },
  ],
  price_min: 5200000,
  price_max: 12500000,
  railway_distance_km: 2.5,
  latitude: 19.0469,
  longitude: 73.0713,
  fit_score: 84,
  scores: MOCK_SCORES,
  strengths: [
    { category: 'rera_compliance', label: 'RERA Compliance', score: 95 },
    { category: 'public_transport', label: 'Public Transport', score: 91 },
    { category: 'developer_reputation', label: 'Developer Reputation', score: 90 },
  ],
  tradeoffs: [
    { category: 'green_building', label: 'Green Building', score: 55 },
    { category: 'rental_yield', label: 'Rental Yield', score: 65 },
    { category: 'parking_ratio', label: 'Parking Ratio', score: 68 },
  ],
};

const MOCK_PROS = [
  'Excellent connectivity to Kharghar railway station (2.5 km) and upcoming metro line',
  'Reputed developer with strong track record of timely deliveries',
  'RERA registered with full compliance — verified documentation',
  'Well-designed floor plans with good natural light and cross-ventilation',
  'Premium amenities including Olympic-size swimming pool and landscaped gardens',
  'Strong appreciation potential due to upcoming Navi Mumbai International Airport',
];

const MOCK_CONS = [
  'Green building certification not yet obtained',
  'Parking ratio is below average for projects in this price segment',
  'Rental yield currently lower compared to established locations like Vashi or Nerul',
  'Possession timeline has been revised once; monitor for further delays',
];

const MOCK_AMENITIES = [
  'Swimming Pool',
  'Gym',
  'Parking',
  'Garden',
  'Clubhouse',
  'Playground',
  'Security',
  'Power Backup',
  'Lift',
  'Jogging Track',
  'Indoor Games',
  'Landscaped Garden',
];

const MOCK_FAQS = [
  {
    question: 'What is the price range of Lodha Palava City?',
    answer: `Lodha Palava City offers configurations ranging from ${formatPriceRange(MOCK_PROJECT.price_min, MOCK_PROJECT.price_max)}. The 1 BHK starts at ${formatPrice(5200000)}, 2 BHK at ${formatPrice(8500000)}, and 3 BHK at ${formatPrice(12500000)}.`,
  },
  {
    question: 'Is Lodha Palava City RERA registered?',
    answer: `Yes, Lodha Palava City is RERA registered with registration number ${MOCK_PROJECT.rera_number}. The developer maintains a 95% RERA compliance rate across all projects.`,
  },
  {
    question: 'What is the possession date of Lodha Palava City?',
    answer: `The expected possession date for Lodha Palava City is ${MOCK_PROJECT.expected_possession}. The project is currently ${MOCK_PROJECT.construction_stage.toLowerCase()}.`,
  },
  {
    question: 'What amenities are available at Lodha Palava City?',
    answer: `Lodha Palava City offers premium amenities including swimming pool, gymnasium, clubhouse, landscaped gardens, jogging track, indoor games, children's playground, 24/7 security, power backup, and high-speed lifts.`,
  },
  {
    question: 'How is the connectivity of Lodha Palava City?',
    answer: `Lodha Palava City is located ${MOCK_PROJECT.railway_distance_km} km from Kharghar railway station. It has excellent connectivity via the Sion-Panvel Expressway and the upcoming Navi Mumbai Metro. The Navi Mumbai International Airport is also being developed nearby.`,
  },
];

const PROJECT_TABS = [
  { id: 'overview', label: 'Overview' },
  { id: 'price', label: 'Price' },
  { id: 'pros-cons', label: 'Pros & Cons' },
  { id: 'amenities', label: 'Amenities' },
  { id: 'location', label: 'Location' },
  { id: 'developer', label: 'Developer' },
  { id: 'faq', label: 'FAQ' },
];

// --- Page component ---

type PageProps = {
  params: Promise<{ location: string; project: string }>;
};

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { location, project } = await params;
  return projectMetadata(MOCK_PROJECT);
}

export async function generateStaticParams(): Promise<
  Array<{ location: string; project: string }>
> {
  return [];
}

export default async function ProjectDetailPage({ params }: PageProps) {
  const { location, project: projectSlug } = await params;
  const project = MOCK_PROJECT;
  const fullUrl = `${process.env.NEXT_PUBLIC_SITE_URL || 'https://10projects.com'}${project.permalink}`;

  const breadcrumbItems = [
    { label: 'Navi Mumbai', href: '/navi-mumbai' },
    { label: project.location, href: `/navi-mumbai/${location}` },
    { label: project.title },
  ];

  const breadcrumbLdItems = [
    { name: 'Home', url: '/' },
    { name: 'Navi Mumbai', url: '/navi-mumbai' },
    { name: project.location, url: `/navi-mumbai/${location}` },
    { name: project.title, url: project.permalink },
  ];

  return (
    <>
      {/* Structured data */}
      <JsonLd data={realEstateListingJsonLd(project)} />
      <JsonLd data={breadcrumbJsonLd(breadcrumbLdItems)} />
      <JsonLd data={faqJsonLd(MOCK_FAQS)} />

      {/* Tab navigation */}
      <ProjectTabs tabs={PROJECT_TABS} />

      <Container>
        {/* Breadcrumbs */}
        <Breadcrumbs items={breadcrumbItems} className="mt-lg" />

        {/* Main layout: content + sidebar */}
        <div className="flex gap-3xl pb-4xl">
          {/* Main content column */}
          <div className="min-w-0 flex-1">
            {/* Gallery */}
            <ProjectGallery images={MOCK_IMAGES} />

            {/* Title and badges */}
            <div className="mt-xl">
              <div className="flex items-start gap-lg">
                <div className="flex-1">
                  <h1 className="text-h1 text-gray-900">{project.title}</h1>
                  <p className="mt-xs text-base text-gray-500">
                    by {project.developer} in {project.location}
                  </p>
                </div>
                {project.fit_score != null && project.fit_score > 0 && (
                  <FitScoreBadge score={project.fit_score} size="lg" />
                )}
              </div>

              {/* Status badges */}
              <div className="mt-md flex flex-wrap items-center gap-sm">
                <Badge variant="accent" size="md">
                  {project.construction_stage}
                </Badge>
                {project.rera_number && (
                  <Badge variant="success" size="md">
                    RERA: {project.rera_number}
                  </Badge>
                )}
                {project.expected_possession && (
                  <Badge variant="primary" size="md">
                    Possession: {project.expected_possession}
                  </Badge>
                )}
              </div>

              {/* Action buttons */}
              <div className="mt-lg">
                <ProjectActionsWrapper
                  projectId={project.id}
                  projectTitle={project.title}
                  projectUrl={fullUrl}
                />
              </div>
            </div>

            {/* Section: Overview */}
            <section id="overview" className="mt-3xl">
              <h2 className="text-h2 text-gray-900">Overview</h2>
              <p className="mt-lg text-base leading-relaxed text-gray-600">
                {project.title} is a premium residential project by {project.developer}{' '}
                located in the heart of {project.location}, Navi Mumbai. Spread across
                well-planned towers, the project offers 1 BHK, 2 BHK, and 3 BHK
                configurations designed for modern living. With a strong focus on
                connectivity, lifestyle amenities, and quality construction, this project
                has earned a Fit Score of {project.fit_score} on 10Projects.
              </p>

              {/* Configuration summary */}
              <div className="mt-xl grid grid-cols-2 gap-md sm:grid-cols-4">
                <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
                  <p className="text-caption uppercase tracking-wider text-gray-500">Configurations</p>
                  <p className="mt-xs text-h4 text-gray-900">
                    {project.configurations.map((c) => c.config_type).join(', ')}
                  </p>
                </div>
                <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
                  <p className="text-caption uppercase tracking-wider text-gray-500">Price Range</p>
                  <p className="mt-xs text-h4 text-gray-900">
                    {formatPriceRange(project.price_min, project.price_max)}
                  </p>
                </div>
                <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
                  <p className="text-caption uppercase tracking-wider text-gray-500">Possession</p>
                  <p className="mt-xs text-h4 text-gray-900">{project.expected_possession}</p>
                </div>
                <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg text-center">
                  <p className="text-caption uppercase tracking-wider text-gray-500">Status</p>
                  <p className="mt-xs text-h4 text-gray-900">{project.construction_stage}</p>
                </div>
              </div>

              {/* Fit Score Breakdown */}
              {project.scores && (
                <div className="mt-3xl">
                  <h3 className="text-h3 text-gray-900">Fit Score Breakdown</h3>
                  <p className="mt-sm text-sm text-gray-500">
                    AI-powered analysis across 20 scoring categories
                  </p>
                  <div className="mt-xl">
                    <FitScoreBreakdown scores={project.scores as unknown as Record<string, number>} />
                  </div>
                </div>
              )}
            </section>

            {/* Section: Price */}
            <section id="price" className="mt-3xl">
              <h2 className="text-h2 text-gray-900">Price & Configuration</h2>

              {/* Price table */}
              <div className="mt-xl overflow-x-auto">
                <table className="w-full text-left text-sm">
                  <thead>
                    <tr className="border-b border-gray-200">
                      <th className="pb-md pr-xl font-semibold text-gray-700">Type</th>
                      <th className="pb-md pr-xl font-semibold text-gray-700">Carpet Area</th>
                      <th className="pb-md pr-xl font-semibold text-gray-700">Base Price</th>
                      <th className="pb-md pr-xl font-semibold text-gray-700">Total Price</th>
                      <th className="pb-md font-semibold text-gray-700">Availability</th>
                    </tr>
                  </thead>
                  <tbody>
                    {project.configurations.map((config, index) => (
                      <tr
                        key={config.config_type}
                        className="border-b border-gray-100 last:border-0"
                      >
                        <td className="py-md pr-xl font-medium text-gray-900">
                          {config.config_type}
                        </td>
                        <td className="py-md pr-xl text-gray-600">
                          {config.carpet_area_sqft} sq ft
                        </td>
                        <td className="py-md pr-xl text-gray-600 tabular-nums">
                          {formatPrice(config.base_price)}
                        </td>
                        <td className="py-md pr-xl font-semibold text-gray-900 tabular-nums">
                          {formatPrice(config.total_price)}
                        </td>
                        <td className="py-md text-gray-600">
                          {config.inventory_available} / {config.inventory_total} units
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {/* EMI Calculator */}
              <div className="mt-3xl">
                <EmiCalculator defaultPrice={project.price_min} />
              </div>
            </section>

            {/* Section: Pros & Cons */}
            <section id="pros-cons" className="mt-3xl">
              <h2 className="text-h2 text-gray-900">Pros & Cons</h2>
              <p className="mt-sm text-sm text-gray-500">
                Based on our AI-powered 20-category analysis
              </p>
              <div className="mt-xl">
                <ProsConsList pros={MOCK_PROS} cons={MOCK_CONS} />
              </div>
            </section>

            {/* Section: Amenities */}
            <section id="amenities" className="mt-3xl">
              <h2 className="text-h2 text-gray-900">Amenities</h2>
              <p className="mt-sm text-sm text-gray-500">
                {MOCK_AMENITIES.length} amenities available
              </p>
              <div className="mt-xl">
                <AmenityGrid amenities={MOCK_AMENITIES} />
              </div>
            </section>

            {/* Section: Location */}
            <section id="location" className="mt-3xl">
              <h2 className="text-h2 text-gray-900">Location</h2>
              <div className="mt-xl">
                <div className="grid grid-cols-1 gap-lg sm:grid-cols-2">
                  <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg">
                    <p className="text-caption uppercase tracking-wider text-gray-500">
                      Nearest Railway Station
                    </p>
                    <p className="mt-xs text-base font-medium text-gray-900">
                      Kharghar Station
                    </p>
                    <p className="mt-xs text-sm text-gray-500">
                      {project.railway_distance_km} km away
                    </p>
                  </div>
                  <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg">
                    <p className="text-caption uppercase tracking-wider text-gray-500">
                      Location Connectivity Score
                    </p>
                    <p className="mt-xs text-base font-medium text-gray-900">
                      {project.scores?.location_connectivity ?? 'N/A'} / 100
                    </p>
                    <p className="mt-xs text-sm text-gray-500">
                      Above average for Navi Mumbai
                    </p>
                  </div>
                  <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg">
                    <p className="text-caption uppercase tracking-wider text-gray-500">
                      Public Transport Score
                    </p>
                    <p className="mt-xs text-base font-medium text-gray-900">
                      {project.scores?.public_transport ?? 'N/A'} / 100
                    </p>
                    <p className="mt-xs text-sm text-gray-500">
                      Excellent metro and bus connectivity
                    </p>
                  </div>
                  <div className="rounded-sm border border-gray-100 bg-gray-50 p-lg">
                    <p className="text-caption uppercase tracking-wider text-gray-500">
                      Neighbourhood Safety
                    </p>
                    <p className="mt-xs text-base font-medium text-gray-900">
                      {project.scores?.neighbourhood_safety ?? 'N/A'} / 100
                    </p>
                    <p className="mt-xs text-sm text-gray-500">
                      Well-developed residential area
                    </p>
                  </div>
                </div>

                {/* Map placeholder */}
                <div className="mt-xl flex h-[300px] items-center justify-center rounded-md border border-gray-200 bg-gray-100">
                  <div className="text-center">
                    <svg
                      width="40"
                      height="40"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="1.5"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      className="mx-auto text-gray-400"
                      aria-hidden="true"
                    >
                      <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                      <circle cx="12" cy="10" r="3" />
                    </svg>
                    <p className="mt-sm text-sm text-gray-500">
                      {project.location}, Navi Mumbai
                    </p>
                    <p className="mt-xs text-caption text-gray-400">
                      {project.latitude.toFixed(4)}, {project.longitude.toFixed(4)}
                    </p>
                  </div>
                </div>
              </div>
            </section>

            {/* Section: Developer */}
            <section id="developer" className="mt-3xl">
              <h2 className="text-h2 text-gray-900">About the Developer</h2>
              <div className="mt-xl rounded-md border border-gray-200 p-xl">
                <div className="flex items-center gap-lg">
                  <div className="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-md bg-brand-primary-pale text-h4 font-bold text-brand-primary">
                    LG
                  </div>
                  <div>
                    <h3 className="text-h4 text-gray-900">{project.developer}</h3>
                    <p className="mt-xs text-sm text-gray-500">
                      Tier 1 Developer — Established 1995
                    </p>
                  </div>
                </div>
                <p className="mt-lg text-sm leading-relaxed text-gray-600">
                  Lodha Group is one of India&apos;s largest real estate developers with
                  a strong presence in the Mumbai Metropolitan Region. Known for
                  world-class construction quality, timely delivery, and premium
                  amenities, Lodha has delivered over 50 million sq ft of residential and
                  commercial spaces across India and London.
                </p>
                <div className="mt-lg grid grid-cols-2 gap-md sm:grid-cols-4">
                  <div className="text-center">
                    <p className="text-h3 font-bold text-gray-900">150+</p>
                    <p className="mt-xs text-caption text-gray-500">Projects Completed</p>
                  </div>
                  <div className="text-center">
                    <p className="text-h3 font-bold text-gray-900">4.5/5</p>
                    <p className="mt-xs text-caption text-gray-500">Customer Rating</p>
                  </div>
                  <div className="text-center">
                    <p className="text-h3 font-bold text-gray-900">98%</p>
                    <p className="mt-xs text-caption text-gray-500">RERA Compliance</p>
                  </div>
                  <div className="text-center">
                    <p className="text-h3 font-bold text-gray-900">Strong</p>
                    <p className="mt-xs text-caption text-gray-500">Financial Stability</p>
                  </div>
                </div>
              </div>
            </section>

            {/* Section: FAQ */}
            <section id="faq" className="mt-3xl">
              <h2 className="text-h2 text-gray-900">Frequently Asked Questions</h2>
              <div className="mt-xl flex flex-col gap-lg">
                {MOCK_FAQS.map((faq, index) => (
                  <details
                    key={index}
                    className="group rounded-sm border border-gray-200 bg-white"
                  >
                    <summary className="flex cursor-pointer items-center justify-between px-xl py-lg text-base font-medium text-gray-900 [&::-webkit-details-marker]:hidden">
                      {faq.question}
                      <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className="shrink-0 text-gray-400 transition-transform group-open:rotate-180"
                        aria-hidden="true"
                      >
                        <path d="m6 9 6 6 6-6" />
                      </svg>
                    </summary>
                    <div className="border-t border-gray-100 px-xl py-lg">
                      <p className="text-sm leading-relaxed text-gray-600">
                        {faq.answer}
                      </p>
                    </div>
                  </details>
                ))}
              </div>
            </section>
          </div>

          {/* Sidebar */}
          <ProjectSidebar project={project} />
        </div>
      </Container>

      {/* Mobile sticky CTA bar */}
      <MobileStickyBar projectTitle={project.title} projectUrl={fullUrl} />
    </>
  );
}
