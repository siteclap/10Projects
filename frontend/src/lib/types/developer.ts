export interface Developer {
  id: number;
  title: string;
  slug: string;
  logo: string | null;
  description: string;
  established_year: number | null;
  total_projects_completed: number;
  total_projects_ongoing: number;
  total_area_developed_sqft: number;
  avg_delivery_delay_months: number;
  rera_compliance_rate: number;
  legal_cases_pending: number;
  customer_rating: number;
  financial_stability: 'strong' | 'moderate' | 'weak' | '';
  tier: 'tier_1' | 'tier_2' | 'tier_3' | '';
  headquarters: string;
  website: string;
}

export interface DeveloperCard {
  id: number;
  title: string;
  slug: string;
  logo: string | null;
  tier: string;
  total_projects_completed: number;
  customer_rating: number;
}
