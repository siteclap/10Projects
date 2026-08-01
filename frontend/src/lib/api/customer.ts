import { apiClient } from '@/lib/api/client';
import type { Customer } from '@/lib/types/customer';

/**
 * Customer requirements profile returned from the API.
 *
 * Represents the buyer preferences captured during the assessment flow,
 * used by the scoring engine to rank projects.
 */
export interface CustomerRequirements {
  id: number;
  customer_id: number;
  city: string;
  budget_min: number;
  budget_max: number;
  configurations: string[];
  purpose: 'self_use' | 'investment' | 'both';
  possession_timeline: string;
  preferred_locations: string[];
  priorities: string[];
  lifestyle_preferences: string[];
  family_size: number;
  commute_mode: string;
  workplace_location: string;
  created_at: string;
  updated_at: string;
}

/**
 * Partial customer profile fields accepted by the update endpoint.
 */
export interface UpdateProfileData {
  full_name?: string;
  email?: string;
  preferred_contact?: 'whatsapp' | 'call' | 'email';
}

/**
 * Fetch the authenticated customer's profile.
 *
 * Requires a valid auth token in the `tp_auth_token` cookie.
 *
 * @returns  Full customer profile
 */
export async function getProfile(): Promise<Customer> {
  return apiClient<Customer>('customer/profile', {
    method: 'GET',
  });
}

/**
 * Update the authenticated customer's profile.
 *
 * Only the fields provided in `data` will be updated; omitted fields
 * remain unchanged.
 *
 * @param data  Partial profile fields to update
 * @returns     Updated customer profile
 */
export async function updateProfile(data: UpdateProfileData): Promise<Customer> {
  return apiClient<Customer>('customer/profile', {
    method: 'PUT',
    body: data as unknown as Record<string, unknown>,
  });
}

/**
 * Fetch the authenticated customer's assessment requirements.
 *
 * Returns the buyer profile built from assessment answers. This data
 * is used to display preferences on the dashboard and to re-run
 * scoring when requirements are updated.
 *
 * @returns  Customer requirements or null if no assessment completed
 */
export async function getRequirements(): Promise<CustomerRequirements> {
  return apiClient<CustomerRequirements>('customer/requirements', {
    method: 'GET',
  });
}
