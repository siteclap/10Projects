<?php
/**
 * Project Gallery — Full-width carousel with swipe + arrows + lightbox
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$images = $args['images'] ?? array();
if ( empty( $images ) ) return;

$total = count( $images );
?>

<!-- Gallery Carousel -->
<div class="tp-gallery-carousel" id="galleryCarousel">
	<div class="tp-gallery-carousel__track" id="galleryTrack">
		<?php foreach ( $images as $i => $img ) : ?>
			<div class="tp-gallery-carousel__slide">
				<img src="<?php echo esc_url( $img ); ?>" alt="<?php the_title_attribute(); ?><?php echo $i > 0 ? ' - Image ' . ( $i + 1 ) : ''; ?>" decoding="async">
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( $total > 1 ) : ?>
		<button class="tp-gallery-carousel__arrow tp-gallery-carousel__arrow--left" id="galleryPrev" aria-label="Previous">
			<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
		</button>
		<button class="tp-gallery-carousel__arrow tp-gallery-carousel__arrow--right" id="galleryNext" aria-label="Next">
			<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
		</button>
		<div class="tp-gallery-carousel__counter" id="galleryCounter">1 / <?php echo $total; ?></div>
	<?php endif; ?>
</div>

<!-- Lightbox -->
<div class="tp-lightbox" id="lightbox">
	<button class="tp-lightbox-close" onclick="closeLightbox()">&times;</button>
	<button class="tp-lightbox-nav tp-lightbox-prev" onclick="navLightbox(-1)">&#8249;</button>
	<img id="lightboxImg" src="" alt="">
	<button class="tp-lightbox-nav tp-lightbox-next" onclick="navLightbox(1)">&#8250;</button>
	<div class="tp-lightbox-counter">
		<span id="lightboxIndex">1</span> / <?php echo $total; ?>
	</div>
</div>

<script>
window.galleryImages = <?php echo wp_json_encode( $images ); ?>;

(function() {
	var track = document.getElementById('galleryTrack');
	var total = <?php echo $total; ?>;
	var current = 0;

	// Slide click → open lightbox
	var slides = track.querySelectorAll('.tp-gallery-carousel__slide');
	for (var s = 0; s < slides.length; s++) {
		(function(idx) {
			slides[idx].addEventListener('click', function() { openLightbox(idx); });
		})(s);
	}

	if (total <= 1) return;

	var prevBtn = document.getElementById('galleryPrev');
	var nextBtn = document.getElementById('galleryNext');
	var counter = document.getElementById('galleryCounter');
	var isDesktop = window.innerWidth >= 768;

	if (isDesktop) {
		// Desktop: horizontal scroll strip (multiple images visible)
		function getScrollAmount() {
			var item = track.querySelector('.tp-gallery-carousel__slide');
			return item ? item.offsetWidth + 8 : 300;
		}

		prevBtn.addEventListener('click', function(e) {
			e.stopPropagation();
			track.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
		});
		nextBtn.addEventListener('click', function(e) {
			e.stopPropagation();
			track.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
		});

		function updateArrows() {
			var atStart = track.scrollLeft <= 5;
			var atEnd = track.scrollLeft >= track.scrollWidth - track.clientWidth - 5;
			prevBtn.style.opacity = atStart ? '0' : '1';
			prevBtn.style.pointerEvents = atStart ? 'none' : 'auto';
			nextBtn.style.opacity = atEnd ? '0' : '1';
			nextBtn.style.pointerEvents = atEnd ? 'none' : 'auto';
		}

		track.addEventListener('scroll', updateArrows);
		updateArrows();
	} else {
		// Mobile: JS transform carousel (one image at a time)
		function goTo(n) {
			if (n < 0) n = 0;
			if (n >= total) n = total - 1;
			current = n;
			track.style.transform = 'translateX(-' + (current * 100) + '%)';
			prevBtn.style.opacity = current === 0 ? '0' : '1';
			prevBtn.style.pointerEvents = current === 0 ? 'none' : 'auto';
			nextBtn.style.opacity = current === total - 1 ? '0' : '1';
			nextBtn.style.pointerEvents = current === total - 1 ? 'none' : 'auto';
			counter.textContent = (current + 1) + ' / ' + total;
		}

		prevBtn.addEventListener('click', function(e) { e.stopPropagation(); goTo(current - 1); });
		nextBtn.addEventListener('click', function(e) { e.stopPropagation(); goTo(current + 1); });

		// Touch/swipe
		var startX = 0, moveX = 0, dragging = false;

		track.addEventListener('touchstart', function(e) {
			startX = e.touches[0].clientX;
			moveX = 0;
			dragging = true;
			track.style.transition = 'none';
		}, { passive: true });

		track.addEventListener('touchmove', function(e) {
			if (!dragging) return;
			moveX = e.touches[0].clientX - startX;
			var pct = -(current * 100) + (moveX / track.parentElement.offsetWidth * 100);
			track.style.transform = 'translateX(' + pct + '%)';
		}, { passive: true });

		track.addEventListener('touchend', function() {
			if (!dragging) return;
			dragging = false;
			track.style.transition = 'transform 0.3s ease';
			if (moveX > 50) goTo(current - 1);
			else if (moveX < -50) goTo(current + 1);
			else goTo(current);
		});

		goTo(0);
	}
})();
</script>
