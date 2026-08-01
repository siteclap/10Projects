import type { MetadataRoute } from 'next';

const BASE_URL =
  process.env.NEXT_PUBLIC_SITE_URL || 'https://10projects.com';

/**
 * Mock slugs for dynamic pages.
 *
 * In production these would be fetched from the WordPress REST API
 * (e.g., /wp-json/tp/v1/projects, /wp-json/tp/v1/locations, etc.).
 * Replace the arrays below with actual API calls when the backend is live.
 */

const MOCK_LOCATIONS = [
  'kharghar',
  'panvel',
  'ulwe',
  'vashi',
  'airoli',
  'ghansoli',
  'nerul',
  'belapur',
  'kopar-khairane',
  'sanpada',
  'seawoods',
  'taloja',
  'kamothe',
  'kalamboli',
  'dronagiri',
];

const MOCK_PROJECTS: Array<{ location: string; slug: string }> = [
  { location: 'kharghar', slug: 'lodha-palava-crown' },
  { location: 'kharghar', slug: 'paradise-sai-world-empire' },
  { location: 'panvel', slug: 'balaji-symphony' },
  { location: 'panvel', slug: 'arihant-aspire' },
  { location: 'ulwe', slug: 'jerai-elysium' },
  { location: 'vashi', slug: 'lt-seawoods-residences' },
  { location: 'airoli', slug: 'godrej-vihaa' },
];

const MOCK_DEVELOPERS = [
  'lodha-group',
  'godrej-properties',
  'lt-realty',
  'paradise-group',
  'arihant-group',
  'jerai-group',
  'balaji-group',
  'haware-group',
];

const MOCK_GUIDES = [
  'first-time-home-buyer-navi-mumbai',
  'rera-explained',
  'home-loan-eligibility',
  'investment-vs-end-use',
  'hidden-costs-buying-flat',
];

export default function sitemap(): MetadataRoute.Sitemap {
  const now = new Date();

  /* ---------- Static pages ---------- */

  const staticPages: MetadataRoute.Sitemap = [
    {
      url: BASE_URL,
      lastModified: now,
      changeFrequency: 'daily',
      priority: 1.0,
    },
    {
      url: `${BASE_URL}/navi-mumbai`,
      lastModified: now,
      changeFrequency: 'weekly',
      priority: 0.9,
    },
    {
      url: `${BASE_URL}/projects`,
      lastModified: now,
      changeFrequency: 'daily',
      priority: 0.9,
    },
    {
      url: `${BASE_URL}/developers`,
      lastModified: now,
      changeFrequency: 'weekly',
      priority: 0.7,
    },
    {
      url: `${BASE_URL}/guides`,
      lastModified: now,
      changeFrequency: 'weekly',
      priority: 0.6,
    },
    {
      url: `${BASE_URL}/methodology`,
      lastModified: now,
      changeFrequency: 'monthly',
      priority: 0.5,
    },
  ];

  /* ---------- Location pages ---------- */

  const locationPages: MetadataRoute.Sitemap = MOCK_LOCATIONS.map(
    (location) => ({
      url: `${BASE_URL}/navi-mumbai/${location}`,
      lastModified: now,
      changeFrequency: 'weekly' as const,
      priority: 0.8,
    })
  );

  /* ---------- Project pages ---------- */

  const projectPages: MetadataRoute.Sitemap = MOCK_PROJECTS.map(
    ({ location, slug }) => ({
      url: `${BASE_URL}/navi-mumbai/${location}/${slug}`,
      lastModified: now,
      changeFrequency: 'daily' as const,
      priority: 0.9,
    })
  );

  /* ---------- Developer pages ---------- */

  const developerPages: MetadataRoute.Sitemap = MOCK_DEVELOPERS.map(
    (slug) => ({
      url: `${BASE_URL}/developers/${slug}`,
      lastModified: now,
      changeFrequency: 'weekly' as const,
      priority: 0.7,
    })
  );

  /* ---------- Guide pages ---------- */

  const guidePages: MetadataRoute.Sitemap = MOCK_GUIDES.map((slug) => ({
    url: `${BASE_URL}/guides/${slug}`,
    lastModified: now,
    changeFrequency: 'monthly' as const,
    priority: 0.6,
  }));

  return [
    ...staticPages,
    ...projectPages,
    ...locationPages,
    ...developerPages,
    ...guidePages,
  ];
}
