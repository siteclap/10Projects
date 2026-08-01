<?php
/**
 * Database migration manager.
 *
 * Handles versioned schema creation and updates using WordPress dbDelta().
 * Migration files are loaded from the migrations/ subdirectory in numeric order.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

namespace TenProjects\Database;

defined( 'ABSPATH' ) || exit;

class DB_Manager {

	/**
	 * Current database version from migration files.
	 *
	 * @var string
	 */
	private string $target_version;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->target_version = TP_DB_VERSION;
	}

	/**
	 * Run all migration files in order.
	 *
	 * Each migration file returns an SQL string with {prefix} and {charset_collate}
	 * placeholders. This method replaces the placeholders and passes the SQL to
	 * WordPress dbDelta() for safe, idempotent schema creation or update.
	 *
	 * @return array<string, array<string>> Results from dbDelta() keyed by migration file.
	 */
	public function run_migrations(): array {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix;

		$migration_dir = TP_PLUGIN_DIR . 'includes/database/migrations/';
		$files         = glob( $migration_dir . '*.php' );

		if ( empty( $files ) ) {
			return array();
		}

		sort( $files );

		$results = array();

		foreach ( $files as $file ) {
			$sql = include $file;

			if ( ! is_string( $sql ) || empty( trim( $sql ) ) ) {
				continue;
			}

			// Replace placeholders with actual values.
			$sql = str_replace( '{prefix}', $prefix, $sql );
			$sql = str_replace( '{charset_collate}', $charset_collate, $sql );

			$result = dbDelta( $sql );

			$results[ basename( $file ) ] = $result;
		}

		// Log migration run for debugging.
		update_option( 'tp_db_last_migration', current_time( 'mysql' ) );

		return $results;
	}

	/**
	 * Check whether a database update is required.
	 *
	 * @return bool True if the installed version is behind the target version.
	 */
	public function needs_update(): bool {
		$installed = get_option( 'tp_db_version', '0' );
		return version_compare( $installed, $this->target_version, '<' );
	}

	/**
	 * Get installed database version.
	 *
	 * @return string
	 */
	public function get_installed_version(): string {
		return get_option( 'tp_db_version', '0' );
	}

	/**
	 * Get target database version.
	 *
	 * @return string
	 */
	public function get_target_version(): string {
		return $this->target_version;
	}

	/**
	 * Drop all plugin tables.
	 *
	 * USE WITH EXTREME CAUTION — this permanently deletes all plugin data.
	 * Intended for full uninstall only, never for deactivation.
	 *
	 * @return void
	 */
	public function drop_all_tables(): void {
		global $wpdb;

		$tables = array(
			'tp_site_visits',
			'tp_audit_log',
			'tp_notifications',
			'tp_analytics_events',
			'tp_consent_log',
			'tp_lead_routing_rules',
			'tp_lead_assignments',
			'tp_partners',
			'tp_leads',
			'tp_comparisons',
			'tp_saved_projects',
			'tp_recommendations',
			'tp_project_scores',
			'tp_ai_sessions',
			'tp_customer_requirements',
			'tp_customers',
			'tp_project_configurations',
		);

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		foreach ( $tables as $table ) {
			$table_name = $wpdb->prefix . $table;
			$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery

		delete_option( 'tp_db_version' );
		delete_option( 'tp_db_last_migration' );
	}

	/**
	 * Verify that all expected tables exist.
	 *
	 * @return array{existing: string[], missing: string[]}
	 */
	public function verify_tables(): array {
		global $wpdb;

		$expected = array(
			'tp_project_configurations',
			'tp_customers',
			'tp_customer_requirements',
			'tp_ai_sessions',
			'tp_project_scores',
			'tp_recommendations',
			'tp_saved_projects',
			'tp_comparisons',
			'tp_leads',
			'tp_partners',
			'tp_lead_assignments',
			'tp_lead_routing_rules',
			'tp_consent_log',
			'tp_analytics_events',
			'tp_notifications',
			'tp_audit_log',
			'tp_site_visits',
		);

		$existing = array();
		$missing  = array();

		foreach ( $expected as $table ) {
			$table_name = $wpdb->prefix . $table;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$result = $wpdb->get_var(
				$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
			);

			if ( $result === $table_name ) {
				$existing[] = $table;
			} else {
				$missing[] = $table;
			}
		}

		return array(
			'existing' => $existing,
			'missing'  => $missing,
		);
	}
}
