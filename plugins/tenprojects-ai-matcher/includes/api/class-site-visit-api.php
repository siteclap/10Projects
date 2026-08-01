<?php
/**
 * Site Visit REST API controller.
 *
 * Handles site visit request creation, customer listing,
 * partner/admin status updates, and admin-level visit management.
 *
 * @package TenProjects\API
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

use TenProjects\Services\Lead_Service;

/**
 * Class Site_Visit_API
 *
 * Routes:
 *  POST   /site-visits              — Request a site visit (customer).
 *  GET    /site-visits              — List customer's site visits (customer).
 *  PUT    /site-visits/<id>         — Update site visit status (partner/admin).
 *  GET    /site-visits/admin        — Admin listing of all site visits (admin).
 */
class Site_Visit_API extends API_Base {

	/**
	 * Lead service instance.
	 *
	 * @var Lead_Service
	 */
	private $lead_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->lead_service = new Lead_Service();
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {

		// POST /site-visits — create a site visit request.
		register_rest_route(
			$this->namespace,
			'/site-visits',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_site_visit' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'project_id'          => array(
							'required'          => true,
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'validate_callback' => function ( $value ) {
								return is_numeric( $value ) && (int) $value > 0;
							},
						),
						'preferred_date'      => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return (bool) \DateTime::createFromFormat( 'Y-m-d', $value );
							},
						),
						'preferred_time_slot' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return in_array( $value, array( 'morning', 'afternoon', 'evening' ), true );
							},
						),
						'notes'               => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_textarea_field',
						),
						'guests_count'        => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// GET /site-visits — list customer's own site visits.
		register_rest_route(
			$this->namespace,
			'/site-visits',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_customer_visits' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'page'     => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'default'           => 1,
						),
						'per_page' => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'default'           => 20,
						),
					),
				),
			)
		);

		// PUT /site-visits/<id> — update site visit status (partner/admin).
		register_rest_route(
			$this->namespace,
			'/site-visits/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_site_visit' ),
					'permission_callback' => array( $this, 'admin_or_partner_permissions' ),
					'args'                => array(
						'id'              => array(
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
							'validate_callback' => function ( $value ) {
								return is_numeric( $value ) && (int) $value > 0;
							},
						),
						'status'          => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								$valid = array( 'pending', 'confirmed', 'rescheduled', 'completed', 'cancelled', 'no_show' );
								return in_array( $value, $valid, true );
							},
						),
						'confirmed_date'  => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return empty( $value ) || (bool) \DateTime::createFromFormat( 'Y-m-d', $value );
							},
						),
						'confirmed_time'  => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'partner_notes'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_textarea_field',
						),
					),
				),
			)
		);

		// GET /site-visits/admin — admin listing of all site visits.
		register_rest_route(
			$this->namespace,
			'/site-visits/admin',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_admin_visits' ),
					'permission_callback' => array( $this, 'admin_permissions' ),
					'args'                => array(
						'status'     => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'date_from'  => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return empty( $value ) || (bool) strtotime( $value );
							},
						),
						'date_to'    => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return empty( $value ) || (bool) strtotime( $value );
							},
						),
						'project_id' => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
						),
						'partner_id' => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
						),
						'page'       => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'default'           => 1,
						),
						'per_page'   => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'default'           => 20,
						),
					),
				),
			)
		);
	}

	/**
	 * Permission check: admin OR partner.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return true|\WP_Error
	 */
	public function admin_or_partner_permissions( $request ) {
		if ( current_user_can( 'manage_tp_settings' ) ) {
			return true;
		}

		return $this->partner_permissions( $request );
	}

	/**
	 * POST /site-visits
	 *
	 * Create a site visit request from an authenticated customer.
	 * Also creates a lead of type 'site_visit' if one does not already exist
	 * for this customer and project.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_site_visit( $request ) {
		global $wpdb;

		$valid = $this->validate_required( $request, array( 'project_id', 'preferred_date', 'preferred_time_slot' ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$customer = $this->get_current_customer( $request );
		if ( ! $customer ) {
			return $this->error( 'unauthorized', 'Authentication required.', 401 );
		}

		$project_id          = absint( $request->get_param( 'project_id' ) );
		$preferred_date      = sanitize_text_field( $request->get_param( 'preferred_date' ) );
		$preferred_time_slot = sanitize_text_field( $request->get_param( 'preferred_time_slot' ) );
		$notes               = $request->get_param( 'notes' ) ? sanitize_textarea_field( $request->get_param( 'notes' ) ) : null;
		$guests_count        = $request->get_param( 'guests_count' ) ? absint( $request->get_param( 'guests_count' ) ) : null;

		// Validate the project exists and is published.
		if ( 'publish' !== get_post_status( $project_id ) ) {
			return $this->error( 'invalid_project', 'Project not found or not available.', 404 );
		}

		// Validate preferred_time_slot.
		$valid_slots = array( 'morning', 'afternoon', 'evening' );
		if ( ! in_array( $preferred_time_slot, $valid_slots, true ) ) {
			return $this->error(
				'invalid_time_slot',
				'preferred_time_slot must be one of: morning, afternoon, evening.',
				400
			);
		}

		// Validate date is not in the past.
		$date_obj = \DateTime::createFromFormat( 'Y-m-d', $preferred_date );
		if ( ! $date_obj ) {
			return $this->error( 'invalid_date', 'preferred_date must be in Y-m-d format.', 400 );
		}

		$today = new \DateTime( 'today', wp_timezone() );
		if ( $date_obj < $today ) {
			return $this->error( 'past_date', 'Site visit date cannot be in the past.', 400 );
		}

		// Check for existing site_visit lead for this customer + project.
		$existing_lead_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}tp_leads
				 WHERE customer_id = %d AND project_id = %d AND lead_type = 'site_visit'
				 ORDER BY id DESC LIMIT 1",
				$customer->id,
				$project_id
			)
		);

		// Create a lead if one doesn't exist.
		$lead_id = $existing_lead_id ? (int) $existing_lead_id : null;

		if ( ! $lead_id ) {
			$lead = $this->lead_service->create( array(
				'customer_id'      => (int) $customer->id,
				'project_id'       => $project_id,
				'lead_type'        => 'site_visit',
				'customer_name'    => $customer->full_name ?? $customer->name ?? null,
				'customer_phone'   => $customer->phone ?? '',
				'customer_email'   => $customer->email ?? null,
				'customer_city'    => $customer->city ?? null,
				'site_visit_intent' => 1,
			) );

			if ( $lead ) {
				$lead_id = is_object( $lead ) ? (int) $lead->id : (int) $lead;
			}
		}

		// Build site visit data. Include guests_count in customer_notes if provided.
		$customer_notes = $notes;
		if ( $guests_count ) {
			$guest_note     = sprintf( 'Guests: %d', $guests_count );
			$customer_notes = $customer_notes ? $customer_notes . "\n" . $guest_note : $guest_note;
		}

		// Insert site visit record.
		$inserted = $wpdb->insert(
			$wpdb->prefix . 'tp_site_visits',
			array(
				'customer_id'         => (int) $customer->id,
				'project_id'          => $project_id,
				'lead_id'             => $lead_id,
				'requested_date'      => $preferred_date,
				'requested_time_slot' => $preferred_time_slot,
				'status'              => 'requested',
				'customer_notes'      => $customer_notes,
				'created_at'          => current_time( 'mysql' ),
				'updated_at'          => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return $this->error( 'creation_failed', 'Failed to create site visit request.', 500 );
		}

		$visit_id = (int) $wpdb->insert_id;

		return $this->success(
			array(
				'site_visit_id' => $visit_id,
				'project_id'    => $project_id,
				'project_title' => get_the_title( $project_id ),
				'date'          => $preferred_date,
				'time_slot'     => $preferred_time_slot,
				'status'        => 'requested',
				'lead_id'       => $lead_id,
				'message'       => 'Site visit request submitted successfully.',
			),
			201
		);
	}

	/**
	 * GET /site-visits
	 *
	 * List the authenticated customer's own site visit requests.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function list_customer_visits( $request ) {
		global $wpdb;

		$customer = $this->get_current_customer( $request );
		if ( ! $customer ) {
			return $this->error( 'unauthorized', 'Authentication required.', 401 );
		}

		$pagination = $this->get_pagination( $request );

		// Count total.
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}tp_site_visits WHERE customer_id = %d",
				$customer->id
			)
		);

		// Fetch visits.
		$visits = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_site_visits
				 WHERE customer_id = %d
				 ORDER BY created_at DESC
				 LIMIT %d OFFSET %d",
				$customer->id,
				$pagination['per_page'],
				$pagination['offset']
			)
		);

		$data = array_map( function ( $visit ) {
			return array(
				'id'                  => (int) $visit->id,
				'project_id'         => (int) $visit->project_id,
				'project_title'      => get_the_title( $visit->project_id ),
				'requested_date'     => $visit->requested_date,
				'requested_time_slot' => $visit->requested_time_slot,
				'confirmed_date'     => $visit->confirmed_date,
				'confirmed_time'     => $visit->confirmed_time,
				'status'             => $visit->status,
				'customer_notes'     => $visit->customer_notes,
				'partner_notes'      => $visit->partner_notes,
				'feedback_rating'    => $visit->feedback_rating ? (int) $visit->feedback_rating : null,
				'created_at'         => $visit->created_at,
				'updated_at'         => $visit->updated_at,
			);
		}, $visits );

		$response = $this->success( $data );
		$response = $this->add_pagination_headers( $response, $total, $pagination['per_page'], $pagination['page'] );

		return $response;
	}

	/**
	 * PUT /site-visits/<id>
	 *
	 * Update a site visit status. Partners can only update visits for leads
	 * assigned to them. Admins can update any visit.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_site_visit( $request ) {
		global $wpdb;

		$valid = $this->validate_required( $request, array( 'status' ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$visit_id   = absint( $request->get_param( 'id' ) );
		$new_status = sanitize_text_field( $request->get_param( 'status' ) );

		// Validate status.
		$valid_statuses = array( 'pending', 'confirmed', 'rescheduled', 'completed', 'cancelled', 'no_show' );
		if ( ! in_array( $new_status, $valid_statuses, true ) ) {
			return $this->error(
				'invalid_status',
				'Status must be one of: ' . implode( ', ', $valid_statuses ) . '.',
				400
			);
		}

		// Fetch the site visit.
		$visit = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_site_visits WHERE id = %d",
				$visit_id
			)
		);

		if ( ! $visit ) {
			return $this->error( 'not_found', 'Site visit not found.', 404 );
		}

		// If partner (not admin), verify they are assigned to this lead.
		if ( ! current_user_can( 'manage_tp_settings' ) ) {
			$partner = $this->get_current_partner();
			if ( ! $partner ) {
				return $this->error( 'forbidden', 'Partner record not found.', 403 );
			}

			// Check assignment via lead_id.
			if ( $visit->lead_id ) {
				$assignment = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM {$wpdb->prefix}tp_lead_assignments
						 WHERE lead_id = %d AND partner_id = %d",
						$visit->lead_id,
						$partner->id
					)
				);

				if ( ! $assignment ) {
					return $this->error( 'forbidden', 'You are not assigned to this site visit.', 403 );
				}
			} else {
				// No lead_id linked — check if partner_id matches directly.
				if ( (int) $visit->partner_id !== (int) $partner->id ) {
					return $this->error( 'forbidden', 'You are not assigned to this site visit.', 403 );
				}
			}
		}

		// Build update data.
		$update_data = array(
			'status'     => $new_status,
			'updated_at' => current_time( 'mysql' ),
		);
		$format = array( '%s', '%s' );

		$confirmed_date = $request->get_param( 'confirmed_date' );
		if ( $confirmed_date ) {
			$update_data['confirmed_date'] = sanitize_text_field( $confirmed_date );
			$format[] = '%s';
		}

		$confirmed_time = $request->get_param( 'confirmed_time' );
		if ( $confirmed_time ) {
			$update_data['confirmed_time'] = sanitize_text_field( $confirmed_time );
			$format[] = '%s';
		}

		$partner_notes = $request->get_param( 'partner_notes' );
		if ( $partner_notes ) {
			$update_data['partner_notes'] = sanitize_textarea_field( $partner_notes );
			$format[] = '%s';
		}

		$result = $wpdb->update(
			$wpdb->prefix . 'tp_site_visits',
			$update_data,
			array( 'id' => $visit_id ),
			$format,
			array( '%d' )
		);

		if ( false === $result ) {
			return $this->error( 'update_failed', 'Failed to update site visit.', 500 );
		}

		// Fetch updated record.
		$updated = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_site_visits WHERE id = %d",
				$visit_id
			)
		);

		return $this->success( array(
			'id'                  => (int) $updated->id,
			'project_id'         => (int) $updated->project_id,
			'project_title'      => get_the_title( $updated->project_id ),
			'status'             => $updated->status,
			'requested_date'     => $updated->requested_date,
			'requested_time_slot' => $updated->requested_time_slot,
			'confirmed_date'     => $updated->confirmed_date,
			'confirmed_time'     => $updated->confirmed_time,
			'partner_notes'      => $updated->partner_notes,
			'updated_at'         => $updated->updated_at,
			'message'            => 'Site visit updated successfully.',
		) );
	}

	/**
	 * GET /site-visits/admin
	 *
	 * Paginated admin listing of all site visits with filtering.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function list_admin_visits( $request ) {
		global $wpdb;

		$pagination = $this->get_pagination( $request );
		$table      = $wpdb->prefix . 'tp_site_visits';

		// Build WHERE conditions.
		$where  = array( '1=1' );
		$values = array();

		$status = $request->get_param( 'status' );
		if ( $status ) {
			$where[]  = 'sv.status = %s';
			$values[] = sanitize_text_field( $status );
		}

		$date_from = $request->get_param( 'date_from' );
		if ( $date_from ) {
			$where[]  = 'sv.requested_date >= %s';
			$values[] = sanitize_text_field( $date_from );
		}

		$date_to = $request->get_param( 'date_to' );
		if ( $date_to ) {
			$where[]  = 'sv.requested_date <= %s';
			$values[] = sanitize_text_field( $date_to );
		}

		$project_id = $request->get_param( 'project_id' );
		if ( $project_id ) {
			$where[]  = 'sv.project_id = %d';
			$values[] = absint( $project_id );
		}

		$partner_id = $request->get_param( 'partner_id' );
		if ( $partner_id ) {
			$where[]  = 'sv.partner_id = %d';
			$values[] = absint( $partner_id );
		}

		$where_sql = implode( ' AND ', $where );

		// Count total.
		$count_sql = "SELECT COUNT(*) FROM {$table} sv WHERE {$where_sql}";
		if ( ! empty( $values ) ) {
			$count_sql = $wpdb->prepare( $count_sql, $values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$total = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Fetch site visits with customer info from leads table.
		$query = "SELECT sv.*,
					c.full_name AS customer_name, c.phone AS customer_phone, c.email AS customer_email,
					p.company_name AS partner_company
				  FROM {$table} sv
				  LEFT JOIN {$wpdb->prefix}tp_customers c ON sv.customer_id = c.id
				  LEFT JOIN {$wpdb->prefix}tp_partners p ON sv.partner_id = p.id
				  WHERE {$where_sql}
				  ORDER BY sv.requested_date DESC, sv.created_at DESC
				  LIMIT %d OFFSET %d";

		$query_values   = array_merge( $values, array( $pagination['per_page'], $pagination['offset'] ) );
		$prepared_query = $wpdb->prepare( $query, $query_values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$visits         = $wpdb->get_results( $prepared_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$data = array_map( function ( $visit ) {
			return array(
				'id'                  => (int) $visit->id,
				'customer_id'        => (int) $visit->customer_id,
				'customer_name'      => $visit->customer_name,
				'customer_phone'     => $visit->customer_phone,
				'customer_email'     => $visit->customer_email,
				'project_id'         => (int) $visit->project_id,
				'project_title'      => get_the_title( $visit->project_id ),
				'lead_id'            => $visit->lead_id ? (int) $visit->lead_id : null,
				'partner_id'         => $visit->partner_id ? (int) $visit->partner_id : null,
				'partner_company'    => $visit->partner_company,
				'requested_date'     => $visit->requested_date,
				'requested_time_slot' => $visit->requested_time_slot,
				'confirmed_date'     => $visit->confirmed_date,
				'confirmed_time'     => $visit->confirmed_time,
				'status'             => $visit->status,
				'customer_notes'     => $visit->customer_notes,
				'partner_notes'      => $visit->partner_notes,
				'feedback_rating'    => $visit->feedback_rating ? (int) $visit->feedback_rating : null,
				'feedback_text'      => $visit->feedback_text,
				'created_at'         => $visit->created_at,
				'updated_at'         => $visit->updated_at,
			);
		}, $visits );

		$response = $this->success( $data );
		$response = $this->add_pagination_headers( $response, $total, $pagination['per_page'], $pagination['page'] );

		return $response;
	}

	/**
	 * Get the current partner record from the logged-in WP user.
	 *
	 * @return object|null Partner DB row or null.
	 */
	private function get_current_partner() {
		global $wpdb;

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return null;
		}

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_partners
				 WHERE wp_user_id = %d AND is_active = 1",
				$user_id
			)
		);
	}
}
