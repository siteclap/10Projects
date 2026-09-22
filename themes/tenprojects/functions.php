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

	// Banner images auto-crop to 800×530 on upload.
	add_image_size( 'tp-banner', 800, 530, true );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'tenprojects' ),
		'footer'  => __( 'Footer Menu', 'tenprojects' ),
	) );
}
add_action( 'after_setup_theme', 'tenprojects_theme_setup' );

/* ─── Favicon ─────────────────────────────────────────────── */

function tp_favicon() {
	$dir = get_template_directory_uri() . '/assets/images';
	echo '<link rel="icon" type="image/x-icon" href="' . esc_url( $dir . '/favicon.ico' ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $dir . '/logo.png' ) . '">' . "\n";
}
add_action( 'wp_head', 'tp_favicon', 1 );
add_action( 'admin_head', 'tp_favicon', 1 );
add_action( 'login_head', 'tp_favicon', 1 );

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

	// Nearby projects JS (homepage only).
	if ( is_front_page() ) {
		wp_enqueue_script(
			'tenprojects-nearby',
			get_template_directory_uri() . '/assets/js/nearby.js',
			array(),
			filemtime( get_template_directory() . '/assets/js/nearby.js' ),
			true
		);
		wp_localize_script( 'tenprojects-nearby', 'tpNearby', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'tp_nearby_nonce' ),
		) );
	}

	// Filter bar JS (search, taxonomy archives).
	if ( is_search() || is_tax( 'tp_location_area' ) || is_tax( 'tp_property_type' ) ) {
		wp_enqueue_script(
			'tenprojects-filters',
			get_template_directory_uri() . '/assets/js/filters.js',
			array(),
			filemtime( get_template_directory() . '/assets/js/filters.js' ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'tenprojects_enqueue_assets' );

/* ─── Performance: Remove WordPress Bloat ────────────────── */

function tenprojects_remove_bloat() {
	// Remove Gutenberg block library CSS (theme uses classic editor, not blocks).
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'wc-blocks-style' );

	// Remove global styles (Gutenberg FSE styles — not used).
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );

	// Remove emoji styles.
	wp_dequeue_style( 'wp-emoji-styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'wp_enqueue_scripts', 'tenprojects_remove_bloat', 100 );

// Remove emoji detection script (runs at priority 7 on wp_head).
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

// Remove global styles inline CSS for classic themes.
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

// Remove WordPress version meta tag (security).
remove_action( 'wp_head', 'wp_generator' );

// Remove RSD/EditURI link (xmlrpc attack surface).
remove_action( 'wp_head', 'rsd_link' );

// Remove Windows Live Writer manifest.
remove_action( 'wp_head', 'wlwmanifest_link' );

// Remove shortlink.
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

// Remove REST API link from head (still accessible, just hidden).
remove_action( 'wp_head', 'rest_output_link_wp_head' );

// Disable XML-RPC entirely (brute-force attack vector).
add_filter( 'xmlrpc_enabled', '__return_false' );

/* ─── Performance: Optimize Google Fonts Loading ─────────── */

function tenprojects_preconnect_fonts( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.googleapis.com',
			'crossorigin' => true,
		);
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => true,
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'tenprojects_preconnect_fonts', 10, 2 );

/* ─── Performance: Defer JS Loading ──────────────────────── */

function tenprojects_defer_scripts( $tag, $handle, $src ) {
	// Don't defer inline scripts or admin scripts.
	if ( is_admin() || ! $src ) {
		return $tag;
	}
	// Defer all theme scripts (they're already in footer, defer helps further).
	$defer_handles = array( 'tenprojects-project', 'tenprojects-nearby', 'tenprojects-filters', 'tp-chatbot' );
	if ( in_array( $handle, $defer_handles, true ) ) {
		return str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'tenprojects_defer_scripts', 10, 3 );

/* ─── Performance: Clear Homepage Transients on Project Save ── */

function tp_clear_homepage_cache( $post_id ) {
	if ( get_post_type( $post_id ) !== 'tp_project' ) {
		return;
	}
	$keys = array(
		'tp_hp_new_launches', 'tp_hp_featured',
		'tp_hp_buy', 'tp_hp_rent', 'tp_hp_commercial', 'tp_hp_plot',
		'tp_hp_popular_locs', 'tp_hp_locations', 'tp_hp_dev_logos',
	);
	foreach ( $keys as $k ) {
		delete_transient( $k );
	}
}
add_action( 'save_post', 'tp_clear_homepage_cache' );

/* ─── LiteSpeed Cache Compatibility ──────────────────────── */

function tenprojects_litespeed_compat() {
	// Tell LiteSpeed to cache pages for logged-out users.
	if ( ! is_user_logged_in() && function_exists( 'do_action' ) ) {
		// Ensure nonces don't break page caching.
		// LiteSpeed's ESI (Edge Side Includes) handles nonces if enabled.
		// If ESI is not available, mark nonce-containing pages as cacheable
		// by using LiteSpeed's public cache tag.
		if ( defined( 'LSCWP_V' ) ) {
			do_action( 'litespeed_control_set_public' );
			do_action( 'litespeed_tag_add', 'tp_homepage' );
		}
	}
}
add_action( 'wp', 'tenprojects_litespeed_compat' );

/* ─── Anti-Copy & Content Protection ─────────────────────── */

function tenprojects_content_protection() {
	// Skip for logged-in admins so they can still inspect/debug.
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<style>
		/* Disable text selection on content areas */
		body:not(input):not(textarea):not(select) {
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
		}
		/* Allow selection in form fields */
		input, textarea, select, [contenteditable="true"] {
			-webkit-user-select: text !important;
			-moz-user-select: text !important;
			-ms-user-select: text !important;
			user-select: text !important;
		}
		/* Prevent image dragging */
		img {
			-webkit-user-drag: none;
			-khtml-user-drag: none;
			-moz-user-drag: none;
			-o-user-drag: none;
			user-drag: none;
			pointer-events: none;
		}
		/* Re-enable pointer events on clickable images (links) */
		a img { pointer-events: auto; }
	</style>
	<script>
	(function(){
		// Block right-click context menu (except on form fields)
		document.addEventListener('contextmenu',function(e){
			var t=e.target.tagName.toLowerCase();
			if(t==='input'||t==='textarea'||t==='select')return;
			e.preventDefault();
		});

		// Block keyboard shortcuts: Ctrl+U, Ctrl+S, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C, F12
		document.addEventListener('keydown',function(e){
			// F12
			if(e.key==='F12'){e.preventDefault();return;}
			if(e.ctrlKey||e.metaKey){
				// Ctrl+U (view source)
				if(e.key==='u'||e.key==='U'){e.preventDefault();return;}
				// Ctrl+S (save page)
				if(e.key==='s'||e.key==='S'){e.preventDefault();return;}
				// Ctrl+Shift+I (DevTools), Ctrl+Shift+J (Console), Ctrl+Shift+C (Inspector)
				if(e.shiftKey&&(e.key==='I'||e.key==='i'||e.key==='J'||e.key==='j'||e.key==='C'||e.key==='c')){
					e.preventDefault();return;
				}
			}
		});

		// Block image drag
		document.addEventListener('dragstart',function(e){
			if(e.target.tagName==='IMG'){e.preventDefault();}
		});

		// Disable copy (except in form fields)
		document.addEventListener('copy',function(e){
			var t=document.activeElement.tagName.toLowerCase();
			if(t==='input'||t==='textarea')return;
			e.preventDefault();
		});

		// Disable cut
		document.addEventListener('cut',function(e){
			var t=document.activeElement.tagName.toLowerCase();
			if(t==='input'||t==='textarea')return;
			e.preventDefault();
		});
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'tenprojects_content_protection', 99 );

/* ─── Helper Functions ────────────────────────────────────── */

/**
 * Get project meta field (adds _tp_ prefix automatically).
 */
function tp_get_meta( $post_id, $key ) {
	return get_post_meta( $post_id, '_tp_' . $key, true );
}

/**
 * Format price in Indian format (₹ Lakhs / Crores).
 * Accepts value in Lakhs (e.g. 900 = ₹9.00 Cr, 45 = ₹45.00 L).
 */
function tp_format_price( $cr ) {
	if ( ! $cr || $cr <= 0 ) {
		return '';
	}
	$cr = floatval( $cr );
	if ( $cr < 1 ) {
		$lakhs = $cr * 100;
		return '₹' . number_format( $lakhs, 0 ) . ' Lacs';
	}
	return '₹' . rtrim( rtrim( number_format( $cr, 2 ), '0' ), '.' ) . ' Cr';
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
 * Format construction stage label (e.g. "foundation" → "Foundation").
 */
function tp_format_stage( $stage ) {
	if ( ! $stage ) {
		return '';
	}
	return ucwords( str_replace( array( '-', '_' ), ' ', $stage ) );
}

/**
 * Format possession date (e.g. "2030-12-31" → "Dec 2030").
 */
function tp_format_possession( $date_str ) {
	if ( ! $date_str ) {
		return '';
	}
	$ts = strtotime( $date_str );
	if ( $ts && preg_match( '/^\d{4}-\d{2}/', $date_str ) ) {
		return date( 'M Y', $ts );
	}
	return $date_str; // Already human-readable.
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
		// Use tp-banner (800×530 cropped) with full as fallback.
		$url = wp_get_attachment_image_url( $id, 'tp-banner' );
		if ( ! $url ) {
			$url = wp_get_attachment_image_url( $id, 'full' );
		}
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
function tp_calculate_emi( $cr, $rate_annual = 8.5, $tenure_months = 240 ) {
	$principal = floatval( $cr ) * 10000000; // Convert Cr to rupees.
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

/* ─── Search → Location Archive Redirect ─────────────────── */

/**
 * If a tp_project search term matches a known location (tp_location_area term),
 * redirect to that location's taxonomy archive, preserving property_type filter.
 */
function tenprojects_redirect_location_search() {
	if ( ! is_search() ) return;
	if ( get_query_var( 'post_type' ) !== 'tp_project' ) return;

	$raw = strtolower( trim( get_search_query() ) );
	if ( ! $raw ) return;

	// Try slug first, then name.
	$loc_term = get_term_by( 'slug', sanitize_title( $raw ), 'tp_location_area' );
	if ( ! $loc_term ) {
		$loc_term = get_term_by( 'name', $raw, 'tp_location_area' );
	}
	if ( ! $loc_term ) return;

	// Build redirect URL: taxonomy archive + optional property_type param.
	$archive_url = get_term_link( $loc_term );
	if ( is_wp_error( $archive_url ) ) return;

	if ( ! empty( $_GET['property_type'] ) ) {
		$archive_url = add_query_arg( 'property_type', sanitize_text_field( $_GET['property_type'] ), $archive_url );
	}

	wp_redirect( $archive_url, 302 );
	exit;
}
add_action( 'template_redirect', 'tenprojects_redirect_location_search' );

/* ─── Filter Bar: Server-Side Query Modification ─────────── */

function tenprojects_filter_projects( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	// Only on search, location archives, or property-type archives.
	$is_filter_page = $query->is_search()
		|| is_tax( 'tp_location_area' )
		|| is_tax( 'tp_property_type' );

	if ( ! $is_filter_page ) {
		return;
	}

	// ── Property type filter (Buy, Rent, Commercial, etc.) ──
	if ( ! empty( $_GET['property_type'] ) ) {
		$pt_slug   = sanitize_text_field( $_GET['property_type'] );
		$tax_query = $query->get( 'tax_query' ) ?: array();
		$tax_query[] = array(
			'taxonomy' => 'tp_property_type',
			'field'    => 'slug',
			'terms'    => $pt_slug,
		);
		$query->set( 'tax_query', $tax_query );
	}

	$meta_query = $query->get( 'meta_query' ) ?: array();

	// ── Budget filter (range slider: budget_min / budget_max) ──
	if ( ! empty( $_GET['budget_min'] ) ) {
		$meta_query[] = array(
			'key'     => '_tp_price_display_min',
			'value'   => intval( $_GET['budget_min'] ),
			'compare' => '>=',
			'type'    => 'NUMERIC',
		);
	}
	if ( ! empty( $_GET['budget_max'] ) && intval( $_GET['budget_max'] ) < 75000000 ) {
		$meta_query[] = array(
			'key'     => '_tp_price_display_min',
			'value'   => intval( $_GET['budget_max'] ),
			'compare' => '<=',
			'type'    => 'NUMERIC',
		);
	}

	// ── BHK filter ──
	if ( ! empty( $_GET['bhk'] ) ) {
		$bhks     = array_map( 'sanitize_text_field', explode( ',', $_GET['bhk'] ) );
		$bhk_meta = array( 'relation' => 'OR' );

		foreach ( $bhks as $bhk ) {
			if ( $bhk === '4+' ) {
				// Match 4 BHK, 5 BHK, or higher.
				$bhk_meta[] = array(
					'key'     => '_tp_available_configs_text',
					'value'   => '4',
					'compare' => 'LIKE',
				);
				$bhk_meta[] = array(
					'key'     => '_tp_available_configs_text',
					'value'   => '5',
					'compare' => 'LIKE',
				);
			} else {
				$bhk_meta[] = array(
					'key'     => '_tp_available_configs_text',
					'value'   => $bhk,
					'compare' => 'LIKE',
				);
			}
		}

		$meta_query[] = $bhk_meta;
	}

	// ── Construction status filter ──
	if ( ! empty( $_GET['status'] ) ) {
		$status_map = array(
			'new-launch'         => 'New Launch',
			'under-construction' => 'Under Construction',
			'nearing-completion' => 'Nearing Completion',
			'ready-to-move'      => 'Ready to Move',
		);
		$status = sanitize_text_field( $_GET['status'] );
		if ( isset( $status_map[ $status ] ) ) {
			$meta_query[] = array(
				'key'     => '_tp_construction_stage',
				'value'   => $status_map[ $status ],
				'compare' => '=',
			);
		}
	}

	// ── RERA only filter ──
	if ( ! empty( $_GET['rera'] ) ) {
		$meta_query[] = array(
			'key'     => '_tp_rera_number',
			'value'   => '',
			'compare' => '!=',
		);
	}

	// Apply meta query.
	if ( ! empty( $meta_query ) ) {
		$meta_query['relation'] = 'AND';
		$query->set( 'meta_query', $meta_query );
	}

	// ── Sort ──
	if ( ! empty( $_GET['sort'] ) ) {
		$sort = sanitize_text_field( $_GET['sort'] );
		switch ( $sort ) {
			case 'price-asc':
				$query->set( 'meta_key', '_tp_price_display_min' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'ASC' );
				break;
			case 'price-desc':
				$query->set( 'meta_key', '_tp_price_display_min' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'DESC' );
				break;
			case 'newest':
				$query->set( 'orderby', 'date' );
				$query->set( 'order', 'DESC' );
				break;
		}
	}
}
add_action( 'pre_get_posts', 'tenprojects_filter_projects' );

/* ─── Nearby Projects (Geolocation) ──────────────────────── */

/**
 * Get hardcoded coordinates for all 15 Navi Mumbai locations.
 */
function tp_get_location_coordinates() {
	return array(
		'kharghar'        => array( 19.0473, 73.0699 ),
		'panvel'          => array( 18.9894, 73.1175 ),
		'ulwe'            => array( 18.9726, 73.0197 ),
		'vashi'           => array( 19.0771, 73.0016 ),
		'nerul'           => array( 19.0330, 73.0169 ),
		'belapur'         => array( 19.0235, 73.0385 ),
		'airoli'          => array( 19.1590, 72.9988 ),
		'ghansoli'        => array( 19.1167, 73.0080 ),
		'kopar-khairane'  => array( 19.1038, 73.0060 ),
		'sanpada'         => array( 19.0635, 72.9988 ),
		'seawoods'        => array( 19.0220, 73.0170 ),
		'taloja'          => array( 19.0645, 73.1235 ),
		'dronagiri'       => array( 18.8830, 72.9930 ),
		'kamothe'         => array( 19.0190, 73.0890 ),
		'kalamboli'       => array( 19.0350, 73.1080 ),
	);
}

/**
 * Haversine distance in km (standalone, no plugin dependency).
 */
function tp_haversine_distance( $lat1, $lng1, $lat2, $lng2 ) {
	$r = 6371;
	$d_lat = deg2rad( $lat2 - $lat1 );
	$d_lng = deg2rad( $lng2 - $lng1 );
	$a = sin( $d_lat / 2 ) * sin( $d_lat / 2 )
		+ cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) )
		* sin( $d_lng / 2 ) * sin( $d_lng / 2 );
	return $r * 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
}

/**
 * AJAX handler: find nearby projects based on user's lat/lng.
 */
function tp_ajax_nearby_projects() {
	check_ajax_referer( 'tp_nearby_nonce', 'nonce' );

	$lat = isset( $_POST['lat'] ) ? floatval( $_POST['lat'] ) : 0;
	$lng = isset( $_POST['lng'] ) ? floatval( $_POST['lng'] ) : 0;

	if ( ! $lat || ! $lng ) {
		wp_send_json( array( 'found' => false, 'reason' => 'invalid_coords' ) );
	}

	// Find nearest location.
	$locations   = tp_get_location_coordinates();
	$nearest     = '';
	$nearest_dist = PHP_INT_MAX;

	foreach ( $locations as $slug => $coords ) {
		$dist = tp_haversine_distance( $lat, $lng, $coords[0], $coords[1] );
		if ( $dist < $nearest_dist ) {
			$nearest_dist = $dist;
			$nearest      = $slug;
		}
	}

	// Too far from any Navi Mumbai location.
	if ( $nearest_dist > 30 ) {
		wp_send_json( array( 'found' => false, 'reason' => 'too_far' ) );
	}

	// Get the taxonomy term.
	$term = get_term_by( 'slug', $nearest, 'tp_location_area' );
	if ( ! $term || is_wp_error( $term ) ) {
		wp_send_json( array( 'found' => false, 'reason' => 'no_term' ) );
	}

	// Query projects in this location.
	$projects = get_posts( array(
		'post_type'      => 'tp_project',
		'posts_per_page' => 6,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'tax_query'      => array(
			array(
				'taxonomy' => 'tp_location_area',
				'field'    => 'slug',
				'terms'    => $nearest,
			),
		),
	) );

	if ( empty( $projects ) ) {
		wp_send_json( array( 'found' => false, 'reason' => 'no_projects' ) );
	}

	$items = array();
	foreach ( $projects as $p ) {
		$p_id  = $p->ID;
		$thumb = get_the_post_thumbnail_url( $p_id, 'medium' );
		$p_min = floatval( tp_get_meta( $p_id, 'price_display_min' ) );
		$p_max = floatval( tp_get_meta( $p_id, 'price_display_max' ) );

		$items[] = array(
			'title'     => get_the_title( $p_id ),
			'url'       => get_permalink( $p_id ),
			'thumb'     => $thumb ?: '',
			'price'     => tp_format_price_range( $p_min, $p_max ),
			'configs'   => tp_get_meta( $p_id, 'available_configs_text' ) ?: '',
			'developer' => tp_get_meta( $p_id, 'developer_name' ) ?: '',
			'stage'     => tp_get_meta( $p_id, 'construction_stage' ) ?: '',
		);
	}

	wp_send_json( array(
		'found'         => true,
		'location_name' => $term->name,
		'location_slug' => $term->slug,
		'location_url'  => home_url( '/navi-mumbai/' . $term->slug . '/' ),
		'projects'      => $items,
	) );
}
add_action( 'wp_ajax_tp_nearby', 'tp_ajax_nearby_projects' );
add_action( 'wp_ajax_nopriv_tp_nearby', 'tp_ajax_nearby_projects' );

/* ─── Custom Title Format ─────────────────────────────────── */

function tenprojects_document_title_parts( $title ) {
	if ( is_singular( 'tp_project' ) ) {
		$location = tp_get_location_term( get_the_ID() );
		$loc_name = $location ? $location->name : 'Navi Mumbai';
		$title['title'] = get_the_title() . ' | ' . $loc_name . ' | Price, Floor Plans, Reviews';
		$title['site']  = 'LeadMAAXX';
	}
	if ( is_tax( 'tp_property_type' ) ) {
		$term = get_queried_object();
		$title['title'] = $term->name . ' Properties in Navi Mumbai';
		$title['site']  = 'LeadMAAXX';
	}
	if ( is_tax( 'tp_location_area' ) ) {
		$term = get_queried_object();
		$title['title'] = 'Projects in ' . $term->name . ', Navi Mumbai | Price, Reviews';
		$title['site']  = 'LeadMAAXX';
	}
	if ( is_front_page() ) {
		$title['title'] = 'Find the 10 Best-Fit Projects for You';
		$title['site']  = 'LeadMAAXX';
	}
	if ( is_search() ) {
		$title['title'] = 'Search: ' . get_search_query();
		$title['site']  = 'LeadMAAXX';
	}
	return $title;
}
add_filter( 'document_title_parts', 'tenprojects_document_title_parts' );

/* ─── Force Classic Editor for Projects ──────────────────── */

/**
 * Use classic editor for tp_project so meta boxes are visible.
 */
function tp_use_classic_editor( $use_block_editor, $post_type ) {
	if ( $post_type === 'tp_project' ) {
		return false;
	}
	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'tp_use_classic_editor', 10, 2 );

/* ─── Floor Plans Tab (injected into plugin's Settings meta box) ── */

/**
 * Inject "Floor Plans" tab + panel into the plugin's tabbed Settings meta box.
 * Supports grouped configurations, each with multiple units (carpet sizes).
 */
function tp_floor_plans_admin_tab() {
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'tp_project' || $screen->base !== 'post' ) {
		return;
	}

	global $post;
	if ( ! $post ) return;

	$nonce = wp_create_nonce( 'tp_floor_plans_save' );

	$raw = get_post_meta( $post->ID, '_tp_floor_plans', true );
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}

	// Normalize old flat format → grouped format for admin display.
	$grouped = array();
	if ( ! empty( $raw ) ) {
		if ( isset( $raw[0]['config'] ) ) {
			$grouped = $raw;
		} else {
			// Old flat format — group by label.
			$by_label = array();
			foreach ( $raw as $fp ) {
				$label = $fp['label'] ?? 'Other';
				if ( ! isset( $by_label[ $label ] ) ) {
					$by_label[ $label ] = array();
				}
				$by_label[ $label ][] = array(
					'area'  => $fp['area'] ?? '',
					'price' => $fp['price'] ?? '',
					'image' => $fp['image'] ?? '',
				);
			}
			foreach ( $by_label as $label => $units ) {
				$grouped[] = array( 'config' => $label, 'units' => $units );
			}
		}
	}

	$grouped_json = wp_json_encode( $grouped );
	?>
	<script>
	jQuery(function($){
		$('#post').append('<input type="hidden" name="tp_floor_plans_nonce" value="<?php echo esc_attr( $nonce ); ?>">');

		var tabs = document.querySelector('.tp-meta-tabs');
		var panels = document.querySelector('.tp-meta-panels');
		if (!tabs || !panels) return;

		var pricingTab = tabs.querySelector('[data-tab="pricing"]');
		var fpTab = document.createElement('button');
		fpTab.type = 'button';
		fpTab.className = 'tp-meta-tab';
		fpTab.setAttribute('data-tab', 'floorplans');
		fpTab.textContent = 'Floor Plans';
		if (pricingTab && pricingTab.nextSibling) {
			tabs.insertBefore(fpTab, pricingTab.nextSibling);
		} else {
			tabs.appendChild(fpTab);
		}

		var panel = document.createElement('div');
		panel.className = 'tp-meta-panel';
		panel.setAttribute('data-panel', 'floorplans');
		panel.innerHTML = '<h3 style="margin:0 0 4px;font-size:14px;">Floor Plans by Configuration</h3>'
			+ '<p style="color:#666;margin-bottom:12px;font-size:13px;">Add configurations (e.g. 2 BHK, 3 BHK) and multiple carpet sizes/units within each.</p>'
			+ '<div id="tp-fp-configs"></div>'
			+ '<button type="button" class="button button-primary" id="tp-fp-add-config" style="margin-top:8px;">+ Add Configuration</button>';

		var pricingPanel = panels.querySelector('[data-panel="pricing"]');
		if (pricingPanel && pricingPanel.nextSibling) {
			panels.insertBefore(panel, pricingPanel.nextSibling);
		} else {
			panels.appendChild(panel);
		}

		fpTab.addEventListener('click', function(e){
			e.preventDefault();
			tabs.querySelectorAll('.tp-meta-tab').forEach(function(t){ t.classList.remove('active'); });
			fpTab.classList.add('active');
			panels.querySelectorAll('.tp-meta-panel').forEach(function(p){ p.classList.remove('active'); });
			panel.classList.add('active');
		});

		var configIdx = 0;

		function addUnit(container, ci, unit) {
			var ui = container.children.length;
			var img = unit.image || '';
			var imgStyle = img ? '' : 'display:none;';
			var row = $('<div class="tp-fp-unit-row" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-start;padding:10px;background:#fff;border:1px solid #e5e5e5;border-radius:4px;margin-bottom:6px;">'
				+ '<div style="flex:1;min-width:120px;"><label style="display:block;font-weight:500;margin-bottom:3px;font-size:12px;color:#666;">Carpet Area</label><input type="text" name="tp_fp['+ci+'][units]['+ui+'][area]" value="'+esc(unit.area || '')+'" placeholder="e.g. 650 sq.ft." style="width:100%;"></div>'
				+ '<div style="flex:1;min-width:120px;"><label style="display:block;font-weight:500;margin-bottom:3px;font-size:12px;color:#666;">Price</label><input type="text" name="tp_fp['+ci+'][units]['+ui+'][price]" value="'+esc(unit.price || '')+'" placeholder="e.g. 85 L" style="width:100%;"></div>'
				+ '<div style="flex:2;min-width:180px;"><label style="display:block;font-weight:500;margin-bottom:3px;font-size:12px;color:#666;">Floor Plan Image</label><div style="display:flex;gap:6px;align-items:center;"><input type="hidden" name="tp_fp['+ci+'][units]['+ui+'][image]" value="'+esc(img)+'" class="tp-fp-image-input"><img src="'+esc(img)+'" style="height:50px;border:1px solid #ddd;border-radius:4px;'+imgStyle+'" class="tp-fp-preview"><button type="button" class="button button-small tp-fp-upload">Upload</button><button type="button" class="button button-small tp-fp-remove" style="color:#a00;">×</button></div></div>'
				+ '<button type="button" class="button button-small tp-fp-del-unit" style="color:#a00;margin-top:18px;" title="Remove unit">✕</button>'
				+ '</div>');
			$(container).append(row);
		}

		function addConfig(config) {
			var ci = configIdx++;
			var group = $('<div class="tp-fp-config-group" style="border:1px solid #c3c4c7;border-radius:6px;margin-bottom:12px;overflow:hidden;">'
				+ '<div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f0f0f1;border-bottom:1px solid #c3c4c7;">'
				+ '<strong style="font-size:13px;white-space:nowrap;">Configuration:</strong>'
				+ '<input type="text" name="tp_fp['+ci+'][config]" value="'+esc(config.config || '')+'" placeholder="e.g. 3 BHK" style="flex:1;font-weight:600;">'
				+ '<button type="button" class="button button-small tp-fp-del-config" style="color:#a00;">Delete Config</button>'
				+ '</div>'
				+ '<div class="tp-fp-units-list" style="padding:10px 12px;"></div>'
				+ '<div style="padding:0 12px 10px;"><button type="button" class="button button-small tp-fp-add-unit">+ Add Unit</button></div>'
				+ '</div>');
			$('#tp-fp-configs').append(group);

			var unitsList = group.find('.tp-fp-units-list')[0];
			if (config.units && config.units.length) {
				config.units.forEach(function(u){ addUnit(unitsList, ci, u); });
			} else {
				addUnit(unitsList, ci, {});
			}
		}

		function esc(s) { return $('<span>').text(s).html(); }

		// Load existing data
		var existing = <?php echo $grouped_json; ?>;
		existing.forEach(function(g){ addConfig(g); });

		// Add new config
		$(document).on('click', '#tp-fp-add-config', function(){ addConfig({ config: '', units: [{}] }); });

		// Delete config
		$(document).on('click', '.tp-fp-del-config', function(){ $(this).closest('.tp-fp-config-group').remove(); });

		// Add unit within config
		$(document).on('click', '.tp-fp-add-unit', function(){
			var group = $(this).closest('.tp-fp-config-group');
			var ci = group.find('input[name$="[config]"]').attr('name').match(/\d+/)[0];
			var unitsList = group.find('.tp-fp-units-list')[0];
			addUnit(unitsList, ci, {});
		});

		// Delete unit
		$(document).on('click', '.tp-fp-del-unit', function(){ $(this).closest('.tp-fp-unit-row').remove(); });

		// Upload image
		$(document).on('click', '.tp-fp-upload', function(){
			var row = $(this).closest('.tp-fp-unit-row');
			var frame = wp.media({ title: 'Select Floor Plan Image', multiple: false, library: { type: 'image' } });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				row.find('.tp-fp-image-input').val(att.url);
				row.find('.tp-fp-preview').attr('src', att.url).show();
			});
			frame.open();
		});

		// Remove image
		$(document).on('click', '.tp-fp-remove', function(){
			var row = $(this).closest('.tp-fp-unit-row');
			row.find('.tp-fp-image-input').val('');
			row.find('.tp-fp-preview').attr('src','').hide();
		});
	});
	</script>
	<?php
}
add_action( 'admin_footer', 'tp_floor_plans_admin_tab' );

/**
 * Save floor plans meta data (grouped format).
 */
function tp_floor_plans_save( $post_id ) {
	if ( ! isset( $_POST['tp_floor_plans_nonce'] ) || ! wp_verify_nonce( $_POST['tp_floor_plans_nonce'], 'tp_floor_plans_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['tp_fp'] ) ? $_POST['tp_fp'] : array();
	$plans = array();

	if ( is_array( $raw ) ) {
		foreach ( $raw as $group ) {
			$config = sanitize_text_field( $group['config'] ?? '' );
			$units = array();
			if ( ! empty( $group['units'] ) && is_array( $group['units'] ) ) {
				foreach ( $group['units'] as $u ) {
					$area  = sanitize_text_field( $u['area'] ?? '' );
					$price = sanitize_text_field( $u['price'] ?? '' );
					$image = esc_url_raw( $u['image'] ?? '' );
					if ( empty( $area ) && empty( $image ) && empty( $price ) ) {
						continue;
					}
					$units[] = array( 'area' => $area, 'price' => $price, 'image' => $image );
				}
			}
			if ( empty( $config ) && empty( $units ) ) {
				continue;
			}
			$plans[] = array( 'config' => $config, 'units' => $units );
		}
	}

	update_post_meta( $post_id, '_tp_floor_plans', $plans );
}
add_action( 'save_post_tp_project', 'tp_floor_plans_save' );

/* ─── Chatbot: Lead Capture Widget ───────────────────────── */

/**
 * Register hidden CPT for chatbot leads.
 */
function tp_register_chatbot_lead_cpt() {
	register_post_type( 'tp_chatbot_lead', array(
		'labels'       => array(
			'name'          => 'Chatbot Leads',
			'singular_name' => 'Chatbot Lead',
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-format-chat',
		'supports'     => array( 'title' ),
		'capabilities' => array(
			'create_posts' => 'do_not_allow',
		),
		'map_meta_cap' => true,
	) );
}
add_action( 'init', 'tp_register_chatbot_lead_cpt' );

/**
 * Add meta columns to leads admin list.
 */
function tp_chatbot_lead_columns( $columns ) {
	return array(
		'cb'       => $columns['cb'],
		'title'    => 'Lead',
		'phone'    => 'Phone',
		'config'   => 'Config',
		'budget'   => 'Budget',
		'location' => 'Location',
		'timeline' => 'Timeline',
		'date'     => 'Date',
	);
}
add_filter( 'manage_tp_chatbot_lead_posts_columns', 'tp_chatbot_lead_columns' );

function tp_chatbot_lead_column_data( $column, $post_id ) {
	$meta = get_post_meta( $post_id );
	switch ( $column ) {
		case 'phone':
			echo esc_html( $meta['_lead_phone'][0] ?? '' );
			break;
		case 'config':
			echo esc_html( $meta['_lead_config'][0] ?? '' );
			break;
		case 'budget':
			echo esc_html( $meta['_lead_budget'][0] ?? '' );
			break;
		case 'location':
			echo esc_html( $meta['_lead_location'][0] ?? '' );
			break;
		case 'timeline':
			echo esc_html( $meta['_lead_timeline'][0] ?? '' );
			break;
	}
}
add_action( 'manage_tp_chatbot_lead_posts_custom_column', 'tp_chatbot_lead_column_data', 10, 2 );

/**
 * Enqueue chatbot JS + pass data.
 */
function tp_enqueue_chatbot() {
	$js_path = get_template_directory() . '/assets/js/chatbot.js';
	if ( ! file_exists( $js_path ) ) return;

	wp_enqueue_script(
		'tp-chatbot',
		get_template_directory_uri() . '/assets/js/chatbot.js',
		array(),
		'2.2',
		true
	);

	// Get location terms for chips.
	$locations = get_terms( array(
		'taxonomy'   => 'tp_location_area',
		'hide_empty' => false,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 12,
	) );
	$loc_names = array();
	if ( ! is_wp_error( $locations ) ) {
		foreach ( $locations as $loc ) {
			$loc_names[] = $loc->name;
		}
	}

	// Get project configurations for chatbot chips.
	$project_configs = array();
	if ( is_singular( 'tp_project' ) ) {
		global $wpdb;
		$table = $wpdb->prefix . 'tp_project_configurations';
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table ) {
			$rows = $wpdb->get_col( $wpdb->prepare(
				"SELECT DISTINCT configuration FROM {$table} WHERE project_id = %d ORDER BY configuration ASC",
				get_the_ID()
			) );
			if ( $rows ) $project_configs = $rows;
		}
		// Fallback to text meta
		if ( empty( $project_configs ) ) {
			$text = tp_get_meta( get_the_ID(), 'available_configs_text' );
			if ( $text ) {
				$project_configs = array_map( 'trim', preg_split( '/[,&]/', $text ) );
			}
		}
	}

	// Determine page title for chatbot greeting.
	$cb_page_title = '';
	if ( is_singular( 'tp_project' ) ) {
		$cb_page_title = get_the_title();
	} elseif ( ! empty( $GLOBALS['tp_lp_post_id'] ) ) {
		$cb_page_title = get_post_meta( $GLOBALS['tp_lp_post_id'], '_tp_lp_project_name', true );
	}

	wp_localize_script( 'tp-chatbot', 'tpChatbot', array(
		'ajax_url'      => admin_url( 'admin-ajax.php' ),
		'nonce'         => wp_create_nonce( 'tp_chatbot_nonce' ),
		'locations'     => $loc_names,
		'page_title'    => $cb_page_title,
		'configs'       => $project_configs,
		'thankyou_url'  => home_url( '/thank-you/' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'tp_enqueue_chatbot' );

/**
 * Listing-card overview toggle — inline JS injected on archive & search pages.
 */
add_action( 'wp_footer', function () {
	if ( ! is_tax() && ! is_search() && ! is_post_type_archive() ) return;
	?>
	<script>
	(function(){
		document.addEventListener('click', function(e){
			var toggle = e.target.closest('.tp-lp-card__overview-toggle');
			if (!toggle) return;
			var expanded = toggle.getAttribute('aria-expanded') === 'true';
			toggle.setAttribute('aria-expanded', !expanded);
			var text = toggle.closest('.tp-lp-card__overview').querySelector('.tp-lp-card__overview-text');
			if (text) text.classList.toggle('is-open', !expanded);
		});
	})();
	</script>
	<?php
} );

/**
 * Lead email helpers — polished HTML notifications.
 */
function tp_lead_email_html_type() {
	return 'text/html';
}

function tp_lead_email_row( $label, $value ) {
	return '<tr><td style="padding:12px 16px;font-size:13px;color:#6b7280;border-bottom:1px solid #f3f4f6;width:140px;font-weight:500;">' . esc_html( $label ) . '</td><td style="padding:12px 16px;font-size:15px;color:#111827;border-bottom:1px solid #f3f4f6;font-weight:600;">' . esc_html( $value ) . '</td></tr>';
}

function tp_lead_email_html( $source_label, $rows_html, $page_url, $phone ) {
	$time = current_time( 'j M Y, g:i A' );
	$call_url = 'tel:+91' . preg_replace( '/[^0-9]/', '', $phone );
	$wa_url   = 'https://wa.me/91' . preg_replace( '/[^0-9]/', '', $phone );

	return '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f0f2f5;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f2f5;padding:32px 16px;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

<!-- Alert Banner -->
<tr><td style="background:linear-gradient(135deg,#1a56db,#1e40af);padding:28px 32px;text-align:center;">
<div style="font-size:32px;margin-bottom:8px;">&#128276;</div>
<div style="color:#ffffff;font-size:22px;font-weight:700;letter-spacing:-0.3px;">New Lead Alert</div>
<div style="color:rgba(255,255,255,0.8);font-size:13px;margin-top:6px;">' . esc_html( $source_label ) . ' &bull; ' . esc_html( $time ) . '</div>
</td></tr>

<!-- Phone Highlight -->
<tr><td style="padding:24px 32px 0;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;border-radius:12px;">
<tr><td style="padding:20px 24px;text-align:center;">
<div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:#15803d;font-weight:700;margin-bottom:6px;">CONTACT NUMBER</div>
<div style="font-size:28px;font-weight:800;color:#166534;letter-spacing:0.5px;">+91 ' . esc_html( preg_replace( '/[^0-9]/', '', $phone ) ) . '</div>
</td></tr>
</table>
</td></tr>

<!-- Lead Details -->
<tr><td style="padding:20px 32px;">
<div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:#9ca3af;font-weight:700;margin-bottom:12px;">LEAD DETAILS</div>
<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
' . $rows_html . '
</table>
</td></tr>

<!-- Action Buttons -->
<tr><td style="padding:0 32px 24px;">
<table width="100%" cellpadding="0" cellspacing="0"><tr>
<td width="48%" style="padding-right:8px;">
<a href="' . esc_url( $call_url ) . '" style="display:block;background:#1a56db;color:#ffffff;text-decoration:none;text-align:center;padding:14px 16px;border-radius:10px;font-size:14px;font-weight:700;">&#128222; Call Now</a>
</td>
<td width="48%" style="padding-left:8px;">
<a href="' . esc_url( $wa_url ) . '" style="display:block;background:#25d366;color:#ffffff;text-decoration:none;text-align:center;padding:14px 16px;border-radius:10px;font-size:14px;font-weight:700;">&#128172; WhatsApp</a>
</td>
</tr></table>
</td></tr>

<!-- Source Page -->
<tr><td style="padding:0 32px 24px;">
<div style="background:#f9fafb;border-radius:10px;padding:14px 18px;">
<div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;font-weight:600;margin-bottom:4px;">SOURCE PAGE</div>
<a href="' . esc_url( $page_url ) . '" style="font-size:13px;color:#1a56db;text-decoration:none;word-break:break-all;">' . esc_html( $page_url ) . '</a>
</div>
</td></tr>

<!-- Footer -->
<tr><td style="background:#f9fafb;padding:16px 32px;text-align:center;border-top:1px solid #e5e7eb;">
<div style="font-size:12px;color:#9ca3af;">Powered by <strong style="color:#6b7280;">SiteClap Connect</strong></div>
</td></tr>

</table>
</td></tr>
</table>
</body></html>';
}

/**
 * Collect tracking data from POST and save to post meta.
 */
function tp_lead_save_tracking( $post_id ) {
	$tracking_fields = array(
		'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
		'referrer', 'device', 'browser', 'os', 'screen', 'landing_page',
	);
	foreach ( $tracking_fields as $f ) {
		$val = sanitize_text_field( $_POST[ $f ] ?? '' );
		if ( $val ) update_post_meta( $post_id, '_lead_' . $f, $val );
	}
	// IP address from server
	$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
	if ( strpos( $ip, ',' ) !== false ) $ip = trim( explode( ',', $ip )[0] );
	if ( $ip ) update_post_meta( $post_id, '_lead_ip', sanitize_text_field( $ip ) );
	// City/Country from Cloudflare headers
	$country = sanitize_text_field( $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '' );
	if ( $country ) update_post_meta( $post_id, '_lead_country', $country );
}

function tp_lead_tracking_email_rows() {
	$rows = '';
	$ip      = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
	if ( strpos( $ip, ',' ) !== false ) $ip = trim( explode( ',', $ip )[0] );
	$device  = sanitize_text_field( $_POST['device'] ?? '' );
	$browser = sanitize_text_field( $_POST['browser'] ?? '' );
	$os      = sanitize_text_field( $_POST['os'] ?? '' );
	$scr     = sanitize_text_field( $_POST['screen'] ?? '' );
	$ref     = sanitize_text_field( $_POST['referrer'] ?? '' );
	$utm_s   = sanitize_text_field( $_POST['utm_source'] ?? '' );
	$utm_m   = sanitize_text_field( $_POST['utm_medium'] ?? '' );
	$utm_c   = sanitize_text_field( $_POST['utm_campaign'] ?? '' );
	$country = sanitize_text_field( $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '' );

	// Device & Location section
	$device_str = implode( ' / ', array_filter( array( ucfirst( $device ), $os, $browser ) ) );
	if ( $device_str ) $rows .= tp_lead_email_row( 'Device', $device_str );
	if ( $scr )        $rows .= tp_lead_email_row( 'Screen', $scr );
	if ( $ip )         $rows .= tp_lead_email_row( 'IP Address', $ip . ( $country ? ' (' . $country . ')' : '' ) );
	if ( $ref )        $rows .= tp_lead_email_row( 'Referrer', $ref );

	// UTM section
	$utm_parts = array_filter( array( $utm_s, $utm_m, $utm_c ) );
	if ( $utm_parts )  $rows .= tp_lead_email_row( 'UTM', implode( ' / ', $utm_parts ) );

	return $rows;
}

/**
 * AJAX handler: save chatbot lead.
 */
function tp_chatbot_save_lead() {
	check_ajax_referer( 'tp_chatbot_nonce', 'nonce' );

	$name     = sanitize_text_field( $_POST['name'] ?? '' );
	$phone    = sanitize_text_field( $_POST['phone'] ?? '' );
	$config   = sanitize_text_field( $_POST['config'] ?? '' );
	$budget   = sanitize_text_field( $_POST['budget'] ?? '' );
	$location = sanitize_text_field( $_POST['location'] ?? '' );
	$timeline = sanitize_text_field( $_POST['timeline'] ?? '' );
	$page_url = esc_url_raw( $_POST['page_url'] ?? '' );
	$flow     = sanitize_text_field( $_POST['flow'] ?? 'general' );
	$visited  = sanitize_text_field( $_POST['visited'] ?? '' );
	$pres_date = sanitize_text_field( $_POST['presentation_date'] ?? '' );
	$pres_time = sanitize_text_field( $_POST['presentation_time'] ?? '' );

	if ( ! $phone || strlen( $phone ) < 10 ) {
		wp_send_json_error( 'Valid phone number required.' );
	}

	$post_id = wp_insert_post( array(
		'post_type'   => 'tp_chatbot_lead',
		'post_title'  => ( $name ? $name . ' — ' : '' ) . $phone . ' (' . $flow . ')',
		'post_status' => 'publish',
		'meta_input'  => array(
			'_lead_name'              => $name,
			'_lead_phone'             => $phone,
			'_lead_config'            => $config,
			'_lead_budget'            => $budget,
			'_lead_location'          => $location,
			'_lead_timeline'          => $timeline,
			'_lead_source'            => 'chatbot',
			'_lead_flow'              => $flow,
			'_lead_visited'           => $visited,
			'_lead_presentation_date' => $pres_date,
			'_lead_presentation_time' => $pres_time,
			'_lead_page'              => $page_url,
		),
	) );

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		tp_lead_save_tracking( $post_id );

		$flow_label = ucfirst( str_replace( '_', ' ', $flow ) );
		$subject = "\xF0\x9F\x94\x94 New Lead [Chatbot — " . $flow_label . '] +91 ' . $phone;

		$rows = '';
		$rows .= tp_lead_email_row( 'Phone', '+91 ' . $phone );
		if ( $name )      $rows .= tp_lead_email_row( 'Name', $name );
		if ( $config )    $rows .= tp_lead_email_row( 'Configuration', $config );
		if ( $budget )    $rows .= tp_lead_email_row( 'Budget', $budget );
		if ( $location )  $rows .= tp_lead_email_row( 'Location', $location );
		if ( $timeline )  $rows .= tp_lead_email_row( 'Timeline', $timeline );
		if ( $visited )   $rows .= tp_lead_email_row( 'Visited Before', $visited );
		if ( $pres_date ) $rows .= tp_lead_email_row( 'Presentation', $pres_date . ' at ' . $pres_time );
		$rows .= tp_lead_tracking_email_rows();

		$body = tp_lead_email_html( 'Chatbot — ' . $flow_label, $rows, $page_url, $phone );

		add_filter( 'wp_mail_content_type', 'tp_lead_email_html_type' );
		wp_mail( 'connect.siteclap@gmail.com', $subject, $body );
		remove_filter( 'wp_mail_content_type', 'tp_lead_email_html_type' );
	}

	wp_send_json_success( array( 'id' => $post_id ) );
}
add_action( 'wp_ajax_tp_chatbot_lead', 'tp_chatbot_save_lead' );
add_action( 'wp_ajax_nopriv_tp_chatbot_lead', 'tp_chatbot_save_lead' );

/**
 * AJAX handler: save sidebar lead form.
 */
function tp_sidebar_save_lead() {
	check_ajax_referer( 'tp_sidebar_lead_nonce', 'nonce' );

	$name        = sanitize_text_field( $_POST['name'] ?? '' );
	$phone       = preg_replace( '/[^0-9]/', '', $_POST['phone'] ?? '' );
	$email       = sanitize_email( $_POST['email'] ?? '' );
	$project     = sanitize_text_field( $_POST['project_name'] ?? '' );
	$url         = esc_url_raw( $_POST['page_url'] ?? '' );
	$lead_source = sanitize_text_field( $_POST['lead_source'] ?? 'sidebar_form' );

	if ( strlen( $phone ) < 10 ) {
		wp_send_json_error( 'Invalid phone number' );
	}

	$post_id = wp_insert_post( array(
		'post_type'   => 'tp_chatbot_lead',
		'post_status' => 'publish',
		'post_title'  => $name ?: 'Lead — ' . $phone,
	) );

	if ( $post_id ) {
		update_post_meta( $post_id, '_lead_name', $name );
		update_post_meta( $post_id, '_lead_phone', $phone );
		update_post_meta( $post_id, '_lead_email', $email );
		update_post_meta( $post_id, '_lead_source', $lead_source );
		update_post_meta( $post_id, '_lead_page', $url );
		update_post_meta( $post_id, '_lead_project', $project );
		tp_lead_save_tracking( $post_id );

		// Email notification
		$source_label = ucwords( str_replace( array( '_', '-' ), ' ', $lead_source ) );
		$subject = "\xF0\x9F\x94\x94 New Lead [" . $source_label . '] +91 ' . $phone . ( $project ? ' — ' . $project : '' );

		$rows = '';
		$rows .= tp_lead_email_row( 'Phone', '+91 ' . $phone );
		if ( $name )    $rows .= tp_lead_email_row( 'Name', $name );
		if ( $email )   $rows .= tp_lead_email_row( 'Email', $email );
		if ( $project ) $rows .= tp_lead_email_row( 'Project', $project );
		$rows .= tp_lead_tracking_email_rows();

		$body = tp_lead_email_html( $source_label . ( $project ? ' — ' . $project : '' ), $rows, $url, $phone );

		add_filter( 'wp_mail_content_type', 'tp_lead_email_html_type' );
		wp_mail( 'connect.siteclap@gmail.com', $subject, $body );
		remove_filter( 'wp_mail_content_type', 'tp_lead_email_html_type' );
	}

	wp_send_json_success( array( 'id' => $post_id ) );
}
add_action( 'wp_ajax_tp_sidebar_lead', 'tp_sidebar_save_lead' );
add_action( 'wp_ajax_nopriv_tp_sidebar_lead', 'tp_sidebar_save_lead' );

/**
 * AJAX handler: save blog CTA sidebar lead.
 */
function tp_blog_cta_save_lead() {
	check_ajax_referer( 'tp_blog_cta_nonce', 'nonce' );

	$phone = preg_replace( '/[^0-9]/', '', $_POST['phone'] ?? '' );
	$url   = esc_url_raw( $_POST['page_url'] ?? '' );

	if ( strlen( $phone ) < 10 ) {
		wp_send_json_error( 'Invalid phone number' );
	}

	$post_id = wp_insert_post( array(
		'post_type'   => 'tp_chatbot_lead',
		'post_status' => 'publish',
		'post_title'  => 'Blog Lead — ' . $phone,
	) );

	if ( $post_id ) {
		update_post_meta( $post_id, '_lead_phone', $phone );
		update_post_meta( $post_id, '_lead_source', 'blog_cta' );
		update_post_meta( $post_id, '_lead_page', $url );
		tp_lead_save_tracking( $post_id );

		$subject = "\xF0\x9F\x94\x94 New Lead [Blog CTA] +91 " . $phone;

		$rows = '';
		$rows .= tp_lead_email_row( 'Phone', '+91 ' . $phone );
		$rows .= tp_lead_tracking_email_rows();

		$body = tp_lead_email_html( 'Blog CTA', $rows, $url, $phone );

		add_filter( 'wp_mail_content_type', 'tp_lead_email_html_type' );
		wp_mail( 'connect.siteclap@gmail.com', $subject, $body );
		remove_filter( 'wp_mail_content_type', 'tp_lead_email_html_type' );
	}

	wp_send_json_success( array( 'id' => $post_id ) );
}
add_action( 'wp_ajax_tp_blog_cta_lead', 'tp_blog_cta_save_lead' );
add_action( 'wp_ajax_nopriv_tp_blog_cta_lead', 'tp_blog_cta_save_lead' );

/**
 * AJAX handler: save contact page form lead.
 */
function tp_contact_form_save_lead() {
	check_ajax_referer( 'tp_contact_form_nonce', 'nonce' );

	$name    = sanitize_text_field( $_POST['name'] ?? '' );
	$phone   = preg_replace( '/[^0-9]/', '', $_POST['phone'] ?? '' );
	$message = sanitize_textarea_field( $_POST['message'] ?? '' );
	$url     = esc_url_raw( $_POST['page_url'] ?? '' );

	if ( strlen( $phone ) < 10 ) {
		wp_send_json_error( 'Invalid phone number' );
	}

	$post_id = wp_insert_post( array(
		'post_type'   => 'tp_chatbot_lead',
		'post_status' => 'publish',
		'post_title'  => $name ?: 'Contact Lead — ' . $phone,
	) );

	if ( $post_id ) {
		update_post_meta( $post_id, '_lead_name', $name );
		update_post_meta( $post_id, '_lead_phone', $phone );
		update_post_meta( $post_id, '_lead_source', 'contact_form' );
		update_post_meta( $post_id, '_lead_page', $url );
		if ( $message ) {
			update_post_meta( $post_id, '_lead_message', $message );
		}
		tp_lead_save_tracking( $post_id );

		$subject = "\xF0\x9F\x94\x94 New Lead [Contact Form] +91 " . $phone . ( $name ? ' — ' . $name : '' );

		$rows = '';
		$rows .= tp_lead_email_row( 'Phone', '+91 ' . $phone );
		if ( $name )    $rows .= tp_lead_email_row( 'Name', $name );
		if ( $message ) $rows .= tp_lead_email_row( 'Message', nl2br( esc_html( $message ) ) );
		$rows .= tp_lead_tracking_email_rows();

		$body = tp_lead_email_html( 'Contact Form', $rows, $url, $phone );

		add_filter( 'wp_mail_content_type', 'tp_lead_email_html_type' );
		wp_mail( 'connect.siteclap@gmail.com', $subject, $body );
		remove_filter( 'wp_mail_content_type', 'tp_lead_email_html_type' );
	}

	wp_send_json_success( array( 'id' => $post_id ) );
}
add_action( 'wp_ajax_tp_contact_form_lead', 'tp_contact_form_save_lead' );
add_action( 'wp_ajax_nopriv_tp_contact_form_lead', 'tp_contact_form_save_lead' );

/**
 * Project Brochure — ?brochure=1 renders a clean PDF-ready page.
 */
function tp_brochure_template_redirect() {
	if ( ! is_singular( 'tp_project' ) || ! isset( $_GET['brochure'] ) ) return;
	get_template_part( 'template-parts/project/brochure' );
	exit;
}
add_action( 'template_redirect', 'tp_brochure_template_redirect' );

// Landing page URL resolution is handled in Landing_Page_CPT::resolve_landing_page().

/* ─── Thank You Page (Virtual Route) ─────────────────────── */

/**
 * Render a standalone thank you page at /thank-you/.
 * No WP page required — intercepted via template_redirect.
 */
function tp_thankyou_page() {
	$uri = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
	if ( $uri !== 'thank-you' ) {
		return;
	}

	$name    = isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : '';
	$project = isset( $_GET['project'] ) ? sanitize_text_field( wp_unslash( $_GET['project'] ) ) : '';
	$display = $name ?: 'there';

	nocache_headers();
	status_header( 200 );

	?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Thank You — <?php echo esc_html( $project ?: 'LeadMAAXX' ); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#f0f4ff 0%,#e8f0fe 50%,#f5f0ff 100%);padding:24px;color:#111827;}
.ty{text-align:center;max-width:520px;width:100%;}
.ty__icon{width:80px;height:80px;margin:0 auto 24px;background:linear-gradient(135deg,#10b981,#059669);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 32px rgba(16,185,129,0.25);animation:tyPop 0.5s cubic-bezier(0.34,1.56,0.64,1);}
@keyframes tyPop{from{transform:scale(0);opacity:0}to{transform:scale(1);opacity:1}}
.ty__icon svg{width:40px;height:40px;color:#fff;stroke-width:3;}
.ty__title{font-size:28px;font-weight:800;color:#111827;margin-bottom:8px;letter-spacing:-0.5px;}
.ty__sub{font-size:16px;color:#4b5563;margin-bottom:32px;line-height:1.6;}
.ty__sub strong{color:#111827;}
.ty__card{background:#fff;border-radius:16px;padding:28px 24px;box-shadow:0 4px 24px rgba(0,0,0,0.06);margin-bottom:24px;text-align:left;}
.ty__card-title{font-size:14px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:16px;}
.ty__step{display:flex;align-items:flex-start;gap:14px;padding:12px 0;border-bottom:1px solid #f3f4f6;}
.ty__step:last-child{border-bottom:none;}
.ty__step-num{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#1a56db,#1e40af);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ty__step-text{font-size:14px;color:#374151;line-height:1.5;}
.ty__step-text strong{color:#111827;font-weight:600;}
.ty__back{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:linear-gradient(135deg,#1a56db,#1e40af);color:#fff;border-radius:12px;font-size:15px;font-weight:700;text-decoration:none;transition:all 0.25s ease;box-shadow:0 4px 16px rgba(26,86,219,0.25);}
.ty__back:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(26,86,219,0.35);}
.ty__back svg{width:16px;height:16px;}
@media(max-width:480px){.ty__title{font-size:22px;}.ty__sub{font-size:14px;}.ty__card{padding:20px 18px;}}
</style>
</head>
<body>
<div class="ty">
	<div class="ty__icon">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
	</div>
	<h1 class="ty__title">Thank You, <?php echo esc_html( $display ); ?>!</h1>
	<p class="ty__sub">Your enquiry<?php if ( $project ) : ?> for <strong><?php echo esc_html( $project ); ?></strong><?php endif; ?> has been received successfully.</p>
	<div class="ty__card">
		<div class="ty__card-title">What happens next?</div>
		<div class="ty__step">
			<div class="ty__step-num">1</div>
			<div class="ty__step-text"><strong>Instant callback</strong> — Our team will call you within 5 minutes.</div>
		</div>
		<div class="ty__step">
			<div class="ty__step-num">2</div>
			<div class="ty__step-text"><strong>Best offer shared</strong> — We'll send you the latest pricing and exclusive deals.</div>
		</div>
		<div class="ty__step">
			<div class="ty__step-num">3</div>
			<div class="ty__step-text"><strong>Free site visit</strong> — We'll arrange a complimentary visit with pickup & drop.</div>
		</div>
	</div>
	<a href="javascript:history.back()" class="ty__back">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
		Go Back
	</a>
</div>
</body>
</html><?php
	exit;
}
add_action( 'template_redirect', 'tp_thankyou_page', 5 );

/* ─── Google Reviews (Places API) ────────────────────────── */

/**
 * Fetch Google reviews for the configured business.
 * Returns cached reviews (24 hr transient) or fetches fresh from API.
 *
 * @param bool $force_refresh Skip transient cache.
 * @return array Array of review arrays, or empty array on failure.
 */
function tp_get_google_reviews( $force_refresh = false ) {
	$cache_key = 'tp_google_reviews';

	if ( ! $force_refresh ) {
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}
	}

	$api_key  = get_option( 'tp_google_places_api_key', '' );
	$place_id = get_option( 'tp_google_place_id', '' );
	$biz_name = get_option( 'tp_google_business_name', '' );

	if ( empty( $api_key ) ) {
		return array();
	}

	// If no Place ID, try to find it from business name.
	if ( empty( $place_id ) && ! empty( $biz_name ) ) {
		$search_url = add_query_arg( array(
			'input'     => $biz_name,
			'inputtype' => 'textquery',
			'fields'    => 'place_id',
			'key'       => $api_key,
		), 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json' );

		$search_resp = wp_remote_get( $search_url, array( 'timeout' => 10 ) );
		if ( ! is_wp_error( $search_resp ) ) {
			$search_data = json_decode( wp_remote_retrieve_body( $search_resp ), true );
			if ( ! empty( $search_data['candidates'][0]['place_id'] ) ) {
				$place_id = $search_data['candidates'][0]['place_id'];
				update_option( 'tp_google_place_id', $place_id );
			}
		}
	}

	if ( empty( $place_id ) ) {
		return array();
	}

	// Fetch place details with reviews.
	$details_url = add_query_arg( array(
		'place_id' => $place_id,
		'fields'   => 'name,rating,user_ratings_total,reviews',
		'key'      => $api_key,
		'reviews_sort' => 'newest',
	), 'https://maps.googleapis.com/maps/api/place/details/json' );

	$details_resp = wp_remote_get( $details_url, array( 'timeout' => 10 ) );
	if ( is_wp_error( $details_resp ) ) {
		return array();
	}

	$details_data = json_decode( wp_remote_retrieve_body( $details_resp ), true );
	if ( empty( $details_data['result']['reviews'] ) ) {
		return array();
	}

	// Save overall rating and review count.
	if ( isset( $details_data['result']['rating'] ) ) {
		update_option( 'tp_google_overall_rating', $details_data['result']['rating'] );
	}
	if ( isset( $details_data['result']['user_ratings_total'] ) ) {
		update_option( 'tp_google_total_reviews', $details_data['result']['user_ratings_total'] );
	}

	$reviews = array();
	foreach ( $details_data['result']['reviews'] as $r ) {
		$reviews[] = array(
			'name'  => $r['author_name'] ?? '',
			'date'  => $r['relative_time_description'] ?? '',
			'stars' => (int) ( $r['rating'] ?? 5 ),
			'text'  => $r['text'] ?? '',
			'photo' => $r['profile_photo_url'] ?? '',
		);
	}

	// Cache for 24 hours.
	set_transient( $cache_key, $reviews, DAY_IN_SECONDS );

	return $reviews;
}

/**
 * AJAX handler: refresh Google reviews on demand from admin.
 */
function tp_ajax_refresh_google_reviews() {
	check_ajax_referer( 'tp_refresh_reviews' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Unauthorized' );
	}

	$reviews = tp_get_google_reviews( true );

	if ( empty( $reviews ) ) {
		$api_key  = get_option( 'tp_google_places_api_key', '' );
		$place_id = get_option( 'tp_google_place_id', '' );
		$biz_name = get_option( 'tp_google_business_name', '' );

		if ( empty( $api_key ) ) {
			wp_send_json_error( 'No API key configured' );
		} elseif ( empty( $place_id ) && empty( $biz_name ) ) {
			wp_send_json_error( 'No business name or Place ID configured' );
		} else {
			wp_send_json_error( 'No reviews found. Check business name or Place ID.' );
		}
	}

	wp_send_json_success( array( 'count' => count( $reviews ) ) );
}
add_action( 'wp_ajax_tp_refresh_google_reviews', 'tp_ajax_refresh_google_reviews' );
