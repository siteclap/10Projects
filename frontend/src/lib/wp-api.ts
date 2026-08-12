/**
 * WordPress REST API client for build-time data fetching.
 *
 * During `next build`, these functions fetch project/location data
 * from the WordPress REST API and map it to the frontend types.
 */

import type { Project, ProjectCard, ProjectConfiguration } from '@/lib/types/project';
import type { SiteSettings } from '@/lib/types/site-settings';

const WP_API_BASE =
  process.env.WP_API_URL ||
  process.env.NEXT_PUBLIC_WP_API_URL ||
  'https://leadmax.siteclap.com/wp-json/tenprojects/v1';

// ---------- Raw WP API response types ----------

interface WpProjectCard {
  id: number;
  title: string;
  slug: string;
  thumbnail: string | null;
  permalink: string;
  location: Array<{ slug: string; name: string }>;
  developer_name: string | null;
  price_range: { min: number | null; max: number | null };
  configurations: Array<{ slug: string; name: string }>;
  construction_stage: string | null;
  expected_possession: string | null;
  rera_number: string | null;
  verified: boolean;
  sponsored: boolean;
}

interface WpProjectDetail {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  content: string;
  thumbnail: string | null;
  permalink: string;
  rera_number: string | null;
  construction_stage: string | null;
  expected_possession: string | null;
  price_display_min: number | null;
  price_display_max: number | null;
  total_towers: number | null;
  total_floors: number | null;
  total_units: number | null;
  address: string | null;
  address_pin: string | null;
  location_advantage_1: string | null;
  location_advantage_2: string | null;
  location_brief: string | null;
  latitude: number | null;
  longitude: number | null;
  railway_distance_km: number | null;
  metro_distance_km: number | null;
  highway_distance_km: number | null;
  airport_distance_km: number | null;
  school_distance_km: number | null;
  hospital_distance_km: number | null;
  mall_distance_km: number | null;
  employment_hub_km: number | null;
  highlights: string[];
  pros: string[];
  cons: string[];
  offers: string[];
  phone: string | null;
  email: string | null;
  sales_office_address: string | null;
  verified: boolean;
  status: string | null;
  sponsored: boolean;
  sponsor_label: string | null;
  // About Developer / Project Overview.
  developer_name: string | null;
  project_location: string | null;
  land_parcel: string | null;
  floors_display: string | null;
  qr_code: string[];
  short_overview: string | null;
  google_review_rating: string | null;
  available_configs_text: string | null;
  // Images & galleries.
  gallery_images: string[];
  banner_desktop_images: string[];
  banner_mobile_images: string[];
  developer_logo: string | null;
  developer: {
    id: number;
    name: string;
    logo: string | null;
  } | null;
  configurations: WpConfiguration[];
  taxonomies: {
    city: Array<{ slug: string; name: string }>;
    location: Array<{ slug: string; name: string }>;
    amenities: Array<{ slug: string; name: string }>;
    [key: string]: Array<{ slug: string; name: string }>;
  };
}

interface WpConfiguration {
  id: number;
  configuration: string;
  carpet_area_min: number;
  carpet_area_max: number;
  price_min: number;
  price_max: number;
  price_per_sqft: number;
  unit_count: number;
  inventory_status: string;
  [key: string]: unknown;
}

interface WpApiResponse<T> {
  success: boolean;
  data: T;
}

// ---------- Fetch helpers ----------

async function wpFetch<T>(endpoint: string): Promise<T | null> {
  try {
    const url = `${WP_API_BASE}${endpoint}`;
    const res = await fetch(url, { cache: 'no-store' });
    if (!res.ok) return null;
    const json: WpApiResponse<T> = await res.json();
    return json.success ? json.data : null;
  } catch {
    return null;
  }
}

// ---------- Public API ----------

/**
 * Fetch all published projects, optionally filtered by location slug.
 */
export async function wpFetchProjects(locationSlug?: string): Promise<ProjectCard[]> {
  const params = new URLSearchParams({ per_page: '100' });
  if (locationSlug) params.set('location', locationSlug);

  const data = await wpFetch<WpProjectCard[]>(`/projects?${params}`);
  if (!data) return [];

  return data.map(mapWpCardToProjectCard);
}

/**
 * Fetch a single project by slug.
 */
export async function wpFetchProject(slug: string): Promise<Project | null> {
  const data = await wpFetch<WpProjectDetail>(`/projects/by-slug/${slug}`);
  if (!data) return null;

  return mapWpDetailToProject(data);
}

/**
 * Fetch all project slugs with their location slugs (for generateStaticParams).
 */
export async function wpFetchAllProjectSlugs(): Promise<Array<{ slug: string; location_slug: string }>> {
  const cards = await wpFetchProjects();
  return cards.map((c) => ({
    slug: c.slug,
    location_slug: typeof c.location === 'string' ? c.location : '',
  }));
}

/**
 * Fetch all location taxonomy terms.
 */
export async function wpFetchLocations(): Promise<Array<{ slug: string; name: string; count: number }>> {
  try {
    const url = `${WP_API_BASE.replace('/tenprojects/v1', '')}/wp/v2/tp_location_area?per_page=100`;
    const res = await fetch(url);
    if (!res.ok) return [];
    const terms: Array<{ slug: string; name: string; count: number }> = await res.json();
    return terms;
  } catch {
    return [];
  }
}

