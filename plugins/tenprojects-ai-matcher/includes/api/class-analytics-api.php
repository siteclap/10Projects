<?php
/**
 * Analytics REST API controller.
 *
 * Routes:
 *  POST /analytics/track   — Public: track a frontend event (rate limited).
 *  GET  /analytics/summary — Admin: dashboard summary stats for a date range.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

use TenProjects\Services\Analytics_Service;

class Analytics_API extends API_Base {

	/**
	 * Analytics service instance.
	 *
	 * @var Analytics_Service
	 */
	private $analytics;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->analytics = new Analytics_Service();
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {

		// POST /tenprojects/v1/analytics/track
		register_rest_route(
			$this->namespace,
			'/analytics/track',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'track_event' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => array(
						'event_name' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return ! empty( $value ) && strlen( $value ) <= 100;
							},
						),
						'properties' => array(
							'required' => false,
							'type'     => 'object',
							'default'  => array(),
						),
						'page_url' => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => 'esc_url_raw',
						),
						'referrer' => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => 'esc_url_raw',
						),
						'session_id' => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);

		// GET /tenprojects/v1/analytics/summary
		register_rest_route(
			$this->namespace,
			'/analytics/summary',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_summary' ),
					'permission_callback' => array( $this, 'admin_permissions' ),
					'args'                => array(
						'date_from' => array(
							'required'          => false,
							'type'              => 'string',
							'default'           => date( 'Y-m-d', strtotime( '-30 days' ) ),
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value );
							},
						),
						'date_to' => array(
							'required'          => false,
							'type'              => 'string',
							'default'           => date( 'Y-m-d' ),
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value );
							},
						),
					),
				),
			)
		);
	}

	/**
	 * POST /analytics/track — Track a frontend event.
	 *
	 * Accepts event_name and optional properties from the client-side
	 * analytics script. Rate limited via public_permissions.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function track_event( $request ) {
		$event_name = $request->get_param( 'event_name' );
		$properties = $request->get_param( 'properties' );
		$session_id = $request->get_param( 'session_id' );

		// Whitelist allowed event names to prevent abuse.
		$allowed_events = array(
			'page_view',
			'assessment_started',
			'assessment_completed',
			'project_clicked',
			'project_saved',
			'project_unsaved',
			'share_clicked',
			'callback_requested',
			'whatsapp_clicked',
			'site_visit_scheduled',
			'comparison_started',
			'comparison_shared',
			'guide_read',
			'scroll_depth',
			'cta_clicked',
			'search_performed',
			'filter_applied',
			'location_clicked',
			'developer_clicked',
		);

		if ( ! in_array( $event_name, $allowed_events, true ) ) {
			return $this->error( 'invalid_event', 'Unknown event name.', 400 );
		}

		// Merge page_url and referrer into properties if provided.
		$page_url = $request->get_param( 'page_url' );
		$referrer = $request->get_param( 'referrer' );

		if ( $page_url ) {
			$properties['page_url'] = $page_url;
		}
		if ( $referrer ) {
			$properties['referrer'] = $referrer;
		}

		// Sanitize all property values.
		if ( is_array( $properties ) ) {
			$properties = $this->sanitize_properties( $properties );
		} else {
			$properties = array();
		}

		// Try to get customer from auth token.
		$customer    = $this->get_current_customer( $request );
		$customer_id = $customer ? $customer->id : null;

		$result = $this->analytics->track( $event_name, $properties, $customer_id, $session_id );

		if ( false === $result ) {
			return $this->error( 'tracking_failed', 'Event could not be recorded.', 500 );
		}

		return $this->success( array( 'tracked' => true ), 201 );
	}

	/**
	 * GET /analytics/summary — Dashboard summary for admins.
	 *
	 * Returns total events, unique visitors, top events, funnel stats,
	 * and source breakdown for a given date range.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_summary( $request ) {
		$date_from = $request->get_param( 'date_from' );
		$date_to   = $request->get_param( 'date_to' );

		// Total events.
		$total_events = $this->analytics->get_event_counts( '%', $date_from, $date_to );

		// Count total events (all types) differently — use a direct query.
		global $wpdb;
		$total_events = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}tp_analytics_events
				 WHERE created_at >= %s AND created_at < %s",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		// Unique visitors.
		$unique_visitors = $this->analytics->get_unique_visitors( $date_from, $date_to );

		// Top events.
		$top_events = $this->analytics->get_top_events( 10, $date_from, $date_to );

		// Funnel stats.
		$funnel = $this->analytics->get_funnel_stats( $date_from, $date_to );

		// Source breakdown.
		$sources = $this->analytics->get_source_breakdown( $date_from, $date_to );

		return $this->success( array(
			'date_range'      => array(
				'from' => $date_from,
				'to'   => $date_to,
			),
			'total_events'    => $total_events,
			'unique_visitors' => $unique_visitors,
			'top_events'      => $top_events,
			'funnel_stats'    => $funnel,
			'source_breakdown' => $sources,
		) );
	}

	/**
	 * Sanitize event properties recursively.
	 *
	 * Only allows scalar values and one level of nesting.
	 *
	 * @param array $properties Raw properties.
	 * @return array Sanitized properties.
	 */
	private function sanitize_properties( array $properties ) {
		$clean = array();

		foreach ( $properties as $key => $value ) {
			$key = sanitize_key( $key );

			if ( is_string( $value ) ) {
				$clean[ $key ] = sanitize_text_field( substr( $value, 0, 500 ) );
			} elseif ( is_numeric( $value ) ) {
				$clean[ $key ] = $value;
			} elseif ( is_bool( $value ) ) {
				$clean[ $key ] = $value;
			} elseif ( is_array( $value ) ) {
				// One level of nesting only.
				$nested = array();
				foreach ( $value as $k => $v ) {
					if ( is_scalar( $v ) ) {
						$nested[ sanitize_key( $k ) ] = is_string( $v )
							? sanitize_text_field( substr( $v, 0, 500 ) )
							: $v;
					}
				}
				$clean[ $key ] = $nested;
			}
			// Silently drop anything else (objects, deeply nested arrays).
		}

		return $clean;
	}
}
