<?php
/**
 * Visual scoring weight configuration admin page.
 *
 * Displays all 20 scoring categories with end-user and investor weight inputs,
 * relative weight visualization bars, total weight validation, reset to defaults,
 * and a sample calculation preview.
 *
 * @package TenProjects\Admin
 * @since   1.0.0
 */

namespace TenProjects\Admin;

defined( 'ABSPATH' ) || exit;

class Scoring_Admin {

	/**
	 * Option prefix.
	 *
	 * @var string
	 */
	const OPT_PREFIX = 'tp_';

	/**
	 * Default end-user weights keyed by category slug.
	 *
	 * @var array
	 */
	const DEFAULTS_ENDUSER = array(
		'budget_fit'             => 15,
		'location_fit'           => 12,
		'configuration_fit'      => 8,
		'carpet_area_fit'        => 5,
		'possession_fit'         => 10,
		'emi_fit'                => 8,
		'commute_fit'            => 8,
		'lifestyle_fit'          => 5,
		'developer_reliability'  => 8,
		'construction_stage'     => 3,
		'legal_confidence'       => 5,
		'resale_liquidity'       => 2,
		'rental_potential'       => 1,
		'appreciation_drivers'   => 2,
		'risk_compatibility'     => 3,
		'infrastructure'         => 1,
		'family_suitability'     => 5,
		'urgency_match'          => 2,
		'inventory_availability' => 2,
		'proximity_score'        => 5,
	);

	/**
	 * Default investor weights keyed by category slug.
	 *
	 * @var array
	 */
	const DEFAULTS_INVESTOR = array(
		'budget_fit'             => 12,
		'location_fit'           => 8,
		'configuration_fit'      => 5,
		'carpet_area_fit'        => 3,
		'possession_fit'         => 3,
		'emi_fit'                => 5,
		'commute_fit'            => 0,
		'lifestyle_fit'          => 0,
		'developer_reliability'  => 8,
		'construction_stage'     => 8,
		'legal_confidence'       => 5,
		'resale_liquidity'       => 10,
		'rental_potential'       => 8,
		'appreciation_drivers'   => 12,
		'risk_compatibility'     => 5,
		'infrastructure'         => 5,
		'family_suitability'     => 0,
		'urgency_match'          => 3,
		'inventory_availability' => 3,
		'proximity_score'        => 0,
	);

	/**
	 * Category descriptions.
	 *
	 * @return array
	 */
	private function get_descriptions(): array {
		return array(
			'budget_fit'             => __( 'How well the project price fits within the customer budget.', 'tenprojects-ai-matcher' ),
			'location_fit'           => __( 'Match with preferred and alternative locations.', 'tenprojects-ai-matcher' ),
			'configuration_fit'      => __( 'BHK configuration match (exact, flexible, or close).', 'tenprojects-ai-matcher' ),
			'carpet_area_fit'        => __( 'Carpet area within the customer desired range.', 'tenprojects-ai-matcher' ),
			'possession_fit'         => __( 'Possession year vs. customer timeline preference.', 'tenprojects-ai-matcher' ),
			'emi_fit'                => __( 'Estimated EMI vs. comfortable EMI capacity.', 'tenprojects-ai-matcher' ),
			'commute_fit'            => __( 'Commute distance and time to workplace.', 'tenprojects-ai-matcher' ),
			'lifestyle_fit'          => __( 'Amenity and lifestyle preference match.', 'tenprojects-ai-matcher' ),
			'developer_reliability'  => __( 'Developer track record, brand, delivery history.', 'tenprojects-ai-matcher' ),
			'construction_stage'     => __( 'How the construction stage matches purpose and risk.', 'tenprojects-ai-matcher' ),
			'legal_confidence'       => __( 'RERA, approvals, litigation, bank approvals.', 'tenprojects-ai-matcher' ),
			'resale_liquidity'       => __( 'Ease of resale based on demand and location.', 'tenprojects-ai-matcher' ),
			'rental_potential'       => __( 'Estimated rental yield and vacancy risk.', 'tenprojects-ai-matcher' ),
			'appreciation_drivers'   => __( 'Infrastructure catalysts and market dynamics.', 'tenprojects-ai-matcher' ),
			'risk_compatibility'     => __( 'Project risk level vs. customer risk tolerance.', 'tenprojects-ai-matcher' ),
			'infrastructure'         => __( 'Planned infrastructure within 5 km radius.', 'tenprojects-ai-matcher' ),
			'family_suitability'     => __( 'Schools, hospitals, parks for family buyers.', 'tenprojects-ai-matcher' ),
			'urgency_match'          => __( 'Project status vs. purchase urgency.', 'tenprojects-ai-matcher' ),
			'inventory_availability' => __( 'Available units, floor choice, waitlist status.', 'tenprojects-ai-matcher' ),
			'proximity_score'        => __( 'Composite distance to key points of interest.', 'tenprojects-ai-matcher' ),
		);
	}

