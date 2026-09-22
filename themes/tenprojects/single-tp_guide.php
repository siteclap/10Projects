<?php
/**
 * Single Blog Post Template (tp_guide CPT)
 *
 * Revaa-style clean layout:
 * - Breadcrumbs
 * - 3-column: share bar | content | sidebar
 * - Content: title, meta, image, TOC, body
 * - Sticky sidebar + sticky share bar
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

get_header();

$post_id     = get_the_ID();
$guide_terms = get_the_terms( $post_id, 'tp_guide_type' );
$cat_name    = ( $guide_terms && ! is_wp_error( $guide_terms ) ) ? $guide_terms[0]->name : 'Blog';
$cat_link    = ( $guide_terms && ! is_wp_error( $guide_terms ) ) ? get_term_link( $guide_terms[0] ) : '';
$author_id   = get_the_author_meta( 'ID' );
$author_name = get_the_author();
$author_bio  = get_the_author_meta( 'description' );
$word_count  = str_word_count( wp_strip_all_tags( get_the_content() ) );
$read_time   = max( 1, round( $word_count / 200 ) );
$thumbnail   = get_the_post_thumbnail_url( $post_id, 'full' );
$content     = apply_filters( 'the_content', get_the_content() );

// Extract headings for Table of Contents.
$toc_items = array();
if ( preg_match_all( '/<h([2-3])[^>]*>(.*?)<\/h[2-3]>/i', $content, $matches, PREG_SET_ORDER ) ) {
	$heading_index = 0;
	foreach ( $matches as $m ) {
		$heading_index++;
		$level    = $m[1];
		$text     = wp_strip_all_tags( $m[2] );
		$anchor   = 'section-' . $heading_index;
		$toc_items[] = array(
			'level'  => $level,
			'text'   => $text,
			'anchor' => $anchor,
		);
		$old_tag  = $m[0];
		$new_tag  = preg_replace( '/<h([2-3])/', '<h$1 id="' . esc_attr( $anchor ) . '"', $old_tag, 1 );
		$content  = str_replace( $old_tag, $new_tag, $content );
	}
}

// Related posts (same guide type, exclude current).
$related_args = array(
	'post_type'      => 'tp_guide',
	'posts_per_page' => 3,
	'post_status'    => 'publish',
	'post__not_in'   => array( $post_id ),
	'orderby'        => 'date',
	'order'          => 'DESC',
);
if ( $guide_terms && ! is_wp_error( $guide_terms ) ) {
	$related_args['tax_query'] = array( array(
		'taxonomy' => 'tp_guide_type',
		'terms'    => $guide_terms[0]->term_id,
	) );
}
$related_query = new WP_Query( $related_args );

// Share URLs.
$share_url   = rawurlencode( get_permalink() );
$share_title = rawurlencode( get_the_title() );
?>

<main class="tp-blog-single">

	<!-- Breadcrumbs -->
	<div class="tp-blog-single__breadcrumbs">
		<div class="tp-container">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
			<span class="tp-sep">&rsaquo;</span>
			<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a>
			<span class="tp-sep">&rsaquo;</span>
			<?php if ( $cat_link && ! is_wp_error( $cat_link ) ) : ?>
				<a href="<?php echo esc_url( $cat_link ); ?>"><?php echo esc_html( $cat_name ); ?></a>
				<span class="tp-sep">&rsaquo;</span>
			<?php endif; ?>
			<span><?php the_title(); ?></span>
		</div>
	</div>

	<!-- Content Area -->
	<div class="tp-blog-single__content-wrap">
		<div class="tp-container">
			<div class="tp-blog-single__layout">

				<!-- Share Bar (vertical, sticky) -->
				<div class="tp-blog-single__sharebar">
					<div class="tp-blog-single__sharebar-inner">
						<span class="tp-blog-single__sharebar-label">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg>
							Share
						</span>
						<a href="https://wa.me/?text=<?php echo $share_title . '%20' . $share_url; ?>" target="_blank" rel="noopener" class="tp-blog-single__sharebar-btn tp-blog-single__sharebar-btn--wa" aria-label="Share on WhatsApp">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
						</a>
						<a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener" class="tp-blog-single__sharebar-btn" aria-label="Share on X">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
						</a>
						<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url; ?>" target="_blank" rel="noopener" class="tp-blog-single__sharebar-btn" aria-label="Share on LinkedIn">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
						</a>
						<button class="tp-blog-single__sharebar-btn" onclick="navigator.clipboard.writeText('<?php echo esc_js( get_permalink() ); ?>');this.innerHTML='<svg width=18 height=18 viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path d=\'M20 6L9 17l-5-5\'/></svg>';setTimeout(()=>{this.innerHTML='<svg width=18 height=18 viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><rect x=\'9\' y=\'9\' width=\'13\' height=\'13\' rx=\'2\' ry=\'2\'/><path d=\'M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1\'/></svg>'},2000)" aria-label="Copy link">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
						</button>
					</div>
				</div>

				<!-- Main Content -->
				<article class="tp-blog-single__content">

					<!-- Header (title + meta) -->
					<header class="tp-blog-single__header">
						<h1 class="tp-blog-single__title"><?php the_title(); ?></h1>
						<div class="tp-blog-single__meta">
							<span>Written by <strong><?php echo esc_html( $author_name ); ?></strong></span>
							<span class="tp-blog-single__meta-sep">|</span>
							<span>Last updated on <?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
							<span class="tp-blog-single__meta-sep">|</span>
							<span><?php echo esc_html( $read_time ); ?> min read</span>
						</div>
					</header>

					<!-- Featured Image -->
					<?php if ( $thumbnail ) : ?>
					<div class="tp-blog-single__cover">
						<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="tp-blog-single__cover-img" loading="lazy">
					</div>
					<?php endif; ?>

					<!-- Table of Contents -->
					<?php if ( count( $toc_items ) >= 3 ) : ?>
					<div class="tp-blog-toc">
						<div class="tp-blog-toc__header" onclick="this.parentElement.classList.toggle('tp-blog-toc--collapsed')">
							<span class="tp-blog-toc__title">
								<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
								Table of Contents
							</span>
							<svg class="tp-blog-toc__chevron" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
						</div>
						<nav class="tp-blog-toc__list">
							<?php foreach ( $toc_items as $toc ) : ?>
								<a href="#<?php echo esc_attr( $toc['anchor'] ); ?>" class="tp-blog-toc__item tp-blog-toc__item--h<?php echo esc_attr( $toc['level'] ); ?>">
									<?php echo esc_html( $toc['text'] ); ?>
								</a>
							<?php endforeach; ?>
						</nav>
					</div>
					<?php endif; ?>

					<!-- Post Content -->
					<div class="tp-blog-single__body">
						<?php echo $content; ?>
					</div>

				</article>

				<!-- Sidebar -->
				<aside class="tp-blog-single__sidebar">
					<div class="tp-blog-single__cta-card">
						<div class="tp-blog-single__cta-photo">
							<div class="tp-blog-single__cta-photo-circle">
								<img src="<?php echo esc_url( content_url( '/uploads/2026/09/blog-cta-consultant.jpg' ) ); ?>" alt="Real Estate Consultant" class="tp-blog-single__cta-photo-img">
							</div>
						</div>
						<div class="tp-blog-single__cta-body">
							<h3 class="tp-blog-single__cta-title">Your Expert Home Journey!</h3>
							<p class="tp-blog-single__cta-text">Join <strong>78%</strong> of our readers who found their dream home with a free 30-minute consultation.</p>
							<p class="tp-blog-single__cta-sub">Get expert advice now!</p>
							<form class="tp-blog-single__cta-form" id="blogCtaForm">
								<?php wp_nonce_field( 'tp_blog_cta_nonce', 'blog_cta_nonce' ); ?>
								<input type="tel" placeholder="Phone Number" class="tp-blog-single__cta-input" maxlength="10" pattern="[0-9]{10}" required>
								<button type="submit" class="tp-blog-single__cta-btn">
									Subscribe
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4z"/></svg>
								</button>
							</form>
							<script>
							document.getElementById('blogCtaForm').addEventListener('submit',function(e){
								e.preventDefault();
								var f=this,p=f.querySelector('input[type="tel"]').value.replace(/\D/g,''),btn=f.querySelector('.tp-blog-single__cta-btn');
								if(p.length<10)return;
								btn.textContent='Sending...';btn.disabled=true;
								var fd=new FormData();
								fd.append('action','tp_blog_cta_lead');
								fd.append('nonce',f.querySelector('#blog_cta_nonce').value);
								fd.append('phone',p);
								fd.append('page_url',location.href);
								fetch('<?php echo esc_url( admin_url("admin-ajax.php") ); ?>',{method:'POST',body:fd})
								.then(function(r){return r.json();})
								.then(function(d){btn.textContent=d.success?'Thank you!':'Try again';if(!d.success)btn.disabled=false;})
								.catch(function(){btn.textContent='Try again';btn.disabled=false;});
							});
							</script>
							<p class="tp-blog-single__cta-disclaimer">By subscribing you agree to with our <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a></p>
						</div>
					</div>
				</aside>

			</div>
		</div>
	</div>

	<!-- Related Posts -->
	<?php if ( $related_query->have_posts() ) : ?>
	<section class="tp-blog-single__related">
		<div class="tp-container">
			<h2 class="tp-blog-single__related-title">Related Articles</h2>
			<div class="tp-blog-grid tp-blog-grid--archive">
				<?php while ( $related_query->have_posts() ) : $related_query->the_post();
					$r_terms    = get_the_terms( get_the_ID(), 'tp_guide_type' );
					$r_cat      = ( $r_terms && ! is_wp_error( $r_terms ) ) ? $r_terms[0]->name : 'Blog';
					$r_words    = str_word_count( wp_strip_all_tags( get_the_content() ) );
					$r_read     = max( 1, round( $r_words / 200 ) );
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
							<span class="tp-blog-card__category"><?php echo esc_html( $r_cat ); ?></span>
						</div>
						<div class="tp-blog-card__body">
							<h3 class="tp-blog-card__title"><?php the_title(); ?></h3>
							<div class="tp-blog-card__meta-row">
								<span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
								<span class="tp-blog-card__dot">&middot;</span>
								<span><?php echo esc_html( $r_read ); ?> min read</span>
							</div>
						</div>
					</a>
				</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

</main>

<?php get_footer(); ?>
