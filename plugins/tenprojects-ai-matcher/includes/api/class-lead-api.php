<?php
/**
 * Lead REST API controller.
 *
 * Handles lead creation, listing, status updates, and detail retrieval.
 * Integrates lead qualification, routing, and partner notification on creation.
 *
 * @package TenProjects\API
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

use TenProjects\Services\Lead_Service;
use TenProjects\Services\Lead_Qualifier;
use TenProjects\Services\Lead_Router;
use TenProjects\Services\Notification_Service;
use TenProjects\Helpers\Rate_Limiter;

/**
 * Class Lead_API
 *
 * Routes:
 *  POST   /leads                  — Create a new lead (customer).
 *  GET    /leads                  — List leads with filters (admin).
 *  PUT    /leads/<id>/status      — Update lead status (admin or partner).
 *  GET    /leads/<id>             — Get full lead detail (admin or partner).
 */
class Lead_API extends API_Base {

	/**
	 * Lead service instance.
	 *
	 * @var Lead_Service
	 */
	private $lead_service;

	/**
	 * Lead qualifier instance.
	 *
	 * @var Lead_Qualifier
	 */
	private $lead_qualifier;

	/**
	 * Lead router instance.
	 *
	 * @var Lead_Router
	 */
	private $lead_router;

	/**
	 * Notification service instance.
	 *
	 * @var Notification_Service
	 */
	private $notification_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->lead_service         = new Lead_Service();
		$this->lead_qualifier       = new Lead_Qualifier();
		$this->lead_router          = new Lead_Router();
		$this->notification_service = new Notification_Service();
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {

		// POST /leads — create a new lead.
		register_rest_route(
			$this->namespace,
			'/leads',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_lead' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'lead_type'      => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return in_array( $value, array( 'site_visit', 'best_price', 'advisor', 'callback', 'whatsapp' ), true );
							},
						),
						'project_id'     => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
						),
						'message'        => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_textarea_field',
						),
						'preferred_time' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'source_page'    => array(
							'type'              => 'string',
							'sanitize_callback' => 'esc_url_raw',
						),
					),
				),
			)
		);

		// GET /leads — admin lead listing.
		register_rest_route(
			$this->namespace,
			'/leads',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_leads' ),
					'permission_callback' => array( $this, 'admin_permissions' ),
					'args'                => array(
						'status'         => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'classification' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'date_from'      => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return empty( $value ) || (bool) strtotime( $value );
							},
						),
						'date_to'        => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return empty( $value ) || (bool) strtotime( $value );
							},
						),
						'partner_id'     => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
						),
						'location'       => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'page'           => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'default'           => 1,
						),
						'per_page'       => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'default'           => 20,
						),
					),
				),
			)
		);

		// PUT /leads/<id>/status — update lead status.
		register_rest_route(
			$this->namespace,
			'/leads/(?P<id>\d+)/status',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_lead_status' ),
					'permission_callback' => array( $this, 'admin_or_partner_permissions' ),
					'args'                => array(
						'id'     => array(
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
							'validate_callback' => function ( $value ) {
								return is_numeric( $value ) && (int) $value > 0;
							},
						),
						'status' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								$valid = array(
									'new', 'contacted', 'qualified', 'site_visit_scheduled',
									'site_visit_done', 'negotiating', 'booked', 'lost', 'invalid',
								);
								return in_array( $value, $valid, true );
							},
						),
						'notes'  => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_textarea_field',
						),
					),
				),
			)
		);

		// GET /leads/<id> — full lead detail.
		register_rest_route(
			$this->namespace,
			'/leads/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_lead' ),
					'permission_callback' => array( $this, 'admin_or_partner_permissions' ),
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
	}

	/**
	 * Permission check: admin OR partner.
	 *
	 * Allows access for administrators and partners (who can view_tp_leads).
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
	 * POST /leads
	 *
	 * Create a new lead from an authenticated customer.
	 * Qualifies, routes (if auto-routable), and notifies assigned partner(s).
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_lead( $request ) {
		$valid = $this->validate_required( $request, array( 'lead_type' ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$customer = $this->get_current_customer( $request );
		if ( ! $customer ) {
			return $this->error( 'unauthorized', 'Authentication required.', 401 );
		}

		// Rate limit by phone: 5 leads per hour.
		$phone = $customer->phone ?? '';
		if ( $phone && Rate_Limiter::throttle_lead( $phone ) ) {
			$retry_after = Rate_Limiter::retry_after( 'lead_' . $phone, 3600 );
			return $this->error(
				'rate_limited',
				'Too many lead submissions. Please try again later.',
				429,
				array( 'retry_after' => $retry_after )
			);
		}

		// Validate lead_type.
		$lead_type   = sanitize_text_field( $request->get_param( 'lead_type' ) );
		$valid_types = array( 'site_visit', 'best_price', 'advisor', 'callback', 'whatsapp' );
		if ( ! in_array( $lead_type, $valid_types, true ) ) {
			return $this->error(
				'invalid_lead_type',
				'lead_type must be one of: ' . implode( ', ', $valid_types ) . '.',
				400
			);
		}

		// Build lead data.
		$lead_data = array(
			'customer_id'    => (int) $customer->id,
			'lead_type'      => $lead_type,
			'customer_name'  => $customer->full_name ?? $customer->name ?? null,
			'customer_phone' => $customer->phone ?? '',
			'customer_email' => $customer->email ?? null,
			'customer_city'  => $customer->city ?? null,
			'source_page'    => $request->get_param( 'source_page' ) ? esc_url_raw( $request->get_param( 'source_page' ) ) : null,
		);

		// Optional fields.
		$project_id = $request->get_param( 'project_id' );
		if ( $project_id ) {
			$lead_data['project_id'] = absint( $project_id );
		}

		$message = $request->get_param( 'message' );
		if ( $message ) {
			$lead_data['conversation_summary'] = sanitize_textarea_field( $message );
		}

		$preferred_time = $request->get_param( 'preferred_time' );
		if ( $preferred_time ) {
			$lead_data['purchase_timeline'] = sanitize_text_field( $preferred_time );
		}

		// Create lead via service.
		$lead = $this->lead_service->create( $lead_data );
		if ( ! $lead ) {
			return $this->error( 'creation_failed', 'Failed to create lead. Please try again.', 500 );
		}

		$lead_id = is_object( $lead ) ? $lead->id : $lead;

		// Qualify the lead.
		$qualification = $this->lead_qualifier->qualify( $lead_id );

		// Attempt auto-routing.
		$routing_result  = null;
		$partner_info    = null;
		$routed          = $this->lead_router->route( $lead_id );

		if ( $routed && ! empty( $routed['partner_id'] ) ) {
			$routing_result = $routed;
			$partner_info   = $this->get_partner_summary( $routed['partner_id'] );

			// Notify assigned partner(s).
			$this->notification_service->notify_partner_new_lead(
				$routed['partner_id'],
				$lead_id
			);
		}

		$response_data = array(
			'lead_id'        => (int) $lead_id,
			'classification' => $qualification['classification'] ?? null,
			'quality_score'  => $qualification['quality_score'] ?? null,
		);

		if ( $partner_info ) {
			$response_data['assigned_partner'] = $partner_info;
		}

		return $this->success( $response_data, 201 );
	}

	/**
	 * GET /leads
	 *
	 * Paginated lead listing for administrators with filtering support.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function list_leads( $request ) {
		global $wpdb;

		$pagination = $this->get_pagination( $request );
		$table      = $wpdb->prefix . 'tp_leads';

		// Build WHERE conditions.
		$where  = array( '1=1' );
		$values = array();

		$status = $request->get_param( 'status' );
		if ( $status ) {
			$where[]  = 'l.status = %s';
			$values[] = sanitize_text_field( $status );
		}

		$classification = $request->get_param( 'classification' );
		if ( $classification ) {
			$where[]  = 'l.classification = %s';
			$values[] = sanitize_text_field( $classification );
		}

		$date_from = $request->get_param( 'date_from' );
		if ( $date_from ) {
			$where[]  = 'l.created_at >= %s';
			$values[] = sanitize_text_field( $date_from ) . ' 00:00:00';
		}

		$date_to = $request->get_param( 'date_to' );
		if ( $date_to ) {
			$where[]  = 'l.created_at <= %s';
			$values[] = sanitize_text_field( $date_to ) . ' 23:59:59';
		}

		$partner_id = $request->get_param( 'partner_id' );
		if ( $partner_id ) {
			$where[]  = 'a.partner_id = %d';
			$values[] = absint( $partner_id );
		}

		$location = $request->get_param( 'location' );
		if ( $location ) {
			$where[]  = 'l.location_preference LIKE %s';
			$values[] = '%' . $wpdb->esc_like( sanitize_text_field( $location ) ) . '%';
		}

		$where_sql = implode( ' AND ', $where );

		// Join with assignments if partner_id filter is used.
		$join_sql = '';
		if ( $partner_id ) {
			$join_sql = "LEFT JOIN {$wpdb->prefix}tp_lead_assignments a ON l.id = a.lead_id";
		}

		// Count total.
		$count_sql = "SELECT COUNT(DISTINCT l.id) FROM {$table} l {$join_sql} WHERE {$where_sql}";
		if ( ! empty( $values ) ) {
			$count_sql = $wpdb->prepare( $count_sql, $values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$total = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Fetch leads.
		$query = "SELECT DISTINCT l.* FROM {$table} l {$join_sql} WHERE {$where_sql}
				  ORDER BY l.created_at DESC
				  LIMIT %d OFFSET %d";

		$query_values   = array_merge( $values, array( $pagination['per_page'], $pagination['offset'] ) );
		$prepared_query = $wpdb->prepare( $query, $query_values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$leads          = $wpdb->get_results( $prepared_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$data = array_map( function ( $lead ) {
			return array(
				'id'               => (int) $lead->id,
				'lead_uuid'        => $lead->lead_uuid,
				'lead_type'        => $lead->lead_type,
				'status'           => $lead->status,
				'classification'   => $lead->classification,
				'customer_name'    => $lead->customer_name,
				'customer_phone'   => $lead->customer_phone,
				'customer_email'   => $lead->customer_email,
				'project_id'       => $lead->project_id ? (int) $lead->project_id : null,
				'project_title'    => $lead->project_id ? get_the_title( $lead->project_id ) : null,
				'quality_score'    => (int) $lead->quality_score,
				'readiness_score'  => (int) $lead->readiness_score,
				'engagement_score' => (int) $lead->engagement_score,
				'created_at'       => $lead->created_at,
				'updated_at'       => $lead->updated_at,
			);
		}, $leads );

		$response = $this->success( $data );
		$response = $this->add_pagination_headers( $response, $total, $pagination['per_page'], $pagination['page'] );

		return $response;
	}

	/**
	 * PUT /leads/<id>/status
	 *
	 * Update lead status. Admins can update any lead. Partners can only
	 * update leads assigned to them.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_lead_status( $request ) {
		global $wpdb;

		$valid = $this->validate_required( $request, array( 'status' ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$lead_id    = absint( $request->get_param( 'id' ) );
		$new_status = sanitize_text_field( $request->get_param( 'status' ) );
		$notes      = $request->get_param( 'notes' ) ? sanitize_textarea_field( $request->get_param( 'notes' ) ) : null;

		// Validate status value.
		$valid_statuses = array(
			'new', 'contacted', 'qualified', 'site_visit_scheduled',
			'site_visit_done', 'negotiating', 'booked', 'lost', 'invalid',
		);
		if ( ! in_array( $new_status, $valid_statuses, true ) ) {
			return $this->error(
				'invalid_status',
				'Status must be one of: ' . implode( ', ', $valid_statuses ) . '.',
				400
			);
		}

		// Fetch the lead.
		$lead = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_leads WHERE id = %d",
				$lead_id
			)
		);

		if ( ! $lead ) {
			return $this->error( 'not_found', 'Lead not found.', 404 );
		}

		// If partner (not admin), verify assignment.
		if ( ! current_user_can( 'manage_tp_settings' ) ) {
			$partner = $this->get_current_partner();
			if ( ! $partner ) {
				return $this->error( 'forbidden', 'Partner record not found.', 403 );
			}

			$assignment = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}tp_lead_assignments
					 WHERE lead_id = %d AND partner_id = %d",
					$lead_id,
					$partner->id
				)
			);

			if ( ! $assignment ) {
				return $this->error( 'forbidden', 'You are not assigned to this lead.', 403 );
			}
		}

		// Update lead status.
		$update_data = array(
			'status'     => $new_status,
			'updated_at' => current_time( 'mysql' ),
		);

		$result = $wpdb->update(
			$wpdb->prefix . 'tp_leads',
			$update_data,
			array( 'id' => $lead_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $result ) {
			return $this->error( 'update_failed', 'Failed to update lead status.', 500 );
		}

		// Store notes in assignment if partner and notes provided.
		if ( $notes && ! current_user_can( 'manage_tp_settings' ) && isset( $partner, $assignment ) ) {
			$wpdb->update(
				$wpdb->prefix . 'tp_lead_assignments',
				array( 'partner_notes' => $notes ),
				array( 'id' => $assignment->id ),
				array( '%s' ),
				array( '%d' )
			);
		}

		return $this->success( array(
			'lead_id' => (int) $lead_id,
			'status'  => $new_status,
			'message' => 'Lead status updated successfully.',
		) );
	}

	/**
	 * GET /leads/<id>
	 *
	 * Full lead detail including customer info, requirement, scores,
	 * and assignment history. Partners can only view their assigned leads.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_lead( $request ) {
		global $wpdb;

		$lead_id = absint( $request->get_param( 'id' ) );

		// Fetch the lead.
		$lead = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_leads WHERE id = %d",
				$lead_id
			)
		);

		if ( ! $lead ) {
			return $this->error( 'not_found', 'Lead not found.', 404 );
		}

		// If partner (not admin), verify assignment.
		if ( ! current_user_can( 'manage_tp_settings' ) ) {
			$partner = $this->get_current_partner();
			if ( ! $partner ) {
				return $this->error( 'forbidden', 'Partner record not found.', 403 );
			}

			$assignment = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}tp_lead_assignments
					 WHERE lead_id = %d AND partner_id = %d",
					$lead_id,
					$partner->id
				)
			);

			if ( ! $assignment ) {
				return $this->error( 'forbidden', 'You are not assigned to this lead.', 403 );
			}
		}

		// Build response.
		$data = array(
			'id'                     => (int) $lead->id,
			'lead_uuid'              => $lead->lead_uuid,
			'lead_type'              => $lead->lead_type,
			'status'                 => $lead->status,
			'classification'         => $lead->classification,
			'customer'               => array(
				'id'    => (int) $lead->customer_id,
				'name'  => $lead->customer_name,
				'phone' => $lead->customer_phone,
				'email' => $lead->customer_email,
				'city'  => $lead->customer_city,
			),
			'project_id'             => $lead->project_id ? (int) $lead->project_id : null,
			'project_title'          => $lead->project_id ? get_the_title( $lead->project_id ) : null,
			'requirement'            => array(
				'location_preference'    => json_decode( $lead->location_preference ?? '[]', true ),
				'budget_comfortable'     => $lead->budget_comfortable ? (int) $lead->budget_comfortable : null,
				'budget_maximum'         => $lead->budget_maximum ? (int) $lead->budget_maximum : null,
				'configuration'          => json_decode( $lead->configuration ?? '[]', true ),
				'funding_type'           => $lead->funding_type,
				'purpose'                => $lead->purpose,
				'purchase_timeline'      => $lead->purchase_timeline,
				'possession_preference'  => $lead->possession_preference,
			),
			'scores'                 => array(
				'quality_score'    => (int) $lead->quality_score,
				'readiness_score'  => (int) $lead->readiness_score,
				'engagement_score' => (int) $lead->engagement_score,
			),
			'ai_lead_summary'        => $lead->ai_lead_summary,
			'conversation_summary'   => $lead->conversation_summary,
			'top_matched_projects'   => json_decode( $lead->top_matched_projects ?? '[]', true ),
			'site_visit_intent'      => (bool) $lead->site_visit_intent,
			'loan_required'          => (bool) $lead->loan_required,
			'consent_contact'        => (bool) $lead->consent_contact,
			'consent_share'          => (bool) $lead->consent_share,
			'is_duplicate'           => (bool) $lead->is_duplicate,
			'duplicate_of'           => $lead->duplicate_of ? (int) $lead->duplicate_of : null,
			'utm'                    => array(
				'source'   => $lead->utm_source,
				'medium'   => $lead->utm_medium,
				'campaign' => $lead->utm_campaign,
				'content'  => $lead->utm_content,
				'term'     => $lead->utm_term,
			),
			'landing_page'           => $lead->landing_page,
			'referrer'               => $lead->referrer,
			'device_type'            => $lead->device_type,
			'created_at'             => $lead->created_at,
			'updated_at'             => $lead->updated_at,
		);

		// Include assignment history.
		$assignments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.*, p.company_name, p.contact_person
				 FROM {$wpdb->prefix}tp_lead_assignments a
				 LEFT JOIN {$wpdb->prefix}tp_partners p ON a.partner_id = p.id
				 WHERE a.lead_id = %d
				 ORDER BY a.sent_at DESC",
				$lead_id
			)
		);

		$data['assignments'] = array_map( function ( $a ) {
			return array(
				'id'                    => (int) $a->id,
				'partner_id'            => (int) $a->partner_id,
				'partner_company'       => $a->company_name,
				'partner_contact'       => $a->contact_person,
				'assignment_type'       => $a->assignment_type,
				'credits_charged'       => (int) $a->credits_charged,
				'status'                => $a->status,
				'sent_at'               => $a->sent_at,
				'opened_at'             => $a->opened_at,
				'accepted_at'           => $a->accepted_at,
				'first_contact_at'      => $a->first_contact_at,
				'contact_attempts'      => (int) $a->contact_attempts,
				'site_visit_at'         => $a->site_visit_at,
				'booking_at'            => $a->booking_at,
				'response_time_minutes' => $a->response_time_minutes ? (int) $a->response_time_minutes : null,
				'partner_notes'         => $a->partner_notes,
			);
		}, $assignments );

		return $this->success( $data );
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

	/**
	 * Get a partner summary for the lead creation response.
	 *
	 * @param int $partner_id Partner ID.
	 * @return array|null Partner summary or null.
	 */
	private function get_partner_summary( $partner_id ) {
		global $wpdb;

		$partner = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, company_name, contact_person, partner_type
				 FROM {$wpdb->prefix}tp_partners WHERE id = %d",
				absint( $partner_id )
			)
		);

		if ( ! $partner ) {
			return null;
		}

		return array(
			'partner_id'     => (int) $partner->id,
			'company_name'   => $partner->company_name,
			'contact_person' => $partner->contact_person,
			'partner_type'   => $partner->partner_type,
		);
	}
}
