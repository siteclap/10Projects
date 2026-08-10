'use client';

import { useState } from 'react';
import Link from 'next/link';
import { ProjectCard } from '@/components/project/ProjectCard';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { ROUTES } from '@/lib/constants/routes';
import type { ProjectCard as ProjectCardType } from '@/lib/types/project';

// ---------------------------------------------------------------------------
// Mock data
// ---------------------------------------------------------------------------

interface SavedProjectEntry {
  id: number;
  saved_at: string;
  project: ProjectCardType;
}

const MOCK_SAVED_PROJECTS: SavedProjectEntry[] = [
  {
    id: 1,
    saved_at: '2026-07-30T14:30:00Z',
    project: {
      id: 101,
      title: 'Lodha Palava Phase 3',
      slug: 'lodha-palava-phase-3',
      permalink: '/navi-mumbai/dombivli/lodha-palava-phase-3',
      thumbnail: '',
      developer: 'Lodha Group',
      location: 'Dombivli',
      construction_stage: 'Under Construction',
      expected_possession: 'Dec 2027',
      rera_number: 'P51700045678',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 650, base_price: 5200000, total_price: 6100000, inventory_total: 200, inventory_available: 45 },
        { config_type: '3 BHK', carpet_area_sqft: 950, base_price: 7800000, total_price: 9100000, inventory_total: 100, inventory_available: 22 },
      ],
      price_min: 6100000,
      price_max: 9100000,
      fit_score: 92,
    },
  },
  {
    id: 2,
    saved_at: '2026-07-29T11:15:00Z',
    project: {
      id: 102,
      title: 'Arihant Aspire',
      slug: 'arihant-aspire',
      permalink: '/navi-mumbai/panvel/arihant-aspire',
      thumbnail: '',
      developer: 'Arihant Superstructures',
      location: 'Panvel',
      construction_stage: 'Ready to Move',
      expected_possession: 'Ready',
      rera_number: 'P51700012345',
      configurations: [
        { config_type: '1 BHK', carpet_area_sqft: 420, base_price: 3200000, total_price: 3800000, inventory_total: 150, inventory_available: 8 },
        { config_type: '2 BHK', carpet_area_sqft: 620, base_price: 4800000, total_price: 5600000, inventory_total: 100, inventory_available: 12 },
      ],
      price_min: 3800000,
      price_max: 5600000,
      fit_score: 87,
    },
  },
  {
    id: 3,
    saved_at: '2026-07-26T13:20:00Z',
    project: {
      id: 103,
      title: 'Paradise Sai Mannat',
      slug: 'paradise-sai-mannat',
      permalink: '/navi-mumbai/kharghar/paradise-sai-mannat',
      thumbnail: '',
      developer: 'Paradise Group',
      location: 'Kharghar',
      construction_stage: 'Under Construction',
      expected_possession: 'Mar 2028',
      rera_number: 'P51700098765',
      configurations: [
        { config_type: '2 BHK', carpet_area_sqft: 700, base_price: 7500000, total_price: 8800000, inventory_total: 80, inventory_available: 30 },
        { config_type: '3 BHK', carpet_area_sqft: 1050, base_price: 11000000, total_price: 12900000, inventory_total: 40, inventory_available: 15 },
      ],
      price_min: 8800000,
      price_max: 12900000,
      fit_score: 84,
    },
  },
  {
    id: 4,
    saved_at: '2026-07-25T10:45:00Z',
    project: {
      id: 104,
      title: 'Balaji Symphony',
      slug: 'balaji-symphony',
      permalink: '/navi-mumbai/ulwe/balaji-symphony',
      thumbnail: '',
      developer: 'Balaji Developers',
      location: 'Ulwe',
      construction_stage: 'Under Construction',
      expected_possession: 'Jun 2027',
      rera_number: 'P51700034567',
      configurations: [
        { config_type: '1 BHK', carpet_area_sqft: 380, base_price: 2800000, total_price: 3300000, inventory_total: 120, inventory_available: 35 },
        { config_type: '2 BHK', carpet_area_sqft: 580, base_price: 4200000, total_price: 4900000, inventory_total: 80, inventory_available: 20 },
      ],
      price_min: 3300000,
      price_max: 4900000,
      fit_score: 79,
    },
  },
];

// ---------------------------------------------------------------------------
// Component
// ---------------------------------------------------------------------------

export function SavedProjectsList() {
  const [savedProjects, setSavedProjects] = useState(MOCK_SAVED_PROJECTS);

  function handleUnsave(projectId: number | string) {
    setSavedProjects((prev) => prev.filter((sp) => sp.project.id !== projectId));
  }

  // Empty state
  if (savedProjects.length === 0) {
    return (
      <Card className="flex flex-col items-center justify-center px-lg py-16 text-center">
        <div className="flex h-[64px] w-[64px] items-center justify-center rounded-full bg-gray-100">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" className="text-gray-400" aria-hidden="true">
            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
          </svg>
        </div>
        <h3 className="mt-4 text-lg font-semibold text-gray-900">No saved projects yet</h3>
        <p className="mt-2 max-w-sm text-sm text-gray-500">
          Browse projects and save the ones you like to compare them later or schedule site visits.
        </p>
        <Button variant="primary" size="md" className="mt-6" asChild>
          <Link href={ROUTES.CITY}>Browse Projects</Link>
        </Button>
      </Card>
    );
  }

  return (
    <div className="space-y-6">
      <p className="text-sm text-gray-500">
        {savedProjects.length} saved project{savedProjects.length !== 1 ? 's' : ''}
      </p>

      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {savedProjects.map((entry) => (
          <div key={entry.id} className="relative">
            <ProjectCard
              project={entry.project}
              showFitScore
              className="w-full min-w-0"
            />
            <button
              type="button"
              className="absolute right-2 top-2 z-10 flex items-center gap-1.5 rounded-md bg-white/95 px-2.5 py-1.5 text-xs font-medium text-danger shadow-sm backdrop-blur-sm transition-colors hover:bg-danger hover:text-white"
              onClick={() => handleUnsave(entry.project.id)}
              aria-label={`Remove ${entry.project.title} from saved`}
            >
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <line x1="18" x2="6" y1="6" y2="18" />
                <line x1="6" x2="18" y1="6" y2="18" />
              </svg>
              Remove
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}
