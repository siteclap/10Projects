import { apiClient, apiClientPaginated } from '@/lib/api/client';
import type { PaginatedResponse } from '@/lib/types/api';
import type { ProjectCard } from '@/lib/types/project';

/**
 * A saved project record including the user's optional notes
 * and the timestamp of when it was saved.
 */
export interface SavedProject {
  id: number;
  project_id: number;
  customer_id: number;
  notes: string | null;
  saved_at: string;
  project: ProjectCard;
}

/**
 * Fetch paginated list of the authenticated customer's saved projects.
 *
 * Results are ordered by most recently saved first.
 *
 * @param page     Page number (1-indexed, defaults to 1)
 * @param perPage  Items per page (defaults to 12)
 * @returns        Paginated response with saved project records
 */
export async function getSavedProjects(
  page: number = 1,
  perPage: number = 12
): Promise<PaginatedResponse<SavedProject[]>> {
  return apiClientPaginated<SavedProject[]>('saved-projects', {
    method: 'GET',
    params: {
      page,
      per_page: perPage,
    },
  });
}

/**
 * Save a project to the authenticated customer's collection.
 *
 * @param projectId  The project ID to save
 * @param notes      Optional notes about why this project was saved
 * @returns          The created saved project record
 */
export async function saveProject(
  projectId: number,
  notes?: string
): Promise<SavedProject> {
  const body: Record<string, unknown> = { project_id: projectId };
  if (notes) body.notes = notes;

  return apiClient<SavedProject>('saved-projects', {
    method: 'POST',
    body,
  });
}

/**
 * Remove a project from the authenticated customer's saved collection.
 *
 * @param projectId  The project ID to unsave
 */
export async function unsaveProject(projectId: number): Promise<void> {
  return apiClient<void>(`saved-projects/${projectId}`, {
    method: 'DELETE',
  });
}
