<?php
/**
 * Location Area Taxonomy Archive Template
 *
 * Renders a listing page for each location (Kharghar, Panvel, Ulwe, etc.).
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$term       = get_queried_object();
$term_name  = $term->name;
$term_slug  = $term->slug;
$term_count = $term->count;

get_header();
?>

<main class="tp-category-page">

	<?php get_template_part( 'partials/filter-bar' ); ?>

	<!-- Breadcrumbs -->
	<div class="tp-breadcrumbs" style="padding: var(--lg) 0;">
		<div class="tp-container">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
			<span class="tp-breadcrumb-sep">›</span>
			<a href="<?php echo esc_url( home_url( '/navi-mumbai/' ) ); ?>">Navi Mumbai</a>
			<span class="tp-breadcrumb-sep">›</span>
			<span><?php echo esc_html( $term_name ); ?></span>
		</div>
	</div>

	<div class="tp-container">

		<!-- Page Header -->
		<div class="tp-category-header">
			<h1>Projects in <?php echo esc_html( $term_name ); ?>, Navi Mumbai</h1>
			<p class="tp-category-count"><?php echo esc_html( $term_count ); ?> project<?php echo $term_count !== 1 ? 's' : ''; ?> available</p>
		</div>

		<?php if ( have_posts() ) : ?>

			<div class="tp-category-grid">
				<?php while ( have_posts() ) : the_post();
					$p_id       = get_the_ID();
					$p_thumb    = get_the_post_thumbnail_url( $p_id, 'medium' );
					$p_dev      = tp_get_meta( $p_id, 'developer_name' ) ?: '';
					$p_min      = intval( tp_get_meta( $p_id, 'price_display_min' ) );
					$p_max      = intval( tp_get_meta( $p_id, 'price_display_max' ) );
					$p_configs  = tp_get_meta( $p_id, 'available_configs_text' );
					$p_stage    = tp_get_meta( $p_id, 'construction_stage' );
				?>
					<div class="tp-project-card">
						<div class="tp-project-card__img">
							<?php if ( $p_thumb ) : ?>
								<img src="<?php echo esc_url( $p_thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
							<?php else : ?>
								<div style="width:100%;height:100%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--gray-400);">
									<svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
								</div>
							<?php endif; ?>
							<?php if ( $p_dev ) : ?>
								<span style="position:absolute;top:8px;left:8px;background:rgba(0,0,0,0.6);color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;"><?php echo esc_html( $p_dev ); ?></span>
							<?php endif; ?>
							<?php if ( $p_stage ) : ?>
								<span style="position:absolute;top:8px;right:8px;background:<?php echo $p_stage === 'Ready to Move' ? 'var(--success)' : 'var(--brand-primary)'; ?>;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;"><?php echo esc_html( $p_stage ); ?></span>
							<?php endif; ?>
						</div>
						<div class="tp-project-card__body">
							<div class="tp-project-card__title">
								<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
							</div>
							<div class="tp-project-card__location"><?php echo esc_html( $term_name ); ?>, Navi Mumbai</div>
							<?php if ( $p_min ) : ?>
								<div class="tp-project-card__price"><?php echo esc_html( tp_format_price_range( $p_min, $p_max ) ); ?></div>
							<?php endif; ?>
							<?php if ( $p_configs ) : ?>
								<div class="tp-project-card__meta"><?php echo esc_html( $p_configs ); ?></div>
							<?php endif; ?>
						</div>
					</div>
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
					<path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
					<path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
				</svg>
				<h3>No projects in <?php echo esc_html( $term_name ); ?> yet</h3>
				<p>We're adding new projects regularly. Check back soon!</p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-btn tp-btn--primary">Back to Home</a>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
