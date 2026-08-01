import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { JsonLd } from '@/components/seo/JsonLd';
import { breadcrumbJsonLd } from '@/lib/seo/json-ld';

const SITE_NAME = '10Projects';

export function generateMetadata(): Metadata {
  const title = `How We Score Projects — Methodology — ${SITE_NAME}`;
  const description =
    'Learn how 10Projects scores every real estate project across 20 categories including value for money, construction quality, developer reputation, RERA compliance, and more. Transparent, data-driven, and AI-powered.';

  return {
    title,
    description,
    openGraph: {
      title,
      description,
      url: 'https://10projects.com/methodology',
      siteName: SITE_NAME,
      type: 'website',
      locale: 'en_IN',
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description,
    },
    robots: { index: true, follow: true },
    alternates: { canonical: '/methodology' },
  };
}

/* ---------- Scoring Categories ---------- */

interface ScoringCategory {
  id: string;
  label: string;
  description: string;
  weightEndUser: number;
  weightInvestor: number;
}

const SCORING_CATEGORIES: ScoringCategory[] = [
  {
    id: 'value_for_money',
    label: 'Value for Money',
    description:
      'Evaluates the price per square foot against comparable projects in the same micro-market. Considers carpet area efficiency, inclusion of amenities in base price, registration and stamp duty impact, and overall cost-benefit ratio. Projects offering more usable space and better amenities per rupee score higher.',
    weightEndUser: 8,
    weightInvestor: 7,
  },
  {
    id: 'location_connectivity',
    label: 'Location & Connectivity',
    description:
      'Measures proximity and access to major transportation hubs including railway stations, metro lines, expressways, and bus terminals. Factors in commute time to key employment centres (BKC, Andheri, Fort), airport distance, and planned connectivity improvements like upcoming metro lines or road widening projects.',
    weightEndUser: 7,
    weightInvestor: 6,
  },
  {
    id: 'construction_quality',
    label: 'Construction Quality',
    description:
      'Assesses the structural integrity, materials used, finishing quality, and engineering standards of the project. We evaluate RCC grade, wall thickness, waterproofing methods, flooring quality, electrical fittings grade, plumbing standards, and overall build quality based on site inspections and developer track record.',
    weightEndUser: 7,
    weightInvestor: 5,
  },
  {
    id: 'developer_reputation',
    label: 'Developer Reputation',
    description:
      'Comprehensive evaluation of the developer\'s track record including number of completed projects, delivery timeline adherence, build quality consistency, financial stability, corporate governance, and customer satisfaction scores from previous buyers. Tier 1 developers with strong balance sheets score highest.',
    weightEndUser: 6,
    weightInvestor: 7,
  },
  {
    id: 'rera_compliance',
    label: 'RERA Compliance',
    description:
      'Verifies active RERA registration, checks for any regulatory violations or complaints, evaluates transparency in quarterly RERA filings, and assesses compliance with approved building plans. Projects with clean RERA records and no buyer complaints receive maximum scores.',
    weightEndUser: 6,
    weightInvestor: 6,
  },
  {
    id: 'possession_timeline',
    label: 'Possession Timeline',
    description:
      'Evaluates the likelihood of on-time possession based on current construction progress versus stated timeline, developer\'s historical delivery performance, and any external factors that could cause delays (regulatory approvals, environmental clearances). Bonus points for projects offering penalty clauses for delays.',
    weightEndUser: 6,
    weightInvestor: 4,
  },
  {
    id: 'amenities_lifestyle',
    label: 'Amenities & Lifestyle',
    description:
      'Rates the quality and breadth of amenities including swimming pool, gym, clubhouse, sports facilities, children\'s play areas, landscaped gardens, community spaces, and lifestyle features. Considers maintenance cost sustainability and whether amenities match the project\'s price positioning.',
    weightEndUser: 5,
    weightInvestor: 3,
  },
  {
    id: 'floor_plan_design',
    label: 'Floor Plan Design',
    description:
      'Analyses architectural efficiency including room proportions, kitchen functionality, bathroom sizing, storage provisions, balcony utility, natural light ingress from multiple directions, cross-ventilation design, privacy from neighbouring units, and overall space utilisation. Vastu compliance is noted but not weighted.',
    weightEndUser: 5,
    weightInvestor: 3,
  },
  {
    id: 'appreciation_potential',
    label: 'Appreciation Potential',
    description:
      'Projects future price growth based on micro-market trends, upcoming infrastructure catalysts (airport, metro, roads), absorption rates in the area, demand-supply dynamics, and comparable project appreciation history. Locations with confirmed infrastructure projects and low current pricing score highest.',
    weightEndUser: 4,
    weightInvestor: 9,
  },
  {
    id: 'rental_yield',
    label: 'Rental Yield',
    description:
      'Calculates expected rental return as a percentage of property value, factoring in location demand drivers (IT parks, commercial zones, educational institutions), rental comparable data, occupancy rates in the micro-market, and expected rent escalation over 5 years.',
    weightEndUser: 3,
    weightInvestor: 8,
  },
  {
    id: 'neighbourhood_safety',
    label: 'Neighbourhood Safety',
    description:
      'Evaluates the safety profile of the surrounding neighbourhood based on crime statistics, gated community features, street lighting, CCTV coverage, proximity to police stations, and overall area development maturity. Well-established CIDCO nodes with active resident welfare associations score higher.',
    weightEndUser: 5,
    weightInvestor: 3,
  },
  {
    id: 'water_supply',
    label: 'Water Supply',
    description:
      'Assesses reliability and quality of water supply including source (CIDCO, bore well, tanker), daily supply hours, water treatment facilities, rainwater harvesting implementation, and backup arrangements. Projects with 24/7 CIDCO water supply and functional rainwater harvesting systems score highest.',
    weightEndUser: 5,
    weightInvestor: 3,
  },
  {
    id: 'power_backup',
    label: 'Power Backup',
    description:
      'Evaluates the power infrastructure including DG backup coverage (common areas only vs full flat), solar panel integration, smart metering, power factor correction, and overall electrical infrastructure robustness. Full-flat DG backup with solar integration and smart energy management receives top scores.',
    weightEndUser: 4,
    weightInvestor: 3,
  },
  {
    id: 'natural_light_ventilation',
    label: 'Natural Light & Ventilation',
    description:
      'Measures the quality of natural light and air circulation based on building orientation, window-to-wall ratio, floor height, building spacing, prevailing wind patterns, and architectural design features that enhance airflow and daylight penetration into living spaces.',
    weightEndUser: 5,
    weightInvestor: 2,
  },
  {
    id: 'parking_ratio',
    label: 'Parking Ratio',
    description:
      'Calculates the ratio of parking spaces to total apartments, evaluates parking type (covered stilt, basement, mechanical), visitor parking provisions, EV charging infrastructure, and ease of access. A ratio of 1:1 or better with covered parking receives the highest scores.',
    weightEndUser: 4,
    weightInvestor: 3,
  },
  {
    id: 'green_building',
    label: 'Green Building',
    description:
      'Evaluates environmental sustainability features including IGBC/GRIHA certification, energy-efficient design, solar integration, waste management systems, green cover percentage, rainwater harvesting, and overall carbon footprint reduction measures. Certified green buildings receive significant score bonuses.',
    weightEndUser: 3,
    weightInvestor: 2,
  },
  {
    id: 'school_proximity',
    label: 'School Proximity',
    description:
      'Maps proximity and quality of educational institutions within a 5 km radius. Considers CBSE, ICSE, and State board schools, international schools, coaching centres, and pre-schools. Factors in school ratings, fee structure compatibility with the project\'s target demographic, and safe commute routes.',
    weightEndUser: 5,
    weightInvestor: 3,
  },
  {
    id: 'hospital_proximity',
    label: 'Hospital Proximity',
    description:
      'Evaluates access to healthcare including distance to multi-speciality hospitals, availability of 24/7 emergency services within 3 km, number of clinics and diagnostic centres in the vicinity, ambulance response time estimates, and presence of pharmacies within walking distance.',
    weightEndUser: 5,
    weightInvestor: 3,
  },
  {
    id: 'shopping_proximity',
    label: 'Shopping & Entertainment',
    description:
      'Measures access to daily convenience shopping (grocery stores, vegetable markets), organised retail (malls, supermarkets), restaurants and dining options, entertainment venues (cinema, recreational centres), and overall commercial ecosystem maturity within a 3 km radius.',
    weightEndUser: 4,
    weightInvestor: 3,
  },
  {
    id: 'public_transport',
    label: 'Public Transport',
    description:
      'Detailed evaluation of public transport access including railway station distance and frequency, bus route coverage, auto-rickshaw availability, planned metro connectivity, and last-mile transport options. Projects within 1 km of a railway station with frequent services score highest.',
    weightEndUser: 5,
    weightInvestor: 4,
  },
];

