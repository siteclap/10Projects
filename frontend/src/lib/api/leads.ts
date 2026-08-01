import { apiClient } from '@/lib/api/client';
import type { Lead, CreateLeadParams } from '@/lib/types/lead';

/**
 * Create a new lead for the authenticated customer.
 *
 * Leads are routed to channel partners based on the lead type,
 * project, and customer profile. Quality scoring happens server-side
 * based on assessment completeness and engagement signals.
 *
 * @param data  Lead creation parameters including type and optional project
 * @returns     The created lead record with quality score and classification
 */
export async function createLead(data: CreateLeadParams): Promise<Lead> {
  return apiClient<Lead>('leads', {
    method: 'POST',
    body: data as unknown as Record<string, unknown>,
  });
}

/**
 * Fetch all leads created by the authenticated customer.
 *
 * Returns leads in reverse chronological order. Each lead includes
 * its current routing status and quality classification.
 *
 * @returns  Array of lead records
 */
export async function getLeads(): Promise<Lead[]> {
  return apiClient<Lead[]>('leads', {
    method: 'GET',
  });
}
