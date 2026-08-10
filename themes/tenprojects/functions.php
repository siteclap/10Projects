<?php
/**
 * 10Projects Theme Functions
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

/* ─── Theme Setup ─────────────────────────────────────────── */

function tenprojects_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'rank-math-breadcrumbs' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'tenprojects' ),
		'footer'  => __( 'Footer Menu', 'tenprojects' ),
	) );
}
add_action( 'after_setup_theme', 'tenprojects_theme_setup' );

/* ─── Enqueue Styles & Scripts ────────────────────────────── */

function tenprojects_enqueue_assets() {
	// Theme CSS (version-busted).
	wp_enqueue_style(
		'tenprojects-style',
		get_stylesheet_uri(),
		array(),
		filemtime( get_stylesheet_directory() . '/style.css' )
	);

	// Inter font from Google.
	wp_enqueue_style(
		'google-fonts-inter',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	// Project page JS (only on single project pages).
	if ( is_singular( 'tp_project' ) ) {
		wp_enqueue_script(
			'tenprojects-project',
			get_template_directory_uri() . '/assets/js/project.js',
			array(),
			filemtime( get_template_directory() . '/assets/js/project.js' ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'tenprojects_enqueue_assets' );

/* ─── Helper Functions ────────────────────────────────────── */

/**
 * Get project meta field (adds _tp_ prefix automatically).
 */
function tp_get_meta( $post_id, $key ) {
	return get_post_meta( $post_id, '_tp_' . $key, true );
}

/**
 * Format price in Indian format (₹ Lakhs / Crores).
 */
function tp_format_price( $amount ) {
	if ( ! $amount || $amount <= 0 ) {
		return '';
	}
	if ( $amount >= 10000000 ) {
		$cr = $amount / 10000000;
		return '₹' . number_format( $cr, 2 ) . ' Cr';
	}
	if ( $amount >= 100000 ) {
		$lakh = $amount / 100000;
		return '₹' . number_format( $lakh, 2 ) . ' L';
	}
	return '₹' . number_format( $amount );
}

/**
 * Format price range.
 */
function tp_format_price_range( $min, $max ) {
	if ( ! $min && ! $max ) {
		return 'Price on Request';
	}
	if ( $min === $max || ! $max ) {
		return tp_format_price( $min );
	}
	return tp_format_price( $min ) . ' – ' . tp_format_price( $max );
}

/**
 * Get gallery image URLs from comma-separated attachment IDs.
 */
function tp_get_gallery_urls( $post_id, $size = 'large' ) {
	$ids_str = tp_get_meta( $post_id, 'gallery_ids' );
	if ( ! $ids_str ) {
		return array();
	}
	$ids  = array_filter( array_map( 'intval', explode( ',', $ids_str ) ) );
	$urls = array();
	foreach ( $ids as $id ) {
		$url = wp_get_attachment_image_url( $id, $size );
		if ( $url ) {
			$urls[] = $url;
		}
	}
	return $urls;
}

/**
 * Get banner image URLs.
 */
function tp_get_banner_urls( $post_id, $type = 'desktop' ) {
	$key     = 'banner_' . $type . '_ids';
	$ids_str = tp_get_meta( $post_id, $key );
	if ( ! $ids_str ) {
		return array();
	}
	$ids  = array_filter( array_map( 'intval', explode( ',', $ids_str ) ) );
	$urls = array();
	foreach ( $ids as $id ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( $url ) {
			$urls[] = $url;
		}
	}
	return $urls;
}

/**
 * Get QR code image URLs.
 */
function tp_get_qr_urls( $post_id ) {
	$ids_str = tp_get_meta( $post_id, 'qr_code_id' );
	if ( ! $ids_str ) {
		return array();
	}
	$ids  = array_filter( array_map( 'intval', explode( ',', $ids_str ) ) );
	$urls = array();
	foreach ( $ids as $id ) {
		$url = wp_get_attachment_image_url( $id, 'medium' );
		if ( $url ) {
			$urls[] = $url;
		}
	}
	return $urls;
}

/**
 * Get location area taxonomy term for a project.
 */
function tp_get_location_term( $post_id ) {
	$terms = wp_get_object_terms( $post_id, 'tp_location_area' );
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		return $terms[0];
	}
	return null;
}

/**
 * Parse JSON meta field (pros, cons, amenities, offers, etc.).
 */
function tp_parse_json_meta( $post_id, $key ) {
	$raw = tp_get_meta( $post_id, $key );
	if ( ! $raw ) {
		return array();
	}
	$decoded = json_decode( $raw, true );
	return is_array( $decoded ) ? $decoded : array();
}

/**
 * Get developer logo URL.
 */
function tp_get_developer_logo( $post_id ) {
	$id = tp_get_meta( $post_id, 'developer_logo_id' );
	if ( $id ) {
		return wp_get_attachment_image_url( intval( $id ), 'thumbnail' );
	}
	return '';
}

/**
 * Calculate EMI.
 */
function tp_calculate_emi( $principal, $rate_annual = 8.5, $tenure_months = 240 ) {
	if ( $principal <= 0 ) {
		return 0;
	}
	$r   = ( $rate_annual / 100 ) / 12;
	$n   = $tenure_months;
	$emi = $principal * $r * pow( 1 + $r, $n ) / ( pow( 1 + $r, $n ) - 1 );
	return round( $emi );
}

/**
 * Get similar projects (same location, exclude current).
 */
function tp_get_similar_projects( $post_id, $count = 4 ) {
	$location = tp_get_location_term( $post_id );
	if ( ! $location ) {
		return array();
	}

	$args = array(
		'post_type'      => 'tp_project',
		'posts_per_page' => $count,
		'post__not_in'   => array( $post_id ),
		'post_status'    => 'publish',
		'tax_query'      => array(
			array(
				'taxonomy' => 'tp_location_area',
				'field'    => 'term_id',
				'terms'    => $location->term_id,
			),
		),
	);

	return get_posts( $args );
}

/* ─── SEO: JSON-LD Structured Data ────────────────────────── */

function tenprojects_project_jsonld() {
	if ( ! is_singular( 'tp_project' ) ) {
		return;
	}

	$post_id  = get_the_ID();
	$location = tp_get_location_term( $post_id );
	$loc_name = $location ? $location->name : '';

	// RealEstateListing schema.
	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'RealEstateListing',
		'name'        => get_the_title(),
		'description' => get_the_excerpt() ?: tp_get_meta( $post_id, 'short_overview' ),
		'url'         => get_permalink(),
		'image'       => get_the_post_thumbnail_url( $post_id, 'large' ),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => $loc_name,
			'addressRegion'   => 'Maharashtra',
			'addressCountry'  => 'IN',
		),
	);

	$price_min = tp_get_meta( $post_id, 'price_display_min' );
	if ( $price_min ) {
		$schema['offers'] = array(
			'@type'         => 'Offer',
			'price'         => $price_min,
			'priceCurrency' => 'INR',
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

	// BreadcrumbList schema.
	$breadcrumb = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => array(
			array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) ),
			array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Navi Mumbai', 'item' => home_url( '/navi-mumbai/' ) ),
		),
	);
	if ( $loc_name ) {
		$breadcrumb['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 3,
			'name'     => $loc_name,
			'item'     => home_url( '/navi-mumbai/' . ( $location ? $location->slug : '' ) . '/' ),
		);
		$breadcrumb['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 4,
			'name'     => get_the_title(),
		);
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $breadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'tenprojects_project_jsonld' );

/* ─── Custom Title Format ─────────────────────────────────── */

function tenprojects_document_title_parts( $title ) {
	if ( is_singular( 'tp_project' ) ) {
		$location = tp_get_location_term( get_the_ID() );
		$loc_name = $location ? $location->name : 'Navi Mumbai';
		$title['title'] = get_the_title() . ' | ' . $loc_name . ' | Price, Floor Plans, Reviews';
		$title['site']  = '10Projects';
	}
	return $title;
}
add_filter( 'document_title_parts', 'tenprojects_document_title_parts' );
