<?php
/**
 * Plugin settings admin page.
 *
 * Uses the WordPress Settings API to register and render all plugin settings
 * organized into tabbed sections: AI, OTP, Scoring Weights, Lead Routing,
 * and General configuration.
 *
 * @package TenProjects\Admin
 * @since   1.0.0
 */

namespace TenProjects\Admin;

defined( 'ABSPATH' ) || exit;

class Settings_Admin {

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'tenprojects-settings';

	/**
	 * Option prefix for all settings.
	 *
	 * @var string
	 */
	const OPT_PREFIX = 'tp_';

	/**
	 * Available tabs.
	 *
	 * @return array Tab slug => label.
	 */
	private function get_tabs(): array {
		return array(
			'ai'           => __( 'AI Configuration', 'tenprojects-ai-matcher' ),
			'otp'          => __( 'OTP Configuration', 'tenprojects-ai-matcher' ),
			'scoring'      => __( 'Scoring Weights', 'tenprojects-ai-matcher' ),
			'lead_routing' => __( 'Lead Routing', 'tenprojects-ai-matcher' ),
			'general'      => __( 'General', 'tenprojects-ai-matcher' ),
		);
	}

	/**
	 * Get the current active tab.
	 *
	 * @return string
	 */
	private function get_current_tab(): string {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'ai'; // phpcs:ignore WordPress.Security.NonceVerification
		$tabs = $this->get_tabs();
		return array_key_exists( $tab, $tabs ) ? $tab : 'ai';
	}

	/**
	 * Register all settings (called on admin_init).
	 */
	public function register_settings(): void {
		$this->register_ai_settings();
		$this->register_otp_settings();
		$this->register_scoring_settings();
		$this->register_lead_routing_settings();
		$this->register_general_settings();
	}

	/**
	 * Render the settings page.
	 */
	public function render_page(): void {
		// Register settings on render (ensures they are registered for the current page load).
		$this->register_settings();

		$tabs        = $this->get_tabs();
		$current_tab = $this->get_current_tab();

		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html__( 'Settings', 'tenprojects-ai-matcher' ) . '</h1>';
		echo '</div>';

		// Tab navigation.
		echo '<div class="tp-meta-tabs" style="margin:-6px 0 16px;padding:0;">';
		foreach ( $tabs as $slug => $label ) {
			$active = ( $slug === $current_tab ) ? ' active' : '';
			$url    = admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&tab=' . $slug );
			echo '<a href="' . esc_url( $url ) . '" class="tp-meta-tab' . esc_attr( $active ) . '" style="text-decoration:none;">'
				. esc_html( $label ) . '</a>';
		}
		echo '</div>';

		// Settings form.
		echo '<form method="post" action="options.php">';

		switch ( $current_tab ) {
			case 'ai':
				settings_fields( 'tp_ai_settings' );
				do_settings_sections( 'tp_ai_settings' );
				break;

			case 'otp':
				settings_fields( 'tp_otp_settings' );
				do_settings_sections( 'tp_otp_settings' );
				break;

			case 'scoring':
				settings_fields( 'tp_scoring_settings' );
				do_settings_sections( 'tp_scoring_settings' );
				echo '<p class="description" style="margin-top:8px;">'
					. esc_html__( 'Tip: Use the Scoring Weights page for a visual weight editor with live preview.', 'tenprojects-ai-matcher' )
					. '</p>';
				break;

			case 'lead_routing':
				settings_fields( 'tp_lead_routing_settings' );
				do_settings_sections( 'tp_lead_routing_settings' );
				break;

			case 'general':
				settings_fields( 'tp_general_settings' );
				do_settings_sections( 'tp_general_settings' );
				break;
		}

		submit_button();

		echo '</form>';
		echo '</div>';
	}

	// ------------------------------------------------------------------
	// AI Configuration.
	// ------------------------------------------------------------------

	/**
	 * Register AI settings.
	 */
	private function register_ai_settings(): void {
		$group   = 'tp_ai_settings';
		$section = 'tp_ai_section';

		add_settings_section(
			$section,
			__( 'AI Provider Configuration', 'tenprojects-ai-matcher' ),
			function () {
				echo '<p>' . esc_html__( 'Configure the AI provider used for assessments, summaries, and lead analysis.', 'tenprojects-ai-matcher' ) . '</p>';
			},
			$group
		);

		// Provider.
		$this->add_select_field( $group, $section, 'ai_provider', __( 'AI Provider', 'tenprojects-ai-matcher' ), array(
			'claude'  => 'Claude (Anthropic)',
			'openai'  => 'OpenAI',
			'gemini'  => 'Gemini (Google)',
		) );

		// Model.
		$this->add_text_field( $group, $section, 'ai_model', __( 'Model Name', 'tenprojects-ai-matcher' ), 'claude-sonnet-4-20250514' );

		// API Key.
		$this->add_password_field( $group, $section, 'ai_api_key', __( 'API Key', 'tenprojects-ai-matcher' ) );

		// Temperature.
		$this->add_number_field( $group, $section, 'ai_temperature', __( 'Temperature', 'tenprojects-ai-matcher' ), '0.7', '0', '2', '0.1' );

		// Max Tokens.
		$this->add_number_field( $group, $section, 'ai_max_tokens', __( 'Max Tokens', 'tenprojects-ai-matcher' ), '4096', '256', '128000', '256' );
	}

