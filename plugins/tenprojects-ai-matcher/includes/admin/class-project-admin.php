<?php
/**
 * Enhanced Project admin — custom columns, sortable columns,
 * quick edit, and bulk actions for the tp_project list table.
 *
 * Supplements the CPT meta boxes defined in Project_CPT.
 *
 * @package TenProjects\Admin
 * @since   1.0.0
 */

namespace TenProjects\Admin;

defined( 'ABSPATH' ) || exit;

class Project_Admin {

	/**
	 * Post type key.
	 *
	 * @var string
	 */
	const POST_TYPE = 'tp_project';

	/**
	 * Meta key prefix.
	 *
	 * @var string
	 */
	const META_PREFIX = '_tp_';

	/**
	 * Register all hooks.
	 */
	public function register(): void {
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'set_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'set_sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_orderby' ) );
		add_filter( 'bulk_actions-edit-' . self::POST_TYPE, array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-' . self::POST_TYPE, array( $this, 'handle_bulk_actions' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'bulk_action_notices' ) );
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_fields' ), 10, 2 );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_quick_edit' ), 10, 2 );
	}

	/**
	 * Define custom columns for the project list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function set_columns( array $columns ): array {
		$new = array();

		$new['cb']                  = $columns['cb'];
		$new['thumbnail']           = __( 'Image', 'tenprojects-ai-matcher' );
		$new['title']               = $columns['title'];
		$new['tp_developer']        = __( 'Developer', 'tenprojects-ai-matcher' );
		$new['tp_location']         = __( 'Location', 'tenprojects-ai-matcher' );
		$new['tp_price_range']      = __( 'Price Range', 'tenprojects-ai-matcher' );
		$new['tp_status']           = __( 'Status', 'tenprojects-ai-matcher' );
		$new['tp_fit_score']        = __( 'Fit Score', 'tenprojects-ai-matcher' );
		$new['tp_construction']     = __( 'Construction', 'tenprojects-ai-matcher' );
		$new['date']                = $columns['date'];

		return $new;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column slug.
	 * @param int    $post_id Post ID.
	 */
	public function render_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'thumbnail':
				$thumb = get_the_post_thumbnail( $post_id, array( 50, 50 ) );
				echo $thumb ?: '<span class="dashicons dashicons-format-image" style="color:#ccc;font-size:32px;"></span>';
				break;

			case 'tp_developer':
				$dev_id = get_post_meta( $post_id, self::META_PREFIX . 'developer_id', true );
				if ( $dev_id ) {
					$dev_title = get_the_title( (int) $dev_id );
					echo $dev_title ? esc_html( $dev_title ) : '—';
				} else {
					echo '—';
				}
				break;

			case 'tp_location':
				$terms = wp_get_object_terms( $post_id, 'tp_location_area', array( 'fields' => 'names' ) );
				echo ! is_wp_error( $terms ) && ! empty( $terms )
					? esc_html( implode( ', ', $terms ) )
					: '—';
				break;

			case 'tp_price_range':
				$min = get_post_meta( $post_id, self::META_PREFIX . 'price_display_min', true );
				$max = get_post_meta( $post_id, self::META_PREFIX . 'price_display_max', true );
				if ( $min || $max ) {
					echo esc_html( $this->format_price( $min ) . ' – ' . $this->format_price( $max ) );
				} else {
					echo '—';
				}
				break;

			case 'tp_status':
				$status = get_post_meta( $post_id, self::META_PREFIX . 'status', true );
				if ( $status ) {
					$badge_map = array(
						'active'   => 'active',
						'paused'   => 'paused',
						'sold_out' => 'sold-out',
						'delisted' => 'sold-out',
					);
					$badge = $badge_map[ $status ] ?? 'paused';
					echo '<span class="tp-badge tp-badge--' . esc_attr( $badge ) . '">'
						. esc_html( ucfirst( str_replace( '_', ' ', $status ) ) ) . '</span>';
				} else {
					echo '—';
				}
				break;

			case 'tp_fit_score':
				global $wpdb;
				$table = $wpdb->prefix . 'tp_project_scores';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$avg = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT ROUND(AVG(fit_score)) FROM {$table} WHERE project_id = %d",
						$post_id
					)
				);
				echo $avg ? esc_html( $avg . '/100' ) : '—';
				break;

			case 'tp_construction':
				$stage    = get_post_meta( $post_id, self::META_PREFIX . 'construction_stage', true );
				$progress = get_post_meta( $post_id, self::META_PREFIX . 'construction_progress', true );
				if ( $stage ) {
					echo esc_html( ucfirst( str_replace( '_', ' ', $stage ) ) );
					if ( $progress ) {
						echo '<br><small style="color:#6B7280;">' . esc_html( $progress . '%' ) . '</small>';
					}
				} else {
					echo '—';
				}
				break;
		}
	}

	/**
	 * Mark columns as sortable.
	 *
	 * @param array $columns Existing sortable columns.
	 * @return array Modified sortable columns.
	 */
	public function set_sortable_columns( array $columns ): array {
		$columns['tp_price_range']  = 'tp_price_min';
		$columns['tp_status']       = 'tp_status';
		$columns['tp_construction'] = 'tp_construction_progress';

		return $columns;
	}

	/**
	 * Handle custom orderby for sortable columns.
	 *
	 * @param \WP_Query $query Current query.
	 */
	public function handle_orderby( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		switch ( $orderby ) {
			case 'tp_price_min':
				$query->set( 'meta_key', self::META_PREFIX . 'price_display_min' );
				$query->set( 'orderby', 'meta_value_num' );
				break;

			case 'tp_status':
				$query->set( 'meta_key', self::META_PREFIX . 'status' );
				$query->set( 'orderby', 'meta_value' );
				break;

			case 'tp_construction_progress':
				$query->set( 'meta_key', self::META_PREFIX . 'construction_progress' );
				$query->set( 'orderby', 'meta_value_num' );
				break;
		}
	}

	/**
	 * Register bulk actions.
	 *
	 * @param array $actions Existing bulk actions.
	 * @return array Modified actions.
	 */
	public function register_bulk_actions( array $actions ): array {
		$actions['tp_change_active']   = __( 'Change Status: Active', 'tenprojects-ai-matcher' );
		$actions['tp_change_paused']   = __( 'Change Status: Paused', 'tenprojects-ai-matcher' );
		$actions['tp_change_sold_out'] = __( 'Change Status: Sold Out', 'tenprojects-ai-matcher' );
		$actions['tp_refresh_scores']  = __( 'Refresh Scores', 'tenprojects-ai-matcher' );

		return $actions;
	}

	/**
	 * Handle bulk actions.
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $doaction    Action name.
	 * @param int[]  $post_ids    Selected post IDs.
	 * @return string Redirect URL.
	 */
	public function handle_bulk_actions( string $redirect_to, string $doaction, array $post_ids ): string {
		$status_map = array(
			'tp_change_active'   => 'active',
			'tp_change_paused'   => 'paused',
			'tp_change_sold_out' => 'sold_out',
		);

		if ( isset( $status_map[ $doaction ] ) ) {
			$count = 0;
			foreach ( $post_ids as $post_id ) {
				update_post_meta( $post_id, self::META_PREFIX . 'status', $status_map[ $doaction ] );
				$count++;
			}
			$redirect_to = add_query_arg( 'tp_bulk_status_updated', $count, $redirect_to );
		}

		if ( 'tp_refresh_scores' === $doaction ) {
			// Trigger a score recalculation for selected projects.
			// The actual scoring runs asynchronously via the Scoring_Engine service.
			$count = count( $post_ids );
			foreach ( $post_ids as $post_id ) {
				do_action( 'tp_refresh_project_scores', $post_id );
			}
			$redirect_to = add_query_arg( 'tp_bulk_scores_refreshed', $count, $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Display admin notices for bulk action results.
	 */
	public function bulk_action_notices(): void {
		if ( ! empty( $_REQUEST['tp_bulk_status_updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$count = intval( $_REQUEST['tp_bulk_status_updated'] ); // phpcs:ignore WordPress.Security.NonceVerification
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: %d: number of projects updated */
						_n( '%d project status updated.', '%d project statuses updated.', $count, 'tenprojects-ai-matcher' ),
						$count
					)
				)
			);
		}

		if ( ! empty( $_REQUEST['tp_bulk_scores_refreshed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$count = intval( $_REQUEST['tp_bulk_scores_refreshed'] ); // phpcs:ignore WordPress.Security.NonceVerification
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: %d: number of projects queued */
						_n( '%d project queued for score refresh.', '%d projects queued for score refresh.', $count, 'tenprojects-ai-matcher' ),
						$count
					)
				)
			);
		}
	}

	/**
	 * Add quick-edit fields for project status.
	 *
	 * @param string $column_name Column being rendered.
	 * @param string $post_type   Current post type.
	 */
	public function quick_edit_fields( string $column_name, string $post_type ): void {
		if ( self::POST_TYPE !== $post_type || 'tp_status' !== $column_name ) {
			return;
		}

		wp_nonce_field( 'tp_quick_edit', 'tp_quick_edit_nonce' );

		$statuses = array(
			'active'   => __( 'Active', 'tenprojects-ai-matcher' ),
			'paused'   => __( 'Paused', 'tenprojects-ai-matcher' ),
			'sold_out' => __( 'Sold Out', 'tenprojects-ai-matcher' ),
			'delisted' => __( 'Delisted', 'tenprojects-ai-matcher' ),
		);

		echo '<fieldset class="inline-edit-col-right">';
		echo '<div class="inline-edit-col">';
		echo '<label>';
		echo '<span class="title">' . esc_html__( 'Project Status', 'tenprojects-ai-matcher' ) . '</span>';
		echo '<select name="' . esc_attr( self::META_PREFIX . 'status' ) . '">';
		echo '<option value="">' . esc_html__( '— No Change —', 'tenprojects-ai-matcher' ) . '</option>';

		foreach ( $statuses as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
		}

		echo '</select>';
		echo '</label>';
		echo '</div>';
		echo '</fieldset>';
	}

	/**
	 * Save quick-edit data.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_quick_edit( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['tp_quick_edit_nonce'] )
			|| ! wp_verify_nonce( $_POST['tp_quick_edit_nonce'], 'tp_quick_edit' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$status_key = self::META_PREFIX . 'status';

		if ( isset( $_POST[ $status_key ] ) && '' !== $_POST[ $status_key ] ) {
			$allowed = array( 'active', 'paused', 'sold_out', 'delisted' );
			$value   = sanitize_text_field( wp_unslash( $_POST[ $status_key ] ) );

			if ( in_array( $value, $allowed, true ) ) {
				update_post_meta( $post_id, $status_key, $value );
			}
		}
	}

	/**
	 * Format price in Indian numbering (lakhs / crores).
	 *
	 * @param int|string $price Price in rupees.
	 * @return string Formatted price string.
	 */
	private function format_price( $price ): string {
		$price = (int) $price;

		if ( $price <= 0 ) {
			return '—';
		}

		if ( $price >= 10000000 ) {
			return number_format( $price / 10000000, 2 ) . ' Cr';
		}

		if ( $price >= 100000 ) {
			return number_format( $price / 100000, 2 ) . ' L';
		}

		return number_format( $price );
	}
}
