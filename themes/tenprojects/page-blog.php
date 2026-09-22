<?php
/**
 * Template Name: Blog
 * Slug: blog
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$brand = get_option( 'tp_brand_name', 'LeadMAAXX' );
$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

$blog_query = new WP_Query( array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 12,
	'paged'          => $paged,
) );

get_header();
?>

<style>
.tp-page-hero {
	background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
	color: #fff;
	padding: 80px 0 60px;
	text-align: center;
}
.tp-page-hero h1 {
	font-size: 36px;
	font-weight: 700;
	margin-bottom: 12px;
}
.tp-page-hero p {
	font-size: 18px;
	opacity: 0.85;
	max-width: 600px;
	margin: 0 auto;
}
.tp-blog-wrap {
	max-width: 1000px;
	margin: 0 auto;
	padding: 48px 24px 80px;
}
.tp-blog-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 28px;
}
.tp-blog-card {
	background: #fff;
	border: 1px solid var(--gray-200);
	border-radius: var(--radius-lg);
	overflow: hidden;
	transition: box-shadow 0.2s, transform 0.2s;
}
.tp-blog-card:hover {
	box-shadow: var(--shadow-hover);
	transform: translateY(-2px);
}
.tp-blog-card-img {
	width: 100%;
	height: 200px;
	object-fit: cover;
	background: var(--gray-100);
}
.tp-blog-card-body {
	padding: 20px 24px 24px;
}
.tp-blog-card-meta {
	font-size: 12px;
	color: var(--gray-400);
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 10px;
}
.tp-blog-card-body h3 {
	font-size: 18px;
	font-weight: 600;
	color: var(--gray-900);
	margin-bottom: 8px;
	line-height: 1.4;
}
.tp-blog-card-body h3 a {
	color: inherit;
	text-decoration: none;
}
.tp-blog-card-body h3 a:hover {
	color: var(--brand-primary);
}
.tp-blog-card-excerpt {
	font-size: 14px;
	color: var(--gray-500);
	line-height: 1.6;
	display: -webkit-box;
	-webkit-line-clamp: 3;
	-webkit-box-orient: vertical;
	overflow: hidden;
}
.tp-blog-empty {
	text-align: center;
	padding: 60px 24px;
}
.tp-blog-empty h2 {
	font-size: 22px;
	font-weight: 600;
	color: var(--gray-800);
	margin-bottom: 12px;
}
.tp-blog-empty p {
	font-size: 16px;
	color: var(--gray-500);
}
.tp-blog-pagination {
	display: flex;
	justify-content: center;
	gap: 8px;
	margin-top: 48px;
}
.tp-blog-pagination a,
.tp-blog-pagination span {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 40px;
	height: 40px;
	padding: 0 12px;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 500;
	text-decoration: none;
	transition: background 0.15s, color 0.15s;
}
.tp-blog-pagination a {
	background: var(--gray-50);
	color: var(--gray-700);
	border: 1px solid var(--gray-200);
}
.tp-blog-pagination a:hover {
	background: var(--brand-primary-bg);
	color: var(--brand-primary);
	border-color: var(--brand-primary);
}
.tp-blog-pagination span.current {
	background: var(--brand-primary);
	color: #fff;
}
</style>

<div class="tp-page-hero">
	<div class="tp-container">
		<h1><?php echo esc_html( $brand ); ?> Blog</h1>
		<p>Insights, guides, and updates on real estate in Navi Mumbai.</p>
	</div>
</div>

<div class="tp-blog-wrap">
	<?php if ( $blog_query->have_posts() ) : ?>
		<div class="tp-blog-grid">
			<?php while ( $blog_query->have_posts() ) : $blog_query->the_post(); ?>
				<article class="tp-blog-card">
					<?php if ( has_post_thumbnail() ) : ?>
						<img class="tp-blog-card-img" src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ) ); ?>" alt="<?php the_title_attribute(); ?>">
					<?php else : ?>
						<div class="tp-blog-card-img" style="display:flex;align-items:center;justify-content:center;">
							<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="<?php echo 'var(--gray-300)'; ?>" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
						</div>
					<?php endif; ?>
					<div class="tp-blog-card-body">
						<div class="tp-blog-card-meta"><?php echo esc_html( get_the_date( 'M d, Y' ) ); ?></div>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="tp-blog-card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php if ( $blog_query->max_num_pages > 1 ) : ?>
			<div class="tp-blog-pagination">
				<?php
				echo paginate_links( array(
					'total'     => $blog_query->max_num_pages,
					'current'   => $paged,
					'prev_text' => '&larr; Prev',
					'next_text' => 'Next &rarr;',
				) );
				?>
			</div>
		<?php endif; ?>

		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<div class="tp-blog-empty">
			<h2>Coming Soon</h2>
			<p>We're working on insightful articles about real estate in Navi Mumbai. Check back soon!</p>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
