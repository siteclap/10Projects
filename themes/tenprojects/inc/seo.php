<?php
/**
 * SEO — Dynamic meta tags, Open Graph, Twitter Cards, canonicals, and robots directives.
 *
 * Generates all SEO meta markup for every page type on the site.
 * Hooks into wp_head for meta output and document_title_parts for title rewriting.
 *
 * @package TenProjects
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * ---------------------------------------------------------------------------
 * Title tag
 * ---------------------------------------------------------------------------
 * WordPress title-tag support is already declared in tenprojects_setup().
 * We filter document_title_parts to set per-page-type titles.
 */
function tp_seo_document_title( $title_parts ) {

	// Homepage.
	if ( is_front_page() ) {
		$title_parts['title']   = '10Projects — Find the 10 Best-Fit Projects for You';
		$title_parts['tagline'] = '';
		$title_parts['site']    = '';
		return $title_parts;
	}

	// Single project.
	if ( is_singular( 'tp_project' ) ) {
		$location = tp_seo_get_primary_location();
		$title_parts['title'] = sprintf(
			'%s, %s — Price, Reviews, Pros & Cons — 10Projects',
			get_the_title(),
			$location
		);
		$title_parts['site'] = '';
		return $title_parts;
	}

	// Single developer.
	if ( is_singular( 'tp_developer' ) ) {
		$title_parts['title'] = sprintf(
			'%s Projects — Track Record, Reviews, Analysis',
			get_the_title()
		);
		$title_parts['site'] = '';
		return $title_parts;
	}

	// Single location.
	if ( is_singular( 'tp_location' ) ) {
		$city = get_post_meta( get_the_ID(), '_tp_city', true ) ?: 'Navi Mumbai';
		$title_parts['title'] = sprintf(
			'Best Projects in %s, %s — Prices, Reviews, Analysis',
			get_the_title(),
			$city
		);
		$title_parts['site'] = '';
		return $title_parts;
	}

	// Single guide / article.
	if ( is_singular( 'tp_guide' ) ) {
		$title_parts['title'] = sprintf(
			'%s — 10Projects Guide',
			get_the_title()
		);
		$title_parts['site'] = '';
		return $title_parts;
	}

	// Project archive.
	if ( is_post_type_archive( 'tp_project' ) ) {
		$year = date( 'Y' );
		$title_parts['title'] = sprintf(
			'New Projects in Navi Mumbai (%s) — 10Projects',
			$year
		);
		$title_parts['site'] = '';
		return $title_parts;
	}

	// Location archive.
	if ( is_post_type_archive( 'tp_location' ) ) {
		$title_parts['title'] = 'Explore Locations — 10Projects';
		$title_parts['site']  = '';
		return $title_parts;
	}

	// Developer archive.
	if ( is_post_type_archive( 'tp_developer' ) ) {
		$title_parts['title'] = 'Developers & Builders — 10Projects';
		$title_parts['site']  = '';
		return $title_parts;
	}

	// Static pages by slug.
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_the_ID() );
		$page_titles = array(
			'start'       => 'Start Your AI Property Assessment — 10Projects',
			'results'     => 'Your Top 10 Project Results — 10Projects',
			'compare'     => 'Compare Projects Side by Side — 10Projects',
			'dashboard'   => 'My Dashboard — 10Projects',
			'methodology' => 'How the Fit Score Works — Our Methodology — 10Projects',
		);
		if ( isset( $page_titles[ $slug ] ) ) {
			$title_parts['title'] = $page_titles[ $slug ];
			$title_parts['site']  = '';
		}
	}

	return $title_parts;
}
add_filter( 'document_title_parts', 'tp_seo_document_title', 20 );

/**
 * ---------------------------------------------------------------------------
 * Meta description + OG + Twitter + Canonical
 * ---------------------------------------------------------------------------
 */
