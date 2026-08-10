import type { Metadata } from 'next';
import { wpFetchProjects } from '@/lib/wp-api';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { Breadcrumbs } from '@/components/layout/Breadcrumbs';
import { LocationStats } from '@/components/location/LocationStats';
import { ProjectCard } from '@/components/project/ProjectCard';
import { JsonLd } from '@/components/seo/JsonLd';
import { locationMetadata } from '@/lib/seo/metadata';
import { placeJsonLd, breadcrumbJsonLd } from '@/lib/seo/json-ld';
import Link from 'next/link';
import { formatPrice } from '@/lib/utils/format-price';
import type { Location } from '@/lib/types/location';
import type { ProjectCard as ProjectCardType } from '@/lib/types/project';

/* ---------- Mock location data ---------- */

const MOCK_LOCATIONS: Record<string, Location> = {
  kharghar: {
    id: 1,
    title: 'Kharghar',
    slug: 'kharghar',
    description:
      'Kharghar is one of the most sought-after residential destinations in Navi Mumbai, known for its well-planned infrastructure, excellent connectivity, and lush green surroundings. The node features Central Park, a golf course, and proximity to the upcoming Navi Mumbai International Airport.',
    thumbnail: 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=800&q=80',
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
    thumbnail: 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&q=80',
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
    thumbnail: 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=800&q=80',
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
    thumbnail: 'https://images.unsplash.com/photo-1444723121867-7a241cacace9?w=800&q=80',
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
      id: 10,
      title: 'Adhiraj Capital City',
      slug: 'adhiraj-capital-city',
      permalink: '/navi-mumbai/kharghar/adhiraj-capital-city',
      thumbnail: 'https://images.unsplash.com/photo-1574362848149-11496d93a7c7?w=800&q=80',
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
      thumbnail: 'https://images.unsplash.com/photo-1560185127-6ed189bf02f4?w=800&q=80',
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
    {
      id: 12,
      title: 'Haware Citi',
      slug: 'haware-citi',
      permalink: '/navi-mumbai/panvel/haware-citi',
      thumbnail: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80',
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
      thumbnail: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&q=80',
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
      thumbnail: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&q=80',
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
      thumbnail: 'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?w=800&q=80',
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
      thumbnail: 'https://images.unsplash.com/photo-1567496898669-ee935f5f647a?w=800&q=80',
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
      thumbnail: 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&q=80',
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
      thumbnail: 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&q=80',
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
      thumbnail: 'https://images.unsplash.com/photo-1560184897-ae75f418493e?w=800&q=80',
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
      thumbnail: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80',
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
      thumbnail: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&q=80',
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

/* ---------- About locality content ---------- */

const MOCK_ABOUT: Record<string, { highlights: string[]; whyBuy: string }> = {
  kharghar: {
    highlights: [
      'Home to Central Park — one of Asia\'s largest parks spanning 80+ acres',
      'Premium educational institutions including NMIMS, ITM, and DY Patil',
      'Well-connected via Kharghar railway station on the Harbour Line',
      'Upcoming Navi Mumbai Metro Line 1 with a station at Kharghar',
      'Proximity to Navi Mumbai International Airport (15 km)',
      'Active golf course and recreational sports infrastructure',
    ],
    whyBuy:
      'Kharghar offers the perfect blend of nature and urban convenience. With CIDCO-planned infrastructure, wide roads, and a growing social fabric, it consistently ranks among the top residential choices in Navi Mumbai. The upcoming metro and airport are expected to boost appreciation by 15–20% over the next 3 years.',
  },
  panvel: {
    highlights: [
      'Gateway to Navi Mumbai International Airport (just 8 km away)',
      'Part of NAINA Smart City — a 600 sq km planned township',
      'Mumbai-Pune Expressway and NH-4 provide seamless connectivity',
      'Virar-Alibaug Multimodal Corridor planned with a station at Panvel',
      'Among the most affordable locations in the Mumbai Metropolitan Region',
      'Multiple large-scale townships by reputed developers',
    ],
    whyBuy:
      'Panvel is the fastest-appreciating micro-market in MMR, driven by the airport and NAINA development. First-time buyers get larger apartments at 40–50% lower prices compared to established nodes like Vashi or Kharghar, with strong appreciation potential as infrastructure develops.',
  },
  ulwe: {
    highlights: [
      'Closest residential node to the upcoming NMIA (just 5 km)',
      'Direct access via Mumbai Trans Harbour Link (MTHL)',
      'CIDCO-planned sectors with wide roads and green spaces',
      'Creek-facing properties with scenic waterfront views',
      'Highest price appreciation in Navi Mumbai — 22% YoY growth',
      'Planned metro extension and waterfront promenade',
    ],
    whyBuy:
      'Ulwe is the top pick for investors seeking high returns. With MTHL now operational and the airport nearing completion, property values have surged. Early buyers stand to benefit from continued infrastructure development and increasing demand.',
  },
  vashi: {
    highlights: [
      'Commercial hub of Navi Mumbai with Inorbit Mall and APMC Market',
      'Excellent railway connectivity — Vashi station on Harbour Line',
      'Premium schools, hospitals, and entertainment infrastructure',
      'Upcoming Navi Mumbai Metro station at Vashi',
      'Mature neighbourhood with established amenities and social infrastructure',
      'High rental yield due to commercial activity and demand',
    ],
    whyBuy:
      'Vashi is a premium, established market best suited for end-users who value urban convenience. While prices are higher, buyers get a fully developed neighbourhood with zero dependency on upcoming infrastructure. Rental yields are among the highest in Navi Mumbai.',
  },
};

/* ---------- Location FAQs ---------- */

const MOCK_FAQS: Record<string, Array<{ question: string; answer: string }>> = {
  kharghar: [
    { question: 'What is the average property price in Kharghar?', answer: 'The average property price in Kharghar is approximately ₹8,500 per sq ft. A 2 BHK apartment typically ranges from ₹85 Lac to ₹1.4 Cr, while 3 BHK units range from ₹1.2 Cr to ₹2 Cr depending on the developer and amenities.' },
    { question: 'Is Kharghar a good location to buy a flat?', answer: 'Yes, Kharghar is one of the best locations in Navi Mumbai for homebuyers. It offers excellent infrastructure, green spaces like Central Park, top educational institutions, and strong connectivity. The upcoming metro and proximity to the Navi Mumbai airport make it a solid long-term investment.' },
    { question: 'How far is Kharghar from the Navi Mumbai airport?', answer: 'Kharghar is approximately 15 km from the upcoming Navi Mumbai International Airport (NMIA). Once operational, the airport combined with metro connectivity will significantly enhance Kharghar\'s accessibility and property values.' },
    { question: 'What are the top developers in Kharghar?', answer: 'Leading developers in Kharghar include Lodha Group, Paradise Group, Adhiraj Constructions, Dosti Realty, Balaji Group, and Haware. These developers offer RERA-registered projects with modern amenities and reliable delivery records.' },
  ],
  panvel: [
    { question: 'What is the average property price in Panvel?', answer: 'The average property price in Panvel is approximately ₹5,800 per sq ft. A 1 BHK starts from ₹36 Lac, while 2 BHK apartments range from ₹62 Lac to ₹1.05 Cr, making it one of the most affordable locations in the Mumbai Metropolitan Region.' },
    { question: 'Is Panvel good for real estate investment?', answer: 'Yes, Panvel is among the best locations for real estate investment in MMR. With 18.2% price growth in the last year, proximity to the upcoming airport, and inclusion in the NAINA Smart City plan, Panvel offers strong appreciation potential at affordable entry prices.' },
    { question: 'How far is Panvel from the Navi Mumbai airport?', answer: 'Panvel is just 8 km from the upcoming Navi Mumbai International Airport. This close proximity is the primary growth driver for the area, with property prices expected to rise significantly once the airport becomes operational.' },
    { question: 'What are the upcoming infrastructure projects near Panvel?', answer: 'Key upcoming projects include the Navi Mumbai International Airport (8 km), Virar-Alibaug Multimodal Corridor, NAINA Smart City township development, and the Panvel-Karjat railway line upgrade. These will transform Panvel\'s connectivity and livability.' },
  ],
  ulwe: [
    { question: 'What is the average property price in Ulwe?', answer: 'The average property price in Ulwe is approximately ₹6,200 per sq ft. A 1 BHK starts from around ₹41 Lac, while 2 BHK apartments range from ₹65 Lac to ₹79 Lac, offering excellent value given its proximity to the airport and MTHL.' },
    { question: 'Is Ulwe a good place to invest in property?', answer: 'Ulwe is currently the highest-appreciating location in Navi Mumbai with 22% YoY price growth. Its proximity to the Navi Mumbai airport (5 km) and direct MTHL access make it a top investment destination. Early buyers have seen significant returns.' },
    { question: 'How is the connectivity of Ulwe?', answer: 'Ulwe is connected via the Mumbai Trans Harbour Link (MTHL), providing direct access to South Mumbai. The nearest railway station is Nerul (8 km). A metro extension to Ulwe is planned, which will further improve connectivity.' },
    { question: 'What is the future of Ulwe real estate?', answer: 'Ulwe\'s future is extremely promising. With the airport 5 km away, MTHL operational, proposed metro extension, and waterfront promenade development, property values are expected to continue appreciating strongly over the next 3-5 years.' },
  ],
  vashi: [
    { question: 'What is the average property price in Vashi?', answer: 'Vashi commands premium pricing with an average of ₹14,200 per sq ft. A 2 BHK typically costs ₹1.28 Cr to ₹1.58 Cr, while 3 BHK apartments range from ₹2 Cr to ₹3.3 Cr. Prices reflect its status as Navi Mumbai\'s most established node.' },
    { question: 'Why is Vashi more expensive than other Navi Mumbai locations?', answer: 'Vashi is the commercial hub of Navi Mumbai with mature infrastructure, premium malls (Inorbit), APMC market, top schools, and excellent railway connectivity. The established social infrastructure and high demand for both residential and commercial spaces justify the premium pricing.' },
    { question: 'Is Vashi good for end-users or investors?', answer: 'Vashi is primarily suited for end-users who want a ready, fully-developed neighbourhood. While appreciation (8.5% YoY) is moderate compared to emerging nodes, it offers stability, high rental yields, and zero infrastructure dependency — everything is already built.' },
    { question: 'What are the best projects in Vashi?', answer: 'Top projects in Vashi include L&T Seawoods Residences, Ekta Tripolis, Ariisto Sommet, and Sai Mannat. These are from reputed developers offering premium specifications, modern amenities, and excellent locations within the Vashi node.' },
  ],
};

/* ---------- Nearby locations ---------- */

const ALL_LOCATIONS = [
  { title: 'Kharghar', slug: 'kharghar', projectCount: 42, avgPrice: 8500 },
  { title: 'Panvel', slug: 'panvel', projectCount: 38, avgPrice: 5800 },
  { title: 'Ulwe', slug: 'ulwe', projectCount: 35, avgPrice: 6200 },
  { title: 'Vashi', slug: 'vashi', projectCount: 18, avgPrice: 14200 },
  { title: 'Airoli', slug: 'airoli', projectCount: 12, avgPrice: 11500 },
  { title: 'Ghansoli', slug: 'ghansoli', projectCount: 10, avgPrice: 9800 },
  { title: 'Nerul', slug: 'nerul', projectCount: 15, avgPrice: 12500 },
  { title: 'Taloja', slug: 'taloja', projectCount: 28, avgPrice: 4200 },
];

/* ---------- Fetch projects from WordPress API ---------- */

async function getDbProjects(locationSlug: string): Promise<ProjectCardType[]> {
  return wpFetchProjects(locationSlug);
}

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
  const dbProjects = await getDbProjects(locationSlug);
  // Use DB projects if available, fall back to mock data
  const projects = dbProjects.length > 0 ? dbProjects : (MOCK_PROJECTS[locationSlug] ?? []);
  const aboutData = MOCK_ABOUT[locationSlug];
  const faqs = MOCK_FAQS[locationSlug] ?? [];
  const nearbyLocations = ALL_LOCATIONS.filter((l) => l.slug !== locationSlug);

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

      {/* About the locality */}
      {aboutData && (
        <Section>
          <Container>
            <h2 className="text-h2 text-gray-900">
              About {location.title}, Navi Mumbai
            </h2>
            <p className="mt-lg max-w-[720px] text-sm leading-relaxed text-gray-600">
              {location.description}
            </p>

            {/* Highlights */}
            <div className="mt-xl">
              <h3 className="text-base font-semibold text-gray-900">
                Key Highlights
              </h3>
              <div className="mt-lg grid grid-cols-1 gap-sm sm:grid-cols-2">
                {aboutData.highlights.map((item, i) => (
                  <div key={i} className="flex items-start gap-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" className="mt-[2px] shrink-0 text-success" aria-hidden="true">
                      <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span className="text-sm text-gray-700">{item}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Why buy */}
            <div className="mt-xl rounded-md border border-brand-primary/10 bg-brand-primary-bg p-xl">
              <h3 className="text-base font-semibold text-gray-900">
                Why Buy in {location.title}?
              </h3>
              <p className="mt-md text-sm leading-relaxed text-gray-700">
                {aboutData.whyBuy}
              </p>
            </div>
          </Container>
        </Section>
      )}

      {/* FAQ Section */}
      {faqs && faqs.length > 0 && (
        <Section variant="alt">
          <Container>
            <h2 className="text-h2 text-gray-900">
              Frequently Asked Questions — {location.title}
            </h2>
            <p className="mt-sm text-sm text-gray-500">
              Common questions about buying property in {location.title}, Navi Mumbai
            </p>
            <div className="mt-xl flex flex-col gap-md">
              {faqs.map((faq, index) => (
                <details
                  key={index}
                  className="group rounded-md border border-gray-200 bg-white"
                >
                  <summary className="flex cursor-pointer items-center justify-between px-xl py-lg text-sm font-medium text-gray-900 [&::-webkit-details-marker]:hidden">
                    {faq.question}
                    <svg
                      width="18"
                      height="18"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      className="ml-lg shrink-0 text-gray-400 transition-transform group-open:rotate-180"
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
          </Container>
        </Section>
      )}

      {/* Explore Nearby Locations */}
      <Section>
        <Container>
          <h2 className="text-h2 text-gray-900">
            Explore Other Locations in Navi Mumbai
          </h2>
          <p className="mt-sm text-sm text-gray-500">
            Compare projects across Navi Mumbai&apos;s top residential nodes
          </p>
          <div className="mt-xl grid grid-cols-2 gap-md sm:grid-cols-3 lg:grid-cols-4">
            {nearbyLocations.map((loc) => (
              <Link
                key={loc.slug}
                href={`/navi-mumbai/${loc.slug}`}
                className="group flex flex-col rounded-lg border border-gray-200 bg-white p-lg no-underline transition-shadow hover:shadow-card hover:no-underline"
              >
                <h3 className="text-base font-semibold text-gray-900 group-hover:text-brand-primary">
                  {loc.title}
                </h3>
                <p className="mt-xs text-caption text-gray-500">
                  {loc.projectCount} projects
                </p>
                <p className="mt-sm text-sm font-semibold text-gray-900">
                  {'\u20B9'}{loc.avgPrice.toLocaleString('en-IN')}/sqft
                </p>
                <span className="mt-md text-caption font-medium text-brand-primary">
                  View Projects →
                </span>
              </Link>
            ))}
          </div>
        </Container>
      </Section>

      {/* Lead Capture CTA */}
      <section className="bg-gradient-to-br from-brand-primary to-brand-primary-dark py-3xl">
        <Container>
          <div className="mx-auto max-w-narrow text-center">
            <h2 className="text-h2 text-white">
              Need help finding the right project in {location.title}?
            </h2>
            <p className="mx-auto mt-md max-w-[480px] text-sm text-white/70">
              Our property advisors have in-depth knowledge of {location.title} and can help
              you shortlist the best options based on your budget and requirements.
            </p>
            <div className="mt-2xl flex flex-col items-center justify-center gap-md sm:flex-row">
              <Link
                href="/"
                className="inline-flex h-[48px] items-center justify-center rounded-sm bg-white px-2xl text-sm font-semibold text-brand-primary no-underline transition-colors hover:bg-white/90 hover:no-underline"
              >
                Get AI Recommendations
              </Link>
              <a
                href="tel:+919876543210"
                className="inline-flex h-[48px] items-center justify-center gap-sm rounded-sm border border-white/30 px-2xl text-sm font-medium text-white no-underline transition-colors hover:bg-white/10 hover:no-underline"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                </svg>
                Call Us Now
              </a>
            </div>
            <p className="mt-lg text-caption text-white/50">
              Free consultation • No brokerage • RERA verified projects only
            </p>
          </div>
        </Container>
      </section>
    </>
  );
}
