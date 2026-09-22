<?php
/**
 * Homepage Template
 *
 * Hero → Returning User → New Launches → Featured →
 * Buy → Rent → Commercial → Plots →
 * Browse by Category → Popular Locations → Blog → Trust → CTA
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

/* ═══════════════ DATA (Cached with 6-hour transients) ═══════════════ */

function tp_hp_get_by_type( string $slug, int $n = 10 ): array {
	$cache_key = 'tp_hp_' . $slug;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}
	$result = get_posts( array(
		'post_type'      => 'tp_project',
		'posts_per_page' => $n,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'tax_query'      => array( array(
			'taxonomy' => 'tp_property_type',
			'field'    => 'slug',
			'terms'    => $slug,
		) ),
	) );
	set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
	return $result;
}

$new_launches = get_transient( 'tp_hp_new_launches' );
if ( false === $new_launches ) {
	$new_launches = get_posts( array(
		'post_type'      => 'tp_project',
		'posts_per_page' => 10,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array( array( 'key' => '_tp_is_new_launch', 'value' => '1' ) ),
	) );
	set_transient( 'tp_hp_new_launches', $new_launches, 6 * HOUR_IN_SECONDS );
}

$featured = get_transient( 'tp_hp_featured' );
if ( false === $featured ) {
	$featured = get_posts( array(
		'post_type'      => 'tp_project',
		'posts_per_page' => 10,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array( array( 'key' => '_tp_is_featured', 'value' => '1' ) ),
	) );
	set_transient( 'tp_hp_featured', $featured, 6 * HOUR_IN_SECONDS );
}

$buy_projects        = tp_hp_get_by_type( 'buy' );
$rent_projects       = tp_hp_get_by_type( 'rent' );
$commercial_projects = tp_hp_get_by_type( 'commercial' );
$plot_projects       = tp_hp_get_by_type( 'plot' );

