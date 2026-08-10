import { apiClient, apiClientPaginated } from '@/lib/api/client';
import type { Project, ProjectCard } from '@/lib/types/project';

interface ListProjectsParams {
  city?: string;
  location?: string;
  config?: string;
  budget_range?: string;
  construction_stage?: string;
  page?: number;
  per_page?: number;
  orderby?: 'title' | 'price' | 'possession';
  order?: 'ASC' | 'DESC';
}

/**
 * GET /projects — paginated, filterable project list.
 */
export async function listProjects(params: ListProjectsParams = {}) {
  return apiClientPaginated<ProjectCard[]>('projects', {
    params: params as Record<string, string | number | boolean | undefined>,
    next: { revalidate: 3600 },
  });
}

/**
 * GET /projects/:id — full project detail.
 */
export async function getProject(id: number | string) {
  return apiClient<Project>(`projects/${id}`, {
    next: { revalidate: 1800 },
  });
}

/**
 * GET /projects/:id — full project detail by slug.
 *
 * Since the WP API uses numeric IDs, this fetches the list filtered
 * to a single result and returns the full detail for that project.
 * In practice, pages should use `getProject(id)` when the ID is known.
 */
export async function getProjectBySlug(slug: string, location?: string) {
  const result = await apiClientPaginated<ProjectCard[]>('projects', {
    params: {
      location,
      per_page: 1,
    },
    next: { revalidate: 1800 },
  });

  const match = result.data?.find((p) => p.slug === slug);
  if (!match) return null;

  return getProject(match.id);
}

/**
 * GET /projects/:id/scores/:requirementId — fit score breakdown.
 * Requires authentication.
 */
export async function getProjectScores(projectId: number, requirementId: number) {
  return apiClient<{
    project_id: number;
    requirement_id: number;
    total_fit_score: number;
    category_scores: Record<string, number>;
    weights_used: Record<string, number>;
    match_reasons: string[];
    trade_offs: string[];
    ai_explanation: string | null;
  }>(`projects/${projectId}/scores/${requirementId}`);
}
