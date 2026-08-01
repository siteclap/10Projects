export interface Comparison {
  id: number;
  customer_id: number;
  project_ids: number[];
  share_token: string;
  projects: ComparisonProject[];
  created_at: string;
}

export interface ComparisonProject {
  id: number;
  title: string;
  slug: string;
  thumbnail: string | null;
  developer: string;
  location: string;
  configurations: string[];
  price_min: number;
  price_max: number;
  possession: string;
  construction_stage: string;
  rera_number: string;
  amenities: string[];
  railway_distance_km: number;
  fit_score?: number;
}

export interface CreateComparisonParams {
  project_ids: number[];
}
