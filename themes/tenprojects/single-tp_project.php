<?php
/**
 * Single Project Detail Page (PDP) — Polished
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

get_header();

$post_id      = get_the_ID();
$location     = tp_get_location_term( $post_id );
$loc_name     = $location ? $location->name : '';
$loc_slug     = $location ? $location->slug : '';
$price_min    = floatval( tp_get_meta( $post_id, 'price_display_min' ) );
$price_max    = floatval( tp_get_meta( $post_id, 'price_display_max' ) );
$rera         = tp_get_meta( $post_id, 'rera_number' );
$stage        = tp_get_meta( $post_id, 'construction_stage' );
$possession   = tp_get_meta( $post_id, 'expected_possession' );
$developer    = tp_get_meta( $post_id, 'developer_name' ) ?: get_the_title();
$gallery      = tp_get_gallery_urls( $post_id );
$thumbnail    = get_the_post_thumbnail_url( $post_id, 'large' );
$amenities    = tp_parse_json_meta( $post_id, 'highlights' );
$pros         = tp_parse_json_meta( $post_id, 'pros' );
$cons         = tp_parse_json_meta( $post_id, 'cons' );
$offers       = tp_parse_json_meta( $post_id, 'offers' );
$configs_text = tp_get_meta( $post_id, 'available_configs_text' );
$phone        = tp_get_meta( $post_id, 'phone' );
$wa_number    = $phone ? preg_replace( '/[^0-9]/', '', $phone ) : '919999999999';
$wa_msg       = rawurlencode( 'Hi, I am interested in ' . get_the_title() );

// ── Determine property category ──
$prop_terms    = wp_get_object_terms( $post_id, 'tp_property_type', array( 'fields' => 'slugs' ) );
$property_type = ! empty( $prop_terms ) && ! is_wp_error( $prop_terms ) ? $prop_terms[0] : 'buy';

// Category-specific meta.
$monthly_rent      = intval( tp_get_meta( $post_id, 'monthly_rent' ) );
$security_deposit  = intval( tp_get_meta( $post_id, 'security_deposit' ) );
$furnishing_status = tp_get_meta( $post_id, 'furnishing_status' );
$available_from    = tp_get_meta( $post_id, 'available_from' );
$commercial_type   = tp_get_meta( $post_id, 'commercial_type' );
$commercial_carpet = intval( tp_get_meta( $post_id, 'commercial_carpet' ) );
$price_per_sqft    = intval( tp_get_meta( $post_id, 'price_per_sqft' ) );
$building_grade    = tp_get_meta( $post_id, 'building_grade' );
$fitout_status     = tp_get_meta( $post_id, 'fitout_status' );
$plot_area         = intval( tp_get_meta( $post_id, 'plot_area' ) );
$plot_width        = tp_get_meta( $post_id, 'plot_width' );
$plot_depth        = tp_get_meta( $post_id, 'plot_depth' );
$corner_plot       = tp_get_meta( $post_id, 'corner_plot' );
$fsi               = tp_get_meta( $post_id, 'fsi' );
$pg_gender         = tp_get_meta( $post_id, 'pg_gender' );
$pg_single_rent    = intval( tp_get_meta( $post_id, 'pg_single_rent' ) );
$pg_meals          = tp_get_meta( $post_id, 'pg_meals' );
$pg_wifi           = tp_get_meta( $post_id, 'pg_wifi' );
$pg_ac             = tp_get_meta( $post_id, 'pg_ac' );

// CTA labels per category.
$cta_labels = array(
	'buy'        => array( 'primary' => 'Get Best Price', 'secondary' => 'Book Site Visit' ),
	'resale'     => array( 'primary' => 'Get Best Price', 'secondary' => 'Book Site Visit' ),
	'rent'       => array( 'primary' => 'Schedule Visit', 'secondary' => 'Check Availability' ),
	'commercial' => array( 'primary' => 'Get Quote', 'secondary' => 'Schedule Tour' ),
	'plot'       => array( 'primary' => 'Get Best Price', 'secondary' => 'Visit Site' ),
	'plots'      => array( 'primary' => 'Get Best Price', 'secondary' => 'Visit Site' ),
	'pg'         => array( 'primary' => 'Check Availability', 'secondary' => 'Book a Room' ),
);
$cta = isset( $cta_labels[ $property_type ] ) ? $cta_labels[ $property_type ] : $cta_labels['buy'];

// Build all images array.
$all_images  = array();
$banner_imgs = tp_get_banner_urls( $post_id, 'desktop' );
if ( $banner_imgs ) {
	$all_images = array_merge( $all_images, $banner_imgs );
}
if ( $gallery ) {
	$all_images = array_merge( $all_images, $gallery );
}
if ( empty( $all_images ) && $thumbnail ) {
	$all_images[] = $thumbnail;
}

// Section nav items — dynamic per category.
switch ( $property_type ) {
	case 'rent':
		$sections = array(
			'overview'    => 'Overview',
			'rent'        => 'Rent & Details',
			'amenities'   => 'Amenities',
			'floor-plans' => 'Floor Plans',
			'pros-cons'   => 'Pros & Cons',
			'location'    => 'Location',
			'developer'   => 'Developer',
		);
		break;

	case 'commercial':
		$sections = array(
			'overview'        => 'Overview',
			'specs'           => 'Price & Specs',
			'unit-plans'      => 'Unit Plans',
			'pros-cons'       => 'Pros & Cons',
			'location'        => 'Location',
			'emi-calculator'  => 'EMI Calculator',
			'developer'       => 'Developer',
			'faq'             => 'FAQ',
		);
		// Add amenities nav only if project has amenity data.
		$_am_terms = wp_get_object_terms( $post_id, 'tp_amenity', array( 'fields' => 'ids' ) );
		$_am_json  = tp_parse_json_meta( $post_id, 'highlights' );
		if ( ( ! is_wp_error( $_am_terms ) && ! empty( $_am_terms ) ) || ! empty( $_am_json ) ) {
			$sections = array_slice( $sections, 0, 2, true )
				+ array( 'amenities' => 'Amenities' )
				+ array_slice( $sections, 2, null, true );
		}
		break;

	case 'plot':
	case 'plots':
		$sections = array(
			'overview'    => 'Overview',
			'plot'        => 'Price & Details',
			'features'    => 'Features',
			'pros-cons'   => 'Pros & Cons',
			'location'    => 'Location',
			'developer'   => 'Developer',
			'faq'         => 'FAQ',
		);
		break;

	case 'pg':
		$sections = array(
			'overview'    => 'Overview',
			'rooms'       => 'Rooms & Pricing',
			'amenities'   => 'Amenities & Facilities',
			'rules'       => 'Rules',
			'location'    => 'Location',
			'faq'         => 'FAQ',
		);
		break;

	default: // buy, resale
		$sections = array(
			'overview'      => 'Overview',
			'price'         => 'Price & Configuration',
			'amenities'     => 'Amenities',
			'pros-cons'     => 'Pros & Cons',
			'location'      => 'Location',
			'virtual-tour'  => 'Virtual Tour',
			'developer'     => 'Developer',
			'faq'           => 'FAQ',
		);
		break;
}
?>

<div class="tp-container">

	<!-- Breadcrumbs -->
	<div class="tp-breadcrumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
		<span class="sep">›</span>
		<a href="<?php echo esc_url( home_url( '/navi-mumbai/' ) ); ?>">Navi Mumbai</a>
		<?php if ( $loc_name ) : ?>
			<span class="sep">›</span>
			<a href="<?php echo esc_url( home_url( '/navi-mumbai/' . $loc_slug . '/' ) ); ?>"><?php echo esc_html( $loc_name ); ?></a>
		<?php endif; ?>
		<span class="sep">›</span>
		<span><?php the_title(); ?></span>
	</div>

	<!-- Gallery -->
	<?php if ( ! empty( $all_images ) ) : ?>
		<?php get_template_part( 'template-parts/project/gallery', null, array( 'images' => $all_images ) ); ?>
	<?php endif; ?>

	<!-- Offers Strip -->
	<?php if ( ! empty( $offers ) ) : ?>
		<?php get_template_part( 'template-parts/project/offers-strip', null, array( 'offers' => $offers ) ); ?>
	<?php endif; ?>

	<!-- Title + Quick Stats -->
	<?php $dev_logo = tp_get_developer_logo( $post_id ); ?>
	<div class="tp-pdp-header">
		<div class="tp-pdp-header__left">
			<div class="tp-pdp-header__title-row">
				<?php if ( $dev_logo ) : ?>
					<img src="<?php echo esc_url( $dev_logo ); ?>" alt="<?php echo esc_attr( $developer ); ?>" class="tp-pdp-header__dev-logo">
				<?php endif; ?>
				<h1><?php the_title(); ?></h1>
			</div>
			<div class="tp-pdp-header__meta">
				<span class="tp-pdp-header__dev">by <?php echo esc_html( $developer ); ?></span>
				<?php if ( $loc_name ) : ?>
					<span class="tp-pdp-header__loc">
						<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
						<?php echo esc_html( $loc_name ); ?>, Navi Mumbai
					</span>
				<?php endif; ?>
			</div>
			<div class="tp-badges">
				<?php if ( $stage ) : ?>
					<span class="tp-badge tp-badge--accent"><?php echo esc_html( tp_format_stage( $stage ) ); ?></span>
				<?php endif; ?>
				<?php if ( $rera ) : ?>
					<span class="tp-badge tp-badge--success">RERA Verified</span>
				<?php endif; ?>
				<?php if ( $possession ) : ?>
					<span class="tp-badge tp-badge--primary">Possession: <?php echo esc_html( tp_format_possession( $possession ) ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( 'rent' === $property_type && $monthly_rent ) : ?>
			<div class="tp-pdp-header__price">
				<div class="tp-pdp-header__price-label">Rent</div>
				<div class="tp-pdp-header__price-value">₹<?php echo number_format( $monthly_rent ); ?>/mo</div>
				<?php if ( $security_deposit ) : ?>
					<div class="tp-pdp-header__emi">Deposit: ₹<?php echo number_format( $security_deposit ); ?></div>
				<?php endif; ?>
			</div>
		<?php elseif ( 'commercial' === $property_type && $price_min ) : ?>
			<div class="tp-pdp-header__price">
				<div class="tp-pdp-header__price-label">Price Starts From</div>
				<div class="tp-pdp-header__price-value"><?php echo esc_html( tp_format_price( $price_min ) ); ?></div>
				<?php if ( $price_per_sqft ) : ?>
					<div class="tp-pdp-header__emi">₹<?php echo number_format( $price_per_sqft ); ?>/sqft</div>
				<?php endif; ?>
			</div>
		<?php elseif ( 'pg' === $property_type && $pg_single_rent ) : ?>
			<div class="tp-pdp-header__price">
				<div class="tp-pdp-header__price-label">Starting from</div>
				<div class="tp-pdp-header__price-value">₹<?php echo number_format( $pg_single_rent ); ?>/bed/mo</div>
			</div>
		<?php elseif ( $price_min ) : ?>
			<div class="tp-pdp-header__price">
				<div class="tp-pdp-header__price-label">Starting from</div>
				<div class="tp-pdp-header__price-value"><?php echo esc_html( tp_format_price( $price_min ) ); ?></div>
			</div>
		<?php endif; ?>
	</div>

	<!-- Quick Highlights Strip — category-specific -->
	<div class="tp-highlights-strip">
		<?php if ( 'rent' === $property_type ) : ?>
			<?php if ( $configs_text ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
					<div><span class="label">Config</span><span class="value"><?php echo esc_html( $configs_text ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $monthly_rent ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<div><span class="label">Rent</span><span class="value">₹<?php echo number_format( $monthly_rent ); ?>/mo</span></div>
				</div>
			<?php endif; ?>
			<?php if ( $furnishing_status ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
					<div><span class="label">Furnishing</span><span class="value"><?php echo esc_html( $furnishing_status ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $available_from ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
					<div><span class="label">Available From</span><span class="value"><?php echo esc_html( $available_from ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $security_deposit ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
					<div><span class="label">Deposit</span><span class="value">₹<?php echo number_format( $security_deposit ); ?></span></div>
				</div>
			<?php endif; ?>

		<?php elseif ( 'commercial' === $property_type ) : ?>
			<?php if ( $commercial_type ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
					<div><span class="label">Type</span><span class="value"><?php echo esc_html( $commercial_type ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $commercial_carpet ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
					<div><span class="label">Area</span><span class="value"><?php echo number_format( $commercial_carpet ); ?> sqft</span></div>
				</div>
			<?php endif; ?>
			<?php if ( $price_per_sqft ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<div><span class="label">Price</span><span class="value">₹<?php echo number_format( $price_per_sqft ); ?>/sqft</span></div>
				</div>
			<?php endif; ?>
			<?php if ( $building_grade ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<div><span class="label">Grade</span><span class="value"><?php echo esc_html( $building_grade ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $fitout_status ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
					<div><span class="label">Fit-out</span><span class="value"><?php echo esc_html( $fitout_status ); ?></span></div>
				</div>
			<?php endif; ?>

		<?php elseif ( 'plot' === $property_type || 'plots' === $property_type ) : ?>
			<?php if ( $plot_area ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
					<div><span class="label">Plot Area</span><span class="value"><?php echo number_format( $plot_area ); ?> sqft</span></div>
				</div>
			<?php endif; ?>
			<?php if ( $plot_width && $plot_depth ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
					<div><span class="label">Dimensions</span><span class="value"><?php echo esc_html( $plot_width . ' × ' . $plot_depth . ' ft' ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $price_min ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<div><span class="label">Price</span><span class="value"><?php echo esc_html( tp_format_price( $price_min ) ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $corner_plot ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<div><span class="label">Corner Plot</span><span class="value"><?php echo esc_html( $corner_plot ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $fsi ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
					<div><span class="label">FSI</span><span class="value"><?php echo esc_html( $fsi ); ?></span></div>
				</div>
			<?php endif; ?>

		<?php elseif ( 'pg' === $property_type ) : ?>
			<?php if ( $pg_gender ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
					<div><span class="label">For</span><span class="value"><?php echo esc_html( $pg_gender ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $pg_single_rent ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<div><span class="label">From</span><span class="value">₹<?php echo number_format( $pg_single_rent ); ?>/mo</span></div>
				</div>
			<?php endif; ?>
			<?php if ( $pg_meals ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
					<div><span class="label">Meals</span><span class="value"><?php echo esc_html( $pg_meals ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $pg_wifi ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.858 15.355-5.858 21.213 0"/></svg>
					<div><span class="label">Wi-Fi</span><span class="value"><?php echo esc_html( $pg_wifi ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $pg_ac ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
					<div><span class="label">AC</span><span class="value"><?php echo esc_html( $pg_ac ); ?></span></div>
				</div>
			<?php endif; ?>

		<?php else : /* Buy / Resale — original strip */ ?>
			<?php if ( $configs_text ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
					<div><span class="label">Configurations</span><span class="value"><?php echo esc_html( $configs_text ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $price_min ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<div><span class="label">Price Range</span><span class="value"><?php echo esc_html( tp_format_price_range( $price_min, $price_max ) ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $stage ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<div><span class="label">Status</span><span class="value"><?php echo esc_html( tp_format_stage( $stage ) ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $possession ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
					<div><span class="label">Possession</span><span class="value"><?php echo esc_html( tp_format_possession( $possession ) ); ?></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $rera ) : ?>
				<div class="tp-highlights-strip__item">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
					<div><span class="label">RERA</span><span class="value"><?php echo esc_html( $rera ); ?></span></div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

</div><!-- /.tp-container (header portion) -->

<!-- Section Nav (sticky) -->
<div class="tp-section-nav" id="sectionNav">
	<div class="tp-container">
		<div class="tp-section-nav__inner">
			<?php foreach ( $sections as $id => $label ) : ?>
				<a href="#<?php echo esc_attr( $id ); ?>" class="tp-section-nav__link" data-section="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<div class="tp-container">

	<!-- Mobile Sidebar (visible only on mobile) -->
	<div class="tp-mobile-sidebar">
		<?php get_template_part( 'template-parts/project/sidebar', null, array(
			'price_min'      => $price_min,
			'price_max'      => $price_max,
			'post_id'        => $post_id,
			'offers'         => $offers,
			'property_type'  => $property_type,
			'cta'            => $cta,
		) ); ?>
	</div>

	<!-- Two-Column Layout -->
	<div class="tp-two-col">
		<!-- Main Content — category-aware -->
		<div class="tp-main">
			<?php get_template_part( 'template-parts/project/overview', null, array( 'post_id' => $post_id, 'property_type' => $property_type ) ); ?>

			<?php if ( 'rent' === $property_type ) : ?>
				<?php get_template_part( 'template-parts/project/rental-details', null, array( 'post_id' => $post_id ) ); ?>
				<?php get_template_part( 'template-parts/project/amenities', null, array( 'post_id' => $post_id ) ); ?>
				<?php get_template_part( 'template-parts/project/floor-plans', null, array( 'post_id' => $post_id ) ); ?>

			<?php elseif ( 'commercial' === $property_type ) : ?>
				<?php get_template_part( 'template-parts/project/commercial-specs', null, array( 'post_id' => $post_id ) ); ?>
				<?php get_template_part( 'template-parts/project/amenities', null, array( 'post_id' => $post_id ) ); ?>
				<?php get_template_part( 'template-parts/project/floor-plans', null, array( 'post_id' => $post_id, 'label' => 'Unit Plans', 'property_type' => 'commercial' ) ); ?>

			<?php elseif ( 'plot' === $property_type || 'plots' === $property_type ) : ?>
				<?php get_template_part( 'template-parts/project/plot-details', null, array( 'post_id' => $post_id ) ); ?>

			<?php elseif ( 'pg' === $property_type ) : ?>
				<?php get_template_part( 'template-parts/project/pg-rooms', null, array( 'post_id' => $post_id ) ); ?>
				<?php get_template_part( 'template-parts/project/amenities', null, array( 'post_id' => $post_id ) ); ?>
				<?php get_template_part( 'template-parts/project/pg-rules', null, array( 'post_id' => $post_id ) ); ?>

			<?php else : /* Buy / Resale */ ?>
				<?php get_template_part( 'template-parts/project/floor-plans', null, array( 'post_id' => $post_id ) ); ?>
				<?php get_template_part( 'template-parts/project/amenities', null, array( 'post_id' => $post_id ) ); ?>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/project/pros-cons', null, array( 'pros' => $pros, 'cons' => $cons ) ); ?>
			<?php get_template_part( 'template-parts/project/location', null, array( 'post_id' => $post_id ) ); ?>
			<?php get_template_part( 'template-parts/project/virtual-tour', null, array( 'post_id' => $post_id, 'property_type' => $property_type ) ); ?>

			<!-- Mid-page CTA -->
			<div class="tp-mid-cta">
				<div class="tp-mid-cta__content">
					<h3>Interested in <?php the_title(); ?>?</h3>
					<p>Get the best deal with zero brokerage. Free site visit with cab pickup.</p>
				</div>
				<div class="tp-mid-cta__actions">
					<button type="button" class="tp-btn tp-btn--whatsapp js-open-lead-popup" data-source="mid_cta_whatsapp">
						<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
						WhatsApp Us
					</button>
					<button type="button" class="tp-btn tp-btn--primary js-open-lead-popup" data-source="mid_cta_best_price"><?php echo esc_html( $cta['primary'] ); ?></button>
				</div>
			</div>

			<?php if ( in_array( $property_type, array( 'buy', 'resale', 'commercial' ), true ) ) : ?>
				<?php get_template_part( 'template-parts/project/emi-calculator', null, array( 'post_id' => $post_id ) ); ?>

				<!-- Loan Eligibility Banner -->
				<div class="tp-loan-banner">
					<div class="tp-loan-banner__icon">
						<svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
							<path d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
						</svg>
					</div>
					<div class="tp-loan-banner__content">
						<div class="tp-loan-banner__title"><?php echo 'commercial' === $property_type ? 'Check Your Commercial Loan Eligibility' : 'Check Your Home Loan Eligibility'; ?></div>
						<div class="tp-loan-banner__text">Know how much loan you can get from top banks. Free, instant &amp; no impact on credit score.</div>
					</div>
					<button type="button" class="tp-loan-banner__btn js-open-lead-popup" data-source="loan_eligibility">
						Check Now
						<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
					</button>
				</div>
			<?php endif; ?>

			<?php if ( 'pg' !== $property_type ) : ?>
				<?php get_template_part( 'template-parts/project/developer', null, array( 'post_id' => $post_id ) ); ?>
			<?php endif; ?>
			<?php if ( 'rent' !== $property_type ) : ?>
				<?php get_template_part( 'template-parts/project/faq', null, array( 'post_id' => $post_id, 'property_type' => $property_type ) ); ?>
			<?php endif; ?>
			<?php get_template_part( 'template-parts/project/why-us' ); ?>
			<?php get_template_part( 'template-parts/project/similar', null, array( 'post_id' => $post_id ) ); ?>
		</div>

		<!-- Desktop Sidebar -->
		<div class="tp-sidebar">
			<?php get_template_part( 'template-parts/project/sidebar', null, array(
				'price_min'  => $price_min,
				'price_max'  => $price_max,
				'post_id'    => $post_id,
				'offers'     => $offers,
			) ); ?>
		</div>
	</div>
</div>

<!-- Mobile Sticky Bar -->
<div class="tp-mobile-bar">
	<button type="button" class="tp-btn tp-btn--whatsapp js-open-lead-popup" data-source="mobile_whatsapp">
		<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
		WhatsApp
	</button>
	<button type="button" class="tp-btn tp-btn--primary js-open-lead-popup" data-source="mobile_best_price"><?php echo esc_html( $cta['primary'] ); ?></button>
	<button type="button" class="tp-btn tp-btn--outline js-open-lead-popup" data-source="mobile_site_visit"><?php echo esc_html( $cta['secondary'] ); ?></button>
</div>

<!-- Lead Popup Overlay — Premium -->
<div class="tp-lead-popup" id="tp-lead-popup">
	<div class="tp-lead-popup__backdrop" id="tp-lead-popup-close-bg"></div>
	<div class="tp-lead-popup__card">
		<button type="button" class="tp-lead-popup__close" id="tp-lead-popup-close" aria-label="Close">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>

		<!-- Gradient Visual Header -->
		<div class="tp-popup__visual">
			<div class="tp-popup__visual-icon">
				<svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
			</div>
			<h3 class="tp-popup__title">Get the Best Deal</h3>
			<p class="tp-popup__project"><?php the_title(); ?></p>
		</div>

		<!-- Benefits -->
		<div class="tp-popup__benefits">
			<div class="tp-popup__benefit">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
				<span>Free site visit with cab pickup</span>
			</div>
			<div class="tp-popup__benefit">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
				<span>Exclusive pre-launch & festive offers</span>
			</div>
			<div class="tp-popup__benefit">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
				<span>Zero brokerage, guaranteed lowest price</span>
			</div>
		</div>

		<!-- Form -->
		<form class="tp-popup__form" id="tp-lead-popup-form" novalidate>
			<input type="hidden" name="action" value="tp_sidebar_lead">
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'tp_sidebar_lead_nonce' ); ?>">
			<input type="hidden" name="project_id" value="<?php echo esc_attr( $post_id ); ?>">
			<input type="hidden" name="project_name" value="<?php echo esc_attr( get_the_title() ); ?>">
			<input type="hidden" name="page_url" value="<?php echo esc_url( get_permalink() ); ?>">
			<input type="hidden" name="lead_source" id="tp-lead-popup-source" value="popup">

			<div class="tp-lf__field">
				<span class="tp-lf__field-icon">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
				</span>
				<input type="text" name="name" placeholder="Your Name" required autocomplete="name" class="tp-lead-form__input tp-lf__input">
			</div>

			<div class="tp-lf__field">
				<span class="tp-lf__field-icon tp-lf__field-icon--prefix">+91</span>
				<input type="tel" name="phone" placeholder="Mobile Number" required pattern="[0-9]{10}" maxlength="10" inputmode="numeric" autocomplete="tel" class="tp-lead-form__input tp-lf__input tp-lf__input--phone">
			</div>

			<button type="submit" class="tp-lf__submit tp-lead-popup__submit">
				<span class="tp-lf__submit-text">Get Best Price</span>
				<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</button>

			<div class="tp-lead-popup__status" id="tp-lead-popup-status"></div>
		</form>

		<!-- Trust Footer -->
		<div class="tp-popup__trust-footer">
			<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
			<span>Your data is safe. No spam calls, guaranteed.</span>
		</div>
	</div>
</div>

<script>
(function(){
	var popup = document.getElementById('tp-lead-popup');
	var form = document.getElementById('tp-lead-popup-form');
	var sourceInput = document.getElementById('tp-lead-popup-source');
	if (!popup || !form) return;

	/* UTM & device tracking */
	function getTrackingData() {
		var params = new URLSearchParams(window.location.search);
		return {
			utm_source:   params.get('utm_source') || '',
			utm_medium:   params.get('utm_medium') || '',
			utm_campaign: params.get('utm_campaign') || '',
			utm_term:     params.get('utm_term') || '',
			utm_content:  params.get('utm_content') || '',
			referrer:     document.referrer || '',
			device:       /Mobi|Android/i.test(navigator.userAgent) ? 'mobile' : 'desktop',
			browser: (function() {
				var ua = navigator.userAgent;
				if (ua.indexOf('Chrome') > -1 && ua.indexOf('Edg') === -1) return 'Chrome';
				if (ua.indexOf('Safari') > -1 && ua.indexOf('Chrome') === -1) return 'Safari';
				if (ua.indexOf('Firefox') > -1) return 'Firefox';
				if (ua.indexOf('Edg') > -1) return 'Edge';
				return 'Other';
			})(),
			os: (function() {
				var ua = navigator.userAgent;
				if (/iPhone|iPad|iPod/.test(ua)) return 'iOS';
				if (/Android/.test(ua)) return 'Android';
				if (/Windows/.test(ua)) return 'Windows';
				if (/Mac/.test(ua)) return 'macOS';
				if (/Linux/.test(ua)) return 'Linux';
				return 'Other';
			})(),
			screen_width:  screen.width,
			screen_height: screen.height,
			page_url:      window.location.href
		};
	}
	function appendTracking(formData) {
		var t = getTrackingData();
		for (var k in t) { if (t[k]) formData.append(k, t[k]); }
	}

	/* Open */
	document.querySelectorAll('.js-open-lead-popup').forEach(function(btn){
		btn.addEventListener('click', function(){
			sourceInput.value = btn.getAttribute('data-source') || 'popup';
			popup.classList.add('is-open');
			document.body.style.overflow = 'hidden';
			setTimeout(function(){ form.querySelector('input[name="name"]').focus(); }, 300);
		});
	});

	/* Close */
	function closePopup(){
		popup.classList.remove('is-open');
		document.body.style.overflow = '';
	}
	document.getElementById('tp-lead-popup-close').addEventListener('click', closePopup);
	document.getElementById('tp-lead-popup-close-bg').addEventListener('click', closePopup);
	document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closePopup(); });

	/* Submit */
	var thankYouUrl = '<?php echo esc_url( home_url( "/thank-you/" ) ); ?>';
	var projectName = <?php echo wp_json_encode( get_the_title() ); ?>;

	form.addEventListener('submit', function(e){
		e.preventDefault();
		var btn = form.querySelector('.tp-lead-popup__submit');
		var status = document.getElementById('tp-lead-popup-status');
		var nameInput = form.querySelector('input[name="name"]');
		var phoneInput = form.querySelector('input[name="phone"]');
		var name = nameInput.value.trim();
		var phone = phoneInput.value.replace(/\D/g,'');

		if (!name) {
			nameInput.closest('.tp-lf__field').classList.add('is-error');
			status.className = 'tp-lead-popup__status is-error';
			status.textContent = 'Please enter your name';
			return;
		}
		if (phone.length < 10) {
			phoneInput.closest('.tp-lf__field').classList.add('is-error');
			status.className = 'tp-lead-popup__status is-error';
			status.textContent = 'Please enter a valid 10-digit number';
			return;
		}

		btn.disabled = true;
		btn.querySelector('.tp-lf__submit-text').textContent = 'Submitting...';

		var data = new FormData(form);
		appendTracking(data);

		var tyRedirect = thankYouUrl + '?name=' + encodeURIComponent(name) + '&project=' + encodeURIComponent(projectName);

		fetch('<?php echo esc_url( admin_url("admin-ajax.php") ); ?>', { method: 'POST', body: data })
			.then(function(r){ return r.json(); })
			.then(function(){
				window.location.href = tyRedirect;
			})
			.catch(function(){
				/* Redirect anyway — lead is likely saved */
				window.location.href = tyRedirect;
			});
	});

	form.querySelectorAll('.tp-lf__input').forEach(function(inp){
		inp.addEventListener('input', function(){
			var field = inp.closest('.tp-lf__field');
			if (field) field.classList.remove('is-error');
			var st = document.getElementById('tp-lead-popup-status');
			if (st && st.classList.contains('is-error')) { st.textContent = ''; st.className = 'tp-lead-popup__status'; }
		});
	});
})();
</script>

<?php get_template_part( 'template-parts/project/brochure-modal', null, array( 'post_id' => $post_id ) ); ?>

<script>
/* Track project view in localStorage for homepage returning-user personalization */
(function () {
	try {
		var pid  = <?php echo (int) get_the_ID(); ?>;
		var type = '<?php
			$_tp_types = wp_get_object_terms( get_the_ID(), 'tp_property_type' );
			echo ( $_tp_types && ! is_wp_error( $_tp_types ) ) ? esc_js( $_tp_types[0]->slug ) : '';
		?>';
		var loc  = '<?php echo esc_js( $loc_slug ); ?>';
		var d    = JSON.parse( localStorage.getItem('tp_viewed') || '{"projects":[],"types":{},"locations":{}}' );
		if ( !d.projects.includes(pid) ) d.projects.unshift(pid);
		if ( type ) d.types[type]    = (d.types[type]    || 0) + 1;
		if ( loc )  d.locations[loc] = (d.locations[loc] || 0) + 1;
		d.last_visit = new Date().toISOString();
		d.projects   = d.projects.slice(0, 50);
		localStorage.setItem('tp_viewed', JSON.stringify(d));
	} catch (e) {}
})();
</script>
<?php get_footer(); ?>
