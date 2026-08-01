<?php
/**
 * Scoring Engine — 20-category deterministic Fit Score system.
 *
 * Scores each project against a customer's requirements across 20 categories,
 * applies weight profiles (end-user vs investor), and returns final 0–100 score.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

use TenProjects\Helpers\EMI_Calculator;
use TenProjects\Helpers\Geo_Helper;

class Scoring_Engine {

    /**
     * Category keys in order.
     *
     * @var string[]
     */
    private const CATEGORIES = array(
        'budget_fit',
        'location_fit',
        'configuration_fit',
        'carpet_area_fit',
        'possession_fit',
        'emi_fit',
        'commute_fit',
        'lifestyle_fit',
        'developer_reliability',
        'construction_stage',
        'legal_confidence',
        'resale_liquidity',
        'rental_potential',
        'appreciation_drivers',
        'risk_compatibility',
        'infrastructure_potential',
        'family_suitability',
        'urgency_match',
        'inventory_availability',
        'proximity_score',
    );

    /**
     * End-user default weights (total = 100).
     *
     * @var array
     */
    private const WEIGHTS_END_USER = array(
        'budget_fit'              => 15,
        'location_fit'            => 12,
        'configuration_fit'       => 8,
        'carpet_area_fit'         => 5,
        'possession_fit'          => 10,
        'emi_fit'                 => 8,
        'commute_fit'             => 8,
        'lifestyle_fit'           => 5,
        'developer_reliability'   => 8,
        'construction_stage'      => 3,
        'legal_confidence'        => 5,
        'resale_liquidity'        => 2,
        'rental_potential'        => 1,
        'appreciation_drivers'    => 2,
        'risk_compatibility'      => 3,
        'infrastructure_potential' => 1,
        'family_suitability'      => 5,
        'urgency_match'           => 2,
        'inventory_availability'  => 2,
        'proximity_score'         => 5,
    );

    /**
     * Investor default weights (total = 100).
     *
     * @var array
     */
    private const WEIGHTS_INVESTOR = array(
        'budget_fit'              => 12,
        'location_fit'            => 8,
        'configuration_fit'       => 5,
        'carpet_area_fit'         => 3,
        'possession_fit'          => 3,
        'emi_fit'                 => 5,
        'commute_fit'             => 0,
        'lifestyle_fit'           => 0,
        'developer_reliability'   => 8,
        'construction_stage'      => 8,
        'legal_confidence'        => 5,
        'resale_liquidity'        => 10,
        'rental_potential'        => 8,
        'appreciation_drivers'    => 12,
        'risk_compatibility'      => 5,
        'infrastructure_potential' => 5,
        'family_suitability'      => 0,
        'urgency_match'           => 3,
        'inventory_availability'  => 3,
        'proximity_score'         => 0,
    );

    /**
     * Priority factor to scoring category mapping.
     *
     * @var array
     */
    private const PRIORITY_MAP = array(
        'best_price'         => 'budget_fit',
        'best_location'      => 'location_fit',
        'trusted_developer'  => 'developer_reliability',
        'early_possession'   => 'possession_fit',
        'family_friendly'    => 'family_suitability',
        'low_risk'           => 'risk_compatibility',
        'entry_price'        => 'budget_fit',
        'high_growth'        => 'appreciation_drivers',
        'rental_income'      => 'rental_potential',
        'emerging_location'  => 'infrastructure_potential',
        'quick_exit'         => 'resale_liquidity',
        'trusted_brand'      => 'developer_reliability',
    );

    /**
     * Score a single project against a requirement.
     *
     * @param int    $project_id     Project post ID.
     * @param object $requirement    Requirement row from tp_customer_requirements.
     * @param array  $project_data   Optional pre-loaded project meta (avoid repeated DB calls).
     * @param array  $developer_data Optional pre-loaded developer meta.
     * @param array  $location_data  Optional pre-loaded location meta.
     * @return array Category scores + final weighted score.
     */
    public function score( $project_id, $requirement, $project_data = null, $developer_data = null, $location_data = null ) {
        // Load project data if not provided.
        if ( null === $project_data ) {
            $project_data = $this->load_project_data( $project_id );
        }

        // Load developer data.
        if ( null === $developer_data && ! empty( $project_data['developer_id'] ) ) {
            $developer_data = $this->load_developer_data( $project_data['developer_id'] );
        }
        $developer_data = $developer_data ?: array();

        // Load location data.
        if ( null === $location_data ) {
            $location_data = $this->load_location_data( $project_id );
        }
        $location_data = $location_data ?: array();

        // Parse requirement JSON fields.
        $req = $this->parse_requirement( $requirement );

        // Score each category.
        $scores = array();
        foreach ( self::CATEGORIES as $category ) {
            $method = 'score_' . $category;
            $scores[ $category ] = method_exists( $this, $method )
                ? $this->clamp( $this->{$method}( $req, $project_data, $developer_data, $location_data ) )
                : 50; // Default for unimplemented categories.
        }

        // Get weights and apply priority adjustments.
        $weights = $this->get_weights( $req );

        // Calculate final score.
        $final = 0;
        $total_weight = array_sum( $weights );
        foreach ( self::CATEGORIES as $category ) {
            $w = $weights[ $category ] ?? 0;
            $final += $scores[ $category ] * ( $w / $total_weight );
        }

        return array(
            'project_id'   => $project_id,
            'scores'       => $scores,
            'weights'      => $weights,
            'final_score'  => (int) round( $final ),
            'label'        => $this->score_label( (int) round( $final ) ),
        );
    }

    /**
     * Check if a project passes all hard filters.
     *
     * @param array $project_data Project data.
     * @param array $req          Parsed requirement.
     * @param array $configs      Project configurations from tp_project_configurations.
     * @return bool True if project passes all filters.
     */
    public function passes_hard_filters( array $project_data, array $req, array $configs = array() ) {
        // City filter.
        if ( ! empty( $req['city'] ) ) {
            $project_cities = wp_get_post_terms( $project_data['id'], 'tp_city', array( 'fields' => 'slugs' ) );
            if ( is_array( $project_cities ) && ! in_array( $req['city'], $project_cities, true ) ) {
                return false;
            }
        }

        // Configuration filter.
        if ( ! empty( $req['configurations'] ) ) {
            $has_matching_config = false;
            foreach ( $configs as $config ) {
                $config_slug = strtolower( str_replace( ' ', '_', $config->configuration ?? '' ) );
                foreach ( $req['configurations'] as $wanted ) {
                    if ( $config_slug === $wanted || str_contains( $config_slug, str_replace( '_bhk', '', $wanted ) ) ) {
                        $has_matching_config = true;
                        break 2;
                    }
                }
            }
            if ( ! $has_matching_config && ! empty( $configs ) ) {
                return false;
            }
        }

        // Budget filter: min price must be ≤ max budget.
        if ( ! empty( $req['budget_maximum'] ) ) {
            $min_price = $this->get_min_price( $configs );
            if ( $min_price > 0 && $min_price > $req['budget_maximum'] * 1.05 ) {
                return false;
            }
        }

        // Inventory: not sold out.
        if ( ( $project_data['status'] ?? '' ) === 'sold_out' ) {
            return false;
        }

        // Active status.
        if ( ! in_array( $project_data['status'] ?? 'active', array( 'active' ), true ) ) {
            return false;
        }

        // RERA check (if must-have).
        if ( in_array( 'rera_registered', $req['must_haves'] ?? array(), true ) ) {
            if ( empty( $project_data['rera_number'] ) ) {
                return false;
            }
        }

        // Possession year filter.
        if ( ! empty( $req['max_possession_year'] ) && ! empty( $project_data['expected_possession'] ) ) {
            $possession_year = (int) gmdate( 'Y', strtotime( $project_data['expected_possession'] ) );
            if ( $possession_year > $req['max_possession_year'] + 1 ) {
                return false;
            }
        }

        return true;
    }

    // ─── Category Scorers ──────────────────────────────────────────────

    /**
     * Category 1: Budget Fit.
     */
    private function score_budget_fit( $req, $project, $developer, $location ) {
        $comfortable = $req['budget_comfortable'] ?? 0;
        $maximum     = $req['budget_maximum'] ?? 0;

        if ( $comfortable <= 0 ) {
            return 50;
        }

        $min_price = $project['min_price'] ?? 0;
        if ( $min_price <= 0 ) {
            return 50;
        }

        if ( $min_price <= $comfortable ) {
            return 100;
        }
        if ( $min_price <= $comfortable * 1.1 ) {
            return 85;
        }
        if ( $maximum > $comfortable && $min_price <= $maximum ) {
            $ratio = ( $min_price - $comfortable ) / ( $maximum - $comfortable );
            return (int) round( 70 - $ratio * 30 );
        }
        if ( $min_price <= $maximum * 1.05 ) {
            return 40;
        }

        return 0;
    }

    /**
     * Category 2: Location Fit.
     */
    private function score_location_fit( $req, $project, $developer, $location ) {
        $preferred    = $req['preferred_locations'] ?? array();
        $alternatives = $req['alternative_locations'] ?? array();

        if ( empty( $preferred ) ) {
            return 70; // No preference = neutral score.
        }

        $project_locations = wp_get_post_terms( $project['id'], 'tp_location_area', array( 'fields' => 'slugs' ) );
        if ( ! is_array( $project_locations ) ) {
            $project_locations = array();
        }

        // Check preferred.
        foreach ( $project_locations as $loc ) {
            if ( in_array( $loc, $preferred, true ) ) {
                $base = 100;
                // Adjust for infrastructure score.
                $infra_score = $location['liveability_score'] ?? 50;
                return $base + (int) round( ( $infra_score - 50 ) * 0.1 );
            }
        }

        // Check alternatives.
        if ( ! empty( $alternatives ) ) {
            foreach ( $project_locations as $loc ) {
                if ( in_array( $loc, $alternatives, true ) ) {
                    return 70;
                }
            }
        }

        // Same city but different location.
        if ( ! empty( $req['location_flexible'] ) ) {
            return 40;
        }

        return 20;
    }

    /**
     * Category 3: Configuration Fit.
     */
    private function score_configuration_fit( $req, $project, $developer, $location ) {
        $wanted = $req['configurations'] ?? array();
        if ( empty( $wanted ) ) {
            return 70;
        }

        $project_configs = wp_get_post_terms( $project['id'], 'tp_configuration', array( 'fields' => 'slugs' ) );
        if ( ! is_array( $project_configs ) ) {
            return 50;
        }

        // Exact match.
        foreach ( $wanted as $w ) {
            $w_slug = sanitize_title( $w );
            foreach ( $project_configs as $pc ) {
                if ( $pc === $w_slug || str_contains( $pc, str_replace( '_', '-', $w ) ) ) {
                    return 100;
                }
            }
        }

        // Project has multiple configs, one matches loosely.
        if ( count( $project_configs ) > 1 ) {
            return 60;
        }

        return 30;
    }

    /**
     * Category 4: Carpet Area Fit.
     */
    private function score_carpet_area_fit( $req, $project, $developer, $location ) {
        $min_area = $req['carpet_area_min'] ?? 0;
        $max_area = $req['carpet_area_max'] ?? 0;

        // If no area preference, infer from config.
        if ( $min_area <= 0 && $max_area <= 0 ) {
            return 70; // No specific requirement.
        }

        $project_area = $project['avg_carpet_area'] ?? 0;
        if ( $project_area <= 0 ) {
            return 50;
        }

        if ( $project_area >= $min_area && ( $max_area <= 0 || $project_area <= $max_area ) ) {
            return 100;
        }

        $range = max( $max_area - $min_area, $min_area * 0.2 );
        $mid   = ( $min_area + $max_area ) / 2;
        $diff  = abs( $project_area - $mid );
        $pct   = $diff / max( $range, 1 );

        if ( $pct <= 0.1 ) {
            return 80;
        }
        if ( $pct <= 0.2 ) {
            return 60;
        }

        return 30;
    }

    /**
     * Category 5: Possession Fit.
     */
    private function score_possession_fit( $req, $project, $developer, $location ) {
        $max_year = $req['max_possession_year'] ?? ( (int) gmdate( 'Y' ) + 5 );
        $pref     = $req['possession_preference'] ?? '';

        $project_possession = $project['expected_possession'] ?? '';
        if ( empty( $project_possession ) ) {
            return 50;
        }

        $possession_year = (int) gmdate( 'Y', strtotime( $project_possession ) );
        $stage = $project['construction_stage'] ?? '';

        // Ready preference.
        if ( $pref === 'ready_now' && $stage === 'ready' ) {
            return 100;
        }

        $score = 50;
        if ( $possession_year <= $max_year ) {
            $years_early = $max_year - $possession_year;
            $score = min( 100, 80 + $years_early * 5 );
        } else {
            $years_late = $possession_year - $max_year;
            $score = max( 0, 60 - $years_late * 20 );
        }

        // Developer delivery track record adjustment.
        $avg_delay = $developer['avg_delay_months'] ?? 0;
        if ( $avg_delay > 24 ) {
            $score -= 30;
        } elseif ( $avg_delay > 12 ) {
            $score -= 15;
        }

        return max( 0, $score );
    }

    /**
     * Category 6: EMI / Funding Fit.
     */
    private function score_emi_fit( $req, $project, $developer, $location ) {
        $emi_comfort = $req['monthly_emi_comfort'] ?? 0;
        $funding     = $req['funding_type'] ?? 'home_loan';

        if ( $funding === 'self_funded' ) {
            return 80; // EMI not relevant.
        }

        $min_price = $project['min_price'] ?? 0;
        if ( $min_price <= 0 || $emi_comfort <= 0 ) {
            return 50;
        }

        // Calculate EMI (80% LTV, 8.5%, 20 years).
        $loan = $min_price * 0.8;
        $emi  = EMI_Calculator::calculate( $loan );

        if ( $emi <= $emi_comfort ) {
            return 100;
        }
        if ( $emi <= $emi_comfort * 1.15 ) {
            return 75;
        }
        if ( $emi <= $emi_comfort * 1.3 ) {
            return 50;
        }

        return 25;
    }

    /**
     * Category 7: Commute Fit.
     */
    private function score_commute_fit( $req, $project, $developer, $location ) {
        // For investors, commute is irrelevant.
        if ( ( $req['purpose'] ?? '' ) === 'investment' ) {
            return 50;
        }

        $railway_km = (float) ( $project['railway_distance_km'] ?? 99 );

        // Use proximity to railway as a commute proxy.
        if ( $railway_km <= 1 ) {
            return 100;
        }
        if ( $railway_km <= 2 ) {
            return 85;
        }
        if ( $railway_km <= 3 ) {
            return 70;
        }
        if ( $railway_km <= 5 ) {
            return 55;
        }

        return 35;
    }

    /**
     * Category 8: Lifestyle Fit (amenities).
     */
    private function score_lifestyle_fit( $req, $project, $developer, $location ) {
        if ( ( $req['purpose'] ?? '' ) === 'investment' ) {
            return 50;
        }

        $project_amenities = wp_get_post_terms( $project['id'], 'tp_amenity', array( 'fields' => 'slugs' ) );
        if ( ! is_array( $project_amenities ) || empty( $project_amenities ) ) {
            return 40;
        }

        $must_haves = $req['must_haves'] ?? array();
        // Filter for amenity-related must-haves.
        $amenity_prefs = array_filter( $must_haves, function ( $m ) {
            return in_array( $m, array( 'gated_community', 'low_density', 'parking' ), true );
        } );

        if ( empty( $amenity_prefs ) ) {
            // Score based on total amenity count.
            $count = count( $project_amenities );
            return min( 100, 40 + $count * 6 );
        }

        $matched = 0;
        foreach ( $amenity_prefs as $pref ) {
            $slug = sanitize_title( $pref );
            if ( in_array( $slug, $project_amenities, true ) ) {
                $matched++;
            }
        }

        return (int) round( ( $matched / count( $amenity_prefs ) ) * 100 );
    }

    /**
     * Category 9: Developer Reliability.
     */
    private function score_developer_reliability( $req, $project, $developer, $location ) {
        if ( empty( $developer ) ) {
            return 40;
        }

        $base = (int) ( $developer['reputation_score'] ?? 50 );

        // Years in business.
        $established = (int) ( $developer['established_year'] ?? (int) gmdate( 'Y' ) );
        $years       = (int) gmdate( 'Y' ) - $established;
        if ( $years >= 10 ) {
            $base += 10;
        } elseif ( $years >= 5 ) {
            $base += 5;
        }

        // Completed projects.
        $completed = (int) ( $developer['completed_projects'] ?? 0 );
        if ( $completed >= 10 ) {
            $base += 10;
        } elseif ( $completed >= 5 ) {
            $base += 5;
        }

        // On-time delivery rate.
        $ontime = (float) ( $developer['ontime_rate'] ?? 0 );
        if ( $ontime > 80 ) {
            $base += 15;
        } elseif ( $ontime >= 50 ) {
            $base += 5;
        } elseif ( $ontime > 0 ) {
            $base -= 20;
        }

        // Litigation.
        $litigation = $developer['litigation_flags'] ?? 'none';
        if ( $litigation === 'none' ) {
            $base += 5;
        } elseif ( $litigation === 'major' ) {
            $base -= 20;
        }

        // Tier bonus.
        $tier = $developer['tier'] ?? 'new';
        if ( $tier === 'tier_1' ) {
            $base += 10;
        } elseif ( $tier === 'tier_2' ) {
            $base += 5;
        }

        return $base;
    }

    /**
     * Category 10: Construction Stage Suitability.
     */
    private function score_construction_stage( $req, $project, $developer, $location ) {
        $purpose = $req['purpose'] ?? 'end_use';
        $risk    = $req['risk_tolerance'] ?? 'balanced';
        $stage   = $project['construction_stage'] ?? 'under_construction';

        // Build lookup table.
        $matrix = array(
            'end_use' => array(
                'low' => array(
                    'ready'     => 100, 'finishing' => 85,
                    'structure' => 70,  'foundation' => 40,
                    'new_launch' => 25,
                ),
                'balanced' => array(
                    'ready'     => 90,  'finishing' => 90,
                    'structure' => 75,  'foundation' => 65,
                    'new_launch' => 55,
                ),
            ),
            'investment' => array(
                'high' => array(
                    'new_launch' => 100, 'foundation' => 90,
                    'structure'  => 70,  'finishing'  => 60,
                    'ready'      => 50,
                ),
                'balanced' => array(
                    'new_launch' => 85, 'foundation' => 80,
                    'structure'  => 75, 'finishing'  => 70,
                    'ready'      => 65,
                ),
            ),
        );

        $purpose_key = $purpose === 'investment' ? 'investment' : 'end_use';
        $risk_key    = $risk;

        // Fallback to balanced if exact risk not found.
        if ( ! isset( $matrix[ $purpose_key ][ $risk_key ] ) ) {
            $risk_key = 'balanced';
        }

        return $matrix[ $purpose_key ][ $risk_key ][ $stage ] ?? 50;
    }

    /**
     * Category 11: Legal & Approval Confidence.
     */
    private function score_legal_confidence( $req, $project, $developer, $location ) {
        $score = 0;

        if ( ! empty( $project['rera_number'] ) ) {
            $score += 30;
        } else {
            $score -= 40;
        }

        $legal_conf = (int) ( $project['legal_confidence'] ?? 0 );
        if ( $legal_conf > 0 ) {
            // Use stored editorial score.
            return $legal_conf;
        }

        // Bank approved.
        $banks = $project['bank_approved'] ?? array();
        if ( is_string( $banks ) ) {
            $banks = json_decode( $banks, true ) ?: array();
        }
        if ( count( $banks ) >= 3 ) {
            $score += 15;
        } elseif ( count( $banks ) >= 1 ) {
            $score += 10;
        }

        // Litigation.
        $litigation = $project['litigation_status'] ?? 'none';
        if ( $litigation === 'none' ) {
            $score += 15;
        } elseif ( $litigation === 'major' ) {
            $score -= 30;
        }

        // Base points for being listed.
        $score += 40;

        return $score;
    }

    /**
     * Category 12: Resale Liquidity.
     */
    private function score_resale_liquidity( $req, $project, $developer, $location ) {
        $score = 0;

        // Location demand.
        $rental_demand = $location['rental_demand'] ?? 'medium';
        $demand_score  = array( 'high' => 30, 'medium' => 20, 'low' => 10 );
        $score += $demand_score[ $rental_demand ] ?? 15;

        // Developer brand.
        $tier = $developer['tier'] ?? 'new';
        $tier_score = array( 'tier_1' => 20, 'tier_2' => 15, 'tier_3' => 10, 'new' => 5 );
        $score += $tier_score[ $tier ] ?? 5;

        // Configuration demand (2BHK most liquid).
        $configs = wp_get_post_terms( $project['id'], 'tp_configuration', array( 'fields' => 'slugs' ) );
        if ( is_array( $configs ) ) {
            if ( in_array( '2-bhk', $configs, true ) || in_array( '2_bhk', $configs, true ) ) {
                $score += 25;
            } elseif ( in_array( '1-bhk', $configs, true ) ) {
                $score += 20;
            } elseif ( in_array( '3-bhk', $configs, true ) ) {
                $score += 15;
            } else {
                $score += 10;
            }
        }

        // Micro-market maturity.
        $resale_liq = $location['resale_liquidity'] ?? 'medium';
        $liq_score  = array( 'high' => 15, 'medium' => 10, 'low' => 5 );
        $score += $liq_score[ $resale_liq ] ?? 8;

        // Price competitiveness.
        $micro_price  = (int) ( $project['micro_market_price'] ?? 0 );
        $project_psf  = $project['avg_price_per_sqft'] ?? 0;
        if ( $micro_price > 0 && $project_psf > 0 ) {
            if ( $project_psf < $micro_price * 0.95 ) {
                $score += 10;
            } elseif ( $project_psf <= $micro_price * 1.05 ) {
                $score += 5;
            }
        }

        return $score;
    }

    /**
     * Category 13: Rental Potential.
     */
    private function score_rental_potential( $req, $project, $developer, $location ) {
        $yield = (float) ( $project['rental_yield_pct'] ?? 0 );

        if ( $yield <= 0 ) {
            // Estimate from location data.
            $rental_max = (int) ( $project['rental_range_max'] ?? 0 );
            $min_price  = $project['min_price'] ?? 0;
            if ( $rental_max > 0 && $min_price > 0 ) {
                $yield = ( $rental_max * 12 ) / $min_price * 100;
            }
        }

        $score = 20;
        if ( $yield > 4 ) {
            $score = 100;
        } elseif ( $yield > 3 ) {
            $score = 80;
        } elseif ( $yield > 2 ) {
            $score = 60;
        } elseif ( $yield > 1 ) {
            $score = 40;
        }

        // Vacancy risk adjustment.
        $vacancy = $project['vacancy_risk'] ?? 'medium';
        if ( $vacancy === 'low' ) {
            $score += 10;
        } elseif ( $vacancy === 'high' ) {
            $score -= 15;
        }

        // Transit connectivity.
        $railway_km = (float) ( $project['railway_distance_km'] ?? 99 );
        if ( $railway_km <= 2 ) {
            $score += 10;
        }

        return $score;
    }

    /**
     * Category 14: Appreciation Drivers.
     */
    private function score_appreciation_drivers( $req, $project, $developer, $location ) {
        $score = 0;

        // Infrastructure catalysts.
        $metro_km   = (float) ( $project['metro_distance_km'] ?? 99 );
        $highway_km = (float) ( $project['highway_distance_km'] ?? 99 );
        $airport_km = (float) ( $project['airport_distance_km'] ?? 99 );

        if ( $metro_km <= 5 ) {
            $score += 20;
        }
        if ( $airport_km <= 15 ) {
            $score += 15;
        }
        if ( $highway_km <= 3 ) {
            $score += 10;
        }

        // Location price trend.
        $cagr = (float) ( $location['price_trend_3yr'] ?? 0 );
        if ( $cagr > 10 ) {
            $score += 15;
        } elseif ( $cagr > 5 ) {
            $score += 10;
        } elseif ( $cagr > 0 ) {
            $score += 5;
        }

        // Price below micro-market average.
        $micro_price = (int) ( $project['micro_market_price'] ?? 0 );
        $project_psf = $project['avg_price_per_sqft'] ?? 0;
        if ( $micro_price > 0 && $project_psf > 0 && $project_psf < $micro_price ) {
            $score += 10;
        }

        // Appreciation score from editorial data.
        $appreciation = (int) ( $project['appreciation_score'] ?? 0 );
        if ( $appreciation > 0 ) {
            $score = (int) round( ( $score + $appreciation ) / 2 );
        }

        return $score;
    }

    /**
     * Category 15: Risk Profile Compatibility.
     */
    private function score_risk_compatibility( $req, $project, $developer, $location ) {
        $customer_risk = $req['risk_tolerance'] ?? 'balanced';

        // Calculate project risk from multiple signals.
        $risk_signals = 0;
        $risk_count   = 0;

        // Developer risk.
        $dev_risk = $developer['financial_risk'] ?? 'medium';
        $risk_map = array( 'low' => 1, 'medium' => 2, 'high' => 3 );
        $risk_signals += $risk_map[ $dev_risk ] ?? 2;
        $risk_count++;

        // Legal risk.
        $litigation = $project['litigation_status'] ?? 'none';
        $lit_risk   = array( 'none' => 1, 'minor' => 2, 'major' => 3 );
        $risk_signals += $lit_risk[ $litigation ] ?? 2;
        $risk_count++;

        // Construction risk.
        $stage = $project['construction_stage'] ?? 'structure';
        $stage_risk = array( 'ready' => 1, 'finishing' => 1, 'structure' => 2, 'foundation' => 3, 'new_launch' => 3 );
        $risk_signals += $stage_risk[ $stage ] ?? 2;
        $risk_count++;

        $avg_risk = $risk_count > 0 ? $risk_signals / $risk_count : 2;
        $project_risk = $avg_risk <= 1.5 ? 'low' : ( $avg_risk <= 2.2 ? 'medium' : 'high' );

        // Compatibility matrix.
        $matrix = array(
            'low'      => array( 'low' => 100, 'medium' => 50, 'high' => 10 ),
            'balanced' => array( 'low' => 90, 'medium' => 100, 'high' => 40 ),
            'high'     => array( 'low' => 70, 'medium' => 90, 'high' => 80 ),
        );

        return $matrix[ $customer_risk ][ $project_risk ] ?? 50;
    }

    /**
     * Category 16: Infrastructure Potential.
     */
    private function score_infrastructure_potential( $req, $project, $developer, $location ) {
        // Use location investment score if available.
        $investment_score = (int) ( $location['investment_score'] ?? 0 );
        if ( $investment_score > 0 ) {
            return $investment_score;
        }

        $score = 30; // Base.

        $metro_km   = (float) ( $project['metro_distance_km'] ?? 99 );
        $airport_km = (float) ( $project['airport_distance_km'] ?? 99 );

        if ( $metro_km <= 3 ) {
            $score += 30;
        } elseif ( $metro_km <= 5 ) {
            $score += 20;
        }

        if ( $airport_km <= 10 ) {
            $score += 20;
        }

        // Location supply pipeline (low supply = good).
        $supply = (int) ( $location['supply_pipeline'] ?? 0 );
        if ( $supply > 0 && $supply < 500 ) {
            $score += 15;
        } elseif ( $supply >= 500 && $supply < 2000 ) {
            $score += 5;
        }

        return $score;
    }

    /**
     * Category 17: Family Suitability.
     */
    private function score_family_suitability( $req, $project, $developer, $location ) {
        if ( ( $req['purpose'] ?? '' ) === 'investment' ) {
            return 50;
        }

        $score = 30;

        $school_km   = (float) ( $project['school_distance_km'] ?? 99 );
        $hospital_km = (float) ( $project['hospital_distance_km'] ?? 99 );

        // School proximity.
        if ( $school_km <= 1 ) {
            $score += 25;
        } elseif ( $school_km <= 3 ) {
            $score += 15;
        } elseif ( $school_km <= 5 ) {
            $score += 5;
        }

        // Hospital proximity.
        if ( $hospital_km <= 2 ) {
            $score += 20;
        } elseif ( $hospital_km <= 5 ) {
            $score += 10;
        }

        // Kid-friendly amenities.
        $amenities = wp_get_post_terms( $project['id'], 'tp_amenity', array( 'fields' => 'slugs' ) );
        if ( is_array( $amenities ) ) {
            if ( in_array( 'kids-play', $amenities, true ) || in_array( 'kids_play', $amenities, true ) ) {
                $score += 10;
            }
            if ( in_array( 'garden', $amenities, true ) ) {
                $score += 5;
            }
            if ( in_array( 'swimming-pool', $amenities, true ) || in_array( 'swimming_pool', $amenities, true ) ) {
                $score += 5;
            }
        }

        // Density.
        $density = $project['density_rating'] ?? 'medium';
        if ( $density === 'low' ) {
            $score += 5;
        }

        return $score;
    }

    /**
     * Category 18: Urgency Match.
     */
    private function score_urgency_match( $req, $project, $developer, $location ) {
        $timeline = $req['purchase_timeline_months'] ?? 6;
        $stage    = $project['construction_stage'] ?? 'structure';

        if ( $timeline <= 1 ) {
            // Need immediately.
            return $stage === 'ready' ? 100 : ( $stage === 'finishing' ? 60 : 30 );
        }
        if ( $timeline <= 3 ) {
            $scores = array( 'ready' => 100, 'finishing' => 90, 'structure' => 60, 'foundation' => 40, 'new_launch' => 30 );
            return $scores[ $stage ] ?? 50;
        }
        if ( $timeline <= 6 ) {
            $scores = array( 'ready' => 85, 'finishing' => 90, 'structure' => 80, 'foundation' => 65, 'new_launch' => 50 );
            return $scores[ $stage ] ?? 60;
        }
        if ( $timeline <= 12 ) {
            return 70; // Any stage works.
        }

        // Just researching — everything is fine.
        return 75;
    }

    /**
     * Category 19: Inventory Availability.
     */
    private function score_inventory_availability( $req, $project, $developer, $location ) {
        $total = (int) ( $project['total_units'] ?? 0 );
        $stage = $project['construction_stage'] ?? '';

        if ( $stage === 'ready' && $total <= 10 ) {
            return 50; // Last few units.
        }
        if ( $total > 100 ) {
            return 100;
        }
        if ( $total > 50 ) {
            return 85;
        }
        if ( $total > 20 ) {
            return 70;
        }
        if ( $total > 0 ) {
            return 50;
        }

        return 30; // No data.
    }

    /**
     * Category 20: Proximity Score.
     */
    private function score_proximity_score( $req, $project, $developer, $location ) {
        if ( ( $req['purpose'] ?? '' ) === 'investment' ) {
            return 50;
        }

        $must_haves = $req['must_haves'] ?? array();
        $score      = 50;
        $checks     = 0;
        $points     = 0;

        // Railway proximity.
        if ( in_array( 'near_railway', $must_haves, true ) ) {
            $checks++;
            $km = (float) ( $project['railway_distance_km'] ?? 99 );
            if ( $km <= 1 ) {
                $points += 100;
            } elseif ( $km <= 2 ) {
                $points += 80;
            } elseif ( $km <= 3 ) {
                $points += 60;
            } else {
                $points += 20;
            }
        }

        // School proximity.
        if ( in_array( 'near_school', $must_haves, true ) ) {
            $checks++;
            $km = (float) ( $project['school_distance_km'] ?? 99 );
            if ( $km <= 1 ) {
                $points += 100;
            } elseif ( $km <= 2 ) {
                $points += 80;
            } elseif ( $km <= 3 ) {
                $points += 60;
            } else {
                $points += 20;
            }
        }

        // Hospital proximity.
        if ( in_array( 'near_hospital', $must_haves, true ) ) {
            $checks++;
            $km = (float) ( $project['hospital_distance_km'] ?? 99 );
            if ( $km <= 2 ) {
                $points += 100;
            } elseif ( $km <= 3 ) {
                $points += 80;
            } elseif ( $km <= 5 ) {
                $points += 60;
            } else {
                $points += 20;
            }
        }

        if ( $checks > 0 ) {
            $score = (int) round( $points / $checks );
        } else {
            // General proximity based on available data.
            $railway_km  = (float) ( $project['railway_distance_km'] ?? 99 );
            $school_km   = (float) ( $project['school_distance_km'] ?? 99 );
            $hospital_km = (float) ( $project['hospital_distance_km'] ?? 99 );
            $mall_km     = (float) ( $project['mall_distance_km'] ?? 99 );

            $total = 0;
            $count = 0;
            foreach ( array( $railway_km, $school_km, $hospital_km, $mall_km ) as $km ) {
                if ( $km < 99 ) {
                    $count++;
                    if ( $km <= 1 ) {
                        $total += 100;
                    } elseif ( $km <= 3 ) {
                        $total += 75;
                    } elseif ( $km <= 5 ) {
                        $total += 50;
                    } else {
                        $total += 25;
                    }
                }
            }
            if ( $count > 0 ) {
                $score = (int) round( $total / $count );
            }
        }

        return $score;
    }

    // ─── Helpers ────────────────────────────────────────────────────────

    /**
     * Get weight profile based on requirement.
     *
     * @param array $req Parsed requirement.
     * @return array Weights per category.
     */
    private function get_weights( array $req ) {
        $purpose = $req['purpose'] ?? 'end_use';
        $weights = $purpose === 'investment' ? self::WEIGHTS_INVESTOR : self::WEIGHTS_END_USER;

        // Apply priority-based multipliers.
        $priorities = $req['priorities'] ?? array();
        $multipliers = array( 1 => 1.5, 2 => 1.3, 3 => 1.2, 4 => 1.1, 5 => 1.05 );

        foreach ( $priorities as $p ) {
            $factor = $p['factor'] ?? '';
            $rank   = (int) ( $p['rank'] ?? 0 );
            $mult   = $multipliers[ $rank ] ?? 1.0;

            $category = self::PRIORITY_MAP[ $factor ] ?? '';
            if ( $category && isset( $weights[ $category ] ) ) {
                $weights[ $category ] = $weights[ $category ] * $mult;
            }
        }

        // Renormalize to 100.
        $total = array_sum( $weights );
        if ( $total > 0 && abs( $total - 100 ) > 0.01 ) {
            $factor = 100 / $total;
            foreach ( $weights as $key => $w ) {
                $weights[ $key ] = $w * $factor;
            }
        }

        return $weights;
    }

    /**
     * Parse requirement JSON fields into usable array.
     *
     * @param object $requirement Requirement DB row.
     * @return array Parsed data.
     */
    private function parse_requirement( $requirement ) {
        $req = (array) $requirement;

        // Decode JSON fields.
        $json_fields = array( 'configurations', 'preferred_locations', 'priorities', 'must_haves' );
        foreach ( $json_fields as $field ) {
            if ( isset( $req[ $field ] ) && is_string( $req[ $field ] ) ) {
                $req[ $field ] = json_decode( $req[ $field ], true ) ?: array();
            }
        }

        // Derive risk tolerance from priorities.
        if ( ! isset( $req['risk_tolerance'] ) ) {
            $req['risk_tolerance'] = 'balanced';
            $prios = $req['priorities'] ?? array();
            foreach ( $prios as $p ) {
                if ( ( $p['factor'] ?? '' ) === 'low_risk' && ( $p['rank'] ?? 99 ) <= 3 ) {
                    $req['risk_tolerance'] = 'low';
                    break;
                }
            }
        }

        return $req;
    }

    /**
     * Load all project meta as flat array.
     *
     * @param int $project_id Project post ID.
     * @return array Project data.
     */
    public function load_project_data( $project_id ) {
        $meta = get_post_meta( $project_id );
        $data = array( 'id' => $project_id );

        $keys = array(
            'rera_number', 'developer_id', 'latitude', 'longitude',
            'construction_stage', 'construction_progress', 'expected_possession',
            'total_units', 'density_rating', 'legal_confidence', 'litigation_status',
            'bank_approved', 'railway_distance_km', 'metro_distance_km',
            'highway_distance_km', 'airport_distance_km', 'school_distance_km',
            'hospital_distance_km', 'mall_distance_km', 'employment_hub_km',
            'micro_market_price', 'rental_range_min', 'rental_range_max',
            'vacancy_risk', 'appreciation_score', 'rental_yield_pct',
            'status', 'sponsored',
        );

        foreach ( $keys as $key ) {
            $meta_key = '_tp_' . $key;
            $data[ $key ] = isset( $meta[ $meta_key ] ) ? $meta[ $meta_key ][0] : null;
        }

        // Get min price from configurations table.
        $data['min_price']       = $this->get_project_min_price( $project_id );
        $data['avg_carpet_area'] = $this->get_project_avg_area( $project_id );
        $data['avg_price_per_sqft'] = $this->get_project_avg_psf( $project_id );

        return $data;
    }

    /**
     * Load developer meta data.
     *
     * @param int $developer_id Developer post ID.
     * @return array
     */
    private function load_developer_data( $developer_id ) {
        $meta = get_post_meta( $developer_id );
        $data = array( 'id' => $developer_id );

        $keys = array(
            'established_year'   => 'dev_established_year',
            'total_projects'     => 'dev_total_projects',
            'completed_projects' => 'dev_completed_projects',
            'ontime_rate'        => 'dev_ontime_rate',
            'avg_delay_months'   => 'dev_avg_delay_months',
            'litigation_flags'   => 'dev_litigation_flags',
            'financial_risk'     => 'dev_financial_risk',
            'reputation_score'   => 'dev_reputation_score',
            'tier'               => 'dev_tier',
        );

        foreach ( $keys as $field => $meta_suffix ) {
            $meta_key = '_tp_' . $meta_suffix;
            $data[ $field ] = isset( $meta[ $meta_key ] ) ? $meta[ $meta_key ][0] : null;
        }

        return $data;
    }

    /**
     * Load location data for a project.
     *
     * @param int $project_id Project post ID.
     * @return array
     */
    private function load_location_data( $project_id ) {
        // Get project's location area terms.
        $locations = wp_get_post_terms( $project_id, 'tp_location_area', array( 'fields' => 'slugs' ) );
        if ( ! is_array( $locations ) || empty( $locations ) ) {
            return array();
        }

        // Find the location CPT post matching this area.
        $loc_query = new \WP_Query( array(
            'post_type'      => 'tp_location',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'name'           => $locations[0],
        ) );

        if ( ! $loc_query->have_posts() ) {
            return array();
        }

        $loc_id = $loc_query->posts[0]->ID;
        $meta   = get_post_meta( $loc_id );
        $data   = array( 'id' => $loc_id );

        $keys = array(
            'price_trend_3yr'    => 'loc_price_trend_3yr',
            'liveability_score'  => 'loc_liveability_score',
            'investment_score'   => 'loc_investment_score',
            'rental_demand'      => 'loc_rental_demand',
            'resale_liquidity'   => 'loc_resale_liquidity',
            'supply_pipeline'    => 'loc_supply_pipeline',
        );

        foreach ( $keys as $field => $meta_suffix ) {
            $meta_key = '_tp_' . $meta_suffix;
            $data[ $field ] = isset( $meta[ $meta_key ] ) ? $meta[ $meta_key ][0] : null;
        }

        return $data;
    }

    /**
     * Get minimum price from project configurations.
     *
     * @param int $project_id Project ID.
     * @return int Minimum price or 0.
     */
    private function get_project_min_price( $project_id ) {
        global $wpdb;

        $price = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MIN(base_price) FROM {$wpdb->prefix}tp_project_configurations
                 WHERE project_id = %d AND base_price > 0",
                $project_id
            )
        );

        return (int) ( $price ?: 0 );
    }

    /**
     * Get average carpet area from project configurations.
     *
     * @param int $project_id Project ID.
     * @return float
     */
    private function get_project_avg_area( $project_id ) {
        global $wpdb;

        $area = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT AVG(carpet_area_sqft) FROM {$wpdb->prefix}tp_project_configurations
                 WHERE project_id = %d AND carpet_area_sqft > 0",
                $project_id
            )
        );

        return (float) ( $area ?: 0 );
    }

    /**
     * Get average price per sqft from project configurations.
     *
     * @param int $project_id Project ID.
     * @return float
     */
    private function get_project_avg_psf( $project_id ) {
        global $wpdb;

        $psf = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT AVG(base_price / NULLIF(carpet_area_sqft, 0))
                 FROM {$wpdb->prefix}tp_project_configurations
                 WHERE project_id = %d AND carpet_area_sqft > 0 AND base_price > 0",
                $project_id
            )
        );

        return (float) ( $psf ?: 0 );
    }

    /**
     * Get minimum price from configuration rows.
     *
     * @param array $configs Configuration objects.
     * @return int
     */
    private function get_min_price( array $configs ) {
        $min = PHP_INT_MAX;
        foreach ( $configs as $c ) {
            $price = (int) ( $c->base_price ?? 0 );
            if ( $price > 0 && $price < $min ) {
                $min = $price;
            }
        }
        return $min === PHP_INT_MAX ? 0 : $min;
    }

    /**
     * Clamp score to 0–100.
     *
     * @param int $score Raw score.
     * @return int Clamped score.
     */
    private function clamp( $score ) {
        return max( 0, min( 100, (int) round( $score ) ) );
    }

    /**
     * Get human-readable label for a score.
     *
     * @param int $score Score 0-100.
     * @return string Label.
     */
    private function score_label( $score ) {
        if ( $score >= 90 ) {
            return 'Excellent Fit';
        }
        if ( $score >= 80 ) {
            return 'Very Good Fit';
        }
        if ( $score >= 70 ) {
            return 'Good Fit';
        }
        if ( $score >= 60 ) {
            return 'Moderate Fit';
        }
        if ( $score >= 50 ) {
            return 'Partial Fit';
        }
        return 'Weak Fit';
    }

    /**
     * Get project configurations from DB.
     *
     * @param int $project_id Project ID.
     * @return array Configuration objects.
     */
    public function get_project_configs( $project_id ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_project_configurations
                 WHERE project_id = %d",
                $project_id
            )
        );
    }
}
