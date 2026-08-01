<?php
/**
 * CORS handler for the TenProjects REST API.
 *
 * Allows the Next.js frontend (running on a different origin) to make
 * authenticated cross-origin requests to the WordPress REST API.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects;

defined( 'ABSPATH' ) || exit;

class CORS {

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'add_cors_headers' ], 15 );
		add_filter( 'rest_pre_serve_request', [ $this, 'handle_preflight' ], 10, 4 );
	}

	/**
	 * Get the list of allowed origins.
	 *
	 * Reads from the TP_CORS_ORIGINS constant (comma-separated) or
	 * the `tp_cors_origins` option. Falls back to localhost:3000 in development.
	 *
	 * @return string[]
	 */
	private function get_allowed_origins(): array {
		if ( defined( 'TP_CORS_ORIGINS' ) && TP_CORS_ORIGINS ) {
			return array_map( 'trim', explode( ',', TP_CORS_ORIGINS ) );
		}

		$option = get_option( 'tp_cors_origins', '' );
		if ( $option ) {
			return array_map( 'trim', explode( ',', $option ) );
		}

		// Default for local development.
		return [ 'http://localhost:3000' ];
	}

	/**
	 * Check whether the given origin is allowed.
	 *
	 * @param string $origin The Origin header value.
	 * @return bool
	 */
	private function is_origin_allowed( string $origin ): bool {
		$allowed = $this->get_allowed_origins();
		return in_array( rtrim( $origin, '/' ), array_map( fn( $o ) => rtrim( $o, '/' ), $allowed ), true );
	}

	/**
	 * Add CORS headers to REST API responses.
	 *
	 * Hooked to `rest_api_init` so headers are set before any response is sent.
	 */
	public function add_cors_headers(): void {
		$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

		if ( ! $origin || ! $this->is_origin_allowed( $origin ) ) {
			return;
		}

		header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
		header( 'Access-Control-Allow-Credentials: true' );
		header( 'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS' );
		header( 'Access-Control-Allow-Headers: Content-Type, X-TP-Auth-Token, X-WP-Nonce, Authorization' );
		header( 'Access-Control-Expose-Headers: X-WP-Total, X-WP-TotalPages' );
		header( 'Vary: Origin' );
	}

	/**
	 * Handle OPTIONS preflight requests.
	 *
	 * Intercepts preflight requests and returns a 200 response with the
	 * appropriate CORS headers, preventing WordPress from processing
	 * the request further.
	 *
	 * @param bool              $served  Whether the request has already been served.
	 * @param \WP_HTTP_Response $result  Response object.
	 * @param \WP_REST_Request  $request Request object.
	 * @param \WP_REST_Server   $server  Server instance.
	 * @return bool
	 */
	public function handle_preflight( bool $served, \WP_HTTP_Response $result, \WP_REST_Request $request, \WP_REST_Server $server ): bool {
		if ( 'OPTIONS' !== $request->get_method() ) {
			return $served;
		}

		$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

		if ( ! $origin || ! $this->is_origin_allowed( $origin ) ) {
			return $served;
		}

		// Headers are already set via add_cors_headers().
		// Add cache duration for preflight results.
		header( 'Access-Control-Max-Age: 86400' );

		// Send a 200 OK with no body.
		$server->send_header( 'Content-Type', 'text/plain' );
		http_response_code( 200 );
		echo '';

		return true;
	}
}
