import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { Breadcrumbs } from '@/components/layout/Breadcrumbs';
import { LocationStats } from '@/components/location/LocationStats';
import { ProjectCard } from '@/components/project/ProjectCard';
import { JsonLd } from '@/components/seo/JsonLd';
import { locationMetadata } from '@/lib/seo/metadata';
import { placeJsonLd, breadcrumbJsonLd } from '@/lib/seo/json-ld';
import type { Location } from '@/lib/types/location';
import type { ProjectCard as ProjectCardType } from '@/lib/types/project';

export const revalidate = 3600;

/* ---------- Mock location data ---------- */

const MOCK_LOCATIONS: Record<string, Location> = {
  kharghar: {
    id: 1,
    title: 'Kharghar',
    slug: 'kharghar',
    description:
      'Kharghar is one of the most sought-after residential destinations in Navi Mumbai, known for its well-planned infrastructure, excellent connectivity, and lush green surroundings. The node features Central Park, a golf course, and proximity to the upcoming Navi Mumbai International Airport.',
    thumbnail: null,
    city: 'Navi Mumbai',
    city_slug: 'navi-mumbai',
    avg_price_psf: 8500,
    price_trend_1y: 12.5,
    livability_score: 82,
    connectivity_score: 85,
    infrastructure_score: 80,
    project_count: 42,
    upcoming_infra: [
      'Navi Mumbai International Airport (NMIA) — 15 km, expected 2027',
      'Navi Mumbai Metro Line 1 (Belapur-Pendhar) — Station at Kharghar',
      'Mumbai Trans Harbour Link (MTHL) — Improved Mumbai connectivity',
      'Kharghar-Turbhe elevated corridor',
    ],
    nearest_railway: 'Kharghar Railway Station',
    railway_distance_km: 1.2,
    nearest_metro: 'Kharghar Metro (upcoming)',
    metro_distance_km: 0.8,
    nearest_highway: 'Sion-Panvel Expressway',
    highway_distance_km: 2.5,
    latitude: 19.0469,
    longitude: 73.0713,
  },
  panvel: {
    id: 2,
    title: 'Panvel',
    slug: 'panvel',
    description:
      'Panvel is rapidly emerging as a major residential hub in Navi Mumbai, driven by the upcoming Navi Mumbai International Airport and improved connectivity. With affordable pricing and large-scale township developments, Panvel offers excellent value for first-time buyers and investors.',
    thumbnail: null,
    city: 'Navi Mumbai',
    city_slug: 'navi-mumbai',
    avg_price_psf: 5800,
    price_trend_1y: 18.2,
    livability_score: 75,
    connectivity_score: 72,
    infrastructure_score: 78,
    project_count: 38,
    upcoming_infra: [
      'Navi Mumbai International Airport (NMIA) — 8 km, expected 2027',
      'Virar-Alibaug Multimodal Corridor — Station at Panvel',
      'NAINA Smart City Township — 600 sq km planned development',
      'Panvel-Karjat railway line upgrade',
    ],
    nearest_railway: 'Panvel Railway Station',
    railway_distance_km: 1.5,
    nearest_metro: null,
    metro_distance_km: null,
    nearest_highway: 'Mumbai-Pune Expressway',
    highway_distance_km: 3.0,
    latitude: 18.9894,
    longitude: 73.1175,
  },
  ulwe: {
    id: 3,
    title: 'Ulwe',
    slug: 'ulwe',
    description:
      'Ulwe is the fastest-growing node in Navi Mumbai, positioned closest to the upcoming Navi Mumbai International Airport. With CIDCO-planned infrastructure, creek-facing views, and competitive pricing, Ulwe is a hotspot for both end-users and investors seeking high appreciation potential.',
    thumbnail: null,
    city: 'Navi Mumbai',
    city_slug: 'navi-mumbai',
    avg_price_psf: 6200,
    price_trend_1y: 22.0,
    livability_score: 71,
    connectivity_score: 68,
    infrastructure_score: 74,
    project_count: 35,
    upcoming_infra: [
      'Navi Mumbai International Airport (NMIA) — 5 km, expected 2027',
      'Mumbai Trans Harbour Link (MTHL) — Direct access from Ulwe',
      'Navi Mumbai Metro Line 1 — Planned extension to Ulwe',
      'Ulwe waterfront promenade and recreational zone',
    ],
    nearest_railway: 'Nerul Railway Station',
    railway_distance_km: 8.0,
    nearest_metro: 'Ulwe Metro (proposed)',
    metro_distance_km: null,
    nearest_highway: 'Palm Beach Road',
    highway_distance_km: 4.5,
    latitude: 18.9733,
    longitude: 73.0166,
  },
  vashi: {
    id: 4,
    title: 'Vashi',
    slug: 'vashi',
    description:
      'Vashi is one of the oldest and most established nodes in Navi Mumbai, serving as the commercial and retail hub of the city. With premium infrastructure, Inorbit Mall, APMC Market, and excellent railway connectivity, Vashi commands premium pricing and offers a mature urban lifestyle.',
    thumbnail: null,
    city: 'Navi Mumbai',
    city_slug: 'navi-mumbai',
    avg_price_psf: 14200,
    price_trend_1y: 8.5,
    livability_score: 88,
    connectivity_score: 92,
    infrastructure_score: 90,
    project_count: 18,
    upcoming_infra: [
      'Navi Mumbai Metro Line 1 — Station at Vashi',
      'Vashi Creek Bridge widening project',
      'Smart City infrastructure upgrades',
    ],
    nearest_railway: 'Vashi Railway Station',
    railway_distance_km: 0.8,
    nearest_metro: 'Vashi Metro (upcoming)',
    metro_distance_km: 0.5,
    nearest_highway: 'Sion-Panvel Expressway',
    highway_distance_km: 1.0,
    latitude: 19.0771,
    longitude: 73.0003,
  },
};

