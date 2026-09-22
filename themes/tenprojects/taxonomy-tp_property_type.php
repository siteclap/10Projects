<?php
/**
 * Property Type Taxonomy Archive
 *
 * Renders a listing page for each category (Buy, Rent, Commercial, Resale, Plot).
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

<main class="tp-archive-page">

	<?php get_template_part( 'partials/filter-bar' ); ?>

	<!-- Breadcrumbs -->
	<div class="tp-archive-breadcrumbs">
		<div class="tp-container">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
			<span class="tp-sep">›</span>
			<span><?php echo esc_html( $term_name ); ?> Properties</span>
		</div>
	</div>

	<div class="tp-container">

		<!-- Page Header -->
		<div class="tp-archive-header">
			<h1><?php echo esc_html( $term_count ); ?>+ <?php echo esc_html( $term_name ); ?> Properties in Navi Mumbai</h1>
			<p class="tp-archive-header__sub">Browse verified RERA-registered <?php echo esc_html( strtolower( $term_name ) ); ?> projects across Navi Mumbai.</p>
		</div>

		<?php if ( have_posts() ) : ?>

			<div class="tp-lp-list">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'template-parts/project/listing-card', null, array(
						'post_id' => get_the_ID(),
					) ); ?>
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
				<svg width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="var(--gray-300)" stroke-width="1"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
				<h3>No <?php echo esc_html( strtolower( $term_name ) ); ?> properties yet</h3>
				<p>We're adding new projects regularly. Check back soon!</p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-btn tp-btn--primary">Back to Home</a>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
