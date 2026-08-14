<?php
/**
 * Recommendation REST API controller.
 *
 * Handles retrieval, sharing, and regeneration of project recommendations.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

use TenProjects\Services\Recommendation_Engine;
use TenProjects\Services\Customer_Service;

/**
 * Class Recommendation_API
 *
 * Routes:
 *  GET    /recommendations/<id>               — Get recommendation results by ID.
 *  GET    /recommendations/share/<token>       — Get shared results by share token.
 *  POST   /recommendations/regenerate          — Clear cache and rescore.
 */
class Recommendation_API extends API_Base {

	/**
	 * Recommendation engine instance.
	 *
	 * @var Recommendation_Engine
	 */
	private $recommendation_engine;

	/**
	 * Customer service instance.
	 *
	 * @var Customer_Service
	 */
	private $customer_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->recommendation_engine = new Recommendation_Engine();
		$this->customer_service      = new Customer_Service();
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {

		// GET /tenprojects/v1/recommendations/<id>
		register_rest_route(
			$this->namespace,
			'/recommendations/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_recommendation' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'id' => array(
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
							'validate_callback' => function ( $value ) {
								return is_numeric( $value ) && (int) $value > 0;
							},
						),
					),
				),
			)
		);

		// GET /tenprojects/v1/recommendations/share/<token>
		register_rest_route(
			$this->namespace,
			'/recommendations/share/(?P<token>[a-zA-Z0-9]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_shared_recommendation' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => array(
						'token' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return preg_match( '/^[a-zA-Z0-9]+$/', $value ) && strlen( $value ) >= 8;
							},
						),
					),
				),
			)
		);

		// POST /tenprojects/v1/recommendations/regenerate
		register_rest_route(
			$this->namespace,
			'/recommendations/regenerate',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'regenerate_recommendations' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'requirement_id' => array(
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
							'validate_callback' => function ( $value ) {
								return is_numeric( $value ) && (int) $value > 0;
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Get recommendation results by ID.
	 *
	 * Returns the ranked project list with scores, strengths, and tradeoffs.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_recommendation( $request ) {
		$id = $request->get_param( 'id' );

		$recommendation = $this->recommendation_engine->find( $id );
		if ( ! $recommendation ) {
			return $this->error( 'not_found', 'Recommendation not found.', 404 );
		}

		// Verify ownership — only the customer who owns this recommendation can access it.
		$customer = $this->get_current_customer( $request );
		if ( $recommendation && (int) $recommendation->customer_id !== (int) $customer->id ) {
			return new \WP_Error( 'forbidden', 'You do not have access to this recommendation.', array( 'status' => 403 ) );
		}

		if ( 'active' !== $recommendation->status ) {
			return $this->error( 'inactive', 'This recommendation set is no longer active.', 410 );
		}

		$results = $this->build_recommendation_response( $recommendation );

		return $this->success( $results );
	}

	/**
	 * Get shared recommendation results by share token.
	 *
	 * This is the public sharing endpoint. No authentication is required
	 * beyond the share token acting as a capability URL.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_shared_recommendation( $request ) {
		$token = $request->get_param( 'token' );

		$recommendation = $this->recommendation_engine->find_by_share_token( $token );
		if ( ! $recommendation ) {
			return $this->error( 'not_found', 'Shared recommendation not found or has expired.', 404 );
		}

		if ( 'active' !== $recommendation->status ) {
			return $this->error( 'inactive', 'This shared recommendation is no longer available.', 410 );
		}

		// Increment view count.
		$this->increment_share_views( $recommendation->id );

		$results = $this->build_recommendation_response( $recommendation );

		// Add share context.
		$results['is_shared'] = true;

		return $this->success( $results );
	}

	/**
	 * Regenerate recommendations for a requirement.
	 *
	 * Clears caches and rescores all eligible projects against the requirement.
	 * Requires authenticated customer who owns the requirement.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function regenerate_recommendations( $request ) {
		$valid = $this->validate_required( $request, array( 'requirement_id' ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$requirement_id = $request->get_param( 'requirement_id' );
		$customer       = $this->get_current_customer( $request );

		// Customer is guaranteed by customer_permissions, but double-check.
		if ( ! $customer ) {
			return $this->error( 'unauthorized', 'Authentication required.', 401 );
		}

		// Fetch the requirement and verify ownership.
		$requirement = $this->get_requirement( $requirement_id );
		if ( ! $requirement ) {
			return $this->error( 'not_found', 'Requirement not found.', 404 );
		}

		if ( (int) $requirement->customer_id !== (int) $customer->id ) {
			return $this->error( 'forbidden', 'You do not have access to this requirement.', 403 );
		}

		// Regenerate recommendations (clears cache, rescores).
		$results = $this->recommendation_engine->regenerate( $requirement );

		if ( empty( $results ) ) {
			return $this->error( 'generation_failed', 'Could not regenerate recommendations. Please try again.', 500 );
		}

		return $this->success( array(
			'recommendation_id' => $results['recommendation_id'] ?? null,
			'requirement_id'    => $results['requirement_id'] ?? $requirement_id,
			'total_candidates'  => $results['total_candidates'] ?? 0,
			'total_eligible'    => $results['total_eligible'] ?? 0,
			'results'           => $results['results'] ?? array(),
			'regenerated_at'    => current_time( 'mysql' ),
		) );
	}

	/**
	 * Build a standardized recommendation response from a DB row.
	 *
	 * Loads the scored project data from the database and assembles the
	 * full response payload including project details, scores, and metadata.
	 *
	 * @param object $recommendation Recommendation DB row.
	 * @return array Formatted recommendation response.
	 */
	private function build_recommendation_response( $recommendation ) {
		global $wpdb;

		$project_ids = json_decode( $recommendation->project_ids, true ) ?: array();

		// Load scores for these projects from the scores table.
		$results = array();
		$rank    = 0;

		foreach ( $project_ids as $project_id ) {
			$project_id = absint( $project_id );

			// Skip if project no longer exists or is not published.
			if ( 'publish' !== get_post_status( $project_id ) ) {
				continue;
			}

			$rank++;

			// Load the score record.
			$score_row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}tp_project_scores
					 WHERE requirement_id = %d AND project_id = %d
					 ORDER BY scored_at DESC LIMIT 1",
					absint( $recommendation->requirement_id ),
					$project_id
				)
			);

			$scores    = $this->extract_category_scores( $score_row );
			$strengths = $this->identify_strengths( $scores );
			$tradeoffs = $this->identify_tradeoffs( $scores );

			$results[] = array(
				'rank'         => $rank,
				'project_id'   => $project_id,
				'title'        => get_the_title( $project_id ),
				'permalink'    => get_permalink( $project_id ),
				'thumbnail'    => get_the_post_thumbnail_url( $project_id, 'tp-card-thumb' ),
				'final_score'  => $score_row ? (float) $score_row->final_score : 0,
				'scores'       => $scores,
				'strengths'    => $strengths,
				'tradeoffs'    => $tradeoffs,
				'project_meta' => $this->get_project_card_meta( $project_id ),
			);
		}

		return array(
			'recommendation_id' => (int) $recommendation->id,
			'requirement_id'    => (int) $recommendation->requirement_id,
			'customer_id'       => (int) $recommendation->customer_id,
			'total_results'     => count( $results ),
			'share_token'       => $recommendation->share_token,
			'created_at'        => $recommendation->created_at,
			'results'           => $results,
		);
	}

	/**
	 * Extract individual category scores from a score DB row.
	 *
	 * @param object|null $score_row Score DB row.
	 * @return array Category score map.
	 */
	private function extract_category_scores( $score_row ) {
		if ( ! $score_row ) {
			return array();
		}

		$categories = array(
			'budget_fit', 'location_fit', 'configuration_fit', 'carpet_area_fit',
			'possession_fit', 'emi_fit', 'commute_fit', 'lifestyle_fit',
			'developer_reliability', 'construction_stage', 'legal_confidence',
			'resale_liquidity', 'rental_potential', 'appreciation_drivers',
			'risk_compatibility', 'infrastructure_potential', 'family_suitability',
			'urgency_match', 'inventory_availability', 'proximity_score',
		);

		$scores = array();
		foreach ( $categories as $cat ) {
			$scores[ $cat ] = isset( $score_row->$cat ) ? (float) $score_row->$cat : 0;
		}

		return $scores;
	}

	/**
	 * Identify the top 3 strengths from category scores.
	 *
	 * @param array $scores Category scores.
	 * @return array Top strengths with labels.
	 */
	private function identify_strengths( array $scores ) {
		$labels  = $this->category_labels();
		$strong  = array();

		foreach ( $scores as $cat => $score ) {
			if ( $score >= 70 ) {
				$strong[ $cat ] = $score;
			}
		}

		arsort( $strong );
		$top = array_slice( array_keys( $strong ), 0, 3 );

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
	 * Identify the top 2 tradeoffs from category scores.
	 *
	 * @param array $scores Category scores.
	 * @return array Tradeoffs with labels.
	 */
	private function identify_tradeoffs( array $scores ) {
		$labels   = $this->category_labels();
		$concerns = array();

		foreach ( $scores as $cat => $score ) {
			if ( $score < 60 && $score > 0 ) {
				$concerns[ $cat ] = $score;
			}
		}

		asort( $concerns );
		$bottom = array_slice( array_keys( $concerns ), 0, 2 );

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
	 * Get project metadata for the result card display.
	 *
	 * @param int $project_id Project post ID.
	 * @return array Card meta.
	 */
	private function get_project_card_meta( $project_id ) {
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

		// Price range from configurations table.
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
	 * Human-readable category labels.
	 *
	 * @return array Category key => label.
	 */
	private function category_labels() {
		return array(
			'budget_fit'               => 'Budget Match',
			'location_fit'             => 'Location Match',
			'configuration_fit'        => 'Configuration Match',
			'carpet_area_fit'          => 'Size Match',
			'possession_fit'           => 'Possession Timeline',
			'emi_fit'                  => 'EMI Affordability',
			'commute_fit'              => 'Commute Convenience',
			'lifestyle_fit'            => 'Lifestyle & Amenities',
			'developer_reliability'    => 'Developer Trust',
			'construction_stage'       => 'Construction Progress',
			'legal_confidence'         => 'Legal Safety',
			'resale_liquidity'         => 'Resale Potential',
			'rental_potential'         => 'Rental Income',
			'appreciation_drivers'     => 'Growth Potential',
			'risk_compatibility'       => 'Risk Match',
			'infrastructure_potential' => 'Infrastructure',
			'family_suitability'       => 'Family Friendliness',
			'urgency_match'            => 'Timeline Fit',
			'inventory_availability'   => 'Availability',
			'proximity_score'          => 'Nearby Essentials',
		);
	}

	/**
	 * Retrieve a requirement record by ID.
	 *
	 * @param int $requirement_id Requirement ID.
	 * @return object|null Requirement DB row.
	 */
	private function get_requirement( $requirement_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_customer_requirements WHERE id = %d",
				absint( $requirement_id )
			)
		);
	}

	/**
	 * Increment the view count for a shared recommendation.
	 *
	 * @param int $recommendation_id Recommendation ID.
	 */
	private function increment_share_views( $recommendation_id ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}tp_recommendations
				 SET share_views = COALESCE(share_views, 0) + 1
				 WHERE id = %d",
				absint( $recommendation_id )
			)
		);
	}
}