function tp_seo_meta_tags() {
	$description = tp_seo_get_description();
	$og_image    = tp_seo_get_og_image();
	$canonical   = tp_seo_get_canonical();
	$og_type     = tp_seo_get_og_type();
	$title       = tp_seo_get_meta_title();

	// Meta description.
	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	// Canonical.
	if ( $canonical ) {
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canonical ) );
	}

	// Open Graph.
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( '10Projects' ) );
	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $og_type ) );
	if ( $title ) {
		printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	}
	if ( $description ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	if ( $canonical ) {
		printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $canonical ) );
	}
	if ( $og_image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $og_image ) );
		echo '<meta property="og:image:width" content="1200" />' . "\n";
		echo '<meta property="og:image:height" content="630" />' . "\n";
	}

	// Twitter Card.
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	if ( $title ) {
		printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );
	}
	if ( $description ) {
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	if ( $og_image ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $og_image ) );
	}
}
add_action( 'wp_head', 'tp_seo_meta_tags', 1 );

/**
 * ---------------------------------------------------------------------------
 * Robots — noindex assessment and results pages.
 * ---------------------------------------------------------------------------
 */
function tp_seo_robots( $robots ) {
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_the_ID() );
		$noindex_slugs = array( 'start', 'results', 'dashboard', 'compare' );

		if ( in_array( $slug, $noindex_slugs, true ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = false;
		}
	}

	return $robots;
}
add_filter( 'wp_robots', 'tp_seo_robots', 20 );

/**
 * ---------------------------------------------------------------------------
 * Helper: Build meta description per page type.
 * ---------------------------------------------------------------------------
 */
function tp_seo_get_description() {

	// Homepage.
	if ( is_front_page() ) {
		return 'Tell us what you need, and our AI finds the 10 best-fit real estate projects in Navi Mumbai. Transparent scoring across 20 categories — budget, location, developer, legal, and more.';
	}

	// Single project.
	if ( is_singular( 'tp_project' ) ) {
		$post_id  = get_the_ID();
		$location = tp_seo_get_primary_location();
		$price_min = get_post_meta( $post_id, '_tp_price_display_min', true );
		$price_max = get_post_meta( $post_id, '_tp_price_display_max', true );
		$price     = ( $price_min ) ? tp_format_price_range( (int) $price_min, (int) $price_max ) : 'Price on request';
		$developer = tp_seo_get_developer_name( $post_id );

		return sprintf(
			'%s in %s by %s. Price starts at %s. Read AI analysis, Fit Score breakdown, pros & cons, floor plans, and honest reviews on 10Projects.',
			get_the_title(),
			$location,
			$developer,
			$price
		);
	}

	// Single developer.
	if ( is_singular( 'tp_developer' ) ) {
		$completed = get_post_meta( get_the_ID(), '_tp_projects_completed', true );
		$ongoing   = get_post_meta( get_the_ID(), '_tp_projects_ongoing', true );

		return sprintf(
			'Detailed analysis of %s — %s projects completed, %s ongoing. Track record, RERA compliance, on-time delivery rate, and all current projects on 10Projects.',
			get_the_title(),
			$completed ?: '0',
			$ongoing ?: '0'
		);
	}

	// Single location.
	if ( is_singular( 'tp_location' ) ) {
		$city  = get_post_meta( get_the_ID(), '_tp_city', true ) ?: 'Navi Mumbai';
		$count = get_post_meta( get_the_ID(), '_tp_project_count', true ) ?: '0';

		return sprintf(
			'Explore %s+ projects in %s, %s. Price trends, livability score, infrastructure updates, connectivity, schools, hospitals — everything you need to decide.',
			$count,
			get_the_title(),
			$city
		);
	}

	// Single guide.
	if ( is_singular( 'tp_guide' ) ) {
		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return wp_trim_words( $excerpt, 25, '...' );
		}
		return sprintf(
			'%s — Expert guide from 10Projects covering real estate buying in Navi Mumbai.',
			get_the_title()
		);
	}

	// Project archive.
	if ( is_post_type_archive( 'tp_project' ) ) {
		$year = date( 'Y' );
		return sprintf(
			'Browse all new residential projects in Navi Mumbai (%s). Compare prices, read AI-powered reviews, and find the best fit with 10Projects.',
			$year
		);
	}

	// Location archive.
	if ( is_post_type_archive( 'tp_location' ) ) {
		return 'Explore all locations across Navi Mumbai. Compare price trends, livability scores, infrastructure developments, and find the best neighbourhood for you.';
	}

	// Static pages.
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_the_ID() );
		$descs = array(
			'methodology' => 'Learn how 10Projects scores every project across 20 categories. Understand the Fit Score methodology, weight profiles, and AI explanation generation.',
		);
		if ( isset( $descs[ $slug ] ) ) {
			return $descs[ $slug ];
		}
	}

	return '';
}

