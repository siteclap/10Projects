<?php
/**
 * Recommendation Engine — orchestrates hard filters, scoring, ranking, and top-10 selection.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

use TenProjects\Helpers\Cache_Helper;

class Recommendation_Engine {

    /** @var Scoring_Engine */
    private $scoring;

    public function __construct() {
        $this->scoring = new Scoring_Engine();
    }

    /**
     * Generate recommendations for a requirement.
     *
     * @param object $requirement Requirement DB row.
     * @param int    $limit       Number of results (default 10).
     * @return array Ranked recommendations.
     */
    public function generate( $requirement, $limit = 10 ) {
        // Check cache.
        $cache_key = 'reco_' . $requirement->id . '_' . md5( wp_json_encode( $requirement ) );
        $cached    = Cache_Helper::get( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $req = $this->parse_requirement( $requirement );

        // Step 1: Get all active projects in the city.
        $candidates = $this->get_candidates( $req );

        // Step 2: Apply hard filters.
        $eligible = array();
        foreach ( $candidates as $project_id ) {
            $project_data = $this->scoring->load_project_data( $project_id );
            $configs      = $this->scoring->get_project_configs( $project_id );

            if ( $this->scoring->passes_hard_filters( $project_data, $req, $configs ) ) {
                $eligible[] = array(
                    'project_id'   => $project_id,
                    'project_data' => $project_data,
                    'configs'      => $configs,
                );
            }
        }

        // Step 3: Score all eligible projects.
        $scored = array();
        foreach ( $eligible as $item ) {
            $result   = $this->scoring->score( $item['project_id'], $requirement, $item['project_data'] );
            $scored[] = $result;
        }

        // Step 4: Sort by final score descending.
        usort( $scored, function ( $a, $b ) {
            return $b['final_score'] - $a['final_score'];
        } );

        // Step 5: Take top N.
        $top = array_slice( $scored, 0, $limit );

        // Step 6: Enrich with project details.
        $results = array();
        $rank    = 0;
        foreach ( $top as $item ) {
            $rank++;
            $project_id = $item['project_id'];

            $results[] = array(
                'rank'        => $rank,
                'project_id'  => $project_id,
                'title'       => get_the_title( $project_id ),
                'permalink'   => get_permalink( $project_id ),
                'thumbnail'   => get_the_post_thumbnail_url( $project_id, 'tp-card-thumb' ),
                'final_score' => $item['final_score'],
                'label'       => $item['label'],
                'scores'      => $item['scores'],
                'strengths'   => $this->get_strengths( $item['scores'], $item['weights'] ),
                'tradeoffs'   => $this->get_tradeoffs( $item['scores'], $item['weights'] ),
                'project_meta' => $this->get_card_meta( $project_id ),
            );
        }

        // Step 7: Save scores to DB.
        $this->save_scores( $requirement->id, $results );

        // Step 8: Save recommendation set.
        $reco_id = $this->save_recommendation( $requirement, $results );

        $output = array(
            'recommendation_id' => $reco_id,
            'requirement_id'    => $requirement->id,
            'total_candidates'  => count( $candidates ),
            'total_eligible'    => count( $eligible ),
            'results'           => $results,
        );

        // Cache for 30 minutes.
        Cache_Helper::set( $cache_key, $output, 1800 );

        return $output;
    }

    /**
     * Regenerate recommendations (clear cache and rescore).
     *
     * @param object $requirement Requirement DB row.
     * @param int    $limit       Number of results.
     * @return array
     */
    public function regenerate( $requirement, $limit = 10 ) {
        Cache_Helper::flush_pattern( 'reco_' . $requirement->id );
        Cache_Helper::flush_pattern( 'scores_' . $requirement->id );
        return $this->generate( $requirement, $limit );
    }

    /**
     * Get quick results for Phase 1 (partial data, top 5).
     *
     * @param object $requirement Requirement with only Phase 1 data.
     * @return array Quick match results.
     */
    public function quick_match( $requirement ) {
        return $this->generate( $requirement, 5 );
    }

    /**
     * Get candidate project IDs from the target city.
     *
     * @param array $req Parsed requirement.
     * @return int[] Project IDs.
     */
    private function get_candidates( array $req ) {
        $args = array(
            'post_type'      => 'tp_project',
            'post_status'    => 'publish',
            'posts_per_page' => 500,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => '_tp_status',
                    'value'   => 'active',
                    'compare' => '=',
                ),
            ),
        );

        // City filter via taxonomy.
        if ( ! empty( $req['city'] ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'tp_city',
                    'field'    => 'slug',
                    'terms'    => sanitize_title( $req['city'] ),
                ),
            );
        }

        $query = new \WP_Query( $args );

        return $query->posts;
    }

    /**
     * Identify top 3 strengths (highest scoring weighted categories).
     *
     * @param array $scores  Category scores.
     * @param array $weights Category weights.
     * @return array Top 3 strengths.
     */
    private function get_strengths( array $scores, array $weights ) {
        $weighted = array();
        foreach ( $scores as $cat => $score ) {
            $w = $weights[ $cat ] ?? 0;
            if ( $score >= 70 && $w > 0 ) {
                $weighted[ $cat ] = $score * $w;
            }
        }

        arsort( $weighted );
        $top = array_slice( array_keys( $weighted ), 0, 3 );

        $labels = $this->category_labels();
        $result = array();
        foreach ( $top as $cat ) {
            $result[] = array(
                'category' => $cat,
                'label'    => $labels[ $cat ] ?? $cat,
                'score'    => $scores[ $cat ],
            );
        }

        return $result;
    }

    /**
     * Identify top 2 trade-offs (lowest scoring important categories).
     *
     * @param array $scores  Category scores.
     * @param array $weights Category weights.
     * @return array Trade-offs.
     */
    private function get_tradeoffs( array $scores, array $weights ) {
        $concerns = array();
        foreach ( $scores as $cat => $score ) {
            $w = $weights[ $cat ] ?? 0;
            if ( $score < 60 && $w >= 3 ) {
                $concerns[ $cat ] = $score;
            }
        }

        asort( $concerns );
        $bottom = array_slice( array_keys( $concerns ), 0, 2 );

        $labels = $this->category_labels();
        $result = array();
        foreach ( $bottom as $cat ) {
            $result[] = array(
                'category' => $cat,
                'label'    => $labels[ $cat ] ?? $cat,
                'score'    => $scores[ $cat ],
            );
        }

        return $result;
    }

    /**
     * Get project card meta for result display.
     *
     * @param int $project_id Project ID.
     * @return array
     */
    private function get_card_meta( $project_id ) {
        global $wpdb;

        $meta = array(
            'developer'           => '',
            'location'            => '',
            'construction_stage'  => get_post_meta( $project_id, '_tp_construction_stage', true ),
            'expected_possession' => get_post_meta( $project_id, '_tp_expected_possession', true ),
            'rera_number'         => get_post_meta( $project_id, '_tp_rera_number', true ),
            'railway_distance_km' => get_post_meta( $project_id, '_tp_railway_distance_km', true ),
        );

        // Developer name.
        $dev_id = get_post_meta( $project_id, '_tp_developer_id', true );
        if ( $dev_id ) {
            $meta['developer'] = get_the_title( $dev_id );
        }

        // Location.
        $locations = wp_get_post_terms( $project_id, 'tp_location_area', array( 'fields' => 'names' ) );
        if ( is_array( $locations ) && ! empty( $locations ) ) {
            $meta['location'] = implode( ', ', $locations );
        }

        // Price range from configurations.
        $prices = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT MIN(base_price) as min_price, MAX(base_price) as max_price
                 FROM {$wpdb->prefix}tp_project_configurations
                 WHERE project_id = %d AND base_price > 0",
                $project_id
            )
        );

        $meta['price_min'] = (int) ( $prices->min_price ?? 0 );
        $meta['price_max'] = (int) ( $prices->max_price ?? 0 );

        // Configurations available.
        $configs = wp_get_post_terms( $project_id, 'tp_configuration', array( 'fields' => 'names' ) );
        $meta['configurations'] = is_array( $configs ) ? $configs : array();

        return $meta;
    }

    /**
     * Save individual project scores to DB.
     *
     * @param int   $requirement_id Requirement ID.
     * @param array $results        Scored results.
     */
    private function save_scores( $requirement_id, array $results ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tp_project_scores';

        foreach ( $results as $r ) {
            // Delete existing score for this pair.
            $wpdb->delete( $table, array(
                'requirement_id' => $requirement_id,
                'project_id'     => $r['project_id'],
            ) );

            $scores = $r['scores'];

            $wpdb->insert( $table, array(
                'requirement_id'         => $requirement_id,
                'project_id'             => $r['project_id'],
                'final_score'            => $r['final_score'],
                'budget_fit'             => $scores['budget_fit'] ?? 0,
                'location_fit'           => $scores['location_fit'] ?? 0,
                'configuration_fit'      => $scores['configuration_fit'] ?? 0,
                'carpet_area_fit'        => $scores['carpet_area_fit'] ?? 0,
                'possession_fit'         => $scores['possession_fit'] ?? 0,
                'emi_fit'                => $scores['emi_fit'] ?? 0,
                'commute_fit'            => $scores['commute_fit'] ?? 0,
                'lifestyle_fit'          => $scores['lifestyle_fit'] ?? 0,
                'developer_reliability'  => $scores['developer_reliability'] ?? 0,
                'construction_stage'     => $scores['construction_stage'] ?? 0,
                'legal_confidence'       => $scores['legal_confidence'] ?? 0,
                'resale_liquidity'       => $scores['resale_liquidity'] ?? 0,
                'rental_potential'       => $scores['rental_potential'] ?? 0,
                'appreciation_drivers'   => $scores['appreciation_drivers'] ?? 0,
                'risk_compatibility'     => $scores['risk_compatibility'] ?? 0,
                'infrastructure_potential' => $scores['infrastructure_potential'] ?? 0,
                'family_suitability'     => $scores['family_suitability'] ?? 0,
                'urgency_match'          => $scores['urgency_match'] ?? 0,
                'inventory_availability' => $scores['inventory_availability'] ?? 0,
                'proximity_score'        => $scores['proximity_score'] ?? 0,
                'scored_at'              => current_time( 'mysql' ),
            ) );
        }
    }

    /**
     * Save recommendation set to DB.
     *
     * @param object $requirement Requirement row.
     * @param array  $results     Ranked results.
     * @return int Recommendation ID.
     */
    private function save_recommendation( $requirement, array $results ) {
        global $wpdb;

        $project_ids = wp_list_pluck( $results, 'project_id' );
        $share_token = substr( md5( wp_generate_uuid4() ), 0, 12 );

        $wpdb->insert(
            $wpdb->prefix . 'tp_recommendations',
            array(
                'uuid'            => wp_generate_uuid4(),
                'customer_id'     => $requirement->customer_id,
                'requirement_id'  => $requirement->id,
                'project_ids'     => wp_json_encode( $project_ids ),
                'total_scored'    => count( $results ),
                'share_token'     => $share_token,
                'status'          => 'active',
                'created_at'      => current_time( 'mysql' ),
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Parse requirement JSON fields.
     *
     * @param object $requirement Requirement row.
     * @return array
     */
    private function parse_requirement( $requirement ) {
        $req = (array) $requirement;

        $json_fields = array( 'configurations', 'preferred_locations', 'priorities', 'must_haves' );
        foreach ( $json_fields as $field ) {
            if ( isset( $req[ $field ] ) && is_string( $req[ $field ] ) ) {
                $req[ $field ] = json_decode( $req[ $field ], true ) ?: array();
            }
        }

        return $req;
    }

    /**
     * Human-readable category labels.
     *
     * @return array
     */
    private function category_labels() {
        return array(
            'budget_fit'              => 'Budget Match',
            'location_fit'            => 'Location Match',
            'configuration_fit'       => 'Configuration Match',
            'carpet_area_fit'         => 'Size Match',
            'possession_fit'          => 'Possession Timeline',
            'emi_fit'                 => 'EMI Affordability',
            'commute_fit'             => 'Commute Convenience',
            'lifestyle_fit'           => 'Lifestyle & Amenities',
            'developer_reliability'   => 'Developer Trust',
            'construction_stage'      => 'Construction Progress',
            'legal_confidence'        => 'Legal Safety',
            'resale_liquidity'        => 'Resale Potential',
            'rental_potential'        => 'Rental Income',
            'appreciation_drivers'    => 'Growth Potential',
            'risk_compatibility'      => 'Risk Match',
            'infrastructure_potential' => 'Infrastructure',
            'family_suitability'      => 'Family Friendliness',
            'urgency_match'           => 'Timeline Fit',
            'inventory_availability'  => 'Availability',
            'proximity_score'         => 'Nearby Essentials',
        );
    }

    /**
     * Get recommendation by share token (for public sharing).
     *
     * @param string $token Share token.
     * @return object|null
     */
    public function find_by_share_token( $token ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_recommendations
                 WHERE share_token = %s AND status = 'active'",
                sanitize_text_field( $token )
            )
        );
    }

    /**
     * Get recommendation by ID.
     *
     * @param int $id Recommendation ID.
     * @return object|null
     */
    public function find( $id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_recommendations WHERE id = %d",
                absint( $id )
            )
        );
    }
}
