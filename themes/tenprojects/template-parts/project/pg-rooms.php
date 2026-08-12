<?php
/**
 * PG Rooms & Pricing Section — Sharing-wise rents, deposit, facilities grid
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id         = $args['post_id'] ?? get_the_ID();
$pg_gender       = tp_get_meta( $post_id, 'pg_gender' );
$pg_occupant     = tp_get_meta( $post_id, 'pg_occupant' );
$pg_single_rent  = intval( tp_get_meta( $post_id, 'pg_single_rent' ) );
$pg_double_rent  = intval( tp_get_meta( $post_id, 'pg_double_rent' ) );
$pg_triple_rent  = intval( tp_get_meta( $post_id, 'pg_triple_rent' ) );
$pg_deposit      = intval( tp_get_meta( $post_id, 'pg_deposit' ) );
$pg_notice       = tp_get_meta( $post_id, 'pg_notice_period' );
$pg_meals        = tp_get_meta( $post_id, 'pg_meals' );
$pg_meal_type    = tp_get_meta( $post_id, 'pg_meal_type' );
$pg_kitchen      = tp_get_meta( $post_id, 'pg_kitchen' );
$pg_wifi         = tp_get_meta( $post_id, 'pg_wifi' );
$pg_laundry      = tp_get_meta( $post_id, 'pg_laundry' );
$pg_housekeeping = tp_get_meta( $post_id, 'pg_housekeeping' );
$pg_ac           = tp_get_meta( $post_id, 'pg_ac' );

$has_data = $pg_single_rent || $pg_double_rent || $pg_triple_rent;
if ( ! $has_data ) {
	return;
}
?>

<section class="tp-section" id="rooms">
	<h2>Rooms & Pricing</h2>

	<?php if ( $pg_gender || $pg_occupant ) : ?>
		<div class="tp-pg-badges">
			<?php if ( $pg_gender ) : ?>
				<span class="tp-badge tp-badge--primary"><?php echo esc_html( $pg_gender ); ?></span>
			<?php endif; ?>
			<?php if ( $pg_occupant ) : ?>
				<span class="tp-badge tp-badge--accent"><?php echo esc_html( $pg_occupant ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<!-- Sharing Cards -->
	<div class="tp-pg-sharing-cards">
		<?php
		$sharings = array(
			array( 'label' => 'Single Sharing', 'rent' => $pg_single_rent, 'icon' => '1' ),
			array( 'label' => 'Double Sharing', 'rent' => $pg_double_rent, 'icon' => '2' ),
			array( 'label' => 'Triple Sharing', 'rent' => $pg_triple_rent, 'icon' => '3' ),
		);
		foreach ( $sharings as $s ) :
			if ( ! $s['rent'] ) continue;
		?>
			<div class="tp-pg-sharing-card">
				<div class="tp-pg-sharing-card__icon"><?php echo esc_html( $s['icon'] ); ?></div>
				<div class="tp-pg-sharing-card__label"><?php echo esc_html( $s['label'] ); ?></div>
				<div class="tp-pg-sharing-card__price">₹<?php echo number_format( $s['rent'] ); ?><span>/mo</span></div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Additional Details -->
	<div class="tp-detail-table" style="margin-top:1.5rem;">
		<?php if ( $pg_deposit ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Security Deposit</span>
				<span class="tp-detail-table__value">₹<?php echo number_format( $pg_deposit ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $pg_notice ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Notice Period</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $pg_notice ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<!-- Facilities Grid -->
	<?php
	$facilities = array(
		array( 'label' => 'Meals', 'value' => $pg_meals ),
		array( 'label' => 'Meal Type', 'value' => $pg_meal_type ),
		array( 'label' => 'Kitchen Access', 'value' => $pg_kitchen ),
		array( 'label' => 'Wi-Fi', 'value' => $pg_wifi ),
		array( 'label' => 'Laundry', 'value' => $pg_laundry ),
		array( 'label' => 'Housekeeping', 'value' => $pg_housekeeping ),
		array( 'label' => 'AC Rooms', 'value' => $pg_ac ),
	);
	$active_facilities = array_filter( $facilities, function( $f ) { return ! empty( $f['value'] ); } );
	if ( ! empty( $active_facilities ) ) :
	?>
		<h3 style="margin-top:1.5rem;">Facilities</h3>
		<div class="tp-pg-facilities">
			<?php foreach ( $active_facilities as $f ) : ?>
				<div class="tp-pg-facility">
					<span class="tp-pg-facility__label"><?php echo esc_html( $f['label'] ); ?></span>
					<span class="tp-pg-facility__value"><?php echo esc_html( $f['value'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