/**
 * ---------------------------------------------------------------------------
 * Helper: Build the meta title (for OG / Twitter, distinct from document title).
 * ---------------------------------------------------------------------------
 */
function tp_seo_get_meta_title() {
	$parts = apply_filters( 'document_title_parts', array(
		'title'   => '',
		'page'    => '',
		'tagline' => get_bloginfo( 'description', 'display' ),
		'site'    => get_bloginfo( 'name', 'display' ),
	) );

	return $parts['title'] ?: wp_get_document_title();
}

/**
 * ---------------------------------------------------------------------------
 * Helper: OG image.
 * ---------------------------------------------------------------------------
 */
function tp_seo_get_og_image() {
	// Singular with thumbnail.
	if ( is_singular() && has_post_thumbnail() ) {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
		if ( $image ) {
			return $image[0];
		}
	}

	// Fallback: site-wide default.
	$default_og = get_option( 'tp_default_og_image', '' );
	if ( $default_og ) {
		return $default_og;
	}

	return TENPROJECTS_THEME_URI . '/assets/images/og-default.jpg';
}

/**
 * ---------------------------------------------------------------------------
 * Helper: Canonical URL.
 * ---------------------------------------------------------------------------
 */
function tp_seo_get_canonical() {
	if ( is_singular() ) {
		return get_permalink();
	}

	if ( is_post_type_archive() ) {
		return get_post_type_archive_link( get_queried_object()->name ?? '' );
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_tax() || is_category() || is_tag() ) {
		return get_term_link( get_queried_object() );
	}

	return '';
}

/**
 * ---------------------------------------------------------------------------
 * Helper: OG type.
 * ---------------------------------------------------------------------------
 */
function tp_seo_get_og_type() {
	if ( is_singular( 'tp_project' ) ) {
		return 'product'; // RealEstateListing maps best to product.
	}

	if ( is_singular( 'tp_guide' ) || is_singular( 'post' ) ) {
		return 'article';
	}

	if ( is_singular( 'tp_developer' ) || is_singular( 'tp_location' ) ) {
		return 'profile';
	}

	return 'website';
}

/**
 * ---------------------------------------------------------------------------
 * Helper: Get primary location name for a project post.
 * ---------------------------------------------------------------------------
 */
function tp_seo_get_primary_location() {
	$locations = wp_get_post_terms( get_the_ID(), 'tp_location_area', array( 'fields' => 'names' ) );
	if ( is_array( $locations ) && ! empty( $locations ) ) {
		return $locations[0];
	}
	return 'Navi Mumbai';
}

/**
 * ---------------------------------------------------------------------------
 * Helper: Get developer name for a project post.
 * ---------------------------------------------------------------------------
 */
function tp_seo_get_developer_name( $post_id ) {
	$developer_id = get_post_meta( $post_id, '_tp_developer_id', true );
	if ( $developer_id ) {
		$developer_post = get_post( $developer_id );
		if ( $developer_post ) {
			return $developer_post->post_title;
		}
	}
	return 'Developer';
}

/**
 * ---------------------------------------------------------------------------
 * Remove default WordPress canonical (we output our own above).
 * ---------------------------------------------------------------------------
 */
function tp_seo_remove_default_canonical() {
	remove_action( 'wp_head', 'rel_canonical' );
}
add_action( 'wp', 'tp_seo_remove_default_canonical' );
