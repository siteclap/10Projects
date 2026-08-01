<?php
/**
 * Project REST API controller.
 *
 * Provides public endpoints for browsing and viewing project listings,
 * and an authenticated endpoint for viewing fit-score breakdowns.
 *
 * @package TenProjects\API
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

class Project_API extends API_Base {

	/**
	 * Register routes.
	 */
	public function register_routes() {

		// GET /projects — paginated, filterable project list.
		register_rest_route(
			$this->namespace,
			'/projects',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_projects' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => $this->get_list_args(),
				),
			)
		);

		// GET /projects/<id> — full project detail.
		register_rest_route(
			$this->namespace,
			'/projects/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_project' ),
					'permission_callback' => array( $this, 'public_permissions' ),
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

		// GET /projects/<id>/scores/<requirement_id> — fit score breakdown.
		register_rest_route(
			$this->namespace,
			'/projects/(?P<id>\d+)/scores/(?P<requirement_id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_project_scores' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'id'             => array(
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && (int) $param > 0;
							},
							'sanitize_callback' => 'absint',
						),
						'requirement_id' => array(
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
	}

	/**
	 * Define query parameters for the project list endpoint.
	 *
	 * @return array
	 */
	private function get_list_args() {
		return array(
			'city'               => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'location'           => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'config'             => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'budget_range'       => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'construction_stage' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'page'               => array(
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page'           => array(
				'type'              => 'integer',
				'default'           => 20,
				'sanitize_callback' => 'absint',
			),
			'orderby'            => array(
				'type'              => 'string',
				'default'           => 'title',
				'enum'              => array( 'title', 'price', 'possession' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'order'              => array(
				'type'              => 'string',
				'default'           => 'ASC',
				'enum'              => array( 'ASC', 'DESC' ),
				'sanitize_callback' => function ( $value ) {
					return in_array( strtoupper( $value ), array( 'ASC', 'DESC' ), true )
						? strtoupper( $value )
						: 'ASC';
				},
			),
		);
	}

	/**
	 * GET /projects
	 *
	 * Paginated project listing with taxonomy and meta filters.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function list_projects( $request ) {
		$pagination = $this->get_pagination( $request );

		$query_args = array(
			'post_type'      => 'tp_project',
			'post_status'    => 'publish',
			'posts_per_page' => $pagination['per_page'],
			'paged'          => $pagination['page'],
		);

		// Build taxonomy queries.
		$tax_query = array();

		$tax_map = array(
			'city'               => 'tp_city',
			'location'           => 'tp_location_area',
			'config'             => 'tp_configuration',
			'budget_range'       => 'tp_budget_range',
			'construction_stage' => 'tp_construction_stage',
		);

		foreach ( $tax_map as $param => $taxonomy ) {
			$value = $request->get_param( $param );
			if ( ! empty( $value ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $value ),
				);
			}
		}

		if ( ! empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
			$query_args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		// Handle orderby.
		$orderby = $request->get_param( 'orderby' ) ?: 'title';
		$order   = $request->get_param( 'order' ) ?: 'ASC';

		switch ( $orderby ) {
			case 'price':
				$query_args['meta_key'] = '_tp_price_display_min'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$query_args['orderby']  = 'meta_value_num';
				$query_args['order']    = $order;
				break;

			case 'possession':
				$query_args['meta_key'] = '_tp_expected_possession'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$query_args['orderby']  = 'meta_value';
				$query_args['order']    = $order;
				break;

			default:
				$query_args['orderby'] = 'title';
				$query_args['order']   = $order;
				break;
		}

		$query = new \WP_Query( $query_args );
		$items = array();

		if ( $query->have_posts() ) {
			global $wpdb;

			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				$items[] = $this->format_project_card( $post_id );
			}
			wp_reset_postdata();
		}

		$response = $this->success( $items );

		return $this->add_pagination_headers(
			$response,
			$query->found_posts,
			$pagination['per_page'],
			$pagination['page']
		);
	}

	/**
	 * GET /projects/<id>
	 *
	 * Full project detail including meta, developer, configurations, taxonomies.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_project( $request ) {
		$post_id = $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post || 'tp_project' !== $post->post_type || 'publish' !== $post->post_status ) {
			return $this->error( 'not_found', 'Project not found.', 404 );
		}

		global $wpdb;

		// Developer info.
		$developer_id = (int) get_post_meta( $post_id, '_tp_developer_id', true );
		$developer    = null;

		if ( $developer_id ) {
			$dev_post = get_post( $developer_id );
			if ( $dev_post && 'tp_developer' === $dev_post->post_type ) {
				$developer = array(
					'id'                 => $dev_post->ID,
					'name'               => $dev_post->post_title,
					'logo'               => get_the_post_thumbnail_url( $dev_post->ID, 'thumbnail' ) ?: null,
					'established_year'   => (int) get_post_meta( $developer_id, '_tp_dev_established_year', true ) ?: null,
					'headquarters'       => get_post_meta( $developer_id, '_tp_dev_headquarters', true ) ?: null,
					'website'            => get_post_meta( $developer_id, '_tp_dev_website', true ) ?: null,
					'total_projects'     => (int) get_post_meta( $developer_id, '_tp_dev_total_projects', true ) ?: null,
					'completed_projects' => (int) get_post_meta( $developer_id, '_tp_dev_completed_projects', true ) ?: null,
					'ontime_rate'        => (float) get_post_meta( $developer_id, '_tp_dev_ontime_rate', true ) ?: null,
					'reputation_score'   => (int) get_post_meta( $developer_id, '_tp_dev_reputation_score', true ) ?: null,
					'tier'               => get_post_meta( $developer_id, '_tp_dev_tier', true ) ?: null,
					'customer_rating'    => (float) get_post_meta( $developer_id, '_tp_dev_customer_rating', true ) ?: null,
				);
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
				'floor_availability' => $config->floor_availability,
				'inventory_status'  => $config->inventory_status,
				'unit_count'        => (int) $config->unit_count,
				'views_available'   => $config->views_available,
				'facing'            => $config->facing,
				'balconies'         => (int) $config->balconies,
				'bathrooms'         => (int) $config->bathrooms,
				'parking_included'  => (bool) $config->parking_included,
			);
		}

		// Taxonomies.
		$taxonomies = array(
			'city'               => $this->get_taxonomy_terms( $post_id, 'tp_city' ),
			'location'           => $this->get_taxonomy_terms( $post_id, 'tp_location_area' ),
			'micro_location'     => $this->get_taxonomy_terms( $post_id, 'tp_micro_location' ),
			'configurations'     => $this->get_taxonomy_terms( $post_id, 'tp_configuration' ),
			'budget_range'       => $this->get_taxonomy_terms( $post_id, 'tp_budget_range' ),
			'construction_stage' => $this->get_taxonomy_terms( $post_id, 'tp_construction_stage' ),
			'possession_year'    => $this->get_taxonomy_terms( $post_id, 'tp_possession_year' ),
			'property_type'      => $this->get_taxonomy_terms( $post_id, 'tp_property_type' ),
			'amenities'          => $this->get_taxonomy_terms( $post_id, 'tp_amenity' ),
		);

		// Build full detail response.
		$data = array(
			'id'                     => $post_id,
			'title'                  => $post->post_title,
			'slug'                   => $post->post_name,
			'excerpt'                => $post->post_excerpt,
			'content'                => apply_filters( 'the_content', $post->post_content ),
			'thumbnail'              => get_the_post_thumbnail_url( $post_id, 'large' ) ?: null,
			'permalink'              => get_permalink( $post_id ),
			'published_at'           => $post->post_date,

			// Basic info.
			'rera_number'            => get_post_meta( $post_id, '_tp_rera_number', true ) ?: null,
			'rera_phase'             => get_post_meta( $post_id, '_tp_rera_phase', true ) ?: null,
			'status'                 => get_post_meta( $post_id, '_tp_status', true ) ?: null,
			'sponsored'              => (bool) get_post_meta( $post_id, '_tp_sponsored', true ),
			'sponsor_label'          => get_post_meta( $post_id, '_tp_sponsor_label', true ) ?: null,
			'launch_date'            => get_post_meta( $post_id, '_tp_launch_date', true ) ?: null,
			'verified'               => (bool) get_post_meta( $post_id, '_tp_verified', true ),
			'price_display_min'      => (int) get_post_meta( $post_id, '_tp_price_display_min', true ) ?: null,
			'price_display_max'      => (int) get_post_meta( $post_id, '_tp_price_display_max', true ) ?: null,
			'primary_config'         => get_post_meta( $post_id, '_tp_primary_config', true ) ?: null,

			// Construction.
			'construction_stage'     => get_post_meta( $post_id, '_tp_construction_stage', true ) ?: null,
			'construction_progress'  => (int) get_post_meta( $post_id, '_tp_construction_progress', true ) ?: null,
			'construction_start'     => get_post_meta( $post_id, '_tp_construction_start', true ) ?: null,
			'rera_registration_date' => get_post_meta( $post_id, '_tp_rera_registration_date', true ) ?: null,
			'promised_possession'    => get_post_meta( $post_id, '_tp_promised_possession', true ) ?: null,
			'rera_possession'        => get_post_meta( $post_id, '_tp_rera_possession', true ) ?: null,
			'expected_possession'    => get_post_meta( $post_id, '_tp_expected_possession', true ) ?: null,
			'total_towers'           => (int) get_post_meta( $post_id, '_tp_total_towers', true ) ?: null,
			'total_floors'           => (int) get_post_meta( $post_id, '_tp_total_floors', true ) ?: null,
			'total_units'            => (int) get_post_meta( $post_id, '_tp_total_units', true ) ?: null,

			// Location.
			'address'                => get_post_meta( $post_id, '_tp_address', true ) ?: null,
			'latitude'               => (float) get_post_meta( $post_id, '_tp_latitude', true ) ?: null,
			'longitude'              => (float) get_post_meta( $post_id, '_tp_longitude', true ) ?: null,
			'railway_distance_km'    => (float) get_post_meta( $post_id, '_tp_railway_distance_km', true ) ?: null,
			'metro_distance_km'      => (float) get_post_meta( $post_id, '_tp_metro_distance_km', true ) ?: null,
			'highway_distance_km'    => (float) get_post_meta( $post_id, '_tp_highway_distance_km', true ) ?: null,
			'airport_distance_km'    => (float) get_post_meta( $post_id, '_tp_airport_distance_km', true ) ?: null,
			'school_distance_km'     => (float) get_post_meta( $post_id, '_tp_school_distance_km', true ) ?: null,
			'hospital_distance_km'   => (float) get_post_meta( $post_id, '_tp_hospital_distance_km', true ) ?: null,
			'mall_distance_km'       => (float) get_post_meta( $post_id, '_tp_mall_distance_km', true ) ?: null,
			'employment_hub_km'      => (float) get_post_meta( $post_id, '_tp_employment_hub_km', true ) ?: null,

			// Scoring data.
			'legal_confidence'       => (int) get_post_meta( $post_id, '_tp_legal_confidence', true ) ?: null,
			'possession_confidence'  => (int) get_post_meta( $post_id, '_tp_possession_confidence', true ) ?: null,
			'bank_approved'          => get_post_meta( $post_id, '_tp_bank_approved', true ) ?: null,
			'litigation_status'      => get_post_meta( $post_id, '_tp_litigation_status', true ) ?: null,
			'micro_market_price'     => (int) get_post_meta( $post_id, '_tp_micro_market_price', true ) ?: null,
			'rental_range_min'       => (int) get_post_meta( $post_id, '_tp_rental_range_min', true ) ?: null,
			'rental_range_max'       => (int) get_post_meta( $post_id, '_tp_rental_range_max', true ) ?: null,
			'vacancy_risk'           => get_post_meta( $post_id, '_tp_vacancy_risk', true ) ?: null,
			'appreciation_score'     => (int) get_post_meta( $post_id, '_tp_appreciation_score', true ) ?: null,
			'rental_yield_pct'       => (float) get_post_meta( $post_id, '_tp_rental_yield_pct', true ) ?: null,

			// Editorial (JSON-encoded lists).
			'highlights'             => json_decode( get_post_meta( $post_id, '_tp_highlights', true ) ?: '[]', true ),
			'pros'                   => json_decode( get_post_meta( $post_id, '_tp_pros', true ) ?: '[]', true ),
			'cons'                   => json_decode( get_post_meta( $post_id, '_tp_cons', true ) ?: '[]', true ),
			'risks'                  => json_decode( get_post_meta( $post_id, '_tp_risks', true ) ?: '[]', true ),
			'best_for'               => json_decode( get_post_meta( $post_id, '_tp_best_for', true ) ?: '[]', true ),
			'not_for'                => json_decode( get_post_meta( $post_id, '_tp_not_for', true ) ?: '[]', true ),

			// Infrastructure.
			'open_space_ratio'       => (float) get_post_meta( $post_id, '_tp_open_space_ratio', true ) ?: null,
			'density_rating'         => get_post_meta( $post_id, '_tp_density_rating', true ) ?: null,
			'maintenance_estimate'   => (int) get_post_meta( $post_id, '_tp_maintenance_estimate', true ) ?: null,
			'parking_info'           => get_post_meta( $post_id, '_tp_parking_info', true ) ?: null,
			'water_source'           => get_post_meta( $post_id, '_tp_water_source', true ) ?: null,
			'power_backup'           => get_post_meta( $post_id, '_tp_power_backup', true ) ?: null,

			// Verification.
			'last_verified'          => get_post_meta( $post_id, '_tp_last_verified', true ) ?: null,
			'reviewed_by'            => get_post_meta( $post_id, '_tp_reviewed_by', true ) ?: null,

			// Related data.
			'developer'              => $developer,
			'configurations'         => $formatted_configs,
			'taxonomies'             => $taxonomies,
		);

		return $this->success( $data );
	}

	/**
	 * GET /projects/<id>/scores/<requirement_id>
	 *
	 * Retrieve the fit-score breakdown for a project against a requirement.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_project_scores( $request ) {
		global $wpdb;

		$project_id     = $request->get_param( 'id' );
		$requirement_id = $request->get_param( 'requirement_id' );
		$customer       = $this->get_current_customer( $request );

		// Verify the project exists.
		$post = get_post( $project_id );
		if ( ! $post || 'tp_project' !== $post->post_type ) {
			return $this->error( 'not_found', 'Project not found.', 404 );
		}

		// Verify the requirement belongs to this customer.
		$requirement = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_customer_requirements
				 WHERE id = %d AND customer_id = %d",
				$requirement_id,
				$customer->id
			)
		);

		if ( ! $requirement ) {
			return $this->error( 'not_found', 'Requirement not found.', 404 );
		}

		// Get the score record.
		$score = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_project_scores
				 WHERE project_id = %d AND requirement_id = %d",
				$project_id,
				$requirement_id
			)
		);

		if ( ! $score ) {
			return $this->error( 'not_found', 'Score not found for this project and requirement.', 404 );
		}

		$data = array(
			'project_id'              => (int) $score->project_id,
			'requirement_id'          => (int) $score->requirement_id,
			'config_id'               => $score->config_id ? (int) $score->config_id : null,
			'total_fit_score'         => (int) $score->total_fit_score,
			'ranking'                 => $score->ranking ? (int) $score->ranking : null,
			'weight_profile'          => $score->weight_profile,
			'calculated_at'           => $score->calculated_at,
			'category_scores'         => array(
				'budget_fit'              => (int) $score->budget_fit,
				'location_fit'            => (int) $score->location_fit,
				'config_fit'              => (int) $score->config_fit,
				'carpet_area_fit'         => (int) $score->carpet_area_fit,
				'possession_fit'          => (int) $score->possession_fit,
				'emi_fit'                 => (int) $score->emi_fit,
				'commute_fit'             => (int) $score->commute_fit,
				'lifestyle_fit'           => (int) $score->lifestyle_fit,
				'developer_reliability'   => (int) $score->developer_reliability,
				'construction_stage_fit'  => (int) $score->construction_stage_fit,
				'legal_confidence'        => (int) $score->legal_confidence,
				'resale_liquidity'        => (int) $score->resale_liquidity,
				'rental_potential'        => (int) $score->rental_potential,
				'appreciation_drivers'    => (int) $score->appreciation_drivers,
				'risk_compatibility'      => (int) $score->risk_compatibility,
				'infrastructure_potential' => (int) $score->infrastructure_potential,
				'family_suitability'      => (int) $score->family_suitability,
				'urgency_match'           => (int) $score->urgency_match,
				'inventory_availability'  => (int) $score->inventory_availability,
				'proximity_score'         => (int) $score->proximity_score,
			),
			'weights_used'            => json_decode( $score->weights_used ?: '{}', true ),
			'match_reasons'           => json_decode( $score->match_reasons ?: '[]', true ),
			'trade_offs'              => json_decode( $score->trade_offs ?: '[]', true ),
			'ai_explanation'          => $score->ai_explanation,
		);

		return $this->success( $data );
	}

	/**
	 * Format a project post into card-level data for list responses.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function format_project_card( $post_id ) {
		global $wpdb;

		// Get developer name.
		$developer_id   = (int) get_post_meta( $post_id, '_tp_developer_id', true );
		$developer_name = null;
		if ( $developer_id ) {
			$dev_post = get_post( $developer_id );
			if ( $dev_post && 'tp_developer' === $dev_post->post_type ) {
				$developer_name = $dev_post->post_title;
			}
		}

		// Get price range from configurations table.
		$price_range = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT MIN(price_min) AS min_price, MAX(price_max) AS max_price
				 FROM {$wpdb->prefix}tp_project_configurations
				 WHERE project_id = %d",
				$post_id
			)
		);

		// Get configuration types.
		$configs = $this->get_taxonomy_terms( $post_id, 'tp_configuration' );

		// Get location term.
		$locations = $this->get_taxonomy_terms( $post_id, 'tp_location_area' );

		return array(
			'id'                  => $post_id,
			'title'               => get_the_title( $post_id ),
			'slug'                => get_post_field( 'post_name', $post_id ),
			'thumbnail'           => get_the_post_thumbnail_url( $post_id, 'medium' ) ?: null,
			'permalink'           => get_permalink( $post_id ),
			'location'            => $locations,
			'developer_name'      => $developer_name,
			'price_range'         => array(
				'min' => $price_range && $price_range->min_price ? (int) $price_range->min_price : null,
				'max' => $price_range && $price_range->max_price ? (int) $price_range->max_price : null,
			),
			'configurations'      => $configs,
			'construction_stage'  => get_post_meta( $post_id, '_tp_construction_stage', true ) ?: null,
			'expected_possession' => get_post_meta( $post_id, '_tp_expected_possession', true ) ?: null,
			'rera_number'         => get_post_meta( $post_id, '_tp_rera_number', true ) ?: null,
			'verified'            => (bool) get_post_meta( $post_id, '_tp_verified', true ),
			'sponsored'           => (bool) get_post_meta( $post_id, '_tp_sponsored', true ),
		);
	}

	/**
	 * Get taxonomy terms for a post as a simple array of slug/name pairs.
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
