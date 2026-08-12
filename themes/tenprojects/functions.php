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
function tp_format_price( $lakhs ) {
	if ( ! $lakhs || $lakhs <= 0 ) {
		return '';
	}
	if ( $lakhs >= 100 ) {
		$cr = $lakhs / 100;
		return '₹' . number_format( $cr, 2 ) . ' Cr';
	}
	return '₹' . number_format( $lakhs, 2 ) . ' L';
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
function tp_calculate_emi( $lakhs, $rate_annual = 8.5, $tenure_months = 240 ) {
	$principal = $lakhs * 100000; // Convert Lakhs to rupees.
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
		$pt_slug = sanitize_text_field( $_GET['property_type'] );
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
		$p_min = intval( tp_get_meta( $p_id, 'price_display_min' ) );
		$p_max = intval( tp_get_meta( $p_id, 'price_display_max' ) );

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
		filemtime( $js_path ),
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

	wp_localize_script( 'tp-chatbot', 'tpChatbot', array(
		'ajax_url'   => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'tp_chatbot_nonce' ),
		'locations'  => $loc_names,
		'page_title' => is_singular( 'tp_project' ) ? get_the_title() : '',
		'configs'    => $project_configs,
	) );
}
add_action( 'wp_enqueue_scripts', 'tp_enqueue_chatbot' );

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

	// Email notification.
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		$flow_label = ucfirst( str_replace( '_', ' ', $flow ) );
		$subject = 'New Chatbot Lead [' . $flow_label . ']: ' . $phone . ( $config ? ' (' . $config . ')' : '' );
		$body  = "Flow: $flow_label\nPhone: $phone\nConfig: $config\nBudget: $budget\n";
		if ( $visited ) $body .= "Visited Before: $visited\n";
		if ( $pres_date ) $body .= "Presentation Date: $pres_date\nPresentation Time: $pres_time\n";
		$body .= "Page: $page_url";
		wp_mail( 'hello@leadmaaxx.com', $subject, $body );
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

	$name    = sanitize_text_field( $_POST['name'] ?? '' );
	$phone   = preg_replace( '/[^0-9]/', '', $_POST['phone'] ?? '' );
	$email   = sanitize_email( $_POST['email'] ?? '' );
	$project = sanitize_text_field( $_POST['project_name'] ?? '' );
	$url     = esc_url_raw( $_POST['page_url'] ?? '' );

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
		update_post_meta( $post_id, '_lead_source', 'sidebar_form' );
		update_post_meta( $post_id, '_lead_page', $url );
		update_post_meta( $post_id, '_lead_project', $project );

		// Email notification
		$body = "New Lead from Sidebar Form\n\n";
		$body .= "Name: $name\nPhone: +91 $phone\nEmail: $email\n";
		$body .= "Project: $project\nPage: $url\n";
		wp_mail( 'hello@leadmaaxx.com', 'New Sidebar Lead — ' . $project, $body );
	}

	wp_send_json_success( array( 'id' => $post_id ) );
}
add_action( 'wp_ajax_tp_sidebar_lead', 'tp_sidebar_save_lead' );
add_action( 'wp_ajax_nopriv_tp_sidebar_lead', 'tp_sidebar_save_lead' );

/**
 * Project Brochure — ?brochure=1 renders a clean PDF-ready page.
 */
function tp_brochure_template_redirect() {
	if ( ! is_singular( 'tp_project' ) || ! isset( $_GET['brochure'] ) ) return;
	get_template_part( 'template-parts/project/brochure' );
	exit;
}
add_action( 'template_redirect', 'tp_brochure_template_redirect' );
