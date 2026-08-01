<?php
/**
 * Partner REST API controller.
 *
 * Provides partner-facing endpoints for dashboard summary, lead management,
 * lead acceptance/rejection, feedback submission, and profile retrieval.
 *
 * @package TenProjects\API
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

/**
 * Class Partner_API
 *
 * Routes:
 *  GET    /partner/dashboard               — Partner dashboard summary.
 *  GET    /partner/leads                    — Paginated partner leads.
 *  PUT    /partner/leads/<id>/accept        — Accept a lead assignment.
 *  PUT    /partner/leads/<id>/reject        — Reject a lead assignment.
 *  POST   /partner/leads/<id>/feedback      — Submit lead feedback/outcome.
 *  GET    /partner/profile                  — Get partner's own profile.
 */
class Partner_API extends API_Base {

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {

		// GET /partner/dashboard — partner dashboard summary.
		register_rest_route(
			$this->namespace,
			'/partner/dashboard',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_dashboard' ),
					'permission_callback' => array( $this, 'partner_permissions' ),
				),
			)
		);

		// GET /partner/leads — paginated leads for this partner.
		register_rest_route(
			$this->namespace,
			'/partner/leads',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_leads' ),
					'permission_callback' => array( $this, 'partner_permissions' ),
					'args'                => array(
						'status'    => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'date_from' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return empty( $value ) || (bool) strtotime( $value );
							},
						),
						'date_to'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return empty( $value ) || (bool) strtotime( $value );
							},
						),
						'page'      => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'default'           => 1,
						),
						'per_page'  => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'default'           => 20,
						),
					),
				),
			)
		);

		// PUT /partner/leads/<id>/accept — accept a lead.
		register_rest_route(
			$this->namespace,
			'/partner/leads/(?P<id>\d+)/accept',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'accept_lead' ),
					'permission_callback' => array( $this, 'partner_permissions' ),
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

		// PUT /partner/leads/<id>/reject — reject a lead.
		register_rest_route(
			$this->namespace,
			'/partner/leads/(?P<id>\d+)/reject',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'reject_lead' ),
					'permission_callback' => array( $this, 'partner_permissions' ),
					'args'                => array(
						'id'     => array(
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
							'validate_callback' => function ( $value ) {
								return is_numeric( $value ) && (int) $value > 0;
							},
						),
						'reason' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								$valid = array( 'not_interested', 'budget_mismatch', 'area_mismatch', 'duplicate', 'other' );
								return in_array( $value, $valid, true );
							},
						),
					),
				),
			)
		);

		// POST /partner/leads/<id>/feedback — submit feedback on a lead.
		register_rest_route(
			$this->namespace,
			'/partner/leads/(?P<id>\d+)/feedback',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'submit_feedback' ),
					'permission_callback' => array( $this, 'partner_permissions' ),
					'args'                => array(
						'id'                 => array(
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
							'validate_callback' => function ( $value ) {
								return is_numeric( $value ) && (int) $value > 0;
							},
						),
						'status'             => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								$valid = array( 'contacted', 'site_visit_done', 'negotiating', 'booked', 'lost' );
								return in_array( $value, $valid, true );
							},
						),
						'notes'              => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_textarea_field',
						),
						'next_followup_date' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return empty( $value ) || (bool) \DateTime::createFromFormat( 'Y-m-d', $value );
							},
						),
					),
				),
			)
		);

		// GET /partner/profile — get partner's own profile.
		register_rest_route(
			$this->namespace,
			'/partner/profile',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_profile' ),
					'permission_callback' => array( $this, 'partner_permissions' ),
				),
			)
		);
	}

	/**
	 * GET /partner/dashboard
	 *
	 * Partner's dashboard summary with key metrics:
	 * total leads, pending, accepted, this month's stats, credits, rating.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_dashboard( $request ) {
		global $wpdb;

		$partner = $this->get_current_partner();
		if ( ! $partner ) {
			return $this->error( 'partner_not_found', 'Partner record not found.', 404 );
		}

		$partner_id = (int) $partner->id;
		$assign_table = $wpdb->prefix . 'tp_lead_assignments';

		// Total leads assigned to this partner.
		$total_leads = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$assign_table} WHERE partner_id = %d",
				$partner_id
			)
		);

		// Leads with status 'sent' (pending — not yet accepted or rejected).
		$pending_leads = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$assign_table} WHERE partner_id = %d AND status = 'sent'",
				$partner_id
			)
		);

		// Accepted leads (status = 'accepted' or any active status beyond accepted).
		$accepted_leads = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$assign_table}
				 WHERE partner_id = %d AND status NOT IN ('sent', 'rejected', 'disputed')",
				$partner_id
			)
		);

		// This month's stats.
		$month_start = gmdate( 'Y-m-01 00:00:00' );
		$month_end   = gmdate( 'Y-m-t 23:59:59' );

		$this_month_total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$assign_table}
				 WHERE partner_id = %d AND sent_at >= %s AND sent_at <= %s",
				$partner_id,
				$month_start,
				$month_end
			)
		);

		$this_month_accepted = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$assign_table}
				 WHERE partner_id = %d AND accepted_at >= %s AND accepted_at <= %s",
				$partner_id,
				$month_start,
				$month_end
			)
		);

		$this_month_booked = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$assign_table}
				 WHERE partner_id = %d AND booking_at >= %s AND booking_at <= %s",
				$partner_id,
				$month_start,
				$month_end
			)
		);

		return $this->success( array(
			'partner_id'       => $partner_id,
			'company_name'     => $partner->company_name,
			'total_leads'      => $total_leads,
			'pending_leads'    => $pending_leads,
			'accepted_leads'   => $accepted_leads,
			'this_month'       => array(
				'total'    => $this_month_total,
				'accepted' => $this_month_accepted,
				'booked'   => $this_month_booked,
			),
			'credits_remaining' => (int) $partner->lead_credits,
			'rating'            => (float) $partner->rating,
			'conversion_rate'   => (float) $partner->conversion_rate,
			'response_sla_hours' => (int) $partner->response_sla_hours,
		) );
	}

	/**
	 * GET /partner/leads
	 *
	 * Paginated list of leads assigned to the current partner
	 * with filter support for status and date range.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function list_leads( $request ) {
		global $wpdb;

		$partner = $this->get_current_partner();
		if ( ! $partner ) {
			return $this->error( 'partner_not_found', 'Partner record not found.', 404 );
		}

		$pagination  = $this->get_pagination( $request );
		$partner_id  = (int) $partner->id;

		// Build WHERE conditions.
		$where  = array( 'a.partner_id = %d' );
		$values = array( $partner_id );

		$status = $request->get_param( 'status' );
		if ( $status ) {
			$where[]  = 'a.status = %s';
			$values[] = sanitize_text_field( $status );
		}

		$date_from = $request->get_param( 'date_from' );
		if ( $date_from ) {
			$where[]  = 'a.sent_at >= %s';
			$values[] = sanitize_text_field( $date_from ) . ' 00:00:00';
		}

		$date_to = $request->get_param( 'date_to' );
		if ( $date_to ) {
			$where[]  = 'a.sent_at <= %s';
			$values[] = sanitize_text_field( $date_to ) . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );

		// Count total.
		$count_sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}tp_lead_assignments a WHERE {$where_sql}",
			$values
		); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Fetch leads with assignment and lead data.
		$query = "SELECT a.*, l.lead_uuid, l.lead_type, l.customer_name, l.customer_phone,
					l.customer_email, l.customer_city, l.project_id, l.quality_score,
					l.readiness_score, l.classification, l.status AS lead_status,
					l.budget_comfortable, l.budget_maximum, l.configuration,
					l.location_preference, l.created_at AS lead_created_at
				  FROM {$wpdb->prefix}tp_lead_assignments a
				  INNER JOIN {$wpdb->prefix}tp_leads l ON a.lead_id = l.id
				  WHERE {$where_sql}
				  ORDER BY a.sent_at DESC
				  LIMIT %d OFFSET %d";

		$query_values   = array_merge( $values, array( $pagination['per_page'], $pagination['offset'] ) );
		$prepared_query = $wpdb->prepare( $query, $query_values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results        = $wpdb->get_results( $prepared_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$data = array_map( function ( $row ) {
			return array(
				'assignment_id'    => (int) $row->id,
				'lead_id'          => (int) $row->lead_id,
				'lead_uuid'        => $row->lead_uuid,
				'lead_type'        => $row->lead_type,
				'assignment_status' => $row->status,
				'lead_status'      => $row->lead_status,
				'customer'         => array(
					'name'  => $row->customer_name,
					'phone' => $row->customer_phone,
					'email' => $row->customer_email,
					'city'  => $row->customer_city,
				),
				'project_id'       => $row->project_id ? (int) $row->project_id : null,
				'project_title'    => $row->project_id ? get_the_title( $row->project_id ) : null,
				'quality_score'    => (int) $row->quality_score,
				'readiness_score'  => (int) $row->readiness_score,
				'classification'   => $row->classification,
				'budget'           => array(
					'comfortable' => $row->budget_comfortable ? (int) $row->budget_comfortable : null,
					'maximum'     => $row->budget_maximum ? (int) $row->budget_maximum : null,
				),
				'configuration'    => json_decode( $row->configuration ?? '[]', true ),
				'location'         => json_decode( $row->location_preference ?? '[]', true ),
				'assignment_type'  => $row->assignment_type,
				'credits_charged'  => (int) $row->credits_charged,
				'sent_at'          => $row->sent_at,
				'accepted_at'      => $row->accepted_at,
				'lead_created_at'  => $row->lead_created_at,
				'partner_notes'    => $row->partner_notes,
			);
		}, $results );

		$response = $this->success( $data );
		$response = $this->add_pagination_headers( $response, $total, $pagination['per_page'], $pagination['page'] );

		return $response;
	}

	/**
	 * PUT /partner/leads/<id>/accept
	 *
	 * Accept a lead assignment. Changes assignment status from 'sent' to 'accepted'
	 * and records response time.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function accept_lead( $request ) {
		global $wpdb;

		$lead_id = absint( $request->get_param( 'id' ) );

		$partner = $this->get_current_partner();
		if ( ! $partner ) {
			return $this->error( 'partner_not_found', 'Partner record not found.', 404 );
		}

		// Find the assignment.
		$assignment = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_lead_assignments
				 WHERE lead_id = %d AND partner_id = %d",
				$lead_id,
				$partner->id
			)
		);

		if ( ! $assignment ) {
			return $this->error( 'not_found', 'Lead assignment not found.', 404 );
		}

		if ( 'sent' !== $assignment->status ) {
			return $this->error(
				'invalid_action',
				'This lead has already been ' . $assignment->status . '.',
				400
			);
		}

		// Calculate response time in minutes.
		$sent_time     = strtotime( $assignment->sent_at );
		$now           = time();
		$response_mins = $sent_time ? (int) round( ( $now - $sent_time ) / 60 ) : null;

		$result = $wpdb->update(
			$wpdb->prefix . 'tp_lead_assignments',
			array(
				'status'                => 'accepted',
				'accepted_at'           => current_time( 'mysql' ),
				'response_time_minutes' => $response_mins,
			),
			array( 'id' => $assignment->id ),
			array( '%s', '%s', '%d' ),
			array( '%d' )
		);

		if ( false === $result ) {
			return $this->error( 'update_failed', 'Failed to accept lead.', 500 );
		}

		return $this->success( array(
			'lead_id'              => $lead_id,
			'assignment_id'        => (int) $assignment->id,
			'status'               => 'accepted',
			'response_time_minutes' => $response_mins,
			'message'              => 'Lead accepted successfully.',
		) );
	}

	/**
	 * PUT /partner/leads/<id>/reject
	 *
	 * Reject a lead assignment. Credits may be refunded based on rejection reason.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function reject_lead( $request ) {
		global $wpdb;

		$valid = $this->validate_required( $request, array( 'reason' ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$lead_id = absint( $request->get_param( 'id' ) );
		$reason  = sanitize_text_field( $request->get_param( 'reason' ) );

		// Validate reason.
		$valid_reasons = array( 'not_interested', 'budget_mismatch', 'area_mismatch', 'duplicate', 'other' );
		if ( ! in_array( $reason, $valid_reasons, true ) ) {
			return $this->error(
				'invalid_reason',
				'Reason must be one of: ' . implode( ', ', $valid_reasons ) . '.',
				400
			);
		}

		$partner = $this->get_current_partner();
		if ( ! $partner ) {
			return $this->error( 'partner_not_found', 'Partner record not found.', 404 );
		}

		// Find the assignment.
		$assignment = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_lead_assignments
				 WHERE lead_id = %d AND partner_id = %d",
				$lead_id,
				$partner->id
			)
		);

		if ( ! $assignment ) {
			return $this->error( 'not_found', 'Lead assignment not found.', 404 );
		}

		if ( 'sent' !== $assignment->status ) {
			return $this->error(
				'invalid_action',
				'This lead has already been ' . $assignment->status . '.',
				400
			);
		}

		// Determine if credits should be refunded.
		// Refund for: duplicate, area_mismatch, budget_mismatch (data quality issues).
		$refundable_reasons = array( 'duplicate', 'area_mismatch', 'budget_mismatch' );
		$refund_eligible    = in_array( $reason, $refundable_reasons, true );
		$credits_refunded   = 0;

		// Update assignment.
		$result = $wpdb->update(
			$wpdb->prefix . 'tp_lead_assignments',
			array(
				'status'           => 'rejected',
				'rejection_reason' => $reason,
				'refund_eligible'  => $refund_eligible ? 1 : 0,
			),
			array( 'id' => $assignment->id ),
			array( '%s', '%s', '%d' ),
			array( '%d' )
		);

		if ( false === $result ) {
			return $this->error( 'update_failed', 'Failed to reject lead.', 500 );
		}

		// Refund credits if eligible.
		if ( $refund_eligible && $assignment->credits_charged > 0 ) {
			$credits_refunded = (int) $assignment->credits_charged;

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}tp_partners
					 SET lead_credits = lead_credits + %d
					 WHERE id = %d",
					$credits_refunded,
					$partner->id
				)
			);
		}

		return $this->success( array(
			'lead_id'           => $lead_id,
			'assignment_id'     => (int) $assignment->id,
			'status'            => 'rejected',
			'reason'            => $reason,
			'credits_refunded'  => $credits_refunded,
			'refund_eligible'   => $refund_eligible,
			'message'           => 'Lead rejected.' . ( $credits_refunded > 0 ? " {$credits_refunded} credit(s) refunded." : '' ),
		) );
	}

	/**
	 * POST /partner/leads/<id>/feedback
	 *
	 * Submit feedback or outcome update on a lead.
	 * Updates both the assignment record and the lead status.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function submit_feedback( $request ) {
		global $wpdb;

		$valid = $this->validate_required( $request, array( 'status' ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$lead_id    = absint( $request->get_param( 'id' ) );
		$new_status = sanitize_text_field( $request->get_param( 'status' ) );
		$notes      = $request->get_param( 'notes' ) ? sanitize_textarea_field( $request->get_param( 'notes' ) ) : null;
		$followup   = $request->get_param( 'next_followup_date' ) ? sanitize_text_field( $request->get_param( 'next_followup_date' ) ) : null;

		// Validate status.
		$valid_statuses = array( 'contacted', 'site_visit_done', 'negotiating', 'booked', 'lost' );
		if ( ! in_array( $new_status, $valid_statuses, true ) ) {
			return $this->error(
				'invalid_status',
				'Status must be one of: ' . implode( ', ', $valid_statuses ) . '.',
				400
			);
		}

		$partner = $this->get_current_partner();
		if ( ! $partner ) {
			return $this->error( 'partner_not_found', 'Partner record not found.', 404 );
		}

		// Find the assignment.
		$assignment = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_lead_assignments
				 WHERE lead_id = %d AND partner_id = %d",
				$lead_id,
				$partner->id
			)
		);

		if ( ! $assignment ) {
			return $this->error( 'not_found', 'Lead assignment not found.', 404 );
		}

		// Build assignment update data.
		$assignment_update = array(
			'status' => $new_status,
		);
		$assignment_format = array( '%s' );

		if ( $notes ) {
			$assignment_update['partner_notes'] = $notes;
			$assignment_format[] = '%s';
		}

		// Update milestone timestamps based on status.
		switch ( $new_status ) {
			case 'contacted':
				if ( ! $assignment->first_contact_at ) {
					$assignment_update['first_contact_at'] = current_time( 'mysql' );
					$assignment_format[] = '%s';
				}
				$assignment_update['contact_attempts'] = (int) $assignment->contact_attempts + 1;
				$assignment_format[] = '%d';
				break;

			case 'site_visit_done':
				$assignment_update['site_visit_at'] = current_time( 'mysql' );
				$assignment_format[] = '%s';
				break;

			case 'booked':
				$assignment_update['booking_at'] = current_time( 'mysql' );
				$assignment_format[] = '%s';
				break;
		}

		$result = $wpdb->update(
			$wpdb->prefix . 'tp_lead_assignments',
			$assignment_update,
			array( 'id' => $assignment->id ),
			$assignment_format,
			array( '%d' )
		);

		if ( false === $result ) {
			return $this->error( 'update_failed', 'Failed to submit feedback.', 500 );
		}

		// Also update the lead's status to keep it in sync.
		$lead_status_map = array(
			'contacted'       => 'contacted',
			'site_visit_done' => 'site_visit_done',
			'negotiating'     => 'negotiating',
			'booked'          => 'booked',
			'lost'            => 'lost',
		);

		$mapped_lead_status = $lead_status_map[ $new_status ] ?? $new_status;

		$wpdb->update(
			$wpdb->prefix . 'tp_leads',
			array(
				'status'     => $mapped_lead_status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $lead_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		// If conversion (booked), update partner metrics.
		if ( 'booked' === $new_status ) {
			$this->update_partner_conversion_stats( $partner->id );
		}

		$response_data = array(
			'lead_id'       => $lead_id,
			'assignment_id' => (int) $assignment->id,
			'status'        => $new_status,
			'message'       => 'Feedback submitted successfully.',
		);

		if ( $followup ) {
			$response_data['next_followup_date'] = $followup;
		}

		return $this->success( $response_data );
	}

	/**
	 * GET /partner/profile
	 *
	 * Get the current partner's own profile data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_profile( $request ) {
		$partner = $this->get_current_partner();
		if ( ! $partner ) {
			return $this->error( 'partner_not_found', 'Partner record not found.', 404 );
		}

		$data = array(
			'id'                    => (int) $partner->id,
			'company_name'          => $partner->company_name,
			'contact_person'        => $partner->contact_person,
			'phone'                 => $partner->phone,
			'email'                 => $partner->email,
			'partner_type'          => $partner->partner_type,
			'rera_number'           => $partner->rera_number,
			'cities'                => json_decode( $partner->cities ?? '[]', true ),
			'locations'             => json_decode( $partner->locations ?? '[]', true ),
			'projects'              => json_decode( $partner->projects ?? '[]', true ),
			'budget_range'          => array(
				'min' => $partner->budget_range_min ? (int) $partner->budget_range_min : null,
				'max' => $partner->budget_range_max ? (int) $partner->budget_range_max : null,
			),
			'configurations'        => json_decode( $partner->configurations ?? '[]', true ),
			'lead_types'            => json_decode( $partner->lead_types ?? '[]', true ),
			'subscription_plan'     => $partner->subscription_plan,
			'lead_credits'          => (int) $partner->lead_credits,
			'monthly_lead_limit'    => (int) $partner->monthly_lead_limit,
			'daily_lead_limit'      => (int) $partner->daily_lead_limit,
			'exclusivity'           => (bool) $partner->exclusivity,
			'response_sla_hours'    => (int) $partner->response_sla_hours,
			'is_active'             => (bool) $partner->is_active,
			'rating'                => (float) $partner->rating,
			'total_leads_received'  => (int) $partner->total_leads_received,
			'total_leads_converted' => (int) $partner->total_leads_converted,
			'conversion_rate'       => (float) $partner->conversion_rate,
			'avg_response_minutes'  => (int) $partner->avg_response_minutes,
			'business_hours'        => array(
				'start' => $partner->business_hours_start,
				'end'   => $partner->business_hours_end,
			),
			'business_days'         => json_decode( $partner->business_days ?? '[]', true ),
			'created_at'            => $partner->created_at,
			'updated_at'            => $partner->updated_at,
		);

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
	 * Update partner conversion stats after a booking.
	 *
	 * Recalculates total_leads_converted and conversion_rate.
	 *
	 * @param int $partner_id Partner ID.
	 */
	private function update_partner_conversion_stats( $partner_id ) {
		global $wpdb;

		$total_converted = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}tp_lead_assignments
				 WHERE partner_id = %d AND booking_at IS NOT NULL",
				$partner_id
			)
		);

		$total_received = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}tp_lead_assignments
				 WHERE partner_id = %d",
				$partner_id
			)
		);

		$conversion_rate = $total_received > 0
			? round( ( $total_converted / $total_received ) * 100, 2 )
			: 0;

		$wpdb->update(
			$wpdb->prefix . 'tp_partners',
			array(
				'total_leads_converted' => $total_converted,
				'conversion_rate'       => $conversion_rate,
			),
			array( 'id' => $partner_id ),
			array( '%d', '%f' ),
			array( '%d' )
		);
	}
}
