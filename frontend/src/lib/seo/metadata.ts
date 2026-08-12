import type { Metadata } from 'next';
import type { Project } from '@/lib/types/project';
import type { Location } from '@/lib/types/location';
import type { Developer } from '@/lib/types/developer';
import { formatPriceRange } from '@/lib/utils/format-price';

const SITE_NAME = 'LeadMAAXX';
const BASE_URL = process.env.NEXT_PUBLIC_SITE_URL || 'https://leadmaaxx.com';

/**
 * Generate metadata for the homepage.
 */
export function homeMetadata(): Metadata {
  return {
    title: 'LeadMAAXX — Find the 10 Best-Fit Projects for You',
    description:
      "India's first AI-powered real estate platform. Our scoring engine analyses 150+ projects across 20 categories to find the 10 best-fit matches for your lifestyle, budget, and priorities.",
    openGraph: {
      title: 'LeadMAAXX — Find the 10 Best-Fit Projects for You',
      description:
        "India's first AI-powered real estate platform that scores every project across 20 categories.",
      url: BASE_URL,
      siteName: SITE_NAME,
      type: 'website',
      locale: 'en_IN',
    },
    twitter: {
      card: 'summary_large_image',
      title: 'LeadMAAXX — Find the 10 Best-Fit Projects for You',
    },
    robots: {
      index: true,
      follow: true,
    },
    alternates: {
      canonical: BASE_URL,
    },
  };
}

/**
 * Generate metadata for a project detail page.
 */
export function projectMetadata(project: Project): Metadata {
  const priceRange = formatPriceRange(project.price_min, project.price_max);
  const title = `${project.title}, ${project.location} — Price, Reviews, Pros & Cons — ${SITE_NAME}`;
  const description = `${project.title} by ${project.developer} in ${project.location}. Price: ${priceRange}. Construction: ${project.construction_stage}. RERA: ${project.rera_number}. Read AI-powered analysis, pros & cons, and detailed scoring.`;
  const url = `${BASE_URL}${project.permalink}`;

  return {
    title,
    description,
    openGraph: {
      title,
      description,
      url,
      siteName: SITE_NAME,
      type: 'website',
      locale: 'en_IN',
      images: project.thumbnail
        ? [
            {
              url: project.thumbnail,
              width: 1200,
              height: 630,
              alt: project.title,
            },
          ]
        : undefined,
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description,
      images: project.thumbnail ? [project.thumbnail] : undefined,
    },
    robots: {
      index: true,
      follow: true,
    },
    alternates: {
      canonical: url,
    },
  };
}

/**
 * Generate metadata for a location listing page.
 */
export function locationMetadata(location: Location): Metadata {
  const title = `Projects in ${location.title}, ${location.city} — Prices, Scores & Reviews — ${SITE_NAME}`;
  const description = `Explore ${location.project_count} projects in ${location.title}, ${location.city}. Average price: Rs ${location.avg_price_psf}/sqft. Livability score: ${location.livability_score}/100. AI-scored rankings, pros & cons for every project.`;
  const url = `${BASE_URL}/navi-mumbai/${location.slug}`;

  return {
    title,
    description,
    openGraph: {
      title,
      description,
      url,
      siteName: SITE_NAME,
      type: 'website',
      locale: 'en_IN',
      images: location.thumbnail
        ? [
            {
              url: location.thumbnail,
              width: 1200,
              height: 630,
              alt: `Projects in ${location.title}`,
            },
          ]
        : undefined,
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description,
    },
    robots: {
      index: true,
      follow: true,
    },
    alternates: {
      canonical: url,
    },
  };
}

/**
 * Generate metadata for a developer profile page.
 */
export function developerMetadata(developer: Developer): Metadata {
  const title = `${developer.title} — Projects, Reviews & Ratings — ${SITE_NAME}`;
  const description = `${developer.title} developer profile. ${developer.total_projects_completed} projects completed, ${developer.total_projects_ongoing} ongoing. Customer rating: ${developer.customer_rating}/5. RERA compliance: ${developer.rera_compliance_rate}%. View all projects with AI-powered scoring.`;
  const url = `${BASE_URL}/developers/${developer.slug}`;

  return {
    title,
    description,
    openGraph: {
      title,
      description,
      url,
      siteName: SITE_NAME,
      type: 'website',
      locale: 'en_IN',
      images: developer.logo
        ? [
            {
              url: developer.logo,
              width: 400,
              height: 400,
              alt: developer.title,
            },
          ]
        : undefined,
    },
    twitter: {
      card: 'summary',
      title,
      description,
    },
    robots: {
      index: true,
      follow: true,
    },
    alternates: {
      canonical: url,
    },
  };
}
