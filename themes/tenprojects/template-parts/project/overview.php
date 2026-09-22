<?php
/**
 * Overview Section — Description + Project Details Grid + Why Consider
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id    = $args['post_id'] ?? get_the_ID();
$stage      = tp_get_meta( $post_id, 'construction_stage' );
$possession = tp_get_meta( $post_id, 'expected_possession' );
$rera       = tp_get_meta( $post_id, 'rera_number' );
$price_min  = floatval( tp_get_meta( $post_id, 'price_display_min' ) );
$price_max  = floatval( tp_get_meta( $post_id, 'price_display_max' ) );
$configs    = tp_get_meta( $post_id, 'available_configs_text' );
$land_parcel = tp_get_meta( $post_id, 'land_parcel' );
$pros       = tp_parse_json_meta( $post_id, 'pros' );
$location   = tp_get_location_term( $post_id );
$loc_name   = $location ? $location->name : '';
$title      = get_the_title( $post_id );

// Build dynamic overview paragraph.
$dyn = array();
$s1 = $title . ' is an exclusive residential project';
if ( $configs && $loc_name ) {
	$s1 .= ' offering ' . $configs . ' for sale in ' . $loc_name . ', Navi Mumbai.';
} elseif ( $configs ) {
	$s1 .= ' offering ' . $configs . '.';
} elseif ( $loc_name ) {
	$s1 .= ' located in ' . $loc_name . ', Navi Mumbai.';
} else {
	$s1 .= '.';
}
$dyn[] = $s1;
if ( $land_parcel ) {
	$dyn[] = 'Spread across ' . $land_parcel . ', this project is designed to cater to the lifestyle and space requirements of modern families.';
} else {
	$dyn[] = 'This project is designed to cater to the lifestyle and space requirements of modern families.';
}
if ( $stage || $possession ) {
	$s3 = '';
	if ( $stage ) $s3 .= 'It is currently ' . tp_format_stage( $stage ) . '. ';
	if ( $possession ) $s3 .= $title . ' is expected to be ready for possession by ' . tp_format_possession( $possession ) . '.';
	$dyn[] = trim( $s3 );
}
$description = implode( ' ', array_filter( $dyn ) );

// Project facts grid data.
$facts = array(
	array( 'label' => 'Project Location', 'value' => tp_get_meta( $post_id, 'project_location' ) ?: $loc_name, 'icon' => '<path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>' ),
	array( 'label' => 'Land Parcel', 'value' => tp_get_meta( $post_id, 'land_parcel' ), 'icon' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>' ),
	array( 'label' => 'Total Floors', 'value' => tp_get_meta( $post_id, 'floors_display' ), 'icon' => '<path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>' ),
	array( 'label' => 'Possession', 'value' => tp_format_possession( $possession ), 'icon' => '<path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>' ),
	array( 'label' => 'RERA Number', 'value' => $rera, 'icon' => '<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>' ),
	array( 'label' => 'Configurations', 'value' => $configs, 'icon' => '<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>' ),
	array( 'label' => 'Construction Stage', 'value' => tp_format_stage( $stage ), 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' ),
	array( 'label' => 'Price Range', 'value' => $price_min ? tp_format_price_range( $price_min, $price_max ) : '', 'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' ),
	array( 'label' => 'Total Towers', 'value' => tp_get_meta( $post_id, 'total_towers' ), 'icon' => '<path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>' ),
	array( 'label' => 'Total Floors', 'value' => tp_get_meta( $post_id, 'total_floors' ), 'icon' => '<path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>' ),
	array( 'label' => 'Total Units', 'value' => tp_get_meta( $post_id, 'total_units' ), 'icon' => '<path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>' ),
);
$facts = array_filter( $facts, function( $d ) { return ! empty( $d['value'] ); } );
?>

<section class="tp-section" id="overview">
	<h2>About <?php the_title(); ?><?php echo $loc_name ? ', ' . esc_html( $loc_name ) : ''; ?></h2>

	<?php if ( $description ) : ?>
		<div class="tp-overview-text tp-overview-text--clamped" id="overviewText">
			<?php echo wp_kses_post( $description ); ?>
		</div>
		<button class="tp-overview-toggle" id="overviewToggle" onclick="var o=document.getElementById('overviewText'),t=this;o.classList.toggle('tp-overview-text--clamped');t.textContent=o.classList.contains('tp-overview-text--clamped')?'See More \u25BE':'See Less \u25B4';">See More &#9662;</button>
	<?php endif; ?>

	<!-- Project Facts Grid -->
	<?php if ( ! empty( $facts ) ) : ?>
		<div class="tp-facts-grid">
			<?php foreach ( $facts as $d ) : ?>
				<div class="tp-fact">
					<div class="tp-fact__icon">
						<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><?php echo $d['icon']; ?></svg>
					</div>
					<div>
						<div class="tp-fact__label"><?php echo esc_html( $d['label'] ); ?></div>
						<div class="tp-fact__value"><?php echo esc_html( $d['value'] ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<!-- Why Consider -->
	<?php if ( ! empty( $pros ) ) : ?>
		<div class="tp-why-consider">
			<h3>Why Consider <?php the_title(); ?>?</h3>
			<div class="tp-why-consider__list">
				<?php foreach ( array_slice( $pros, 0, 6 ) as $pro ) : ?>
					<div class="tp-why-consider__item">
						<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--success)" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
						<span><?php echo esc_html( $pro ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
