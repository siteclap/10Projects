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
		// Top-level menu — points to the Dashboard (Analytics) page.
		$this->page_hooks[] = add_menu_page(
			__( '10Projects', 'tenprojects-ai-matcher' ),
			__( '10Projects', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this, 'render_dashboard' ),
			'dashicons-building',
			3
		);

		// 1. Dashboard (default submenu — replaces auto-generated duplicate).
		$this->page_hooks[] = add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'tenprojects-ai-matcher' ),
			__( 'Dashboard', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this, 'render_dashboard' )
		);

		// 2. Projects — CPT list table.
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Projects', 'tenprojects-ai-matcher' ),
			__( 'Projects', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			'edit.php?post_type=tp_project'
		);

		// 3. Developers — CPT list table.
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Developers', 'tenprojects-ai-matcher' ),
			__( 'Developers', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			'edit.php?post_type=tp_developer'
		);

		// 4. Locations — CPT list table.
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Locations', 'tenprojects-ai-matcher' ),
			__( 'Locations', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			'edit.php?post_type=tp_location'
		);

		// 5. Leads.
		$this->page_hooks[] = add_submenu_page(
			self::MENU_SLUG,
			__( 'Leads', 'tenprojects-ai-matcher' ),
			__( 'Leads', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-leads',
			array( $this, 'render_leads' )
		);

		// 6. Partners.
		$this->page_hooks[] = add_submenu_page(
			self::MENU_SLUG,
			__( 'Partners', 'tenprojects-ai-matcher' ),
			__( 'Partners', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-partners',
			array( $this, 'render_partners' )
		);

		// 7. Site Visits.
		$this->page_hooks[] = add_submenu_page(
			self::MENU_SLUG,
			__( 'Site Visits', 'tenprojects-ai-matcher' ),
			__( 'Site Visits', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-site-visits',
			array( $this, 'render_site_visits' )
		);

		// 8. Analytics.
		$this->page_hooks[] = add_submenu_page(
			self::MENU_SLUG,
			__( 'Analytics', 'tenprojects-ai-matcher' ),
			__( 'Analytics', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-analytics',
			array( $this, 'render_analytics' )
		);

		// 9. Import.
		$this->page_hooks[] = add_submenu_page(
			self::MENU_SLUG,
			__( 'Import', 'tenprojects-ai-matcher' ),
			__( 'Import', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-import',
			array( $this, 'render_import' )
		);

		// 10. Settings.
		$this->page_hooks[] = add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'tenprojects-ai-matcher' ),
			__( 'Settings', 'tenprojects-ai-matcher' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-settings',
			array( $this, 'render_settings' )
		);

		// Bootstrap sub-modules that need early hooks (e.g. project columns).
		$this->bootstrap_modules();
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
		);

		return in_array( $screen->post_type, $plugin_cpts, true );
	}
}
