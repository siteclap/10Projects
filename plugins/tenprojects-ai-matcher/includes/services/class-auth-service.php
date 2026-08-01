<?php
/**
 * Authentication service — OTP login, Google OAuth, token management.
 *
 * Phone-based OTP login is the primary auth method (Indian market, mobile-first).
 * Google OAuth is supported as an alternative.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

use TenProjects\Helpers\Validator;
use TenProjects\Helpers\Rate_Limiter;
use TenProjects\Helpers\Sanitizer;

class Auth_Service {

	/**
	 * Customer service instance.
	 *
	 * @var Customer_Service
	 */
	private $customer_service;

	/**
	 * OTP service instance.
	 *
	 * @var OTP_Service
	 */
	private $otp_service;

	/**
	 * Consent service instance.
	 *
	 * @var Consent_Service
	 */
	private $consent_service;

	/**
	 * OTP transient prefix.
	 *
	 * @var string
	 */
	private const OTP_TRANSIENT_PREFIX = 'tp_otp_';

	/**
	 * OTP TTL in seconds (5 minutes).
	 *
	 * @var int
	 */
	private const OTP_TTL = 300;

	/**
	 * Maximum verification attempts per OTP.
	 *
	 * @var int
	 */
	private const MAX_VERIFY_ATTEMPTS = 3;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->customer_service = new Customer_Service();
		$this->otp_service      = new OTP_Service();
		$this->consent_service  = new Consent_Service();
	}

	/**
	 * Send OTP to a phone number.
	 *
	 * Validates the phone number, checks rate limits, generates OTP,
	 * stores it in a transient, and sends via the OTP service.
	 *
	 * @param string $phone Phone number.
	 * @return array {
	 *     @type bool   $success            Whether OTP was sent successfully.
	 *     @type string $message            Status message.
	 *     @type int    $remaining_attempts Remaining OTP send attempts in the rate limit window.
	 * }
	 */
	public function send_otp( $phone ) {
		// Validate phone number.
		if ( ! Validator::is_valid_phone( $phone ) ) {
			return array(
				'success' => false,
				'message' => 'Invalid phone number. Please enter a valid 10-digit Indian mobile number.',
			);
		}

		$clean_phone = Sanitizer::phone( $phone );

		// Check rate limit (3 OTP requests per 10 minutes per phone).
		if ( Rate_Limiter::throttle_otp( $clean_phone ) ) {
			$retry_after = Rate_Limiter::retry_after( 'otp_' . $clean_phone, 600 );

			return array(
				'success'     => false,
				'message'     => 'Too many OTP requests. Please try again later.',
				'retry_after' => $retry_after,
			);
		}

		// Generate OTP.
		$otp_code = $this->otp_service->generate();

		// Store OTP in transient.
		$phone_hash    = $this->get_phone_hash( $clean_phone );
		$transient_key = self::OTP_TRANSIENT_PREFIX . $phone_hash;

		$otp_data = array(
			'code'       => $otp_code,
			'attempts'   => 0,
			'created_at' => time(),
		);

		set_transient( $transient_key, $otp_data, self::OTP_TTL );

		// Send OTP via provider.
		$sent = $this->otp_service->send( $clean_phone, $otp_code );

		if ( ! $sent ) {
			// Clean up transient if send failed.
			delete_transient( $transient_key );

			return array(
				'success' => false,
				'message' => 'Failed to send OTP. Please try again.',
			);
		}

		$remaining = Rate_Limiter::remaining( 'otp_' . $clean_phone, 3 );

		return array(
			'success'            => true,
			'message'            => 'OTP sent successfully.',
			'remaining_attempts' => $remaining,
		);
	}

	/**
	 * Verify OTP code for a phone number.
	 *
	 * Validates the OTP format, checks against stored OTP, handles expiry
	 * and attempt limits. On success: finds or creates the customer,
	 * marks phone verified, generates a login token, and records consent.
	 *
	 * @param string $phone    Phone number.
	 * @param string $otp_code OTP code entered by user.
	 * @param string $ip       Client IP address for consent logging.
	 * @return array {
	 *     @type bool   $success  Whether verification succeeded.
	 *     @type string $message  Status message.
	 *     @type array  $customer Customer data (on success).
	 *     @type string $token    Login token (on success).
	 *     @type bool   $is_new   Whether this is a new customer (on success).
	 *     @type int    $remaining_attempts Remaining verification attempts (on failure).
	 * }
	 */
	public function verify_otp( $phone, $otp_code, $ip = '' ) {
		// Validate phone.
		if ( ! Validator::is_valid_phone( $phone ) ) {
			return array(
				'success' => false,
				'message' => 'Invalid phone number.',
			);
		}

		// Validate OTP format.
		if ( ! Validator::is_valid_otp( $otp_code ) ) {
			return array(
				'success' => false,
				'message' => 'Invalid OTP format. Please enter a 6-digit code.',
			);
		}

		$clean_phone   = Sanitizer::phone( $phone );
		$phone_hash    = $this->get_phone_hash( $clean_phone );
		$transient_key = self::OTP_TRANSIENT_PREFIX . $phone_hash;

		// Retrieve stored OTP.
		$otp_data = get_transient( $transient_key );

		if ( false === $otp_data ) {
			return array(
				'success' => false,
				'message' => 'OTP has expired. Please request a new one.',
			);
		}

		// Check verification attempts.
		if ( $otp_data['attempts'] >= self::MAX_VERIFY_ATTEMPTS ) {
			// Invalidate the OTP after max attempts.
			delete_transient( $transient_key );

			return array(
				'success'            => false,
				'message'            => 'Too many incorrect attempts. Please request a new OTP.',
				'remaining_attempts' => 0,
			);
		}

		// Check expiry (belt-and-suspenders; transient TTL handles this too).
		if ( ( time() - $otp_data['created_at'] ) > self::OTP_TTL ) {
			delete_transient( $transient_key );

			return array(
				'success' => false,
				'message' => 'OTP has expired. Please request a new one.',
			);
		}

		// Verify the OTP code.
		if ( ! hash_equals( (string) $otp_data['code'], (string) $otp_code ) ) {
			// Increment attempts.
			$otp_data['attempts']++;
			$remaining_ttl = self::OTP_TTL - ( time() - $otp_data['created_at'] );
			set_transient( $transient_key, $otp_data, max( 1, $remaining_ttl ) );

			$remaining = self::MAX_VERIFY_ATTEMPTS - $otp_data['attempts'];

			return array(
				'success'            => false,
				'message'            => 'Incorrect OTP. Please try again.',
				'remaining_attempts' => $remaining,
			);
		}

		// OTP verified successfully — clean up.
		delete_transient( $transient_key );

		// Find or create customer.
		$existing_customer = $this->customer_service->find_by_phone( $clean_phone );
		$is_new            = ! $existing_customer;

		$customer = $this->customer_service->find_or_create( array(
			'phone' => $clean_phone,
		) );

		if ( ! $customer ) {
			return array(
				'success' => false,
				'message' => 'Failed to create customer record. Please try again.',
			);
		}

		// Mark phone as verified.
		$this->customer_service->update( $customer->id, array(
			'phone_verified' => 1,
		) );

		// Generate login token.
		$token = $this->customer_service->generate_login_token( $customer->id );

		// Record data collection consent (implicit with OTP verification).
		$this->consent_service->record(
			$customer->id,
			'data_collection',
			'given',
			$ip,
			'otp_verification'
		);

		// Refresh customer data after updates.
		$customer = $this->customer_service->find( $customer->id );

		return array(
			'success'  => true,
			'message'  => 'Phone verified successfully.',
			'customer' => array(
				'id'             => (int) $customer->id,
				'uuid'           => $customer->uuid ?? null,
				'phone'          => $customer->phone,
				'phone_verified' => true,
				'full_name'      => $customer->full_name ?? $customer->name ?? null,
				'email'          => $customer->email ?? null,
			),
			'token'  => $token,
			'is_new' => $is_new,
		);
	}

	/**
	 * Verify Google OAuth ID token and authenticate customer.
	 *
	 * Verifies the token via Google's tokeninfo endpoint, extracts email
	 * and name, then finds or creates the customer record.
	 *
	 * @param string $id_token Google OAuth ID token.
	 * @param string $ip       Client IP address for consent logging.
	 * @return array {
	 *     @type bool   $success  Whether verification succeeded.
	 *     @type string $message  Status message.
	 *     @type array  $customer Customer data (on success).
	 *     @type string $token    Login token (on success).
	 *     @type bool   $is_new   Whether this is a new customer (on success).
	 * }
	 */
	public function verify_google( $id_token, $ip = '' ) {
		if ( empty( $id_token ) ) {
			return array(
				'success' => false,
				'message' => 'ID token is required.',
			);
		}

		// Verify token with Google.
		$response = wp_remote_get(
			'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode( $id_token ),
			array(
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => 'Failed to verify Google token. Please try again.',
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return array(
				'success' => false,
				'message' => 'Invalid or expired Google token.',
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['email'] ) ) {
			return array(
				'success' => false,
				'message' => 'Unable to retrieve email from Google account.',
			);
		}

		// Verify the token audience matches our client ID.
		$expected_client_id = get_option( 'tp_google_client_id', '' );
		if ( ! empty( $expected_client_id ) && ( $body['aud'] ?? '' ) !== $expected_client_id ) {
			return array(
				'success' => false,
				'message' => 'Token was not issued for this application.',
			);
		}

		// Verify email is verified in Google's response.
		if ( empty( $body['email_verified'] ) || 'true' !== $body['email_verified'] ) {
			return array(
				'success' => false,
				'message' => 'Google email is not verified.',
			);
		}

		$email     = sanitize_email( $body['email'] );
		$full_name = sanitize_text_field( $body['name'] ?? '' );

		// Find or create customer.
		$existing_customer = $this->customer_service->find_by_email( $email );
		$is_new            = ! $existing_customer;

		$customer = $this->customer_service->find_or_create( array(
			'email'     => $email,
			'full_name' => $full_name,
		) );

		if ( ! $customer ) {
			return array(
				'success' => false,
				'message' => 'Failed to create customer record. Please try again.',
			);
		}

		// Update name if it was empty.
		$update_data = array();
		if ( ! empty( $full_name ) && empty( $customer->full_name ) && empty( $customer->name ) ) {
			$update_data['full_name'] = $full_name;
		}
		if ( ! empty( $update_data ) ) {
			$this->customer_service->update( $customer->id, $update_data );
		}

		// Generate login token.
		$token = $this->customer_service->generate_login_token( $customer->id );

		// Record consent.
		$this->consent_service->record(
			$customer->id,
			'data_collection',
			'given',
			$ip,
			'google_oauth'
		);

		// Refresh customer data.
		$customer = $this->customer_service->find( $customer->id );

		return array(
			'success'  => true,
			'message'  => 'Google authentication successful.',
			'customer' => array(
				'id'             => (int) $customer->id,
				'uuid'           => $customer->uuid ?? null,
				'phone'          => $customer->phone ?? null,
				'phone_verified' => (bool) ( $customer->phone_verified ?? false ),
				'full_name'      => $customer->full_name ?? $customer->name ?? null,
				'email'          => $customer->email ?? null,
			),
			'token'  => $token,
			'is_new' => $is_new,
		);
	}

	/**
	 * Log out a customer by clearing their login token.
	 *
	 * @param int $customer_id Customer ID.
	 * @return array {
	 *     @type bool   $success Whether logout succeeded.
	 *     @type string $message Status message.
	 * }
	 */
	public function logout( $customer_id ) {
		$customer = $this->customer_service->find( absint( $customer_id ) );

		if ( ! $customer ) {
			return array(
				'success' => false,
				'message' => 'Customer not found.',
			);
		}

		$this->customer_service->update( $customer->id, array(
			'login_token'      => null,
			'token_expires_at' => null,
		) );

		return array(
			'success' => true,
			'message' => 'Logged out successfully.',
		);
	}

	/**
	 * Get authenticated customer by login token.
	 *
	 * Looks up the customer by token and verifies the token has not expired.
	 *
	 * @param string $token Login token.
	 * @return object|null Customer object or null if token is invalid/expired.
	 */
	public function get_authenticated_customer( $token ) {
		global $wpdb;

		if ( empty( $token ) ) {
			return null;
		}

		$customer = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_customers
				 WHERE login_token = %s
				 AND token_expires_at > %s
				 AND deleted_at IS NULL",
				sanitize_text_field( $token ),
				current_time( 'mysql' )
			)
		);

		return $customer ?: null;
	}

	/**
	 * Generate a hashed key for phone-based transient storage.
	 *
	 * Uses a hash to avoid storing raw phone numbers in transient keys.
	 *
	 * @param string $phone Sanitized phone number (10 digits).
	 * @return string Hashed phone key.
	 */
	private function get_phone_hash( $phone ) {
		return wp_hash( $phone, 'nonce' );
	}
}
