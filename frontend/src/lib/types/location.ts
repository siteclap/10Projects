export interface Location {
  id: number;
  title: string;
  slug: string;
  description: string;
  thumbnail: string | null;
  city: string;
  city_slug: string;
  avg_price_psf: number;
  price_trend_1y: number;
  livability_score: number;
  connectivity_score: number;
  infrastructure_score: number;
  project_count: number;
  upcoming_infra: string[];
  nearest_railway: string;
  railway_distance_km: number;
  nearest_metro: string | null;
  metro_distance_km: number | null;
  nearest_highway: string;
  highway_distance_km: number;
  latitude: number | null;
  longitude: number | null;
}

export interface LocationCard {
  id: number;
  title: string;
  slug: string;
  thumbnail: string | null;
  city_slug: string;
  project_count: number;
  avg_price_psf: number;
  livability_score: number;
}
