<?php
/**
 * CSV Import admin page.
 *
 * Provides a file upload form for bulk-importing projects via CSV,
 * column mapping to project meta fields, taxonomy term creation,
 * configuration row generation, and a detailed import report.
 *
 * @package TenProjects\Admin
 * @since   1.0.0
 */

namespace TenProjects\Admin;

defined( 'ABSPATH' ) || exit;

class Import_Admin {

	/**
	 * Meta prefix for project fields.
	 *
	 * @var string
	 */
	const META_PREFIX = '_tp_';

	/**
	 * Import log option key.
	 *
	 * @var string
	 */
	const LOG_OPTION = 'tp_import_log';

	/**
	 * Render the import page.
	 */
	public function render_page(): void {
		// Handle import if a file was submitted.
		if ( isset( $_POST['tp_import_nonce'] ) && wp_verify_nonce( $_POST['tp_import_nonce'], 'tp_import_csv' ) ) {
			$this->handle_import();
			return;
		}

		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html__( 'Import Projects', 'tenprojects-ai-matcher' ) . '</h1>';
		echo '</div>';

		// Upload form.
		echo '<div class="tp-stat-card" style="max-width:600px;margin-bottom:24px;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Upload CSV File', 'tenprojects-ai-matcher' ) . '</div>';

		echo '<form method="post" enctype="multipart/form-data">';
		wp_nonce_field( 'tp_import_csv', 'tp_import_nonce' );

		echo '<div class="tp-field" style="margin-bottom:16px;">';
		echo '<label for="tp_csv_file">' . esc_html__( 'CSV File', 'tenprojects-ai-matcher' ) . '</label>';
		echo '<input type="file" name="tp_csv_file" id="tp_csv_file" accept=".csv" required />';
		echo '<span class="description">' . esc_html__( 'Maximum file size: 10 MB. CSV must include a header row.', 'tenprojects-ai-matcher' ) . '</span>';
		echo '</div>';

		echo '<div class="tp-field" style="margin-bottom:16px;">';
		echo '<label>';
		echo '<input type="checkbox" name="tp_update_existing" value="1" /> ';
		echo esc_html__( 'Update existing projects (match by RERA Number)', 'tenprojects-ai-matcher' );
		echo '</label>';
		echo '</div>';

		submit_button( __( 'Import', 'tenprojects-ai-matcher' ), 'primary', 'tp_submit_import' );

		echo '</form>';
		echo '</div>';

		// Sample CSV download.
		echo '<div class="tp-stat-card" style="max-width:600px;margin-bottom:24px;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Sample CSV Template', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<p>' . esc_html__( 'Download a sample CSV template with all supported columns.', 'tenprojects-ai-matcher' ) . '</p>';
		echo '<form method="post">';
		wp_nonce_field( 'tp_download_template', 'tp_template_nonce' );
		echo '<input type="hidden" name="tp_download_template" value="1" />';
		submit_button( __( 'Download Template', 'tenprojects-ai-matcher' ), 'secondary', 'tp_download_btn', false );
		echo '</form>';
		echo '</div>';

		// Handle template download.
		if ( isset( $_POST['tp_download_template'] ) && isset( $_POST['tp_template_nonce'] )
			&& wp_verify_nonce( $_POST['tp_template_nonce'], 'tp_download_template' ) ) {
			$this->download_template();
		}

		// Import history log.
		$history = get_option( self::LOG_OPTION, array() );

