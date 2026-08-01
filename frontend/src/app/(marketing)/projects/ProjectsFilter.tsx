'use client';

import { useState } from 'react';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { Chip } from '@/components/ui/Chip';
import { ProjectCard } from '@/components/project/ProjectCard';
import type { ProjectCard as ProjectCardType } from '@/lib/types/project';

const LOCATIONS = ['All', 'Kharghar', 'Panvel', 'Ulwe', 'Vashi', 'Airoli', 'Ghansoli', 'Nerul', 'Taloja'];
const CONFIGURATIONS = ['All', '1 BHK', '2 BHK', '3 BHK', '4 BHK'];
const BUDGET_RANGES = ['All', 'Under 50L', '50L - 1Cr', '1Cr - 2Cr', '2Cr+'];
const CONSTRUCTION_STAGES = ['All', 'Under Construction', 'Ready to Move', 'New Launch'];

function matchesLocation(project: ProjectCardType, filter: string): boolean {
  return filter === 'All' || project.location === filter;
}

function matchesConfig(project: ProjectCardType, filter: string): boolean {
  if (filter === 'All') return true;
  return project.configurations.some((c) => c.config_type === filter);
}

function matchesBudget(project: ProjectCardType, filter: string): boolean {
  if (filter === 'All') return true;
  const min = project.price_min;
  switch (filter) {
    case 'Under 50L':
      return min < 5_000_000;
    case '50L - 1Cr':
      return min >= 5_000_000 && min < 10_000_000;
    case '1Cr - 2Cr':
      return min >= 10_000_000 && min < 20_000_000;
    case '2Cr+':
      return min >= 20_000_000;
    default:
      return true;
  }
}

function matchesStage(project: ProjectCardType, filter: string): boolean {
  return filter === 'All' || project.construction_stage === filter;
}

interface ProjectsFilterProps {
  projects: ProjectCardType[];
}

export function ProjectsFilter({ projects }: ProjectsFilterProps) {
  const [locationFilter, setLocationFilter] = useState('All');
  const [configFilter, setConfigFilter] = useState('All');
  const [budgetFilter, setBudgetFilter] = useState('All');
  const [stageFilter, setStageFilter] = useState('All');

  const filtered = projects.filter(
    (p) =>
      matchesLocation(p, locationFilter) &&
      matchesConfig(p, configFilter) &&
      matchesBudget(p, budgetFilter) &&
      matchesStage(p, stageFilter)
  );

  return (
    <>
      {/* Filter bar */}
      <Section className="border-b border-gray-200 py-xl">
        <Container>
          <div className="flex flex-col gap-lg">
            {/* Location */}
            <div>
              <p className="mb-sm text-caption font-semibold uppercase tracking-wider text-gray-500">
                Location
              </p>
              <div className="flex flex-wrap gap-sm" role="listbox" aria-label="Filter by location">
                {LOCATIONS.map((loc) => (
                  <Chip
                    key={loc}
                    selected={locationFilter === loc}
                    onClick={() => setLocationFilter(loc)}
                  >
                    {loc}
                  </Chip>
                ))}
              </div>
            </div>

            {/* Configuration */}
            <div>
              <p className="mb-sm text-caption font-semibold uppercase tracking-wider text-gray-500">
                Configuration
              </p>
              <div className="flex flex-wrap gap-sm" role="listbox" aria-label="Filter by configuration">
                {CONFIGURATIONS.map((cfg) => (
                  <Chip
                    key={cfg}
                    selected={configFilter === cfg}
                    onClick={() => setConfigFilter(cfg)}
                  >
                    {cfg}
                  </Chip>
                ))}
              </div>
            </div>

            {/* Budget */}
            <div>
              <p className="mb-sm text-caption font-semibold uppercase tracking-wider text-gray-500">
                Budget Range
              </p>
              <div className="flex flex-wrap gap-sm" role="listbox" aria-label="Filter by budget">
                {BUDGET_RANGES.map((budget) => (
                  <Chip
                    key={budget}
                    selected={budgetFilter === budget}
                    onClick={() => setBudgetFilter(budget)}
                  >
                    {budget}
                  </Chip>
                ))}
              </div>
            </div>

            {/* Construction Stage */}
            <div>
              <p className="mb-sm text-caption font-semibold uppercase tracking-wider text-gray-500">
                Construction Stage
              </p>
              <div className="flex flex-wrap gap-sm" role="listbox" aria-label="Filter by construction stage">
                {CONSTRUCTION_STAGES.map((stage) => (
                  <Chip
                    key={stage}
                    selected={stageFilter === stage}
                    onClick={() => setStageFilter(stage)}
                  >
                    {stage}
                  </Chip>
                ))}
              </div>
            </div>
          </div>
        </Container>
      </Section>

      {/* Results */}
      <Section>
        <Container>
          <div className="mb-xl">
            <p className="text-base text-gray-500">
              Showing {filtered.length} project{filtered.length !== 1 ? 's' : ''}
            </p>
          </div>

          {filtered.length > 0 ? (
            <div className="grid grid-cols-1 gap-xl sm:grid-cols-2 lg:grid-cols-4">
              {filtered.map((project) => (
                <ProjectCard
                  key={project.id}
                  project={project}
                  showFitScore
                  fitScore={project.fit_score}
                  className="w-full min-w-0"
                />
              ))}
            </div>
          ) : (
            <div className="py-5xl text-center">
              <svg
                width="48"
                height="48"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="mx-auto text-gray-300"
                aria-hidden="true"
              >
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
              </svg>
              <p className="mt-lg text-base text-gray-500">
                No projects match your filters. Try adjusting your criteria.
              </p>
            </div>
          )}

          {/* Pagination placeholder */}
          <div className="mt-3xl flex items-center justify-center gap-sm">
            <button
              type="button"
              disabled
              className="rounded-sm border border-gray-200 px-lg py-sm text-sm text-gray-400"
            >
              Previous
            </button>
            <span className="rounded-sm bg-brand-primary px-lg py-sm text-sm font-medium text-white">
              1
            </span>
            <button
              type="button"
              className="cursor-pointer rounded-sm border border-gray-200 px-lg py-sm text-sm text-gray-700 transition-colors hover:bg-gray-50"
            >
              2
            </button>
            <button
              type="button"
              className="cursor-pointer rounded-sm border border-gray-200 px-lg py-sm text-sm text-gray-700 transition-colors hover:bg-gray-50"
            >
              3
            </button>
            <button
              type="button"
              className="cursor-pointer rounded-sm border border-gray-200 px-lg py-sm text-sm text-gray-700 transition-colors hover:bg-gray-50"
            >
              Next
            </button>
          </div>
        </Container>
      </Section>
    </>
  );
}
