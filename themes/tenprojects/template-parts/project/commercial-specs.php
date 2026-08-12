<?php
/**
 * Commercial Specs Section — Area, CAM, Power, Grade, Lease Terms
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id           = $args['post_id'] ?? get_the_ID();
$commercial_type   = tp_get_meta( $post_id, 'commercial_type' );
$building_grade    = tp_get_meta( $post_id, 'building_grade' );
$fitout_status     = tp_get_meta( $post_id, 'fitout_status' );
$commercial_carpet = intval( tp_get_meta( $post_id, 'commercial_carpet' ) );
$price_per_sqft    = intval( tp_get_meta( $post_id, 'price_per_sqft' ) );
$cam_charges       = intval( tp_get_meta( $post_id, 'cam_charges' ) );
$power_load        = tp_get_meta( $post_id, 'power_load' );
$seating_capacity  = intval( tp_get_meta( $post_id, 'seating_capacity' ) );
$cabins_count      = intval( tp_get_meta( $post_id, 'cabins_count' ) );
$washrooms_count   = intval( tp_get_meta( $post_id, 'washrooms_count' ) );
$hvac_type         = tp_get_meta( $post_id, 'hvac_type' );
$parking_bays      = intval( tp_get_meta( $post_id, 'parking_bays' ) );
$fire_noc          = tp_get_meta( $post_id, 'fire_noc' );
$lease_term        = tp_get_meta( $post_id, 'lease_term' );
$lock_in_period    = tp_get_meta( $post_id, 'lock_in_period' );
$escalation_clause = tp_get_meta( $post_id, 'escalation_clause' );
$price_min         = intval( tp_get_meta( $post_id, 'price_display_min' ) );

$has_data = $commercial_type || $commercial_carpet || $price_per_sqft || $price_min;
if ( ! $has_data ) {
	return;
}
?>

<section class="tp-section" id="specs">
	<h2>Price & Specifications</h2>

	<div class="tp-detail-table">
		<?php if ( $commercial_type ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Property Type</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $commercial_type ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $commercial_carpet ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Carpet Area</span>
				<span class="tp-detail-table__value"><?php echo number_format( $commercial_carpet ); ?> sqft</span>
			</div>
		<?php endif; ?>
		<?php if ( $price_per_sqft ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Price per sqft</span>
				<span class="tp-detail-table__value tp-detail-table__value--highlight">₹<?php echo number_format( $price_per_sqft ); ?>/sqft</span>
			</div>
		<?php endif; ?>
		<?php if ( $price_min ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Total Price</span>
				<span class="tp-detail-table__value tp-detail-table__value--highlight"><?php echo esc_html( tp_format_price( $price_min ) ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $cam_charges ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">CAM Charges</span>
				<span class="tp-detail-table__value">₹<?php echo number_format( $cam_charges ); ?>/sqft/mo</span>
			</div>
		<?php endif; ?>
		<?php if ( $building_grade ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Building Grade</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $building_grade ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $fitout_status ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Fit-out Status</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $fitout_status ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $seating_capacity ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Seating Capacity</span>
				<span class="tp-detail-table__value"><?php echo number_format( $seating_capacity ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $cabins_count ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Cabins</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $cabins_count ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $washrooms_count ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Washrooms</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $washrooms_count ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $power_load ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Power Load</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $power_load ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $hvac_type ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">HVAC / AC</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $hvac_type ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $parking_bays ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Parking Bays</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $parking_bays ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $fire_noc ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Fire NOC</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $fire_noc ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $lease_term || $lock_in_period || $escalation_clause ) : ?>
		<h3 style="margin-top:1.5rem;">Lease Terms</h3>
		<div class="tp-detail-table">
			<?php if ( $lease_term ) : ?>
				<div class="tp-detail-table__row">
					<span class="tp-detail-table__label">Lease Term</span>
					<span class="tp-detail-table__value"><?php echo esc_html( $lease_term ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( $lock_in_period ) : ?>
				<div class="tp-detail-table__row">
					<span class="tp-detail-table__label">Lock-in Period</span>
					<span class="tp-detail-table__value"><?php echo esc_html( $lock_in_period ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( $escalation_clause ) : ?>
				<div class="tp-detail-table__row">
					<span class="tp-detail-table__label">Escalation</span>
					<span class="tp-detail-table__value"><?php echo esc_html( $escalation_clause ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section>
