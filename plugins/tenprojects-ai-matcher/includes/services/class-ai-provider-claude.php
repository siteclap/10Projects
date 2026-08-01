<?php
/**
 * AI Provider: Claude (Anthropic) — Messages API integration.
 *
 * Implements the Anthropic Messages API using wp_remote_post().
 * Handles rate limiting, error responses, and token management.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

class AI_Provider_Claude {

	/**
	 * Anthropic API endpoint.
	 *
	 * @var string
	 */
	private const API_URL = 'https://api.anthropic.com/v1/messages';

	/**
	 * Anthropic API version header.
	 *
	 * @var string
	 */
	private const API_VERSION = '2023-06-01';

	/**
	 * Default model identifier.
	 *
	 * @var string
	 */
	private const DEFAULT_MODEL = 'claude-sonnet-4-6';

	/**
	 * Maximum retry attempts for rate-limited requests.
	 *
	 * @var int
	 */
	private const MAX_RETRIES = 2;

	/**
	 * API key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Model ID.
	 *
	 * @var string
	 */
	private $model;

	/**
	 * Constructor — loads settings from WordPress options.
	 */
	public function __construct() {
		$this->api_key = get_option( 'tp_ai_api_key', '' );
		$this->model   = get_option( 'tp_ai_model', self::DEFAULT_MODEL );
	}

	/**
	 * Check if this provider is available (API key configured and provider selected).
	 *
	 * @return bool True if provider can be used.
	 */
	public function is_available() {
		$provider = get_option( 'tp_ai_provider', 'claude' );
		return 'claude' === $provider && ! empty( $this->api_key );
	}

	/**
	 * Send a chat request to the Anthropic Messages API.
	 *
	 * @param string $system_prompt System prompt (sent as top-level `system` parameter).
	 * @param array  $messages      Messages array. Each message: ['role' => 'user'|'assistant', 'content' => '...'].
	 * @param array  $options       Optional overrides: 'temperature', 'max_tokens', 'model'.
	 * @return string|null Response text or null on failure.
	 *
	 * @throws \Exception On unrecoverable API errors.
	 */
	public function chat( string $system_prompt, array $messages, array $options = array() ) {
		if ( ! $this->is_available() ) {
			return null;
		}

		$temperature = $options['temperature'] ?? (float) get_option( 'tp_ai_temperature', 0.7 );
		$max_tokens  = $options['max_tokens'] ?? (int) get_option( 'tp_ai_max_tokens', 1024 );
		$model       = $options['model'] ?? $this->model;

		// Build request body per Anthropic Messages API spec.
		$body = array(
			'model'       => $model,
			'max_tokens'  => $max_tokens,
			'temperature' => $temperature,
			'system'      => $system_prompt,
			'messages'    => $this->format_messages( $messages ),
		);

		$attempt = 0;

		while ( $attempt <= self::MAX_RETRIES ) {
			$response = wp_remote_post( self::API_URL, array(
				'timeout' => 60,
				'headers' => array(
					'Content-Type'     => 'application/json',
					'x-api-key'        => $this->api_key,
					'anthropic-version' => self::API_VERSION,
				),
				'body'    => wp_json_encode( $body ),
			) );

			// Check for WordPress HTTP errors (network failure, timeout, etc.).
			if ( is_wp_error( $response ) ) {
				$this->log( 'HTTP error: ' . $response->get_error_message() );
				throw new \Exception( 'Claude API HTTP error: ' . $response->get_error_message() );
			}

			$status_code = wp_remote_retrieve_response_code( $response );
			$body_raw    = wp_remote_retrieve_body( $response );
			$data        = json_decode( $body_raw, true );

			// Success.
			if ( 200 === $status_code ) {
				return $this->extract_text( $data );
			}

			// Rate limited — wait and retry.
			if ( 429 === $status_code ) {
				$attempt++;
				if ( $attempt > self::MAX_RETRIES ) {
					$this->log( 'Rate limit exceeded after ' . self::MAX_RETRIES . ' retries' );
					throw new \Exception( 'Claude API rate limit exceeded' );
				}

				$retry_after = $this->get_retry_delay( $response, $attempt );
				sleep( $retry_after ); // phpcs:ignore WordPress.WP.AlternativeFunctions.sleep_sleep
				continue;
			}

			// Overloaded — transient, retry once.
			if ( 529 === $status_code ) {
				$attempt++;
				if ( $attempt > self::MAX_RETRIES ) {
					$this->log( 'API overloaded after retries' );
					throw new \Exception( 'Claude API overloaded' );
				}
				sleep( 5 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.sleep_sleep
				continue;
			}

			// Authentication error — do not retry.
			if ( 401 === $status_code ) {
				$this->log( 'Authentication failed — check tp_ai_api_key' );
				throw new \Exception( 'Claude API authentication failed' );
			}

			// All other errors — do not retry.
			$error_msg = $data['error']['message'] ?? ( 'HTTP ' . $status_code );
			$this->log( 'API error: ' . $error_msg );
			throw new \Exception( 'Claude API error: ' . $error_msg );
		}

		return null;
	}

	/**
	 * Format messages for Anthropic API.
	 *
	 * Anthropic expects messages to strictly alternate between 'user' and 'assistant'.
	 * System messages are handled via the top-level 'system' parameter, not in messages.
	 *
	 * @param array $messages Raw messages.
	 * @return array Formatted messages.
	 */
	private function format_messages( array $messages ) {
		$formatted = array();

		foreach ( $messages as $msg ) {
			$role    = $msg['role'] ?? 'user';
			$content = $msg['content'] ?? '';

			// Skip system messages — they are passed separately in Anthropic's API.
			if ( 'system' === $role ) {
				continue;
			}

			// Ensure valid role.
			if ( ! in_array( $role, array( 'user', 'assistant' ), true ) ) {
				$role = 'user';
			}

			$formatted[] = array(
				'role'    => $role,
				'content' => $content,
			);
		}

		return $formatted;
	}

	/**
	 * Extract text content from Anthropic API response.
	 *
	 * Response format: { "content": [{ "type": "text", "text": "..." }] }
	 *
	 * @param array $data Decoded response body.
	 * @return string|null Extracted text or null.
	 */
	private function extract_text( $data ) {
		if ( ! is_array( $data ) ) {
			return null;
		}

		// Standard response: content[0].text.
		if ( isset( $data['content'] ) && is_array( $data['content'] ) ) {
			foreach ( $data['content'] as $block ) {
				if ( isset( $block['type'] ) && 'text' === $block['type'] && isset( $block['text'] ) ) {
					return trim( $block['text'] );
				}
			}

			// Fallback: first content block without type check.
			if ( isset( $data['content'][0]['text'] ) ) {
				return trim( $data['content'][0]['text'] );
			}
		}

		return null;
	}

	/**
	 * Calculate retry delay from response headers or exponential backoff.
	 *
	 * @param array|WP_Error $response HTTP response.
	 * @param int            $attempt  Current attempt number.
	 * @return int Seconds to wait.
	 */
	private function get_retry_delay( $response, int $attempt ) {
		// Check Retry-After header.
		$retry_after = wp_remote_retrieve_header( $response, 'retry-after' );
		if ( ! empty( $retry_after ) && is_numeric( $retry_after ) ) {
			return min( (int) $retry_after, 30 );
		}

		// Exponential backoff: 2s, 4s.
		return min( pow( 2, $attempt ), 30 );
	}

	/**
	 * Log a provider-specific message.
	 *
	 * @param string $message Log message.
	 */
	private function log( string $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[TenProjects AI][Claude] ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
