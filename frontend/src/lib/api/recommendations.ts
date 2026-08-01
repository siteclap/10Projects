import { apiClient } from '@/lib/api/client';
import type { Recommendation } from '@/lib/types/recommendation';

/**
 * Fetch recommendation results by requirement ID.
 *
 * Returns the scored and ranked project list generated
 * by the scoring engine after assessment completion.
 *
 * @param id  Requirement ID linked to the assessment session
 * @returns   Recommendation with ranked project results
 */
export async function getRecommendations(id: number): Promise<Recommendation> {
  return apiClient<Recommendation>(`recommendations/${id}`, {
    method: 'GET',
  });
}

/**
 * Fetch shared recommendation results by share token.
 *
 * Public endpoint that does not require authentication.
 * Used when a user shares their results page via link.
 *
 * @param token  Share token from the URL
 * @returns      Recommendation with ranked project results
 */
export async function getSharedRecommendations(token: string): Promise<Recommendation> {
  return apiClient<Recommendation>(`recommendations/share/${token}`, {
    method: 'GET',
  });
}

/**
 * Regenerate recommendations for a given requirement.
 *
 * Re-runs the scoring engine against the latest project data
 * using the existing buyer profile. Useful when the user
 * advances to a later assessment phase.
 *
 * @param requirementId  Requirement ID to regenerate scores for
 * @returns              Updated recommendation with re-ranked results
 */
export async function regenerateRecommendations(
  requirementId: number
): Promise<Recommendation> {
  return apiClient<Recommendation>('recommendations/regenerate', {
    method: 'POST',
    body: { requirement_id: requirementId },
  });
}
