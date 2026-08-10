<?php
/**
 * Location Section — Map, Advantages, Brief
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id     = $args['post_id'] ?? get_the_ID();
$address_pin = tp_get_meta( $post_id, 'address_pin' );
$adv1        = tp_get_meta( $post_id, 'location_advantage_1' );
$adv2        = tp_get_meta( $post_id, 'location_advantage_2' );
$brief       = tp_get_meta( $post_id, 'location_brief' );
$location    = tp_get_location_term( $post_id );
$loc_name    = $location ? $location->name : '';

$map_query = $address_pin ?: get_the_title() . ', ' . $loc_name . ', Navi Mumbai';
?>

<section class="tp-section" id="location">
	<h2>Location</h2>

	<div class="tp-map-container">
		<iframe
			src="https://maps.google.com/maps?q=<?php echo rawurlencode( $map_query ); ?>&output=embed"
			width="100%"
			height="350"
			loading="lazy"
			allowfullscreen
			referrerpolicy="no-referrer-when-downgrade"
		></iframe>
	</div>

	<?php if ( $adv1 || $adv2 ) : ?>
		<div class="tp-location-advantages">
			<?php if ( $adv1 ) : ?>
				<div class="tp-card">
					<h3>Location Advantages</h3>
					<div><?php echo wp_kses_post( $adv1 ); ?></div>
				</div>
			<?php endif; ?>
			<?php if ( $adv2 ) : ?>
				<div class="tp-card">
					<h3>Nearby Connectivity</h3>
					<div><?php echo wp_kses_post( $adv2 ); ?></div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $brief ) : ?>
		<div class="tp-card mt-xl">
			<h3 style="font-size:16px;font-weight:600;margin-bottom:var(--md);">About the Location</h3>
			<div style="font-size:14px;color:var(--gray-600);line-height:1.7;">
				<?php echo wp_kses_post( $brief ); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	// Distance indicators.
	$distances = array(
		'Railway Station'  => tp_get_meta( $post_id, 'railway_distance_km' ),
		'Metro Station'    => tp_get_meta( $post_id, 'metro_distance_km' ),
		'Highway'          => tp_get_meta( $post_id, 'highway_distance_km' ),
		'Airport'          => tp_get_meta( $post_id, 'airport_distance_km' ),
		'School'           => tp_get_meta( $post_id, 'school_distance_km' ),
		'Hospital'         => tp_get_meta( $post_id, 'hospital_distance_km' ),
		'Shopping Mall'    => tp_get_meta( $post_id, 'mall_distance_km' ),
		'Employment Hub'   => tp_get_meta( $post_id, 'employment_hub_km' ),
	);
	$distances = array_filter( $distances );

	if ( ! empty( $distances ) ) :
	?>
		<div class="tp-card mt-xl">
			<h3 style="font-size:16px;font-weight:600;margin-bottom:var(--md);">Distance to Key Points</h3>
			<div class="tp-stats-grid">
				<?php foreach ( $distances as $label => $km ) : ?>
					<div>
						<div class="tp-stat__label"><?php echo esc_html( $label ); ?></div>
						<div class="tp-stat__value"><?php echo esc_html( $km ); ?> km</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
