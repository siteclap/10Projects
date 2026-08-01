import { apiClient } from '@/lib/api/client';

/**
 * Site visit status lifecycle.
 */
export type SiteVisitStatus = 'pending' | 'confirmed' | 'completed' | 'cancelled';

/**
 * A scheduled site visit record.
 */
export interface SiteVisit {
  id: number;
  customer_id: number;
  project_id: number;
  project_title: string;
  project_location: string;
  project_thumbnail: string;
  scheduled_date: string;
  time_slot: string;
  status: SiteVisitStatus;
  notes: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * Parameters for scheduling a new site visit.
 */
export interface ScheduleSiteVisitData {
  project_id: number;
  scheduled_date: string;
  time_slot: string;
  notes?: string;
}

/**
 * Schedule a new site visit for a project.
 *
 * The visit starts in `pending` status and is confirmed once a channel
 * partner acknowledges the booking.
 *
 * @param data  Site visit scheduling details
 * @returns     The created site visit record
 */
export async function scheduleSiteVisit(
  data: ScheduleSiteVisitData
): Promise<SiteVisit> {
  return apiClient<SiteVisit>('site-visits', {
    method: 'POST',
    body: data as unknown as Record<string, unknown>,
  });
}

/**
 * Fetch all site visits for the authenticated customer.
 *
 * Returns both upcoming and past visits, ordered by scheduled date descending.
 *
 * @returns  Array of site visit records
 */
export async function getSiteVisits(): Promise<SiteVisit[]> {
  return apiClient<SiteVisit[]>('site-visits', {
    method: 'GET',
  });
}

/**
 * Cancel a scheduled site visit.
 *
 * Only visits in `pending` or `confirmed` status can be cancelled.
 *
 * @param id  The site visit ID to cancel
 */
export async function cancelSiteVisit(id: number): Promise<void> {
  return apiClient<void>(`site-visits/${id}`, {
    method: 'DELETE',
  });
}