/**
 * Fetch search configuration (active categories).
 */
export async function wpFetchSearchConfig(): Promise<string[]> {
  const data = await wpFetch<{ active_categories: string[] }>('/search/config');
  return data?.active_categories || ['buy'];
}

/**
 * Fetch site-wide brand settings (logos, colors, banners).
 */
export async function wpFetchSiteSettings(): Promise<SiteSettings> {
  const data = await wpFetch<SiteSettings>('/site-settings');
  return data || {
    site_name: '10Projects',
    logo_light: '',
    logo_dark: '',
    favicon: '',
    hero_desktop: '',
    hero_mobile: '',
    phone: '',
    email: '',
    address: '',
    rera_agent: '',
    rera_legal_name: '',
    about: '',
    colors: {
      primary: '#4B1CB0',
      primary_dark: '#3B1490',
      accent: '#F59E0B',
      hero_bg: '#111827',
    },
    social: {
      facebook: '',
      instagram: '',
      linkedin: '',
      youtube: '',
      twitter: '',
      whatsapp: '',
    },
  };
}

// ---------- Mappers ----------

function mapWpCardToProjectCard(wp: WpProjectCard): ProjectCard {
  const locationName = wp.location?.[0]?.name || '';
  const locationSlug = wp.location?.[0]?.slug || '';

  return {
    id: wp.id,
    title: wp.title,
    slug: wp.slug,
    permalink: `/navi-mumbai/${locationSlug}/${wp.slug}/`,
    thumbnail: wp.thumbnail || '/placeholder-project.jpg',
    developer: wp.developer_name || '',
    location: locationName,
    construction_stage: wp.construction_stage || 'Under Construction',
    expected_possession: wp.expected_possession || '',
    rera_number: wp.rera_number || '',
    configurations: wp.configurations.map((c) => ({
      config_type: c.name,
      carpet_area_sqft: 0,
      base_price: 0,
      total_price: 0,
      inventory_total: 0,
      inventory_available: 0,
    })),
    price_min: wp.price_range?.min || 0,
    price_max: wp.price_range?.max || 0,
  };
}

function mapWpDetailToProject(wp: WpProjectDetail): Project {
  const locationName = wp.taxonomies?.location?.[0]?.name || '';
  const locationSlug = wp.taxonomies?.location?.[0]?.slug || '';
  const amenities = wp.taxonomies?.amenities?.map((a) => a.name) || [];

  const configurations: ProjectConfiguration[] = wp.configurations.map((c) => ({
    config_type: c.configuration,
    carpet_area_sqft: c.carpet_area_min,
    base_price: c.price_per_sqft,
    total_price: c.price_min,
    inventory_total: c.unit_count,
    inventory_available: c.inventory_status === 'sold_out' ? 0 : c.unit_count,
  }));

  return {
    id: wp.id,
    title: wp.title,
    slug: wp.slug,
    permalink: `/navi-mumbai/${locationSlug}/${wp.slug}/`,
    thumbnail: wp.thumbnail || '/placeholder-project.jpg',
    developer: wp.developer?.name || '',
    location: locationName,
    construction_stage: wp.construction_stage || 'Under Construction',
    expected_possession: wp.expected_possession || '',
    rera_number: wp.rera_number || '',
    configurations,
    price_min: wp.price_display_min || 0,
    price_max: wp.price_display_max || 0,
    railway_distance_km: wp.railway_distance_km || undefined,
    latitude: wp.latitude || undefined,
    longitude: wp.longitude || undefined,
    description: wp.content || wp.excerpt || undefined,
    highlights: wp.highlights?.join('\n') || undefined,
    amenities,
    pros: wp.pros || [],
    cons: wp.cons || [],
    floors: wp.total_floors ? String(wp.total_floors) : undefined,
    land_parcel: wp.land_parcel || wp.address || undefined,
    // Gallery & media.
    gallery_images: wp.gallery_images || [],
    banner_desktop_images: wp.banner_desktop_images || [],
    banner_mobile_images: wp.banner_mobile_images || [],
    developer_logo: wp.developer_logo || wp.developer?.logo || undefined,
    // Contact & extras.
    phone: wp.phone || undefined,
    email: wp.email || undefined,
    verified: wp.verified || false,
    status: wp.status || undefined,
    offers: wp.offers || [],
    // About Developer / Project Overview.
    developer_name: wp.developer_name || wp.developer?.name || undefined,
    project_location: wp.project_location || undefined,
    floors_display: wp.floors_display || undefined,
    qr_code: wp.qr_code || [],
    short_overview: wp.short_overview || undefined,
    google_review_rating: wp.google_review_rating || undefined,
    available_configs_text: wp.available_configs_text || undefined,
    // Location details.
    address_pin: wp.address_pin || undefined,
    location_advantage_1: wp.location_advantage_1 || undefined,
    location_advantage_2: wp.location_advantage_2 || undefined,
    location_brief: wp.location_brief || undefined,
  };
}