/* ---------- Mock projects per location ---------- */

const MOCK_PROJECTS: Record<string, ProjectCardType[]> = {
  kharghar: [
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
      id: 10,
      title: 'Adhiraj Capital City',
      slug: 'adhiraj-capital-city',
      permalink: '/navi-mumbai/kharghar/adhiraj-capital-city',
      thumbnail: '',
      developer: 'Adhiraj Constructions',
      location: 'Kharghar',
      construction_stage: 'Under Construction',
      expected_possession: 'Sep 2027',
      rera_number: 'P52000043218',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 680, base_price: 7800000, total_price: 8600000, inventory_total: 160, inventory_available: 78 },
        { config_type: '3 BHK', carpet_area_sqft: 1020, base_price: 11800000, total_price: 13000000, inventory_total: 90, inventory_available: 40 },
      ],
      price_min: 8600000,
      price_max: 13000000,
      fit_score: 86,
    },
    {
      id: 11,
      title: 'Dosti Eastern Bay',
      slug: 'dosti-eastern-bay',
      permalink: '/navi-mumbai/kharghar/dosti-eastern-bay',
      thumbnail: '',
      developer: 'Dosti Realty',
      location: 'Kharghar',
      construction_stage: 'Under Construction',
      expected_possession: 'Mar 2028',
      rera_number: 'P52000055671',
      configurations: [
        { config_type: '1 BHK', carpet_area_sqft: 450, base_price: 5200000, total_price: 5800000, inventory_total: 100, inventory_available: 62 },
        { config_type: '2 BHK', carpet_area_sqft: 700, base_price: 8200000, total_price: 9000000, inventory_total: 140, inventory_available: 85 },
      ],
      price_min: 5800000,
      price_max: 9000000,
      fit_score: 83,
    },
  ],
  panvel: [
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
      id: 12,
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
  ],
  ulwe: [
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
      id: 14,
      title: 'Bhoomi Lawns',
      slug: 'bhoomi-lawns',
      permalink: '/navi-mumbai/ulwe/bhoomi-lawns',
      thumbnail: '',
      developer: 'Bhoomi Group',
      location: 'Ulwe',
      construction_stage: 'Under Construction',
      expected_possession: 'Dec 2026',
      rera_number: 'P52000049987',
      configurations: [
        { config_type: '1 BHK', carpet_area_sqft: 410, base_price: 3900000, total_price: 4400000, inventory_total: 130, inventory_available: 72 },
        { config_type: '2 BHK', carpet_area_sqft: 620, base_price: 5800000, total_price: 6500000, inventory_total: 90, inventory_available: 45 },
      ],
      price_min: 4400000,
      price_max: 6500000,
      fit_score: 80,
    },
    {
      id: 15,
      title: 'Sunteck City Avenue 2',
      slug: 'sunteck-city-avenue-2',
      permalink: '/navi-mumbai/ulwe/sunteck-city-avenue-2',
      thumbnail: '',
      developer: 'Sunteck Realty',
      location: 'Ulwe',
      construction_stage: 'Under Construction',
      expected_possession: 'Jun 2027',
      rera_number: 'P52000052341',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 650, base_price: 6500000, total_price: 7200000, inventory_total: 110, inventory_available: 50 },
      ],
      price_min: 7200000,
      price_max: 7200000,
      fit_score: 81,
    },
    {
      id: 16,
      title: 'Akshar Elementa',
      slug: 'akshar-elementa',
      permalink: '/navi-mumbai/ulwe/akshar-elementa',
      thumbnail: '',
      developer: 'Akshar Developers',
      location: 'Ulwe',
      construction_stage: 'Ready to Move',
      expected_possession: '',
      rera_number: 'P52000041562',
      configurations: [
        { config_type: '1 BHK', carpet_area_sqft: 400, base_price: 3600000, total_price: 4100000, inventory_total: 100, inventory_available: 5 },
      ],
      price_min: 4100000,
      price_max: 4100000,
      fit_score: 77,
    },
  ],
  vashi: [
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
      id: 17,
      title: 'Ekta Tripolis',
      slug: 'ekta-tripolis',
      permalink: '/navi-mumbai/vashi/ekta-tripolis',
      thumbnail: '',
      developer: 'Ekta World',
      location: 'Vashi',
      construction_stage: 'Under Construction',
      expected_possession: 'Mar 2027',
      rera_number: 'P52000054892',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 750, base_price: 13200000, total_price: 14500000, inventory_total: 80, inventory_available: 32 },
        { config_type: '3 BHK', carpet_area_sqft: 1050, base_price: 18500000, total_price: 20200000, inventory_total: 50, inventory_available: 18 },
      ],
      price_min: 14500000,
      price_max: 20200000,
      fit_score: 87,
    },
    {
      id: 18,
      title: 'Ariisto Sommet',
      slug: 'ariisto-sommet',
      permalink: '/navi-mumbai/vashi/ariisto-sommet',
      thumbnail: '',
      developer: 'Ariisto Realtors',
      location: 'Vashi',
      construction_stage: 'Under Construction',
      expected_possession: 'Sep 2027',
      rera_number: 'P52000056234',
      configurations: [
        { config_type: '3 BHK', carpet_area_sqft: 1200, base_price: 22000000, total_price: 24500000, inventory_total: 30, inventory_available: 14 },
        { config_type: '4 BHK', carpet_area_sqft: 1600, base_price: 30000000, total_price: 33000000, inventory_total: 15, inventory_available: 8 },
      ],
      price_min: 24500000,
      price_max: 33000000,
      fit_score: 92,
    },
    {
      id: 19,
      title: 'Sai Mannat',
      slug: 'sai-mannat',
      permalink: '/navi-mumbai/vashi/sai-mannat',
      thumbnail: '',
      developer: 'Sai Developers',
      location: 'Vashi',
      construction_stage: 'Ready to Move',
      expected_possession: '',
      rera_number: 'P52000035781',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 700, base_price: 11500000, total_price: 12800000, inventory_total: 70, inventory_available: 6 },
      ],
      price_min: 12800000,
      price_max: 12800000,
      fit_score: 84,
    },
  ],
};

