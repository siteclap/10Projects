import type { Metadata } from 'next';
import { Header } from '@/components/layout/Header';
import { Footer } from '@/components/layout/Footer';
import { Hero } from '@/components/homepage/Hero';
import { HowItWorks } from '@/components/homepage/HowItWorks';
import { WhyUs } from '@/components/homepage/WhyUs';
import { BlogSection } from '@/components/homepage/BlogSection';
import { TrustBar } from '@/components/homepage/TrustBar';
import { FinalCta } from '@/components/homepage/FinalCta';
import { ProjectCard } from '@/components/project/ProjectCard';
import { ProjectCarousel } from '@/components/project/ProjectCarousel';
import { LocationGrid } from '@/components/location/LocationGrid';
import { DeveloperLogos } from '@/components/developer/DeveloperLogos';
import { ROUTES } from '@/lib/constants/routes';
import { wpFetchSearchConfig } from '@/lib/wp-api';
import type { ProjectCard as ProjectCardType } from '@/lib/types/project';
import type { LocationCard as LocationCardType } from '@/lib/types/location';
import type { DeveloperCard } from '@/lib/types/developer';

export const metadata: Metadata = {
  title: '10Projects — Find the 10 Best-Fit Projects for You',
  description:
    "Search 150+ verified projects in Navi Mumbai by name, location, or developer. Compare RERA-verified prices, floor plans, and book free site visits. Zero brokerage, no spam.",
  alternates: {
    canonical: '/',
  },
};

/* ---------- Mock Data ---------- */

const featuredProjects: ProjectCardType[] = [
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
      {
        config_type: '2 BHK',
        carpet_area_sqft: 650,
        base_price: 8500000,
        total_price: 9200000,
        inventory_total: 120,
        inventory_available: 45,
      },
      {
        config_type: '3 BHK',
        carpet_area_sqft: 950,
        base_price: 12500000,
        total_price: 13800000,
        inventory_total: 80,
        inventory_available: 22,
      },
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
      {
        config_type: '2 BHK',
        carpet_area_sqft: 720,
        base_price: 9800000,
        total_price: 10500000,
        inventory_total: 200,
        inventory_available: 15,
      },
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
      {
        config_type: '1 BHK',
        carpet_area_sqft: 420,
        base_price: 4200000,
        total_price: 4800000,
        inventory_total: 150,
        inventory_available: 88,
      },
      {
        config_type: '2 BHK',
        carpet_area_sqft: 630,
        base_price: 6800000,
        total_price: 7500000,
        inventory_total: 100,
        inventory_available: 52,
      },
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
      {
        config_type: '1 BHK',
        carpet_area_sqft: 390,
        base_price: 3800000,
        total_price: 4200000,
        inventory_total: 180,
        inventory_available: 95,
      },
      {
        config_type: '2 BHK',
        carpet_area_sqft: 580,
        base_price: 5600000,
        total_price: 6200000,
        inventory_total: 120,
        inventory_available: 67,
      },
    ],
    price_min: 4200000,
    price_max: 6200000,
    fit_score: 85,
  },
];

const fastSellingProjects: ProjectCardType[] = [
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
      {
        config_type: '2 BHK',
        carpet_area_sqft: 680,
        base_price: 7200000,
        total_price: 7900000,
        inventory_total: 90,
        inventory_available: 8,
      },
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
    thumbnail: 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&q=80',
    developer: 'L&T Realty',
    location: 'Seawoods',
    construction_stage: 'Ready to Move',
    expected_possession: '',
    rera_number: 'P52000032876',
    configurations: [
      {
        config_type: '2 BHK',
        carpet_area_sqft: 780,
        base_price: 14500000,
        total_price: 15800000,
        inventory_total: 60,
        inventory_available: 5,
      },
      {
        config_type: '3 BHK',
        carpet_area_sqft: 1100,
        base_price: 21000000,
        total_price: 23500000,
        inventory_total: 40,
        inventory_available: 3,
      },
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
    thumbnail: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&q=80',
    developer: 'Godrej Properties',
    location: 'Airoli',
    construction_stage: 'Under Construction',
    expected_possession: 'Dec 2027',
    rera_number: 'P52000053447',
    configurations: [
      {
        config_type: '2 BHK',
        carpet_area_sqft: 700,
        base_price: 11200000,
        total_price: 12500000,
        inventory_total: 110,
        inventory_available: 12,
      },
    ],
    price_min: 12500000,
    price_max: 12500000,
    fit_score: 87,
  },
];

