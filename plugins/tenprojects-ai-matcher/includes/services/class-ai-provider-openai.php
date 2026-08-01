<?php
/**
 * AI Provider: OpenAI — Chat Completions API integration.
 *
 * Implements the OpenAI Chat Completions API using wp_remote_post().
 * Handles rate limiting, error responses, and token management.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

class AI_Provider_OpenAI {

	/**
	 * OpenAI API endpoint.
	 *
	 * @var string
	 */
	private const API_URL = 'https://api.openai.com/v1/chat/completions';

	/**
	 * Default model identifier.
	 *
	 * @var string
	 */
	private const DEFAULT_MODEL = 'gpt-4o';

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
		return 'openai' === $provider && ! empty( $this->api_key );
	}

	/**
	 * Send a chat request to the OpenAI Chat Completions API.
	 *
	 * @param string $system_prompt System prompt (sent as first message with role 'system').
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

		// OpenAI uses model-specific default if not set.
		if ( empty( $model ) || 'claude-sonnet-4-6' === $model ) {
			$model = self::DEFAULT_MODEL;
		}

		// Build messages with system prompt as the first message.
		$api_messages = $this->format_messages( $system_prompt, $messages );

		$body = array(
			'model'       => $model,
			'messages'    => $api_messages,
			'max_tokens'  => $max_tokens,
			'temperature' => $temperature,
		);

		$attempt = 0;

		while ( $attempt <= self::MAX_RETRIES ) {
			$response = wp_remote_post( self::API_URL, array(
				'timeout' => 60,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $this->api_key,
				),
				'body'    => wp_json_encode( $body ),
			) );

			// Check for WordPress HTTP errors.
			if ( is_wp_error( $response ) ) {
				$this->log( 'HTTP error: ' . $response->get_error_message() );
				throw new \Exception( 'OpenAI API HTTP error: ' . $response->get_error_message() );
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
					throw new \Exception( 'OpenAI API rate limit exceeded' );
				}

				$retry_after = $this->get_retry_delay( $response, $attempt );
				sleep( $retry_after ); // phpcs:ignore WordPress.WP.AlternativeFunctions.sleep_sleep
				continue;
			}

			// Server error — transient, retry once.
			if ( $status_code >= 500 ) {
				$attempt++;
				if ( $attempt > self::MAX_RETRIES ) {
					$this->log( 'Server error after retries (HTTP ' . $status_code . ')' );
					throw new \Exception( 'OpenAI API server error' );
				}
				sleep( 3 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.sleep_sleep
				continue;
			}

			// Authentication error — do not retry.
			if ( 401 === $status_code ) {
				$this->log( 'Authentication failed — check tp_ai_api_key' );
				throw new \Exception( 'OpenAI API authentication failed' );
			}

			// Insufficient quota.
			if ( 402 === $status_code ) {
				$this->log( 'Insufficient quota — billing issue' );
				throw new \Exception( 'OpenAI API insufficient quota' );
			}

			// All other errors — do not retry.
			$error_msg = $data['error']['message'] ?? ( 'HTTP ' . $status_code );
			$this->log( 'API error: ' . $error_msg );
			throw new \Exception( 'OpenAI API error: ' . $error_msg );
		}

		return null;
	}

	/**
	 * Format messages for OpenAI Chat Completions API.
	 *
	 * OpenAI expects system prompt as the first message with role 'system',
	 * followed by alternating user/assistant messages.
	 *
	 * @param string $system_prompt System instruction.
	 * @param array  $messages      Raw messages.
	 * @return array Formatted messages array.
	 */
	private function format_messages( string $system_prompt, array $messages ) {
		$formatted = array();

		// System prompt as first message.
		if ( ! empty( $system_prompt ) ) {
			$formatted[] = array(
				'role'    => 'system',
				'content' => $system_prompt,
			);
		}

		foreach ( $messages as $msg ) {
			$role    = $msg['role'] ?? 'user';
			$content = $msg['content'] ?? '';

			// Skip duplicate system messages — already added above.
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
	 * Extract text content from OpenAI API response.
	 *
	 * Response format: { "choices": [{ "message": { "content": "..." } }] }
	 *
	 * @param array $data Decoded response body.
	 * @return string|null Extracted text or null.
	 */
	private function extract_text( $data ) {
		if ( ! is_array( $data ) ) {
			return null;
		}

		if ( isset( $data['choices'][0]['message']['content'] ) ) {
			return trim( $data['choices'][0]['message']['content'] );
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

		// Check x-ratelimit-reset-requests header.
		$reset = wp_remote_retrieve_header( $response, 'x-ratelimit-reset-requests' );
		if ( ! empty( $reset ) && is_numeric( $reset ) ) {
			return min( (int) ceil( (float) $reset ), 30 );
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
			error_log( '[TenProjects AI][OpenAI] ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
