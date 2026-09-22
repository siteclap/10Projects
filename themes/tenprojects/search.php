<?php
/**
 * Search Results Template
 *
 * Shows project search results with the shared listing-card design.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

get_header();
$search_query = get_search_query();
$found        = $wp_query->found_posts;
?>

<main class="tp-archive-page">

	<?php get_template_part( 'partials/filter-bar' ); ?>

	<!-- Breadcrumbs -->
	<div class="tp-archive-breadcrumbs">
		<div class="tp-container">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
			<span class="tp-sep">›</span>
			<span>Search</span>
		</div>
	</div>

	<div class="tp-container">

		<!-- Search Bar (re-search) -->
		<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="tp-archive-search-form">
			<input type="hidden" name="post_type" value="tp_project">
			<div class="tp-archive-search-inner">
				<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
				<input type="text" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Search by project, location or developer…" class="tp-archive-search-input">
				<button type="submit" class="tp-btn tp-btn--primary">Search</button>
			</div>
		</form>

		<!-- Page Header -->
		<div class="tp-archive-header">
			<?php if ( $search_query ) : ?>
				<h1>RERA Verified Properties in <?php echo esc_html( ucwords( $search_query ) ); ?>, Navi Mumbai</h1>
				<p class="tp-archive-header__sub"><?php echo esc_html( $found ); ?> verified project<?php echo $found !== 1 ? 's' : ''; ?> found matching "<?php echo esc_html( $search_query ); ?>".</p>
			<?php else : ?>
				<h1>RERA Verified Properties in Navi Mumbai</h1>
			<?php endif; ?>
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
				<svg width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="var(--gray-300)" stroke-width="1"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
				<h3>No projects found<?php echo $search_query ? ' for "' . esc_html( $search_query ) . '"' : ''; ?></h3>
				<p>Try a different search term or browse by location.</p>
				<div style="display:flex;gap:var(--sm);justify-content:center;flex-wrap:wrap;margin-top:var(--lg);">
					<a href="<?php echo esc_url( home_url( '/navi-mumbai/kharghar/' ) ); ?>" class="tp-btn tp-btn--outline">Kharghar</a>
					<a href="<?php echo esc_url( home_url( '/navi-mumbai/vashi/' ) ); ?>" class="tp-btn tp-btn--outline">Vashi</a>
					<a href="<?php echo esc_url( home_url( '/navi-mumbai/panvel/' ) ); ?>" class="tp-btn tp-btn--outline">Panvel</a>
				</div>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
