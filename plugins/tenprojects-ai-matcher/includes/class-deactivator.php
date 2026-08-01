<?php
/**
 * Plugin deactivation handler.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects;

defined( 'ABSPATH' ) || exit;

class Deactivator {

    /**
     * Run on plugin deactivation.
     *
     * Note: We do NOT drop tables or remove roles on deactivation.
     * Tables and data are preserved for re-activation.
     * Use uninstall.php for complete cleanup.
     */
    public static function deactivate() {
        // Clear all plugin transients.
        self::clear_transients();

        // Clear scheduled cron events.
        self::clear_cron_events();

        // Flush rewrite rules.
        flush_rewrite_rules();
    }

    /**
     * Clear plugin transients.
     */
    private static function clear_transients() {
        global $wpdb;

        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_tp_%'
             OR option_name LIKE '_transient_timeout_tp_%'"
        );
    }

    /**
     * Clear scheduled cron events.
     */
    private static function clear_cron_events() {
        $events = array(
            'tp_daily_analytics_rollup',
            'tp_hourly_lead_check',
            'tp_daily_score_refresh',
            'tp_weekly_report',
        );

        foreach ( $events as $event ) {
            $timestamp = wp_next_scheduled( $event );
            if ( $timestamp ) {
                wp_unschedule_event( $timestamp, $event );
            }
        }
    }
}
