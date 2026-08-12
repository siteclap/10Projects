<?php
/**
 * EMI Calculator Section — Standalone
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id   = $args['post_id'] ?? get_the_ID();
$price_min = intval( tp_get_meta( $post_id, 'price_display_min' ) );

if ( $price_min <= 0 ) return;

$property_val = $price_min * 100000;
$default_dp   = 20;
$default_rate = 8.5;
$default_yrs  = 20;
?>

<section class="tp-section" id="emi-calculator">
	<div class="tp-calc" id="tp-calc"
		data-property="<?php echo esc_attr( $property_val ); ?>"
		data-dp="<?php echo esc_attr( $default_dp ); ?>"
		data-rate="<?php echo esc_attr( $default_rate ); ?>"
		data-years="<?php echo esc_attr( $default_yrs ); ?>">

		<h3>EMI Calculator</h3>

		<div class="tp-calc__result">
			<div class="tp-calc__emi-label">Your Monthly EMI</div>
			<div class="tp-calc__emi-value" id="tc-emi">₹0</div>
		</div>

		<div class="tp-calc__sliders">
			<div class="tp-calc__field">
				<div class="tp-calc__field-head">
					<label>Property Value</label>
					<div class="tp-calc__input-wrap">
						<span class="tp-calc__rupee">₹</span>
						<input type="text" class="tp-calc__val-input" id="tc-prop-val" inputmode="numeric">
					</div>
				</div>
				<input type="range" class="tp-calc__range" id="tc-prop" min="1000000" max="100000000" step="100000" value="<?php echo esc_attr( $property_val ); ?>">
				<div class="tp-calc__range-labels"><span>₹10L</span><span>₹10Cr</span></div>
			</div>

			<div class="tp-calc__field">
				<div class="tp-calc__field-head">
					<label>Down Payment</label>
					<div class="tp-calc__input-wrap">
						<input type="text" class="tp-calc__val-input tp-calc__val-input--sm" id="tc-dp-val" inputmode="numeric">
						<span class="tp-calc__pct">%</span>
					</div>
				</div>
				<input type="range" class="tp-calc__range" id="tc-dp" min="5" max="75" step="1" value="<?php echo esc_attr( $default_dp ); ?>">
				<div class="tp-calc__range-labels"><span>5%</span><span>75%</span></div>
			</div>

			<div class="tp-calc__field">
				<div class="tp-calc__field-head">
					<label>Interest Rate</label>
					<div class="tp-calc__input-wrap">
						<input type="text" class="tp-calc__val-input tp-calc__val-input--sm" id="tc-rate-val" inputmode="decimal">
						<span class="tp-calc__pct">%</span>
					</div>
				</div>
				<input type="range" class="tp-calc__range" id="tc-rate" min="5" max="15" step="0.1" value="<?php echo esc_attr( $default_rate ); ?>">
				<div class="tp-calc__range-labels"><span>5%</span><span>15%</span></div>
			</div>

			<div class="tp-calc__field">
				<div class="tp-calc__field-head">
					<label>Loan Tenure</label>
					<div class="tp-calc__input-wrap">
						<input type="text" class="tp-calc__val-input tp-calc__val-input--sm" id="tc-tenure-val" inputmode="numeric">
						<span class="tp-calc__pct">Yrs</span>
					</div>
				</div>
				<input type="range" class="tp-calc__range" id="tc-tenure" min="5" max="30" step="1" value="<?php echo esc_attr( $default_yrs ); ?>">
				<div class="tp-calc__range-labels"><span>5 Yrs</span><span>30 Yrs</span></div>
			</div>
		</div>

		<div class="tp-calc__summary">
			<div class="tp-calc__sum-row">
				<span>Loan Amount</span>
				<span id="tc-loan">₹0</span>
			</div>
			<div class="tp-calc__sum-row">
				<span>Total Interest</span>
				<span id="tc-interest">₹0</span>
			</div>
			<div class="tp-calc__sum-row tp-calc__sum-row--total">
				<span>Total Payment</span>
				<span id="tc-total">₹0</span>
			</div>
		</div>
	</div>
</section>