	// ------------------------------------------------------------------
	// OTP Configuration.
	// ------------------------------------------------------------------

	/**
	 * Register OTP settings.
	 */
	private function register_otp_settings(): void {
		$group   = 'tp_otp_settings';
		$section = 'tp_otp_section';

		add_settings_section(
			$section,
			__( 'OTP Provider Configuration', 'tenprojects-ai-matcher' ),
			function () {
				echo '<p>' . esc_html__( 'Configure the OTP provider for phone verification.', 'tenprojects-ai-matcher' ) . '</p>';
			},
			$group
		);

		$this->add_select_field( $group, $section, 'otp_provider', __( 'OTP Provider', 'tenprojects-ai-matcher' ), array(
			'msg91'  => 'MSG91',
			'twilio' => 'Twilio',
		) );

		$this->add_password_field( $group, $section, 'otp_api_key', __( 'API Key', 'tenprojects-ai-matcher' ) );
		$this->add_text_field( $group, $section, 'otp_template_id', __( 'Template ID', 'tenprojects-ai-matcher' ) );
		$this->add_text_field( $group, $section, 'otp_twilio_sid', __( 'Twilio Account SID', 'tenprojects-ai-matcher' ) );
		$this->add_text_field( $group, $section, 'otp_twilio_from', __( 'Twilio From Number', 'tenprojects-ai-matcher' ) );
	}

	// ------------------------------------------------------------------
	// Scoring Weights.
	// ------------------------------------------------------------------

	/**
	 * Register scoring weight settings.
	 */
	private function register_scoring_settings(): void {
		$group   = 'tp_scoring_settings';
		$section = 'tp_scoring_section';

		add_settings_section(
			$section,
			__( 'Scoring Weights (End-User / Investor)', 'tenprojects-ai-matcher' ),
			function () {
				echo '<p>' . esc_html__( 'Set the weight for each scoring category. End-user and investor weights should each total 100.', 'tenprojects-ai-matcher' ) . '</p>';
			},
			$group
		);

		$categories = $this->get_scoring_categories();

		foreach ( $categories as $key => $label ) {
			// End-user weight.
			$opt_eu = 'scoring_weight_enduser_' . $key;
			register_setting( $group, self::OPT_PREFIX . $opt_eu, array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 5,
			) );

			// Investor weight.
			$opt_inv = 'scoring_weight_investor_' . $key;
			register_setting( $group, self::OPT_PREFIX . $opt_inv, array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 5,
			) );

