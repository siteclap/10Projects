import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { LocationGrid } from '@/components/location/LocationGrid';
import { ProjectCard } from '@/components/project/ProjectCard';
import { JsonLd } from '@/components/seo/JsonLd';
import { breadcrumbJsonLd } from '@/lib/seo/json-ld';
import type { LocationCard } from '@/lib/types/location';
import type { ProjectCard as ProjectCardType } from '@/lib/types/project';

const SITE_NAME = '10Projects';

export function generateMetadata(): Metadata {
  const title = `New Projects in Navi Mumbai — Prices, Scores & Reviews — ${SITE_NAME}`;
  const description =
    'Explore 150+ new residential projects across 15 locations in Navi Mumbai. AI-scored rankings, prices, pros & cons for every project. Find your perfect home with 10Projects.';

  return {
    title,
    description,
    openGraph: {
      title,
      description,
      url: 'https://10projects.com/navi-mumbai',
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
    alternates: { canonical: '/navi-mumbai' },
  };
}

/* ---------- Mock Data ---------- */

const MOCK_LOCATIONS: LocationCard[] = [
  {
    id: 1,
    title: 'Kharghar',
    slug: 'kharghar',
    thumbnail: 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=800&q=80',
    city_slug: 'navi-mumbai',
    project_count: 42,
    avg_price_psf: 8500,
    livability_score: 82,
  },
  {
    id: 2,
    title: 'Panvel',
    slug: 'panvel',
    thumbnail: 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&q=80',
    city_slug: 'navi-mumbai',
    project_count: 38,
    avg_price_psf: 5800,
    livability_score: 75,
  },
  {
    id: 3,
    title: 'Ulwe',
    slug: 'ulwe',
    thumbnail: 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=800&q=80',
    city_slug: 'navi-mumbai',
    project_count: 35,
    avg_price_psf: 6200,
    livability_score: 71,
  },
  {
    id: 4,
    title: 'Vashi',
    slug: 'vashi',
    thumbnail: 'https://images.unsplash.com/photo-1444723121867-7a241cacace9?w=800&q=80',
    city_slug: 'navi-mumbai',
    project_count: 18,
    avg_price_psf: 14200,
    livability_score: 88,
  },
  {
    id: 5,
    title: 'Airoli',
    slug: 'airoli',
    thumbnail: 'https://images.unsplash.com/photo-1496568816309-51d7c20e3b21?w=800&q=80',
    city_slug: 'navi-mumbai',
    project_count: 14,
    avg_price_psf: 12800,
    livability_score: 84,
  },
  {
    id: 6,
    title: 'Ghansoli',
    slug: 'ghansoli',
    thumbnail: 'https://images.unsplash.com/photo-1464938050520-ef2571e0d6f3?w=800&q=80',
    city_slug: 'navi-mumbai',
    project_count: 12,
    avg_price_psf: 11500,
    livability_score: 79,
  },
  {
    id: 7,
    title: 'Nerul',
    slug: 'nerul',
    thumbnail: 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=800&q=80',
    city_slug: 'navi-mumbai',
    project_count: 16,
    avg_price_psf: 13500,
    livability_score: 86,
  },
  {
    id: 8,
    title: 'Taloja',
    slug: 'taloja',
    thumbnail: 'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?w=800&q=80',
    city_slug: 'navi-mumbai',
    project_count: 28,
    avg_price_psf: 4500,
    livability_score: 65,
  },
];

const POPULAR_PROJECTS: ProjectCardType[] = [
  {
    id: 1,
    title: 'Lodha Palava Crown',
    slug: 'lodha-palava-crown',
    permalink: '/navi-mumbai/kharghar/lodha-palava-crown',
    thumbnail: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&q=80',
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
    id: 2,
    title: 'Paradise Sai World Empire',
    slug: 'paradise-sai-world-empire',
    permalink: '/navi-mumbai/kharghar/paradise-sai-world-empire',
    thumbnail: 'https://images.unsplash.com/photo-1460317442991-0ec209397118?w=800&q=80',
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
    id: 3,
    title: 'Balaji Symphony',
    slug: 'balaji-symphony',
    permalink: '/navi-mumbai/panvel/balaji-symphony',
    thumbnail: 'https://images.unsplash.com/photo-1515263487990-61b07816b324?w=800&q=80',
    developer: 'Balaji Group',
    location: 'Panvel',
    construction_stage: 'Under Construction',
    expected_possession: 'Mar 2027',
    rera_number: 'P52000048892',
    configurations: [
      { config_type: '1 BHK', carpet_area_sqft: 420, base_price: 4200000, total_price: 4800000, inventory_total: 150, inventory_available: 88 },
      { config_type: '2 BHK', carpet_area_sqft: 630, base_price: 6800000, total_price: 7500000, inventory_total: 100, inventory_available: 52 },
    ],
    price_min: 4800000,
    price_max: 7500000,
    fit_score: 88,
  },
  {
    id: 4,
    title: 'Arihant Aspire',
    slug: 'arihant-aspire',
    permalink: '/navi-mumbai/panvel/arihant-aspire',
    thumbnail: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&q=80',
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
];

const TOTAL_PROJECT_COUNT = 156;

/* ---------- Page ---------- */

export default function NaviMumbaiCityPage() {
  const breadcrumbLdItems = [
    { name: 'Home', url: '/' },
    { name: 'Navi Mumbai', url: '/navi-mumbai' },
  ];

  return (
    <>
      <JsonLd data={breadcrumbJsonLd(breadcrumbLdItems)} />

      {/* Hero */}
      <Section variant="alt" className="py-3xl">
        <Container>
          <h1 className="text-h1 text-gray-900">
            New Projects in Navi Mumbai
          </h1>
          <p className="mt-md max-w-[640px] text-body-lg text-gray-600">
            Explore {TOTAL_PROJECT_COUNT} AI-scored residential projects across{' '}
            {MOCK_LOCATIONS.length} locations. Every project analysed across 20
            categories to help you find the perfect home.
          </p>

          {/* Key stats bar */}
          <div className="mt-xl flex flex-wrap gap-xl">
            <div>
              <p className="text-h2 text-brand-primary">{TOTAL_PROJECT_COUNT}</p>
              <p className="text-sm text-gray-500">Total Projects</p>
            </div>
            <div className="h-auto w-px bg-gray-200" />
            <div>
              <p className="text-h2 text-brand-primary">{MOCK_LOCATIONS.length}</p>
              <p className="text-sm text-gray-500">Locations</p>
            </div>
            <div className="h-auto w-px bg-gray-200" />
            <div>
              <p className="text-h2 text-brand-primary">
                {'\u20B9'}4,500 - 14,200
              </p>
              <p className="text-sm text-gray-500">Price Range / Sq Ft</p>
            </div>
            <div className="h-auto w-px bg-gray-200" />
            <div>
              <p className="text-h2 text-brand-primary">20</p>
              <p className="text-sm text-gray-500">Scoring Categories</p>
            </div>
          </div>
        </Container>
      </Section>

      {/* Locations grid */}
      <LocationGrid
        locations={MOCK_LOCATIONS}
        title="Explore by Location"
        subtitle="Choose a neighbourhood to see all projects, prices, and AI scores"
      />

      {/* Popular projects */}
      <Section variant="alt">
        <Container>
          <div className="mb-xl">
            <h2 className="text-h2 text-gray-900">Popular Projects</h2>
            <p className="mt-xs text-base text-gray-500">
              Top-rated projects across Navi Mumbai based on our 20-category scoring
            </p>
          </div>

          <div className="grid grid-cols-1 gap-xl sm:grid-cols-2 lg:grid-cols-4">
            {POPULAR_PROJECTS.map((project) => (
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