/* ---------- Page ---------- */

export default function MethodologyPage() {
  const totalEndUser = SCORING_CATEGORIES.reduce(
    (sum, cat) => sum + cat.weightEndUser,
    0
  );
  const totalInvestor = SCORING_CATEGORIES.reduce(
    (sum, cat) => sum + cat.weightInvestor,
    0
  );

  const breadcrumbLdItems = [
    { name: 'Home', url: '/' },
    { name: 'Methodology', url: '/methodology' },
  ];

  return (
    <>
      <JsonLd data={breadcrumbJsonLd(breadcrumbLdItems)} />

      {/* Hero */}
      <Section variant="alt" className="py-3xl">
        <Container>
          <h1 className="text-h1 text-gray-900">How We Score Projects</h1>
          <p className="mt-md max-w-[640px] text-body-lg text-gray-600">
            Every project on 10Projects is evaluated across 20 objective
            categories. Our scoring engine combines verified data, on-ground
            research, and AI-powered analysis to generate a comprehensive Fit
            Score for each project.
          </p>
        </Container>
      </Section>

      {/* Scoring categories */}
      <Section>
        <Container>
          <h2 className="text-h2 text-gray-900">20 Scoring Categories</h2>
          <p className="mt-sm text-base text-gray-500">
            Click on any category to learn how we evaluate it
          </p>

          <div className="mt-xl flex flex-col gap-md">
            {SCORING_CATEGORIES.map((category, index) => (
              <details
                key={category.id}
                className="group rounded-sm border border-gray-200 bg-white"
              >
                <summary className="flex cursor-pointer items-center justify-between px-xl py-lg [&::-webkit-details-marker]:hidden">
                  <div className="flex items-center gap-lg">
                    <span className="flex h-[32px] w-[32px] shrink-0 items-center justify-center rounded-full bg-brand-primary-pale text-caption font-bold text-brand-primary">
                      {index + 1}
                    </span>
                    <span className="text-base font-medium text-gray-900">
                      {category.label}
                    </span>
                  </div>
                  <div className="flex items-center gap-xl">
                    <div className="hidden items-center gap-lg sm:flex">
                      <span className="text-caption text-gray-400">
                        End-user: {category.weightEndUser}%
                      </span>
                      <span className="text-caption text-gray-400">
                        Investor: {category.weightInvestor}%
                      </span>
                    </div>
                    <svg
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      className="shrink-0 text-gray-400 transition-transform group-open:rotate-180"
                      aria-hidden="true"
                    >
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </div>
                </summary>
                <div className="border-t border-gray-100 px-xl py-lg">
                  <p className="text-sm leading-relaxed text-gray-600">
                    {category.description}
                  </p>
                  <div className="mt-lg flex gap-xl sm:hidden">
                    <span className="text-caption text-gray-500">
                      End-user weight: {category.weightEndUser}%
                    </span>
                    <span className="text-caption text-gray-500">
                      Investor weight: {category.weightInvestor}%
                    </span>
                  </div>
                </div>
              </details>
            ))}
          </div>
        </Container>
      </Section>

      {/* Weight profile comparison */}
      <Section variant="alt">
        <Container>
          <h2 className="text-h2 text-gray-900">
            Weight Profiles: End-User vs Investor
          </h2>
          <p className="mt-sm text-base text-gray-500">
            Different buyer types have different priorities. Our Fit Score adapts
            based on your profile.
          </p>

          <div className="mt-xl overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead>
                <tr className="border-b border-gray-200">
                  <th className="pb-md pr-xl font-semibold text-gray-700">
                    Category
                  </th>
                  <th className="pb-md pr-xl text-center font-semibold text-brand-primary">
                    End-User Weight
                  </th>
                  <th className="pb-md text-center font-semibold text-accent-dark">
                    Investor Weight
                  </th>
                </tr>
              </thead>
              <tbody>
                {SCORING_CATEGORIES.map((category) => (
                  <tr
                    key={category.id}
                    className="border-b border-gray-100 last:border-0"
                  >
                    <td className="py-md pr-xl text-gray-900">
                      {category.label}
                    </td>
                    <td className="py-md pr-xl text-center">
                      <div className="flex items-center justify-center gap-sm">
                        <div className="h-[6px] w-[60px] overflow-hidden rounded-full bg-gray-200">
                          <div
                            className="h-full rounded-full bg-brand-primary"
                            style={{
                              width: `${(category.weightEndUser / 10) * 100}%`,
                            }}
                          />
                        </div>
                        <span className="w-[28px] text-right tabular-nums text-gray-700">
                          {category.weightEndUser}%
                        </span>
                      </div>
                    </td>
                    <td className="py-md text-center">
                      <div className="flex items-center justify-center gap-sm">
                        <div className="h-[6px] w-[60px] overflow-hidden rounded-full bg-gray-200">
                          <div
                            className="h-full rounded-full bg-accent"
                            style={{
                              width: `${(category.weightInvestor / 10) * 100}%`,
                            }}
                          />
                        </div>
                        <span className="w-[28px] text-right tabular-nums text-gray-700">
                          {category.weightInvestor}%
                        </span>
                      </div>
                    </td>
                  </tr>
                ))}
                <tr className="border-t-2 border-gray-300">
                  <td className="py-md pr-xl font-semibold text-gray-900">
                    Total
                  </td>
                  <td className="py-md pr-xl text-center font-semibold text-brand-primary">
                    {totalEndUser}%
                  </td>
                  <td className="py-md text-center font-semibold text-accent-dark">
                    {totalInvestor}%
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Container>
      </Section>

      {/* Transparency pledge */}
      <Section>
        <Container>
          <div className="mx-auto max-w-narrow text-center">
            <h2 className="text-h2 text-gray-900">Our Transparency Pledge</h2>
            <p className="mt-lg text-base leading-relaxed text-gray-600">
              10Projects is committed to unbiased, data-driven project evaluation.
              We do not accept payments from developers to influence scores.
              Our revenue comes from connecting qualified buyers with trusted
              channel partners -- never from manipulating rankings.
            </p>

            <div className="mt-3xl grid grid-cols-1 gap-xl sm:grid-cols-3">
              <div className="rounded-md border border-gray-200 bg-white p-xl">
                <div className="mx-auto flex h-[48px] w-[48px] items-center justify-center rounded-full bg-success-light">
                  <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="text-success"
                    aria-hidden="true"
                  >
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                    <path d="m9 12 2 2 4-4" />
                  </svg>
                </div>
                <h3 className="mt-lg text-h4 text-gray-900">No Pay-to-Rank</h3>
                <p className="mt-sm text-sm text-gray-600">
                  Developers cannot pay to improve their scores. Every project
                  is evaluated purely on data and verified facts.
                </p>
              </div>

              <div className="rounded-md border border-gray-200 bg-white p-xl">
                <div className="mx-auto flex h-[48px] w-[48px] items-center justify-center rounded-full bg-brand-primary-pale">
                  <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="text-brand-primary"
                    aria-hidden="true"
                  >
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                </div>
                <h3 className="mt-lg text-h4 text-gray-900">Full Disclosure</h3>
                <p className="mt-sm text-sm text-gray-600">
                  Every score is broken down across all 20 categories. See
                  exactly why a project scored the way it did.
                </p>
              </div>

              <div className="rounded-md border border-gray-200 bg-white p-xl">
                <div className="mx-auto flex h-[48px] w-[48px] items-center justify-center rounded-full bg-accent-pale">
                  <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="text-accent-dark"
                    aria-hidden="true"
                  >
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                  </svg>
                </div>
                <h3 className="mt-lg text-h4 text-gray-900">Verified Data</h3>
                <p className="mt-sm text-sm text-gray-600">
                  Scores are based on RERA filings, site visits, government
                  records, and verified buyer reviews -- not developer claims.
                </p>
              </div>
            </div>
          </div>
        </Container>
      </Section>
    </>
  );
}
