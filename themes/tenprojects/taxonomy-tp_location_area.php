<?php
/**
 * Location Area Taxonomy Archive
 *
 * Renders a listing page for each location (Kharghar, Panvel, Vashi, etc.).
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
			<a href="<?php echo esc_url( home_url( '/navi-mumbai/' ) ); ?>">Navi Mumbai</a>
			<span class="tp-sep">›</span>
			<span><?php echo esc_html( $term_name ); ?></span>
		</div>
	</div>

	<div class="tp-container">

		<!-- Page Header -->
		<div class="tp-archive-header">
			<h1>RERA Verified Properties in <?php echo esc_html( $term_name ); ?>, Navi Mumbai</h1>
			<p class="tp-archive-header__sub"><?php echo esc_html( $term_count ); ?> verified project<?php echo $term_count !== 1 ? 's' : ''; ?> available in <?php echo esc_html( $term_name ); ?>.</p>
		</div>

		<?php if ( have_posts() ) : ?>

			<div class="tp-lp-list">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'template-parts/project/listing-card', null, array(
						'post_id'       => get_the_ID(),
						'location_name' => $term_name,
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
				<svg width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="var(--gray-300)" stroke-width="1"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
				<h3>No projects in <?php echo esc_html( $term_name ); ?> yet</h3>
				<p>We're adding new projects regularly. Check back soon!</p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-btn tp-btn--primary">Back to Home</a>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
