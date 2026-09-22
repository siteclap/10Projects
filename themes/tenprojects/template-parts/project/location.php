<?php
/**
 * Location Section — Map, Advantages, Brief, Distances
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
	<h2>Location of <?php the_title(); ?><?php echo $loc_name ? ', ' . esc_html( $loc_name ) : ''; ?></h2>

	<?php if ( $brief ) : ?>
		<div class="tp-loc-brief">
			<div class="tp-loc-brief__icon">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
			</div>
			<div>
				<h3 class="tp-loc-brief__title">About <?php echo esc_html( $loc_name ?: 'the Location' ); ?></h3>
				<p class="tp-loc-brief__text"><?php echo wp_kses_post( $brief ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<div class="tp-loc-map">
		<iframe
			src="https://maps.google.com/maps?q=<?php echo rawurlencode( $map_query ); ?>&output=embed"
			width="100%"
			height="300"
			loading="lazy"
			allowfullscreen
			referrerpolicy="no-referrer-when-downgrade"
		></iframe>
	</div>

	<?php if ( $adv1 || $adv2 ) : ?>
		<div class="tp-loc-grid">
			<?php if ( $adv1 ) : ?>
				<div class="tp-loc-card">
					<div class="tp-loc-card__header">
						<div class="tp-loc-card__icon tp-loc-card__icon--blue">
							<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
						</div>
						<h3 class="tp-loc-card__title">Location Advantages</h3>
					</div>
					<div class="tp-loc-card__body">
						<?php echo wp_kses_post( $adv1 ); ?>
					</div>
				</div>
			<?php endif; ?>
			<?php if ( $adv2 ) : ?>
				<div class="tp-loc-card">
					<div class="tp-loc-card__header">
						<div class="tp-loc-card__icon tp-loc-card__icon--purple">
							<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
						</div>
						<h3 class="tp-loc-card__title">Nearby Connectivity</h3>
					</div>
					<div class="tp-loc-card__body">
						<?php echo wp_kses_post( $adv2 ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php
	// Distance indicators with icons.
	$dist_data = array(
		array(
			'label' => 'Railway Station',
			'value' => tp_get_meta( $post_id, 'railway_distance_km' ),
			'icon'  => '<path d="M8 17l-2 4m10-4l2 4M12 2v2m-4 0h8l2 10H6L8 4m-4 10h16m-6 0v4H10v-4"/>',
			'color' => '#1A56DB',
		),
		array(
			'label' => 'Metro Station',
			'value' => tp_get_meta( $post_id, 'metro_distance_km' ),
			'icon'  => '<rect x="4" y="3" width="16" height="14" rx="2"/><path d="M9 21l3-6 3 6"/><path d="M10 17h4"/>',
			'color' => '#7C3AED',
		),
		array(
			'label' => 'Highway',
			'value' => tp_get_meta( $post_id, 'highway_distance_km' ),
			'icon'  => '<path d="M9 20l3-18 3 18M5 12h14"/>',
			'color' => '#059669',
		),
		array(
			'label' => 'Airport',
			'value' => tp_get_meta( $post_id, 'airport_distance_km' ),
			'icon'  => '<path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>',
			'color' => '#DC2626',
		),
		array(
			'label' => 'School',
			'value' => tp_get_meta( $post_id, 'school_distance_km' ),
			'icon'  => '<path d="M12 14l9-5-9-5-9 5 9 5zm0 0v7.5M6 10.5V17l6 3.5L18 17v-6.5"/>',
			'color' => '#F59E0B',
		),
		array(
			'label' => 'Hospital',
			'value' => tp_get_meta( $post_id, 'hospital_distance_km' ),
			'icon'  => '<path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2zM12 7v10m-5-5h10"/>',
			'color' => '#EF4444',
		),
		array(
			'label' => 'Shopping Mall',
			'value' => tp_get_meta( $post_id, 'mall_distance_km' ),
			'icon'  => '<path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>',
			'color' => '#EC4899',
		),
		array(
			'label' => 'Employment Hub',
			'value' => tp_get_meta( $post_id, 'employment_hub_km' ),
			'icon'  => '<path d="M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>',
			'color' => '#6366F1',
		),
	);
	$dist_data = array_filter( $dist_data, function( $d ) { return ! empty( $d['value'] ); } );

	if ( ! empty( $dist_data ) ) :
	?>
		<div class="tp-loc-distances">
			<h3 class="tp-loc-distances__title">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
				Distance to Key Points
			</h3>
			<div class="tp-loc-dist-grid">
				<?php foreach ( $dist_data as $d ) : ?>
					<div class="tp-loc-dist-item">
						<div class="tp-loc-dist-item__icon" style="color: <?php echo esc_attr( $d['color'] ); ?>;">
							<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><?php echo $d['icon']; ?></svg>
						</div>
						<div class="tp-loc-dist-item__value"><?php echo esc_html( $d['value'] ); ?> km</div>
						<div class="tp-loc-dist-item__label"><?php echo esc_html( $d['label'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
