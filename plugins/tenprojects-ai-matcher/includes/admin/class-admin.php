<?php
/**
 * Main admin orchestrator.
 *
 * Registers the top-level "10Projects" admin menu and all submenus,
 * enqueues admin assets on plugin pages only, and bootstraps
 * each admin sub-module.
 *
 * @package TenProjects\Admin
 * @since   1.0.0
 */

namespace TenProjects\Admin;

defined( 'ABSPATH' ) || exit;

class Admin {

	/**
	 * Menu slug prefix.
	 *
	 * @var string
	 */
	const MENU_SLUG = 'tenprojects';

	/**
	 * Capability required for all admin pages.
	 *
	 * @var string
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Hook suffixes for plugin pages — used for asset scoping.
	 *
	 * @var string[]
	 */
	private array $page_hooks = array();

	/**
	 * Register admin menus.
	 *
	 * Called from Plugin::register_admin() on the `admin_menu` action.
	 */
	public function register_menus(): void {
		// Sidebar order: Buy(4) Commercial(5) Resale(6) Rent(7) Plot(8) PG(9)
		// → Landing Page(10) → Location Areas(11) → Brand Settings(12)
		// → Chatbot(13) → Lead(14) → Users(15)

		// Blog (Guides CPT).
		add_menu_page(
			__( 'Blog', 'tenprojects-ai-matcher' ),
			__( 'Blog', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			'edit.php?post_type=tp_guide',
			'',
			'dashicons-welcome-write-blog',
			10
		);

		// Landing Pages.
		add_menu_page(
			__( 'Landing Pages', 'tenprojects-ai-matcher' ),
			__( 'Landing Pages', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			'edit.php?post_type=tp_landing_page',
			'',
			'dashicons-media-text',
			10.1
		);

		// Location Areas.
		add_menu_page(
			__( 'Location Areas', 'tenprojects-ai-matcher' ),
			__( 'Location Areas', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			'edit-tags.php?taxonomy=tp_location_area&post_type=tp_project',
			'',
			'dashicons-location-alt',
			11
		);

		// Brand Settings.
		$this->page_hooks[] = add_menu_page(
			__( 'Brand Settings', 'tenprojects-ai-matcher' ),
			__( 'Brand Settings', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-brand',
			array( $this, 'render_brand_settings' ),
			'dashicons-art',
			12
		);

		// Lead.
		$this->page_hooks[] = add_menu_page(
			__( 'Lead', 'tenprojects-ai-matcher' ),
			__( 'Lead', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-leads',
			array( $this, 'render_leads' ),
			'dashicons-groups',
			14
		);

		// Users — WordPress default, reposition to 15.
		add_menu_page(
			__( 'Users' ),
			__( 'Users' ),
			'list_users',
			'users.php',
			'',
			'dashicons-admin-users',
			15
		);

		// Hide default WordPress menus that are not needed.
		add_action( 'admin_menu', array( $this, 'hide_default_menus' ), 999 );

		// Bootstrap sub-modules that need early hooks (e.g. project columns).
		$this->bootstrap_modules();
	}

	/**
	 * Remove default WordPress sidebar menus that are not needed.
	 */
	public function hide_default_menus(): void {
		remove_menu_page( 'edit.php' );                    // Posts
		remove_menu_page( 'upload.php' );                  // Media
		remove_menu_page( 'edit.php?post_type=page' );     // Pages
		remove_menu_page( 'edit-comments.php' );           // Comments
		remove_menu_page( 'themes.php' );                  // Appearance
		remove_menu_page( 'plugins.php' );                 // Plugins
		remove_menu_page( 'tools.php' );                   // Tools
		remove_menu_page( 'options-general.php' );         // Settings
		remove_menu_page( 'litespeed-cache' );             // LiteSpeed Cache
		remove_menu_page( 'litespeed' );                   // LiteSpeed (alt slug)
		// Remove the default Users menu (we re-add it at position 15).
		remove_menu_page( 'users.php' );
	}

	/**
	 * Bootstrap admin sub-modules that register their own hooks.
	 */
	private function bootstrap_modules(): void {
		$project_admin = new Project_Admin();
		$project_admin->register();
	}

	// ------------------------------------------------------------------
	// Render callbacks — delegate to dedicated admin classes.
	// ------------------------------------------------------------------

	/**
	 * Render the Dashboard page.
	 */
	public function render_dashboard(): void {
		$analytics = new Analytics_Admin();
		$analytics->render_dashboard();
	}

	/**
	 * Render the Chatbot settings page.
	 */
	public function render_chatbot(): void {
		echo '<div class="wrap"><h1>Chatbot</h1><p>Chatbot settings coming soon.</p></div>';
	}

	/**
	 * Render the Leads page.
	 */
	public function render_leads(): void {
		$leads = new Lead_Admin();
		$leads->render_page();
	}

	/**
	 * Render the Partners page.
	 */
	public function render_partners(): void {
		$partners = new Partner_Admin();
		$partners->render_page();
	}

	/**
	 * Render the Site Visits page.
	 */
	public function render_site_visits(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'tp_site_visits';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$visits = $wpdb->get_results(
			"SELECT sv.*,
				c.customer_name, c.customer_phone,
				p.post_title AS project_name
			FROM {$table} sv
			LEFT JOIN {$wpdb->prefix}tp_customers c ON sv.customer_id = c.id
			LEFT JOIN {$wpdb->posts} p ON sv.project_id = p.ID
			ORDER BY sv.created_at DESC
			LIMIT 100"
		);

		$statuses = array(
			'requested' => __( 'Requested', 'tenprojects-ai-matcher' ),
			'confirmed' => __( 'Confirmed', 'tenprojects-ai-matcher' ),
			'completed' => __( 'Completed', 'tenprojects-ai-matcher' ),
			'cancelled' => __( 'Cancelled', 'tenprojects-ai-matcher' ),
			'no_show'   => __( 'No Show', 'tenprojects-ai-matcher' ),
		);

		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html__( 'Site Visits', 'tenprojects-ai-matcher' ) . '</h1>';
		echo '</div>';

		echo '<table class="widefat striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'ID', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Customer', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Phone', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Project', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Requested Date', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Time Slot', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Status', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Rating', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Created', 'tenprojects-ai-matcher' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		if ( empty( $visits ) ) {
			echo '<tr><td colspan="9">' . esc_html__( 'No site visits found.', 'tenprojects-ai-matcher' ) . '</td></tr>';
		}

		foreach ( $visits as $visit ) {
			$status_label = $statuses[ $visit->status ] ?? $visit->status;
			$badge_class  = 'tp-badge tp-badge--' . esc_attr( $visit->status );

			echo '<tr>';
			echo '<td>' . esc_html( $visit->id ) . '</td>';
			echo '<td>' . esc_html( $visit->customer_name ?? '—' ) . '</td>';
			echo '<td>' . esc_html( $visit->customer_phone ?? '—' ) . '</td>';
			echo '<td>' . esc_html( $visit->project_name ?? '—' ) . '</td>';
			echo '<td>' . esc_html( $visit->requested_date ?? '—' ) . '</td>';
			echo '<td>' . esc_html( $visit->requested_time_slot ?? '—' ) . '</td>';
			echo '<td><span class="' . esc_attr( $badge_class ) . '">' . esc_html( $status_label ) . '</span></td>';
			echo '<td>' . ( $visit->feedback_rating ? esc_html( $visit->feedback_rating . '/5' ) : '—' ) . '</td>';
			echo '<td>' . esc_html( $visit->created_at ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Render the Analytics page (full view — separate from dashboard).
	 */
	public function render_analytics(): void {
		$analytics = new Analytics_Admin();
		$analytics->render_analytics();
	}

	/**
	 * Render the Import page.
	 */
	public function render_import(): void {
		$import = new Import_Admin();
		$import->render_page();
	}

	/**
	 * Render the Brand Settings page (dedicated top-level menu).
	 */
	public function render_brand_settings(): void {
		$settings = new Settings_Admin();
		$settings->render_brand_page();
	}

	/**
	 * Render the Settings page.
	 */
	public function render_settings(): void {
		$settings = new Settings_Admin();
		$settings->render_page();
	}

	/**
	 * Enqueue admin CSS and JS on plugin pages only.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		// Check if we are on a plugin page or one of the CPT edit screens.
		$is_plugin_page = in_array( $hook_suffix, $this->page_hooks, true );
		$is_cpt_page    = $this->is_plugin_cpt_screen();

		if ( ! $is_plugin_page && ! $is_cpt_page ) {
			return;
		}

		// Enqueue WP media library on the settings page (needed for brand media uploads).
		if ( $is_plugin_page ) {
			wp_enqueue_media();
		}

		wp_enqueue_style(
			'tp-admin',
			TP_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			TP_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'tp-admin',
			TP_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			TP_PLUGIN_VERSION,
			true
		);

		wp_localize_script( 'tp-admin', 'tpAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'tp_admin_nonce' ),
		) );
	}

	/**
	 * Check if the current screen belongs to a plugin CPT.
	 *
	 * @return bool
	 */
	private function is_plugin_cpt_screen(): bool {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		$plugin_cpts = array(
			'tp_project',
			'tp_developer',
			'tp_location',
			'tp_guide',
			'tp_review',
			'tp_infrastructure',
			'tp_market_report',
			'tp_landing_page',
		);

		return in_array( $screen->post_type, $plugin_cpts, true );
	}
}
