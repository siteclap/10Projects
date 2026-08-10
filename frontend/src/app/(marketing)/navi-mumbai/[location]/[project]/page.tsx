import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { prisma } from '@/lib/db';
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
import type { Project } from '@/lib/types/project';

// --- ISR: revalidate every 30 minutes ---
export const revalidate = 1800;

// --- Data fetching ---

async function getProject(locationSlug: string, projectSlug: string) {
  const dbProject = await prisma.project.findFirst({
    where: {
      slug: projectSlug,
      location: { slug: locationSlug },
      published: true,
    },
    include: {
      location: true,
      configurations: { orderBy: { basePrice: 'asc' } },
      gallery: { orderBy: { sortOrder: 'asc' } },
    },
  });

  if (!dbProject) return null;

  const locationName = dbProject.location?.name || locationSlug;
  const locationSlugSafe = dbProject.location?.slug || locationSlug;

  // Map DB model → frontend Project type
  const project: Project = {
    id: dbProject.id,
    title: dbProject.title,
    slug: dbProject.slug,
    permalink: `/navi-mumbai/${locationSlugSafe}/${dbProject.slug}`,
    thumbnail: dbProject.thumbnail || '',
    developer: dbProject.developer,
    location: locationName,
    construction_stage: dbProject.constructionStage || 'Under Construction',
    expected_possession: dbProject.expectedPossession || 'TBA',
    rera_number: dbProject.reraNumber || '',
    configurations: dbProject.configurations.map((c) => ({
      config_type: c.configType,
      carpet_area_sqft: c.carpetAreaSqft,
      base_price: c.basePrice,
      total_price: c.totalPrice,
      inventory_total: c.inventoryTotal,
      inventory_available: c.inventoryAvailable,
    })),
    price_min: dbProject.priceMin,
    price_max: dbProject.priceMax,
    fit_score: dbProject.fitScore ?? undefined,
    description: dbProject.description ?? undefined,
    highlights: dbProject.highlights ?? undefined,
    amenities: dbProject.amenities,
    pros: dbProject.pros,
    cons: dbProject.cons,
    land_parcel: dbProject.landParcel ?? undefined,
    floors: dbProject.floors ?? undefined,
  };

  const images = dbProject.gallery.length > 0
    ? dbProject.gallery.map((img) => img.url)
    : dbProject.thumbnail
      ? [dbProject.thumbnail]
      : [];

  return { project, images };
}

async function getSimilarProjects(locationSlug: string, excludeSlug: string) {
  const similar = await prisma.project.findMany({
    where: {
      published: true,
      slug: { not: excludeSlug },
      location: { slug: locationSlug },
    },
    include: {
      location: true,
      configurations: true,
    },
    take: 4,
    orderBy: { fitScore: 'desc' },
  });

  return similar.map((p) => ({
    title: p.title,
    developer: p.developer,
    location: `${p.location?.name || ''}, Navi Mumbai`,
    priceMin: p.priceMin,
    priceMax: p.priceMax,
    configs: p.configurations.map((c) => c.configType).join(', '),
    fitScore: p.fitScore ?? 0,
    image: p.thumbnail || '',
    slug: `/navi-mumbai/${p.location?.slug || locationSlug}/${p.slug}`,
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

const PROJECT_TABS = [
  { id: 'overview', label: 'Overview' },
  { id: 'price', label: 'Price' },
  { id: 'pros-cons', label: 'Pros & Cons' },
  { id: 'amenities', label: 'Amenities' },
  { id: 'floor-plans', label: 'Floor Plans' },
  { id: 'location', label: 'Location' },
  { id: 'developer', label: 'Developer' },
  { id: 'faq', label: 'FAQ' },
];

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
  const projects = await prisma.project.findMany({
    where: { published: true },
    select: { slug: true, location: { select: { slug: true } } },
  });

  return projects
    .filter((p) => p.location)
    .map((p) => ({
      location: p.location!.slug,
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
  const fullUrl = `${process.env.NEXT_PUBLIC_SITE_URL || 'https://10projects.com'}${project.permalink}`;
  const faqs = generateFaqs(project);
  const similarProjects = await getSimilarProjects(location, projectSlug);

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
      <ProjectTabs tabs={PROJECT_TABS} />

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
                        {project.title} is a residential project by {project.developer}{' '}
                        located in {project.location}, Navi Mumbai.
                        {project.configurations.length > 0 && (
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
                    Why consider buying at{' '}
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

            {/* Section: Price — only if configurations exist */}
            {project.configurations.length > 0 && (
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

            {/* Section: Floor Plans */}
            {project.configurations.length > 0 && (
              <FloorPlanSection
                configurations={project.configurations}
                projectTitle={project.title}
              />
            )}

            {/* Inline CTA — after floor plans */}
            <div className="mt-3xl">
              <InlineLeadCTA projectTitle={project.title} />
            </div>

            {/* Section: Location */}
            <section id="location" className="mt-3xl border-t border-gray-100 pt-3xl">
              <h2 className="text-h2 text-gray-900">Location</h2>
              <div className="mt-xl">
                {/* Map placeholder */}
                <div className="flex h-[300px] items-center justify-center rounded-md border border-gray-200 bg-gray-100">
                  <div className="text-center">
                    <svg
                      width="40"
                      height="40"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="1.5"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      className="mx-auto text-gray-400"
                      aria-hidden="true"
                    >
                      <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                      <circle cx="12" cy="10" r="3" />
                    </svg>
                    <p className="mt-sm text-sm text-gray-500">
                      {project.location}, Navi Mumbai
                    </p>
                  </div>
                </div>
              </div>
            </section>

            {/* Section: Developer */}
            <section id="developer" className="mt-3xl border-t border-gray-100 pt-3xl">
              <h2 className="text-h2 text-gray-900">About the Developer</h2>
              <div className="mt-xl rounded-md border border-gray-200 p-xl">
                <div className="flex items-center gap-xl">
                  <div className="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-md bg-brand-primary-pale text-h4 font-bold text-brand-primary">
                    {devInitials}
                  </div>
                  <div>
                    <h3 className="text-h4 text-gray-900">{project.developer}</h3>
                  </div>
                </div>
              </div>
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

            {/* Section: Why Buy from 10Projects */}
            <section className="mt-3xl border-t border-gray-100 pt-3xl">
              <h2 className="text-h2 text-gray-900">Why Buy from 10Projects?</h2>
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
            className="sticky top-[100px] hidden w-[340px] shrink-0 self-start lg:flex"
          />
        </div>
      </Container>

      {/* Mobile sticky CTA bar */}
      <MobileStickyBar projectTitle={project.title} projectUrl={fullUrl} />
    </LeadFormWrapper>
  );
}
