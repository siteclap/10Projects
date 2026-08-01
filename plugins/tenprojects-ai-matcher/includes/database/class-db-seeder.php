<?php
/**
 * Database seeder for development and testing.
 *
 * Inserts sample data into custom tables so the plugin can be explored
 * immediately after activation.  Runs only once (guarded by tp_seeded option).
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

namespace TenProjects\Database;

defined( 'ABSPATH' ) || exit;

class DB_Seeder {

	/**
	 * Run the seeder.
	 *
	 * Inserts sample developers, locations, and project configurations.
	 * Skips silently if data has already been seeded.
	 *
	 * @return bool True if seeded, false if already seeded.
	 */
	public function seed(): bool {
		if ( get_option( 'tp_seeded' ) ) {
			return false;
		}

		global $wpdb;

		$this->seed_developers( $wpdb );
		$this->seed_locations( $wpdb );
		$this->seed_configurations( $wpdb );

		update_option( 'tp_seeded', current_time( 'mysql' ) );

		return true;
	}

	/**
	 * Insert sample developer posts.
	 *
	 * Creates three developers as CPT posts with meta fields.
	 *
	 * @param \wpdb $wpdb WordPress database object.
	 * @return void
	 */
	private function seed_developers( \wpdb $wpdb ): void {
		$developers = array(
			array(
				'title'  => 'Prestige Group',
				'meta'   => array(
					'_tp_developer_rera'           => 'RERA-MH-001',
					'_tp_developer_established'    => 1986,
					'_tp_developer_total_projects' => 250,
					'_tp_developer_city'           => 'Navi Mumbai',
					'_tp_developer_track_record'   => 'excellent',
					'_tp_developer_rating'         => 4.5,
				),
			),
			array(
				'title'  => 'Godrej Properties',
				'meta'   => array(
					'_tp_developer_rera'           => 'RERA-MH-002',
					'_tp_developer_established'    => 1990,
					'_tp_developer_total_projects' => 180,
					'_tp_developer_city'           => 'Navi Mumbai',
					'_tp_developer_track_record'   => 'excellent',
					'_tp_developer_rating'         => 4.6,
				),
			),
			array(
				'title'  => 'Indiabulls Real Estate',
				'meta'   => array(
					'_tp_developer_rera'           => 'RERA-MH-003',
					'_tp_developer_established'    => 2006,
					'_tp_developer_total_projects' => 40,
					'_tp_developer_city'           => 'Navi Mumbai',
					'_tp_developer_track_record'   => 'good',
					'_tp_developer_rating'         => 4.0,
				),
			),
		);

		foreach ( $developers as $developer ) {
			// Skip if a post with this title already exists.
			$exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s AND post_status = %s LIMIT 1",
					$developer['title'],
					'tp_developer',
					'publish'
				)
			);

			if ( $exists ) {
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_title'  => $developer['title'],
				'post_type'   => 'tp_developer',
				'post_status' => 'publish',
			) );

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}

			foreach ( $developer['meta'] as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	/**
	 * Insert sample location posts.
	 *
	 * Creates five Navi Mumbai locations as CPT posts with meta fields.
	 *
	 * @param \wpdb $wpdb WordPress database object.
	 * @return void
	 */
	private function seed_locations( \wpdb $wpdb ): void {
		$locations = array(
			array(
				'title'  => 'Kharghar',
				'meta'   => array(
					'_tp_location_city'              => 'Navi Mumbai',
					'_tp_location_pincode'           => '410210',
					'_tp_location_avg_price_sqft'    => 9500,
					'_tp_location_infrastructure'    => 'Metro connectivity, International School, Central Park',
					'_tp_location_livability_score'  => 85,
				),
			),
			array(
				'title'  => 'Panvel',
				'meta'   => array(
					'_tp_location_city'              => 'Navi Mumbai',
					'_tp_location_pincode'           => '410206',
					'_tp_location_avg_price_sqft'    => 7200,
					'_tp_location_infrastructure'    => 'Navi Mumbai Airport, JNPT, Railway Hub',
					'_tp_location_livability_score'  => 78,
				),
			),
			array(
				'title'  => 'Ulwe',
				'meta'   => array(
					'_tp_location_city'              => 'Navi Mumbai',
					'_tp_location_pincode'           => '410218',
					'_tp_location_avg_price_sqft'    => 8000,
					'_tp_location_infrastructure'    => 'Navi Mumbai Airport proximity, Coastal Road, Upcoming Metro',
					'_tp_location_livability_score'  => 75,
				),
			),
			array(
				'title'  => 'Vashi',
				'meta'   => array(
					'_tp_location_city'              => 'Navi Mumbai',
					'_tp_location_pincode'           => '400703',
					'_tp_location_avg_price_sqft'    => 14000,
					'_tp_location_infrastructure'    => 'Business District, Inorbit Mall, Railway Station',
					'_tp_location_livability_score'  => 90,
				),
			),
			array(
				'title'  => 'Taloja',
				'meta'   => array(
					'_tp_location_city'              => 'Navi Mumbai',
					'_tp_location_pincode'           => '410208',
					'_tp_location_avg_price_sqft'    => 5800,
					'_tp_location_infrastructure'    => 'Industrial Belt, Upcoming Metro, Affordable Corridor',
					'_tp_location_livability_score'  => 68,
				),
			),
		);

		foreach ( $locations as $location ) {
			$exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s AND post_status = %s LIMIT 1",
					$location['title'],
					'tp_location',
					'publish'
				)
			);

			if ( $exists ) {
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_title'  => $location['title'],
				'post_type'   => 'tp_location',
				'post_status' => 'publish',
			) );

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}

			foreach ( $location['meta'] as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	/**
	 * Insert sample project configurations into the custom table.
	 *
	 * Requires at least one tp_project CPT post to exist. If none exist,
	 * this step is skipped silently.
	 *
	 * @param \wpdb $wpdb WordPress database object.
	 * @return void
	 */
	private function seed_configurations( \wpdb $wpdb ): void {
		$table = $wpdb->prefix . 'tp_project_configurations';

		// Only seed if the table exists and is empty.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( null === $count || (int) $count > 0 ) {
			return;
		}

		// Get up to 3 existing project post IDs to attach configs to.
		$project_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s ORDER BY ID ASC LIMIT 3",
				'tp_project',
				'publish'
			)
		);

		// If no projects exist yet, create placeholder configurations with ID 0.
		// They can be reassigned once projects are created.
		if ( empty( $project_ids ) ) {
			$project_ids = array( 0 );
		}

		$configurations = array(
			array(
				'project_id'       => $project_ids[0],
				'configuration'    => '2 BHK',
				'carpet_area_min'  => 650.00,
				'carpet_area_max'  => 720.00,
				'price_min'        => 7500000,
				'price_max'        => 8500000,
				'price_per_sqft'   => 11500,
				'inventory_status' => 'available',
				'unit_count'       => 40,
				'balconies'        => 1,
				'bathrooms'        => 2,
				'parking_included' => 1,
			),
			array(
				'project_id'       => $project_ids[0],
				'configuration'    => '3 BHK',
				'carpet_area_min'  => 950.00,
				'carpet_area_max'  => 1100.00,
				'price_min'        => 11000000,
				'price_max'        => 13000000,
				'price_per_sqft'   => 11800,
				'inventory_status' => 'available',
				'unit_count'       => 24,
				'balconies'        => 2,
				'bathrooms'        => 2,
				'parking_included' => 1,
			),
			array(
				'project_id'       => isset( $project_ids[1] ) ? $project_ids[1] : $project_ids[0],
				'configuration'    => '1 BHK',
				'carpet_area_min'  => 380.00,
				'carpet_area_max'  => 450.00,
				'price_min'        => 3500000,
				'price_max'        => 4200000,
				'price_per_sqft'   => 9200,
				'inventory_status' => 'available',
				'unit_count'       => 60,
				'balconies'        => 1,
				'bathrooms'        => 1,
				'parking_included' => 1,
			),
			array(
				'project_id'       => isset( $project_ids[2] ) ? $project_ids[2] : $project_ids[0],
				'configuration'    => '2 BHK',
				'carpet_area_min'  => 700.00,
				'carpet_area_max'  => 780.00,
				'price_min'        => 5600000,
				'price_max'        => 6300000,
				'price_per_sqft'   => 8100,
				'inventory_status' => 'limited',
				'unit_count'       => 12,
				'balconies'        => 1,
				'bathrooms'        => 2,
				'parking_included' => 1,
			),
		);

		foreach ( $configurations as $config ) {
			$wpdb->insert( $table, $config ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}
	}

	/**
	 * Remove all seeded data.
	 *
	 * Deletes the seeder flag so seed() can run again.
	 * Does NOT delete actual data — only resets the guard option.
	 *
	 * @return void
	 */
	public function reset(): void {
		delete_option( 'tp_seeded' );
	}
}
