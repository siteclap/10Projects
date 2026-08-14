<?php
/**
 * Comparison REST API controller.
 *
 * Provides endpoints for creating and viewing project comparison sets.
 * Comparisons can be shared publicly via a unique share token.
 *
 * @package TenProjects\API
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

class Comparison_API extends API_Base {

	/**
	 * Minimum number of projects in a comparison.
	 *
	 * @var int
	 */
	const MIN_PROJECTS = 2;

	/**
	 * Maximum number of projects in a comparison.
	 *
	 * @var int
	 */
	const MAX_PROJECTS = 4;

	/**
	 * Register routes.
	 */
	public function register_routes() {

		// GET /comparisons — list customer's comparisons.
		register_rest_route(
			$this->namespace,
			'/comparisons',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_comparisons' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'page'     => array(
							'type'              => 'integer',
							'default'           => 1,
							'sanitize_callback' => 'absint',
						),
						'per_page' => array(
							'type'              => 'integer',
							'default'           => 20,
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// POST /comparisons — create a comparison set.
		register_rest_route(
			$this->namespace,
			'/comparisons',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_comparison' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'project_ids' => array(
							'required'          => true,
							'type'              => 'array',
							'items'             => array(
								'type' => 'integer',
							),
							'validate_callback' => function ( $param ) {
								return is_array( $param ) && ! empty( $param );
							},
							'sanitize_callback' => function ( $param ) {
								return array_map( 'absint', (array) $param );
							},
						),
					),
				),
			)
		);

		// GET /comparisons/<id> — get comparison detail by ID.
		register_rest_route(
			$this->namespace,
			'/comparisons/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_comparison' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && (int) $param > 0;
							},
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// GET /comparisons/share/<token> — get comparison by share token.
		register_rest_route(
			$this->namespace,
			'/comparisons/share/(?P<token>[a-zA-Z0-9]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_comparison_by_token' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => array(
						'token' => array(
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_string( $param ) && strlen( $param ) > 0;
							},
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);
	}

	/**
	 * GET /comparisons
	 *
	 * List the customer's comparison sets.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function list_comparisons( $request ) {
		global $wpdb;

		$customer   = $this->get_current_customer( $request );
		$pagination = $this->get_pagination( $request );

		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}tp_comparisons
				 WHERE customer_id = %d",
				$customer->id
			)
		);

		$comparisons = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_comparisons
				 WHERE customer_id = %d
				 ORDER BY created_at DESC
				 LIMIT %d OFFSET %d",
				$customer->id,
				$pagination['per_page'],
				$pagination['offset']
			)
		);

		$items = array();

		foreach ( $comparisons as $comparison ) {
			$project_ids = json_decode( $comparison->project_ids, true );

			if ( ! is_array( $project_ids ) ) {
				$project_ids = array();
			}

			// Build a summary with project titles.
			$project_summaries = array();
			foreach ( $project_ids as $pid ) {
				$post = get_post( $pid );
				if ( $post && 'tp_project' === $post->post_type ) {
					$project_summaries[] = array(
						'id'    => (int) $pid,
						'title' => $post->post_title,
					);
				}
			}

			$items[] = array(
				'id'           => (int) $comparison->id,
				'project_ids'  => array_map( 'intval', $project_ids ),
				'projects'     => $project_summaries,
				'share_token'  => $comparison->share_token,
				'created_at'   => $comparison->created_at,
			);
		}

		$response = $this->success( $items );

		return $this->add_pagination_headers(
			$response,
			$total,
			$pagination['per_page'],
			$pagination['page']
		);
	}

	/**
	 * POST /comparisons
	 *
	 * Create a new comparison set with 2-4 projects.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_comparison( $request ) {
		global $wpdb;

		$required = $this->validate_required( $request, array( 'project_ids' ) );
		if ( is_wp_error( $required ) ) {
			return $required;
		}

		$customer    = $this->get_current_customer( $request );
		$project_ids = array_map( 'absint', (array) $request->get_param( 'project_ids' ) );

		// Remove duplicates.
		$project_ids = array_values( array_unique( $project_ids ) );

		// Validate project count.
		$count = count( $project_ids );
		if ( $count < self::MIN_PROJECTS || $count > self::MAX_PROJECTS ) {
			return $this->error(
				'invalid_project_count',
				sprintf(
					'A comparison requires between %d and %d projects. You provided %d.',
					self::MIN_PROJECTS,
					self::MAX_PROJECTS,
					$count
				),
				400
			);
		}

		// Verify all projects exist and are published.
		$invalid_ids = array();
		foreach ( $project_ids as $pid ) {
			$post = get_post( $pid );
			if ( ! $post || 'tp_project' !== $post->post_type || 'publish' !== $post->post_status ) {
				$invalid_ids[] = $pid;
			}
		}

		if ( ! empty( $invalid_ids ) ) {
			return $this->error(
				'invalid_projects',
				'One or more project IDs are invalid or not published.',
				400,
				array( 'invalid_ids' => $invalid_ids )
			);
		}

		// Generate a unique share token.
		$share_token = $this->generate_share_token();

		$result = $wpdb->insert(
			$wpdb->prefix . 'tp_comparisons',
			array(
				'customer_id'     => $customer->id,
				'project_ids'     => wp_json_encode( $project_ids ),
				'comparison_data' => null,
				'share_token'     => $share_token,
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		if ( false === $result ) {
			return $this->error( 'create_failed', 'Failed to create comparison.', 500 );
		}

		$comparison_id = $wpdb->insert_id;

		$data = array(
			'id'          => $comparison_id,
			'project_ids' => $project_ids,
			'share_token' => $share_token,
			'created_at'  => current_time( 'mysql' ),
		);

		return $this->success( $data, 201 );
	}

	/**
	 * GET /comparisons/<id>
	 *
	 * Get a comparison with full project data for all projects in the set.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_comparison( $request ) {
		global $wpdb;

		$comparison_id = absint( $request->get_param( 'id' ) );

		$comparison = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_comparisons WHERE id = %d",
				$comparison_id
			)
		);

		if ( ! $comparison ) {
			return $this->error( 'not_found', 'Comparison not found.', 404 );
		}

		// Verify ownership — only the customer who owns this comparison can access it.
		$customer = $this->get_current_customer( $request );
		if ( (int) $comparison->customer_id !== (int) $customer->id ) {
			return new \WP_Error( 'forbidden', 'You do not have access to this comparison.', array( 'status' => 403 ) );
		}

		return $this->success( $this->format_comparison_detail( $comparison ) );
	}

	/**
	 * GET /comparisons/share/<token>
	 *
	 * Get a comparison by its share token with full project data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_comparison_by_token( $request ) {
		global $wpdb;

		$token = sanitize_text_field( $request->get_param( 'token' ) );

		$comparison = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_comparisons WHERE share_token = %s",
				$token
			)
		);

		if ( ! $comparison ) {
			return $this->error( 'not_found', 'Comparison not found.', 404 );
		}

		return $this->success( $this->format_comparison_detail( $comparison ) );
	}

	/**
	 * Format a comparison record into a full detail response with project data.
	 *
	 * @param object $comparison Database row from tp_comparisons.
	 * @return array
	 */
	private function format_comparison_detail( $comparison ) {
		global $wpdb;

		$project_ids = json_decode( $comparison->project_ids, true );

		if ( ! is_array( $project_ids ) ) {
			$project_ids = array();
		}

		$projects = array();

		foreach ( $project_ids as $pid ) {
			$post = get_post( $pid );

			if ( ! $post || 'tp_project' !== $post->post_type || 'publish' !== $post->post_status ) {
				continue;
			}

			$post_id = (int) $pid;

			// Developer info.
			$developer_id   = (int) get_post_meta( $post_id, '_tp_developer_id', true );
			$developer_name = null;
			if ( $developer_id ) {
				$dev_post = get_post( $developer_id );
				if ( $dev_post && 'tp_developer' === $dev_post->post_type ) {
					$developer_name = $dev_post->post_title;
				}
			}

			// Configurations from custom table.
			$configurations = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}tp_project_configurations
					 WHERE project_id = %d ORDER BY price_min ASC",
					$post_id
				)
			);

			$formatted_configs = array();
			foreach ( $configurations as $config ) {
				$formatted_configs[] = array(
					'id'                => (int) $config->id,
					'configuration'     => $config->configuration,
					'carpet_area_min'   => (float) $config->carpet_area_min,
					'carpet_area_max'   => (float) $config->carpet_area_max,
					'built_up_area_min' => (float) $config->built_up_area_min,
					'built_up_area_max' => (float) $config->built_up_area_max,
					'price_min'         => (int) $config->price_min,
					'price_max'         => (int) $config->price_max,
					'price_per_sqft'    => (int) $config->price_per_sqft,
					'inventory_status'  => $config->inventory_status,
					'balconies'         => (int) $config->balconies,
					'bathrooms'         => (int) $config->bathrooms,
					'parking_included'  => (bool) $config->parking_included,
				);
			}

			// Price range from configurations.
			$price_range = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT MIN(price_min) AS min_price, MAX(price_max) AS max_price
					 FROM {$wpdb->prefix}tp_project_configurations
					 WHERE project_id = %d",
					$post_id
				)
			);

			$projects[] = array(
				'id'                     => $post_id,
				'title'                  => $post->post_title,
				'slug'                   => $post->post_name,
				'thumbnail'              => get_the_post_thumbnail_url( $post_id, 'large' ) ?: null,
				'permalink'              => get_permalink( $post_id ),

				// Location.
				'location'               => $this->get_taxonomy_terms( $post_id, 'tp_location_area' ),
				'city'                   => $this->get_taxonomy_terms( $post_id, 'tp_city' ),
				'address'                => get_post_meta( $post_id, '_tp_address', true ) ?: null,

				// Developer.
				'developer_name'         => $developer_name,

				// Pricing.
				'price_range'            => array(
					'min' => $price_range && $price_range->min_price ? (int) $price_range->min_price : null,
					'max' => $price_range && $price_range->max_price ? (int) $price_range->max_price : null,
				),
				'configurations'         => $formatted_configs,

				// Construction.
				'construction_stage'     => get_post_meta( $post_id, '_tp_construction_stage', true ) ?: null,
				'construction_progress'  => (int) get_post_meta( $post_id, '_tp_construction_progress', true ) ?: null,
				'expected_possession'    => get_post_meta( $post_id, '_tp_expected_possession', true ) ?: null,
				'rera_number'            => get_post_meta( $post_id, '_tp_rera_number', true ) ?: null,
				'verified'               => (bool) get_post_meta( $post_id, '_tp_verified', true ),

				// Size.
				'total_towers'           => (int) get_post_meta( $post_id, '_tp_total_towers', true ) ?: null,
				'total_floors'           => (int) get_post_meta( $post_id, '_tp_total_floors', true ) ?: null,
				'total_units'            => (int) get_post_meta( $post_id, '_tp_total_units', true ) ?: null,

				// Amenities.
				'amenities'              => $this->get_taxonomy_terms( $post_id, 'tp_amenity' ),

				// Infrastructure.
				'open_space_ratio'       => (float) get_post_meta( $post_id, '_tp_open_space_ratio', true ) ?: null,
				'density_rating'         => get_post_meta( $post_id, '_tp_density_rating', true ) ?: null,
				'maintenance_estimate'   => (int) get_post_meta( $post_id, '_tp_maintenance_estimate', true ) ?: null,
				'parking_info'           => get_post_meta( $post_id, '_tp_parking_info', true ) ?: null,

				// Scoring.
				'legal_confidence'       => (int) get_post_meta( $post_id, '_tp_legal_confidence', true ) ?: null,
				'possession_confidence'  => (int) get_post_meta( $post_id, '_tp_possession_confidence', true ) ?: null,
				'appreciation_score'     => (int) get_post_meta( $post_id, '_tp_appreciation_score', true ) ?: null,
				'rental_yield_pct'       => (float) get_post_meta( $post_id, '_tp_rental_yield_pct', true ) ?: null,

				// Editorial.
				'highlights'             => json_decode( get_post_meta( $post_id, '_tp_highlights', true ) ?: '[]', true ),
				'pros'                   => json_decode( get_post_meta( $post_id, '_tp_pros', true ) ?: '[]', true ),
				'cons'                   => json_decode( get_post_meta( $post_id, '_tp_cons', true ) ?: '[]', true ),
				'best_for'               => json_decode( get_post_meta( $post_id, '_tp_best_for', true ) ?: '[]', true ),
				'not_for'                => json_decode( get_post_meta( $post_id, '_tp_not_for', true ) ?: '[]', true ),
			);
		}

		return array(
			'id'              => (int) $comparison->id,
			'project_ids'     => array_map( 'intval', $project_ids ),
			'share_token'     => $comparison->share_token,
			'comparison_data' => json_decode( $comparison->comparison_data ?? '{}', true ),
			'projects'        => $projects,
			'created_at'      => $comparison->created_at,
		);
	}

	/**
	 * Generate a unique alphanumeric share token.
	 *
	 * @return string 16-character token.
	 */
	private function generate_share_token() {
		global $wpdb;

		do {
			$token = wp_generate_password( 16, false );

			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}tp_comparisons WHERE share_token = %s",
					$token
				)
			);
		} while ( $exists > 0 );

		return $token;
	}

	/**
	 * Get taxonomy terms for a post as slug/name pairs.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy name.
	 * @return array
	 */
	private function get_taxonomy_terms( $post_id, $taxonomy ) {
		$terms = wp_get_object_terms( $post_id, $taxonomy );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		return array_map(
			function ( $term ) {
				return array(
					'slug' => $term->slug,
					'name' => $term->name,
				);
			},
			$terms
		);
	}
}
