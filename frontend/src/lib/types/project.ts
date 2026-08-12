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

export type PropertyType = 'buy' | 'rent' | 'commercial' | 'plot' | 'plots' | 'pg' | 'resale';

export interface RentalDetails {
  monthly_rent?: number;
  security_deposit?: number;
  maintenance_charges?: number;
  lock_in_period?: string;
  notice_period?: string;
  available_from?: string;
  tenant_preferred?: string;
  furnishing_status?: string;
  furnishing_details?: string;
  pets_allowed?: string;
  nonveg_allowed?: string;
  water_supply?: string;
  brokerage?: string;
}

export interface CommercialDetails {
  commercial_type?: string;
  building_grade?: string;
  fitout_status?: string;
  commercial_carpet?: number;
  price_per_sqft?: number;
  cam_charges?: number;
  power_load?: string;
  seating_capacity?: number;
  cabins_count?: number;
  washrooms_count?: number;
  hvac_type?: string;
  parking_bays?: number;
  fire_noc?: string;
  lease_term?: string;
  lock_in_period?: string;
  escalation_clause?: string;
}

export interface PlotDetails {
  plot_type?: string;
  plot_area?: number;
  plot_width?: number;
  plot_depth?: number;
  corner_plot?: string;
  road_width?: string;
  sides_open?: string;
  boundary_wall?: string;
  topography?: string;
  fsi?: number;
  permissible_floors?: string;
  water_connection?: string;
  electricity_connection?: string;
  sewage_connection?: string;
  gated_community?: string;
}

export interface PgDetails {
  pg_gender?: string;
  pg_occupant?: string;
  pg_single_rent?: number;
  pg_double_rent?: number;
  pg_triple_rent?: number;
  pg_deposit?: number;
  pg_notice_period?: string;
  pg_meals?: string;
  pg_meal_type?: string;
  pg_kitchen?: string;
  pg_wifi?: string;
  pg_laundry?: string;
  pg_housekeeping?: string;
  pg_ac?: string;
  pg_smoking?: string;
  pg_drinking?: string;
  pg_guests?: string;
  pg_curfew?: string;
}

export interface Project {
  id: number | string;
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
  property_type?: PropertyType;
  railway_distance_km?: number;
  latitude?: number;
  longitude?: number;
  fit_score?: number;
  scores?: ProjectScore;
  strengths?: ScoreHighlight[];
  tradeoffs?: ScoreHighlight[];
  description?: string;
  highlights?: string;
  amenities?: string[];
  pros?: string[];
  cons?: string[];
  land_parcel?: string;
  floors?: string;
  // Gallery & media.
  gallery_images?: string[];
  banner_desktop_images?: string[];
  banner_mobile_images?: string[];
  developer_logo?: string;
  // Contact & extras.
  phone?: string;
  email?: string;
  verified?: boolean;
  status?: string;
  offers?: string[];
  // About Developer / Project Overview.
  developer_name?: string;
  project_location?: string;
  floors_display?: string;
  qr_code?: string[];
  short_overview?: string;
  google_review_rating?: string;
  available_configs_text?: string;
  // Location details.
  address_pin?: string;
  location_advantage_1?: string;
  location_advantage_2?: string;
  location_brief?: string;
  // Category-specific data (populated conditionally by API).
  rental?: RentalDetails;
  commercial?: CommercialDetails;
  plot?: PlotDetails;
  pg?: PgDetails;
}

/**
 * Subset of Project used in list/card views for lighter payloads.
 */
export interface ProjectCard {
  id: number | string;
  title: string;
  slug: string;
  permalink: string;
  thumbnail: string;
  developer: string;
  location: string;
  construction_stage: string;
  expected_possession: string;
  rera_number: string;
  property_type?: PropertyType;
  configurations: ProjectConfiguration[];
  price_min: number;
  price_max: number;
  fit_score?: number;
}
