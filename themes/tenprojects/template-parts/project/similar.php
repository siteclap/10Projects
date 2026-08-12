<?php
/**
 * Similar Projects Section — Horizontal scrollable carousel
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id = $args['post_id'] ?? get_the_ID();
$similar = tp_get_similar_projects( $post_id, 6 );

if ( empty( $similar ) ) return;
?>

<section class="tp-section">
	<div class="tp-scroll-header">
		<h2>Similar Projects Nearby</h2>
		<div class="tp-scroll-nav">
			<a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>" class="tp-scroll-header__link">View All →</a>
			<button class="tp-scroll-arrow" aria-label="Scroll left" onclick="similarScroll(-1)">
				<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
			</button>
			<button class="tp-scroll-arrow" aria-label="Scroll right" onclick="similarScroll(1)">
				<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
			</button>
		</div>
	</div>

	<div class="tp-hscroll" id="similarScroll">
		<?php foreach ( $similar as $p ) :
			$p_id       = $p->ID;
			$p_thumb    = get_the_post_thumbnail_url( $p_id, 'medium' );
			$p_dev      = tp_get_meta( $p_id, 'developer_name' ) ?: '';
			$p_loc      = tp_get_location_term( $p_id );
			$p_loc_name = $p_loc ? $p_loc->name : '';
			$p_min      = intval( tp_get_meta( $p_id, 'price_display_min' ) );
			$p_max      = intval( tp_get_meta( $p_id, 'price_display_max' ) );
			$p_configs  = tp_get_meta( $p_id, 'available_configs_text' );
		?>
			<a href="<?php echo esc_url( get_permalink( $p_id ) ); ?>" class="tp-project-card tp-hscroll__card">
				<div class="tp-project-card__img">
					<?php if ( $p_thumb ) : ?>
						<img src="<?php echo esc_url( $p_thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $p_id ) ); ?>" loading="lazy">
					<?php else : ?>
						<div style="width:100%;height:100%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--gray-400);">
							<svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
						</div>
					<?php endif; ?>
					<?php if ( $p_dev ) : ?>
						<span class="tp-project-card__dev-badge"><?php echo esc_html( $p_dev ); ?></span>
					<?php endif; ?>
				</div>
				<div class="tp-project-card__body">
					<div class="tp-project-card__title"><?php echo esc_html( get_the_title( $p_id ) ); ?></div>
					<div class="tp-project-card__location"><?php echo esc_html( $p_loc_name ); ?></div>
					<?php if ( $p_min ) : ?>
						<div class="tp-project-card__price"><?php echo esc_html( tp_format_price_range( $p_min, $p_max ) ); ?></div>
					<?php endif; ?>
					<?php if ( $p_configs ) : ?>
						<div class="tp-project-card__meta"><?php echo esc_html( $p_configs ); ?></div>
					<?php endif; ?>
				</div>
			</a>
		<?php endforeach; ?>
	</div>

	<script>
	(function(){
		var sc = document.getElementById('similarScroll');
		if (!sc) return;
		window.similarScroll = function(dir) {
			var card = sc.querySelector('.tp-hscroll__card');
			var w = card ? card.offsetWidth + 12 : 280;
			sc.scrollBy({ left: dir * w, behavior: 'smooth' });
		};
		// Drag-to-scroll on desktop
		var isDown = false, startX, scrollLeft, moved;
		sc.addEventListener('mousedown', function(e) {
			isDown = true; moved = false;
			sc.style.cursor = 'grabbing';
			startX = e.pageX - sc.offsetLeft;
			scrollLeft = sc.scrollLeft;
			e.preventDefault();
		});
		sc.addEventListener('mouseleave', function() { isDown = false; sc.style.cursor = 'grab'; });
		sc.addEventListener('mouseup', function() { isDown = false; sc.style.cursor = 'grab'; });
		sc.addEventListener('mousemove', function(e) {
			if (!isDown) return;
			e.preventDefault();
			var x = e.pageX - sc.offsetLeft;
			var walk = (x - startX) * 1.5;
			if (Math.abs(walk) > 5) moved = true;
			sc.scrollLeft = scrollLeft - walk;
		});
		sc.addEventListener('click', function(e) {
			if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
		}, true);
	})();
	</script>
</section>
