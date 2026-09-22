<?php
/**
 * Virtual Site Visit Section — Lead-gen with two video cards
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id       = $args['post_id'] ?? get_the_ID();
$property_type = $args['property_type'] ?? 'buy';
$tour_label    = ( 'commercial' === $property_type ) ? 'Sample Office Tour' : 'Sample Flat Tour';

// Use banner images as thumbnails, fallback to default media IDs.
$thumbs = array();
$banners = tp_get_banner_urls( $post_id, 'desktop' );
if ( ! empty( $banners ) ) {
	$thumbs = array_slice( $banners, 0, 2 );
}
if ( count( $thumbs ) < 2 ) {
	$_vt1 = wp_get_attachment_image_url( 188, 'full' );
	$_vt2 = wp_get_attachment_image_url( 189, 'full' );
	if ( count( $thumbs ) < 1 && $_vt1 ) { $thumbs[] = $_vt1; }
	if ( count( $thumbs ) < 2 && $_vt2 ) { $thumbs[] = $_vt2; }
	if ( count( $thumbs ) < 2 && $_vt1 ) { $thumbs[] = $_vt1; }
}

if ( empty( $thumbs ) ) return;
?>

<section class="tp-section" id="virtual-tour">
	<h2><?php the_title(); ?> Virtual Site Visit</h2>

	<div class="tp-tour-grid">
		<div class="tp-tour-card js-open-lead-popup" data-source="virtual_tour_sample_flat">
			<div class="tp-tour-card__label"><?php echo esc_html( $tour_label ); ?></div>
			<div class="tp-tour-card__media">
				<img class="tp-tour-card__thumb" src="<?php echo esc_url( $thumbs[0] ); ?>" alt="<?php echo esc_attr( $tour_label ); ?> — <?php the_title_attribute(); ?>" loading="lazy">
				<div class="tp-tour-card__play">
					<svg viewBox="0 0 68 48"><path d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55C3.97 2.33 2.27 4.81 1.48 7.74.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z" fill="red"/><path d="M45 24L27 14v20" fill="#fff"/></svg>
				</div>
			</div>
		</div>
		<div class="tp-tour-card js-open-lead-popup" data-source="virtual_tour_drone">
			<div class="tp-tour-card__label">360° Drone Video</div>
			<div class="tp-tour-card__media">
				<img class="tp-tour-card__thumb" src="<?php echo esc_url( isset( $thumbs[1] ) ? $thumbs[1] : $thumbs[0] ); ?>" alt="360° Drone Video — <?php the_title_attribute(); ?>" loading="lazy">
				<div class="tp-tour-card__play">
					<svg viewBox="0 0 68 48"><path d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55C3.97 2.33 2.27 4.81 1.48 7.74.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z" fill="red"/><path d="M45 24L27 14v20" fill="#fff"/></svg>
				</div>
			</div>
		</div>
	</div>
</section>
