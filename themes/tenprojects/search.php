<?php
/**
 * Search Results Template
 *
 * Shows project search results.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$cur_property_type = isset( $_GET['property_type'] ) ? sanitize_text_field( $_GET['property_type'] ) : '';
$property_types = array(
	''           => 'All',
	'buy'        => 'Buy',
	'rent'       => 'Rent',
	'commercial' => 'Commercial',
	'resale'     => 'Resale',
	'plot'       => 'Plot',
);

get_header();
?>

<main class="tp-search-page">

	<?php get_template_part( 'partials/filter-bar' ); ?>

	<div class="tp-container" style="padding-top:var(--2xl);">

		<div class="tp-search-header">
			<h1>
				<?php if ( $cur_property_type && isset( $property_types[ $cur_property_type ] ) ) : ?>
					<?php echo esc_html( $property_types[ $cur_property_type ] ); ?> results for "<?php echo esc_html( get_search_query() ); ?>"
				<?php else : ?>
					Search Results for "<?php echo esc_html( get_search_query() ); ?>"
				<?php endif; ?>
			</h1>
			<p><?php echo esc_html( $wp_query->found_posts ); ?> result<?php echo $wp_query->found_posts !== 1 ? 's' : ''; ?> found</p>
		</div>

		<!-- Category Tabs + Search Again -->
		<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" id="tp-search-form" style="margin-bottom:var(--2xl);">
			<input type="hidden" name="post_type" value="tp_project">
			<input type="hidden" name="property_type" id="tp-search-property-type" value="<?php echo esc_attr( $cur_property_type ); ?>">

			<div class="tp-search-tabs" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:var(--lg);">
				<?php foreach ( $property_types as $slug => $label ) : ?>
					<button type="button" class="tp-hero__tab <?php echo $cur_property_type === $slug ? 'tp-hero__tab--active' : ''; ?>" data-category="<?php echo esc_attr( $slug ); ?>" style="font-size:13px;padding:7px 16px;">
						<?php echo esc_html( $label ); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="tp-hero__search-inner" style="max-width:600px;border:1px solid var(--gray-200);box-shadow:none;">
				<svg class="tp-hero__search-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
				</svg>
				<input type="text" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Search projects..." class="tp-hero__search-input">
				<button type="submit" class="tp-btn tp-btn--primary tp-hero__search-btn">Search</button>
			</div>
		</form>

		<?php if ( have_posts() ) : ?>

			<div class="tp-search-list">
				<?php while ( have_posts() ) : the_post();
					$p_id       = get_the_ID();
					$p_thumb    = get_the_post_thumbnail_url( $p_id, 'medium_large' );
					$p_dev      = tp_get_meta( $p_id, 'developer_name' ) ?: '';
					$p_loc      = tp_get_location_term( $p_id );
					$p_loc_name = $p_loc ? $p_loc->name : '';
					$p_min      = intval( tp_get_meta( $p_id, 'price_display_min' ) );
					$p_max      = intval( tp_get_meta( $p_id, 'price_display_max' ) );
					$p_configs  = tp_get_meta( $p_id, 'available_configs_text' );
					$p_stage    = tp_get_meta( $p_id, 'construction_stage' );
					$p_rera     = tp_get_meta( $p_id, 'rera_number' );
					$p_overview = tp_get_meta( $p_id, 'short_overview' );
				?>
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="tp-list-card">
						<div class="tp-list-card__img">
							<?php if ( $p_thumb ) : ?>
								<img src="<?php echo esc_url( $p_thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
							<?php else : ?>
								<div class="tp-list-card__placeholder">
									<svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
								</div>
							<?php endif; ?>
							<?php if ( $p_stage ) : ?>
								<span class="tp-list-card__badge <?php echo $p_stage === 'Ready to Move' ? 'tp-list-card__badge--green' : ''; ?>"><?php echo esc_html( $p_stage ); ?></span>
							<?php endif; ?>
						</div>
						<div class="tp-list-card__body">
							<div class="tp-list-card__top">
								<h3 class="tp-list-card__title"><?php echo esc_html( get_the_title() ); ?></h3>
								<?php if ( $p_min ) : ?>
									<div class="tp-list-card__price"><?php echo esc_html( tp_format_price_range( $p_min, $p_max ) ); ?></div>
								<?php endif; ?>
							</div>
							<div class="tp-list-card__details">
								<?php if ( $p_loc_name ) : ?>
									<span class="tp-list-card__detail">
										<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
										<?php echo esc_html( $p_loc_name ); ?>, Navi Mumbai
									</span>
								<?php endif; ?>
								<?php if ( $p_configs ) : ?>
									<span class="tp-list-card__detail">
										<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
										<?php echo esc_html( $p_configs ); ?>
									</span>
								<?php endif; ?>
								<?php if ( $p_dev ) : ?>
									<span class="tp-list-card__detail">
										<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
										<?php echo esc_html( $p_dev ); ?>
									</span>
								<?php endif; ?>
								<?php if ( $p_rera ) : ?>
									<span class="tp-list-card__detail tp-list-card__detail--rera">
										<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
										RERA Registered
									</span>
								<?php endif; ?>
							</div>
							<?php if ( $p_overview ) : ?>
								<p class="tp-list-card__desc"><?php echo esc_html( wp_trim_words( $p_overview, 25, '...' ) ); ?></p>
							<?php endif; ?>
							<span class="tp-list-card__cta">View Details &rarr;</span>
						</div>
					</a>
				<?php endwhile; ?>
			</div>

			<div class="tp-pagination">
				<?php the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => '&larr; Previous',
					'next_text' => 'Next &rarr;',
				) ); ?>
			</div>

		<?php else : ?>

			<div class="tp-empty-state">
				<svg width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="var(--gray-300)" stroke-width="1">
					<path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
				</svg>
				<h3>No projects found</h3>
				<p>Try a different search term or browse by category.</p>
				<div style="display:flex;gap:var(--sm);justify-content:center;flex-wrap:wrap;">
					<a href="<?php echo esc_url( home_url( '/properties/buy/' ) ); ?>" class="tp-btn tp-btn--primary">Buy</a>
					<a href="<?php echo esc_url( home_url( '/properties/rent/' ) ); ?>" class="tp-btn tp-btn--outline">Rent</a>
					<a href="<?php echo esc_url( home_url( '/properties/commercial/' ) ); ?>" class="tp-btn tp-btn--outline">Commercial</a>
				</div>
			</div>

		<?php endif; ?>

	</div>
</main>

<script>
(function(){
	var tabs = document.querySelectorAll('#tp-search-form .tp-hero__tab');
	var hidden = document.getElementById('tp-search-property-type');
	var form = document.getElementById('tp-search-form');
	tabs.forEach(function(tab){
		tab.addEventListener('click', function(){
			tabs.forEach(function(t){ t.classList.remove('tp-hero__tab--active'); });
			tab.classList.add('tp-hero__tab--active');
			hidden.value = tab.dataset.category;
			form.submit();
		});
	});
})();
</script>
<?php get_footer(); ?>
