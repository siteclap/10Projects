import { apiClient } from '@/lib/api/client';
import type { Comparison } from '@/lib/types/comparison';

/**
 * Create a new comparison from a list of project IDs.
 *
 * The API fetches full project data for each ID, generates
 * a unique share token, and returns the comparison entity.
 *
 * @param projectIds  Array of 2-4 project IDs to compare
 * @returns           Comparison with full project data and share token
 */
export async function createComparison(projectIds: number[]): Promise<Comparison> {
  return apiClient<Comparison>('comparisons', {
    method: 'POST',
    body: { project_ids: projectIds },
  });
}

/**
 * Fetch an existing comparison by its ID.
 *
 * @param id  Comparison ID
 * @returns   Comparison with full project data
 */
export async function getComparison(id: number): Promise<Comparison> {
  return apiClient<Comparison>(`comparisons/${id}`, {
    method: 'GET',
  });
}

/**
 * Fetch a shared comparison by its public share token.
 *
 * Public endpoint that does not require authentication.
 *
 * @param token  Share token from the shared URL
 * @returns      Comparison with full project data
 */
export async function getSharedComparison(token: string): Promise<Comparison> {
  return apiClient<Comparison>(`comparisons/share/${token}`, {
    method: 'GET',
  });
}

/**
 * List all comparisons for the authenticated user.
 *
 * Returns comparisons in reverse chronological order.
 *
 * @returns  Array of comparisons with project data
 */
export async function listComparisons(): Promise<Comparison[]> {
  return apiClient<Comparison[]>('comparisons', {
    method: 'GET',
  });
}
