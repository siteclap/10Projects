import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { wpFetchProject, wpFetchProjects, wpFetchAllProjectSlugs } from '@/lib/wp-api';
import { Container } from '@/components/layout/Container';
import { Breadcrumbs } from '@/components/layout/Breadcrumbs';
import { Badge } from '@/components/ui/Badge';
import { FitScoreBadge } from '@/components/project/FitScoreBadge';
import { ProjectGallery } from '@/components/project/ProjectGallery';
import { ProjectTabs } from '@/components/project/ProjectTabs';
import { ProjectSidebar } from '@/components/project/ProjectSidebar';
import { FitScoreBreakdown } from '@/components/project/FitScoreBreakdown';
import { AmenityGrid } from '@/components/project/AmenityGrid';
import { ProjectActionsWrapper } from './ProjectActionsWrapper';
import { MobileStickyBar } from './MobileStickyBar';
import { LeadFormWrapper } from './LeadFormWrapper';
import { InlineLeadCTA } from '@/components/lead/InlineLeadCTA';
import { FloorPlanSection } from '@/components/project/FloorPlanSection';
import { EmiCalculator } from '@/components/widgets/EmiCalculator';
import { JsonLd } from '@/components/seo/JsonLd';
import { projectMetadata } from '@/lib/seo/metadata';
import {
  realEstateListingJsonLd,
  breadcrumbJsonLd,
  faqJsonLd,
} from '@/lib/seo/json-ld';
import { formatPrice, formatPriceRange } from '@/lib/utils/format-price';
import type { Project, ProjectCard as ProjectCardType, PropertyType } from '@/lib/types/project';

// --- Mock data (used when WordPress API is unavailable during local build) ---

