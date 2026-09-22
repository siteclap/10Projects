<?php
/**
 * Price & Configuration Section — Merged (price-config + floor-plans)
 *
 * Tiers:
 *   1. Grouped floor plans WITH real images → tabbed view
 *   2. Grouped floor plans WITHOUT images → scroll cards with default BHK images
 *   3. DB config table → scroll cards with default BHK images + full pricing
 *   4. Text-only configs → scroll cards with default BHK images
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return default floor plan image URL based on config name and property type.
 */
if ( ! function_exists( 'tp_fp_placeholder_img' ) ) :
function tp_fp_placeholder_img( $config_name, $property_type = 'buy' ) {
	if ( 'commercial' === $property_type ) {
		return get_template_directory_uri() . '/assets/images/fp-office.png';
	}
	$bhk = 2; // default
	if ( preg_match( '/(\d+)\s*bhk/i', $config_name, $m ) ) {
		$bhk = intval( $m[1] );
	}
	if ( $bhk <= 1 ) {
		$file = '1-BHK-3D-Floor.jpg';
	} elseif ( $bhk == 2 ) {
		$file = '2-BHK-3D-Floor.jpg';
	} else {
		$file = '3-4-BHK-3D-Floor.jpg';
	}
	return get_template_directory_uri() . '/assets/images/' . $file;
}
endif;

$post_id       = $args['post_id'] ?? get_the_ID();
$property_type = $args['property_type'] ?? 'buy';
$configs_text  = tp_get_meta( $post_id, 'available_configs_text' );
$price_min   = floatval( tp_get_meta( $post_id, 'price_display_min' ) );
$location    = tp_get_location_term( $post_id );
$loc_name    = $location ? $location->name : '';

// Get uploaded floor plan data from meta.
$floor_plans_raw = get_post_meta( $post_id, '_tp_floor_plans', true );
if ( ! is_array( $floor_plans_raw ) ) {
	$floor_plans_raw = array();
}

// Normalize: support both old flat format and new grouped format.
$grouped = array();
if ( ! empty( $floor_plans_raw ) ) {
	if ( isset( $floor_plans_raw[0]['config'] ) ) {
		// New grouped format.
		foreach ( $floor_plans_raw as $g ) {
			if ( empty( $g['config'] ) && empty( $g['units'] ) ) continue;
			$units = array();
			if ( ! empty( $g['units'] ) && is_array( $g['units'] ) ) {
				foreach ( $g['units'] as $u ) {
					if ( empty( $u['area'] ) && empty( $u['image'] ) ) continue;
					$units[] = $u;
				}
			}
			if ( ! empty( $units ) || ! empty( $g['config'] ) ) {
				$grouped[] = array( 'config' => $g['config'], 'units' => $units );
			}
		}
	} else {
		// Old flat format — group by label.
		$by_label = array();
		foreach ( $floor_plans_raw as $fp ) {
			if ( empty( $fp['label'] ) && empty( $fp['image'] ) ) continue;
			$label = $fp['label'] ?? 'Other';
			if ( ! isset( $by_label[ $label ] ) ) {
				$by_label[ $label ] = array();
			}
			$by_label[ $label ][] = array(
				'area'  => $fp['area'] ?? '',
				'price' => $fp['price'] ?? '',
				'image' => $fp['image'] ?? '',
			);
		}
		foreach ( $by_label as $label => $units ) {
			$grouped[] = array( 'config' => $label, 'units' => $units );
		}
	}
}

// Sort grouped floor plans: 1 BHK → 2 BHK → 3 BHK → 4+ BHK → Jodi.
usort( $grouped, function( $a, $b ) {
	$bhk_order = function( $name ) {
		if ( stripos( $name, 'jodi' ) !== false ) return 9999;
		if ( preg_match( '/^(\d+(?:\.\d+)?)\s*bhk/i', $name, $m ) ) return floatval( $m[1] ) * 10;
		return 5000;
	};
	return $bhk_order( $a['config'] ) - $bhk_order( $b['config'] );
} );

$has_images = false;
foreach ( $grouped as $g ) {
	foreach ( $g['units'] as $u ) {
		if ( ! empty( $u['image'] ) ) { $has_images = true; break 2; }
	}
}

// Get configs from DB table as fallback.
global $wpdb;
$table = $wpdb->prefix . 'tp_project_configurations';
$configs = array();
if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table ) {
	$configs = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE project_id = %d ORDER BY
			CASE
				WHEN configuration LIKE '%%Jodi%%' THEN 9999
				WHEN configuration REGEXP '^[0-9]' THEN CAST(SUBSTRING_INDEX(configuration, ' ', 1) AS UNSIGNED)
				ELSE 5000
			END ASC,
			price_min ASC",
		$post_id
	) );
}

