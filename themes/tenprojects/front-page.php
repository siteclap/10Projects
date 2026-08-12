<?php
/**
 * Homepage Template — Polished version
 *
 * Dark hero with live badge, category tabs, search, stats,
 * featured projects, how-it-works, why-us, locations, blog, trust, CTA.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

// Featured projects.
$featured = get_posts( array(
	'post_type'      => 'tp_project',
	'posts_per_page' => 6,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

// Category definitions.
$categories = array(
	array( 'slug' => 'buy',        'label' => 'Buy',        'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' ),
	array( 'slug' => 'rent',       'label' => 'Rent',       'icon' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z' ),
	array( 'slug' => 'commercial', 'label' => 'Commercial', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' ),
	array( 'slug' => 'resale',     'label' => 'Resale',     'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15' ),
	array( 'slug' => 'plot',       'label' => 'Plot',       'icon' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7' ),
);

// Locations.
$locations = get_terms( array(
	'taxonomy'   => 'tp_location_area',
	'hide_empty' => false,
	'number'     => 12,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );

$project_count = wp_count_posts( 'tp_project' )->publish ?: 0;

get_header();
?>

<!-- ═══════════════ HERO ═══════════════ -->
<section class="tp-hero" style="background-image:url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-banner.png' ); ?>');">
	<div class="tp-hero__content">
		<!-- Live Badge -->
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

		<!-- Popular Locations -->
		<?php if ( ! empty( $locations ) && ! is_wp_error( $locations ) ) : ?>
			<div class="tp-hero__locations">
				<span>Popular:</span>
				<?php foreach ( array_slice( $locations, 0, 6 ) as $loc ) : ?>
					<a href="<?php echo esc_url( home_url( '/navi-mumbai/' . $loc->slug . '/' ) ); ?>"><?php echo esc_html( $loc->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Stats inside hero -->
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

<!-- ═══════════════ PROJECTS NEAR YOU (Geolocation) ═══════════════ -->
<section class="tp-nearby" id="tp-nearby" style="display:none;">
	<div class="tp-container">
		<div class="tp-hp-section__header">
			<div>
				<h2 class="tp-hp-section__title">Projects Near <span id="tp-nearby-location">You</span></h2>
				<p class="tp-hp-section__subtitle">Based on your current location</p>
			</div>
			<a href="#" id="tp-nearby-link" class="tp-link-arrow">See All <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
		</div>
		<div class="tp-category-grid" id="tp-nearby-grid">
			<!-- Filled by nearby.js -->
		</div>
	</div>
</section>

<!-- ═══════════════ FEATURED PROJECTS ═══════════════ -->
<?php if ( ! empty( $featured ) ) : ?>
<section class="tp-hp-section">
	<div class="tp-container">
		<div class="tp-hp-section__header">
			<div>
				<h2 class="tp-hp-section__title">Featured Projects</h2>
				<p class="tp-hp-section__subtitle">Handpicked projects with the best value in Navi Mumbai</p>
			</div>
			<a href="<?php echo esc_url( home_url( '/properties/buy/' ) ); ?>" class="tp-link-arrow">View All <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
		</div>

		<div class="tp-category-grid">
			<?php foreach ( $featured as $p ) :
				$p_id       = $p->ID;
				$p_thumb    = get_the_post_thumbnail_url( $p_id, 'medium' );
				$p_dev      = tp_get_meta( $p_id, 'developer_name' ) ?: '';
				$p_loc      = tp_get_location_term( $p_id );
				$p_loc_name = $p_loc ? $p_loc->name : '';
				$p_min      = intval( tp_get_meta( $p_id, 'price_display_min' ) );
				$p_max      = intval( tp_get_meta( $p_id, 'price_display_max' ) );
				$p_configs  = tp_get_meta( $p_id, 'available_configs_text' );
				$p_stage    = tp_get_meta( $p_id, 'construction_stage' );
				$p_rera     = tp_get_meta( $p_id, 'rera_number' );
			?>
				<a href="<?php echo esc_url( get_permalink( $p_id ) ); ?>" class="tp-card">
					<div class="tp-card__img">
						<?php if ( $p_thumb ) : ?>
							<img src="<?php echo esc_url( $p_thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $p_id ) ); ?>" loading="lazy">
						<?php else : ?>
							<div class="tp-card__img-placeholder">
								<svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
							</div>
						<?php endif; ?>
						<?php if ( $p_dev ) : ?>
							<span class="tp-card__badge-dev"><?php echo esc_html( $p_dev ); ?></span>
						<?php endif; ?>
						<?php if ( $p_stage ) : ?>
							<span class="tp-card__badge-stage <?php echo $p_stage === 'Ready to Move' ? 'tp-card__badge-stage--ready' : ''; ?>"><?php echo esc_html( $p_stage ); ?></span>
						<?php endif; ?>
					</div>
					<div class="tp-card__body">
						<div class="tp-card__title"><?php echo esc_html( get_the_title( $p_id ) ); ?></div>
						<div class="tp-card__location">
							<svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
							<?php echo esc_html( $p_loc_name ); ?>, Navi Mumbai
						</div>
						<div class="tp-card__footer">
							<?php if ( $p_min ) : ?>
								<div class="tp-card__price"><?php echo esc_html( tp_format_price_range( $p_min, $p_max ) ); ?></div>
							<?php endif; ?>
							<?php if ( $p_configs ) : ?>
								<div class="tp-card__config"><?php echo esc_html( $p_configs ); ?></div>
							<?php endif; ?>
						</div>
						<?php if ( $p_rera ) : ?>
							<div class="tp-card__rera">
								<svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="var(--success)" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
								RERA Verified
							</div>
						<?php endif; ?>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ═══════════════ HOW IT WORKS ═══════════════ -->
<section class="tp-hp-section tp-hp-section--gray">
	<div class="tp-container">
		<div class="tp-hp-section__header tp-hp-section__header--center">
			<div>
				<h2 class="tp-hp-section__title">How It Works</h2>
				<p class="tp-hp-section__subtitle">Find your dream home in 3 simple steps</p>
			</div>
		</div>

		<div class="tp-steps">
			<div class="tp-step">
				<div class="tp-step__number">1</div>
				<div class="tp-step__icon">
					<svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
				</div>
				<h3 class="tp-step__title">Search Projects</h3>
				<p class="tp-step__desc">Browse by location, budget, BHK, or use our AI-powered search to find the perfect match.</p>
			</div>
			<div class="tp-step__connector"></div>
			<div class="tp-step">
				<div class="tp-step__number">2</div>
				<div class="tp-step__icon">
					<svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
				</div>
				<h3 class="tp-step__title">Compare & Shortlist</h3>
				<p class="tp-step__desc">View RERA-verified details, floor plans, prices, pros & cons — all in one place.</p>
			</div>
			<div class="tp-step__connector"></div>
			<div class="tp-step">
				<div class="tp-step__number">3</div>
				<div class="tp-step__icon">
					<svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
				</div>
				<h3 class="tp-step__title">Book a Free Visit</h3>
				<p class="tp-step__desc">Schedule a free site visit with cab pickup. No brokerage. No spam. Just honest advice.</p>
			</div>
		</div>
	</div>
</section>

<!-- ═══════════════ WHY LEADMAAXX ═══════════════ -->
<section class="tp-hp-section">
	<div class="tp-container">
		<div class="tp-hp-section__header tp-hp-section__header--center">
			<div>
				<h2 class="tp-hp-section__title">Why Buy from LeadMAAXX?</h2>
				<p class="tp-hp-section__subtitle">We're not just another property portal</p>
			</div>
		</div>

		<div class="tp-features">
			<div class="tp-feature">
				<div class="tp-feature__icon" style="background:var(--brand-primary-pale);color:var(--brand-primary);">
					<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
				</div>
				<h3 class="tp-feature__title">Smart Search</h3>
				<p class="tp-feature__desc">AI-powered search that understands your preferences and finds the best-fit projects instantly.</p>
			</div>
			<div class="tp-feature">
				<div class="tp-feature__icon" style="background:var(--success-light);color:var(--success);">
					<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
				</div>
				<h3 class="tp-feature__title">RERA-Verified Data</h3>
				<p class="tp-feature__desc">Every project is verified against MahaRERA. No fake listings, no inflated prices.</p>
			</div>
			<div class="tp-feature">
				<div class="tp-feature__icon" style="background:var(--accent-pale);color:var(--accent-dark);">
					<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
				</div>
				<h3 class="tp-feature__title">Zero Brokerage</h3>
				<p class="tp-feature__desc">Save lakhs on your purchase. We earn from developers, not from you. No hidden charges.</p>
			</div>
			<div class="tp-feature">
				<div class="tp-feature__icon" style="background:var(--brand-primary-pale);color:var(--brand-primary);">
					<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
				</div>
				<h3 class="tp-feature__title">Unbiased Reports</h3>
				<p class="tp-feature__desc">AI-powered analysis with pros, cons, and honest scores. No paid rankings or hidden agendas.</p>
			</div>
			<div class="tp-feature">
				<div class="tp-feature__icon" style="background:var(--success-light);color:var(--success);">
					<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
				</div>
				<h3 class="tp-feature__title">Free Site Visits</h3>
				<p class="tp-feature__desc">Book a free site visit with complimentary cab pickup. See the project before you decide.</p>
			</div>
			<div class="tp-feature">
				<div class="tp-feature__icon" style="background:var(--accent-pale);color:var(--accent-dark);">
					<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
				</div>
				<h3 class="tp-feature__title">No Spam Promise</h3>
				<p class="tp-feature__desc">Your data is safe. We'll never share your number. No 50 calls from random agents.</p>
			</div>
		</div>
	</div>
</section>

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
				$term = get_term_by( 'slug', $cat['slug'], 'tp_property_type' );
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

<!-- ═══════════════ BLOG / GUIDES ═══════════════ -->
<section class="tp-hp-section tp-hp-section--gray">
	<div class="tp-container">
		<div class="tp-hp-section__header">
			<div>
				<h2 class="tp-hp-section__title">From the Blog</h2>
				<p class="tp-hp-section__subtitle">Expert advice to help you make the right decision</p>
			</div>
			<a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="tp-link-arrow">View All Articles <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
		</div>

		<div class="tp-blog-grid">
			<article class="tp-blog-card">
				<div class="tp-blog-card__img">
					<div class="tp-blog-card__img-placeholder" style="background:linear-gradient(135deg,var(--success-light),var(--success-bg));">
						<svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--success)" stroke-width="1.5"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
					</div>
					<span class="tp-blog-card__category">RERA Guide</span>
				</div>
				<div class="tp-blog-card__body">
					<h3 class="tp-blog-card__title">RERA Registration: How to Check If a Project is Verified</h3>
					<p class="tp-blog-card__excerpt">Learn how to verify any project on the MahaRERA website. Protect yourself from fraudulent listings.</p>
					<div class="tp-blog-card__meta">5 min read</div>
				</div>
			</article>

			<article class="tp-blog-card">
				<div class="tp-blog-card__img">
					<div class="tp-blog-card__img-placeholder" style="background:linear-gradient(135deg,var(--brand-primary-pale),var(--brand-primary-bg));">
						<svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--brand-primary)" stroke-width="1.5"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
					</div>
					<span class="tp-blog-card__category">Market Insights</span>
				</div>
				<div class="tp-blog-card__body">
					<h3 class="tp-blog-card__title">Navi Mumbai Airport: Impact on Property Prices in 2026</h3>
					<p class="tp-blog-card__excerpt">NMIA is changing the game. See which locations will see the highest appreciation this year.</p>
					<div class="tp-blog-card__meta">7 min read</div>
				</div>
			</article>

			<article class="tp-blog-card">
				<div class="tp-blog-card__img">
					<div class="tp-blog-card__img-placeholder" style="background:linear-gradient(135deg,var(--accent-pale),#FEF9EE);">
						<svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--accent-dark)" stroke-width="1.5"><path d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
					</div>
					<span class="tp-blog-card__category">Buying Guide</span>
				</div>
				<div class="tp-blog-card__body">
					<h3 class="tp-blog-card__title">1 BHK vs 2 BHK: Which Is the Better Investment in 2026?</h3>
					<p class="tp-blog-card__excerpt">We break down rental yields, appreciation, and resale value to help you decide.</p>
					<div class="tp-blog-card__meta">6 min read</div>
				</div>
			</article>
		</div>
	</div>
</section>

<!-- ═══════════════ TRUST BAR ═══════════════ -->
<section class="tp-trust-section">
	<div class="tp-container">
		<div class="tp-trust-row">
			<div class="tp-trust-signal">
				<div class="tp-trust-signal__icon" style="background:var(--success-light);color:var(--success);">
					<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
				</div>
				<div>
					<div class="tp-trust-signal__title">RERA-Verified Data</div>
					<div class="tp-trust-signal__desc">Every listing verified against MahaRERA</div>
				</div>
			</div>
			<div class="tp-trust-signal">
				<div class="tp-trust-signal__icon" style="background:var(--brand-primary-pale);color:var(--brand-primary);">
					<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
				</div>
				<div>
					<div class="tp-trust-signal__title">Unbiased AI Rankings</div>
					<div class="tp-trust-signal__desc">No paid promotions. Pure data-driven analysis</div>
				</div>
			</div>
			<div class="tp-trust-signal">
				<div class="tp-trust-signal__icon" style="background:var(--accent-pale);color:var(--accent-dark);">
					<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
				</div>
				<div>
					<div class="tp-trust-signal__title">No Spam Promise</div>
					<div class="tp-trust-signal__desc">Your data stays with us. Zero unsolicited calls</div>
				</div>
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
(function(){
	var tabs = document.querySelectorAll('#tp-hero-tabs .tp-hero__tab');
	var hidden = document.getElementById('tp-hero-property-type');
	tabs.forEach(function(tab){
		tab.addEventListener('click', function(){
			tabs.forEach(function(t){ t.classList.remove('tp-hero__tab--active'); });
			tab.classList.add('tp-hero__tab--active');
			hidden.value = tab.dataset.category;
		});
	});
})();
</script>
<?php get_footer(); ?>
