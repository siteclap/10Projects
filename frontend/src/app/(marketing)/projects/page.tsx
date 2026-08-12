import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { ProjectsFilter } from './ProjectsFilter';
import type { ProjectCard as ProjectCardType } from '@/lib/types/project';

const SITE_NAME = 'LeadMAAXX';

export function generateMetadata(): Metadata {
  const title = `New Projects in Navi Mumbai — Browse & Filter — ${SITE_NAME}`;
  const description =
    'Browse all RERA-registered residential projects in Navi Mumbai. Filter by location, budget, configuration, and construction stage. Every project scored across 20 categories by AI.';

  return {
    title,
    description,
    openGraph: {
      title,
      description,
      url: 'https://leadmaaxx.com/projects',
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
    alternates: { canonical: '/projects' },
  };
}

/* ---------- Mock Data ---------- */

const ALL_PROJECTS: ProjectCardType[] = [
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
    id: 3,
    title: 'Balaji Symphony',
    slug: 'balaji-symphony',
    permalink: '/navi-mumbai/panvel/balaji-symphony',
    thumbnail: '',
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
    id: 5,
    title: 'JERAI Elysium',
    slug: 'jerai-elysium',
    permalink: '/navi-mumbai/ulwe/jerai-elysium',
    thumbnail: '',
    developer: 'JERAI Group',
    location: 'Ulwe',
    construction_stage: 'Under Construction',
    expected_possession: 'Sep 2026',
    rera_number: 'P52000047123',
    configurations: [
      { config_type: '2 BHK', carpet_area_sqft: 680, base_price: 7200000, total_price: 7900000, inventory_total: 90, inventory_available: 8 },
    ],
    price_min: 7900000,
    price_max: 7900000,
    fit_score: 82,
  },
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
    id: 8,
    title: 'Haware Citi',
    slug: 'haware-citi',
    permalink: '/navi-mumbai/panvel/haware-citi',
    thumbnail: '',
    developer: 'Haware Group',
    location: 'Panvel',
    construction_stage: 'Ready to Move',
    expected_possession: '',
    rera_number: 'P52000038475',
    configurations: [
      { config_type: '1 BHK', carpet_area_sqft: 380, base_price: 3200000, total_price: 3600000, inventory_total: 200, inventory_available: 12 },
    ],
    price_min: 3600000,
    price_max: 3600000,
    fit_score: 79,
  },
];

/* ---------- Page ---------- */

export default function ProjectsBrowsePage() {
  return (
    <>
      {/* Hero */}
      <Section variant="alt" className="py-3xl">
        <Container>
          <h1 className="text-h1 text-gray-900">
            New Projects in Navi Mumbai
          </h1>
          <p className="mt-md max-w-[640px] text-body-lg text-gray-600">
            Browse all {ALL_PROJECTS.length}+ RERA-registered projects with
            AI-powered scoring across 20 categories. Filter by location, budget,
            and more.
          </p>
        </Container>
      </Section>

      <ProjectsFilter projects={ALL_PROJECTS} />
    </>
  );
}
