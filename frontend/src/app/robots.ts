import type { MetadataRoute } from 'next';

const BASE_URL =
  process.env.NEXT_PUBLIC_SITE_URL || 'https://10projects.com';

export default function robots(): MetadataRoute.Robots {
  return {
    rules: {
      userAgent: '*',
      allow: '/',
      disallow: ['/start', '/results', '/compare', '/dashboard', '/api'],
    },
    sitemap: `${BASE_URL}/sitemap.xml`,
  };
}