// Nothing to show at all.
if ( empty( $grouped ) && empty( $configs ) && ! $configs_text ) {
	return;
}
?>

<section class="tp-section" id="<?php echo 'commercial' === $property_type ? 'unit-plans' : 'price'; ?>">
	<h2>Price & Configuration of <?php the_title(); ?><?php echo $loc_name ? ', ' . esc_html( $loc_name ) : ''; ?></h2>

	<?php if ( $has_images && ! empty( $grouped ) ) : ?>
		<!-- Tabbed floor plan view -->

		<?php if ( count( $grouped ) > 1 ) : ?>
		<div class="tp-fp-tabs" id="fpTabs">
			<?php foreach ( $grouped as $ci => $g ) :
				$prices = array_filter( array_column( $g['units'], 'price' ) );
				$price_hint = '';
				if ( count( $prices ) === 1 ) {
					$price_hint = reset( $prices );
				} elseif ( count( $prices ) > 1 ) {
					$price_hint = reset( $prices ) . ' – ' . end( $prices );
				}
			?>
				<button class="tp-fp-tab<?php echo $ci === 0 ? ' active' : ''; ?>" data-config="<?php echo $ci; ?>" type="button">
					<span class="tp-fp-tab__label"><?php echo esc_html( $g['config'] ); ?></span>
					<?php if ( $price_hint ) : ?>
						<span class="tp-fp-tab__price"><?php echo esc_html( $price_hint ); ?></span>
					<?php endif; ?>
				</button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php foreach ( $grouped as $ci => $g ) : ?>
		<div class="tp-fp-config<?php echo $ci === 0 ? ' active' : ''; ?>" data-config-panel="<?php echo $ci; ?>">

			<?php if ( count( $g['units'] ) > 1 ) : ?>
			<div class="tp-fp-areas">
				<span class="tp-fp-areas__label">Carpet Area</span>
				<?php foreach ( $g['units'] as $ui => $unit ) : ?>
					<button class="tp-fp-area<?php echo $ui === 0 ? ' active' : ''; ?>" data-unit="<?php echo $ui; ?>" type="button">
						<?php echo esc_html( $unit['area'] ?: 'Unit ' . ( $ui + 1 ) ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php foreach ( $g['units'] as $ui => $unit ) : ?>
			<div class="tp-fp-unit<?php echo $ui === 0 ? ' active' : ''; ?>" data-unit-panel="<?php echo $ui; ?>">
				<?php if ( ! empty( $unit['image'] ) ) : ?>
				<div class="tp-fp-display__img" role="button" tabindex="0" data-ci="<?php echo $ci; ?>" data-ui="<?php echo $ui; ?>">
					<img src="<?php echo esc_url( $unit['image'] ); ?>" alt="<?php echo esc_attr( $g['config'] . ( $unit['area'] ? ' - ' . $unit['area'] : '' ) ); ?> Floor Plan" loading="lazy">
					<div class="tp-fp-display__zoom">
						<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
						Tap to enlarge
					</div>
				</div>
				<?php else : ?>
				<div class="tp-fp-display__img tp-fp-display__img--dummy">
					<img src="<?php echo esc_url( tp_fp_placeholder_img( $g['config'], $property_type ) ); ?>" alt="<?php echo esc_attr( $g['config'] ); ?> — Indicative Layout" loading="lazy">
					<div class="tp-fp-display__badge">Indicative Layout</div>
				</div>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endforeach; ?>

		<!-- Floor Plan Lightbox -->
		<div class="tp-fp-lightbox" id="fpLightbox">
			<button class="tp-fp-lightbox__close" onclick="closeFloorPlan()">&times;</button>
			<button class="tp-fp-lightbox__nav tp-fp-lightbox__prev" onclick="navFloorPlan(-1)">&#8249;</button>
			<div class="tp-fp-lightbox__content">
				<img id="fpLightboxImg" src="" alt="Floor Plan">
				<div class="tp-fp-lightbox__label" id="fpLightboxLabel"></div>
			</div>
			<button class="tp-fp-lightbox__nav tp-fp-lightbox__next" onclick="navFloorPlan(1)">&#8250;</button>
		</div>

		<script>
		(function(){
			var grouped = <?php echo wp_json_encode( $grouped ); ?>;
			// Flatten all images for lightbox navigation
			var allPlans = [];
			grouped.forEach(function(g){
				g.units.forEach(function(u){
					if (u.image) allPlans.push({ config: g.config, area: u.area || '', price: u.price || '', image: u.image });
				});
			});
			var lbIdx = 0;
			var lb = document.getElementById('fpLightbox');
			var lbImg = document.getElementById('fpLightboxImg');
			var lbLabel = document.getElementById('fpLightboxLabel');

			function showLb(i) {
				if (!allPlans[i]) return;
				lbIdx = i;
				lbImg.src = allPlans[i].image;
				lbLabel.textContent = allPlans[i].config + (allPlans[i].area ? ' — ' + allPlans[i].area : '') + (allPlans[i].price ? ' — ₹' + allPlans[i].price : '');
				lb.classList.add('active');
				document.body.style.overflow = 'hidden';
			}

			// Map (ci, ui) to flat index
			function flatIndex(ci, ui) {
				var idx = 0;
				for (var c = 0; c < grouped.length; c++) {
					for (var u = 0; u < grouped[c].units.length; u++) {
						if (grouped[c].units[u].image) {
							if (c === ci && u === ui) return idx;
							idx++;
						}
					}
				}
				return 0;
			}

			window.openFloorPlan = function(ci, ui) { showLb(flatIndex(ci, ui)); };
			window.closeFloorPlan = function() { lb.classList.remove('active'); document.body.style.overflow = ''; };
			window.navFloorPlan = function(dir) { showLb((lbIdx + dir + allPlans.length) % allPlans.length); };

			lb.addEventListener('click', function(e){ if(e.target===lb) closeFloorPlan(); });
			document.addEventListener('keydown', function(e){
				if (!lb.classList.contains('active')) return;
				if (e.key==='Escape') closeFloorPlan();
				if (e.key==='ArrowLeft') navFloorPlan(-1);
				if (e.key==='ArrowRight') navFloorPlan(1);
			});

			// Click handlers for images and buttons
			document.querySelectorAll('.tp-fp-display__img[data-ci], .tp-fp-display__btn[data-ci]').forEach(function(el){
				el.addEventListener('click', function(){ openFloorPlan(+el.dataset.ci, +el.dataset.ui); });
			});

			// Config tab switching
			var tabs = document.querySelectorAll('.tp-fp-tab');
			var panels = document.querySelectorAll('.tp-fp-config');
			tabs.forEach(function(tab){
				tab.addEventListener('click', function(){
					var ci = +tab.dataset.config;
					tabs.forEach(function(t){ t.classList.remove('active'); });
					tab.classList.add('active');
					panels.forEach(function(p){ p.classList.toggle('active', +p.dataset.configPanel === ci); });
				});
			});

			// Area pill switching within each config
			document.querySelectorAll('.tp-fp-config').forEach(function(panel){
				var areaBtns = panel.querySelectorAll('.tp-fp-area');
				var unitPanels = panel.querySelectorAll('.tp-fp-unit');
				areaBtns.forEach(function(btn){
					btn.addEventListener('click', function(){
						var ui = +btn.dataset.unit;
						areaBtns.forEach(function(b){ b.classList.remove('active'); });
						btn.classList.add('active');
						unitPanels.forEach(function(p){ p.classList.toggle('active', +p.dataset.unitPanel === ui); });
					});
				});
			});
		})();
		</script>

	<?php elseif ( ! empty( $grouped ) ) : ?>
		<!-- Floor plans without images — show as scroll cards with default images -->
		<?php
		$total_cards = 0;
		foreach ( $grouped as $g ) { $total_cards += count( $g['units'] ); }
		?>
		<div class="tp-fp-scroll-wrap">
			<?php if ( $total_cards > 3 ) : ?>
			<button class="tp-fp-arrow tp-fp-arrow--left" aria-label="Scroll left" onclick="fpScroll(-1)">
				<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
			</button>
			<button class="tp-fp-arrow tp-fp-arrow--right" aria-label="Scroll right" onclick="fpScroll(1)">
				<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
			</button>
			<?php endif; ?>
			<div class="tp-fp-scroll" id="fpScrollContainer">
				<?php foreach ( $grouped as $g ) :
					foreach ( $g['units'] as $unit ) : ?>
					<div class="tp-fp-card">
						<div class="tp-fp-card__img">
							<img src="<?php echo esc_url( tp_fp_placeholder_img( $g['config'], $property_type ) ); ?>" alt="<?php echo esc_attr( $g['config'] ); ?> — Indicative Layout" loading="lazy">
							<div class="tp-fp-card__badge">Indicative Layout</div>
						</div>
						<div class="tp-fp-card__body">
							<div class="tp-fp-card__type"><?php echo esc_html( $g['config'] ); ?></div>
							<?php if ( ! empty( $unit['area'] ) ) : ?>
							<div class="tp-fp-card__meta">
								<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
								<?php echo esc_html( $unit['area'] ); ?>
							</div>
							<?php endif; ?>
							<?php if ( ! empty( $unit['price'] ) ) : ?>
							<div class="tp-fp-card__price">
								<span class="tp-fp-card__price-label">Price</span>
								<span class="tp-fp-card__price-value"><?php echo esc_html( $unit['price'] ); ?></span>
							</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; endforeach; ?>
			</div>
		</div>

	<?php elseif ( ! empty( $configs ) ) : ?>
		<!-- DB config cards with default BHK images + full pricing -->
		<div class="tp-fp-scroll-wrap">
			<?php if ( count( $configs ) > 3 ) : ?>
			<button class="tp-fp-arrow tp-fp-arrow--left" aria-label="Scroll left" onclick="fpScroll(-1)">
				<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
			</button>
			<button class="tp-fp-arrow tp-fp-arrow--right" aria-label="Scroll right" onclick="fpScroll(1)">
				<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
			</button>
			<?php endif; ?>
		<div class="tp-fp-scroll" id="fpScrollContainer">
			<?php foreach ( $configs as $c ) :
				$status = $c->inventory_status ?? 'Available';
				$is_sold = $status === 'Sold Out';
			?>
				<div class="tp-fp-card<?php echo $is_sold ? ' tp-fp-card--sold' : ''; ?>">
					<div class="tp-fp-card__img">
						<img src="<?php echo esc_url( tp_fp_placeholder_img( $c->configuration, $property_type ) ); ?>" alt="<?php echo esc_attr( $c->configuration ); ?> — Indicative Layout" loading="lazy">
						<div class="tp-fp-card__badge">Indicative Layout</div>
					</div>
					<div class="tp-fp-card__body">
						<div class="tp-fp-card__header-row">
							<div class="tp-fp-card__type"><?php echo esc_html( $c->configuration ); ?></div>
							<span class="tp-fp-card__status <?php echo $is_sold ? 'tp-fp-card__status--sold' : 'tp-fp-card__status--available'; ?>">
								<?php echo esc_html( $status ); ?>
							</span>
						</div>
						<div class="tp-fp-card__price">
							<span class="tp-fp-card__price-label">Price</span>
							<span class="tp-fp-card__price-value"><?php echo esc_html( tp_format_price_range( $c->price_min / 100, $c->price_max / 100 ) ); ?></span>
						</div>
						<?php if ( ! $is_sold ) : ?>
							<button type="button" class="tp-fp-card__btn js-open-lead-popup" data-source="price_breakup">Get Price Breakup</button>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		</div>

	<?php elseif ( $configs_text ) : ?>
		<!-- Fallback: text-based cards with default images -->
		<div class="tp-fp-scroll" id="fpScrollContainer">
			<?php
			$types = array_map( 'trim', explode( ',', $configs_text ) );
			foreach ( $types as $type ) :
			?>
				<div class="tp-fp-card">
					<div class="tp-fp-card__img">
						<img src="<?php echo esc_url( tp_fp_placeholder_img( $type, $property_type ) ); ?>" alt="<?php echo esc_attr( $type ); ?> — Indicative Layout" loading="lazy">
						<div class="tp-fp-card__badge">Indicative Layout</div>
					</div>
					<div class="tp-fp-card__body">
						<div class="tp-fp-card__type"><?php echo esc_html( $type ); ?></div>
						<div class="tp-fp-card__meta">Floor plan available on request</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! $has_images ) : ?>
	<!-- Arrow navigation + drag-to-scroll for fallback scroll views -->
	<script>
	(function(){
		var sc = document.getElementById('fpScrollContainer');
		if (!sc) return;
		window.fpScroll = function(dir) {
			var card = sc.querySelector('.tp-fp-card');
			var w = card ? card.offsetWidth + 12 : 300;
			sc.scrollBy({ left: dir * w, behavior: 'smooth' });
		};
		var isDown = false, startX, scrollLeft, moved;
		sc.addEventListener('mousedown', function(e) {
			isDown = true; moved = false;
			sc.classList.add('tp-fp-scroll--grabbing');
			startX = e.pageX - sc.offsetLeft;
			scrollLeft = sc.scrollLeft;
			e.preventDefault();
		});
		sc.addEventListener('mouseleave', function() { isDown = false; sc.classList.remove('tp-fp-scroll--grabbing'); });
		sc.addEventListener('mouseup', function(e) {
			isDown = false; sc.classList.remove('tp-fp-scroll--grabbing');
			if (moved) { e.preventDefault(); e.stopPropagation(); }
		});
		sc.addEventListener('mousemove', function(e) {
			if (!isDown) return; e.preventDefault();
			var x = e.pageX - sc.offsetLeft;
			var walk = (x - startX) * 1.5;
			if (Math.abs(walk) > 5) moved = true;
			sc.scrollLeft = scrollLeft - walk;
		});
		sc.addEventListener('click', function(e) { if (moved) { e.stopPropagation(); moved = false; } }, true);
	})();
	</script>
	<?php endif; ?>
</section>
