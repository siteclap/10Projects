<?php
/**
 * Blog Archive Template (tp_guide CPT)
 *
 * Displays all published guides/blog posts in a card grid layout.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

get_header();

$paged       = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$found       = $wp_query->found_posts;

// Get all guide type terms for trending strip.
$guide_types = get_terms( array(
	'taxonomy'   => 'tp_guide_type',
	'hide_empty' => true,
	'orderby'    => 'count',
	'order'      => 'DESC',
	'number'     => 8,
) );
?>

<main class="tp-blog-archive">

	<!-- Hero -->
	<section class="tp-blog-hero">
		<div class="tp-container">
			<h1 class="tp-blog-hero__title">Real Estate Updates & Resources</h1>
			<p class="tp-blog-hero__sub">Expert insights, market analysis, and buying guides for Navi Mumbai real estate.</p>
		</div>
	</section>

	<!-- Trending / Category Strip -->
	<?php if ( ! empty( $guide_types ) && ! is_wp_error( $guide_types ) ) : ?>
	<section class="tp-blog-trending">
		<div class="tp-container">
			<span class="tp-blog-trending__label">
				<span class="tp-blog-trending__icon">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 23c-3.866 0-7-3.358-7-7.5 0-2.09.702-3.72 1.672-5.312a.5.5 0 01.838.086c.395.744.898 1.44 1.51 2.036-.072-.82.096-1.956.686-3.352a.5.5 0 01.876-.096c.87 1.308 2.043 2.446 3.312 3.418C15.886 14.682 17 13 17 10.5a.5.5 0 01.874-.332C19.235 11.72 20 13.915 20 15.5c0 4.142-3.134 7.5-7 7.5h-1z"/></svg>
				</span>
				Trending Now
			</span>
			<div class="tp-blog-trending__divider"></div>
			<div class="tp-blog-trending__tags">
				<?php foreach ( $guide_types as $gt ) :
					$count = $gt->count;
				?>
					<a href="<?php echo esc_url( get_term_link( $gt ) ); ?>" class="tp-blog-trending__tag">
						<?php echo esc_html( $gt->name ); ?>
						<span class="tp-blog-trending__count"><?php echo esc_html( $count ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- Blog Grid -->
	<section class="tp-blog-list">
		<div class="tp-container">

			<?php if ( have_posts() ) : ?>

			<div class="tp-blog-grid tp-blog-grid--archive">
				<?php while ( have_posts() ) : the_post();
					$guide_terms  = get_the_terms( get_the_ID(), 'tp_guide_type' );
					$cat_name     = ( $guide_terms && ! is_wp_error( $guide_terms ) ) ? $guide_terms[0]->name : 'Blog';
					$word_count   = str_word_count( wp_strip_all_tags( get_the_content() ) );
					$read_time    = max( 1, round( $word_count / 200 ) );
					$author_name  = get_the_author();
				?>
				<article class="tp-blog-card tp-blog-card--archive">
					<a href="<?php the_permalink(); ?>" class="tp-blog-card__link">
						<div class="tp-blog-card__img">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large', array( 'class' => 'tp-blog-card__thumb' ) ); ?>
							<?php else : ?>
								<div class="tp-blog-card__img-placeholder" style="background:linear-gradient(135deg,var(--brand-primary-pale),var(--brand-primary-bg));">
									<svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--brand-primary)" stroke-width="1.5"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
								</div>
							<?php endif; ?>
							<span class="tp-blog-card__category"><?php echo esc_html( $cat_name ); ?></span>
						</div>
						<div class="tp-blog-card__body">
							<h2 class="tp-blog-card__title"><?php the_title(); ?></h2>
							<?php if ( has_excerpt() ) : ?>
								<p class="tp-blog-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<?php endif; ?>
							<div class="tp-blog-card__footer">
								<div class="tp-blog-card__author">
									<?php echo get_avatar( get_the_author_meta( 'ID' ), 24, '', '', array( 'class' => 'tp-blog-card__avatar' ) ); ?>
									<span><?php echo esc_html( $author_name ); ?></span>
								</div>
								<div class="tp-blog-card__meta-row">
									<span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
									<span class="tp-blog-card__dot">&middot;</span>
									<span><?php echo esc_html( $read_time ); ?> min read</span>
								</div>
							</div>
						</div>
					</a>
				</article>
				<?php endwhile; ?>
			</div>

			<!-- Pagination -->
			<div class="tp-pagination">
				<?php the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => '&larr; Previous',
					'next_text' => 'Next &rarr;',
				) ); ?>
			</div>

			<?php else : ?>

			<div class="tp-blog-empty">
				<svg width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="var(--gray-300)" stroke-width="1.5"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
				<h2>No articles yet</h2>
				<p>Check back soon for expert real estate insights and guides.</p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-btn tp-btn--primary">Back to Home</a>
			</div>

			<?php endif; ?>

		</div>
	</section>

</main>

<?php get_footer(); ?>
