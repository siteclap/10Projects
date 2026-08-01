<?php
/**
 * Saved Projects REST API controller.
 *
 * Provides authenticated endpoints for customers to save/bookmark projects,
 * list their saved projects, and remove saved projects.
 *
 * @package TenProjects\API
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

class Saved_Projects_API extends API_Base {

	/**
	 * Register routes.
	 */
	public function register_routes() {

		// GET /saved-projects — list saved projects.
		register_rest_route(
			$this->namespace,
			'/saved-projects',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_saved' ),
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

		// POST /saved-projects — save/bookmark a project.
		register_rest_route(
			$this->namespace,
			'/saved-projects',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_project' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'project_id' => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && (int) $param > 0;
							},
							'sanitize_callback' => 'absint',
						),
						'notes'      => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_textarea_field',
						),
					),
				),
			)
		);

		// DELETE /saved-projects/<project_id> — remove a saved project.
		register_rest_route(
			$this->namespace,
			'/saved-projects/(?P<project_id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'remove_saved' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'project_id' => array(
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
	 * GET /saved-projects
	 *
	 * List the customer's saved/bookmarked projects with card-level data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function list_saved( $request ) {
		global $wpdb;

		$customer   = $this->get_current_customer( $request );
		$pagination = $this->get_pagination( $request );

		// Count total saved projects.
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}tp_saved_projects
				 WHERE customer_id = %d",
				$customer->id
			)
		);

		// Get paginated saved project records.
		$saved_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_saved_projects
				 WHERE customer_id = %d
				 ORDER BY sort_order ASC, saved_at DESC
				 LIMIT %d OFFSET %d",
				$customer->id,
				$pagination['per_page'],
				$pagination['offset']
			)
		);

		$items = array();

		foreach ( $saved_rows as $row ) {
			$post = get_post( $row->project_id );

			// Skip if project no longer exists or is not published.
			if ( ! $post || 'tp_project' !== $post->post_type || 'publish' !== $post->post_status ) {
				continue;
			}

			$items[] = array(
				'saved_id'   => (int) $row->id,
				'project_id' => (int) $row->project_id,
				'notes'      => $row->notes,
				'sort_order' => (int) $row->sort_order,
				'saved_at'   => $row->saved_at,
				'project'    => $this->format_project_card( (int) $row->project_id ),
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
	 * POST /saved-projects
	 *
	 * Save/bookmark a project for the customer. Prevents duplicates.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function save_project( $request ) {
		global $wpdb;

		$required = $this->validate_required( $request, array( 'project_id' ) );
		if ( is_wp_error( $required ) ) {
			return $required;
		}

		$customer   = $this->get_current_customer( $request );
		$project_id = absint( $request->get_param( 'project_id' ) );
		$notes      = sanitize_textarea_field( $request->get_param( 'notes' ) ?? '' );

		// Verify the project exists and is published.
		$post = get_post( $project_id );
		if ( ! $post || 'tp_project' !== $post->post_type || 'publish' !== $post->post_status ) {
			return $this->error( 'not_found', 'Project not found.', 404 );
		}

		// Check for duplicate.
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}tp_saved_projects
				 WHERE customer_id = %d AND project_id = %d",
				$customer->id,
				$project_id
			)
		);

		if ( $existing ) {
			return $this->error( 'already_saved', 'This project is already saved.', 409 );
		}

		// Determine sort order (append to end).
		$max_order = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(sort_order) FROM {$wpdb->prefix}tp_saved_projects
				 WHERE customer_id = %d",
				$customer->id
			)
		);

		$result = $wpdb->insert(
			$wpdb->prefix . 'tp_saved_projects',
			array(
				'customer_id' => $customer->id,
				'project_id'  => $project_id,
				'notes'       => $notes,
				'sort_order'  => $max_order + 1,
				'saved_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%d', '%s' )
		);

		if ( false === $result ) {
			return $this->error( 'save_failed', 'Failed to save project.', 500 );
		}

		$data = array(
			'saved_id'   => $wpdb->insert_id,
			'project_id' => $project_id,
			'notes'      => $notes,
			'saved_at'   => current_time( 'mysql' ),
		);

		return $this->success( $data, 201 );
	}

	/**
	 * DELETE /saved-projects/<project_id>
	 *
	 * Remove a saved project from the customer's shortlist.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function remove_saved( $request ) {
		global $wpdb;

		$customer   = $this->get_current_customer( $request );
		$project_id = absint( $request->get_param( 'project_id' ) );

		// Verify the saved record exists for this customer.
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}tp_saved_projects
				 WHERE customer_id = %d AND project_id = %d",
				$customer->id,
				$project_id
			)
		);

		if ( ! $existing ) {
			return $this->error( 'not_found', 'Saved project not found.', 404 );
		}

		$deleted = $wpdb->delete(
			$wpdb->prefix . 'tp_saved_projects',
			array(
				'customer_id' => $customer->id,
				'project_id'  => $project_id,
			),
			array( '%d', '%d' )
		);

		if ( false === $deleted ) {
			return $this->error( 'delete_failed', 'Failed to remove saved project.', 500 );
		}

		return $this->success( null, 200 );
	}

	/**
	 * Format a project post into card-level data.
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

		// Get configuration terms.
		$configs   = $this->get_taxonomy_terms( $post_id, 'tp_configuration' );
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
		);
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
