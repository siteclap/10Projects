<?php
/**
 * Analytics & Dashboard admin page.
 *
 * Renders the main plugin dashboard with stat cards, lead funnel,
 * top performing locations, and assessment completion rate.
 * Uses simple HTML/CSS — no external chart library required.
 *
 * @package TenProjects\Admin
 * @since   1.0.0
 */

namespace TenProjects\Admin;

defined( 'ABSPATH' ) || exit;

class Analytics_Admin {

	/**
	 * Render the main plugin dashboard (top-level menu default page).
	 */
	public function render_dashboard(): void {
		global $wpdb;

		$leads_table    = $wpdb->prefix . 'tp_leads';
		$sessions_table = $wpdb->prefix . 'tp_ai_sessions';
		$scores_table   = $wpdb->prefix . 'tp_project_scores';
		$visits_table   = $wpdb->prefix . 'tp_site_visits';

		// Date range — default to current month.
		$date_from = isset( $_REQUEST['tp_date_from'] ) // phpcs:ignore WordPress.Security.NonceVerification
			? sanitize_text_field( wp_unslash( $_REQUEST['tp_date_from'] ) )
			: gmdate( 'Y-m-01' );
		$date_to   = isset( $_REQUEST['tp_date_to'] ) // phpcs:ignore WordPress.Security.NonceVerification
			? sanitize_text_field( wp_unslash( $_REQUEST['tp_date_to'] ) )
			: gmdate( 'Y-m-d' );

		// --- Stat queries ---

		// Active projects.
		$active_projects = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}
				 WHERE meta_key = '_tp_status' AND meta_value = %s",
				'active'
			)
		);

		// Leads this period.
		$leads_count = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$leads_table}
				 WHERE created_at >= %s AND created_at <= %s",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		// Assessment completion rate.
		$total_sessions = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$sessions_table}
				 WHERE created_at >= %s AND created_at <= %s",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);
		$completed_sessions = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$sessions_table}
				 WHERE status = 'completed'
				 AND created_at >= %s AND created_at <= %s",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);
		$completion_rate = $total_sessions > 0
			? round( ( $completed_sessions / $total_sessions ) * 100, 1 )
			: 0;

		// Average quality score.
		$avg_quality = (float) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT ROUND(AVG(quality_score), 1) FROM {$leads_table}
				 WHERE created_at >= %s AND created_at <= %s AND quality_score > 0",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		// Lead funnel counts.
		$funnel = array();
		$funnel_statuses = array( 'new', 'contacted', 'qualified', 'site_visit', 'booked', 'lost' );
		foreach ( $funnel_statuses as $status ) {
			$funnel[ $status ] = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$leads_table}
					 WHERE status = %s AND created_at >= %s AND created_at <= %s",
					$status,
					$date_from . ' 00:00:00',
					$date_to . ' 23:59:59'
				)
			);
		}

		// Top performing locations.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$top_locations = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT l.customer_city AS location, COUNT(*) AS lead_count,
						ROUND(AVG(l.quality_score), 1) AS avg_score
				 FROM {$leads_table} l
				 WHERE l.created_at >= %s AND l.created_at <= %s
				 AND l.customer_city IS NOT NULL AND l.customer_city != ''
				 GROUP BY l.customer_city
				 ORDER BY lead_count DESC
				 LIMIT 5",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		// Site visits this period.
		$site_visits_count = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$visits_table}
				 WHERE created_at >= %s AND created_at <= %s",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		// --- Render ---
		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html__( '10Projects Dashboard', 'tenprojects-ai-matcher' ) . '</h1>';
		echo '</div>';

		// Date range selector.
		echo '<form method="get" style="margin-bottom:20px;">';
		echo '<input type="hidden" name="page" value="tenprojects" />';
		echo '<label>' . esc_html__( 'From:', 'tenprojects-ai-matcher' ) . ' ';
		echo '<input type="date" name="tp_date_from" value="' . esc_attr( $date_from ) . '" /></label> ';
		echo '<label>' . esc_html__( 'To:', 'tenprojects-ai-matcher' ) . ' ';
		echo '<input type="date" name="tp_date_to" value="' . esc_attr( $date_to ) . '" /></label> ';
		submit_button( __( 'Apply', 'tenprojects-ai-matcher' ), 'secondary', 'tp_apply_date', false );
		echo '</form>';

		// Stat cards — row 1.
		echo '<div class="tp-dashboard-grid">';
		$this->render_stat_card( __( 'Active Projects', 'tenprojects-ai-matcher' ), $active_projects );
		$this->render_stat_card( __( 'Leads (Period)', 'tenprojects-ai-matcher' ), $leads_count );
		$this->render_stat_card( __( 'Completion Rate', 'tenprojects-ai-matcher' ), $completion_rate . '%' );
		$this->render_stat_card( __( 'Avg Quality Score', 'tenprojects-ai-matcher' ), $avg_quality ?: '—' );
		echo '</div>';

		// Stat cards — row 2.
		echo '<div class="tp-dashboard-grid" style="grid-template-columns:repeat(3,1fr);">';
		$this->render_stat_card( __( 'Assessments Started', 'tenprojects-ai-matcher' ), $total_sessions );
		$this->render_stat_card( __( 'Assessments Completed', 'tenprojects-ai-matcher' ), $completed_sessions );
		$this->render_stat_card( __( 'Site Visits', 'tenprojects-ai-matcher' ), $site_visits_count );
		echo '</div>';

		// Lead funnel.
		echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:8px;">';

		echo '<div class="tp-stat-card">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Lead Funnel', 'tenprojects-ai-matcher' ) . '</div>';

		$funnel_max   = max( 1, max( $funnel ) );
		$funnel_labels = array(
			'new'        => __( 'New', 'tenprojects-ai-matcher' ),
			'contacted'  => __( 'Contacted', 'tenprojects-ai-matcher' ),
			'qualified'  => __( 'Qualified', 'tenprojects-ai-matcher' ),
			'site_visit' => __( 'Site Visit', 'tenprojects-ai-matcher' ),
			'booked'     => __( 'Booked', 'tenprojects-ai-matcher' ),
			'lost'       => __( 'Lost', 'tenprojects-ai-matcher' ),
		);
		$funnel_colors = array(
			'new'        => '#1A56DB',
			'contacted'  => '#F59E0B',
			'qualified'  => '#6366F1',
			'site_visit' => '#8B5CF6',
			'booked'     => '#10B981',
			'lost'       => '#EF4444',
		);

		foreach ( $funnel as $status => $count ) {
			$pct   = round( ( $count / $funnel_max ) * 100 );
			$color = $funnel_colors[ $status ] ?? '#6B7280';
			$label = $funnel_labels[ $status ] ?? ucfirst( $status );

			echo '<div style="margin-bottom:12px;">';
			echo '<div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">';
			echo '<span>' . esc_html( $label ) . '</span>';
			echo '<strong>' . esc_html( $count ) . '</strong>';
			echo '</div>';
			echo '<div style="background:#F3F4F6;border-radius:4px;height:20px;overflow:hidden;">';
			echo '<div style="width:' . esc_attr( $pct ) . '%;height:100%;background:'
				. esc_attr( $color ) . ';border-radius:4px;transition:width 0.3s;"></div>';
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';

		// Top locations.
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Top Performing Locations', 'tenprojects-ai-matcher' ) . '</div>';

		if ( empty( $top_locations ) ) {
			echo '<p style="color:#6B7280;">' . esc_html__( 'No location data for this period.', 'tenprojects-ai-matcher' ) . '</p>';
		} else {
			echo '<table class="widefat" style="border:0;">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__( 'Location', 'tenprojects-ai-matcher' ) . '</th>';
			echo '<th>' . esc_html__( 'Leads', 'tenprojects-ai-matcher' ) . '</th>';
			echo '<th>' . esc_html__( 'Avg Score', 'tenprojects-ai-matcher' ) . '</th>';
			echo '</tr></thead><tbody>';

			$loc_max = max( 1, (int) $top_locations[0]->lead_count );
			foreach ( $top_locations as $loc ) {
				$bar_pct = round( ( (int) $loc->lead_count / $loc_max ) * 100 );
				echo '<tr>';
				echo '<td><strong>' . esc_html( $loc->location ) . '</strong></td>';
				echo '<td>';
				echo '<div style="display:flex;align-items:center;gap:8px;">';
				echo '<div style="flex:1;background:#F3F4F6;border-radius:4px;height:12px;overflow:hidden;">';
				echo '<div style="width:' . esc_attr( $bar_pct ) . '%;height:100%;background:var(--tp-primary);border-radius:4px;"></div>';
				echo '</div>';
				echo '<span>' . esc_html( $loc->lead_count ) . '</span>';
				echo '</div>';
				echo '</td>';
				echo '<td>' . esc_html( $loc->avg_score ?? '—' ) . '</td>';
				echo '</tr>';
			}

			echo '</tbody></table>';
		}

		echo '</div>';
		echo '</div>'; // grid.

		echo '</div>'; // wrap.
	}

	/**
	 * Render the full Analytics page (separate from dashboard).
	 *
	 * Extended analytics with event tracking summaries and conversion data.
	 */
	public function render_analytics(): void {
		global $wpdb;

		$events_table = $wpdb->prefix . 'tp_analytics_events';
		$leads_table  = $wpdb->prefix . 'tp_leads';

		$date_from = isset( $_REQUEST['tp_date_from'] ) // phpcs:ignore WordPress.Security.NonceVerification
			? sanitize_text_field( wp_unslash( $_REQUEST['tp_date_from'] ) )
			: gmdate( 'Y-m-01' );
		$date_to   = isset( $_REQUEST['tp_date_to'] ) // phpcs:ignore WordPress.Security.NonceVerification
			? sanitize_text_field( wp_unslash( $_REQUEST['tp_date_to'] ) )
			: gmdate( 'Y-m-d' );

		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html__( 'Analytics', 'tenprojects-ai-matcher' ) . '</h1>';
		echo '</div>';

		// Date range.
		echo '<form method="get" style="margin-bottom:20px;">';
		echo '<input type="hidden" name="page" value="tenprojects-analytics" />';
		echo '<label>' . esc_html__( 'From:', 'tenprojects-ai-matcher' ) . ' ';
		echo '<input type="date" name="tp_date_from" value="' . esc_attr( $date_from ) . '" /></label> ';
		echo '<label>' . esc_html__( 'To:', 'tenprojects-ai-matcher' ) . ' ';
		echo '<input type="date" name="tp_date_to" value="' . esc_attr( $date_to ) . '" /></label> ';
		submit_button( __( 'Apply', 'tenprojects-ai-matcher' ), 'secondary', 'tp_apply_date', false );
		echo '</form>';

		// Top events.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$top_events = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT event_name, COUNT(*) AS event_count
				 FROM {$events_table}
				 WHERE created_at >= %s AND created_at <= %s
				 GROUP BY event_name
				 ORDER BY event_count DESC
				 LIMIT 15",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		// Unique visitors.
		$unique_visitors = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT visitor_id) FROM {$events_table}
				 WHERE created_at >= %s AND created_at <= %s AND visitor_id IS NOT NULL",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		// Total events.
		$total_events = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$events_table}
				 WHERE created_at >= %s AND created_at <= %s",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		// Device breakdown.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$devices = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COALESCE(device_type, 'unknown') AS device, COUNT(*) AS cnt
				 FROM {$events_table}
				 WHERE created_at >= %s AND created_at <= %s
				 GROUP BY device_type
				 ORDER BY cnt DESC",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		// Lead source breakdown.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$sources = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COALESCE(lead_source, 'direct') AS source, COUNT(*) AS cnt
				 FROM {$leads_table}
				 WHERE created_at >= %s AND created_at <= %s
				 GROUP BY lead_source
				 ORDER BY cnt DESC
				 LIMIT 10",
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);

		// Summary cards.
		echo '<div class="tp-dashboard-grid" style="grid-template-columns:repeat(3,1fr);">';
		$this->render_stat_card( __( 'Total Events', 'tenprojects-ai-matcher' ), number_format( $total_events ) );
		$this->render_stat_card( __( 'Unique Visitors', 'tenprojects-ai-matcher' ), number_format( $unique_visitors ) );
		$this->render_stat_card(
			__( 'Events / Visitor', 'tenprojects-ai-matcher' ),
			$unique_visitors > 0 ? round( $total_events / $unique_visitors, 1 ) : '—'
		);
		echo '</div>';

		echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">';

		// Top events table.
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Top Events', 'tenprojects-ai-matcher' ) . '</div>';

		if ( ! empty( $top_events ) ) {
			$evt_max = max( 1, (int) $top_events[0]->event_count );
			echo '<table class="widefat" style="border:0;">';
			echo '<thead><tr><th>' . esc_html__( 'Event', 'tenprojects-ai-matcher' ) . '</th><th>'
				. esc_html__( 'Count', 'tenprojects-ai-matcher' ) . '</th></tr></thead><tbody>';

			foreach ( $top_events as $evt ) {
				$bar_pct = round( ( (int) $evt->event_count / $evt_max ) * 100 );
				echo '<tr>';
				echo '<td><code>' . esc_html( $evt->event_name ) . '</code></td>';
				echo '<td>';
				echo '<div style="display:flex;align-items:center;gap:8px;">';
				echo '<div style="flex:1;background:#F3F4F6;border-radius:4px;height:10px;overflow:hidden;">';
				echo '<div style="width:' . esc_attr( $bar_pct ) . '%;height:100%;background:var(--tp-primary);border-radius:4px;"></div>';
				echo '</div>';
				echo '<span>' . esc_html( number_format( $evt->event_count ) ) . '</span>';
				echo '</div>';
				echo '</td>';
				echo '</tr>';
			}

			echo '</tbody></table>';
		} else {
			echo '<p style="color:#6B7280;">' . esc_html__( 'No events recorded.', 'tenprojects-ai-matcher' ) . '</p>';
		}

		echo '</div>';

		// Device breakdown + lead sources.
		echo '<div>';

		// Device breakdown.
		echo '<div class="tp-stat-card" style="margin-bottom:20px;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Device Breakdown', 'tenprojects-ai-matcher' ) . '</div>';

		if ( ! empty( $devices ) ) {
			$dev_max = max( 1, (int) $devices[0]->cnt );
			foreach ( $devices as $device ) {
				$bar_pct = round( ( (int) $device->cnt / $dev_max ) * 100 );
				echo '<div style="margin-bottom:8px;">';
				echo '<div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:3px;">';
				echo '<span>' . esc_html( ucfirst( $device->device ) ) . '</span>';
				echo '<strong>' . esc_html( number_format( $device->cnt ) ) . '</strong>';
				echo '</div>';
				echo '<div style="background:#F3F4F6;border-radius:4px;height:14px;overflow:hidden;">';
				echo '<div style="width:' . esc_attr( $bar_pct ) . '%;height:100%;background:#6366F1;border-radius:4px;"></div>';
				echo '</div>';
				echo '</div>';
			}
		} else {
			echo '<p style="color:#6B7280;">' . esc_html__( 'No device data.', 'tenprojects-ai-matcher' ) . '</p>';
		}

		echo '</div>';

		// Lead sources.
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Lead Sources', 'tenprojects-ai-matcher' ) . '</div>';

		if ( ! empty( $sources ) ) {
			$src_max = max( 1, (int) $sources[0]->cnt );
			foreach ( $sources as $src ) {
				$bar_pct = round( ( (int) $src->cnt / $src_max ) * 100 );
				echo '<div style="margin-bottom:8px;">';
				echo '<div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:3px;">';
				echo '<span>' . esc_html( ucfirst( $src->source ) ) . '</span>';
				echo '<strong>' . esc_html( $src->cnt ) . '</strong>';
				echo '</div>';
				echo '<div style="background:#F3F4F6;border-radius:4px;height:14px;overflow:hidden;">';
				echo '<div style="width:' . esc_attr( $bar_pct ) . '%;height:100%;background:#10B981;border-radius:4px;"></div>';
				echo '</div>';
				echo '</div>';
			}
		} else {
			echo '<p style="color:#6B7280;">' . esc_html__( 'No lead source data.', 'tenprojects-ai-matcher' ) . '</p>';
		}

		echo '</div>';
		echo '</div>'; // right column.
		echo '</div>'; // grid.

		echo '</div>'; // wrap.
	}

	/**
	 * Render a stat card.
	 *
	 * @param string     $label Card label.
	 * @param string|int $value Card value.
	 * @param string     $change Optional change indicator.
	 * @param string     $direction 'up' or 'down' for change arrow.
	 */
	private function render_stat_card( string $label, $value, string $change = '', string $direction = '' ): void {
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-stat-card__label">' . esc_html( $label ) . '</div>';
		echo '<div class="tp-stat-card__value">' . esc_html( $value ) . '</div>';

		if ( $change ) {
			$class = $direction ? ' tp-stat-card__change--' . esc_attr( $direction ) : '';
			echo '<div class="tp-stat-card__change' . $class . '">' . esc_html( $change ) . '</div>';
		}

		echo '</div>';
	}
}
