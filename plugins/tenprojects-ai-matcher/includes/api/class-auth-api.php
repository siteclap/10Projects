<?php
/**
 * Authentication REST API controller.
 *
 * Provides public endpoints for phone OTP login (primary auth method)
 * and Google OAuth, plus an authenticated endpoint for logout.
 *
 * Routes:
 *   POST /tenprojects/v1/auth/send-otp   — Request OTP
 *   POST /tenprojects/v1/auth/verify-otp  — Verify OTP and login
 *   POST /tenprojects/v1/auth/google      — Google OAuth login
 *   POST /tenprojects/v1/auth/logout      — Logout (authenticated)
 *
 * @package TenProjects\API
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

use TenProjects\Services\Auth_Service;

class Auth_API extends API_Base {

	/**
	 * Auth service instance.
	 *
	 * @var Auth_Service
	 */
	private $auth_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->auth_service = new Auth_Service();
	}

	/**
	 * Register authentication routes.
	 */
	public function register_routes() {

		// POST /auth/send-otp — request OTP for phone login.
		register_rest_route(
			$this->namespace,
			'/auth/send-otp',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'send_otp' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => array(
						'phone' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'description'       => 'Indian mobile number (10 digits).',
						),
					),
				),
			)
		);

		// POST /auth/verify-otp — verify OTP and authenticate.
		register_rest_route(
			$this->namespace,
			'/auth/verify-otp',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'verify_otp' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => array(
						'phone' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'description'       => 'Phone number that OTP was sent to.',
						),
						'otp'   => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'description'       => '6-digit OTP code.',
						),
					),
				),
			)
		);

		// POST /auth/google — Google OAuth login.
		register_rest_route(
			$this->namespace,
			'/auth/google',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'google_login' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => array(
						'id_token' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'description'       => 'Google OAuth ID token.',
						),
					),
				),
			)
		);

		// POST /auth/logout — clear login token (authenticated).
		register_rest_route(
			$this->namespace,
			'/auth/logout',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'logout' ),
					'permission_callback' => array( $this, 'customer_permissions' ),
				),
			)
		);
	}

	/**
	 * POST /auth/send-otp
	 *
	 * Send an OTP to the provided phone number. The response does NOT
	 * include the OTP code itself for security reasons.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function send_otp( $request ) {
		$required = $this->validate_required( $request, array( 'phone' ) );
		if ( is_wp_error( $required ) ) {
			return $required;
		}

		$phone  = sanitize_text_field( $request->get_param( 'phone' ) );
		$result = $this->auth_service->send_otp( $phone );

		if ( ! $result['success'] ) {
			$status = 400;

			// Rate limited responses get 429.
			if ( isset( $result['retry_after'] ) ) {
				$status = 429;
			}

			return $this->error(
				'otp_send_failed',
				$result['message'],
				$status,
				array_filter( array(
					'retry_after'        => $result['retry_after'] ?? null,
					'remaining_attempts' => $result['remaining_attempts'] ?? null,
				) )
			);
		}

		return $this->success( array(
			'message'            => $result['message'],
			'remaining_attempts' => $result['remaining_attempts'],
		) );
	}

	/**
	 * POST /auth/verify-otp
	 *
	 * Verify the OTP code and authenticate the customer.
	 * Returns customer UUID, login token, and whether this is a new customer.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function verify_otp( $request ) {
		$required = $this->validate_required( $request, array( 'phone', 'otp' ) );
		if ( is_wp_error( $required ) ) {
			return $required;
		}

		$phone = sanitize_text_field( $request->get_param( 'phone' ) );
		$otp   = sanitize_text_field( $request->get_param( 'otp' ) );
		$ip    = $this->get_client_ip( $request );

		$result = $this->auth_service->verify_otp( $phone, $otp, $ip );

		if ( ! $result['success'] ) {
			return $this->error(
				'otp_verify_failed',
				$result['message'],
				401,
				array_filter( array(
					'remaining_attempts' => $result['remaining_attempts'] ?? null,
				) )
			);
		}

		return $this->success( array(
			'message'  => $result['message'],
			'customer' => array(
				'uuid'           => $result['customer']['uuid'],
				'phone'          => $result['customer']['phone'],
				'phone_verified' => $result['customer']['phone_verified'],
				'full_name'      => $result['customer']['full_name'],
				'email'          => $result['customer']['email'],
			),
			'token'  => $result['token'],
			'is_new' => $result['is_new'],
		) );
	}

	/**
	 * POST /auth/google
	 *
	 * Authenticate via Google OAuth ID token.
	 * Returns customer data and login token.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function google_login( $request ) {
		$required = $this->validate_required( $request, array( 'id_token' ) );
		if ( is_wp_error( $required ) ) {
			return $required;
		}

		$id_token = sanitize_text_field( $request->get_param( 'id_token' ) );
		$ip       = $this->get_client_ip( $request );

		$result = $this->auth_service->verify_google( $id_token, $ip );

		if ( ! $result['success'] ) {
			return $this->error(
				'google_auth_failed',
				$result['message'],
				401
			);
		}

		return $this->success( array(
			'message'  => $result['message'],
			'customer' => array(
				'uuid'           => $result['customer']['uuid'],
				'phone'          => $result['customer']['phone'],
				'phone_verified' => $result['customer']['phone_verified'],
				'full_name'      => $result['customer']['full_name'],
				'email'          => $result['customer']['email'],
			),
			'token'  => $result['token'],
			'is_new' => $result['is_new'],
		) );
	}

	/**
	 * POST /auth/logout
	 *
	 * Clear the authenticated customer's login token.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function logout( $request ) {
		$customer = $this->get_current_customer( $request );

		if ( ! $customer ) {
			return $this->error( 'unauthorized', 'Authentication required.', 401 );
		}

		$result = $this->auth_service->logout( $customer->id );

		if ( ! $result['success'] ) {
			return $this->error( 'logout_failed', $result['message'], 500 );
		}

		return $this->success( array(
			'message' => $result['message'],
		) );
	}
}
