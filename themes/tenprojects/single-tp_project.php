<?php
/**
 * Single Project Detail Page (PDP)
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

get_header();

$post_id      = get_the_ID();
$location     = tp_get_location_term( $post_id );
$loc_name     = $location ? $location->name : '';
$loc_slug     = $location ? $location->slug : '';
$price_min    = intval( tp_get_meta( $post_id, 'price_display_min' ) );
$price_max    = intval( tp_get_meta( $post_id, 'price_display_max' ) );
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

// Build all images array (banners + gallery + thumbnail).
$all_images = array();
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

	<!-- Title Section -->
	<div class="tp-title-section">
		<div>
			<h1><?php the_title(); ?></h1>
			<div class="tp-title-sub">
				by <?php echo esc_html( $developer ); ?>
				<?php if ( $loc_name ) : ?>
					&middot; <?php echo esc_html( $loc_name ); ?>, Navi Mumbai
				<?php endif; ?>
			</div>
			<div class="tp-badges">
				<?php if ( $stage ) : ?>
					<span class="tp-badge tp-badge--accent"><?php echo esc_html( $stage ); ?></span>
				<?php endif; ?>
				<?php if ( $rera ) : ?>
					<span class="tp-badge tp-badge--success">RERA: <?php echo esc_html( $rera ); ?></span>
				<?php endif; ?>
				<?php if ( $possession ) : ?>
					<span class="tp-badge tp-badge--primary">Possession: <?php echo esc_html( $possession ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Mobile Sidebar (visible only on mobile) -->
	<div class="tp-mobile-sidebar">
		<?php get_template_part( 'template-parts/project/sidebar', null, array(
			'price_min'  => $price_min,
			'price_max'  => $price_max,
			'post_id'    => $post_id,
		) ); ?>
	</div>

	<!-- Two-Column Layout -->
	<div class="tp-two-col">
		<!-- Main Content -->
		<div class="tp-main">
			<?php get_template_part( 'template-parts/project/overview', null, array( 'post_id' => $post_id ) ); ?>
			<?php get_template_part( 'template-parts/project/price-config', null, array( 'post_id' => $post_id ) ); ?>
			<?php get_template_part( 'template-parts/project/pros-cons', null, array( 'pros' => $pros, 'cons' => $cons ) ); ?>
			<?php get_template_part( 'template-parts/project/amenities', null, array( 'post_id' => $post_id ) ); ?>
			<?php get_template_part( 'template-parts/project/floor-plans', null, array( 'post_id' => $post_id ) ); ?>
			<?php get_template_part( 'template-parts/project/location', null, array( 'post_id' => $post_id ) ); ?>
			<?php get_template_part( 'template-parts/project/developer', null, array( 'post_id' => $post_id ) ); ?>
			<?php get_template_part( 'template-parts/project/faq', null, array( 'post_id' => $post_id ) ); ?>
			<?php get_template_part( 'template-parts/project/why-us' ); ?>
			<?php get_template_part( 'template-parts/project/similar', null, array( 'post_id' => $post_id ) ); ?>
		</div>

		<!-- Desktop Sidebar -->
		<div class="tp-sidebar">
			<?php get_template_part( 'template-parts/project/sidebar', null, array(
				'price_min'  => $price_min,
				'price_max'  => $price_max,
				'post_id'    => $post_id,
			) ); ?>
		</div>
	</div>
</div>

<!-- Mobile Sticky Bar -->
<div class="tp-mobile-bar">
	<a href="https://wa.me/919999999999?text=<?php echo rawurlencode( 'Hi, I am interested in ' . get_the_title() ); ?>" class="tp-btn tp-btn--whatsapp" target="_blank" rel="noopener">
		<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
		WhatsApp
	</a>
	<a href="#" class="tp-btn tp-btn--primary">Best Price</a>
	<a href="#" class="tp-btn tp-btn--outline">Site Visit</a>
</div>

<?php get_footer(); ?>
