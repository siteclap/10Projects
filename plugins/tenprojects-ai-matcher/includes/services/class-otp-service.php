<?php
/**
 * OTP delivery service — provider-agnostic OTP generation and sending.
 *
 * Supports MSG91 (default for Indian market) and Twilio as delivery providers.
 * Automatically falls back to dev mode (error_log) when WP_DEBUG is true
 * and no real API key is configured.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

class OTP_Service {

	/**
	 * OTP code length.
	 *
	 * @var int
	 */
	private const OTP_LENGTH = 6;

	/**
	 * Supported OTP providers.
	 *
	 * @var string[]
	 */
	private const PROVIDERS = array( 'msg91', 'twilio' );

	/**
	 * Generate a cryptographically secure 6-digit OTP code.
	 *
	 * @return string 6-digit OTP code (zero-padded).
	 */
	public function generate() {
		$min = (int) pow( 10, self::OTP_LENGTH - 1 );     // 100000
		$max = (int) pow( 10, self::OTP_LENGTH ) - 1;     // 999999

		try {
			$code = random_int( $min, $max );
		} catch ( \Exception $e ) {
			// Fallback to wp_rand if random_int is not available.
			$code = wp_rand( $min, $max );
		}

		return str_pad( (string) $code, self::OTP_LENGTH, '0', STR_PAD_LEFT );
	}

	/**
	 * Send OTP via the configured provider.
	 *
	 * Determines the active provider from WordPress options and dispatches
	 * to the appropriate send method. Falls back to dev mode when debugging
	 * and no API key is configured.
	 *
	 * @param string $phone Sanitized 10-digit phone number.
	 * @param string $code  6-digit OTP code.
	 * @return bool True if OTP was sent successfully.
	 */
	public function send( $phone, $code ) {
		$phone = $this->format_phone( $phone );

		// Check for dev mode: WP_DEBUG active and no real API key configured.
		$api_key = get_option( 'tp_otp_api_key', '' );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && empty( $api_key ) ) {
			return $this->send_dev_mode( $phone, $code );
		}

		$provider = get_option( 'tp_otp_provider', 'msg91' );

		switch ( $provider ) {
			case 'twilio':
				return $this->send_via_twilio( $phone, $code );

			case 'msg91':
			default:
				return $this->send_via_msg91( $phone, $code );
		}
	}

	/**
	 * Send OTP via MSG91 API.
	 *
	 * Uses MSG91's OTP API endpoint with authkey header authentication
	 * and JSON body containing template_id, mobile number, and OTP code.
	 *
	 * @param string $phone Phone number with +91 prefix.
	 * @param string $code  6-digit OTP code.
	 * @return bool True if API call succeeded.
	 */
	private function send_via_msg91( $phone, $code ) {
		$api_key     = get_option( 'tp_otp_api_key', '' );
		$template_id = get_option( 'tp_msg91_template_id', '' );

		if ( empty( $api_key ) || empty( $template_id ) ) {
			error_log( '[TenProjects] MSG91 OTP send failed: API key or template ID not configured.' );
			return false;
		}

		$response = wp_remote_post(
			'https://control.msg91.com/api/v5/otp',
			array(
				'timeout' => 15,
				'headers' => array(
					'authkey'      => $api_key,
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				'body'    => wp_json_encode( array(
					'template_id' => $template_id,
					'mobile'      => $phone,
					'otp'         => $code,
				) ),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( '[TenProjects] MSG91 OTP send error: ' . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status_code < 200 || $status_code >= 300 ) {
			error_log( sprintf(
				'[TenProjects] MSG91 OTP send failed: HTTP %d — %s',
				$status_code,
				wp_json_encode( $body )
			) );
			return false;
		}

		// MSG91 returns {"type":"success"} on success.
		if ( isset( $body['type'] ) && 'success' === $body['type'] ) {
			return true;
		}

		error_log( '[TenProjects] MSG91 OTP unexpected response: ' . wp_json_encode( $body ) );
		return false;
	}

	/**
	 * Send OTP via Twilio API.
	 *
	 * Uses Twilio's Messages API with HTTP Basic authentication
	 * (Account SID:Auth Token) and form-encoded body.
	 *
	 * @param string $phone Phone number with +91 prefix.
	 * @param string $code  6-digit OTP code.
	 * @return bool True if API call succeeded.
	 */
	private function send_via_twilio( $phone, $code ) {
		$account_sid = get_option( 'tp_twilio_sid', '' );
		$auth_token  = get_option( 'tp_otp_api_key', '' );
		$from_number = get_option( 'tp_twilio_from', '' );

		if ( empty( $account_sid ) || empty( $auth_token ) || empty( $from_number ) ) {
			error_log( '[TenProjects] Twilio OTP send failed: Account SID, auth token, or From number not configured.' );
			return false;
		}

		$url = sprintf(
			'https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json',
			rawurlencode( $account_sid )
		);

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 15,
				'headers' => array(
					// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'Authorization' => 'Basic ' . base64_encode( $account_sid . ':' . $auth_token ),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'To'   => $phone,
					'From' => $from_number,
					'Body' => sprintf( 'Your 10Projects verification code is: %s. Valid for 5 minutes. Do not share this code.', $code ),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( '[TenProjects] Twilio OTP send error: ' . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status_code < 200 || $status_code >= 300 ) {
			error_log( sprintf(
				'[TenProjects] Twilio OTP send failed: HTTP %d — %s',
				$status_code,
				$body['message'] ?? wp_json_encode( $body )
			) );
			return false;
		}

		// Twilio returns the message SID on success.
		return ! empty( $body['sid'] );
	}

	/**
	 * Send OTP in development mode.
	 *
	 * Logs the OTP to the WordPress error log instead of sending via SMS.
	 * Active when WP_DEBUG is true AND no real API key is configured.
	 *
	 * @param string $phone Phone number.
	 * @param string $code  6-digit OTP code.
	 * @return bool Always returns true.
	 */
	private function send_dev_mode( $phone, $code ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'TP OTP: Code generated for phone ending in ' . substr( $phone, -4 ) );
		}

		return true;
	}

	/**
	 * Format phone number with +91 country code prefix.
	 *
	 * Accepts a sanitized 10-digit Indian phone number and ensures
	 * it has the +91 international prefix for API calls.
	 *
	 * @param string $phone Sanitized phone number (10 digits or already prefixed).
	 * @return string Phone number with +91 prefix.
	 */
	private function format_phone( $phone ) {
		// Remove any existing prefix to normalize.
		$digits = preg_replace( '/\D/', '', $phone );

		// If already 12 digits starting with 91, just add the +.
		if ( strlen( $digits ) === 12 && str_starts_with( $digits, '91' ) ) {
			return '+' . $digits;
		}

		// If 10 digits, add +91 prefix.
		if ( strlen( $digits ) === 10 ) {
			return '+91' . $digits;
		}

		// Return as-is with + prefix if it looks like an international number.
		return '+' . $digits;
	}
}
