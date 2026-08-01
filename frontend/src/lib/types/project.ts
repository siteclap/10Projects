/**
 * Project-related types for the TenProjects platform.
 *
 * These map to the WordPress CPT `tp_project` and the scoring engine output.
 */

export interface ProjectConfiguration {
  config_type: string;
  carpet_area_sqft: number;
  base_price: number;
  total_price: number;
  inventory_total: number;
  inventory_available: number;
}

export interface ProjectScore {
  value_for_money: number;
  location_connectivity: number;
  construction_quality: number;
  developer_reputation: number;
  rera_compliance: number;
  possession_timeline: number;
  amenities_lifestyle: number;
  floor_plan_design: number;
  appreciation_potential: number;
  rental_yield: number;
  neighbourhood_safety: number;
  water_supply: number;
  power_backup: number;
  natural_light_ventilation: number;
  parking_ratio: number;
  green_building: number;
  school_proximity: number;
  hospital_proximity: number;
  shopping_proximity: number;
  public_transport: number;
}

export interface ScoreHighlight {
  category: string;
  label: string;
  score: number;
}

export interface Project {
  id: number;
  title: string;
  slug: string;
  permalink: string;
  thumbnail: string;
  developer: string;
  location: string;
  construction_stage: string;
  expected_possession: string;
  rera_number: string;
  configurations: ProjectConfiguration[];
  price_min: number;
  price_max: number;
  railway_distance_km: number;
  latitude: number;
  longitude: number;
  fit_score?: number;
  scores?: ProjectScore;
  strengths?: ScoreHighlight[];
  tradeoffs?: ScoreHighlight[];
}

/**
 * Subset of Project used in list/card views for lighter payloads.
 */
export interface ProjectCard {
  id: number;
  title: string;
  slug: string;
  permalink: string;
  thumbnail: string;
  developer: string;
  location: string;
  construction_stage: string;
  expected_possession: string;
  rera_number: string;
  configurations: ProjectConfiguration[];
  price_min: number;
  price_max: number;
  fit_score?: number;
}