/* ---------- Static params ---------- */

type PageProps = {
  params: Promise<{ location: string }>;
};

export async function generateStaticParams(): Promise<Array<{ location: string }>> {
  return [
    { location: 'kharghar' },
    { location: 'panvel' },
    { location: 'ulwe' },
    { location: 'vashi' },
  ];
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { location: locationSlug } = await params;
  const location = MOCK_LOCATIONS[locationSlug];
  if (!location) {
    return { title: 'Location Not Found' };
  }
  return locationMetadata(location);
}

/* ---------- Page ---------- */

export default async function LocationDetailPage({ params }: PageProps) {
  const { location: locationSlug } = await params;
  const location = MOCK_LOCATIONS[locationSlug];
  const projects = MOCK_PROJECTS[locationSlug] ?? [];

  if (!location) {
    return (
      <Container>
        <div className="py-5xl text-center">
          <h1 className="text-h1 text-gray-900">Location Not Found</h1>
          <p className="mt-md text-base text-gray-500">
            The location you are looking for does not exist.
          </p>
        </div>
      </Container>
    );
  }

  const breadcrumbItems = [
    { label: 'Navi Mumbai', href: '/navi-mumbai' },
    { label: location.title },
  ];

  const breadcrumbLdItems = [
    { name: 'Home', url: '/' },
    { name: 'Navi Mumbai', url: '/navi-mumbai' },
    { name: location.title, url: `/navi-mumbai/${location.slug}` },
  ];

  return (
    <>
      <JsonLd data={placeJsonLd(location)} />
      <JsonLd data={breadcrumbJsonLd(breadcrumbLdItems)} />

      <Container>
        <Breadcrumbs items={breadcrumbItems} className="mt-lg" />
      </Container>

      {/* Hero */}
      <Section variant="alt" className="py-xl">
        <Container>
          <h1 className="text-h1 text-gray-900">
            Projects in {location.title}, Navi Mumbai
          </h1>
          <p className="mt-md max-w-[640px] text-base text-gray-600">
            {location.description}
          </p>

          {/* Stats bar */}
          <div className="mt-xl flex flex-wrap gap-xl">
            <div>
              <p className="text-h3 font-bold text-brand-primary">
                {location.project_count}
              </p>
              <p className="text-sm text-gray-500">Projects</p>
            </div>
            <div className="h-auto w-px bg-gray-200" />
            <div>
              <p className="text-h3 font-bold text-brand-primary">
                {'\u20B9'}{location.avg_price_psf.toLocaleString('en-IN')}/sqft
              </p>
              <p className="text-sm text-gray-500">Avg Price</p>
            </div>
            <div className="h-auto w-px bg-gray-200" />
            <div>
              <p className="text-h3 font-bold text-success">
                {location.livability_score}/100
              </p>
              <p className="text-sm text-gray-500">Livability</p>
            </div>
            <div className="h-auto w-px bg-gray-200" />
            <div>
              <p className="text-h3 font-bold text-brand-primary">
                +{location.price_trend_1y}%
              </p>
              <p className="text-sm text-gray-500">Price Growth (1Y)</p>
            </div>
          </div>
        </Container>
      </Section>

      {/* Location Stats */}
      <Section>
        <Container>
          <LocationStats location={location} />
        </Container>
      </Section>

      {/* Projects grid */}
      <Section variant="alt">
        <Container>
          <div className="mb-xl">
            <h2 className="text-h2 text-gray-900">
              Projects in {location.title}
            </h2>
            <p className="mt-xs text-base text-gray-500">
              {projects.length} projects available with AI-powered scoring
            </p>
          </div>

          <div className="grid grid-cols-1 gap-xl sm:grid-cols-2 lg:grid-cols-4">
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
