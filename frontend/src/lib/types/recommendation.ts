/**
 * Recommendation types for the AI scoring engine results.
 *
 * After assessment completion, the engine scores all eligible projects
 * and returns the top 10 with detailed score breakdowns.
 */

export interface ScoreHighlight {
  category: string;
  label: string;
  score: number;
}

export interface RecommendationResult {
  rank: number;
  project_id: number;
  title: string;
  permalink: string;
  thumbnail: string;
  final_score: number;
  label: string;
  scores: Record<string, number>;
  strengths: ScoreHighlight[];
  tradeoffs: ScoreHighlight[];
  project_meta: Record<string, unknown>;
}

export interface Recommendation {
  id: number;
  requirement_id: number;
  customer_id: number;
  results: RecommendationResult[];
  total_candidates: number;
  total_eligible: number;
}
