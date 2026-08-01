'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { Container } from '@/components/layout/Container';
import { Button } from '@/components/ui/Button';
import { CompareTable } from '@/components/compare/CompareTable';
import { useCompare } from '@/lib/hooks/use-compare';
import { useToast } from '@/lib/hooks/use-toast';
import { ROUTES } from '@/lib/constants/routes';
import type { ComparisonProject } from '@/lib/types/comparison';

/**
 * Mock comparison projects for Navi Mumbai.
 *
 * In production, project data will be fetched from the API
 * using the project IDs stored in localStorage via useCompare.
 */
const MOCK_PROJECTS: Record<number, ComparisonProject> = {
  101: {
    id: 101,
    title: 'Lodha Palava City',
    slug: 'lodha-palava-city',
    thumbnail: '/images/projects/lodha-palava.jpg',
    developer: 'Lodha Group',
    location: 'Dombivli',
    configurations: ['1 BHK', '2 BHK', '3 BHK'],
    price_min: 7000000,
    price_max: 18500000,
    possession: 'Dec 2026',
    construction_stage: 'Under Construction',
    rera_number: 'P51700045678',
    amenities: [
      'Swimming Pool',
      'Clubhouse',
      'Gymnasium',
      'Tennis Court',
      'Jogging Track',
      'Kids Play Area',
      'Landscaped Garden',
    ],
    railway_distance_km: 2.5,
    fit_score: 93,
  },
  102: {
    id: 102,
    title: 'Godrej Vistas',
    slug: 'godrej-vistas',
    thumbnail: '/images/projects/godrej-vistas.jpg',
    developer: 'Godrej Properties',
    location: 'Panvel',
    configurations: ['1 BHK', '2 BHK', '3 BHK'],
    price_min: 5500000,
    price_max: 12000000,
    possession: 'Mar 2027',
    construction_stage: 'Under Construction',
    rera_number: 'P51700034567',
    amenities: [
      'Swimming Pool',
      'Clubhouse',
      'Gymnasium',
      'Badminton Court',
      'Children Playground',
      'Multipurpose Hall',
    ],
    railway_distance_km: 3.8,
    fit_score: 89,
  },
  103: {
    id: 103,
    title: 'Paradise Sai Mannat',
    slug: 'paradise-sai-mannat',
    thumbnail: '/images/projects/paradise-sai-mannat.jpg',
    developer: 'Paradise Group',
    location: 'Kharghar',
    configurations: ['2 BHK', '3 BHK'],
    price_min: 8500000,
    price_max: 15000000,
    possession: 'Ready',
    construction_stage: 'Ready to Move',
    rera_number: 'P51700023456',
    amenities: [
      'Swimming Pool',
      'Gymnasium',
      'Garden',
      'Security',
      'Power Backup',
      'Parking',
      'Lift',
      'Rain Water Harvesting',
    ],
    railway_distance_km: 1.2,
    fit_score: 86,
  },
};

/**
 * Default project IDs used when localStorage has no compare data.
 * Shows 3 projects so the compare page always renders something useful.
 */
const DEFAULT_PROJECT_IDS = [101, 102, 103];

export default function ComparePage() {
  const { ids } = useCompare();
  const { toast } = useToast();
  const [projects, setProjects] = useState<ComparisonProject[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    /**
     * In production, replace with API call:
     * const comparison = await createComparison(projectIds);
     * setProjects(comparison.projects);
     */
    const projectIds = ids.length >= 2 ? ids : DEFAULT_PROJECT_IDS;
    const resolved = projectIds
      .map((id) => MOCK_PROJECTS[id])
      .filter((p): p is ComparisonProject => p != null);

    setProjects(resolved);
    setLoading(false);
  }, [ids]);

  function handleShare() {
    const url = window.location.href;
    if (navigator.clipboard) {
      navigator.clipboard.writeText(url).then(() => {
        toast('Comparison link copied to clipboard', 'success');
      });
    }
  }

  if (loading) {
    return (
      <div className="flex flex-1 items-center justify-center py-4xl">
        <div className="flex flex-col items-center gap-lg">
          <div className="h-[32px] w-[32px] animate-spin rounded-full border-2 border-gray-200 border-t-brand-primary" />
          <p className="text-sm text-gray-500">Loading comparison...</p>
        </div>
      </div>
    );
  }

  if (projects.length < 2) {
    return (
      <div className="flex flex-1 flex-col py-xl md:py-2xl">
        <Container>
          <div className="flex flex-col items-center justify-center gap-xl py-4xl text-center">
            <svg
              width="48"
              height="48"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="1.5"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-gray-300"
              aria-hidden="true"
            >
              <line x1="18" x2="18" y1="20" y2="10" />
              <line x1="12" x2="12" y1="20" y2="4" />
              <line x1="6" x2="6" y1="20" y2="14" />
            </svg>
            <div className="flex flex-col gap-sm">
              <h2 className="text-h3 text-gray-900">Not enough projects to compare</h2>
              <p className="text-base text-gray-500">
                Select at least 2 projects to see a side-by-side comparison.
              </p>
            </div>
            <Link href={ROUTES.CITY}>
              <Button variant="primary" size="md">
                Browse Projects
              </Button>
            </Link>
          </div>
        </Container>
      </div>
    );
  }

  return (
    <div className="flex flex-1 flex-col py-xl md:py-2xl">
      <Container>
        <div className="flex flex-col gap-xl">
          {/* Header */}
          <div className="flex flex-col gap-lg sm:flex-row sm:items-center sm:justify-between">
            <div className="flex flex-col gap-xs">
              <h1 className="text-h2 text-gray-900">Compare Projects</h1>
              <p className="text-sm text-gray-500">
                Side-by-side comparison of {projects.length} projects
              </p>
            </div>

            <div className="flex items-center gap-sm">
              <Button variant="secondary" size="sm" onClick={handleShare}>
                <svg
                  width="14"
                  height="14"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  aria-hidden="true"
                >
                  <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                  <polyline points="16 6 12 2 8 6" />
                  <line x1="12" x2="12" y1="2" y2="15" />
                </svg>
                Share
              </Button>

              <Button variant="secondary" size="sm" onClick={() => window.print()}>
                <svg
                  width="14"
                  height="14"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  aria-hidden="true"
                >
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                  <polyline points="7 10 12 15 17 10" />
                  <line x1="12" x2="12" y1="15" y2="3" />
                </svg>
                Download
              </Button>
            </div>
          </div>

          {/* Comparison table */}
          <CompareTable projects={projects} />

          {/* Back link */}
          <div className="flex justify-center">
            <Link
              href={ROUTES.CITY}
              className="text-sm font-medium text-brand-primary no-underline transition-colors hover:text-brand-primary-dark hover:no-underline"
            >
              Browse more projects in Navi Mumbai
            </Link>
          </div>
        </div>
      </Container>
    </div>
  );
}
