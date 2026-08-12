<?php
/**
 * Rental Details Section — Rent, Deposit, Furnishing, Preferences
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id            = $args['post_id'] ?? get_the_ID();
$monthly_rent       = intval( tp_get_meta( $post_id, 'monthly_rent' ) );
$security_deposit   = intval( tp_get_meta( $post_id, 'security_deposit' ) );
$maintenance        = intval( tp_get_meta( $post_id, 'maintenance_charges' ) );
$lock_in            = tp_get_meta( $post_id, 'lock_in_period' );
$notice_period      = tp_get_meta( $post_id, 'notice_period' );
$available_from     = tp_get_meta( $post_id, 'available_from' );
$tenant_preferred   = tp_get_meta( $post_id, 'tenant_preferred' );
$furnishing_status  = tp_get_meta( $post_id, 'furnishing_status' );
$furnishing_details = tp_get_meta( $post_id, 'furnishing_details' );
$pets_allowed       = tp_get_meta( $post_id, 'pets_allowed' );
$nonveg_allowed     = tp_get_meta( $post_id, 'nonveg_allowed' );
$water_supply       = tp_get_meta( $post_id, 'water_supply' );
$brokerage          = tp_get_meta( $post_id, 'brokerage' );

if ( ! $monthly_rent ) {
	return;
}
?>

<section class="tp-section" id="rent">
	<h2>Rent & Details</h2>

	<div class="tp-detail-table">
		<div class="tp-detail-table__row">
			<span class="tp-detail-table__label">Monthly Rent</span>
			<span class="tp-detail-table__value tp-detail-table__value--highlight">₹<?php echo number_format( $monthly_rent ); ?>/mo</span>
		</div>
		<?php if ( $security_deposit ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Security Deposit</span>
				<span class="tp-detail-table__value">₹<?php echo number_format( $security_deposit ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $maintenance ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Maintenance Charges</span>
				<span class="tp-detail-table__value">₹<?php echo number_format( $maintenance ); ?>/mo</span>
			</div>
		<?php endif; ?>
		<?php if ( $furnishing_status ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Furnishing</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $furnishing_status ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $available_from ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Available From</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $available_from ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $tenant_preferred ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Tenant Preferred</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $tenant_preferred ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $lock_in ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Lock-in Period</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $lock_in ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $notice_period ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Notice Period</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $notice_period ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $pets_allowed ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Pets Allowed</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $pets_allowed ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $nonveg_allowed ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Non-veg Cooking</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $nonveg_allowed ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $water_supply ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Water Supply</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $water_supply ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $brokerage ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Brokerage</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $brokerage ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $furnishing_details ) : ?>
		<div class="tp-furnishing-details">
			<h3>Furnishing Details</h3>
			<p><?php echo esc_html( $furnishing_details ); ?></p>
		</div>
	<?php endif; ?>
</section>