const MOCK_PROJECTS: Record<string, Project> = {
  'lodha-palava-crown': { id: 1, title: 'Lodha Palava Crown', slug: 'lodha-palava-crown', permalink: '/navi-mumbai/kharghar/lodha-palava-crown/', thumbnail: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&q=80', developer: 'Lodha Group', location: 'Kharghar', construction_stage: 'Under Construction', expected_possession: 'Dec 2026', rera_number: 'P52000046631', configurations: [{ config_type: '2 BHK', carpet_area_sqft: 650, base_price: 8500000, total_price: 9200000, inventory_total: 120, inventory_available: 45 }, { config_type: '3 BHK', carpet_area_sqft: 950, base_price: 12500000, total_price: 13800000, inventory_total: 80, inventory_available: 22 }], price_min: 9200000, price_max: 13800000, fit_score: 94, description: 'Lodha Palava Crown is a premium residential project in Kharghar by Lodha Group.', amenities: ['Swimming Pool', 'Gymnasium', 'Clubhouse', 'Children\'s Play Area', 'Landscaped Garden'], pros: ['Tier-1 developer with strong delivery track record', 'Near Kharghar railway station'], cons: ['Premium pricing compared to nearby projects'] },
  'paradise-sai-world-empire': { id: 2, title: 'Paradise Sai World Empire', slug: 'paradise-sai-world-empire', permalink: '/navi-mumbai/kharghar/paradise-sai-world-empire/', thumbnail: 'https://images.unsplash.com/photo-1460317442991-0ec209397118?w=800&q=80', developer: 'Paradise Group', location: 'Kharghar', construction_stage: 'Ready to Move', expected_possession: '', rera_number: 'P52000029541', configurations: [{ config_type: '2 BHK', carpet_area_sqft: 720, base_price: 9800000, total_price: 10500000, inventory_total: 200, inventory_available: 15 }], price_min: 10500000, price_max: 10500000, fit_score: 91, description: 'Paradise Sai World Empire is a ready-to-move-in township in Kharghar.', amenities: ['Swimming Pool', 'Clubhouse', 'Garden', 'Gymnasium'], pros: ['Ready to move in', 'Established township'], cons: ['Limited inventory'] },
  'balaji-symphony': { id: 3, title: 'Balaji Symphony', slug: 'balaji-symphony', permalink: '/navi-mumbai/panvel/balaji-symphony/', thumbnail: 'https://images.unsplash.com/photo-1515263487990-61b07816b324?w=800&q=80', developer: 'Balaji Group', location: 'Panvel', construction_stage: 'Under Construction', expected_possession: 'Mar 2027', rera_number: 'P52000048892', configurations: [{ config_type: '1 BHK', carpet_area_sqft: 420, base_price: 4200000, total_price: 4800000, inventory_total: 150, inventory_available: 88 }, { config_type: '2 BHK', carpet_area_sqft: 630, base_price: 6800000, total_price: 7500000, inventory_total: 100, inventory_available: 52 }], price_min: 4800000, price_max: 7500000, fit_score: 88, description: 'Balaji Symphony offers affordable apartments in Panvel near the upcoming airport.', amenities: ['Garden', 'Children\'s Play Area', 'Gymnasium'], pros: ['Affordable pricing', 'Close to NMIA airport'], cons: ['Social infrastructure still developing'] },
  'arihant-aspire': { id: 4, title: 'Arihant Aspire', slug: 'arihant-aspire', permalink: '/navi-mumbai/panvel/arihant-aspire/', thumbnail: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&q=80', developer: 'Arihant Superstructures', location: 'Panvel', construction_stage: 'Under Construction', expected_possession: 'Jun 2027', rera_number: 'P52000051203', configurations: [{ config_type: '1 BHK', carpet_area_sqft: 390, base_price: 3800000, total_price: 4200000, inventory_total: 180, inventory_available: 95 }, { config_type: '2 BHK', carpet_area_sqft: 580, base_price: 5600000, total_price: 6200000, inventory_total: 120, inventory_available: 67 }], price_min: 4200000, price_max: 6200000, fit_score: 85, description: 'Arihant Aspire is a value-for-money project in Panvel.', amenities: ['Gymnasium', 'Garden', 'Community Hall'], pros: ['Most affordable in Panvel', 'Listed developer'], cons: ['Moderate delivery track record'] },
  'jerai-elysium': { id: 5, title: 'JERAI Elysium', slug: 'jerai-elysium', permalink: '/navi-mumbai/ulwe/jerai-elysium/', thumbnail: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&q=80', developer: 'JERAI Group', location: 'Ulwe', construction_stage: 'Under Construction', expected_possession: 'Sep 2026', rera_number: 'P52000047123', configurations: [{ config_type: '2 BHK', carpet_area_sqft: 680, base_price: 7200000, total_price: 7900000, inventory_total: 90, inventory_available: 8 }], price_min: 7900000, price_max: 7900000, fit_score: 82, description: 'JERAI Elysium is a residential project in Ulwe near the upcoming airport.', amenities: ['Clubhouse', 'Swimming Pool', 'Garden'], pros: ['Closest to NMIA airport', 'MTHL access'], cons: ['Very limited inventory'] },
  'lt-seawoods-residences': { id: 6, title: 'L&T Seawoods Residences', slug: 'lt-seawoods-residences', permalink: '/navi-mumbai/vashi/lt-seawoods-residences/', thumbnail: 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&q=80', developer: 'L&T Realty', location: 'Vashi', construction_stage: 'Ready to Move', expected_possession: '', rera_number: 'P52000032876', configurations: [{ config_type: '2 BHK', carpet_area_sqft: 780, base_price: 14500000, total_price: 15800000, inventory_total: 60, inventory_available: 5 }, { config_type: '3 BHK', carpet_area_sqft: 1100, base_price: 21000000, total_price: 23500000, inventory_total: 40, inventory_available: 3 }], price_min: 15800000, price_max: 23500000, fit_score: 90, description: 'L&T Seawoods Residences is a premium ready-to-move project in Vashi.', amenities: ['Swimming Pool', 'Gymnasium', 'Clubhouse', 'Tennis Court', 'Jogging Track'], pros: ['L&T construction quality', 'Ready to move', 'Premium location'], cons: ['Premium pricing', 'Very limited units'] },
  'godrej-vihaa': { id: 7, title: 'Godrej Vihaa', slug: 'godrej-vihaa', permalink: '/navi-mumbai/airoli/godrej-vihaa/', thumbnail: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&q=80', developer: 'Godrej Properties', location: 'Airoli', construction_stage: 'Under Construction', expected_possession: 'Dec 2027', rera_number: 'P52000053447', configurations: [{ config_type: '2 BHK', carpet_area_sqft: 700, base_price: 11200000, total_price: 12500000, inventory_total: 110, inventory_available: 12 }], price_min: 12500000, price_max: 12500000, fit_score: 87, description: 'Godrej Vihaa is a residential project in Airoli by Godrej Properties.', amenities: ['Swimming Pool', 'Gymnasium', 'Clubhouse', 'Garden'], pros: ['Godrej brand quality', 'Airoli IT hub proximity'], cons: ['Limited to 2 BHK', 'Low inventory'] },
};

const MOCK_SIMILAR: Record<string, ProjectCardType[]> = {
  kharghar: [
    { id: 1, title: 'Lodha Palava Crown', slug: 'lodha-palava-crown', permalink: '/navi-mumbai/kharghar/lodha-palava-crown/', thumbnail: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&q=80', developer: 'Lodha Group', location: 'Kharghar', construction_stage: 'Under Construction', expected_possession: 'Dec 2026', rera_number: 'P52000046631', configurations: [{ config_type: '2 BHK', carpet_area_sqft: 650, base_price: 8500000, total_price: 9200000, inventory_total: 120, inventory_available: 45 }], price_min: 9200000, price_max: 13800000, fit_score: 94 },
    { id: 2, title: 'Paradise Sai World Empire', slug: 'paradise-sai-world-empire', permalink: '/navi-mumbai/kharghar/paradise-sai-world-empire/', thumbnail: 'https://images.unsplash.com/photo-1460317442991-0ec209397118?w=800&q=80', developer: 'Paradise Group', location: 'Kharghar', construction_stage: 'Ready to Move', expected_possession: '', rera_number: 'P52000029541', configurations: [{ config_type: '2 BHK', carpet_area_sqft: 720, base_price: 9800000, total_price: 10500000, inventory_total: 200, inventory_available: 15 }], price_min: 10500000, price_max: 10500000, fit_score: 91 },
  ],
  panvel: [
    { id: 3, title: 'Balaji Symphony', slug: 'balaji-symphony', permalink: '/navi-mumbai/panvel/balaji-symphony/', thumbnail: 'https://images.unsplash.com/photo-1515263487990-61b07816b324?w=800&q=80', developer: 'Balaji Group', location: 'Panvel', construction_stage: 'Under Construction', expected_possession: 'Mar 2027', rera_number: 'P52000048892', configurations: [{ config_type: '1 BHK', carpet_area_sqft: 420, base_price: 4200000, total_price: 4800000, inventory_total: 150, inventory_available: 88 }], price_min: 4800000, price_max: 7500000, fit_score: 88 },
    { id: 4, title: 'Arihant Aspire', slug: 'arihant-aspire', permalink: '/navi-mumbai/panvel/arihant-aspire/', thumbnail: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&q=80', developer: 'Arihant Superstructures', location: 'Panvel', construction_stage: 'Under Construction', expected_possession: 'Jun 2027', rera_number: 'P52000051203', configurations: [{ config_type: '1 BHK', carpet_area_sqft: 390, base_price: 3800000, total_price: 4200000, inventory_total: 180, inventory_available: 95 }], price_min: 4200000, price_max: 6200000, fit_score: 85 },
  ],
  ulwe: [
    { id: 5, title: 'JERAI Elysium', slug: 'jerai-elysium', permalink: '/navi-mumbai/ulwe/jerai-elysium/', thumbnail: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&q=80', developer: 'JERAI Group', location: 'Ulwe', construction_stage: 'Under Construction', expected_possession: 'Sep 2026', rera_number: 'P52000047123', configurations: [{ config_type: '2 BHK', carpet_area_sqft: 680, base_price: 7200000, total_price: 7900000, inventory_total: 90, inventory_available: 8 }], price_min: 7900000, price_max: 7900000, fit_score: 82 },
  ],
  vashi: [
    { id: 6, title: 'L&T Seawoods Residences', slug: 'lt-seawoods-residences', permalink: '/navi-mumbai/vashi/lt-seawoods-residences/', thumbnail: 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&q=80', developer: 'L&T Realty', location: 'Vashi', construction_stage: 'Ready to Move', expected_possession: '', rera_number: 'P52000032876', configurations: [{ config_type: '2 BHK', carpet_area_sqft: 780, base_price: 14500000, total_price: 15800000, inventory_total: 60, inventory_available: 5 }], price_min: 15800000, price_max: 23500000, fit_score: 90 },
  ],
  airoli: [
    { id: 7, title: 'Godrej Vihaa', slug: 'godrej-vihaa', permalink: '/navi-mumbai/airoli/godrej-vihaa/', thumbnail: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&q=80', developer: 'Godrej Properties', location: 'Airoli', construction_stage: 'Under Construction', expected_possession: 'Dec 2027', rera_number: 'P52000053447', configurations: [{ config_type: '2 BHK', carpet_area_sqft: 700, base_price: 11200000, total_price: 12500000, inventory_total: 110, inventory_available: 12 }], price_min: 12500000, price_max: 12500000, fit_score: 87 },
  ],
};

// --- Demo gallery images per project ---

const MOCK_GALLERY: Record<string, string[]> = {
  'lodha-palava-crown': [
    'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=1200&q=80',
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=80',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1200&q=80',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=1200&q=80',
    'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?w=1200&q=80',
  ],
  'paradise-sai-world-empire': [
    'https://images.unsplash.com/photo-1460317442991-0ec209397118?w=1200&q=80',
    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80',
    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=1200&q=80',
    'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=1200&q=80',
    'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=80',
  ],
  'balaji-symphony': [
    'https://images.unsplash.com/photo-1515263487990-61b07816b324?w=1200&q=80',
    'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1200&q=80',
    'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?w=1200&q=80',
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=80',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=1200&q=80',
  ],
  'arihant-aspire': [
    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1200&q=80',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1200&q=80',
    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=1200&q=80',
    'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=80',
    'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=1200&q=80',
  ],
  'jerai-elysium': [
    'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=1200&q=80',
    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80',
    'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?w=1200&q=80',
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=80',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=1200&q=80',
  ],
  'lt-seawoods-residences': [
    'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&q=80',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1200&q=80',
    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=1200&q=80',
    'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=80',
    'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=1200&q=80',
  ],
  'godrej-vihaa': [
    'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1200&q=80',
    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80',
    'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?w=1200&q=80',
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=80',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=1200&q=80',
  ],
};

// --- Data fetching with mock fallback ---

async function getProject(_locationSlug: string, projectSlug: string) {
  const project = await wpFetchProject(projectSlug);
  if (project) {
    // Use banner images first, then gallery, then thumbnail as fallback.
    const images = [
      ...(project.banner_desktop_images || []),
      ...(project.gallery_images || []),
    ];
    if (images.length === 0 && project.thumbnail) {
      images.push(project.thumbnail);
    }
    return { project, images };
  }
  const mock = MOCK_PROJECTS[projectSlug];
  if (!mock) return null;
  const images = MOCK_GALLERY[projectSlug] ?? (mock.thumbnail ? [mock.thumbnail] : []);
  return { project: mock, images };
}

async function getSimilarProjects(locationSlug: string, excludeSlug: string) {
  let allProjects = await wpFetchProjects(locationSlug);
  if (allProjects.length === 0) {
    allProjects = MOCK_SIMILAR[locationSlug] ?? [];
  }
  return allProjects
    .filter((p) => p.slug !== excludeSlug)
    .slice(0, 4)
    .map((p) => ({
      title: p.title,
      developer: p.developer,
      location: `${p.location}, Navi Mumbai`,
      priceMin: p.price_min,
      priceMax: p.price_max,
      configs: p.configurations.map((c) => c.config_type).join(', '),
      fitScore: p.fit_score ?? 0,
      image: p.thumbnail || '',
      slug: p.permalink,
    }));
}

function generateFaqs(project: Project) {
  const faqs = [];

  if (project.price_min > 0 && project.price_max > 0) {
    faqs.push({
      question: `What is the price range of ${project.title}?`,
      answer: `${project.title} offers configurations ranging from ${formatPriceRange(project.price_min, project.price_max)}.${project.configurations.length > 0 ? ` Available types include ${project.configurations.map((c) => `${c.config_type} at ${formatPrice(c.total_price)}`).join(', ')}.` : ''}`,
    });
  }

  if (project.rera_number) {
    faqs.push({
      question: `Is ${project.title} RERA registered?`,
      answer: `Yes, ${project.title} is RERA registered with registration number ${project.rera_number}.`,
    });
  }

  if (project.expected_possession) {
    faqs.push({
      question: `What is the possession date of ${project.title}?`,
      answer: `The expected possession date for ${project.title} is ${project.expected_possession}. The project is currently ${project.construction_stage.toLowerCase()}.`,
    });
  }

  if (project.amenities && project.amenities.length > 0) {
    faqs.push({
      question: `What amenities are available at ${project.title}?`,
      answer: `${project.title} offers amenities including ${project.amenities.join(', ')}.`,
    });
  }

  faqs.push({
    question: `Where is ${project.title} located?`,
    answer: `${project.title} is located in ${project.location}, Navi Mumbai. It is a project by ${project.developer}.`,
  });

  return faqs;
}

function getTabsForCategory(category: PropertyType = 'buy') {
  switch (category) {
    case 'rent':
      return [
        { id: 'overview', label: 'Overview' },
        { id: 'rent', label: 'Rent & Details' },
        { id: 'amenities', label: 'Amenities' },
        { id: 'floor-plans', label: 'Floor Plans' },
        { id: 'pros-cons', label: 'Pros & Cons' },
        { id: 'location', label: 'Location' },
        { id: 'developer', label: 'Developer' },
        { id: 'faq', label: 'FAQ' },
      ];
    case 'commercial':
      return [
        { id: 'overview', label: 'Overview' },
        { id: 'specs', label: 'Price & Specs' },
        { id: 'amenities', label: 'Amenities' },
        { id: 'unit-plans', label: 'Unit Plans' },
        { id: 'pros-cons', label: 'Pros & Cons' },
        { id: 'location', label: 'Location' },
        { id: 'developer', label: 'Developer' },
        { id: 'faq', label: 'FAQ' },
      ];
    case 'plot':
    case 'plots':
      return [
        { id: 'overview', label: 'Overview' },
        { id: 'plot', label: 'Price & Details' },
        { id: 'features', label: 'Features' },
        { id: 'pros-cons', label: 'Pros & Cons' },
        { id: 'location', label: 'Location' },
        { id: 'developer', label: 'Developer' },
        { id: 'faq', label: 'FAQ' },
      ];
    case 'pg':
      return [
        { id: 'overview', label: 'Overview' },
        { id: 'rooms', label: 'Rooms & Pricing' },
        { id: 'amenities', label: 'Amenities & Facilities' },
        { id: 'rules', label: 'Rules' },
        { id: 'location', label: 'Location' },
        { id: 'faq', label: 'FAQ' },
      ];
    default: // buy, resale
      return [
        { id: 'overview', label: 'Overview' },
        { id: 'price', label: 'Price' },
        { id: 'pros-cons', label: 'Pros & Cons' },
        { id: 'amenities', label: 'Amenities' },
        { id: 'floor-plans', label: 'Floor Plans' },
        { id: 'location', label: 'Location' },
        { id: 'developer', label: 'Developer' },
        { id: 'faq', label: 'FAQ' },
      ];
  }
}

function getCtaLabels(category: PropertyType = 'buy') {
  switch (category) {
    case 'rent':
      return { primary: 'Schedule Visit', secondary: 'Check Availability' };
    case 'commercial':
      return { primary: 'Get Quote', secondary: 'Schedule Tour' };
    case 'plot':
    case 'plots':
      return { primary: 'Get Best Price', secondary: 'Visit Site' };
    case 'pg':
      return { primary: 'Check Availability', secondary: 'Book a Room' };
    default:
      return { primary: 'Get Best Price', secondary: 'Book Site Visit' };
  }
}

// --- Page component ---

type PageProps = {
  params: Promise<{ location: string; project: string }>;
};

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { location, project: projectSlug } = await params;
  const result = await getProject(location, projectSlug);
  if (!result) return { title: 'Project Not Found' };
  return projectMetadata(result.project);
}

export async function generateStaticParams(): Promise<
  Array<{ location: string; project: string }>
> {
  const slugs = await wpFetchAllProjectSlugs();
  if (slugs.length > 0) {
    return slugs
      .filter((p) => p.location_slug)
      .map((p) => ({
        location: p.location_slug,
        project: p.slug,
      }));
  }
  // Fallback to mock slugs when WP API is unavailable
  return Object.values(MOCK_PROJECTS).map((p) => ({
    location: p.location.toLowerCase(),
    project: p.slug,
  }));
}

export default async function ProjectDetailPage({ params }: PageProps) {
  const { location, project: projectSlug } = await params;
  const result = await getProject(location, projectSlug);

  if (!result) {
    notFound();
  }

  const { project, images } = result;
  const fullUrl = `${process.env.NEXT_PUBLIC_SITE_URL || 'https://leadmaaxx.com'}${project.permalink}`;
  const faqs = generateFaqs(project);
  const similarProjects = await getSimilarProjects(location, projectSlug);

  const category = (project.property_type || 'buy') as PropertyType;
  const tabs = getTabsForCategory(category);
  const ctaLabels = getCtaLabels(category);
  const isBuy = category === 'buy' || category === 'resale';
  const isRent = category === 'rent';
  const isCommercial = category === 'commercial';
  const isPlot = category === 'plot' || category === 'plots';
  const isPg = category === 'pg';

  const pros = project.pros || [];
  const cons = project.cons || [];
  const amenities = project.amenities || [];

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

  const devInitials = project.developer
    .split(' ')
    .map((w) => w[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();

  return (
    <LeadFormWrapper projectTitle={project.title}>
      {/* Structured data */}
      <JsonLd data={realEstateListingJsonLd(project)} />
      <JsonLd data={breadcrumbJsonLd(breadcrumbLdItems)} />
      <JsonLd data={faqJsonLd(faqs)} />

      {/* Tab navigation */}
      <ProjectTabs tabs={tabs} />

      {/* Full-width gallery — outside the content+sidebar flex */}
      <Container>
        <Breadcrumbs items={breadcrumbItems} className="mt-xl" />
        {images.length > 0 && (
          <div className="mt-xl">
            <ProjectGallery images={images} badge={project.construction_stage} />
          </div>
        )}
      </Container>

      {/* Content + sidebar layout starts below gallery */}
      <Container>
        {/* Mobile sidebar — price, advisor, trust (visible below lg) */}
        <ProjectSidebar
          project={project}
          propertyType={category}
          ctaLabels={ctaLabels}
          className="mt-xl lg:hidden"
        />

        <div className="mt-2xl flex items-start gap-3xl pb-5xl">
          {/* Main content column */}
          <div className="min-w-0 flex-1">
            {/* Title and badges */}
            <div>
              <div className="flex items-start gap-xl">
                <div className="flex-1">
                  <h1 className="text-h1 text-gray-900">{project.title}</h1>
                  <p className="mt-sm text-base text-gray-500">
                    by {project.developer} in {project.location}
                  </p>
                </div>
                {project.fit_score != null && project.fit_score > 0 && (
                  <FitScoreBadge score={project.fit_score} size="lg" />
                )}
              </div>

              {/* Status badges */}
              <div className="mt-lg flex flex-wrap items-center gap-sm">
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
              <div className="mt-xl">
                <ProjectActionsWrapper
                  projectId={project.id}
                  projectTitle={project.title}
                  projectUrl={fullUrl}
                />
              </div>
            </div>

            {/* Section: Overview */}
            <section id="overview" className="mt-3xl border-t border-gray-100 pt-3xl">
              {/* Overview card */}
              <div className="rounded-md border border-gray-200 bg-white">
                <div className="p-xl">
                  <h2 className="text-h2 text-gray-900">Overview</h2>
                  <p className="mt-lg text-sm leading-relaxed text-gray-600">
                    {project.description || (
                      <>
                        {project.title} is a {isRent ? 'rental property' : isCommercial ? 'commercial property' : isPlot ? 'plot' : isPg ? 'paying guest accommodation' : 'residential project'} by {project.developer}{' '}
                        located in {project.location}, Navi Mumbai.
                        {isBuy && project.configurations.length > 0 && (
                          <> The project offers {project.configurations.map((c) => c.config_type).join(', ')} configurations.</>
                        )}
                      </>
                    )}
                  </p>
                </div>

                {/* Stats row */}
                <div className="grid grid-cols-2 border-t border-gray-100 sm:grid-cols-4">
                  <div className="border-r border-gray-100 p-lg">
                    <p className="text-caption text-gray-500">Configuration</p>
                    <p className="mt-xs text-sm font-semibold text-gray-900">
                      {project.configurations.length > 0
                        ? project.configurations.map((c) => c.config_type).join(', ')
                        : 'N/A'}
                    </p>
                  </div>
                  <div className="border-r border-gray-100 p-lg sm:border-r">
                    <p className="text-caption text-gray-500">Status / Possession</p>
                    <p className="mt-xs text-sm font-semibold text-gray-900">
                      {project.construction_stage} — {project.expected_possession}
                    </p>
                  </div>
                  <div className="border-r border-gray-100 border-t border-t-gray-100 p-lg sm:border-t-0">
                    <p className="text-caption text-gray-500">Avg. Price</p>
                    <p className="mt-xs text-sm font-semibold text-gray-900">
                      {project.price_min > 0 && project.price_max > 0
                        ? formatPriceRange(project.price_min, project.price_max)
                        : 'On Request'}
                    </p>
                  </div>
                  <div className="border-t border-gray-100 p-lg sm:border-t-0">
                    <p className="text-caption text-gray-500">RERA Number</p>
                    <p className="mt-xs text-sm font-semibold text-gray-900">
                      {project.rera_number || 'N/A'}
                    </p>
                  </div>
                </div>
              </div>

              {/* Why consider card — only show if we have pros */}
              {pros.length > 0 && (
                <div className="mt-xl rounded-md border border-accent/20 bg-accent/5 p-xl">
                  <h3 className="text-base font-semibold text-gray-900">
                    {isRent ? 'Why consider renting at' : isCommercial ? 'Why consider this commercial space at' : isPlot ? 'Why consider this plot at' : isPg ? 'Why consider staying at' : 'Why consider buying at'}{' '}
                    <span className="text-accent-dark">{project.title}</span>?
                  </h3>
                  <div className="mt-md grid grid-cols-1 gap-sm sm:grid-cols-2">
                    {pros.slice(0, 6).map((item, i) => (
                      <div key={i} className="flex items-start gap-md">
                        <span className="mt-[6px] block h-[8px] w-[8px] shrink-0 rounded-[2px] bg-accent" />
                        <span className="text-sm leading-relaxed text-gray-700">{item}</span>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Fit Score Breakdown */}
              {project.scores && (
                <div className="mt-xl">
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

            {/* Section: Price (Buy/Resale) — only if configurations exist */}
            {isBuy && project.configurations.length > 0 && (
              <section id="price" className="mt-3xl border-t border-gray-100 pt-3xl">
                <h2 className="text-h2 text-gray-900">Price & Configuration</h2>

                {/* Price table */}
                <div className="mt-xl overflow-x-auto rounded-md border border-gray-200">
                  <table className="w-full text-left text-sm">
                    <thead>
                      <tr className="border-b border-gray-200 bg-gray-50">
                        <th className="px-xl py-lg font-semibold text-gray-700">Type</th>
                        <th className="px-xl py-lg font-semibold text-gray-700">Carpet Area</th>
                        <th className="px-xl py-lg font-semibold text-gray-700">Base Price</th>
                        <th className="px-xl py-lg font-semibold text-gray-700">Total Price</th>
                        <th className="px-xl py-lg font-semibold text-gray-700">Availability</th>
                      </tr>
                    </thead>
                    <tbody>
                      {project.configurations.map((config) => (
                        <tr
                          key={config.config_type}
                          className="border-b border-gray-100 last:border-0"
                        >
                          <td className="px-xl py-lg font-medium text-gray-900">
                            {config.config_type}
                          </td>
                          <td className="px-xl py-lg text-gray-600">
                            {config.carpet_area_sqft} sq ft
                          </td>
                          <td className="px-xl py-lg text-gray-600 tabular-nums">
                            {formatPrice(config.base_price)}
                          </td>
                          <td className="px-xl py-lg font-semibold text-gray-900 tabular-nums">
                            {formatPrice(config.total_price)}
                          </td>
                          <td className="px-xl py-lg text-gray-600">
                            {config.inventory_available} / {config.inventory_total} units
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                {/* EMI Calculator */}
                {project.price_min > 0 && (
                  <div className="mt-3xl">
                    <EmiCalculator defaultPrice={project.price_min} />
                  </div>
                )}
              </section>
            )}

            {/* Section: Rent & Details (Rental) */}
            {isRent && project.rental && (
              <section id="rent" className="mt-3xl border-t border-gray-100 pt-3xl">
                <h2 className="text-h2 text-gray-900">Rent & Details</h2>
                <div className="mt-xl rounded-md border border-gray-200 divide-y divide-gray-100">
                  {project.rental.monthly_rent && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Monthly Rent</span><span className="text-sm font-semibold text-primary">₹{project.rental.monthly_rent.toLocaleString('en-IN')}/mo</span></div>
                  )}
                  {project.rental.security_deposit && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Security Deposit</span><span className="text-sm font-semibold text-gray-900">₹{project.rental.security_deposit.toLocaleString('en-IN')}</span></div>
                  )}
                  {project.rental.maintenance_charges && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Maintenance</span><span className="text-sm font-semibold text-gray-900">₹{project.rental.maintenance_charges.toLocaleString('en-IN')}/mo</span></div>
                  )}
                  {project.rental.furnishing_status && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Furnishing</span><span className="text-sm font-semibold text-gray-900">{project.rental.furnishing_status}</span></div>
                  )}
                  {project.rental.available_from && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Available From</span><span className="text-sm font-semibold text-gray-900">{project.rental.available_from}</span></div>
                  )}
                  {project.rental.tenant_preferred && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Tenant Preferred</span><span className="text-sm font-semibold text-gray-900">{project.rental.tenant_preferred}</span></div>
                  )}
                  {project.rental.lock_in_period && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Lock-in Period</span><span className="text-sm font-semibold text-gray-900">{project.rental.lock_in_period}</span></div>
                  )}
                  {project.rental.brokerage && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Brokerage</span><span className="text-sm font-semibold text-gray-900">{project.rental.brokerage}</span></div>
                  )}
                </div>
              </section>
            )}

            {/* Section: Commercial Specs */}
            {isCommercial && project.commercial && (
              <section id="specs" className="mt-3xl border-t border-gray-100 pt-3xl">
                <h2 className="text-h2 text-gray-900">Price & Specifications</h2>
                <div className="mt-xl rounded-md border border-gray-200 divide-y divide-gray-100">
                  {project.commercial.commercial_type && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Property Type</span><span className="text-sm font-semibold text-gray-900">{project.commercial.commercial_type}</span></div>
                  )}
                  {project.commercial.commercial_carpet && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Carpet Area</span><span className="text-sm font-semibold text-gray-900">{project.commercial.commercial_carpet.toLocaleString('en-IN')} sqft</span></div>
                  )}
                  {project.commercial.price_per_sqft && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Price per sqft</span><span className="text-sm font-semibold text-primary">₹{project.commercial.price_per_sqft.toLocaleString('en-IN')}/sqft</span></div>
                  )}
                  {project.commercial.building_grade && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Building Grade</span><span className="text-sm font-semibold text-gray-900">{project.commercial.building_grade}</span></div>
                  )}
                  {project.commercial.fitout_status && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Fit-out Status</span><span className="text-sm font-semibold text-gray-900">{project.commercial.fitout_status}</span></div>
                  )}
                  {project.commercial.cam_charges && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">CAM Charges</span><span className="text-sm font-semibold text-gray-900">₹{project.commercial.cam_charges}/sqft/mo</span></div>
                  )}
                  {project.commercial.seating_capacity && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Seating Capacity</span><span className="text-sm font-semibold text-gray-900">{project.commercial.seating_capacity}</span></div>
                  )}
                  {project.commercial.parking_bays && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Parking Bays</span><span className="text-sm font-semibold text-gray-900">{project.commercial.parking_bays}</span></div>
                  )}
                  {project.commercial.hvac_type && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">HVAC</span><span className="text-sm font-semibold text-gray-900">{project.commercial.hvac_type}</span></div>
                  )}
                </div>
              </section>
            )}

            {/* Section: Plot Details */}
            {isPlot && project.plot && (
              <section id="plot" className="mt-3xl border-t border-gray-100 pt-3xl">
                <h2 className="text-h2 text-gray-900">Plot Details & Pricing</h2>
                <div className="mt-xl rounded-md border border-gray-200 divide-y divide-gray-100">
                  {project.plot.plot_type && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Plot Type</span><span className="text-sm font-semibold text-gray-900">{project.plot.plot_type}</span></div>
                  )}
                  {project.plot.plot_area && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Plot Area</span><span className="text-sm font-semibold text-gray-900">{project.plot.plot_area.toLocaleString('en-IN')} sqft</span></div>
                  )}
                  {project.plot.plot_width && project.plot.plot_depth && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Dimensions</span><span className="text-sm font-semibold text-gray-900">{project.plot.plot_width} × {project.plot.plot_depth} ft</span></div>
                  )}
                  {project.plot.corner_plot && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Corner Plot</span><span className="text-sm font-semibold text-gray-900">{project.plot.corner_plot}</span></div>
                  )}
                  {project.plot.fsi && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">FSI / FAR</span><span className="text-sm font-semibold text-gray-900">{project.plot.fsi}</span></div>
                  )}
                  {project.plot.road_width && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Road Width</span><span className="text-sm font-semibold text-gray-900">{project.plot.road_width}</span></div>
                  )}
                  {project.plot.gated_community && (
                    <div className="flex justify-between px-xl py-lg"><span className="text-sm text-gray-500">Gated Community</span><span className="text-sm font-semibold text-gray-900">{project.plot.gated_community}</span></div>
                  )}
                </div>
              </section>
            )}

            {/* Section: PG Rooms & Pricing */}
            {isPg && project.pg && (
              <section id="rooms" className="mt-3xl border-t border-gray-100 pt-3xl">
                <h2 className="text-h2 text-gray-900">Rooms & Pricing</h2>
                {(project.pg.pg_gender || project.pg.pg_occupant) && (
                  <div className="mt-lg flex gap-sm">
                    {project.pg.pg_gender && <Badge variant="primary" size="md">{project.pg.pg_gender}</Badge>}
                    {project.pg.pg_occupant && <Badge variant="accent" size="md">{project.pg.pg_occupant}</Badge>}
                  </div>
                )}
                <div className="mt-xl grid grid-cols-1 gap-lg sm:grid-cols-3">
                  {[
                    { label: 'Single Sharing', rent: project.pg.pg_single_rent, icon: '1' },
                    { label: 'Double Sharing', rent: project.pg.pg_double_rent, icon: '2' },
                    { label: 'Triple Sharing', rent: project.pg.pg_triple_rent, icon: '3' },
                  ].filter(s => s.rent).map((s) => (
                    <div key={s.label} className="rounded-md border-2 border-gray-200 p-xl text-center hover:border-primary transition-colors">
                      <div className="mx-auto flex h-[40px] w-[40px] items-center justify-center rounded-full bg-gradient-to-br from-primary to-purple-600 text-white font-bold">{s.icon}</div>
                      <p className="mt-md text-xs text-gray-500 font-medium">{s.label}</p>
                      <p className="mt-xs text-xl font-bold text-gray-900">₹{s.rent!.toLocaleString('en-IN')}<span className="text-xs text-gray-400 font-normal">/mo</span></p>
                    </div>
                  ))}
                </div>
                {project.pg.pg_deposit && (
                  <div className="mt-lg rounded-md border border-gray-200 px-xl py-lg flex justify-between">
                    <span className="text-sm text-gray-500">Security Deposit</span>
                    <span className="text-sm font-semibold text-gray-900">₹{project.pg.pg_deposit.toLocaleString('en-IN')}</span>
                  </div>
                )}
              </section>
            )}

            {/* Section: Pros & Cons — only if data exists */}
            {(pros.length > 0 || cons.length > 0) && (
              <section id="pros-cons" className="mt-3xl border-t border-gray-100 pt-3xl">
                <h2 className="text-h2 text-gray-900">Pros & Cons</h2>

                <div className="mt-xl grid grid-cols-1 gap-md sm:grid-cols-2">
                  {/* Pros */}
                  {pros.length > 0 && (
                    <div className="rounded-md border border-gray-200 bg-white p-lg">
                      <div className="flex items-center gap-xs">
                        <span className="flex h-[20px] w-[20px] items-center justify-center rounded-full bg-success/10">
                          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" className="text-success" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5" />
                          </svg>
                        </span>
                        <h3 className="text-sm font-semibold text-gray-900">Pros</h3>
                      </div>
                      <ul className="mt-md flex flex-col gap-sm">
                        {pros.map((item, i) => (
                          <li key={i} className="flex items-start gap-sm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" className="mt-[2px] shrink-0 text-success" aria-hidden="true">
                              <path d="M20 6 9 17l-5-5" />
                            </svg>
                            <span className="text-caption leading-snug text-gray-700">{item}</span>
                          </li>
                        ))}
                      </ul>
                    </div>
                  )}

                  {/* Cons */}
                  {cons.length > 0 && (
                    <div className="rounded-md border border-gray-200 bg-white p-lg">
                      <div className="flex items-center gap-xs">
                        <span className="flex h-[20px] w-[20px] items-center justify-center rounded-full bg-red-50">
                          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" className="text-red-500" aria-hidden="true">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                          </svg>
                        </span>
                        <h3 className="text-sm font-semibold text-gray-900">Cons</h3>
                      </div>
                      <ul className="mt-md flex flex-col gap-sm">
                        {cons.map((item, i) => (
                          <li key={i} className="flex items-start gap-sm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" className="mt-[2px] shrink-0 text-red-500" aria-hidden="true">
                              <path d="M18 6 6 18" />
                              <path d="m6 6 12 12" />
                            </svg>
                            <span className="text-caption leading-snug text-gray-700">{item}</span>
                          </li>
                        ))}
                      </ul>
                    </div>
                  )}
                </div>
              </section>
            )}

            {/* Section: Amenities — only if data exists */}
            {amenities.length > 0 && (
              <section id="amenities" className="mt-3xl border-t border-gray-100 pt-3xl">
                <h2 className="text-h2 text-gray-900">Amenities</h2>
                <div className="mt-xl">
                  <AmenityGrid amenities={amenities} initialCount={8} />
                </div>
              </section>
            )}

            {/* Section: Floor Plans — hidden for Plot/PG */}
            {!isPlot && !isPg && project.configurations.length > 0 && (
              <FloorPlanSection
                configurations={project.configurations}
                projectTitle={project.title}
              />
            )}

            {/* Section: PG Rules */}
            {isPg && project.pg && (project.pg.pg_smoking || project.pg.pg_drinking || project.pg.pg_guests || project.pg.pg_curfew) && (
              <section id="rules" className="mt-3xl border-t border-gray-100 pt-3xl">
                <h2 className="text-h2 text-gray-900">House Rules</h2>
                <div className="mt-xl grid grid-cols-1 gap-lg sm:grid-cols-2">
                  {[
                    { label: 'Smoking', value: project.pg.pg_smoking },
                    { label: 'Drinking', value: project.pg.pg_drinking },
                    { label: 'Guests', value: project.pg.pg_guests },
                    { label: 'Curfew', value: project.pg.pg_curfew },
                  ].filter(r => r.value).map((rule) => {
                    const isNo = rule.value?.toLowerCase() === 'no';
                    return (
                      <div key={rule.label} className="flex items-start gap-md rounded-md border border-gray-200 p-lg">
                        <span className={`flex h-[32px] w-[32px] shrink-0 items-center justify-center rounded-full ${isNo ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-600'}`}>
                          {isNo ? (
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                          ) : (
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M20 6 9 17l-5-5" /></svg>
                          )}
                        </span>
                        <div>
                          <p className="text-xs text-gray-500">{rule.label}</p>
                          <p className="text-sm font-semibold text-gray-900">{rule.value}</p>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </section>
            )}

            {/* Inline CTA — after floor plans */}
            <div className="mt-3xl">
              <InlineLeadCTA projectTitle={project.title} />
            </div>

            {/* Section: Location */}
            <section id="location" className="mt-3xl border-t border-gray-100 pt-3xl">
              <h2 className="text-h2 text-gray-900">Location</h2>

              {/* Google Maps embed — uses address_pin or fallback to location name */}
              <div className="mt-xl overflow-hidden rounded-md border border-gray-200">
                <iframe
                  title={`${project.title} location on Google Maps`}
                  width="100%"
                  height="350"
                  style={{ border: 0 }}
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                  src={`https://maps.google.com/maps?q=${encodeURIComponent(project.address_pin || `${project.title}, ${project.location}, Navi Mumbai`)}&output=embed`}
                  allowFullScreen
                />
              </div>

              {/* Location Advantages — side by side */}
              {(project.location_advantage_1 || project.location_advantage_2) && (
                <div className="mt-xl grid grid-cols-1 gap-md sm:grid-cols-2">
                  {project.location_advantage_1 && (
                    <div className="rounded-md border border-gray-200 p-xl">
                      <h3 className="text-base font-semibold text-gray-900">Location Advantages</h3>
                      <div
                        className="mt-md text-sm leading-relaxed text-gray-600 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-5"
                        dangerouslySetInnerHTML={{ __html: project.location_advantage_1 }}
                      />
                    </div>
                  )}
                  {project.location_advantage_2 && (
                    <div className="rounded-md border border-gray-200 p-xl">
                      <h3 className="text-base font-semibold text-gray-900">Nearby Connectivity</h3>
                      <div
                        className="mt-md text-sm leading-relaxed text-gray-600 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-5"
                        dangerouslySetInnerHTML={{ __html: project.location_advantage_2 }}
                      />
                    </div>
                  )}
                </div>
              )}

              {/* Location Brief */}
              {project.location_brief && (
                <div className="mt-lg rounded-md border border-gray-200 p-xl">
                  <h3 className="text-base font-semibold text-gray-900">About the Location</h3>
                  <div
                    className="mt-md text-sm leading-relaxed text-gray-600"
                    dangerouslySetInnerHTML={{ __html: project.location_brief }}
                  />
                </div>
              )}
            </section>

            {/* Section: Developer */}
            <section id="developer" className="mt-3xl border-t border-gray-100 pt-3xl">
              <h2 className="text-h2 text-gray-900">About the Developer</h2>

              {/* Developer header */}
              <div className="mt-xl rounded-md border border-gray-200 p-xl">
                <div className="flex items-center gap-xl">
                  {project.developer_logo ? (
                    <img
                      src={project.developer_logo}
                      alt={project.developer}
                      className="h-[56px] w-[56px] shrink-0 rounded-md object-contain"
                    />
                  ) : (
                    <div className="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-md bg-brand-primary-pale text-h4 font-bold text-brand-primary">
                      {devInitials}
                    </div>
                  )}
                  <div>
                    <h3 className="text-h4 text-gray-900">{project.developer_name || project.developer}</h3>
                    {project.google_review_rating && (
                      <p className="mt-xs text-sm text-gray-500">Google Rating: {project.google_review_rating}</p>
                    )}
                  </div>
                </div>
              </div>

              {/* Project details — horizontal grid */}
              <div className="mt-lg grid grid-cols-2 gap-0 rounded-md border border-gray-200 sm:grid-cols-3 lg:grid-cols-4">
                {project.project_location && (
                  <div className="border-b border-r border-gray-100 p-lg">
                    <p className="text-caption text-gray-500">Project Location</p>
                    <p className="mt-xs text-sm font-semibold text-gray-900">{project.project_location}</p>
                  </div>
                )}
                {project.land_parcel && (
                  <div className="border-b border-r border-gray-100 p-lg">
                    <p className="text-caption text-gray-500">Land Parcel</p>
                    <p className="mt-xs text-sm font-semibold text-gray-900">{project.land_parcel}</p>
                  </div>
                )}
                {project.floors_display && (
                  <div className="border-b border-r border-gray-100 p-lg">
                    <p className="text-caption text-gray-500">Floors</p>
                    <p className="mt-xs text-sm font-semibold text-gray-900">{project.floors_display}</p>
                  </div>
                )}
                <div className="border-b border-r border-gray-100 p-lg">
                  <p className="text-caption text-gray-500">Possession</p>
                  <p className="mt-xs text-sm font-semibold text-gray-900">{project.expected_possession || 'N/A'}</p>
                </div>
                <div className="border-b border-r border-gray-100 p-lg">
                  <p className="text-caption text-gray-500">RERA Number</p>
                  <p className="mt-xs text-sm font-semibold text-gray-900">{project.rera_number || 'N/A'}</p>
                </div>
                {project.available_configs_text && (
                  <div className="border-b border-r border-gray-100 p-lg">
                    <p className="text-caption text-gray-500">Available Configurations</p>
                    <p className="mt-xs text-sm font-semibold text-gray-900">{project.available_configs_text}</p>
                  </div>
                )}
                <div className="border-b border-r border-gray-100 p-lg">
                  <p className="text-caption text-gray-500">Construction Status</p>
                  <p className="mt-xs text-sm font-semibold text-gray-900">{project.construction_stage}</p>
                </div>
                <div className="border-b border-r border-gray-100 p-lg">
                  <p className="text-caption text-gray-500">Price Range</p>
                  <p className="mt-xs text-sm font-semibold text-gray-900">
                    {project.price_min > 0 && project.price_max > 0
                      ? formatPriceRange(project.price_min, project.price_max)
                      : 'On Request'}
                  </p>
                </div>
              </div>

              {/* Short Overview */}
              {project.short_overview && (
                <div className="mt-lg rounded-md border border-gray-200 p-xl">
                  <h3 className="text-base font-semibold text-gray-900">Project Overview</h3>
                  <p className="mt-md text-sm leading-relaxed text-gray-600">{project.short_overview}</p>
                </div>
              )}

              {/* QR Code */}
              {project.qr_code && project.qr_code.length > 0 && (
                <div className="mt-lg flex items-center gap-lg rounded-md border border-gray-200 p-xl">
                  <div>
                    <p className="text-caption text-gray-500">RERA QR Code</p>
                    <div className="mt-sm flex gap-md">
                      {project.qr_code.map((qr, i) => (
                        <img key={i} src={qr} alt="RERA QR Code" className="h-[80px] w-[80px] rounded border border-gray-200" />
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {/* Offers — horizontal */}
              {project.offers && project.offers.length > 0 && (
                <div className="mt-lg rounded-md border border-accent/20 bg-accent/5 p-xl">
                  <h3 className="text-base font-semibold text-gray-900">Current Offers</h3>
                  <div className="mt-md grid grid-cols-1 gap-sm sm:grid-cols-2 lg:grid-cols-3">
                    {project.offers.map((offer, i) => (
                      <div key={i} className="flex items-start gap-md rounded-md border border-accent/20 bg-white p-md">
                        <span className="mt-[2px] text-accent">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        </span>
                        <span className="text-sm text-gray-700">{offer}</span>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </section>

            {/* Section: FAQ */}
            {faqs.length > 0 && (
              <section id="faq" className="mt-3xl border-t border-gray-100 pt-3xl">
                <h2 className="text-h2 text-gray-900">Frequently Asked Questions</h2>
                <div className="mt-xl flex flex-col gap-lg">
                  {faqs.map((faq, index) => (
                    <details
                      key={index}
                      className="group rounded-md border border-gray-200 bg-white"
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
              </section>
            )}

            {/* Section: Why Buy from LeadMAAXX */}
            <section className="mt-3xl border-t border-gray-100 pt-3xl">
              <h2 className="text-h2 text-gray-900">Why Buy from LeadMAAXX?</h2>
              <div className="mt-xl grid grid-cols-2 gap-md sm:grid-cols-4">
                <div className="flex flex-col items-center gap-sm rounded-lg border border-gray-200 bg-white p-lg text-center">
                  <div className="flex h-[40px] w-[40px] items-center justify-center rounded-full bg-success/10">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-success" aria-hidden="true">
                      <path d="M12 2v20" />
                      <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                  </div>
                  <p className="text-sm font-semibold text-gray-900">Lowest Price</p>
                  <p className="text-caption text-gray-500">Guaranteed best deal</p>
                </div>
                <div className="flex flex-col items-center gap-sm rounded-lg border border-gray-200 bg-white p-lg text-center">
                  <div className="flex h-[40px] w-[40px] items-center justify-center rounded-full bg-brand-primary/10">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-brand-primary" aria-hidden="true">
                      <circle cx="12" cy="12" r="10" />
                      <path d="m4.9 4.9 14.2 14.2" />
                      <path d="M12 7v5" />
                      <path d="M9.5 15h5a2.5 2.5 0 0 0 0-5" />
                    </svg>
                  </div>
                  <p className="text-sm font-semibold text-gray-900">No Brokerage</p>
                  <p className="text-caption text-gray-500">Zero commission</p>
                </div>
                <div className="flex flex-col items-center gap-sm rounded-lg border border-gray-200 bg-white p-lg text-center">
                  <div className="flex h-[40px] w-[40px] items-center justify-center rounded-full bg-accent/10">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-accent-dark" aria-hidden="true">
                      <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 10l-2-4H7L5 10l-2.5 1.1C1.7 11.3 1 12.1 1 13v3c0 .6.4 1 1 1h2" />
                      <circle cx="7" cy="17" r="2" />
                      <circle cx="17" cy="17" r="2" />
                    </svg>
                  </div>
                  <p className="text-sm font-semibold text-gray-900">Free Site Visit</p>
                  <p className="text-caption text-gray-500">With cab pickup</p>
                </div>
                <div className="flex flex-col items-center gap-sm rounded-lg border border-gray-200 bg-white p-lg text-center">
                  <div className="flex h-[40px] w-[40px] items-center justify-center rounded-full bg-brand-primary/10">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-brand-primary" aria-hidden="true">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                      <polyline points="14 2 14 8 20 8" />
                      <line x1="16" x2="8" y1="13" y2="13" />
                      <line x1="16" x2="8" y1="17" y2="17" />
                      <polyline points="10 9 9 9 8 9" />
                    </svg>
                  </div>
                  <p className="text-sm font-semibold text-gray-900">Unbiased Reports</p>
                  <p className="text-caption text-gray-500">AI-powered analysis</p>
                </div>
              </div>
            </section>

            {/* Section: Similar Projects — only if we have data */}
            {similarProjects.length > 0 && (
              <section className="mt-3xl border-t border-gray-100 pt-3xl">
                <div className="flex items-center justify-between">
                  <h2 className="text-h2 text-gray-900">Similar Projects Nearby</h2>
                  <a
                    href={`/navi-mumbai/${location}`}
                    className="text-sm font-medium text-brand-primary hover:text-brand-primary-dark"
                  >
                    View All
                  </a>
                </div>

                <div className="mt-xl grid grid-cols-1 gap-md sm:grid-cols-2 lg:grid-cols-4">
                  {similarProjects.map((p) => (
                    <a
                      key={p.slug}
                      href={p.slug}
                      className="group overflow-hidden rounded-lg border border-gray-200 bg-white no-underline transition-shadow hover:shadow-md hover:no-underline"
                    >
                      {/* Image */}
                      <div className="relative h-[140px] bg-gray-100">
                        <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                        <div className="absolute left-sm top-sm rounded-full bg-white/90 px-sm py-[2px] text-[10px] font-semibold text-gray-700 backdrop-blur-sm">
                          {p.developer}
                        </div>
                        {p.fitScore > 0 && (
                          <div className="absolute right-sm top-sm flex h-[28px] w-[28px] items-center justify-center rounded-full bg-white/90 backdrop-blur-sm">
                            <span className="text-caption font-bold text-brand-primary">{p.fitScore}</span>
                          </div>
                        )}
                      </div>

                      {/* Content */}
                      <div className="p-md">
                        <h3 className="text-sm font-semibold text-gray-900 group-hover:text-brand-primary">
                          {p.title}
                        </h3>
                        <p className="mt-[2px] text-caption text-gray-500">{p.location}</p>
                        <div className="mt-sm flex items-center justify-between">
                          <p className="text-sm font-semibold text-gray-900">
                            {formatPrice(p.priceMin)} - {formatPrice(p.priceMax)}
                          </p>
                        </div>
                        <p className="mt-[2px] text-caption text-gray-500">{p.configs}</p>
                      </div>
                    </a>
                  ))}
                </div>
              </section>
            )}

          </div>

          {/* Desktop sidebar — sticky, hidden on mobile */}
          <ProjectSidebar
            project={project}
            propertyType={category}
            ctaLabels={ctaLabels}
            className="sticky top-[100px] hidden w-[340px] shrink-0 self-start lg:flex"
          />
        </div>
      </Container>

      {/* Mobile sticky CTA bar */}
      <MobileStickyBar projectTitle={project.title} projectUrl={fullUrl} ctaLabels={ctaLabels} />
    </LeadFormWrapper>
  );
}