	/**
	 * Render the scoring weights page.
	 */
	public function render_page(): void {
		// Handle save.
		if ( isset( $_POST['tp_scoring_nonce'] ) && wp_verify_nonce( $_POST['tp_scoring_nonce'], 'tp_save_scoring_weights' ) ) {
			$this->handle_save();
		}

		// Handle reset.
		if ( isset( $_POST['tp_reset_nonce'] ) && wp_verify_nonce( $_POST['tp_reset_nonce'], 'tp_reset_scoring_weights' ) ) {
			$this->handle_reset();
		}

		$categories   = $this->get_categories();
		$descriptions = $this->get_descriptions();

		// Load current weights.
		$eu_weights  = array();
		$inv_weights = array();
		foreach ( array_keys( $categories ) as $key ) {
			$eu_weights[ $key ]  = (int) get_option( self::OPT_PREFIX . 'scoring_weight_enduser_' . $key, self::DEFAULTS_ENDUSER[ $key ] ?? 5 );
			$inv_weights[ $key ] = (int) get_option( self::OPT_PREFIX . 'scoring_weight_investor_' . $key, self::DEFAULTS_INVESTOR[ $key ] ?? 5 );
		}

		$eu_total  = array_sum( $eu_weights );
		$inv_total = array_sum( $inv_weights );
		$max_weight = max( 1, max( max( $eu_weights ), max( $inv_weights ) ) );

		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html__( 'Scoring Weights', 'tenprojects-ai-matcher' ) . '</h1>';
		echo '</div>';

		// Totals summary.
		echo '<div class="tp-dashboard-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:24px;">';

		$eu_color  = ( 100 === $eu_total ) ? '#10B981' : '#EF4444';
		$inv_color = ( 100 === $inv_total ) ? '#10B981' : '#EF4444';

		echo '<div class="tp-stat-card">';
		echo '<div class="tp-stat-card__label">' . esc_html__( 'End-User Total', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-stat-card__value" style="color:' . esc_attr( $eu_color ) . ';">' . esc_html( $eu_total ) . '<small style="font-size:14px;color:#6B7280;"> / 100</small></div>';
		echo '</div>';

		echo '<div class="tp-stat-card">';
		echo '<div class="tp-stat-card__label">' . esc_html__( 'Investor Total', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-stat-card__value" style="color:' . esc_attr( $inv_color ) . ';">' . esc_html( $inv_total ) . '<small style="font-size:14px;color:#6B7280;"> / 100</small></div>';
		echo '</div>';

		echo '</div>';

		if ( 100 !== $eu_total || 100 !== $inv_total ) {
			echo '<div class="notice notice-warning" style="margin-bottom:16px;">';
			echo '<p>' . esc_html__( 'Weights should total exactly 100 for each profile. Adjust the values below.', 'tenprojects-ai-matcher' ) . '</p>';
			echo '</div>';
		}

		// Weight form.
		echo '<form method="post">';
		wp_nonce_field( 'tp_save_scoring_weights', 'tp_scoring_nonce' );

		echo '<table class="widefat" style="margin-bottom:24px;">';
		echo '<thead><tr>';
		echo '<th style="width:30%;">' . esc_html__( 'Category', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th style="width:20%;">' . esc_html__( 'Description', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th style="width:10%;text-align:center;">' . esc_html__( 'End-User', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th style="width:15%;">' . esc_html__( 'End-User Bar', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th style="width:10%;text-align:center;">' . esc_html__( 'Investor', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th style="width:15%;">' . esc_html__( 'Investor Bar', 'tenprojects-ai-matcher' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		$i = 0;
		foreach ( $categories as $key => $label ) {
			$i++;
			$eu_val  = $eu_weights[ $key ];
			$inv_val = $inv_weights[ $key ];
			$eu_pct  = round( ( $eu_val / $max_weight ) * 100 );
			$inv_pct = round( ( $inv_val / $max_weight ) * 100 );
			$desc    = $descriptions[ $key ] ?? '';

			$bg = ( $i % 2 === 0 ) ? 'background:#FAFAFA;' : '';

			echo '<tr style="' . $bg . '">';

			// Category name.
			echo '<td><strong>' . esc_html( $label ) . '</strong></td>';

			// Description.
			echo '<td style="font-size:12px;color:#6B7280;">' . esc_html( $desc ) . '</td>';

			// End-user input.
			echo '<td style="text-align:center;">';
			echo '<input type="number" name="eu_' . esc_attr( $key ) . '" value="' . esc_attr( $eu_val )
				. '" min="0" max="100" style="width:60px;text-align:center;" />';
			echo '</td>';

			// End-user bar.
			echo '<td>';
			echo '<div style="background:#E5E7EB;border-radius:4px;height:16px;overflow:hidden;">';
			echo '<div style="width:' . esc_attr( $eu_pct ) . '%;height:100%;background:#1A56DB;border-radius:4px;transition:width 0.3s;"></div>';
			echo '</div>';
			echo '</td>';

			// Investor input.
			echo '<td style="text-align:center;">';
			echo '<input type="number" name="inv_' . esc_attr( $key ) . '" value="' . esc_attr( $inv_val )
				. '" min="0" max="100" style="width:60px;text-align:center;" />';
			echo '</td>';

			// Investor bar.
			echo '<td>';
			echo '<div style="background:#E5E7EB;border-radius:4px;height:16px;overflow:hidden;">';
			echo '<div style="width:' . esc_attr( $inv_pct ) . '%;height:100%;background:#F59E0B;border-radius:4px;transition:width 0.3s;"></div>';
			echo '</div>';
			echo '</td>';

			echo '</tr>';
		}

		echo '</tbody></table>';

		echo '<div style="display:flex;gap:12px;align-items:center;">';
		submit_button( __( 'Save Weights', 'tenprojects-ai-matcher' ), 'primary', 'tp_save_weights', false );

		echo '</form>';

		// Reset form (separate form to avoid conflicts).
		echo '<form method="post" style="display:inline;" onsubmit="return confirm(\''
			. esc_js( __( 'Reset all weights to defaults?', 'tenprojects-ai-matcher' ) ) . '\');">';
		wp_nonce_field( 'tp_reset_scoring_weights', 'tp_reset_nonce' );
		submit_button( __( 'Reset to Defaults', 'tenprojects-ai-matcher' ), 'secondary', 'tp_reset_weights', false );
		echo '</form>';

		echo '</div>';

		// Sample calculation preview.
		$this->render_sample_calculation( $eu_weights, $inv_weights );

		echo '</div>';
	}

	/**
	 * Handle weight save.
	 */
	private function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$categories = $this->get_categories();

		foreach ( array_keys( $categories ) as $key ) {
			$eu_val  = isset( $_POST[ 'eu_' . $key ] ) ? absint( $_POST[ 'eu_' . $key ] ) : 0;
			$inv_val = isset( $_POST[ 'inv_' . $key ] ) ? absint( $_POST[ 'inv_' . $key ] ) : 0;

			update_option( self::OPT_PREFIX . 'scoring_weight_enduser_' . $key, $eu_val );
			update_option( self::OPT_PREFIX . 'scoring_weight_investor_' . $key, $inv_val );
		}

		echo '<div class="notice notice-success is-dismissible"><p>'
			. esc_html__( 'Scoring weights saved successfully.', 'tenprojects-ai-matcher' ) . '</p></div>';
	}

	/**
	 * Handle reset to defaults.
	 */
	private function handle_reset(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		foreach ( self::DEFAULTS_ENDUSER as $key => $val ) {
			update_option( self::OPT_PREFIX . 'scoring_weight_enduser_' . $key, $val );
		}
		foreach ( self::DEFAULTS_INVESTOR as $key => $val ) {
			update_option( self::OPT_PREFIX . 'scoring_weight_investor_' . $key, $val );
		}

		echo '<div class="notice notice-success is-dismissible"><p>'
			. esc_html__( 'Weights reset to defaults.', 'tenprojects-ai-matcher' ) . '</p></div>';
	}

	/**
	 * Render a sample calculation preview with current weights.
	 *
	 * @param array $eu_weights  End-user weights.
	 * @param array $inv_weights Investor weights.
	 */
	private function render_sample_calculation( array $eu_weights, array $inv_weights ): void {
		$categories = $this->get_categories();

		// Sample scores (simulated project).
		$sample_scores = array(
			'budget_fit'             => 85,
			'location_fit'           => 92,
			'configuration_fit'      => 100,
			'carpet_area_fit'        => 78,
			'possession_fit'         => 65,
			'emi_fit'                => 72,
			'commute_fit'            => 88,
			'lifestyle_fit'          => 70,
			'developer_reliability'  => 80,
			'construction_stage'     => 75,
			'legal_confidence'       => 90,
			'resale_liquidity'       => 60,
			'rental_potential'       => 55,
			'appreciation_drivers'   => 68,
			'risk_compatibility'     => 82,
			'infrastructure'         => 70,
			'family_suitability'     => 85,
			'urgency_match'          => 60,
			'inventory_availability' => 100,
			'proximity_score'        => 76,
		);

		$eu_total_weight  = max( 1, array_sum( $eu_weights ) );
		$inv_total_weight = max( 1, array_sum( $inv_weights ) );

		$eu_fit_score  = 0;
		$inv_fit_score = 0;

		foreach ( $sample_scores as $key => $score ) {
			$eu_fit_score  += $score * ( ( $eu_weights[ $key ] ?? 0 ) / $eu_total_weight );
			$inv_fit_score += $score * ( ( $inv_weights[ $key ] ?? 0 ) / $inv_total_weight );
		}

		$eu_fit_score  = round( $eu_fit_score );
		$inv_fit_score = round( $inv_fit_score );

		echo '<div style="margin-top:32px;">';
		echo '<h3>' . esc_html__( 'Sample Calculation Preview', 'tenprojects-ai-matcher' ) . '</h3>';
		echo '<p style="color:#6B7280;">'
			. esc_html__( 'Simulated project with sample category scores to preview how current weights affect the final Fit Score.', 'tenprojects-ai-matcher' )
			. '</p>';

		echo '<div class="tp-dashboard-grid" style="grid-template-columns:repeat(2,1fr);">';

		// End-user result.
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-stat-card__label">' . esc_html__( 'End-User Fit Score', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-stat-card__value" style="color:#1A56DB;">' . esc_html( $eu_fit_score ) . '<small style="font-size:14px;color:#6B7280;"> / 100</small></div>';
		echo '</div>';

		// Investor result.
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-stat-card__label">' . esc_html__( 'Investor Fit Score', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-stat-card__value" style="color:#F59E0B;">' . esc_html( $inv_fit_score ) . '<small style="font-size:14px;color:#6B7280;"> / 100</small></div>';
		echo '</div>';

		echo '</div>';

		// Breakdown table.
		echo '<details style="margin-top:12px;">';
		echo '<summary style="cursor:pointer;font-weight:600;color:#374151;">'
			. esc_html__( 'View Calculation Breakdown', 'tenprojects-ai-matcher' ) . '</summary>';

		echo '<table class="widefat striped" style="margin-top:8px;">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Category', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th style="text-align:center;">' . esc_html__( 'Score', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th style="text-align:center;">' . esc_html__( 'EU Weight', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th style="text-align:center;">' . esc_html__( 'EU Contribution', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th style="text-align:center;">' . esc_html__( 'Inv Weight', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th style="text-align:center;">' . esc_html__( 'Inv Contribution', 'tenprojects-ai-matcher' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $categories as $key => $label ) {
			$score        = $sample_scores[ $key ] ?? 0;
			$eu_w         = $eu_weights[ $key ] ?? 0;
			$inv_w        = $inv_weights[ $key ] ?? 0;
			$eu_contrib   = round( $score * ( $eu_w / $eu_total_weight ), 2 );
			$inv_contrib  = round( $score * ( $inv_w / $inv_total_weight ), 2 );

			echo '<tr>';
			echo '<td>' . esc_html( $label ) . '</td>';
			echo '<td style="text-align:center;">' . esc_html( $score ) . '</td>';
			echo '<td style="text-align:center;">' . esc_html( $eu_w ) . '%</td>';
			echo '<td style="text-align:center;"><strong>' . esc_html( $eu_contrib ) . '</strong></td>';
			echo '<td style="text-align:center;">' . esc_html( $inv_w ) . '%</td>';
			echo '<td style="text-align:center;"><strong>' . esc_html( $inv_contrib ) . '</strong></td>';
			echo '</tr>';
		}

		echo '<tr style="background:#F3F4F6;font-weight:700;">';
		echo '<td>' . esc_html__( 'TOTAL', 'tenprojects-ai-matcher' ) . '</td>';
		echo '<td></td>';
		echo '<td style="text-align:center;">' . esc_html( array_sum( $eu_weights ) ) . '%</td>';
		echo '<td style="text-align:center;color:#1A56DB;">' . esc_html( $eu_fit_score ) . '</td>';
		echo '<td style="text-align:center;">' . esc_html( array_sum( $inv_weights ) ) . '%</td>';
		echo '<td style="text-align:center;color:#F59E0B;">' . esc_html( $inv_fit_score ) . '</td>';
		echo '</tr>';

		echo '</tbody></table>';
		echo '</details>';

		echo '</div>';
	}

	/**
	 * Get all 20 scoring category keys and labels.
	 *
	 * @return array
	 */
	private function get_categories(): array {
		return array(
			'budget_fit'             => __( '1. Budget Fit', 'tenprojects-ai-matcher' ),
			'location_fit'           => __( '2. Location Fit', 'tenprojects-ai-matcher' ),
			'configuration_fit'      => __( '3. Configuration Fit', 'tenprojects-ai-matcher' ),
			'carpet_area_fit'        => __( '4. Carpet Area Fit', 'tenprojects-ai-matcher' ),
			'possession_fit'         => __( '5. Possession Fit', 'tenprojects-ai-matcher' ),
			'emi_fit'                => __( '6. Funding/EMI Fit', 'tenprojects-ai-matcher' ),
			'commute_fit'            => __( '7. Commute Fit', 'tenprojects-ai-matcher' ),
			'lifestyle_fit'          => __( '8. Lifestyle Fit', 'tenprojects-ai-matcher' ),
			'developer_reliability'  => __( '9. Developer Reliability', 'tenprojects-ai-matcher' ),
			'construction_stage'     => __( '10. Construction Stage', 'tenprojects-ai-matcher' ),
			'legal_confidence'       => __( '11. Legal Confidence', 'tenprojects-ai-matcher' ),
			'resale_liquidity'       => __( '12. Resale Liquidity', 'tenprojects-ai-matcher' ),
			'rental_potential'       => __( '13. Rental Potential', 'tenprojects-ai-matcher' ),
			'appreciation_drivers'   => __( '14. Appreciation Drivers', 'tenprojects-ai-matcher' ),
			'risk_compatibility'     => __( '15. Risk Compatibility', 'tenprojects-ai-matcher' ),
			'infrastructure'         => __( '16. Infrastructure Potential', 'tenprojects-ai-matcher' ),
			'family_suitability'     => __( '17. Family Suitability', 'tenprojects-ai-matcher' ),
			'urgency_match'          => __( '18. Urgency Match', 'tenprojects-ai-matcher' ),
			'inventory_availability' => __( '19. Inventory Availability', 'tenprojects-ai-matcher' ),
			'proximity_score'        => __( '20. Proximity Score', 'tenprojects-ai-matcher' ),
		);
	}
}
