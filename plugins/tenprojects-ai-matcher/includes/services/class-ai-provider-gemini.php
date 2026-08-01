<?php
/**
 * AI Provider: Google Gemini — Generative Language API integration.
 *
 * Implements the Google Gemini API using wp_remote_post().
 * Handles rate limiting, error responses, and token management.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

class AI_Provider_Gemini {

	/**
	 * Gemini API base URL.
	 *
	 * @var string
	 */
	private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models/';

	/**
	 * Default model identifier.
	 *
	 * @var string
	 */
	private const DEFAULT_MODEL = 'gemini-2.0-flash';

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
		return 'gemini' === $provider && ! empty( $this->api_key );
	}

	/**
	 * Send a chat request to the Gemini generateContent API.
	 *
	 * @param string $system_prompt System instruction.
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

		// Use Gemini default if a non-Gemini model is stored.
		if ( empty( $model ) || strpos( $model, 'claude' ) !== false || strpos( $model, 'gpt' ) !== false ) {
			$model = self::DEFAULT_MODEL;
		}

		// Build API URL: models/{model}:generateContent?key={key}.
		$url = self::API_BASE . rawurlencode( $model ) . ':generateContent?key=' . rawurlencode( $this->api_key );

		// Build request body.
		$body = array(
			'contents'           => $this->format_contents( $messages ),
			'generationConfig'   => array(
				'temperature'    => $temperature,
				'maxOutputTokens' => $max_tokens,
			),
		);

		// System instruction via dedicated field.
		if ( ! empty( $system_prompt ) ) {
			$body['system_instruction'] = array(
				'parts' => array(
					array( 'text' => $system_prompt ),
				),
			);
		}

		$attempt = 0;

		while ( $attempt <= self::MAX_RETRIES ) {
			$response = wp_remote_post( $url, array(
				'timeout' => 60,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			) );

			// Check for WordPress HTTP errors.
			if ( is_wp_error( $response ) ) {
				$this->log( 'HTTP error: ' . $response->get_error_message() );
				throw new \Exception( 'Gemini API HTTP error: ' . $response->get_error_message() );
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
					throw new \Exception( 'Gemini API rate limit exceeded' );
				}

				$retry_after = $this->get_retry_delay( $response, $attempt );
				sleep( $retry_after ); // phpcs:ignore WordPress.WP.AlternativeFunctions.sleep_sleep
				continue;
			}

			// Server error — transient, retry once.
			if ( 503 === $status_code || 500 === $status_code ) {
				$attempt++;
				if ( $attempt > self::MAX_RETRIES ) {
					$this->log( 'Server error after retries (HTTP ' . $status_code . ')' );
					throw new \Exception( 'Gemini API server error' );
				}
				sleep( 3 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.sleep_sleep
				continue;
			}

			// Authentication / invalid key — do not retry.
			if ( 400 === $status_code || 401 === $status_code || 403 === $status_code ) {
				$error_msg = $this->extract_error_message( $data ) ?? ( 'HTTP ' . $status_code );
				$this->log( 'Auth/request error: ' . $error_msg );
				throw new \Exception( 'Gemini API error: ' . $error_msg );
			}

			// All other errors.
			$error_msg = $this->extract_error_message( $data ) ?? ( 'HTTP ' . $status_code );
			$this->log( 'API error: ' . $error_msg );
			throw new \Exception( 'Gemini API error: ' . $error_msg );
		}

		return null;
	}

	/**
	 * Format messages for Gemini API.
	 *
	 * Gemini uses 'contents' array with 'role' ('user' or 'model') and 'parts' containing 'text'.
	 * System instructions are passed via the separate 'system_instruction' field.
	 *
	 * @param array $messages Raw messages.
	 * @return array Formatted contents array.
	 */
	private function format_contents( array $messages ) {
		$contents = array();

		foreach ( $messages as $msg ) {
			$role    = $msg['role'] ?? 'user';
			$content = $msg['content'] ?? '';

			// Skip system messages — handled via system_instruction field.
			if ( 'system' === $role ) {
				continue;
			}

			// Map 'assistant' to 'model' (Gemini's naming).
			if ( 'assistant' === $role ) {
				$role = 'model';
			}

			// Ensure valid role.
			if ( ! in_array( $role, array( 'user', 'model' ), true ) ) {
				$role = 'user';
			}

			$contents[] = array(
				'role'  => $role,
				'parts' => array(
					array( 'text' => $content ),
				),
			);
		}

		return $contents;
	}

	/**
	 * Extract text content from Gemini API response.
	 *
	 * Response format: { "candidates": [{ "content": { "parts": [{ "text": "..." }] } }] }
	 *
	 * @param array $data Decoded response body.
	 * @return string|null Extracted text or null.
	 */
	private function extract_text( $data ) {
		if ( ! is_array( $data ) ) {
			return null;
		}

		// Check for blocked responses.
		if ( isset( $data['promptFeedback']['blockReason'] ) ) {
			$this->log( 'Response blocked: ' . $data['promptFeedback']['blockReason'] );
			return null;
		}

		// Standard response path.
		if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
			return trim( $data['candidates'][0]['content']['parts'][0]['text'] );
		}

		// Check if candidate was filtered.
		if ( isset( $data['candidates'][0]['finishReason'] ) ) {
			$reason = $data['candidates'][0]['finishReason'];
			if ( in_array( $reason, array( 'SAFETY', 'RECITATION', 'OTHER' ), true ) ) {
				$this->log( 'Response filtered: ' . $reason );
				return null;
			}
		}

		return null;
	}

	/**
	 * Extract error message from Gemini error response.
	 *
	 * @param array $data Decoded response body.
	 * @return string|null Error message or null.
	 */
	private function extract_error_message( $data ) {
		if ( ! is_array( $data ) ) {
			return null;
		}

		if ( isset( $data['error']['message'] ) ) {
			return $data['error']['message'];
		}

		if ( isset( $data['error']['status'] ) ) {
			return $data['error']['status'];
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
			error_log( '[TenProjects AI][Gemini] ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