			add_settings_field(
				$opt_eu,
				$label,
				function () use ( $opt_eu, $opt_inv ) {
					$eu_val  = get_option( self::OPT_PREFIX . $opt_eu, 5 );
					$inv_val = get_option( self::OPT_PREFIX . $opt_inv, 5 );

					echo '<div style="display:flex;gap:16px;align-items:center;">';
					echo '<label style="font-size:12px;color:#6B7280;">' . esc_html__( 'End-User:', 'tenprojects-ai-matcher' ) . ' ';
					echo '<input type="number" name="' . esc_attr( self::OPT_PREFIX . $opt_eu ) . '" value="'
						. esc_attr( $eu_val ) . '" min="0" max="100" style="width:70px;" /></label>';
					echo '<label style="font-size:12px;color:#6B7280;">' . esc_html__( 'Investor:', 'tenprojects-ai-matcher' ) . ' ';
					echo '<input type="number" name="' . esc_attr( self::OPT_PREFIX . $opt_inv ) . '" value="'
						. esc_attr( $inv_val ) . '" min="0" max="100" style="width:70px;" /></label>';
					echo '</div>';
				},
				$group,
				$section
			);
		}
	}

	// ------------------------------------------------------------------
	// Lead Routing.
	// ------------------------------------------------------------------

	/**
	 * Register lead routing settings.
	 */
	private function register_lead_routing_settings(): void {
		$group   = 'tp_lead_routing_settings';
		$section = 'tp_lead_routing_section';

		add_settings_section(
			$section,
			__( 'Lead Routing Configuration', 'tenprojects-ai-matcher' ),
			function () {
				echo '<p>' . esc_html__( 'Configure automatic lead routing and SLA timings.', 'tenprojects-ai-matcher' ) . '</p>';
			},
			$group
		);

		$this->add_number_field( $group, $section, 'default_credits_per_lead', __( 'Default Credits per Lead', 'tenprojects-ai-matcher' ), '1', '1', '100' );

		// Auto-route enabled.
		$opt_key = 'auto_route_enabled';
		register_setting( $group, self::OPT_PREFIX . $opt_key, array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => false,
		) );
		add_settings_field(
			$opt_key,
			__( 'Auto-Route Leads', 'tenprojects-ai-matcher' ),
			function () use ( $opt_key ) {
				$value = get_option( self::OPT_PREFIX . $opt_key, false );
				echo '<label>';
				echo '<input type="checkbox" name="' . esc_attr( self::OPT_PREFIX . $opt_key ) . '" value="1"'
					. checked( $value, true, false ) . ' />';
				echo ' ' . esc_html__( 'Automatically assign leads to matching partners', 'tenprojects-ai-matcher' );
				echo '</label>';
			},
			$group,
			$section
		);

		// SLA hours.
		$this->add_number_field( $group, $section, 'sla_hours_high', __( 'SLA Hours (High Priority)', 'tenprojects-ai-matcher' ), '2', '1', '72' );
		$this->add_number_field( $group, $section, 'sla_hours_medium', __( 'SLA Hours (Medium Priority)', 'tenprojects-ai-matcher' ), '8', '1', '72' );
		$this->add_number_field( $group, $section, 'sla_hours_low', __( 'SLA Hours (Low Priority)', 'tenprojects-ai-matcher' ), '24', '1', '168' );
	}

	// ------------------------------------------------------------------
	// General.
	// ------------------------------------------------------------------

	/**
	 * Register general settings.
	 */
	private function register_general_settings(): void {
		$group   = 'tp_general_settings';
		$section = 'tp_general_section';

		add_settings_section(
			$section,
			__( 'General Settings', 'tenprojects-ai-matcher' ),
			function () {
				echo '<p>' . esc_html__( 'General plugin configuration and contact information.', 'tenprojects-ai-matcher' ) . '</p>';
			},
			$group
		);

		$this->add_text_field( $group, $section, 'site_name', __( 'Site Name', 'tenprojects-ai-matcher' ), '10Projects' );
		$this->add_text_field( $group, $section, 'support_email', __( 'Support Email', 'tenprojects-ai-matcher' ), 'support@10projects.com' );
		$this->add_text_field( $group, $section, 'support_phone', __( 'Support Phone', 'tenprojects-ai-matcher' ), '' );
		$this->add_text_field( $group, $section, 'google_client_id', __( 'Google Client ID', 'tenprojects-ai-matcher' ), '' );
	}

	// ------------------------------------------------------------------
	// Scoring categories definition.
	// ------------------------------------------------------------------

	/**
	 * Get all 20 scoring category keys and labels.
	 *
	 * @return array Key => label.
	 */
	public function get_scoring_categories(): array {
		return array(
			'budget_fit'           => __( '1. Budget Fit', 'tenprojects-ai-matcher' ),
			'location_fit'         => __( '2. Location Fit', 'tenprojects-ai-matcher' ),
			'configuration_fit'    => __( '3. Configuration Fit', 'tenprojects-ai-matcher' ),
			'carpet_area_fit'      => __( '4. Carpet Area Fit', 'tenprojects-ai-matcher' ),
			'possession_fit'       => __( '5. Possession Fit', 'tenprojects-ai-matcher' ),
			'emi_fit'              => __( '6. Funding/EMI Fit', 'tenprojects-ai-matcher' ),
			'commute_fit'          => __( '7. Commute Fit', 'tenprojects-ai-matcher' ),
			'lifestyle_fit'        => __( '8. Lifestyle Fit', 'tenprojects-ai-matcher' ),
			'developer_reliability' => __( '9. Developer Reliability', 'tenprojects-ai-matcher' ),
			'construction_stage'   => __( '10. Construction Stage', 'tenprojects-ai-matcher' ),
			'legal_confidence'     => __( '11. Legal Confidence', 'tenprojects-ai-matcher' ),
			'resale_liquidity'     => __( '12. Resale Liquidity', 'tenprojects-ai-matcher' ),
			'rental_potential'     => __( '13. Rental Potential', 'tenprojects-ai-matcher' ),
			'appreciation_drivers' => __( '14. Appreciation Drivers', 'tenprojects-ai-matcher' ),
			'risk_compatibility'   => __( '15. Risk Compatibility', 'tenprojects-ai-matcher' ),
			'infrastructure'       => __( '16. Infrastructure Potential', 'tenprojects-ai-matcher' ),
			'family_suitability'   => __( '17. Family Suitability', 'tenprojects-ai-matcher' ),
			'urgency_match'        => __( '18. Urgency Match', 'tenprojects-ai-matcher' ),
			'inventory_availability' => __( '19. Inventory Availability', 'tenprojects-ai-matcher' ),
			'proximity_score'      => __( '20. Proximity Score', 'tenprojects-ai-matcher' ),
		);
	}

	// ------------------------------------------------------------------
	// Field helper methods.
	// ------------------------------------------------------------------

	/**
	 * Register and render a text field.
	 *
	 * @param string $group    Settings group.
	 * @param string $section  Section ID.
	 * @param string $key      Option key (without prefix).
	 * @param string $label    Field label.
	 * @param string $default  Default value.
	 */
	private function add_text_field( string $group, string $section, string $key, string $label, string $default = '' ): void {
		$opt_key = self::OPT_PREFIX . $key;

		register_setting( $group, $opt_key, array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => $default,
		) );

		add_settings_field(
			$key,
			$label,
			function () use ( $opt_key, $default ) {
				$value = get_option( $opt_key, $default );
				echo '<input type="text" name="' . esc_attr( $opt_key ) . '" value="' . esc_attr( $value )
					. '" class="regular-text" />';
			},
			$group,
			$section
		);
	}

	/**
	 * Register and render a password field.
	 *
	 * @param string $group   Settings group.
	 * @param string $section Section ID.
	 * @param string $key     Option key (without prefix).
	 * @param string $label   Field label.
	 */
	private function add_password_field( string $group, string $section, string $key, string $label ): void {
		$opt_key = self::OPT_PREFIX . $key;

		register_setting( $group, $opt_key, array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );

		add_settings_field(
			$key,
			$label,
			function () use ( $opt_key ) {
				$value = get_option( $opt_key, '' );
				echo '<input type="password" name="' . esc_attr( $opt_key ) . '" value="' . esc_attr( $value )
					. '" class="regular-text" autocomplete="off" />';
			},
			$group,
			$section
		);
	}

	/**
	 * Register and render a number field.
	 *
	 * @param string $group   Settings group.
	 * @param string $section Section ID.
	 * @param string $key     Option key (without prefix).
	 * @param string $label   Field label.
	 * @param string $default Default value.
	 * @param string $min     Min attribute.
	 * @param string $max     Max attribute.
	 * @param string $step    Step attribute.
	 */
	private function add_number_field( string $group, string $section, string $key, string $label, string $default = '0', string $min = '0', string $max = '', string $step = '1' ): void {
		$opt_key = self::OPT_PREFIX . $key;

		register_setting( $group, $opt_key, array(
			'type'              => 'number',
			'sanitize_callback' => function ( $val ) {
				return is_numeric( $val ) ? (float) $val : 0;
			},
			'default'           => $default,
		) );

		add_settings_field(
			$key,
			$label,
			function () use ( $opt_key, $default, $min, $max, $step ) {
				$value = get_option( $opt_key, $default );
				$max_attr = $max ? ' max="' . esc_attr( $max ) . '"' : '';
				echo '<input type="number" name="' . esc_attr( $opt_key ) . '" value="' . esc_attr( $value )
					. '" min="' . esc_attr( $min ) . '"' . $max_attr . ' step="' . esc_attr( $step ) . '" style="width:120px;" />';
			},
			$group,
			$section
		);
	}

	/**
	 * Register and render a select field.
	 *
	 * @param string $group   Settings group.
	 * @param string $section Section ID.
	 * @param string $key     Option key (without prefix).
	 * @param string $label   Field label.
	 * @param array  $options Value => label.
	 */
	private function add_select_field( string $group, string $section, string $key, string $label, array $options ): void {
		$opt_key = self::OPT_PREFIX . $key;

		$first_key = array_key_first( $options );

		register_setting( $group, $opt_key, array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => $first_key,
		) );

		add_settings_field(
			$key,
			$label,
			function () use ( $opt_key, $options, $first_key ) {
				$value = get_option( $opt_key, $first_key );
				echo '<select name="' . esc_attr( $opt_key ) . '">';
				foreach ( $options as $opt_val => $opt_label ) {
					echo '<option value="' . esc_attr( $opt_val ) . '"' . selected( $value, $opt_val, false ) . '>'
						. esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
			},
			$group,
			$section
		);
	}
}