		if ( ! empty( $history ) ) {
			echo '<div class="tp-stat-card" style="max-width:800px;">';
			echo '<div class="tp-field-group__title">' . esc_html__( 'Import History', 'tenprojects-ai-matcher' ) . '</div>';
			echo '<table class="widefat striped">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__( 'Date', 'tenprojects-ai-matcher' ) . '</th>';
			echo '<th>' . esc_html__( 'File', 'tenprojects-ai-matcher' ) . '</th>';
			echo '<th>' . esc_html__( 'Imported', 'tenprojects-ai-matcher' ) . '</th>';
			echo '<th>' . esc_html__( 'Updated', 'tenprojects-ai-matcher' ) . '</th>';
			echo '<th>' . esc_html__( 'Skipped', 'tenprojects-ai-matcher' ) . '</th>';
			echo '<th>' . esc_html__( 'Errors', 'tenprojects-ai-matcher' ) . '</th>';
			echo '<th>' . esc_html__( 'User', 'tenprojects-ai-matcher' ) . '</th>';
			echo '</tr></thead>';
			echo '<tbody>';

			// Show latest first.
			$history = array_reverse( $history );
			foreach ( array_slice( $history, 0, 20 ) as $entry ) {
				echo '<tr>';
				echo '<td>' . esc_html( $entry['date'] ?? '—' ) . '</td>';
				echo '<td>' . esc_html( $entry['filename'] ?? '—' ) . '</td>';
				echo '<td><strong style="color:#10B981;">' . esc_html( $entry['imported'] ?? 0 ) . '</strong></td>';
				echo '<td><strong style="color:#F59E0B;">' . esc_html( $entry['updated'] ?? 0 ) . '</strong></td>';
				echo '<td>' . esc_html( $entry['skipped'] ?? 0 ) . '</td>';
				echo '<td><strong style="color:#EF4444;">' . esc_html( $entry['errors'] ?? 0 ) . '</strong></td>';
				echo '<td>' . esc_html( $entry['user'] ?? '—' ) . '</td>';
				echo '</tr>';
			}

			echo '</tbody></table>';
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Handle the CSV import.
	 */
	private function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'tenprojects-ai-matcher' ) );
		}

		if ( empty( $_FILES['tp_csv_file']['tmp_name'] ) ) {
			echo '<div class="wrap"><div class="notice notice-error"><p>'
				. esc_html__( 'No file uploaded.', 'tenprojects-ai-matcher' ) . '</p></div></div>';
			return;
		}

		$file = $_FILES['tp_csv_file'];

		if ( $file['error'] !== UPLOAD_ERR_OK ) {
			echo '<div class="wrap"><div class="notice notice-error"><p>'
				. esc_html__( 'File upload error.', 'tenprojects-ai-matcher' ) . '</p></div></div>';
			return;
		}

		$update_existing = ! empty( $_POST['tp_update_existing'] );

		$handle = fopen( $file['tmp_name'], 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		if ( ! $handle ) {
			echo '<div class="wrap"><div class="notice notice-error"><p>'
				. esc_html__( 'Could not read the file.', 'tenprojects-ai-matcher' ) . '</p></div></div>';
			return;
		}

		// Read header row.
		$headers = fgetcsv( $handle );

		if ( empty( $headers ) ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			echo '<div class="wrap"><div class="notice notice-error"><p>'
				. esc_html__( 'CSV file appears to be empty.', 'tenprojects-ai-matcher' ) . '</p></div></div>';
			return;
		}

		// Normalize headers.
		$headers = array_map( function ( $h ) {
			return strtolower( trim( $h ) );
		}, $headers );

		// Column mapping.
		$field_map = $this->get_field_map();

		// Track results.
		$imported     = 0;
		$updated      = 0;
		$skipped      = 0;
		$errors       = 0;
		$error_msgs   = array();
		$row_number   = 1;

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$row_number++;

			if ( count( $row ) !== count( $headers ) ) {
				$errors++;
				$error_msgs[] = sprintf( __( 'Row %d: Column count mismatch.', 'tenprojects-ai-matcher' ), $row_number );
				continue;
			}

			$data = array_combine( $headers, $row );

			// Title is required.
			$title = $data['title'] ?? $data['project_name'] ?? '';
			if ( empty( trim( $title ) ) ) {
				$skipped++;
				continue;
			}

			// Check for existing project by RERA number.
			$rera      = $data['rera_number'] ?? '';
			$existing  = null;

			if ( $rera && $update_existing ) {
				$existing = $this->find_project_by_rera( $rera );
			}

			if ( $existing ) {
				// Update existing post.
				wp_update_post( array(
					'ID'           => $existing,
					'post_title'   => sanitize_text_field( $title ),
					'post_content' => sanitize_textarea_field( $data['description'] ?? '' ),
					'post_excerpt' => sanitize_text_field( $data['excerpt'] ?? '' ),
				) );
				$post_id = $existing;
				$updated++;
			} else {
				// Create new post.
				$post_id = wp_insert_post( array(
					'post_type'    => 'tp_project',
					'post_title'   => sanitize_text_field( $title ),
					'post_content' => sanitize_textarea_field( $data['description'] ?? '' ),
					'post_excerpt' => sanitize_text_field( $data['excerpt'] ?? '' ),
					'post_status'  => 'draft',
				) );

				if ( is_wp_error( $post_id ) ) {
					$errors++;
					$error_msgs[] = sprintf( __( 'Row %d: %s', 'tenprojects-ai-matcher' ), $row_number, $post_id->get_error_message() );
					continue;
				}
				$imported++;
			}

			// Set meta fields.
			foreach ( $field_map as $csv_col => $meta_key ) {
				if ( isset( $data[ $csv_col ] ) && '' !== $data[ $csv_col ] ) {
					update_post_meta( $post_id, self::META_PREFIX . $meta_key, sanitize_text_field( $data[ $csv_col ] ) );
				}
			}

			// Handle taxonomy terms.
			$this->set_taxonomy_terms( $post_id, $data, 'location', 'tp_location_area' );
			$this->set_taxonomy_terms( $post_id, $data, 'city', 'tp_city' );
			$this->set_taxonomy_terms( $post_id, $data, 'configuration', 'tp_configuration' );
			$this->set_taxonomy_terms( $post_id, $data, 'property_type', 'tp_property_type' );
			$this->set_taxonomy_terms( $post_id, $data, 'amenities', 'tp_amenity' );

			// Handle configurations (create rows in custom table).
			$this->import_configurations( $post_id, $data );
		}

		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		// Log the import.
		$log = get_option( self::LOG_OPTION, array() );
		$log[] = array(
			'date'     => current_time( 'mysql' ),
			'filename' => sanitize_file_name( $file['name'] ),
			'imported' => $imported,
			'updated'  => $updated,
			'skipped'  => $skipped,
			'errors'   => $errors,
			'user'     => wp_get_current_user()->display_name,
		);

		// Keep last 50 entries.
		if ( count( $log ) > 50 ) {
			$log = array_slice( $log, -50 );
		}
		update_option( self::LOG_OPTION, $log );

		// Render results.
		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html__( 'Import Results', 'tenprojects-ai-matcher' ) . '</h1>';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=tenprojects-import' ) ) . '" class="page-title-action">'
			. esc_html__( 'Import Another File', 'tenprojects-ai-matcher' ) . '</a>';
		echo '</div>';

		echo '<div class="tp-dashboard-grid">';
		$this->render_stat_card( __( 'Imported', 'tenprojects-ai-matcher' ), $imported, '#10B981' );
		$this->render_stat_card( __( 'Updated', 'tenprojects-ai-matcher' ), $updated, '#F59E0B' );
		$this->render_stat_card( __( 'Skipped', 'tenprojects-ai-matcher' ), $skipped, '#6B7280' );
		$this->render_stat_card( __( 'Errors', 'tenprojects-ai-matcher' ), $errors, '#EF4444' );
		echo '</div>';

		if ( ! empty( $error_msgs ) ) {
			echo '<div class="notice notice-warning" style="margin-top:16px;"><ul>';
			foreach ( $error_msgs as $msg ) {
				echo '<li>' . esc_html( $msg ) . '</li>';
			}
			echo '</ul></div>';
		}

		echo '</div>';
	}

	/**
	 * Get column-to-meta-field mapping.
	 *
	 * @return array CSV column => meta key (without prefix).
	 */
	private function get_field_map(): array {
		return array(
			'rera_number'           => 'rera_number',
			'rera_phase'            => 'rera_phase',
			'developer_id'         => 'developer_id',
			'status'                => 'status',
			'sponsored'             => 'sponsored',
			'sponsor_label'         => 'sponsor_label',
			'launch_date'           => 'launch_date',
			'verified'              => 'verified',
			'price_display_min'     => 'price_display_min',
			'price_display_max'     => 'price_display_max',
			'primary_config'        => 'primary_config',
			'rera_registration_date' => 'rera_registration_date',
			'construction_start'    => 'construction_start',
			'construction_stage'    => 'construction_stage',
			'construction_progress' => 'construction_progress',
			'promised_possession'   => 'promised_possession',
			'rera_possession'       => 'rera_possession',
			'expected_possession'   => 'expected_possession',
			'total_towers'          => 'total_towers',
			'total_floors'          => 'total_floors',
			'total_units'           => 'total_units',
			'latitude'              => 'latitude',
			'longitude'             => 'longitude',
			'address'               => 'address',
			'railway_distance_km'   => 'railway_distance_km',
			'metro_distance_km'     => 'metro_distance_km',
			'highway_distance_km'   => 'highway_distance_km',
			'airport_distance_km'   => 'airport_distance_km',
			'school_distance_km'    => 'school_distance_km',
			'hospital_distance_km'  => 'hospital_distance_km',
			'mall_distance_km'      => 'mall_distance_km',
			'employment_hub_km'     => 'employment_hub_km',
			'legal_confidence'      => 'legal_confidence',
			'possession_confidence' => 'possession_confidence',
			'bank_approved'         => 'bank_approved',
			'litigation_status'     => 'litigation_status',
			'micro_market_price'    => 'micro_market_price',
			'rental_range_min'      => 'rental_range_min',
			'rental_range_max'      => 'rental_range_max',
			'vacancy_risk'          => 'vacancy_risk',
			'appreciation_score'    => 'appreciation_score',
			'rental_yield_pct'      => 'rental_yield_pct',
			'open_space_ratio'      => 'open_space_ratio',
			'density_rating'        => 'density_rating',
			'maintenance_estimate'  => 'maintenance_estimate',
			'parking_info'          => 'parking_info',
			'water_source'          => 'water_source',
			'power_backup'          => 'power_backup',
		);
	}

	/**
	 * Find an existing project by RERA number.
	 *
	 * @param string $rera RERA number to search for.
	 * @return int|null Post ID or null.
	 */
	private function find_project_by_rera( string $rera ): ?int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta}
				 WHERE meta_key = %s AND meta_value = %s
				 LIMIT 1",
				self::META_PREFIX . 'rera_number',
				$rera
			)
		);

		return $post_id ? (int) $post_id : null;
	}

	/**
	 * Set taxonomy terms from CSV data.
	 *
	 * @param int    $post_id  Post ID.
	 * @param array  $data     Row data.
	 * @param string $csv_col  CSV column name.
	 * @param string $taxonomy Taxonomy name.
	 */
	private function set_taxonomy_terms( int $post_id, array $data, string $csv_col, string $taxonomy ): void {
		if ( empty( $data[ $csv_col ] ) ) {
			return;
		}

		$terms = array_map( 'trim', explode( ',', $data[ $csv_col ] ) );

		$term_ids = array();
		foreach ( $terms as $term_name ) {
			if ( empty( $term_name ) ) {
				continue;
			}

			$existing = term_exists( $term_name, $taxonomy );

			if ( $existing ) {
				$term_ids[] = (int) $existing['term_id'];
			} else {
				$new_term = wp_insert_term( $term_name, $taxonomy );
				if ( ! is_wp_error( $new_term ) ) {
					$term_ids[] = (int) $new_term['term_id'];
				}
			}
		}

		if ( ! empty( $term_ids ) ) {
			wp_set_object_terms( $post_id, $term_ids, $taxonomy );
		}
	}

	/**
	 * Import project configurations from CSV row.
	 *
	 * Expects CSV columns: config_type, config_carpet_min, config_carpet_max,
	 * config_price_min, config_price_max (pipe-separated for multiple configs).
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Row data.
	 */
	private function import_configurations( int $post_id, array $data ): void {
		if ( empty( $data['config_type'] ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'tp_project_configurations';

		// Parse pipe-separated configs.
		$types      = explode( '|', $data['config_type'] );
		$carpet_min = isset( $data['config_carpet_min'] ) ? explode( '|', $data['config_carpet_min'] ) : array();
		$carpet_max = isset( $data['config_carpet_max'] ) ? explode( '|', $data['config_carpet_max'] ) : array();
		$price_min  = isset( $data['config_price_min'] ) ? explode( '|', $data['config_price_min'] ) : array();
		$price_max  = isset( $data['config_price_max'] ) ? explode( '|', $data['config_price_max'] ) : array();

		foreach ( $types as $i => $config_type ) {
			$config_type = trim( $config_type );

			if ( empty( $config_type ) ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->replace(
				$table,
				array(
					'project_id'     => $post_id,
					'config_type'    => sanitize_text_field( $config_type ),
					'carpet_area_min' => isset( $carpet_min[ $i ] ) ? (float) $carpet_min[ $i ] : null,
					'carpet_area_max' => isset( $carpet_max[ $i ] ) ? (float) $carpet_max[ $i ] : null,
					'price_min'      => isset( $price_min[ $i ] ) ? absint( $price_min[ $i ] ) : null,
					'price_max'      => isset( $price_max[ $i ] ) ? absint( $price_max[ $i ] ) : null,
				)
			);
		}
	}

	/**
	 * Render a stat card with optional color accent.
	 *
	 * @param string     $label Card label.
	 * @param string|int $value Card value.
	 * @param string     $color Accent color for the value.
	 */
	private function render_stat_card( string $label, $value, string $color = '' ): void {
		$style = $color ? ' style="color:' . esc_attr( $color ) . ';"' : '';

		echo '<div class="tp-stat-card">';
		echo '<div class="tp-stat-card__label">' . esc_html( $label ) . '</div>';
		echo '<div class="tp-stat-card__value"' . $style . '>' . esc_html( $value ) . '</div>';
		echo '</div>';
	}

	/**
	 * Download a sample CSV template.
	 */
	private function download_template(): void {
		$filename = 'tenprojects-import-template.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		// Header row — all supported columns.
		$headers = array_merge(
			array( 'title', 'description', 'excerpt' ),
			array_keys( $this->get_field_map() ),
			array( 'location', 'city', 'configuration', 'property_type', 'amenities' ),
			array( 'config_type', 'config_carpet_min', 'config_carpet_max', 'config_price_min', 'config_price_max' )
		);

		fputcsv( $output, $headers );

		// Sample row.
		$sample = array(
			'Sample Project Name',             // title
			'A beautiful project in Panvel.',   // description
			'2 & 3 BHK in Panvel',              // excerpt
			'P52100012345',                      // rera_number
			'Phase 1',                           // rera_phase
			'',                                  // developer_id
			'active',                            // status
			'0',                                 // sponsored
			'',                                  // sponsor_label
			'2025-03-15',                        // launch_date
			'1',                                 // verified
			'5500000',                           // price_display_min
			'9500000',                           // price_display_max
			'2 BHK',                             // primary_config
			'2024-01-10',                        // rera_registration_date
			'2024-06-01',                        // construction_start
			'superstructure',                    // construction_stage
			'45',                                // construction_progress
			'2026-12-31',                        // promised_possession
			'2027-06-30',                        // rera_possession
			'2027-03-31',                        // expected_possession
			'4',                                 // total_towers
			'25',                                // total_floors
			'400',                               // total_units
			'18.9894',                           // latitude
			'73.1175',                           // longitude
			'Sector 12, New Panvel',             // address
			'2.5',                               // railway_distance_km
			'1.8',                               // metro_distance_km
			'3.0',                               // highway_distance_km
			'25.0',                              // airport_distance_km
			'1.2',                               // school_distance_km
			'3.5',                               // hospital_distance_km
			'4.0',                               // mall_distance_km
			'5.0',                               // employment_hub_km
			'85',                                // legal_confidence
			'75',                                // possession_confidence
			'HDFC,SBI,ICICI',                    // bank_approved
			'none',                              // litigation_status
			'7500',                              // micro_market_price
			'18000',                             // rental_range_min
			'25000',                             // rental_range_max
			'low',                               // vacancy_risk
			'72',                                // appreciation_score
			'3.5',                               // rental_yield_pct
			'65',                                // open_space_ratio
			'medium',                            // density_rating
			'4500',                              // maintenance_estimate
			'Covered parking',                   // parking_info
			'Corporation + Borewell',            // water_source
			'Full DG backup',                    // power_backup
			'Panvel',                            // location
			'Navi Mumbai',                       // city
			'2BHK,3BHK',                         // configuration
			'Apartment',                         // property_type
			'Swimming Pool,Gym,Club House',      // amenities
			'2BHK|3BHK',                         // config_type
			'650|950',                           // config_carpet_min
			'750|1100',                          // config_carpet_max
			'5500000|7500000',                   // config_price_min
			'6500000|9500000',                   // config_price_max
		);

		fputcsv( $output, $sample );

		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}
}
