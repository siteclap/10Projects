import type { Project } from '@/lib/types/project';
import type { Location } from '@/lib/types/location';

const BASE_URL = process.env.NEXT_PUBLIC_SITE_URL || 'https://leadmaaxx.com';

interface BreadcrumbItem {
  name: string;
  url: string;
}

interface FaqItem {
  question: string;
  answer: string;
}

/**
 * Generate RealEstateListing + AggregateOffer JSON-LD for a project.
 */
export function realEstateListingJsonLd(project: Project): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'RealEstateListing',
    name: project.title,
    url: `${BASE_URL}${project.permalink}`,
    description: `${project.title} by ${project.developer} in ${project.location}. ${project.construction_stage}.`,
    image: project.thumbnail || undefined,
    datePosted: new Date().toISOString().split('T')[0],
    offers: {
      '@type': 'AggregateOffer',
      priceCurrency: 'INR',
      lowPrice: project.price_min > 0 ? project.price_min : undefined,
      highPrice: project.price_max > 0 ? project.price_max : undefined,
      offerCount: project.configurations?.length ?? 0,
    },
    geo: project.latitude && project.longitude
      ? {
          '@type': 'GeoCoordinates',
          latitude: project.latitude,
          longitude: project.longitude,
        }
      : undefined,
    address: {
      '@type': 'PostalAddress',
      addressLocality: project.location,
      addressRegion: 'Maharashtra',
      addressCountry: 'IN',
    },
  };
}

/**
 * Generate Organization JSON-LD for LeadMAAXX.
 */
export function organizationJsonLd(): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'LeadMAAXX',
    url: BASE_URL,
    logo: `${BASE_URL}/logo.png`,
    description:
      "India's first AI-powered real estate scoring platform. We analyse 150+ projects across 20 categories to find your 10 best-fit matches.",
    sameAs: [],
    contactPoint: {
      '@type': 'ContactPoint',
      contactType: 'customer service',
      availableLanguage: ['English', 'Hindi'],
    },
  };
}

/**
 * Generate BreadcrumbList JSON-LD.
 */
export function breadcrumbJsonLd(items: BreadcrumbItem[]): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      item: item.url.startsWith('http') ? item.url : `${BASE_URL}${item.url}`,
    })),
  };
}

/**
 * Generate FAQPage JSON-LD.
 */
export function faqJsonLd(faqs: FaqItem[]): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: faqs.map((faq) => ({
      '@type': 'Question',
      name: faq.question,
      acceptedAnswer: {
        '@type': 'Answer',
        text: faq.answer,
      },
    })),
  };
}

/**
 * Generate Place JSON-LD for a location page.
 */
export function placeJsonLd(location: Location): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'Place',
    name: location.title,
    description: location.description,
    url: `${BASE_URL}/navi-mumbai/${location.slug}`,
    address: {
      '@type': 'PostalAddress',
      addressLocality: location.title,
      addressRegion: location.city,
      addressCountry: 'IN',
    },
    geo:
      location.latitude && location.longitude
        ? {
            '@type': 'GeoCoordinates',
            latitude: location.latitude,
            longitude: location.longitude,
          }
        : undefined,
  };
}
