<?php
/**
 * Enhanced Project admin — custom columns, sortable columns,
 * quick edit, and bulk actions for the tp_project list table.
 *
 * Styled to match NewPropertyz admin layout.
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
	 * Matches NewPropertyz layout: Title, Date, Slug, Phone, Taxonomy, Featured Image, Tags.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function set_columns( array $columns ): array {
		$new = array();

		$new['cb']            = $columns['cb'];
		$new['title']         = $columns['title'];
		$new['tp_slug']       = __( 'Slug', 'tenprojects-ai-matcher' );
		$new['tp_phone']      = __( 'Phone', 'tenprojects-ai-matcher' );
		$new['tp_taxonomy']   = __( 'Location', 'tenprojects-ai-matcher' );
		$new['thumbnail']     = __( 'Featured Image', 'tenprojects-ai-matcher' );
		$new['tp_tags']       = __( 'Project Tags', 'tenprojects-ai-matcher' );
		$new['date']          = $columns['date'];

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
				$thumb = get_the_post_thumbnail( $post_id, array( 60, 60 ) );
				if ( $thumb ) {
					echo '<div style="width:60px;height:60px;border-radius:4px;overflow:hidden;">' . $thumb . '</div>';
				} else {
					echo '<span class="dashicons dashicons-format-image" style="color:#ccc;font-size:40px;width:60px;height:60px;line-height:60px;text-align:center;"></span>';
				}
				break;

			case 'tp_slug':
				$post = get_post( $post_id );
				echo '<code style="font-size:12px;color:#6B7280;">' . esc_html( $post->post_name ) . '</code>';
				break;

			case 'tp_phone':
				$phone = get_post_meta( $post_id, self::META_PREFIX . 'phone', true );
				echo $phone ? esc_html( $phone ) : '<span style="color:#ccc;">—</span>';
				break;

			case 'tp_taxonomy':
				$terms = wp_get_object_terms( $post_id, 'tp_location_area', array( 'fields' => 'names' ) );
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					foreach ( $terms as $term_name ) {
						echo '<span class="tp-badge tp-badge--active" style="margin-right:4px;">' . esc_html( $term_name ) . '</span>';
					}
				} else {
					echo '<span style="color:#ccc;">—</span>';
				}
				break;

			case 'tp_tags':
				$status = get_post_meta( $post_id, self::META_PREFIX . 'status', true );
				$stage  = get_post_meta( $post_id, self::META_PREFIX . 'construction_stage', true );

				if ( $status ) {
					$badge_map = array(
						'active'   => 'active',
						'paused'   => 'paused',
						'sold_out' => 'sold-out',
						'delisted' => 'sold-out',
					);
					$badge = $badge_map[ $status ] ?? 'paused';
					echo '<span class="tp-badge tp-badge--' . esc_attr( $badge ) . '" style="margin-right:4px;">'
						. esc_html( ucfirst( str_replace( '_', ' ', $status ) ) ) . '</span>';
				}

				if ( $stage ) {
					echo '<span class="tp-badge" style="background:#EFF6FF;color:#1E40AF;margin-right:4px;">'
						. esc_html( ucfirst( str_replace( '_', ' ', $stage ) ) ) . '</span>';
				}

				$verified = get_post_meta( $post_id, self::META_PREFIX . 'verified', true );
				if ( $verified ) {
					echo '<span class="tp-badge" style="background:#D1FAE5;color:#065F46;">Verified</span>';
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
		$columns['tp_slug'] = 'name';
		$columns['date']    = 'date';

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
	}

	/**
	 * Add quick-edit fields for project status.
	 *
	 * @param string $column_name Column being rendered.
	 * @param string $post_type   Current post type.
	 */
	public function quick_edit_fields( string $column_name, string $post_type ): void {
		if ( self::POST_TYPE !== $post_type || 'tp_tags' !== $column_name ) {
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
}
