import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';
import { RequirementsSummary } from '@/components/results/RequirementsSummary';
import { PhaseCta } from '@/components/results/PhaseCta';
import { ResultsList } from '@/components/results/ResultsList';
import type { RecommendationResult } from '@/lib/types/recommendation';

export async function generateMetadata(): Promise<Metadata> {
  return {
    title: 'Your Top 10 Matches — 10Projects',
    description:
      'AI-ranked projects that match your requirements, budget, and lifestyle preferences.',
    robots: {
      index: false,
      follow: false,
    },
  };
}

/**
 * Mock data: 5 realistic Navi Mumbai project results with fit scores.
 *
 * In production this will be replaced with an API call to
 * GET /recommendations/session/{sessionUuid}.
 */
const MOCK_RESULTS: RecommendationResult[] = [
  {
    rank: 1,
    project_id: 101,
    title: 'Lodha Palava City',
    permalink: '/navi-mumbai/dombivli/lodha-palava-city',
    thumbnail: '/images/projects/lodha-palava.jpg',
    final_score: 93,
    label: 'Best Match',
    scores: {
      value_for_money: 88,
      location_connectivity: 91,
      construction_quality: 95,
      developer_reputation: 97,
      rera_compliance: 100,
      amenities_lifestyle: 94,
    },
    strengths: [
      { category: 'developer_reputation', label: 'Top Developer', score: 97 },
      { category: 'construction_quality', label: 'Premium Construction', score: 95 },
      { category: 'amenities_lifestyle', label: '100+ Amenities', score: 94 },
    ],
    tradeoffs: [
      { category: 'value_for_money', label: 'Premium Pricing', score: 88 },
    ],
    project_meta: {
      developer: 'Lodha Group',
      location: 'Dombivli',
      price_min: 7000000,
      price_max: 18500000,
      configurations: ['1 BHK', '2 BHK', '3 BHK'],
      construction_stage: 'Under Construction',
      possession: 'Dec 2026',
      rera_number: 'P51700045678',
    },
  },
  {
    rank: 2,
    project_id: 102,
    title: 'Godrej Vistas',
    permalink: '/navi-mumbai/panvel/godrej-vistas',
    thumbnail: '/images/projects/godrej-vistas.jpg',
    final_score: 89,
    label: 'Great Value',
    scores: {
      value_for_money: 94,
      location_connectivity: 85,
      construction_quality: 90,
      developer_reputation: 95,
      rera_compliance: 100,
      amenities_lifestyle: 86,
    },
    strengths: [
      { category: 'developer_reputation', label: 'Trusted Developer', score: 95 },
      { category: 'value_for_money', label: 'Great Value', score: 94 },
      { category: 'construction_quality', label: 'Quality Build', score: 90 },
    ],
    tradeoffs: [
      { category: 'location_connectivity', label: 'Developing Area', score: 85 },
    ],
    project_meta: {
      developer: 'Godrej Properties',
      location: 'Panvel',
      price_min: 5500000,
      price_max: 12000000,
      configurations: ['1 BHK', '2 BHK', '3 BHK'],
      construction_stage: 'Under Construction',
      possession: 'Mar 2027',
      rera_number: 'P51700034567',
    },
  },
  {
    rank: 3,
    project_id: 103,
    title: 'Paradise Sai Mannat',
    permalink: '/navi-mumbai/kharghar/paradise-sai-mannat',
    thumbnail: '/images/projects/paradise-sai-mannat.jpg',
    final_score: 86,
    label: 'Location Pick',
    scores: {
      value_for_money: 82,
      location_connectivity: 95,
      construction_quality: 84,
      developer_reputation: 78,
      rera_compliance: 100,
      amenities_lifestyle: 88,
    },
    strengths: [
      { category: 'location_connectivity', label: 'Excellent Connectivity', score: 95 },
      { category: 'amenities_lifestyle', label: 'Modern Amenities', score: 88 },
      { category: 'rera_compliance', label: 'RERA Registered', score: 100 },
    ],
    tradeoffs: [
      { category: 'developer_reputation', label: 'Newer Developer', score: 78 },
      { category: 'value_for_money', label: 'Above Average Price', score: 82 },
    ],
    project_meta: {
      developer: 'Paradise Group',
      location: 'Kharghar',
      price_min: 8500000,
      price_max: 15000000,
      configurations: ['2 BHK', '3 BHK'],
      construction_stage: 'Ready to Move',
      possession: 'Ready',
      rera_number: 'P51700023456',
    },
  },
  {
    rank: 4,
    project_id: 104,
    title: 'Arihant Aspire',
    permalink: '/navi-mumbai/panvel/arihant-aspire',
    thumbnail: '/images/projects/arihant-aspire.jpg',
    final_score: 82,
    label: 'Budget Pick',
    scores: {
      value_for_money: 96,
      location_connectivity: 78,
      construction_quality: 80,
      developer_reputation: 75,
      rera_compliance: 100,
      amenities_lifestyle: 79,
    },
    strengths: [
      { category: 'value_for_money', label: 'Best Value', score: 96 },
      { category: 'rera_compliance', label: 'RERA Registered', score: 100 },
    ],
    tradeoffs: [
      { category: 'developer_reputation', label: 'Mid-tier Developer', score: 75 },
      { category: 'location_connectivity', label: 'Limited Transit', score: 78 },
    ],
    project_meta: {
      developer: 'Arihant Superstructures',
      location: 'Panvel',
      price_min: 3800000,
      price_max: 7200000,
      configurations: ['1 BHK', '2 BHK'],
      construction_stage: 'Under Construction',
      possession: 'Jun 2027',
      rera_number: 'P51700056789',
    },
  },
  {
    rank: 5,
    project_id: 105,
    title: 'L&T Seawoods Residences',
    permalink: '/navi-mumbai/seawoods/lt-seawoods-residences',
    thumbnail: '/images/projects/lt-seawoods.jpg',
    final_score: 79,
    label: 'Premium Pick',
    scores: {
      value_for_money: 68,
      location_connectivity: 96,
      construction_quality: 92,
      developer_reputation: 94,
      rera_compliance: 100,
      amenities_lifestyle: 90,
    },
    strengths: [
      { category: 'location_connectivity', label: 'Metro Connected', score: 96 },
      { category: 'developer_reputation', label: 'L&T Quality', score: 94 },
      { category: 'construction_quality', label: 'Premium Build', score: 92 },
    ],
    tradeoffs: [
      { category: 'value_for_money', label: 'Premium Pricing', score: 68 },
    ],
    project_meta: {
      developer: 'L&T Realty',
      location: 'Seawoods',
      price_min: 15000000,
      price_max: 32000000,
      configurations: ['2 BHK', '3 BHK', '4 BHK'],
      construction_stage: 'Under Construction',
      possession: 'Dec 2027',
      rera_number: 'P51700067890',
    },
  },
];

