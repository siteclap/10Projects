<?php
/**
 * Analytics Service — server-side event tracking and funnel reporting.
 *
 * Inserts events into tp_analytics_events, provides aggregation methods
 * for funnel stats, event counts, top events, and UTM source breakdowns.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

class Analytics_Service {

	/**
	 * Track a generic event.
	 *
	 * @param string      $event_name  Event name (e.g., 'page_view', 'assessment_started').
	 * @param array       $properties  Arbitrary key-value properties (stored as JSON).
	 * @param int|null    $customer_id Customer ID (null for anonymous visitors).
	 * @param string|null $session_id  Visitor/session identifier.
	 * @return int|false Inserted row ID or false on failure.
	 */
	public function track( $event_name, array $properties = array(), $customer_id = null, $session_id = null ) {
		global $wpdb;

		if ( empty( $event_name ) ) {
			return false;
		}

		// Do not track bot traffic.
		if ( $this->is_bot() ) {
			return false;
		}

		$data = array(
			'event_name'  => sanitize_text_field( $event_name ),
			'properties'  => wp_json_encode( $properties ),
			'customer_id' => $customer_id ? absint( $customer_id ) : null,
			'session_id'  => $session_id ? sanitize_text_field( $session_id ) : null,
			'page_url'    => isset( $properties['page_url'] ) ? esc_url_raw( $properties['page_url'] ) : '',
			'referrer'    => isset( $properties['referrer'] ) ? esc_url_raw( $properties['referrer'] ) : '',
			'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( substr( $_SERVER['HTTP_USER_AGENT'], 0, 500 ) ) : '',
			'ip_address'  => $this->get_hashed_ip(),
			'utm_source'  => isset( $properties['utm_source'] ) ? sanitize_text_field( $properties['utm_source'] ) : null,
			'utm_medium'  => isset( $properties['utm_medium'] ) ? sanitize_text_field( $properties['utm_medium'] ) : null,
			'utm_campaign' => isset( $properties['utm_campaign'] ) ? sanitize_text_field( $properties['utm_campaign'] ) : null,
			'created_at'  => current_time( 'mysql' ),
		);

		$formats = array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

		// Null customer_id needs special handling.
		if ( null === $data['customer_id'] ) {
			$data['customer_id'] = null;
			// Replace %d with null-safe format.
		}

		$result = $wpdb->insert( $wpdb->prefix . 'tp_analytics_events', $data );

		if ( false === $result ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Convenience: track a page view event.
	 *
	 * @param string      $page_type Page type identifier (e.g., 'homepage', 'project', 'location').
	 * @param string      $page_url  Full page URL.
	 * @param string      $referrer  HTTP referrer.
	 * @param int|null    $customer_id Customer ID.
	 * @param string|null $session_id  Session ID.
	 * @return int|false
	 */
	public function track_page_view( $page_type, $page_url, $referrer = '', $customer_id = null, $session_id = null ) {
		return $this->track( 'page_view', array(
			'page_type' => $page_type,
			'page_url'  => $page_url,
			'referrer'  => $referrer,
		), $customer_id, $session_id );
	}

	/**
	 * Get funnel stage counts for a date range.
	 *
	 * Funnel stages:
	 *  1. page_view            — visited the site
	 *  2. assessment_started   — started AI assessment
	 *  3. assessment_completed — completed assessment
	 *  4. project_clicked      — clicked a recommended project
	 *  5. callback_requested   — requested a callback
	 *  6. site_visit_scheduled — scheduled a site visit
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to   End date (Y-m-d).
	 * @return array Associative array with stage => count.
	 */
	public function get_funnel_stats( $date_from, $date_to ) {
		global $wpdb;

		$stages = array(
			'page_view',
			'assessment_started',
			'assessment_completed',
			'project_clicked',
			'callback_requested',
			'site_visit_scheduled',
		);

		$table = $wpdb->prefix . 'tp_analytics_events';
		$stats = array();

		foreach ( $stages as $stage ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table}
					 WHERE event_name = %s
					   AND created_at >= %s
					   AND created_at < %s",
					$stage,
					$date_from . ' 00:00:00',
					$date_to . ' 23:59:59'
				)
			);
			$stats[ $stage ] = (int) $count;
		}

		return $stats;
	}

	/**
	 * Get count of a specific event in a date range.
	 *
	 * @param string $event_name Event name.
	 * @param string $date_from  Start date (Y-m-d).
	 * @param string $date_to    End date (Y-m-d).
	 * @return int
	 */
	public function get_event_counts( $event_name, $date_from, $date_to ) {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}tp_analytics_events
				 WHERE event_name = %s
				   AND created_at >= %s
				   AND created_at < %s",
				sanitize_text_field( $event_name ),
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		return (int) $count;
	}

	/**
	 * Get the most common events in a date range.
	 *
	 * @param int    $limit     Number of events to return.
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to   End date (Y-m-d).
	 * @return array Array of objects with event_name and count.
	 */
	public function get_top_events( $limit, $date_from, $date_to ) {
		global $wpdb;

		$limit = min( 50, max( 1, absint( $limit ) ) );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT event_name, COUNT(*) AS event_count
				 FROM {$wpdb->prefix}tp_analytics_events
				 WHERE created_at >= %s
				   AND created_at < %s
				 GROUP BY event_name
				 ORDER BY event_count DESC
				 LIMIT %d",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59',
				$limit
			)
		);

		return $results ?: array();
	}

	/**
	 * Get traffic source breakdown from UTM parameters.
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to   End date (Y-m-d).
	 * @return array Array of objects with utm_source, utm_medium, event_count.
	 */
	public function get_source_breakdown( $date_from, $date_to ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					COALESCE(utm_source, 'direct') AS utm_source,
					COALESCE(utm_medium, 'none') AS utm_medium,
					COUNT(*) AS event_count
				 FROM {$wpdb->prefix}tp_analytics_events
				 WHERE event_name = 'page_view'
				   AND created_at >= %s
				   AND created_at < %s
				 GROUP BY COALESCE(utm_source, 'direct'), COALESCE(utm_medium, 'none')
				 ORDER BY event_count DESC
				 LIMIT 50",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		return $results ?: array();
	}

	/**
	 * Get count of unique visitors (by hashed IP) in a date range.
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to   End date (Y-m-d).
	 * @return int
	 */
	public function get_unique_visitors( $date_from, $date_to ) {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT ip_address) FROM {$wpdb->prefix}tp_analytics_events
				 WHERE event_name = 'page_view'
				   AND created_at >= %s
				   AND created_at < %s",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		return (int) $count;
	}

	/**
	 * Check if the current user agent is a known bot.
	 *
	 * @return bool
	 */
	private function is_bot() {
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		if ( empty( $ua ) ) {
			return true;
		}

		$bots = array(
			'Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot', 'Baiduspider',
			'YandexBot', 'facebookexternalhit', 'Twitterbot', 'LinkedInBot',
			'Applebot', 'Semrush', 'AhrefsBot', 'MJ12bot', 'Dotbot',
			'crawler', 'spider', 'bot/', 'bot;',
		);

		foreach ( $bots as $bot ) {
			if ( stripos( $ua, $bot ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get a hashed version of the client IP for privacy-safe storage.
	 *
	 * We hash the IP so we can count unique visitors without storing raw IPs.
	 * The hash is salted with a daily rotation key so it cannot be reversed.
	 *
	 * @return string 64-char hex hash.
	 */
	private function get_hashed_ip() {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$ip   = sanitize_text_field( trim( $ip ) );
		$salt = wp_salt( 'auth' ) . date( 'Y-m-d' );

		return hash( 'sha256', $ip . $salt );
	}
}
