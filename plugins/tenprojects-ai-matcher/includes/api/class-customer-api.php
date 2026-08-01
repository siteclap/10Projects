<?php
/**
 * Customer REST API controller.
 *
 * Provides authenticated endpoints for customer profile management,
 * requirement retrieval, and consent preference management.
 *
 * @package TenProjects\API
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

use TenProjects\Services\Customer_Service;
use TenProjects\Services\Consent_Service;

class Customer_API extends API_Base {

	/**
	 * Customer service instance.
	 *
	 * @var Customer_Service
	 */
	private $customer_service;

	/**
	 * Consent service instance.
	 *
	 * @var Consent_Service
	 */
	private $consent_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->customer_service = new Customer_Service();
		$this->consent_service  = new Consent_Service();
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {

		// GET /customer/profile — get current customer profile.
		register_rest_route(
			$this->namespace,
			'/customer/profile',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_profile' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
				),
			)
		);

		// PUT /customer/profile — update customer profile.
		register_rest_route(
			$this->namespace,
			'/customer/profile',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_profile' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'full_name'         => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'email'             => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_email',
						),
						'preferred_contact' => array(
							'type'              => 'string',
							'enum'              => array( 'phone', 'email', 'whatsapp' ),
							'sanitize_callback' => 'sanitize_text_field',
						),
						'preferred_time'    => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);

		// GET /customer/requirements — list customer's requirements.
		register_rest_route(
			$this->namespace,
			'/customer/requirements',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_requirements' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
				),
			)
		);

		// GET /customer/consent — get consent status.
		register_rest_route(
			$this->namespace,
			'/customer/consent',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_consent' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
				),
			)
		);

		// PUT /customer/consent — update consent preferences.
		register_rest_route(
			$this->namespace,
			'/customer/consent',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_consent' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
					'args'                => array(
						'consent_type' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'action'       => array(
							'required'          => true,
							'type'              => 'string',
							'enum'              => array( 'given', 'withdrawn' ),
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);
	}

	/**
	 * GET /customer/profile
	 *
	 * Return the current customer's profile data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_profile( $request ) {
		$customer = $this->get_current_customer( $request );

		$data = array(
			'id'                => (int) $customer->id,
			'full_name'         => $customer->full_name ?? $customer->name ?? null,
			'email'             => $customer->email ?? null,
			'phone'             => $customer->phone ?? null,
			'phone_verified'    => (bool) ( $customer->phone_verified ?? false ),
			'email_verified'    => (bool) ( $customer->email_verified ?? false ),
			'city'              => $customer->city ?? null,
			'preferred_contact' => $customer->preferred_contact ?? null,
			'preferred_time'    => $customer->preferred_time ?? null,
			'profile_data'      => json_decode( $customer->profile_data ?? '{}', true ),
			'created_at'        => $customer->created_at ?? null,
			'updated_at'        => $customer->updated_at ?? null,
		);

		return $this->success( $data );
	}

	/**
	 * PUT /customer/profile
	 *
	 * Update the current customer's profile fields.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_profile( $request ) {
		$customer = $this->get_current_customer( $request );

		$allowed_fields = array( 'full_name', 'email', 'preferred_contact', 'preferred_time' );
		$update_data    = array();

		foreach ( $allowed_fields as $field ) {
			$value = $request->get_param( $field );
			if ( null !== $value ) {
				$update_data[ $field ] = $value;
			}
		}

		if ( empty( $update_data ) ) {
			return $this->error( 'no_data', 'No valid fields provided for update.', 400 );
		}

		// Validate email format if provided.
		if ( isset( $update_data['email'] ) && ! is_email( $update_data['email'] ) ) {
			return $this->error( 'invalid_email', 'Invalid email address.', 400 );
		}

		// Validate preferred_contact if provided.
		if ( isset( $update_data['preferred_contact'] ) ) {
			$valid_contact = array( 'phone', 'email', 'whatsapp' );
			if ( ! in_array( $update_data['preferred_contact'], $valid_contact, true ) ) {
				return $this->error(
					'invalid_preferred_contact',
					'preferred_contact must be one of: phone, email, whatsapp.',
					400
				);
			}
		}

		$result = $this->customer_service->update( $customer->id, $update_data );

		if ( ! $result ) {
			return $this->error( 'update_failed', 'Failed to update profile.', 500 );
		}

		// Return the updated profile.
		$updated = $this->customer_service->find( $customer->id );

		$data = array(
			'id'                => (int) $updated->id,
			'full_name'         => $updated->full_name ?? $updated->name ?? null,
			'email'             => $updated->email ?? null,
			'phone'             => $updated->phone ?? null,
			'phone_verified'    => (bool) ( $updated->phone_verified ?? false ),
			'email_verified'    => (bool) ( $updated->email_verified ?? false ),
			'city'              => $updated->city ?? null,
			'preferred_contact' => $updated->preferred_contact ?? null,
			'preferred_time'    => $updated->preferred_time ?? null,
			'profile_data'      => json_decode( $updated->profile_data ?? '{}', true ),
			'created_at'        => $updated->created_at ?? null,
			'updated_at'        => $updated->updated_at ?? null,
		);

		return $this->success( $data );
	}

	/**
	 * GET /customer/requirements
	 *
	 * List all requirement sets (assessment results) for the customer.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_requirements( $request ) {
		$customer     = $this->get_current_customer( $request );
		$requirements = $this->customer_service->get_requirements( $customer->id );

		$data = array_map( function ( $req ) {
			return array(
				'id'                       => (int) $req->id,
				'label'                    => $req->label,
				'is_active'                => (bool) $req->is_active,
				'purpose'                  => $req->purpose,
				'property_type'            => $req->property_type,
				'configuration'            => json_decode( $req->configuration ?? '[]', true ),
				'config_flexible'          => (bool) $req->config_flexible,
				'city'                     => $req->city,
				'construction_stage'       => json_decode( $req->construction_stage ?? '[]', true ),
				'preferred_locations'      => json_decode( $req->preferred_locations ?? '[]', true ),
				'alternative_locations'    => json_decode( $req->alternative_locations ?? '[]', true ),
				'location_flexible'        => (bool) $req->location_flexible,
				'workplace_location'       => $req->workplace_location,
				'max_commute_minutes'      => $req->max_commute_minutes ? (int) $req->max_commute_minutes : null,
				'commute_mode'             => $req->commute_mode,
				'proximity_requirements'   => json_decode( $req->proximity_requirements ?? '[]', true ),
				'budget_comfortable'       => $req->budget_comfortable ? (int) $req->budget_comfortable : null,
				'budget_maximum'           => $req->budget_maximum ? (int) $req->budget_maximum : null,
				'funding_type'             => $req->funding_type,
				'down_payment'             => $req->down_payment ? (int) $req->down_payment : null,
				'monthly_emi_comfort'      => $req->monthly_emi_comfort ? (int) $req->monthly_emi_comfort : null,
				'loan_preapproved'         => $req->loan_preapproved,
				'existing_emi'             => (int) $req->existing_emi,
				'payment_plan_preference'  => $req->payment_plan_preference,
				'purchase_timeline_months' => $req->purchase_timeline_months ? (int) $req->purchase_timeline_months : null,
				'possession_preference'    => $req->possession_preference,
				'max_possession_year'      => $req->max_possession_year ? (int) $req->max_possession_year : null,
				'family_adults'            => $req->family_adults ? (int) $req->family_adults : null,
				'family_children'          => $req->family_children ? (int) $req->family_children : null,
				'children_ages'            => json_decode( $req->children_ages ?? '[]', true ),
				'family_seniors'           => $req->family_seniors ? (int) $req->family_seniors : null,
				'pets'                     => $req->pets,
				'risk_tolerance'           => $req->risk_tolerance,
				'developer_preference'     => $req->developer_preference,
				'specific_developer'       => $req->specific_developer,
				'priorities'               => json_decode( $req->priorities ?? '[]', true ),
				'deal_breakers'            => json_decode( $req->deal_breakers ?? '[]', true ),
				'carpet_area_min'          => $req->carpet_area_min ? (int) $req->carpet_area_min : null,
				'carpet_area_max'          => $req->carpet_area_max ? (int) $req->carpet_area_max : null,
				'amenity_preferences'      => json_decode( $req->amenity_preferences ?? '[]', true ),
				'additional_notes'         => $req->additional_notes,
				'created_at'               => $req->created_at,
				'updated_at'               => $req->updated_at,
			);
		}, $requirements );

		return $this->success( $data );
	}

	/**
	 * GET /customer/consent
	 *
	 * Get the current consent status for all consent types.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_consent( $request ) {
		$customer = $this->get_current_customer( $request );
		$status   = $this->consent_service->get_status( $customer->id );

		return $this->success( $status );
	}

	/**
	 * PUT /customer/consent
	 *
	 * Grant or withdraw a consent preference.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_consent( $request ) {
		$required = $this->validate_required( $request, array( 'consent_type', 'action' ) );
		if ( is_wp_error( $required ) ) {
			return $required;
		}

		$customer     = $this->get_current_customer( $request );
		$consent_type = sanitize_text_field( $request->get_param( 'consent_type' ) );
		$action       = sanitize_text_field( $request->get_param( 'action' ) );

		// Validate action value.
		if ( ! in_array( $action, array( 'given', 'withdrawn' ), true ) ) {
			return $this->error(
				'invalid_action',
				'Action must be "given" or "withdrawn".',
				400
			);
		}

		$ip = $this->get_client_ip( $request );

		$result = $this->consent_service->record(
			$customer->id,
			$consent_type,
			$action,
			$ip,
			'api_update'
		);

		if ( false === $result ) {
			return $this->error(
				'invalid_consent_type',
				'Invalid consent type provided.',
				400
			);
		}

		// Return updated consent status.
		$status = $this->consent_service->get_status( $customer->id );

		return $this->success( $status );
	}
}