const MOCK_REQUIREMENTS: Record<string, string | string[]> = {
  city: 'Navi Mumbai',
  budget: '50L - 1.5 Cr',
  configuration: ['2 BHK', '3 BHK'],
  timeline: 'Within 2 years',
  purpose: 'Self-use',
  priorities: ['Good connectivity', 'Reputed developer', 'Modern amenities'],
};

type Props = {
  params: Promise<{ sessionUuid: string }>;
};

export default async function ResultsPage({ params }: Props) {
  const { sessionUuid } = await params;

  /**
   * In production, replace mock data with API call:
   * const recommendation = await getRecommendations(sessionUuid);
   */
  const results = MOCK_RESULTS;
  const currentPhase = 1;
  const currentAccuracy = 72;
  const totalCandidates = 47;

  return (
    <div className="flex flex-1 flex-col py-xl md:py-2xl">
      <Container>
        <div className="flex flex-col gap-xl">
          {/* Page title */}
          <div className="flex flex-col gap-sm">
            <h1 className="text-h2 text-gray-900">Your Top Matches</h1>
            <p className="text-base text-gray-500">
              AI-ranked projects based on your requirements, budget, and lifestyle preferences
            </p>
          </div>

          {/* Requirements summary */}
          <RequirementsSummary requirements={MOCK_REQUIREMENTS} />

          {/* Phase CTA */}
          <PhaseCta
            currentPhase={currentPhase}
            currentAccuracy={currentAccuracy}
            sessionUuid={sessionUuid}
          />

          {/* Results list */}
          <ResultsList results={results} totalCandidates={totalCandidates} />
        </div>
      </Container>
    </div>
  );
}
