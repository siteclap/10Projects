<?php
/**
 * Project Gallery — desktop grid + mobile swiper + lightbox
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$images = $args['images'] ?? array();
if ( empty( $images ) ) return;

$total = count( $images );
?>

<!-- Mobile Gallery -->
<div class="tp-gallery-mobile">
	<div class="tp-gallery-track" id="galleryTrack">
		<?php foreach ( $images as $img ) : ?>
			<div class="tp-gallery-slide">
				<img src="<?php echo esc_url( $img ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
			</div>
		<?php endforeach; ?>
	</div>
	<div class="tp-gallery-counter">
		<span id="galleryIndex">1</span> / <?php echo $total; ?>
	</div>
</div>

<!-- Desktop Gallery -->
<div class="tp-gallery tp-gallery-desktop">
	<?php if ( isset( $images[0] ) ) : ?>
		<div class="tp-gallery-main" onclick="openLightbox(0)">
			<img src="<?php echo esc_url( $images[0] ); ?>" alt="<?php the_title_attribute(); ?>">
		</div>
	<?php endif; ?>

	<?php for ( $i = 1; $i < min( 5, $total ); $i++ ) : ?>
		<div class="tp-gallery-thumb" onclick="openLightbox(<?php echo $i; ?>)">
			<img src="<?php echo esc_url( $images[ $i ] ); ?>" alt="<?php the_title_attribute(); ?> - Image <?php echo $i + 1; ?>" loading="lazy">
			<?php if ( $i === 4 && $total > 5 ) : ?>
				<div class="tp-gallery-more">+<?php echo $total - 5; ?> more</div>
			<?php endif; ?>
		</div>
	<?php endfor; ?>
</div>

<!-- Lightbox -->
<div class="tp-lightbox" id="lightbox">
	<button class="tp-lightbox-close" onclick="closeLightbox()">&times;</button>
	<button class="tp-lightbox-nav tp-lightbox-prev" onclick="navLightbox(-1)">‹</button>
	<img id="lightboxImg" src="" alt="">
	<button class="tp-lightbox-nav tp-lightbox-next" onclick="navLightbox(1)">›</button>
	<div class="tp-lightbox-counter">
		<span id="lightboxIndex">1</span> / <?php echo $total; ?>
	</div>
</div>

<script>
	window.galleryImages = <?php echo wp_json_encode( $images ); ?>;
</script>