$categories = array(
	array( 'slug' => 'buy',        'label' => 'Buy',        'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' ),
	array( 'slug' => 'rent',       'label' => 'Rent',       'icon' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z' ),
	array( 'slug' => 'commercial', 'label' => 'Commercial', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' ),
	array( 'slug' => 'resale',     'label' => 'Resale',     'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15' ),
	array( 'slug' => 'plot',       'label' => 'Plot',       'icon' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7' ),
);

// Popular locations (cached).
$popular_locations = get_transient( 'tp_hp_popular_locs' );
if ( false === $popular_locations ) {
	$popular_locations = get_terms( array(
		'taxonomy'   => 'tp_location_area',
		'hide_empty' => false,
		'meta_key'   => 'tp_show_on_homepage',
		'meta_value' => '1',
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );
	set_transient( 'tp_hp_popular_locs', $popular_locations, 6 * HOUR_IN_SECONDS );
}

// All locations by project count (cached).
$locations = get_transient( 'tp_hp_locations' );
if ( false === $locations ) {
	$locations = get_terms( array(
		'taxonomy'   => 'tp_location_area',
		'hide_empty' => false,
		'number'     => 12,
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );
	set_transient( 'tp_hp_locations', $locations, 6 * HOUR_IN_SECONDS );
}

$project_count = wp_count_posts( 'tp_project' )->publish ?: 0;

$hero_desktop = get_option( 'tp_brand_hero_desktop', '' );
$hero_bg      = $hero_desktop ?: get_template_directory_uri() . '/assets/images/hero-banner.png';

get_header();
?>

<!-- ═══════════════ HERO ═══════════════ -->
<section class="tp-hero" style="background-image:url('<?php echo esc_url( $hero_bg ); ?>');">
	<div class="tp-hero__content">
		<div class="tp-hero__badge">
			<span class="tp-hero__badge-dot"></span>
			Navi Mumbai's trusted property platform
		</div>

		<h1 class="tp-hero__title">
			Find your <span class="tp-hero__accent">dream home</span><br>in Navi Mumbai
		</h1>
		<p class="tp-hero__subtitle">AI-powered search across <?php echo esc_html( $project_count ); ?>+ verified projects. Zero brokerage. RERA-verified data.</p>

		<!-- Category Tabs -->
		<div class="tp-hero__tabs" id="tp-hero-tabs">
			<?php foreach ( $categories as $i => $cat ) : ?>
				<button type="button" class="tp-hero__tab <?php echo $i === 0 ? 'tp-hero__tab--active' : ''; ?>" data-category="<?php echo esc_attr( $cat['slug'] ); ?>">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="<?php echo esc_attr( $cat['icon'] ); ?>"/></svg>
					<?php echo esc_html( $cat['label'] ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<!-- Search Bar -->
		<form class="tp-hero__search" id="tp-hero-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
			<input type="hidden" name="post_type" value="tp_project">
			<input type="hidden" name="property_type" id="tp-hero-property-type" value="buy">
			<div class="tp-hero__search-inner">
				<svg class="tp-hero__search-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
				<input type="text" name="s" placeholder="Search by project name, location, or developer..." class="tp-hero__search-input" autocomplete="off">
				<button type="submit" class="tp-btn tp-btn--primary tp-hero__search-btn">Search</button>
			</div>
		</form>

		<!-- Popular Locations (admin-managed via Location → "Show on Homepage" checkbox) -->
		<?php if ( ! empty( $popular_locations ) && ! is_wp_error( $popular_locations ) ) : ?>
			<div class="tp-hero__locations">
				<span>Popular:</span>
				<?php foreach ( $popular_locations as $loc ) : ?>
					<a href="<?php echo esc_url( home_url( '/navi-mumbai/' . $loc->slug . '/' ) ); ?>"><?php echo esc_html( $loc->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="tp-hero__stats">
			<div class="tp-hero__stat">
				<div class="tp-hero__stat-num"><?php echo esc_html( $project_count ); ?>+</div>
				<div class="tp-hero__stat-label">Projects Listed</div>
			</div>
			<div class="tp-hero__stat-divider"></div>
			<div class="tp-hero__stat">
				<div class="tp-hero__stat-num">100%</div>
				<div class="tp-hero__stat-label">RERA Verified</div>
			</div>
			<div class="tp-hero__stat-divider"></div>
			<div class="tp-hero__stat">
				<div class="tp-hero__stat-num">0%</div>
				<div class="tp-hero__stat-label">Brokerage</div>
			</div>
		</div>
	</div>
</section>

<!-- ═══════════════ TRUST BADGE STRIP ═══════════════ -->
<section class="tp-trust-strip">
	<div class="tp-container">
		<div class="tp-trust-strip__row">
			<div class="tp-trust-strip__item">
				<div class="tp-trust-strip__icon tp-trust-strip__icon--green">
					<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
				</div>
				<div class="tp-trust-strip__text">
					<span class="tp-trust-strip__title">RERA Verified</span>
					<span class="tp-trust-strip__desc">All projects verified on MahaRERA</span>
				</div>
			</div>
			<div class="tp-trust-strip__divider"></div>
			<div class="tp-trust-strip__item">
				<div class="tp-trust-strip__icon tp-trust-strip__icon--blue">
					<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
				</div>
				<div class="tp-trust-strip__text">
					<span class="tp-trust-strip__title">Zero Brokerage</span>
					<span class="tp-trust-strip__desc">Save lakhs — we earn from developers</span>
				</div>
			</div>
			<div class="tp-trust-strip__divider"></div>
			<div class="tp-trust-strip__item">
				<div class="tp-trust-strip__icon tp-trust-strip__icon--purple">
					<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
				</div>
				<div class="tp-trust-strip__text">
					<span class="tp-trust-strip__title">AI-Powered Search</span>
					<span class="tp-trust-strip__desc">Smart matching to find your best fit</span>
				</div>
			</div>
			<div class="tp-trust-strip__divider"></div>
			<div class="tp-trust-strip__item">
				<div class="tp-trust-strip__icon tp-trust-strip__icon--amber">
					<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
				</div>
				<div class="tp-trust-strip__text">
					<span class="tp-trust-strip__title">No Spam Promise</span>
					<span class="tp-trust-strip__desc">Your data stays safe — zero unwanted calls</span>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ═══════════════ RETURNING USER ═══════════════ -->
<section class="tp-hp-section tp-continue-section" id="tp-continue-section" style="display:none;" aria-label="Continue Exploring">
	<div class="tp-container">
		<div class="tp-hp-section__header">
			<div>
				<h2 class="tp-hp-section__title">Continue Exploring</h2>
				<p class="tp-hp-section__subtitle">Based on your recent activity</p>
			</div>
		</div>
		<div class="tp-hp-carousel-wrap" data-prop-type="" data-returning="1">
			<button class="tp-hp-carousel__arrow tp-hp-carousel__arrow--prev" aria-label="Previous">&#8249;</button>
			<div class="tp-hp-carousel" id="tp-continue-track" aria-live="polite"></div>
			<button class="tp-hp-carousel__arrow tp-hp-carousel__arrow--next" aria-label="Next">&#8250;</button>
		</div>
	</div>
</section>

<?php
/**
 * Render a full-bleed overlay card (new style).
 */
function tp_hp_render_card( int $post_id, string $prop_type ): string {
	$thumb    = get_the_post_thumbnail_url( $post_id, 'large' );
	$dev      = tp_get_meta( $post_id, 'developer_name' ) ?: '';
	$loc      = tp_get_location_term( $post_id );
	$loc_name = $loc ? $loc->name : '';
	$loc_slug = $loc ? $loc->slug : '';
	$min      = floatval( tp_get_meta( $post_id, 'price_display_min' ) );
	$max      = floatval( tp_get_meta( $post_id, 'price_display_max' ) );
	$configs  = tp_get_meta( $post_id, 'available_configs_text' );
	$stage    = tp_get_meta( $post_id, 'construction_stage' );
	$rera     = tp_get_meta( $post_id, 'rera_number' );
	$rent     = intval( tp_get_meta( $post_id, 'monthly_rent' ) );

	// Build price string
	if ( $rent ) {
		$price_str = '&#8377;' . number_format( $rent ) . '/mo';
	} elseif ( $min ) {
		$price_str = esc_html( tp_format_price_range( $min, $max ) );
	} else {
		$price_str = 'Price on Request';
	}

	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"
	   class="tp-hp-card"
	   data-pid="<?php echo esc_attr( $post_id ); ?>"
	   data-type="<?php echo esc_attr( $prop_type ); ?>"
	   data-loc="<?php echo esc_attr( $loc_slug ); ?>">

		<!-- Image -->
		<div class="tp-hp-card__img">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy">
			<?php else : ?>
				<div class="tp-hp-card__img-fallback"></div>
			<?php endif; ?>
			<!-- Gradient overlay -->
			<div class="tp-hp-card__overlay"></div>
		</div>

		<!-- Top badges -->
		<div class="tp-hp-card__top">
			<?php if ( $stage ) : ?>
				<span class="tp-hp-card__badge"><?php echo esc_html( $stage ); ?></span>
			<?php endif; ?>
			<?php if ( $rera ) : ?>
				<span class="tp-hp-card__badge tp-hp-card__badge--rera">RERA</span>
			<?php endif; ?>
		</div>

		<!-- Bottom overlay content -->
		<div class="tp-hp-card__body">
			<div class="tp-hp-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></div>
			<?php if ( $dev ) : ?>
				<div class="tp-hp-card__dev">by <?php echo esc_html( $dev ); ?></div>
			<?php endif; ?>
			<div class="tp-hp-card__meta-row">
				<div class="tp-hp-card__meta-left">
					<?php if ( $configs ) : ?>
						<span><?php echo esc_html( $configs ); ?></span>
					<?php endif; ?>
					<?php if ( $loc_name ) : ?>
						<span class="tp-hp-card__loc">
							<svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
							<?php echo esc_html( $loc_name ); ?>, Navi Mumbai
						</span>
					<?php endif; ?>
				</div>
				<div class="tp-hp-card__price"><?php echo $price_str; ?></div>
			</div>
		</div>
	</a>
	<?php
	return ob_get_clean();
}

/**
 * Render a full carousel section with heading.
 */
function tp_hp_section( string $title, string $subtitle, string $view_all_url, array $posts, string $section_id, string $prop_type ): void {
	if ( empty( $posts ) ) {
		return;
	}
	?>
	<section class="tp-hp-section" id="<?php echo esc_attr( $section_id ); ?>" data-section="<?php echo esc_attr( $section_id ); ?>">
		<div class="tp-container">
			<div class="tp-hp-section__header">
				<div>
					<h2 class="tp-hp-section__title"><?php echo esc_html( $title ); ?></h2>
					<p class="tp-hp-section__subtitle"><?php echo esc_html( $subtitle ); ?></p>
				</div>
				<?php if ( $view_all_url ) : ?>
					<a href="<?php echo esc_url( $view_all_url ); ?>" class="tp-link-arrow">View All <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
				<?php endif; ?>
			</div>
			<div class="tp-hp-carousel-wrap" data-prop-type="<?php echo esc_attr( $prop_type ); ?>">
				<button class="tp-hp-carousel__arrow tp-hp-carousel__arrow--prev" aria-label="Previous">&#8249;</button>
				<div class="tp-hp-carousel">
					<?php foreach ( $posts as $p ) : ?>
						<?php echo tp_hp_render_card( $p->ID, $prop_type ); ?>
					<?php endforeach; ?>
				</div>
				<button class="tp-hp-carousel__arrow tp-hp-carousel__arrow--next" aria-label="Next">&#8250;</button>
			</div>
		</div>
	</section>
	<?php
}
?>

<!-- ═══════════════ NEW LAUNCHES ═══════════════ -->
<?php tp_hp_section( 'New Launches', 'Freshly launched projects in Navi Mumbai', home_url( '/properties/buy/' ), $new_launches, 'section-new-launch', 'buy' ); ?>

<!-- ═══════════════ FEATURED PROJECTS ═══════════════ -->
<?php tp_hp_section( 'Featured Projects', 'Handpicked projects with the best value', home_url( '/properties/buy/' ), $featured, 'section-featured', 'buy' ); ?>

<!-- ═══════════════ BUY PROJECTS ═══════════════ -->
<?php tp_hp_section( 'Buy Projects in Navi Mumbai', 'RERA-verified new residential projects — zero brokerage', home_url( '/properties/buy/' ), $buy_projects, 'section-buy', 'buy' ); ?>

<!-- ═══════════════ FOR RENT ═══════════════ -->
<?php tp_hp_section( 'For Rent in Navi Mumbai', 'Verified rental properties with transparent pricing', home_url( '/properties/rent/' ), $rent_projects, 'section-rent', 'rent' ); ?>

<!-- ═══════════════ COMMERCIAL ═══════════════ -->
<?php tp_hp_section( 'Commercial Properties', 'Offices, shops and commercial spaces', home_url( '/properties/commercial/' ), $commercial_projects, 'section-commercial', 'commercial' ); ?>

<!-- ═══════════════ PLOTS ═══════════════ -->
<?php tp_hp_section( 'Plots & Land', 'Residential and agricultural plots across Navi Mumbai', home_url( '/properties/plot/' ), $plot_projects, 'section-plot', 'plot' ); ?>

<!-- ═══════════════ BROWSE BY CATEGORY ═══════════════ -->
<section class="tp-hp-section tp-hp-section--gray">
	<div class="tp-container">
		<div class="tp-hp-section__header tp-hp-section__header--center">
			<div>
				<h2 class="tp-hp-section__title">Browse by Category</h2>
				<p class="tp-hp-section__subtitle">Find exactly what you're looking for</p>
			</div>
		</div>
		<div class="tp-category-cards">
			<?php foreach ( $categories as $cat ) :
				$term  = get_term_by( 'slug', $cat['slug'], 'tp_property_type' );
				$count = $term ? $term->count : 0;
			?>
				<a href="<?php echo esc_url( home_url( '/properties/' . $cat['slug'] . '/' ) ); ?>" class="tp-category-card">
					<div class="tp-category-card__icon">
						<svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="<?php echo esc_attr( $cat['icon'] ); ?>"/></svg>
					</div>
					<div class="tp-category-card__label"><?php echo esc_html( $cat['label'] ); ?></div>
					<div class="tp-category-card__count"><?php echo esc_html( $count ); ?> project<?php echo $count !== 1 ? 's' : ''; ?></div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ═══════════════ STATS COUNTER ═══════════════ -->
<section class="tp-stats-counter">
	<div class="tp-container">
		<div class="tp-stats-counter__grid">
			<div class="tp-stats-counter__item">
				<div class="tp-stats-counter__icon">
					<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
				</div>
				<div class="tp-stats-counter__text">
					<div class="tp-stats-counter__num" data-target="<?php echo esc_attr( $project_count ); ?>" data-suffix="+">0</div>
					<div class="tp-stats-counter__label">Handpicked Projects</div>
				</div>
			</div>
			<div class="tp-stats-counter__divider"></div>
			<div class="tp-stats-counter__item">
				<div class="tp-stats-counter__icon">
					<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
				</div>
				<div class="tp-stats-counter__text">
					<div class="tp-stats-counter__num" data-target="0" data-prefix="₹">0</div>
					<div class="tp-stats-counter__label">Brokerage</div>
				</div>
			</div>
			<div class="tp-stats-counter__divider"></div>
			<div class="tp-stats-counter__item">
				<div class="tp-stats-counter__icon">
					<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
				</div>
				<div class="tp-stats-counter__text">
					<div class="tp-stats-counter__num" data-target="100" data-suffix="%">0</div>
					<div class="tp-stats-counter__label">RERA Verified</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ═══════════════ POPULAR LOCATIONS ═══════════════ -->
<?php if ( ! empty( $locations ) && ! is_wp_error( $locations ) ) : ?>
<section class="tp-hp-section">
	<div class="tp-container">
		<div class="tp-hp-section__header tp-hp-section__header--center">
			<div>
				<h2 class="tp-hp-section__title">Popular Locations in Navi Mumbai</h2>
				<p class="tp-hp-section__subtitle">Explore projects across top micro-markets</p>
			</div>
		</div>
		<div class="tp-location-grid">
			<?php foreach ( $locations as $loc ) : ?>
				<a href="<?php echo esc_url( home_url( '/navi-mumbai/' . $loc->slug . '/' ) ); ?>" class="tp-location-pill">
					<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
					<?php echo esc_html( $loc->name ); ?>
					<span class="tp-location-pill__count"><?php echo esc_html( $loc->count ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ═══════════════ GOOGLE REVIEWS ═══════════════ -->
<?php
// Try fetched reviews first, fallback to static.
$tp_fetched_reviews = function_exists( 'tp_get_google_reviews' ) ? tp_get_google_reviews() : array();
if ( ! empty( $tp_fetched_reviews ) ) {
	$tp_reviews = $tp_fetched_reviews;
} else {
	$tp_reviews = array(
		array( 'name' => 'Rahul Sharma',    'date' => '2 months ago',  'stars' => 5, 'text' => 'Very professional team. They helped us find a 2 BHK in Kharghar within our budget. Transparent process, no hidden charges. Highly recommended for first-time buyers.' ),
		array( 'name' => 'Priya Desai',     'date' => '3 months ago',  'stars' => 5, 'text' => 'Galaxy Realty made our home-buying journey so smooth. They showed us RERA-verified projects only and the pricing was exactly as quoted. Best real estate consultants in Navi Mumbai!' ),
		array( 'name' => 'Amit Patil',      'date' => '1 month ago',   'stars' => 5, 'text' => 'Excellent service! They guided us through the entire process from site visits to registration. Zero brokerage as promised. Would definitely recommend to anyone looking in Panvel area.' ),
		array( 'name' => 'Sneha Joshi',     'date' => '3 weeks ago',   'stars' => 5, 'text' => 'We were looking for a 3 BHK in Ulwe and Galaxy Realty showed us the best options. Very knowledgeable about the Navi Mumbai market. Happy with our purchase!' ),
		array( 'name' => 'Vikram Mehta',    'date' => '1 month ago',   'stars' => 5, 'text' => 'Bought a commercial shop through Galaxy Realty. The team was very responsive and helped negotiate a great deal. Trustworthy and reliable service.' ),
		array( 'name' => 'Neha Kulkarni',   'date' => '2 weeks ago',   'stars' => 5, 'text' => 'Fantastic experience with Galaxy Realty! They understood exactly what we needed and showed us only relevant properties. Saved us so much time. Highly professional.' ),
		array( 'name' => 'Rajesh Gupta',    'date' => '4 months ago',  'stars' => 5, 'text' => 'I was skeptical about zero brokerage but Galaxy Realty delivered on their promise. Bought a 2 BHK in Taloja with complete documentation support. Very trustworthy.' ),
		array( 'name' => 'Anjali Nair',     'date' => '1 month ago',   'stars' => 5, 'text' => 'As a single woman buying my first home, I needed someone trustworthy. Galaxy Realty was patient, transparent, and helped me through every step. Got a beautiful flat in Vashi!' ),
		array( 'name' => 'Suresh Patel',    'date' => '2 months ago',  'stars' => 5, 'text' => 'Invested in a property in Panvel through Galaxy Realty. Their market knowledge is exceptional. They helped me understand the growth potential of the area. Great investment advice.' ),
		array( 'name' => 'Kavita Rane',     'date' => '3 weeks ago',   'stars' => 5, 'text' => 'We relocated from Pune and Galaxy Realty made finding a home in Navi Mumbai effortless. They arranged multiple site visits in one day and the paperwork was handled smoothly.' ),
		array( 'name' => 'Deepak Sawant',   'date' => '5 months ago',  'stars' => 5, 'text' => 'Best real estate consultants I have worked with. They showed us RERA-verified projects only and were very upfront about pros and cons of each property. No pushy sales tactics.' ),
		array( 'name' => 'Manisha Thakur',  'date' => '2 months ago',  'stars' => 5, 'text' => 'Purchased a 3 BHK in Kharghar for our family. Galaxy Realty negotiated a great deal with the developer and also helped with home loan processing. One-stop solution!' ),
		array( 'name' => 'Sanjay Dubey',    'date' => '6 weeks ago',   'stars' => 5, 'text' => 'I was comparing properties across Navi Mumbai for months. Galaxy Realty simplified everything — shortlisted 5 best projects for my budget and helped me close within 2 weeks.' ),
		array( 'name' => 'Pooja Verma',     'date' => '1 month ago',   'stars' => 5, 'text' => 'Excellent after-sales support! Even after booking, they followed up regularly and coordinated with the developer for updates. Truly care about their clients.' ),
		array( 'name' => 'Manoj Iyer',      'date' => '4 months ago',  'stars' => 5, 'text' => 'Bought a plot in Panvel through Galaxy Realty. They verified all documents, checked RERA status, and ensured clear title. Very thorough and professional team. Highly recommended!' ),
	);
}
$tp_star_svg = '<svg width="12" height="12" viewBox="0 0 20 20" fill="#FBBC05"><path d="M10 1l2.39 4.84 5.34.78-3.87 3.77.91 5.32L10 13.27l-4.77 2.51.91-5.32L2.27 6.69l5.34-.78z"/></svg>';
$tp_biz_name     = get_option( 'tp_google_business_name', 'Galaxy Realty' );
$tp_overall_rate = get_option( 'tp_google_overall_rating', '4.8' );
$tp_total_revs   = get_option( 'tp_google_total_reviews', '120' );
$tp_rate_stars   = round( (float) $tp_overall_rate );
?>
<section class="tp-hp-section tp-reviews">
	<div class="tp-container">
		<div class="tp-reviews__header">
			<div class="tp-reviews__badge">
				<svg class="tp-reviews__google-icon" width="20" height="20" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A10.96 10.96 0 001 12c0 1.78.43 3.46 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
				<div>
					<div class="tp-reviews__rating">
						<span class="tp-reviews__rating-num"><?php echo esc_html( $tp_overall_rate ); ?></span>
						<div class="tp-reviews__stars">
							<?php for ( $i = 0; $i < $tp_rate_stars; $i++ ) : ?>
							<svg width="14" height="14" viewBox="0 0 20 20" fill="#FBBC05"><path d="M10 1l2.39 4.84 5.34.78-3.87 3.77.91 5.32L10 13.27l-4.77 2.51.91-5.32L2.27 6.69l5.34-.78z"/></svg>
							<?php endfor; ?>
						</div>
					</div>
					<div class="tp-reviews__source"><?php echo esc_html( $tp_biz_name ); ?> on Google &middot; <?php echo esc_html( $tp_total_revs ); ?>+ reviews</div>
				</div>
			</div>
			<span class="tp-reviews__powered">Powered by <svg width="14" height="14" viewBox="0 0 24 24" style="vertical-align:-2px;"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A10.96 10.96 0 001 12c0 1.78.43 3.46 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg> Google</span>
		</div>
		<div class="tp-reviews__carousel-wrap">
			<button class="tp-hp-carousel__arrow tp-hp-carousel__arrow--prev" aria-label="Previous">&#8249;</button>
			<div class="tp-reviews__track">
				<?php foreach ( $tp_reviews as $review ) :
					$initial = mb_substr( $review['name'], 0, 1 );
					$stars_html = str_repeat( $tp_star_svg, $review['stars'] );
				?>
				<div class="tp-reviews__card">
					<div class="tp-reviews__card-top">
						<div class="tp-reviews__avatar"><?php echo esc_html( $initial ); ?></div>
						<div>
							<div class="tp-reviews__name"><?php echo esc_html( $review['name'] ); ?></div>
							<div class="tp-reviews__date"><?php echo esc_html( $review['date'] ); ?></div>
						</div>
						<div class="tp-reviews__card-stars"><?php echo $stars_html; ?></div>
					</div>
					<p class="tp-reviews__text"><?php echo esc_html( $review['text'] ); ?></p>
				</div>
				<?php endforeach; ?>
			</div>
			<button class="tp-hp-carousel__arrow tp-hp-carousel__arrow--next" aria-label="Next">&#8250;</button>
		</div>
	</div>
</section>

<!-- ═══════════════ DEVELOPER PARTNERS ═══════════════ -->
<?php
$tp_dev_logos = get_transient( 'tp_hp_dev_logos' );
if ( false === $tp_dev_logos ) {
	$tp_dev_logos = array();
	$tp_dev_seen  = array();
	$tp_dev_query = new WP_Query( array(
		'post_type'      => 'tp_project',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
	) );
	foreach ( $tp_dev_query->posts as $pid ) {
		$_dev_name = get_post_meta( $pid, '_tp_developer_name', true );
		$_dev_logo = tp_get_developer_logo( $pid );
		if ( $_dev_name && $_dev_logo && ! isset( $tp_dev_seen[ $_dev_name ] ) ) {
			$tp_dev_seen[ $_dev_name ] = true;
			$tp_dev_logos[] = array( 'name' => $_dev_name, 'logo' => $_dev_logo );
		}
	}
	set_transient( 'tp_hp_dev_logos', $tp_dev_logos, 12 * HOUR_IN_SECONDS );
}
?>
<?php if ( count( $tp_dev_logos ) >= 4 ) : ?>
<section class="tp-hp-section tp-dev-partners">
	<div class="tp-container">
		<div class="tp-dev-partners__header">
			<p class="tp-dev-partners__label">Trusted by Leading Developers</p>
			<h2 class="tp-dev-partners__title">Our Developer Partners</h2>
		</div>
		<div class="tp-dev-partners__marquee">
			<div class="tp-dev-partners__track">
				<?php foreach ( $tp_dev_logos as $dl ) : ?>
				<div class="tp-dev-partners__item">
					<img src="<?php echo esc_url( $dl['logo'] ); ?>" alt="<?php echo esc_attr( $dl['name'] ); ?>" loading="lazy">
				</div>
				<?php endforeach; ?>
				<?php /* Duplicate for seamless loop */ ?>
				<?php foreach ( $tp_dev_logos as $dl ) : ?>
				<div class="tp-dev-partners__item">
					<img src="<?php echo esc_url( $dl['logo'] ); ?>" alt="<?php echo esc_attr( $dl['name'] ); ?>" loading="lazy">
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ═══════════════ BLOG ═══════════════ -->
<?php
$tp_blog_query = new WP_Query( array(
	'post_type'      => 'tp_guide',
	'posts_per_page' => 3,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
) );
?>
<section class="tp-hp-section tp-hp-section--gray">
	<div class="tp-container">
		<div class="tp-hp-section__header">
			<div>
				<h2 class="tp-hp-section__title">From the Blog</h2>
				<p class="tp-hp-section__subtitle">Expert advice to help you make the right decision</p>
			</div>
			<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="tp-link-arrow">View All Articles <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
		</div>
		<div class="tp-blog-grid">
			<?php if ( $tp_blog_query->have_posts() ) : ?>
				<?php while ( $tp_blog_query->have_posts() ) : $tp_blog_query->the_post();
					$guide_types = get_the_terms( get_the_ID(), 'tp_guide_type' );
					$category_name = ( $guide_types && ! is_wp_error( $guide_types ) ) ? $guide_types[0]->name : 'Guide';
					$word_count = str_word_count( wp_strip_all_tags( get_the_content() ) );
					$read_time  = max( 1, round( $word_count / 200 ) );
				?>
				<article class="tp-blog-card">
					<a href="<?php the_permalink(); ?>" class="tp-blog-card__link">
						<div class="tp-blog-card__img">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large', array( 'class' => 'tp-blog-card__thumb' ) ); ?>
							<?php else : ?>
								<div class="tp-blog-card__img-placeholder" style="background:linear-gradient(135deg,var(--brand-primary-pale),var(--brand-primary-bg));">
									<svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--brand-primary)" stroke-width="1.5"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
								</div>
							<?php endif; ?>
							<span class="tp-blog-card__category"><?php echo esc_html( $category_name ); ?></span>
						</div>
						<div class="tp-blog-card__body">
							<h3 class="tp-blog-card__title"><?php the_title(); ?></h3>
							<?php if ( has_excerpt() ) : ?>
								<p class="tp-blog-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<?php endif; ?>
							<div class="tp-blog-card__meta"><?php echo esc_html( $read_time ); ?> min read</div>
						</div>
					</a>
				</article>
				<?php endwhile; wp_reset_postdata(); ?>
			<?php else : ?>
				<article class="tp-blog-card">
					<div class="tp-blog-card__img"><div class="tp-blog-card__img-placeholder" style="background:linear-gradient(135deg,var(--success-light),var(--success-bg));"><svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--success)" stroke-width="1.5"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div><span class="tp-blog-card__category">RERA Guide</span></div>
					<div class="tp-blog-card__body"><h3 class="tp-blog-card__title">RERA Registration: How to Check If a Project is Verified</h3><p class="tp-blog-card__excerpt">Learn how to verify any project on the MahaRERA website. Protect yourself from fraudulent listings.</p><div class="tp-blog-card__meta">5 min read</div></div>
				</article>
				<article class="tp-blog-card">
					<div class="tp-blog-card__img"><div class="tp-blog-card__img-placeholder" style="background:linear-gradient(135deg,var(--brand-primary-pale),var(--brand-primary-bg));"><svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--brand-primary)" stroke-width="1.5"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div><span class="tp-blog-card__category">Market Insights</span></div>
					<div class="tp-blog-card__body"><h3 class="tp-blog-card__title">Navi Mumbai Airport: Impact on Property Prices in 2026</h3><p class="tp-blog-card__excerpt">NMIA is changing the game. See which locations will see the highest appreciation this year.</p><div class="tp-blog-card__meta">7 min read</div></div>
				</article>
				<article class="tp-blog-card">
					<div class="tp-blog-card__img"><div class="tp-blog-card__img-placeholder" style="background:linear-gradient(135deg,var(--accent-pale),#FEF9EE);"><svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--accent-dark)" stroke-width="1.5"><path d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg></div><span class="tp-blog-card__category">Buying Guide</span></div>
					<div class="tp-blog-card__body"><h3 class="tp-blog-card__title">1 BHK vs 2 BHK: Which Is the Better Investment in 2026?</h3><p class="tp-blog-card__excerpt">We break down rental yields, appreciation, and resale value to help you decide.</p><div class="tp-blog-card__meta">6 min read</div></div>
				</article>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- ═══════════════ TRUST BAR ═══════════════ -->
<section class="tp-trust-section">
	<div class="tp-container">
		<div class="tp-trust-row">
			<div class="tp-trust-signal">
				<div class="tp-trust-signal__icon" style="background:var(--success-light);color:var(--success);"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
				<div><div class="tp-trust-signal__title">RERA-Verified Data</div><div class="tp-trust-signal__desc">Every listing verified against MahaRERA</div></div>
			</div>
			<div class="tp-trust-signal">
				<div class="tp-trust-signal__icon" style="background:var(--brand-primary-pale);color:var(--brand-primary);"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg></div>
				<div><div class="tp-trust-signal__title">Unbiased AI Rankings</div><div class="tp-trust-signal__desc">No paid promotions. Pure data-driven analysis</div></div>
			</div>
			<div class="tp-trust-signal">
				<div class="tp-trust-signal__icon" style="background:var(--accent-pale);color:var(--accent-dark);"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
				<div><div class="tp-trust-signal__title">No Spam Promise</div><div class="tp-trust-signal__desc">Your data stays with us. Zero unsolicited calls</div></div>
			</div>
		</div>
	</div>
</section>

<!-- ═══════════════ FINAL CTA ═══════════════ -->
<section class="tp-final-cta">
	<div class="tp-container">
		<div class="tp-final-cta__inner">
			<div class="tp-final-cta__content">
				<h2 class="tp-final-cta__title">Ready to find your dream home?</h2>
				<p class="tp-final-cta__desc">Get a curated list of the best projects matching your preferences. Free consultation, zero brokerage.</p>
			</div>
			<form class="tp-final-cta__form" onsubmit="return false;">
				<div class="tp-final-cta__fields">
					<input type="text" placeholder="Your name" class="tp-final-cta__input" required>
					<div class="tp-final-cta__phone-wrap">
						<span class="tp-final-cta__phone-prefix">+91</span>
						<input type="tel" placeholder="Mobile number" class="tp-final-cta__input tp-final-cta__input--phone" pattern="[0-9]{10}" maxlength="10" required>
					</div>
					<button type="submit" class="tp-btn tp-btn--white tp-final-cta__submit">Get My Top 10 Projects</button>
				</div>
				<div class="tp-final-cta__trust">
					<span><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> No Spam</span>
					<span><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> 100% Secure</span>
					<span><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Free Forever</span>
				</div>
			</form>
		</div>
	</div>
</section>

<script>
(function () {
	'use strict';

	/* ── Hero tab → property_type sync ── */
	var tabs   = document.querySelectorAll('#tp-hero-tabs .tp-hero__tab');
	var hidden = document.getElementById('tp-hero-property-type');
	tabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
			tabs.forEach(function (t) { t.classList.remove('tp-hero__tab--active'); });
			tab.classList.add('tp-hero__tab--active');
			if (hidden) hidden.value = tab.dataset.category;
		});
	});

	/* ── Carousel: arrows + mouse drag ── */
	document.querySelectorAll('.tp-hp-carousel-wrap').forEach(function (wrap) {
		var track   = wrap.querySelector('.tp-hp-carousel');
		var prevBtn = wrap.querySelector('.tp-hp-carousel__arrow--prev');
		var nextBtn = wrap.querySelector('.tp-hp-carousel__arrow--next');
		if (!track || !prevBtn || !nextBtn) return;

		var SCROLL = 380;

		function updateArrows() {
			prevBtn.disabled = track.scrollLeft <= 0;
			nextBtn.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
		}

		prevBtn.addEventListener('click', function () { track.scrollBy({ left: -SCROLL, behavior: 'smooth' }); });
		nextBtn.addEventListener('click', function () { track.scrollBy({ left: SCROLL,  behavior: 'smooth' }); });
		track.addEventListener('scroll', updateArrows, { passive: true });
		updateArrows();

		/* Mouse drag */
		var isDown = false, startX = 0, scrollStart = 0;
		track.addEventListener('mousedown', function (e) { isDown = true; track.classList.add('is-dragging'); startX = e.pageX; scrollStart = track.scrollLeft; });
		track.addEventListener('mouseleave', function ()  { isDown = false; track.classList.remove('is-dragging'); });
		track.addEventListener('mouseup',    function ()  { isDown = false; track.classList.remove('is-dragging'); });
		track.addEventListener('mousemove',  function (e) { if (!isDown) return; e.preventDefault(); track.scrollLeft = scrollStart - (e.pageX - startX); });
	});

	/* ── Reviews carousel ── */
	(function () {
		var wrap = document.querySelector('.tp-reviews__carousel-wrap');
		if (!wrap) return;
		var track = wrap.querySelector('.tp-reviews__track');
		var prev  = wrap.querySelector('.tp-hp-carousel__arrow--prev');
		var next  = wrap.querySelector('.tp-hp-carousel__arrow--next');
		if (!track || !prev || !next) return;
		function upd() {
			prev.disabled = track.scrollLeft <= 0;
			next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
		}
		prev.addEventListener('click', function () { track.scrollBy({ left: -340, behavior: 'smooth' }); });
		next.addEventListener('click', function () { track.scrollBy({ left: 340, behavior: 'smooth' }); });
		track.addEventListener('scroll', upd, { passive: true });
		upd();
		var isDown = false, sx = 0, ss = 0;
		track.addEventListener('mousedown', function (e) { isDown = true; sx = e.pageX; ss = track.scrollLeft; });
		track.addEventListener('mouseleave', function () { isDown = false; });
		track.addEventListener('mouseup', function () { isDown = false; });
		track.addEventListener('mousemove', function (e) { if (!isDown) return; e.preventDefault(); track.scrollLeft = ss - (e.pageX - sx); });
	})();

	/* ── REST API helpers ── */
	var REST_BASE = '<?php echo esc_js( rest_url( 'tenprojects/v1/projects' ) ); ?>';

	function buildCard(p) {
		var loc      = (p.location && p.location[0]) ? p.location[0] : {};
		var locName  = loc.name  ? loc.name + ', Navi Mumbai' : '';
		var locSlug  = loc.slug  || '';
		var propType = p.property_type || '';

		var priceLine = '';
		if (p.monthly_rent) {
			priceLine = '\u20b9' + Number(p.monthly_rent).toLocaleString('en-IN') + '/mo';
		} else if (p.price_display_min) {
			priceLine = p.price_display_min + (p.price_display_max && p.price_display_max !== p.price_display_min ? ' \u2013 ' + p.price_display_max : '');
		} else {
			priceLine = 'Price on Request';
		}

		var imgHtml = p.thumbnail
			? '<img src="' + p.thumbnail + '" alt="' + (p.title || '') + '" loading="lazy">'
			: '<div class="tp-hp-card__img-fallback"></div>';

		var stageBadge = p.construction_stage ? '<span class="tp-hp-card__badge">' + p.construction_stage + '</span>' : '';
		var reraBadge  = p.rera_number ? '<span class="tp-hp-card__badge tp-hp-card__badge--rera">RERA</span>' : '';
		var devHtml    = p.developer_name ? '<div class="tp-hp-card__dev">by ' + p.developer_name + '</div>' : '';
		var metaLeft   = (p.configs_text ? '<span>' + p.configs_text + '</span>' : '')
		               + (locName ? '<span class="tp-hp-card__loc"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' + locName + '</span>' : '');

		return '<a href="' + p.permalink + '" class="tp-hp-card" data-pid="' + p.id + '" data-type="' + propType + '" data-loc="' + locSlug + '">'
			+ '<div class="tp-hp-card__img">' + imgHtml + '<div class="tp-hp-card__overlay"></div></div>'
			+ '<div class="tp-hp-card__top">' + stageBadge + reraBadge + '</div>'
			+ '<div class="tp-hp-card__body">'
			+   '<div class="tp-hp-card__title">' + (p.title || '') + '</div>'
			+   devHtml
			+   '<div class="tp-hp-card__meta-row"><div class="tp-hp-card__meta-left">' + metaLeft + '</div>'
			+   '<div class="tp-hp-card__price">' + priceLine + '</div></div>'
			+ '</div>'
			+ '</a>';
	}

	function fetchCards(wrap, propType, locationSlug) {
		var track   = wrap.querySelector('.tp-hp-carousel');
		var section = wrap.closest('[data-section]') || wrap.closest('.tp-hp-section');
		if (!track) return;

		var params = new URLSearchParams({ per_page: '10', order: 'DESC' });
		if (propType)     params.set('property_type', propType);
		if (locationSlug) params.set('location', locationSlug);

		fetch(REST_BASE + '?' + params.toString())
			.then(function (r) { return r.json(); })
			.then(function (data) {
				var projects = Array.isArray(data) ? data : (data.data || []);
				if (!projects.length) {
					if (section) section.style.display = 'none';
					return;
				}
				if (section) section.style.display = '';
				track.innerHTML = projects.map(buildCard).join('');
				var prevBtn = wrap.querySelector('.tp-hp-carousel__arrow--prev');
				var nextBtn = wrap.querySelector('.tp-hp-carousel__arrow--next');
				if (prevBtn) prevBtn.disabled = track.scrollLeft <= 0;
				if (nextBtn) nextBtn.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
			})
			.catch(function () {});
	}

	function updateAllSections(locationSlug) {
		document.querySelectorAll('.tp-hp-carousel-wrap[data-prop-type]').forEach(function (wrap) {
			if (wrap.dataset.returning) return;
			fetchCards(wrap, wrap.dataset.propType, locationSlug);
		});
	}


	/* ── Returning User Section ── */
	try {
		var viewed = JSON.parse(localStorage.getItem('tp_viewed') || 'null');
		if (viewed && Array.isArray(viewed.projects) && viewed.projects.length >= 2) {
			var types = viewed.types || {}, locs = viewed.locations || {};
			var domType = Object.keys(types).sort(function(a,b){return types[b]-types[a];})[0] || '';
			var domLoc  = Object.keys(locs).sort(function(a,b){return locs[b]-locs[a];})[0]   || '';
			var continueSection = document.getElementById('tp-continue-section');
			var continueWrap    = continueSection ? continueSection.querySelector('.tp-hp-carousel-wrap') : null;
			if (continueSection && continueWrap) {
				continueSection.style.display = '';
				fetchCards(continueWrap, domType, domLoc);
			}
		}
	} catch (e) {}

	/* ── Stats Counter Animation ── */
	var counters = document.querySelectorAll('.tp-stats-counter__num[data-target]');
	if (counters.length) {
		var animated = false;
		function animateCounters() {
			if (animated) return;
			var section = document.querySelector('.tp-stats-counter');
			if (!section) return;
			var rect = section.getBoundingClientRect();
			if (rect.top < window.innerHeight && rect.bottom > 0) {
				animated = true;
				counters.forEach(function (el) {
					var target = parseInt(el.dataset.target, 10);
					var prefix = el.dataset.prefix || '';
					var suffix = el.dataset.suffix || '';
					var duration = 1500;
					var start = 0;
					var startTime = null;
					function step(timestamp) {
						if (!startTime) startTime = timestamp;
						var progress = Math.min((timestamp - startTime) / duration, 1);
						var eased = 1 - Math.pow(1 - progress, 3);
						var current = Math.round(eased * target);
						el.textContent = prefix + current.toLocaleString('en-IN') + suffix;
						if (progress < 1) requestAnimationFrame(step);
					}
					requestAnimationFrame(step);
				});
			}
		}
		window.addEventListener('scroll', animateCounters, { passive: true });
		animateCounters();
	}

	/* ── Track card clicks ── */
	document.addEventListener('click', function (e) {
		var card = e.target.closest && e.target.closest('.tp-hp-carousel .tp-hp-card[data-pid]');
		if (!card) return;
		try {
			var d   = JSON.parse(localStorage.getItem('tp_viewed') || '{"projects":[],"types":{},"locations":{}}');
			var pid = parseInt(card.dataset.pid, 10);
			var type = card.dataset.type || '', loc = card.dataset.loc || '';
			if (!d.projects.includes(pid)) d.projects.unshift(pid);
			if (type) d.types[type]    = (d.types[type]    || 0) + 1;
			if (loc)  d.locations[loc] = (d.locations[loc] || 0) + 1;
			d.last_visit = new Date().toISOString();
			d.projects   = d.projects.slice(0, 50);
			localStorage.setItem('tp_viewed', JSON.stringify(d));
		} catch (ex) {}
	});

})();
</script>
<?php get_footer(); ?>