const popularLocations: LocationCardType[] = [
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
];

const developerPartners: DeveloperCard[] = [
  { id: 1, title: 'Lodha Group', slug: 'lodha-group', logo: null, tier: 'tier_1', total_projects_completed: 120, customer_rating: 4.2 },
  { id: 2, title: 'Godrej Properties', slug: 'godrej-properties', logo: null, tier: 'tier_1', total_projects_completed: 95, customer_rating: 4.3 },
  { id: 3, title: 'L&T Realty', slug: 'lt-realty', logo: null, tier: 'tier_1', total_projects_completed: 45, customer_rating: 4.4 },
  { id: 4, title: 'Paradise Group', slug: 'paradise-group', logo: null, tier: 'tier_2', total_projects_completed: 30, customer_rating: 4.0 },
  { id: 5, title: 'Arihant Group', slug: 'arihant-group', logo: null, tier: 'tier_2', total_projects_completed: 55, customer_rating: 3.9 },
  { id: 6, title: 'JERAI Group', slug: 'jerai-group', logo: null, tier: 'tier_2', total_projects_completed: 18, customer_rating: 3.8 },
  { id: 7, title: 'Balaji Group', slug: 'balaji-group', logo: null, tier: 'tier_2', total_projects_completed: 22, customer_rating: 3.7 },
  { id: 8, title: 'Haware Group', slug: 'haware-group', logo: null, tier: 'tier_2', total_projects_completed: 65, customer_rating: 3.6 },
];

/* ---------- Page Component ---------- */

export default async function HomePage() {
  const activeCategories = await wpFetchSearchConfig();

  return (
    <>
      <Header />

      <main className="flex-1">
        {/* 1. Hero */}
        <Hero activeCategories={activeCategories} />

        {/* 2. Featured Projects carousel */}
        <ProjectCarousel
          title="Featured Projects"
          subtitle="Top-rated projects in Navi Mumbai"
          seeAllHref={ROUTES.PROJECTS}
          seeAllLabel="View All Projects"
        >
          {featuredProjects.map((project) => (
            <ProjectCard
              key={project.id}
              project={project}
            />
          ))}
        </ProjectCarousel>

        {/* 3. Fast Selling carousel */}
        <ProjectCarousel
          title="Fast Selling"
          subtitle="These projects are selling out quickly"
          seeAllHref={ROUTES.PROJECTS}
          seeAllLabel="View All"
          className="bg-section-alt"
        >
          {fastSellingProjects.map((project) => (
            <ProjectCard key={project.id} project={project} />
          ))}
        </ProjectCarousel>

        {/* 4. How It Works */}
        <HowItWorks />

        {/* 5. Why 10Projects */}
        <WhyUs />

        {/* 6. Popular Locations */}
        <LocationGrid
          locations={popularLocations}
          title="Popular Locations in Navi Mumbai"
          subtitle="Explore projects by your preferred neighbourhood"
          seeAllHref={ROUTES.CITY}
          className="bg-section-alt"
        />

        {/* 7. Developer Partners */}
        <DeveloperLogos developers={developerPartners} />

        {/* 8. Blog */}
        <BlogSection />

        {/* 9. Trust Bar */}
        <TrustBar />

        {/* 10. Final CTA */}
        <FinalCta />
      </main>

      <Footer />
    </>
  );
}
