import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';

interface BlogPost {
  title: string;
  excerpt: string;
  category: string;
  readTime: string;
  date: string;
  slug: string;
  image: string;
}

const BLOG_POSTS: BlogPost[] = [
  {
    title: 'RERA Registration: How to Check if Your Project is RERA Verified',
    excerpt: 'A step-by-step guide to verify RERA registration of any real estate project in Maharashtra before investing.',
    category: 'Buyer Guide',
    readTime: '5 min read',
    date: 'Jul 28, 2026',
    slug: '/blog/how-to-check-rera-registration',
    image: 'https://images.unsplash.com/photo-1554469384-e58fac16e23a?w=600&h=340&fit=crop',
  },
  {
    title: 'Navi Mumbai Airport: Impact on Property Prices in 2026',
    excerpt: 'How the upcoming Navi Mumbai International Airport is reshaping property values across Panvel, Ulwe, and Kharghar.',
    category: 'Market Insights',
    readTime: '7 min read',
    date: 'Jul 22, 2026',
    slug: '/blog/navi-mumbai-airport-property-impact',
    image: 'https://images.unsplash.com/photo-1436491865332-7a61a109db56?w=600&h=340&fit=crop',
  },
  {
    title: '1 BHK vs 2 BHK: Which is the Better Investment in Navi Mumbai?',
    excerpt: 'Comparing rental yields, appreciation potential, and lifestyle factors to help you pick the right configuration.',
    category: 'Investment',
    readTime: '6 min read',
    date: 'Jul 15, 2026',
    slug: '/blog/1bhk-vs-2bhk-navi-mumbai',
    image: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=600&h=340&fit=crop',
  },
];

export function BlogSection() {
  return (
    <Section variant="white">
      <Container>
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-h2 text-gray-900">From the Blog</h2>
            <p className="mt-sm text-base text-gray-500">
              Expert insights on Navi Mumbai real estate
            </p>
          </div>
          <a
            href="/blog"
            className="hidden items-center gap-xs text-sm font-medium text-brand-primary hover:text-brand-primary-dark sm:inline-flex"
          >
            View All Articles
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="m9 18 6-6-6-6" />
            </svg>
          </a>
        </div>

        <div className="mt-xl grid grid-cols-1 gap-lg sm:grid-cols-2 lg:grid-cols-3">
          {BLOG_POSTS.map((post) => (
            <a
              key={post.slug}
              href={post.slug}
              className="group overflow-hidden rounded-lg border border-gray-200 bg-white no-underline transition-shadow hover:shadow-md hover:no-underline"
            >
              {/* Image */}
              <div className="relative h-[180px] overflow-hidden bg-gray-100">
                <img
                  src={post.image}
                  alt={post.title}
                  className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                  loading="lazy"
                />
                <span className="absolute left-md top-md rounded-full bg-white/90 px-md py-xs text-caption font-semibold text-brand-primary backdrop-blur-sm">
                  {post.category}
                </span>
              </div>

              {/* Content */}
              <div className="p-xl">
                <h3 className="text-base font-semibold leading-snug text-gray-900 group-hover:text-brand-primary">
                  {post.title}
                </h3>
                <p className="mt-sm line-clamp-2 text-sm leading-relaxed text-gray-500">
                  {post.excerpt}
                </p>
                <div className="mt-lg flex items-center gap-md text-caption text-gray-400">
                  <span>{post.date}</span>
                  <span>&bull;</span>
                  <span>{post.readTime}</span>
                </div>
              </div>
            </a>
          ))}
        </div>

        {/* Mobile "View All" */}
        <div className="mt-xl text-center sm:hidden">
          <a
            href="/blog"
            className="inline-flex items-center gap-xs text-sm font-medium text-brand-primary"
          >
            View All Articles
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="m9 18 6-6-6-6" />
            </svg>
          </a>
        </div>
      </Container>
    </Section>
  );
}
