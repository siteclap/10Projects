<?php
/**
 * Template: Landing Page (tp_landing_page)
 *
 * Standalone conversion-focused page matching RevaHomes layout.
 * Does NOT use get_header()/get_footer().
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id = ! empty( $GLOBALS['tp_lp_post_id'] ) ? $GLOBALS['tp_lp_post_id'] : get_the_ID();

// Helper to read meta.
$m = function ( $key ) use ( $post_id ) {
	return get_post_meta( $post_id, '_tp_' . $key, true );
};

// Helper to decode JSON meta.
$j = function ( $key ) use ( $m ) {
	$raw = $m( $key );
	$arr = json_decode( $raw, true );
	return is_array( $arr ) ? $arr : array();
};

// Helper to get gallery URLs from comma-separated IDs.
$g = function ( $key ) use ( $m ) {
	$ids = array_filter( array_map( 'intval', explode( ',', $m( $key ) ) ) );
	$urls = array();
	foreach ( $ids as $id ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( $url ) {
			$urls[] = $url;
		}
	}
	return $urls;
};

// Collect all values.
$project_name     = $m( 'lp_project_name' );
$developer_name   = $m( 'lp_developer_name' );
$location         = $m( 'lp_location' );
$configs          = $m( 'lp_configs' );
$total_floors     = $m( 'lp_total_floors' );
$land_parcel      = $m( 'lp_land_parcel' );
$possession       = $m( 'lp_possession' );
$price_min        = $m( 'lp_price_display_min' );
$offer_text       = $m( 'lp_offer_text' );
$form_heading     = $m( 'lp_form_heading' ) ?: 'Get Best Offer';
$form_cta         = $m( 'lp_form_cta_text' ) ?: 'Get It Now!';
$phone            = $m( 'lp_phone' );
$whatsapp         = $m( 'lp_whatsapp_number' );
$lead_source      = sanitize_title( $project_name );
$social_proof     = $m( 'lp_social_proof_count' );
$highlights       = $j( 'lp_highlights' );
$rera_number      = $m( 'lp_rera_number' );
$rera_link        = $m( 'lp_rera_link' );
$dev_about        = $m( 'lp_developer_about' );
$about_heading    = $m( 'lp_about_heading' );
$dev_established  = $m( 'lp_developer_established' );
$dev_projects     = $m( 'lp_developer_projects_count' );
$pricing_configs  = $j( 'lp_pricing_configs' );
$pricing_note     = $m( 'lp_pricing_note' );
$costing_img_id   = $m( 'lp_costing_image_id' );
$costing_img_url  = $costing_img_id ? wp_get_attachment_image_url( intval( $costing_img_id ), 'medium' ) : '';
if ( ! $costing_img_url ) {
	$costing_img_url = wp_get_attachment_image_url( 187, 'medium' ) ?: '';
}
$amenities        = $j( 'lp_amenities' );
$amenity_imgs     = $g( 'lp_amenity_image_ids' );
$loc_brief        = $m( 'lp_location_brief' );
$loc_advantages   = $j( 'lp_location_advantages' );
$latitude         = $m( 'lp_latitude' );
$longitude        = $m( 'lp_longitude' );
$google_biz_name  = $m( 'lp_google_business_name' );
$maps_embed       = $m( 'lp_google_maps_embed' );
$sv_heading       = $m( 'lp_sitevisit_heading' ) ?: 'Book Site Visit Now';
$sv_social        = $m( 'lp_sitevisit_social_proof' );
$disclaimer       = $m( 'lp_disclaimer' );
$meta_title       = $m( 'lp_meta_title' ) ?: $project_name . ' — ' . $location;
$meta_desc        = $m( 'lp_meta_description' );

$banner_desktop   = $g( 'lp_banner_desktop_ids' );
$banner_mobile    = $banner_desktop; // Use same images for mobile
$dev_logo_urls    = $g( 'lp_developer_logo_id' );
$dev_logo         = ! empty( $dev_logo_urls ) ? $dev_logo_urls[0] : '';
$masterplan_urls  = $g( 'lp_masterplan_ids' );
$floorplan_urls   = $g( 'lp_floorplan_ids' );

// Default images when client hasn't uploaded their own.
// IDs: 183=MasterPlan, 184=UnitPlan, 185=1BHK-Floor, 186=2BHK-Floor, 187=Costing.
if ( empty( $masterplan_urls ) ) {
	$def_mp = wp_get_attachment_image_url( 183, 'full' );
	if ( $def_mp ) { $masterplan_urls = array( $def_mp ); }
}
if ( empty( $floorplan_urls ) ) {
	foreach ( array( 184, 185, 186 ) as $_fid ) {
		$_fu = wp_get_attachment_image_url( $_fid, 'full' );
		if ( $_fu ) { $floorplan_urls[] = $_fu; }
	}
}

// Per-config unit plan images from pricing JSON floorplan_id field.
$unitplan_urls = array();
if ( ! empty( $pricing_configs ) ) {
	foreach ( $pricing_configs as $i => $pc ) {
		$fp_id = ! empty( $pc['floorplan_id'] ) ? intval( $pc['floorplan_id'] ) : 0;
		$unitplan_urls[ $i ] = $fp_id ? wp_get_attachment_image_url( $fp_id, 'large' ) : '';
	}
}

// Brand colors — read from DB for backward compat, default to standard theme.
$brand_text = get_post_meta( $post_id, '_tp_lp_brand_color_text', true ) ?: '#111827';
$brand_bg   = get_post_meta( $post_id, '_tp_lp_brand_color_bg', true ) ?: '#c8943e';
$brand_btn  = get_post_meta( $post_id, '_tp_lp_brand_color_button', true ) ?: '#c8943e';

// Generate darker hover shades (~15% darker).
$darken = function ( $hex, $pct = 15 ) {
	$hex = ltrim( $hex, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	$r = max( 0, intval( hexdec( substr( $hex, 0, 2 ) ) * ( 100 - $pct ) / 100 ) );
	$g = max( 0, intval( hexdec( substr( $hex, 2, 2 ) ) * ( 100 - $pct ) / 100 ) );
	$b = max( 0, intval( hexdec( substr( $hex, 4, 2 ) ) * ( 100 - $pct ) / 100 ) );
	return sprintf( '#%02x%02x%02x', $r, $g, $b );
};
$brand_bg_hover  = $darken( $brand_bg );
$brand_btn_hover = $darken( $brand_btn );

// Generate very light tint for alternating section backgrounds (~5% brand + 95% white).
$lighten = function ( $hex, $pct = 5 ) {
	$hex = ltrim( $hex, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	$r = intval( 255 - ( 255 - hexdec( substr( $hex, 0, 2 ) ) ) * $pct / 100 );
	$g = intval( 255 - ( 255 - hexdec( substr( $hex, 2, 2 ) ) ) * $pct / 100 );
	$b = intval( 255 - ( 255 - hexdec( substr( $hex, 4, 2 ) ) ) * $pct / 100 );
	return sprintf( '#%02x%02x%02x', $r, $g, $b );
};
$brand_bg_alt = $lighten( $brand_bg );

$nonce     = wp_create_nonce( 'tp_sidebar_lead_nonce' );
$ajax_url  = admin_url( 'admin-ajax.php' );
$theme_url = get_template_directory_uri();
$site_name = get_bloginfo( 'name' );

// Split offer text into individual badges.
$offer_badges = array();
if ( $offer_text ) {
	$offer_badges = array_map( 'trim', explode( '|', $offer_text ) );
	$offer_badges = array_filter( $offer_badges );
}

// Brand logo fallback.
$brand_logo_id  = get_option( 'tp_brand_logo_id' );
$brand_logo_url = $brand_logo_id ? wp_get_attachment_image_url( $brand_logo_id, 'medium' ) : '';
$header_logo    = $dev_logo ?: $brand_logo_url;

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ( $header_logo ) : ?>
<link rel="icon" type="image/png" href="<?php echo esc_url( $header_logo ); ?>">
<link rel="apple-touch-icon" href="<?php echo esc_url( $header_logo ); ?>">
<?php endif; ?>
<?php
add_filter( 'pre_get_document_title', function () use ( $meta_title ) {
	return $meta_title;
} );
// Prevent WP duplicate canonical/robots since we output our own.
remove_action( 'wp_head', 'rel_canonical' );
remove_action( 'wp_head', 'wp_robots', 1 );
?>
<?php if ( $meta_desc ) : ?>
<meta name="description" content="<?php echo esc_attr( $meta_desc ); ?>">
<?php endif; ?>
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

<?php
// ── SEO: Canonical URL ──
$canonical = home_url( '/' . $post->post_name . '/' );
$og_image  = ! empty( $banner_desktop[0] ) ? $banner_desktop[0] : ( $dev_logo ?: '' );
?>
<link rel="canonical" href="<?php echo esc_url( $canonical ); ?>">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo esc_url( $canonical ); ?>">
<meta property="og:title" content="<?php echo esc_attr( $meta_title ); ?>">
<?php if ( $meta_desc ) : ?>
<meta property="og:description" content="<?php echo esc_attr( $meta_desc ); ?>">
<?php endif; ?>
<?php if ( $og_image ) : ?>
<meta property="og:image" content="<?php echo esc_url( $og_image ); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif; ?>
<meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>">
<meta property="og:locale" content="en_IN">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr( $meta_title ); ?>">
<?php if ( $meta_desc ) : ?>
<meta name="twitter:description" content="<?php echo esc_attr( $meta_desc ); ?>">
<?php endif; ?>
<?php if ( $og_image ) : ?>
<meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>">
<?php endif; ?>

<!-- GEO / Local SEO -->
<?php if ( $location ) : ?>
<meta name="geo.placename" content="<?php echo esc_attr( $location ); ?>">
<meta name="geo.region" content="IN-MH">
<?php endif; ?>
<?php if ( $latitude && $longitude ) : ?>
<meta name="geo.position" content="<?php echo esc_attr( $latitude ); ?>;<?php echo esc_attr( $longitude ); ?>">
<meta name="ICBM" content="<?php echo esc_attr( $latitude ); ?>, <?php echo esc_attr( $longitude ); ?>">
<?php endif; ?>

<?php
// ── JSON-LD: RealEstateListing + ApartmentComplex + FAQ + BreadcrumbList ──
$schema_data = array();

// 1. RealEstateListing Schema (primary — for Google & AI search)
$listing = array(
	'@type'       => 'RealEstateListing',
	'name'        => $project_name,
	'description' => $meta_desc ?: $project_name . ' by ' . $developer_name . ' in ' . $location,
	'url'         => $canonical,
);
if ( $og_image )        $listing['image'] = $og_image;
if ( $location )        $listing['address'] = array(
	'@type'           => 'PostalAddress',
	'addressLocality' => $location,
	'addressRegion'   => 'Maharashtra',
	'addressCountry'  => 'IN',
);
if ( $latitude && $longitude ) $listing['geo'] = array(
	'@type'     => 'GeoCoordinates',
	'latitude'  => floatval( $latitude ),
	'longitude' => floatval( $longitude ),
);
if ( $price_min ) {
	$price_num = preg_replace( '/[^0-9.]/', '', str_replace( array( 'Lacs', 'Lac', 'L', 'Cr', 'Crore' ), '', $price_min ) );
	if ( stripos( $price_min, 'Cr' ) !== false ) $price_num = floatval( $price_num ) * 10000000;
	elseif ( stripos( $price_min, 'L' ) !== false ) $price_num = floatval( $price_num ) * 100000;
	if ( $price_num > 0 ) {
		$listing['offers'] = array(
			'@type'         => 'Offer',
			'price'         => $price_num,
			'priceCurrency' => 'INR',
			'availability'  => 'https://schema.org/InStock',
		);
	}
}
$schema_data[] = $listing;

// 2. ApartmentComplex Schema (for AI understanding of project)
$apartment = array(
	'@type' => 'ApartmentComplex',
	'name'  => $project_name,
	'url'   => $canonical,
);
if ( $developer_name ) $apartment['developer'] = array( '@type' => 'Organization', 'name' => $developer_name );
if ( $location )       $apartment['address'] = $listing['address'] ?? null;
if ( $latitude && $longitude ) $apartment['geo'] = $listing['geo'] ?? null;
if ( $og_image )       $apartment['image'] = $og_image;
if ( $rera_number )    $apartment['identifier'] = array( '@type' => 'PropertyValue', 'name' => 'RERA', 'value' => $rera_number );
if ( ! empty( $amenities ) ) {
	$am_list = array();
	foreach ( $amenities as $am ) {
		if ( ! empty( $am['name'] ) ) $am_list[] = $am['name'];
	}
	if ( $am_list ) $apartment['amenityFeature'] = array_map( function( $a ) {
		return array( '@type' => 'LocationFeatureSpecification', 'name' => $a, 'value' => true );
	}, $am_list );
}
$schema_data[] = $apartment;

// 3. FAQ Schema (AI-friendly — auto-generated from project data)
$faqs = array();
if ( $price_min ) {
	$faqs[] = array(
		'@type'          => 'Question',
		'name'           => 'What is the starting price of ' . $project_name . '?',
		'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $project_name . ' starts from ' . $price_min . '.' ),
	);
}
if ( $location ) {
	$faqs[] = array(
		'@type'          => 'Question',
		'name'           => 'Where is ' . $project_name . ' located?',
		'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $project_name . ' is located in ' . $location . ', Maharashtra, India.' ),
	);
}
if ( $configs ) {
	$faqs[] = array(
		'@type'          => 'Question',
		'name'           => 'What configurations are available in ' . $project_name . '?',
		'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $project_name . ' offers ' . $configs . ' apartments.' ),
	);
}
if ( $rera_number ) {
	$faqs[] = array(
		'@type'          => 'Question',
		'name'           => 'What is the RERA number of ' . $project_name . '?',
		'acceptedAnswer' => array( '@type' => 'Answer', 'text' => 'The RERA registration number for ' . $project_name . ' is ' . $rera_number . '.' ),
	);
}
if ( $possession ) {
	$faqs[] = array(
		'@type'          => 'Question',
		'name'           => 'What is the possession date of ' . $project_name . '?',
		'acceptedAnswer' => array( '@type' => 'Answer', 'text' => 'The expected possession for ' . $project_name . ' is ' . $possession . '.' ),
	);
}
if ( $developer_name ) {
	$faqs[] = array(
		'@type'          => 'Question',
		'name'           => 'Who is the developer of ' . $project_name . '?',
		'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $project_name . ' is developed by ' . $developer_name . '.' ),
	);
}
if ( ! empty( $pricing_configs ) ) {
	$cfg_details = array();
	foreach ( $pricing_configs as $pc ) {
		$detail = $pc['config'] ?? '';
		if ( ! empty( $pc['area'] ) )  $detail .= ' (' . $pc['area'] . ')';
		if ( ! empty( $pc['price'] ) ) $detail .= ' — ' . $pc['price'];
		if ( $detail ) $cfg_details[] = $detail;
	}
	if ( $cfg_details ) {
		$faqs[] = array(
			'@type'          => 'Question',
			'name'           => 'What is the price list of ' . $project_name . '?',
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => implode( '. ', $cfg_details ) . '.' ),
		);
	}
}
if ( $faqs ) {
	$schema_data[] = array(
		'@type'      => 'FAQPage',
		'mainEntity' => $faqs,
	);
}

// 4. BreadcrumbList
$schema_data[] = array(
	'@type'           => 'BreadcrumbList',
	'itemListElement' => array(
		array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) ),
		array( '@type' => 'ListItem', 'position' => 2, 'name' => $project_name, 'item' => $canonical ),
	),
);

// Wrap in @graph
$schema_full = array(
	'@context' => 'https://schema.org',
	'@graph'   => $schema_data,
);
?>
<script type="application/ld+json"><?php echo wp_json_encode( $schema_full, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ── Brand Colors (CSS Custom Properties) ── */
:root {
  --lp-text: <?php echo esc_attr( $brand_text ); ?>;
  --lp-bg: <?php echo esc_attr( $brand_bg ); ?>;
  --lp-bg-hover: <?php echo esc_attr( $brand_bg_hover ); ?>;
  --lp-btn: <?php echo esc_attr( $brand_btn ); ?>;
  --lp-btn-hover: <?php echo esc_attr( $brand_btn_hover ); ?>;
  --lp-bg-alt: <?php echo esc_attr( $brand_bg_alt ); ?>;
  --lp-shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
  --lp-shadow-md: 0 4px 20px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.06);
  --lp-shadow-lg: 0 12px 40px rgba(0,0,0,0.10), 0 4px 12px rgba(0,0,0,0.05);
  --lp-shadow-xl: 0 20px 60px rgba(0,0,0,0.12), 0 8px 20px rgba(0,0,0,0.06);
  --lp-radius: 16px;
  --lp-ease: cubic-bezier(0.4, 0, 0.2, 1);
}

/* ── Animations ── */
@keyframes lpMoveGradient {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
@keyframes lpPulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.04); }
}
@keyframes lpShimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}
@keyframes lpBorderGlow {
  0%, 100% { border-color: rgba(255,255,255,0.25); box-shadow: 0 0 8px color-mix(in srgb, var(--lp-bg) 15%, transparent); }
  50% { border-color: rgba(255,255,255,0.6); box-shadow: 0 0 20px color-mix(in srgb, var(--lp-bg) 30%, transparent); }
}
@keyframes lpDash {
  to { stroke-dashoffset: -20; }
}

/* ── Reset & Base ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { font-family: 'Inter', -apple-system, sans-serif; color: #1f2937; background: #fff; line-height: 1.6; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
img { max-width: 100%; height: auto; display: block; }
a { color: var(--lp-bg); text-decoration: none; }
.lp-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

/* ── Scroll Reveal ── */
.lp-reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.7s var(--lp-ease), transform 0.7s var(--lp-ease); }
.lp-reveal.revealed { opacity: 1; transform: translateY(0); }

/* ── Header ── */
.lp-header { background: rgba(255,255,255,0.92); backdrop-filter: blur(16px) saturate(180%); -webkit-backdrop-filter: blur(16px) saturate(180%); border-bottom: 1px solid rgba(229,231,235,0.6); position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 12px rgba(0,0,0,0.04); transition: box-shadow 0.3s var(--lp-ease); }
.lp-header.scrolled { box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
.lp-header__inner { display: flex; align-items: center; justify-content: space-between; padding: 0 40px; max-width: 1600px; margin: 0 auto; height: 80px; }
.lp-header__logo img { height: 48px; transition: transform 0.2s var(--lp-ease); }
.lp-header__logo img:hover { transform: scale(1.04); }
.lp-header__logo span { font-size: 22px; font-weight: 700; color: var(--lp-bg); }
.lp-header__nav { display: flex; gap: 32px; }
.lp-header__nav a { font-size: 13.5px; font-weight: 600; color: #4b5563; transition: color 0.2s var(--lp-ease); letter-spacing: 0.2px; position: relative; }
.lp-header__nav a::after { content: ''; position: absolute; bottom: -4px; left: 0; width: 0; height: 2px; background: var(--lp-bg); border-radius: 1px; transition: width 0.25s var(--lp-ease); }
.lp-header__nav a:hover { color: var(--lp-bg); }
.lp-header__nav a:hover::after { width: 100%; }
.lp-header__phone { display: inline-flex; align-items: center; gap: 8px; background: var(--lp-bg); color: #fff; padding: 11px 22px; border-radius: 12px; font-size: 13.5px; font-weight: 600; white-space: nowrap; transition: all 0.25s var(--lp-ease); box-shadow: 0 2px 8px color-mix(in srgb, var(--lp-bg) 30%, transparent); }
.lp-header__phone:hover { background: var(--lp-bg-hover); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 16px color-mix(in srgb, var(--lp-bg) 40%, transparent); }
.lp-header__phone svg { flex-shrink: 0; }
.lp-header__hamburger { display: none; background: none; border: none; cursor: pointer; padding: 8px; }

/* ── Hero 3-Column ── */
.lp-hero-section { background: linear-gradient(180deg, #f8f5f0 0%, #f3efe8 100%); padding: 44px 0 52px; position: relative; }
.lp-hero-section::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px; background: linear-gradient(90deg, transparent, rgba(200,148,62,0.2), transparent); }
.lp-hero__grid { max-width: 1500px; margin: 0 auto; padding: 0 32px; display: grid; grid-template-columns: 31% 31% 31%; gap: 24px; justify-content: center; align-items: start; }

/* Hero Cards */
.lp-card { background: #fff; border-radius: var(--lp-radius); padding: 30px 26px; box-shadow: var(--lp-shadow-md); border: 1px solid rgba(0,0,0,0.04); transition: transform 0.35s var(--lp-ease), box-shadow 0.35s var(--lp-ease); }
.lp-card:hover { transform: translateY(-4px); box-shadow: var(--lp-shadow-lg); }
.lp-card__badge { border: 2px dashed color-mix(in srgb, var(--lp-bg) 30%, #e5e7eb); border-radius: 10px; padding: 7px 18px; text-align: center; margin-bottom: 22px; position: relative; overflow: hidden; }
.lp-card__badge::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, color-mix(in srgb, var(--lp-bg) 4%, transparent), transparent); }
.lp-card__badge-text { font-size: 11px; font-weight: 700; letter-spacing: 2px; color: var(--lp-bg); text-transform: uppercase; position: relative; }
.lp-card__name { font-size: 24px; font-weight: 800; color: var(--lp-text); text-align: center; margin-bottom: 8px; line-height: 1.2; letter-spacing: -0.3px; }
.lp-card__developer { font-size: 11px; font-weight: 700; color: #6b7280; text-align: center; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px; }
.lp-card__location { font-size: 11px; font-weight: 600; color: #9ca3af; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 22px; display: flex; align-items: center; justify-content: center; gap: 4px; }
.lp-card__location::before { content: ''; display: inline-block; width: 4px; height: 4px; background: var(--lp-btn); border-radius: 50%; }
.lp-card__facts { display: flex; justify-content: center; gap: 20px; margin-bottom: 22px; flex-wrap: wrap; }
.lp-card__fact { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: #374151; }
.lp-card__fact-icon { width: 22px; height: 22px; background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lp-card__fact-icon svg { width: 12px; height: 12px; color: #059669; }
.lp-card__offer-box { background: linear-gradient(135deg, var(--lp-bg), color-mix(in srgb, var(--lp-bg) 75%, #000), var(--lp-bg)); background-size: 200% 200%; animation: lpMoveGradient 4s ease infinite; border-radius: 14px; padding: 18px; margin-bottom: 22px; box-shadow: 0 4px 20px color-mix(in srgb, var(--lp-bg) 30%, transparent); position: relative; overflow: hidden; }
.lp-card__offer-box::after { content: ''; position: absolute; top: 0; left: -100%; width: 60%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent); animation: lpShimmer 3s ease-in-out infinite; animation-delay: 1s; }
.lp-card__offer-inner { border: 1.5px dashed rgba(255,255,255,0.3); border-radius: 10px; padding: 14px; animation: lpBorderGlow 2.5s ease-in-out infinite; position: relative; }
.lp-card__offer-item { font-size: 12px; font-weight: 600; color: #fff; padding: 5px 0; letter-spacing: 0.3px; position: relative; display: flex; align-items: center; gap: 8px; }
.lp-card__offer-item::before { content: ''; width: 5px; height: 5px; background: var(--lp-btn); border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 6px var(--lp-btn); }
.lp-card__price-label { font-size: 12px; color: #9ca3af; text-align: center; margin-bottom: 4px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
.lp-card__price { font-size: 34px; font-weight: 800; color: var(--lp-bg); text-align: center; margin-bottom: 20px; line-height: 1; letter-spacing: -1px; text-shadow: 0 0 20px color-mix(in srgb, var(--lp-bg) 15%, transparent); }
.lp-card__brochure { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: linear-gradient(90deg, var(--lp-bg), color-mix(in srgb, var(--lp-bg) 70%, #000), var(--lp-bg)); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite; color: #fff; padding: 14px; border-radius: 12px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; transition: all 0.25s var(--lp-ease); box-shadow: 0 3px 12px color-mix(in srgb, var(--lp-bg) 25%, transparent); letter-spacing: 0.3px; }
.lp-card__brochure:hover { transform: translateY(-2px); box-shadow: 0 8px 24px color-mix(in srgb, var(--lp-bg) 40%, transparent); }

/* Hero Gallery */
.lp-gallery { border-radius: var(--lp-radius); overflow: hidden; position: relative; min-height: 500px; box-shadow: var(--lp-shadow-lg); }
.lp-gallery__track { display: flex; transition: transform 0.5s var(--lp-ease); height: 100%; }
.lp-gallery__slide { min-width: 100%; }
.lp-gallery__slide img { width: 100%; height: 100%; object-fit: cover; }
.lp-gallery__dots { position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; background: rgba(0,0,0,0.3); backdrop-filter: blur(8px); padding: 6px 10px; border-radius: 20px; }
.lp-gallery__dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.45); border: none; cursor: pointer; transition: all 0.2s var(--lp-ease); }
.lp-gallery__dot.active { background: #fff; transform: scale(1.2); }
.lp-gallery__placeholder { height: 100%; min-height: 500px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #e5e7eb, #d1d5db); color: #9ca3af; font-size: 18px; border-radius: var(--lp-radius); }

/* Hero Right Card – Highlights */
.lp-card__hl-list { list-style: none; padding: 0; margin: 0 0 18px; }
.lp-card__hl-item { font-size: 13px; color: #374151; padding: 8px 0; display: flex; align-items: flex-start; gap: 10px; border-bottom: 1px solid #f3f4f6; line-height: 1.5; }
.lp-card__hl-item:last-child { border-bottom: none; }
.lp-card__hl-bullet { color: var(--lp-bg); font-weight: 700; flex-shrink: 0; font-size: 16px; line-height: 1.2; }
.lp-card__rera { display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-radius: 10px; font-size: 12px; font-weight: 600; color: #065f46; margin-bottom: 18px; }
.lp-card__rera svg { flex-shrink: 0; color: #059669; }
.lp-card__deal-cta { display: block; width: 100%; background: linear-gradient(90deg, var(--lp-bg), color-mix(in srgb, var(--lp-bg) 65%, #000), var(--lp-bg)); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite, lpPulse 2.5s ease-in-out infinite; color: #fff; padding: 14px; border-radius: 12px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; text-align: center; transition: box-shadow 0.25s var(--lp-ease); box-shadow: 0 3px 12px color-mix(in srgb, var(--lp-bg) 25%, transparent); letter-spacing: 0.3px; }
.lp-card__deal-cta:hover { box-shadow: 0 8px 28px color-mix(in srgb, var(--lp-bg) 45%, transparent); }

/* ── Section Divider (hidden — alternating backgrounds handle separation) ── */
.lp-divider { display: none; }

/* ── Section Common ── */
.lp-section { padding: 48px 0; background: #fff; }
.lp-section--alt { background: #fff; }
.lp-section--dark { background: #111827; color: #fff; }
.lp-section__title { font-size: 22px; font-weight: 700; margin-bottom: 24px; text-align: center; }
.lp-section__subtitle { font-size: 14px; color: #6b7280; text-align: center; margin-bottom: 28px; }
.lp-section--dark .lp-section__subtitle { color: rgba(255,255,255,0.7); }

.lp-form-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 18px; padding: 28px 24px; box-shadow: var(--lp-shadow-xl); }
.lp-form-card__title { font-size: 18px; font-weight: 700; text-align: center; margin-bottom: 16px; }
.lp-input { width: 100%; padding: 13px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 14px; font-family: inherit; margin-bottom: 12px; outline: none; transition: all 0.2s var(--lp-ease); background: #fafafa; }
.lp-input:focus { border-color: var(--lp-bg); background: #fff; box-shadow: 0 0 0 3px color-mix(in srgb, var(--lp-bg) 12%, transparent); }
.lp-btn { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--lp-bg), color-mix(in srgb, var(--lp-bg) 80%, #000)); color: #fff; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all 0.25s var(--lp-ease); box-shadow: 0 3px 12px color-mix(in srgb, var(--lp-bg) 25%, transparent); letter-spacing: 0.3px; }
.lp-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px color-mix(in srgb, var(--lp-bg) 35%, transparent); }
.lp-btn:disabled { opacity: 0.6; cursor: default; transform: none; box-shadow: none; }
.lp-form-card__msg { text-align: center; padding: 14px; background: #ecfdf5; color: #065f46; border-radius: 10px; font-size: 14px; font-weight: 600; display: none; margin-top: 12px; }

/* ── Welcome / About Section ── */
.lp-about { background: var(--lp-bg-alt); padding: 64px 0; }
.lp-about__inner { max-width: 1200px; margin: 0 auto; padding: 0 32px; }
.lp-about__title { font-size: 30px; font-weight: 800; color: var(--lp-text); margin-bottom: 20px; line-height: 1.25; letter-spacing: -0.5px; position: relative; padding-bottom: 16px; }
.lp-about__title::after { content: ''; position: absolute; bottom: 0; left: 0; width: 48px; height: 3px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 40%, transparent)); border-radius: 2px; }
.lp-about__text { font-size: 15px; color: #4b5563; line-height: 1.9; margin-bottom: 32px; max-width: 900px; }
.lp-about__review { display: inline-flex; align-items: center; gap: 14px; margin-bottom: 32px; padding: 14px 20px; background: #fff; border-radius: 14px; border: 1px solid #e5e7eb; box-shadow: var(--lp-shadow-sm); transition: all 0.25s var(--lp-ease); }
.lp-about__review:hover { box-shadow: var(--lp-shadow-md); transform: translateY(-2px); }
.lp-about__review-g { width: 28px; height: 28px; flex-shrink: 0; }
.lp-about__review-info { display: flex; flex-direction: column; }
.lp-about__review-label { font-size: 13px; font-weight: 700; color: var(--lp-text); }
.lp-about__review-stars { display: flex; gap: 2px; margin-top: 2px; }
.lp-about__review-stars svg { width: 16px; height: 16px; }
.lp-about__brochure { display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 65%, #000), var(--lp-btn)); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite; color: #fff; padding: 14px 36px; border-radius: 12px; font-size: 15px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; transition: all 0.25s var(--lp-ease); box-shadow: 0 3px 12px color-mix(in srgb, var(--lp-btn) 25%, transparent); letter-spacing: 0.3px; position: relative; overflow: hidden; }
.lp-about__brochure::after { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent); animation: lpShimmer 2.5s ease-in-out infinite; }
.lp-about__brochure:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 8px 28px color-mix(in srgb, var(--lp-btn) 40%, transparent); }
.lp-about__brochure svg { flex-shrink: 0; }
.lp-about__social { font-size: 14px; color: var(--lp-bg); font-weight: 600; margin-top: 14px; }

/* ── Pricing ── */
.lp-pricing { background: #fff; padding: 64px 0; }
.lp-pricing__inner { max-width: 1300px; margin: 0 auto; padding: 0 32px; }
.lp-pricing__title { font-size: 28px; font-weight: 800; color: var(--lp-text); margin-bottom: 36px; line-height: 1.3; letter-spacing: -0.5px; position: relative; padding-bottom: 16px; }
.lp-pricing__title::after { content: ''; position: absolute; bottom: 0; left: 0; width: 48px; height: 3px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 40%, transparent)); border-radius: 2px; }
.lp-pricing-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.lp-pricing-card { background: #fff; border: 1px solid rgba(0,0,0,0.06); border-radius: var(--lp-radius); overflow: hidden; text-align: center; display: flex; flex-direction: column; box-shadow: var(--lp-shadow-sm); transition: all 0.35s var(--lp-ease); }
.lp-pricing-card:hover { transform: translateY(-6px); box-shadow: var(--lp-shadow-lg); border-color: transparent; }
.lp-pricing-card__img { width: 100%; aspect-ratio: 4/3; object-fit: cover; border-bottom: 1px solid #f3f4f6; background: #f9fafb; transition: transform 0.5s var(--lp-ease); }
.lp-pricing-card:hover .lp-pricing-card__img { transform: scale(1.04); }
.lp-pricing-card__img-placeholder { width: 100%; aspect-ratio: 4/3; background: linear-gradient(135deg, #f3f4f6, #e5e7eb); display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #eee; }
.lp-pricing-card__img-placeholder svg { width: 48px; height: 48px; color: #d1d5db; }
.lp-pricing-card__body { padding: 20px 16px 22px; flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; }
.lp-pricing-card__config { font-size: 15px; font-weight: 700; color: var(--lp-text); line-height: 1.3; }
.lp-pricing-card__area { font-size: 13px; color: #6b7280; }
.lp-pricing-card__price { font-size: 24px; font-weight: 800; color: var(--lp-bg); margin: 8px 0 12px; letter-spacing: -0.5px; text-shadow: 0 0 16px color-mix(in srgb, var(--lp-bg) 12%, transparent); }
.lp-pricing-card__breakup { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 65%, #000), var(--lp-btn)); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite; color: #fff; padding: 10px 24px; border-radius: 10px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; transition: all 0.25s var(--lp-ease); box-shadow: 0 2px 8px color-mix(in srgb, var(--lp-btn) 20%, transparent); letter-spacing: 0.2px; }
.lp-pricing-card__breakup:hover { transform: translateY(-2px) scale(1.03); box-shadow: 0 6px 20px color-mix(in srgb, var(--lp-btn) 35%, transparent); }
/* Costing card */
.lp-pricing-card--costing { justify-content: center; align-items: center; padding: 32px 20px; text-align: center; background: linear-gradient(135deg, #fefce8 0%, #fff 60%); }
.lp-pricing-card--costing .lp-pricing-card__body { justify-content: center; }
.lp-pricing-card--costing-icon { width: 56px; height: 56px; color: var(--lp-btn); margin-bottom: 8px; }
.lp-pricing-card--costing-label { font-size: 16px; font-weight: 700; color: var(--lp-text); margin-bottom: 4px; }
.lp-pricing-card--costing-sub { font-size: 13px; color: #6b7280; margin-bottom: 14px; }
.lp-pricing-note { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 20px; }

/* ── Plans ── */
.lp-plans-section { background: var(--lp-bg-alt); padding: 64px 0; }
.lp-plans-section__inner { max-width: 1300px; margin: 0 auto; padding: 0 32px; }
.lp-plans-section__title { font-size: 28px; font-weight: 800; color: var(--lp-text); margin-bottom: 36px; line-height: 1.3; letter-spacing: -0.5px; position: relative; padding-bottom: 16px; }
.lp-plans-section__title::after { content: ''; position: absolute; bottom: 0; left: 0; width: 48px; height: 3px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 40%, transparent)); border-radius: 2px; }
.lp-plans { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
.lp-plan-card { text-align: center; }
.lp-plan-card__label { font-size: 17px; font-weight: 700; color: var(--lp-text); margin-bottom: 16px; }
.lp-plan-card__img { position: relative; background: #fff; border-radius: var(--lp-radius); overflow: hidden; aspect-ratio: 16/11; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: var(--lp-shadow-md); transition: box-shadow 0.3s var(--lp-ease); }
.lp-plan-card__img:hover { box-shadow: var(--lp-shadow-lg); }
.lp-plan-card__img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s var(--lp-ease); }
.lp-plan-card__img:hover img { transform: scale(1.05); }
.lp-plan-card__img--placeholder { background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); }
.lp-plan-card__img--placeholder svg { width: 64px; height: 64px; color: #d1d5db; }
.lp-plan-card__zoom { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.3); opacity: 0; transition: opacity 0.3s var(--lp-ease); }
.lp-plan-card__img:hover .lp-plan-card__zoom { opacity: 1; }
.lp-plan-card__zoom svg { width: 48px; height: 48px; color: #fff; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.3)); }
.lp-plan-card__cta { padding-top: 18px; }
.lp-plan-card__btn { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 65%, #000), var(--lp-btn)); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite; color: #fff; padding: 12px 32px; border-radius: 10px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; transition: all 0.25s var(--lp-ease); box-shadow: 0 2px 8px color-mix(in srgb, var(--lp-btn) 20%, transparent); position: relative; overflow: hidden; }
.lp-plan-card__btn::after { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent); animation: lpShimmer 2.5s ease-in-out infinite; }
.lp-plan-card__btn:hover { transform: translateY(-2px) scale(1.03); box-shadow: 0 6px 20px color-mix(in srgb, var(--lp-btn) 35%, transparent); }

/* ── Amenities ── */
.lp-amenities { background: #fff; padding: 64px 0; }
.lp-amenities__inner { max-width: 1300px; margin: 0 auto; padding: 0 32px; }
.lp-amenities__title { font-size: 28px; font-weight: 800; color: var(--lp-text); margin-bottom: 36px; line-height: 1.3; letter-spacing: -0.5px; position: relative; padding-bottom: 16px; }
.lp-amenities__title::after { content: ''; position: absolute; bottom: 0; left: 0; width: 48px; height: 3px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 40%, transparent)); border-radius: 2px; }
.lp-amenities-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 36px; }
.lp-amenity { background: #fff; border-radius: var(--lp-radius); overflow: hidden; text-align: center; padding: 0; box-shadow: var(--lp-shadow-sm); border: 1px solid rgba(0,0,0,0.04); transition: all 0.35s var(--lp-ease); }
.lp-amenity:hover { transform: translateY(-4px); box-shadow: var(--lp-shadow-lg); }
.lp-amenity__img { width: 100%; aspect-ratio: 16/10; background: linear-gradient(135deg, #f0ebe3 0%, #e5dfd6 100%); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.lp-amenity__img img { transition: transform 0.5s var(--lp-ease); }
.lp-amenity:hover .lp-amenity__img img { transform: scale(1.08); }
.lp-amenity__img svg { width: 48px; height: 48px; color: #a3917c; transition: transform 0.3s var(--lp-ease); }
.lp-amenity:hover .lp-amenity__img svg { transform: scale(1.1); }
.lp-amenity__label { padding: 14px 12px; font-size: 13.5px; font-weight: 600; color: var(--lp-text); }
.lp-amenities__cta { text-align: center; }
.lp-amenities__btn { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 65%, #000), var(--lp-btn)); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite; color: #fff; padding: 14px 36px; border-radius: 12px; font-size: 15px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; transition: all 0.25s var(--lp-ease); box-shadow: 0 3px 12px color-mix(in srgb, var(--lp-btn) 25%, transparent); letter-spacing: 0.3px; position: relative; overflow: hidden; }
.lp-amenities__btn::after { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent); animation: lpShimmer 2.5s ease-in-out infinite; }
.lp-amenities__btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 8px 28px color-mix(in srgb, var(--lp-btn) 40%, transparent); }
/* Amenity mobile carousel (hidden on desktop) */
.lp-amenities-carousel { display: none; }
.lp-amenities-carousel__track { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; gap: 0; scrollbar-width: none; border-radius: var(--lp-radius); box-shadow: var(--lp-shadow-md); }
.lp-amenities-carousel__track::-webkit-scrollbar { display: none; }
.lp-amenities-carousel__slide { flex: 0 0 100%; scroll-snap-align: center; }
.lp-amenities-carousel__slide .lp-amenity { border-radius: 0; box-shadow: none; border: none; }
.lp-amenities-carousel__slide .lp-amenity__img { aspect-ratio: 16/9; }
.lp-amenities-carousel__dots { display: flex; justify-content: center; gap: 6px; margin-top: 16px; }
.lp-amenities-carousel__dot { width: 8px; height: 8px; border-radius: 50%; background: #d1d5db; border: none; padding: 0; cursor: pointer; transition: all 0.25s var(--lp-ease); }
.lp-amenities-carousel__dot.active { background: var(--lp-btn); transform: scale(1.3); }

/* ── Location ── */
.lp-location { background: var(--lp-bg-alt); padding: 64px 0; }
.lp-location__inner { max-width: 1300px; margin: 0 auto; padding: 0 32px; }
.lp-location__title { font-size: 28px; font-weight: 800; color: var(--lp-text); margin-bottom: 16px; line-height: 1.3; letter-spacing: -0.5px; position: relative; padding-bottom: 16px; }
.lp-location__title::after { content: ''; position: absolute; bottom: 0; left: 0; width: 48px; height: 3px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 40%, transparent)); border-radius: 2px; }
.lp-location__desc { font-size: 15px; color: #4b5563; line-height: 1.8; margin-bottom: 32px; max-width: 900px; }
.lp-location__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; align-items: start; }
.lp-location__map { border-radius: var(--lp-radius); overflow: hidden; border: 1px solid rgba(0,0,0,0.06); background: #f3f4f6; box-shadow: var(--lp-shadow-md); }
.lp-location__map iframe { width: 100%; height: 400px; border: 0; display: block; }
.lp-location__map-placeholder { width: 100%; height: 400px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); }
.lp-location__map-placeholder svg { width: 64px; height: 64px; color: #d1d5db; }
.lp-location__card { background: #fff; border: 1px solid rgba(0,0,0,0.06); border-radius: var(--lp-radius); padding: 28px; box-shadow: var(--lp-shadow-md); }
.lp-location__card-title { font-size: 17px; font-weight: 700; color: var(--lp-text); margin-bottom: 4px; }
.lp-location__card-sub { font-size: 13px; color: #6b7280; margin-bottom: 20px; }
.lp-location__list { list-style: none; padding: 0; margin: 0 0 24px; }
.lp-location__list li { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; font-size: 13px; border-bottom: 1px solid #f3f4f6; transition: background 0.15s; }
.lp-location__list li:hover { background: #fafafa; margin: 0 -8px; padding-left: 8px; padding-right: 8px; border-radius: 6px; }
.lp-location__list li:last-child { border-bottom: none; }
.lp-location__list-place { font-weight: 500; color: var(--lp-text); }
.lp-location__list-dist { color: var(--lp-btn); font-weight: 700; font-size: 12px; background: color-mix(in srgb, var(--lp-btn) 10%, transparent); padding: 3px 10px; border-radius: 20px; }
.lp-location__brochure { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 65%, #000), var(--lp-btn)); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite; color: #fff; padding: 12px 32px; border-radius: 10px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; transition: all 0.25s var(--lp-ease); box-shadow: 0 2px 8px color-mix(in srgb, var(--lp-btn) 20%, transparent); position: relative; overflow: hidden; }
.lp-location__brochure::after { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent); animation: lpShimmer 2.5s ease-in-out infinite; }
.lp-location__brochure:hover { transform: translateY(-2px) scale(1.03); box-shadow: 0 6px 20px color-mix(in srgb, var(--lp-btn) 35%, transparent); }
.lp-location__social { font-size: 13px; color: var(--lp-bg); font-weight: 600; margin-top: 12px; }

/* ── Virtual Tour ── */
.lp-tour { background: #fff; padding: 64px 0; }
.lp-tour__inner { max-width: 1300px; margin: 0 auto; padding: 0 32px; }
.lp-tour__title { font-size: 28px; font-weight: 800; color: var(--lp-text); margin-bottom: 36px; line-height: 1.3; letter-spacing: -0.5px; position: relative; padding-bottom: 16px; }
.lp-tour__title::after { content: ''; position: absolute; bottom: 0; left: 0; width: 48px; height: 3px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 40%, transparent)); border-radius: 2px; }
.lp-tour-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
.lp-tour-card { cursor: pointer; text-align: center; }
.lp-tour-card__label { font-size: 17px; font-weight: 700; color: var(--lp-text); margin-bottom: 14px; }
.lp-tour-card__media { position: relative; border-radius: var(--lp-radius); overflow: hidden; background: #000; box-shadow: var(--lp-shadow-md); transition: all 0.35s var(--lp-ease); }
.lp-tour-card:hover .lp-tour-card__media { box-shadow: var(--lp-shadow-xl); transform: translateY(-4px); }
.lp-tour-card__thumb { width: 100%; aspect-ratio: 16/9; object-fit: cover; display: block; transition: transform 0.5s var(--lp-ease), opacity 0.3s; }
.lp-tour-card:hover .lp-tour-card__thumb { transform: scale(1.05); opacity: 0.85; }
.lp-tour-card__thumb-placeholder { width: 100%; aspect-ratio: 16/9; background: linear-gradient(135deg, #1f2937 0%, #111827 100%); }
.lp-tour-card__play { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
.lp-tour-card__play svg { width: 68px; height: 48px; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.4)); transition: transform 0.3s var(--lp-ease); }
.lp-tour-card:hover .lp-tour-card__play svg { transform: scale(1.15); }

/* ── Site Visit Form ── */
.lp-sitevisit { background: linear-gradient(135deg, #111827 0%, #1a2332 50%, #0f172a 100%); padding: 56px 0; position: relative; overflow: hidden; }
.lp-sitevisit::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle at 30% 40%, color-mix(in srgb, var(--lp-btn) 6%, transparent) 0%, transparent 60%); pointer-events: none; }
.lp-sitevisit__inner { max-width: 800px; margin: 0 auto; padding: 0 32px; text-align: center; position: relative; }
.lp-sitevisit__title { font-size: 26px; font-weight: 800; color: #fff; margin-bottom: 8px; letter-spacing: -0.3px; }
.lp-sitevisit__social { font-size: 14px; color: rgba(255,255,255,0.5); margin-bottom: 28px; }
.lp-sitevisit__form { display: flex; gap: 12px; max-width: 600px; margin: 0 auto; align-items: center; }
.lp-sitevisit__form .lp-input { flex: 1; background: rgba(255,255,255,0.08); border: 1.5px solid rgba(255,255,255,0.15); color: #fff; margin-bottom: 0; backdrop-filter: blur(8px); min-width: 0; }
.lp-sitevisit__form .lp-input::placeholder { color: rgba(255,255,255,0.4); }
.lp-sitevisit__form .lp-input:focus { border-color: var(--lp-btn); background: rgba(255,255,255,0.12); box-shadow: 0 0 0 3px color-mix(in srgb, var(--lp-btn) 15%, transparent); }
.lp-sitevisit__form .lp-btn { width: auto; flex-shrink: 0; padding: 14px 28px; background: linear-gradient(90deg, var(--lp-btn), color-mix(in srgb, var(--lp-btn) 60%, #000), var(--lp-btn)); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite, lpPulse 2.8s ease-in-out infinite; box-shadow: 0 4px 16px color-mix(in srgb, var(--lp-btn) 30%, transparent); }
.lp-sitevisit__form .lp-btn:hover { box-shadow: 0 8px 28px color-mix(in srgb, var(--lp-btn) 45%, transparent); }
.lp-sitevisit__msg { text-align: center; padding: 12px; background: rgba(16,185,129,0.15); color: #6ee7b7; border-radius: 10px; font-size: 14px; font-weight: 600; display: none; margin-top: 16px; border: 1px solid rgba(16,185,129,0.2); }

/* ── Footer ── */
.lp-footer { background: #fff; color: #6b7280; padding: 52px 0 32px; font-size: 13px; border-top: 1px solid #e5e7eb; }
.lp-footer__inner { max-width: 1300px; margin: 0 auto; padding: 0 32px; }
.lp-footer__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid #e5e7eb; }
.lp-footer__logo img { height: 44px; display: block; transition: opacity 0.2s; }
.lp-footer__logo img:hover { opacity: 0.85; }
.lp-footer__nav { display: flex; gap: 28px; }
.lp-footer__nav a { color: #6b7280; text-decoration: none; font-size: 13px; font-weight: 500; transition: color 0.2s var(--lp-ease); }
.lp-footer__nav a:hover { color: var(--lp-text); }
.lp-footer__about { font-size: 13px; line-height: 1.8; color: #9ca3af; margin-bottom: 20px; max-width: 900px; }
.lp-footer__rera { margin-bottom: 16px; font-weight: 600; color: #4b5563; }
.lp-footer__rera a { color: var(--lp-btn); text-decoration: none; transition: opacity 0.15s; }
.lp-footer__rera a:hover { text-decoration: underline; opacity: 0.85; }
.lp-footer__disclaimer { font-size: 11px; color: #9ca3af; line-height: 1.7; margin-bottom: 20px; }
.lp-footer__copy { font-size: 12px; color: #b0b5bd; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; }

/* ── Sticky Mobile CTA ── */
.lp-sticky { display: none; position: fixed; bottom: 0; left: 0; right: 0; z-index: 99; background: rgba(255,255,255,0.95); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-top: 1px solid rgba(0,0,0,0.06); padding: 10px 16px; box-shadow: 0 -4px 24px rgba(0,0,0,0.08); }
.lp-sticky__inner { display: flex; gap: 10px; max-width: 600px; margin: 0 auto; }
.lp-sticky__btn { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 13px; border-radius: 12px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; font-family: inherit; color: #fff; transition: all 0.2s var(--lp-ease); }
.lp-sticky__btn--call { background: linear-gradient(90deg, var(--lp-bg), color-mix(in srgb, var(--lp-bg) 65%, #000), var(--lp-bg)); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite; box-shadow: 0 2px 8px color-mix(in srgb, var(--lp-bg) 25%, transparent); }
.lp-sticky__btn--wa { background: linear-gradient(90deg, #25D366, #128C7E, #25D366); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite; box-shadow: 0 2px 8px rgba(37,211,102,0.25); }
.lp-sticky__btn:active { transform: scale(0.97); }

/* ── Popup Modal ── */
.lp-modal { display: none; position: fixed; inset: 0; z-index: 10000; align-items: center; justify-content: center; }
.lp-modal.active { display: flex; }
.lp-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.55); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
.lp-modal__panel { position: relative; background: #fff; border-radius: 20px; padding: 0; max-width: 740px; width: 94%; box-shadow: 0 25px 80px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.08); text-align: left; animation: lpModalIn 0.4s var(--lp-ease); overflow: hidden; }
@keyframes lpModalIn { from { transform: scale(0.92) translateY(20px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
.lp-modal__close { position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.06); border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #9ca3af; z-index: 2; transition: all 0.2s var(--lp-ease); }
.lp-modal__close:hover { background: rgba(0,0,0,0.1); color: #374151; transform: rotate(90deg); }
.lp-modal__header { background: linear-gradient(135deg, color-mix(in srgb, var(--lp-bg) 8%, #fff) 0%, color-mix(in srgb, var(--lp-bg) 4%, #f9f9f9) 100%); padding: 20px 28px; border-bottom: 1px solid #e8e2d9; display: flex; align-items: center; gap: 16px; }
.lp-modal__logo { height: 44px; flex-shrink: 0; }
.lp-modal__header-text { flex: 1; }
.lp-modal__title { font-size: 18px; font-weight: 800; color: var(--lp-text); margin: 0 0 2px; letter-spacing: -0.3px; line-height: 1.3; }
.lp-modal__sub { font-size: 12px; color: #6b7280; margin: 0; }
.lp-modal__content { display: grid; grid-template-columns: 1fr 1fr; }
.lp-modal__promises { background: linear-gradient(160deg, color-mix(in srgb, var(--lp-bg) 6%, #fff), color-mix(in srgb, var(--lp-bg) 3%, #f8f8f8)); padding: 32px 28px; display: flex; flex-direction: column; justify-content: center; gap: 0; border-right: 1px solid #f0f0f0; }
.lp-modal__promises-title { font-size: 20px; font-weight: 800; color: var(--lp-text); margin-bottom: 24px; letter-spacing: -0.3px; }
.lp-modal__promises-title span { color: var(--lp-bg); }
.lp-modal__promise { display: flex; align-items: flex-start; gap: 14px; padding: 14px 0; border-bottom: 1px solid rgba(0,0,0,0.05); }
.lp-modal__promise:last-child { border-bottom: none; }
.lp-modal__promise-icon { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, color-mix(in srgb, var(--lp-bg) 12%, #fff), color-mix(in srgb, var(--lp-bg) 6%, #fff)); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lp-modal__promise-icon svg { width: 20px; height: 20px; color: var(--lp-bg); }
.lp-modal__promise-text h4 { font-size: 14px; font-weight: 700; color: var(--lp-text); margin: 0 0 2px; }
.lp-modal__promise-text p { font-size: 12px; color: #6b7280; margin: 0; line-height: 1.4; }
.lp-modal__body { padding: 28px 28px 24px; }
.lp-modal__form-title { font-size: 13px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; }
.lp-modal__form .lp-input { text-align: left; border-radius: 10px; padding: 13px 16px; font-size: 14px; border: 1.5px solid #e5e7eb; background: #fafafa; width: 100%; box-sizing: border-box; margin-bottom: 12px; display: block; font-family: inherit; outline: none; transition: all 0.2s var(--lp-ease); }
.lp-modal__form .lp-input:focus { background: #fff; border-color: var(--lp-bg); box-shadow: 0 0 0 3px color-mix(in srgb, var(--lp-bg) 12%, transparent); }
.lp-modal__phone-row { display: flex; gap: 0; margin-bottom: 12px; }
.lp-modal__phone-prefix { display: flex; align-items: center; gap: 6px; padding: 13px 12px; font-size: 14px; font-weight: 600; color: var(--lp-text); background: #f3f4f6; border: 1.5px solid #e5e7eb; border-right: none; border-radius: 10px 0 0 10px; white-space: nowrap; user-select: none; }
.lp-modal__phone-prefix img { width: 20px; height: 14px; border-radius: 2px; object-fit: cover; }
.lp-modal__phone-row .lp-input { border-radius: 0 10px 10px 0; margin-bottom: 0; flex: 1; }
.lp-modal__form .lp-btn { background: linear-gradient(90deg, var(--lp-bg), color-mix(in srgb, var(--lp-bg) 65%, #000), var(--lp-bg)); background-size: 200% 100%; animation: lpMoveGradient 3s ease infinite, lpPulse 2.5s ease-in-out infinite; border-radius: 10px; font-size: 15px; padding: 14px; letter-spacing: 0.3px; box-shadow: 0 3px 12px color-mix(in srgb, var(--lp-bg) 25%, transparent); width: 100%; border: none; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; display: block; }
.lp-modal__form .lp-btn:hover { box-shadow: 0 8px 28px color-mix(in srgb, var(--lp-bg) 40%, transparent); }
.lp-modal__social { font-size: 11px; color: #9ca3af; margin-top: 12px; text-align: center; }
.lp-modal__social strong { color: var(--lp-bg); font-weight: 700; }
.lp-modal__msg { text-align: center; padding: 14px; background: #ecfdf5; color: #065f46; border-radius: 10px; font-size: 14px; font-weight: 600; display: none; margin-top: 14px; }
.lp-modal__footer { background: var(--lp-bg); padding: 12px 28px; display: flex; align-items: center; justify-content: center; gap: 8px; }
.lp-modal__footer a { color: #fff; font-size: 15px; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 8px; letter-spacing: 0.3px; }
.lp-modal__footer svg { width: 18px; height: 18px; color: #fff; }

/* ── Tablet ── */
@media (max-width: 1080px) {
	.lp-hero__grid { grid-template-columns: 1fr 1fr; gap: 20px; padding: 0 24px; }
	.lp-hero__grid > .lp-card:last-child { grid-column: 1 / -1; }
	.lp-gallery { min-height: 400px; }
	.lp-pricing-grid { grid-template-columns: repeat(2, 1fr); }
}

/* ── Mobile ── */
@media (max-width: 767px) {
	.lp-header__inner { height: 60px; padding: 0 16px; }
	.lp-header__nav { display: none; position: absolute; top: 60px; left: 0; right: 0; background: #fff; flex-direction: column; gap: 0; padding: 8px 0; box-shadow: 0 8px 30px rgba(0,0,0,0.12); border-top: 1px solid #e5e7eb; z-index: 99; }
	.lp-header__nav.open { display: flex; animation: lpNavSlide 0.25s var(--lp-ease); }
	@keyframes lpNavSlide { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
	.lp-header__nav a { padding: 14px 24px; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
	.lp-header__nav a:last-child { border-bottom: none; }
	.lp-header__nav a::after { display: none; }
	.lp-header__nav a:hover, .lp-header__nav a:active { background: color-mix(in srgb, var(--lp-bg) 6%, #fff); color: var(--lp-bg); }
	.lp-header__hamburger { display: block; }
	.lp-header__phone { display: none; }
	.lp-hero-section { padding: 16px 0 24px; }
	.lp-hero__grid { grid-template-columns: 1fr; padding: 0 16px; gap: 14px; }
	.lp-hero__grid > .lp-gallery { order: -1; }
	.lp-gallery { min-height: 260px; border-radius: 14px; }
	.lp-card { padding: 22px 18px; border-radius: 14px; }
	.lp-card:hover { transform: none; }
	.lp-card__name { font-size: 21px; }
	.lp-card__price { font-size: 28px; }
	.lp-pricing-grid { grid-template-columns: 1fr 1fr; }
	.lp-pricing-card:hover { transform: none; }
	.lp-pricing-card__price { font-size: 20px; }
	.lp-amenities-grid { display: none; }
	.lp-amenities-carousel { display: block; margin-bottom: 24px; }
	.lp-amenities { padding: 40px 0; }
	.lp-amenities__inner { padding: 0 16px; }
	.lp-amenities__title { font-size: 22px; }
	.lp-location__grid { grid-template-columns: 1fr; }
	.lp-location { padding: 40px 0; }
	.lp-location__inner { padding: 0 16px; }
	.lp-location__title { font-size: 22px; }
	.lp-location__map iframe, .lp-location__map-placeholder { height: 260px; }
	.lp-plans { grid-template-columns: 1fr; }
	.lp-plans-section { padding: 40px 0; }
	.lp-plans-section__inner { padding: 0 16px; }
	.lp-plans-section__title { font-size: 22px; }
	.lp-tour-grid { grid-template-columns: 1fr; }
	.lp-tour { padding: 40px 0; }
	.lp-tour__inner { padding: 0 16px; }
	.lp-tour__title { font-size: 22px; }
	.lp-sitevisit__form { flex-direction: column; align-items: stretch; }
	.lp-sitevisit__form .lp-input { width: 100%; flex: none; }
	.lp-sitevisit__form .lp-btn { width: 100%; }
	.lp-sitevisit__inner { padding: 0 16px; }
	.lp-footer__top { flex-direction: column; gap: 16px; align-items: flex-start; }
	.lp-footer__nav { flex-wrap: wrap; gap: 16px; }
	.lp-footer__inner { padding: 0 16px; }
	.lp-sticky { display: block; }
	.lp-section { padding: 36px 0; }
	.lp-section__title { font-size: 18px; }
	body { padding-bottom: 72px; }
	.lp-pricing__title { font-size: 22px; }
	.lp-pricing { padding: 40px 0; }
	.lp-pricing__inner { padding: 0 16px; }
	.lp-about { padding: 40px 0; }
	.lp-about__title { font-size: 24px; }
	.lp-reveal { opacity: 1; transform: none; }
	.lp-modal { align-items: center; justify-content: center; padding: 16px; }
	.lp-modal__panel { max-width: 400px; width: 92%; border-radius: 16px; position: relative; bottom: auto; left: auto; right: auto; overflow-y: auto; animation: lpModalIn 0.3s var(--lp-ease); }
	.lp-modal__content { grid-template-columns: 1fr; }
	.lp-modal__promises { display: none; }
	.lp-modal__header { padding: 14px 18px; gap: 12px; }
	.lp-modal__logo { height: 34px; }
	.lp-modal__title { font-size: 15px; }
	.lp-modal__sub { font-size: 11px; }
	.lp-modal__body { padding: 16px 18px 14px; }
	.lp-modal__form-title { font-size: 12px; margin-bottom: 12px; text-align: center; }
	.lp-modal__form .lp-input { padding: 11px 14px; font-size: 13px; margin-bottom: 10px; border-radius: 8px; box-sizing: border-box; }
	.lp-modal__phone-row { display: flex; flex-wrap: nowrap; margin-bottom: 10px; width: 100%; }
	.lp-modal__phone-prefix { padding: 11px 8px; font-size: 13px; border-radius: 8px 0 0 8px; flex-shrink: 0; gap: 4px; }
	.lp-modal__phone-prefix img { width: 18px; height: 13px; }
	.lp-modal__phone-row .lp-input { border-radius: 0 8px 8px 0; min-width: 0; flex: 1; margin-bottom: 0; }
	.lp-modal__form .lp-btn { padding: 12px; font-size: 14px; border-radius: 8px; }
	.lp-modal__social { font-size: 10px; margin-top: 8px; text-align: center; }
	.lp-modal__footer { padding: 10px 18px; }
	.lp-modal__footer a { font-size: 13px; }
	.lp-modal__close { top: 10px; right: 10px; width: 28px; height: 28px; font-size: 16px; }
}
@media (max-width: 480px) {
	.lp-pricing-grid { grid-template-columns: 1fr; }
}
/* Respect reduced motion preference */
@media (prefers-reduced-motion: reduce) {
	*, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; }
}
</style>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'lp-body' ); ?>>

<!-- ═══ 1. Header ═══ -->
<header class="lp-header">
	<div class="lp-header__inner">
		<div class="lp-header__logo">
			<?php if ( $header_logo ) : ?>
				<img src="<?php echo esc_url( $header_logo ); ?>" alt="<?php echo esc_attr( $project_name ); ?>">
			<?php else : ?>
				<span><?php echo esc_html( $site_name ); ?></span>
			<?php endif; ?>
		</div>
		<nav class="lp-header__nav" id="lp-nav">
			<a href="#about">About Project</a>
			<a href="#pricing">Configuration &amp; Price</a>
			<a href="#amenities">Amenities &amp; Gallery</a>
			<a href="#location">Location Map</a>
			<a href="#virtual-tour">Virtual Site Visit</a>
		</nav>
		<div style="display:flex;align-items:center;gap:12px;">
			<?php if ( $phone ) : ?>
			<a href="tel:<?php echo esc_attr( $phone ); ?>" class="lp-header__phone">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
				<span><?php echo esc_html( $phone ); ?></span>
			</a>
			<?php endif; ?>
			<button class="lp-header__hamburger" id="lp-hamburger" aria-label="Menu">
				<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
			</button>
		</div>
	</div>
</header>

<!-- ═══ 2. Hero — 3-Column Layout ═══ -->
<section class="lp-hero-section" id="hero">
	<div class="lp-hero__grid">

		<!-- LEFT CARD — Project / Offer -->
		<div class="lp-card">
			<div class="lp-card__badge"><span class="lp-card__badge-text">Booking Open</span></div>
			<h1 class="lp-card__name"><?php echo esc_html( $project_name ); ?></h1>
			<?php if ( $developer_name ) : ?>
			<div class="lp-card__developer"><?php echo esc_html( $developer_name ); ?></div>
			<?php endif; ?>
			<?php if ( $location ) : ?>
			<div class="lp-card__location"><?php echo esc_html( $location ); ?></div>
			<?php endif; ?>

			<?php if ( $land_parcel || $total_floors ) : ?>
			<div class="lp-card__facts">
				<?php if ( $land_parcel ) : ?>
				<div class="lp-card__fact">
					<span class="lp-card__fact-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
					Land Parcel <?php echo esc_html( $land_parcel ); ?>
				</div>
				<?php endif; ?>
				<?php if ( $total_floors ) : ?>
				<div class="lp-card__fact">
					<span class="lp-card__fact-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
					Floors <?php echo esc_html( $total_floors ); ?>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $offer_badges ) ) : ?>
			<div class="lp-card__offer-box">
				<div class="lp-card__offer-inner">
					<?php foreach ( $offer_badges as $badge ) : ?>
					<div class="lp-card__offer-item"><?php echo esc_html( strtoupper( $badge ) ); ?></div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $price_min ) : ?>
			<div class="lp-card__price-label"><?php echo esc_html( $configs ?: 'Luxurious Homes' ); ?> Starts At</div>
			<div class="lp-card__price"><?php echo esc_html( $price_min ); ?>*</div>
			<?php endif; ?>

			<button class="lp-card__brochure js-lp-modal" data-title="Download Brochure" data-cta="Download Now">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
				Download Brochure
			</button>
		</div>

		<!-- CENTER — Project Gallery -->
		<div class="lp-gallery" id="lp-gallery">
			<?php
			$gallery_imgs = ! empty( $banner_desktop ) ? $banner_desktop : array();
			if ( ! empty( $gallery_imgs ) ) : ?>
			<div class="lp-gallery__track" id="lp-gallery-track">
				<?php foreach ( $gallery_imgs as $url ) : ?>
				<div class="lp-gallery__slide"><img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $project_name ); ?>"></div>
				<?php endforeach; ?>
			</div>
			<?php if ( count( $gallery_imgs ) > 1 ) : ?>
			<div class="lp-gallery__dots" id="lp-gal-dots">
				<?php foreach ( $gallery_imgs as $i => $url ) : ?>
				<button class="lp-gallery__dot<?php echo 0 === $i ? ' active' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php else : ?>
			<div class="lp-gallery__placeholder"><?php echo esc_html( $project_name ); ?></div>
			<?php endif; ?>
		</div>

		<!-- RIGHT CARD — Highlights -->
		<div class="lp-card">
			<div class="lp-card__badge"><span class="lp-card__badge-text">Project Highlights</span></div>
			<?php if ( $developer_name ) : ?>
			<div class="lp-card__developer"><?php echo esc_html( $developer_name ); ?></div>
			<?php endif; ?>
			<?php if ( $location ) : ?>
			<div class="lp-card__location" style="margin-bottom:16px;"><?php echo esc_html( $location ); ?></div>
			<?php endif; ?>

			<?php if ( ! empty( $highlights ) ) : ?>
			<ul class="lp-card__hl-list">
				<?php foreach ( $highlights as $hl ) : ?>
				<li class="lp-card__hl-item">
					<span class="lp-card__hl-bullet">&bull;</span>
					<span><?php echo esc_html( $hl ); ?></span>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			<?php if ( $rera_number ) : ?>
			<div class="lp-card__rera">
				<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
				Rera Number: <?php echo esc_html( $rera_number ); ?>
			</div>
			<?php endif; ?>

			<button class="lp-card__deal-cta js-lp-modal" data-title="Get Best Deal & Offers" data-cta="Get Offer!">Get Best Deal &amp; Offers</button>
		</div>

	</div>
</section>

<div class="lp-divider"><div class="lp-divider__line"></div></div>

<!-- ═══ 5. Welcome / About ═══ -->
<?php if ( $dev_about ) : ?>
<section class="lp-about lp-reveal" id="about">
	<div class="lp-about__inner">
		<h2 class="lp-about__title"><?php echo esc_html( $about_heading ?: 'Welcome To ' . $project_name . ' ' . $location ); ?></h2>
		<div class="lp-about__text"><?php echo wp_kses_post( $dev_about ); ?></div>

		<!-- Google Review Badge — 4.8 Stars -->
		<div class="lp-about__review">
			<svg class="lp-about__review-g" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
				<path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
				<path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
				<path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
			</svg>
			<div class="lp-about__review-info">
				<span class="lp-about__review-label">Google Customer Review</span>
				<div style="display:flex;align-items:center;gap:6px;margin-top:3px;">
					<span style="font-size:14px;font-weight:700;color:#1e293b;">4.8</span>
					<div class="lp-about__review-stars">
						<svg viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" fill="#FBBC05"/></svg>
						<svg viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" fill="#FBBC05"/></svg>
						<svg viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" fill="#FBBC05"/></svg>
						<svg viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" fill="#FBBC05"/></svg>
						<svg viewBox="0 0 24 24"><defs><linearGradient id="star80"><stop offset="80%" stop-color="#FBBC05"/><stop offset="80%" stop-color="#d1d5db"/></linearGradient></defs><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" fill="url(#star80)"/></svg>
					</div>
				</div>
			</div>
		</div>

		<div>
			<button class="lp-about__brochure js-lp-modal" data-title="Download Brochure" data-cta="Download Now">
				<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
				Download Brochure
			</button>
			<?php if ( $social_proof ) : ?>
			<div class="lp-about__social"><?php echo esc_html( $social_proof ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<div class="lp-divider"><div class="lp-divider__line"></div></div>

<!-- ═══ 7. Pricing ═══ -->
<?php if ( ! empty( $pricing_configs ) ) : ?>
<section class="lp-pricing lp-reveal" id="pricing">
	<div class="lp-pricing__inner">
		<h2 class="lp-pricing__title"><?php echo esc_html( $project_name ); ?> Price &amp; Carpet Area</h2>
		<div class="lp-pricing-grid">
			<?php
			foreach ( $pricing_configs as $fp_index => $pc ) : ?>
			<div class="lp-pricing-card">
				<?php if ( ! empty( $unitplan_urls[ $fp_index ] ) ) : ?>
				<img class="lp-pricing-card__img" src="<?php echo esc_url( $unitplan_urls[ $fp_index ] ); ?>" alt="<?php echo esc_attr( $pc['config'] ?? 'Floor Plan' ); ?>" loading="lazy">
				<?php elseif ( ! empty( $floorplan_urls[ $fp_index ] ) ) : ?>
				<img class="lp-pricing-card__img" src="<?php echo esc_url( $floorplan_urls[ $fp_index ] ); ?>" alt="<?php echo esc_attr( $pc['config'] ?? 'Floor Plan' ); ?>" loading="lazy">
				<?php else : ?>
				<div class="lp-pricing-card__img-placeholder">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 16l5-5 4 4 3-3 6 6"/><circle cx="8.5" cy="8.5" r="1.5"/></svg>
				</div>
				<?php endif; ?>
				<div class="lp-pricing-card__body">
					<div class="lp-pricing-card__config"><?php echo esc_html( $pc['config'] ?? '' ); ?><?php if ( ! empty( $pc['area'] ) ) : ?><br><span class="lp-pricing-card__area"><?php echo esc_html( $pc['area'] ); ?></span><?php endif; ?></div>
					<div class="lp-pricing-card__price"><?php echo esc_html( $pc['price'] ?? 'Price on Request' ); ?></div>
					<button class="lp-pricing-card__breakup js-lp-modal" data-title="Send Me Costing Details" data-cta="Send Now">Price Breakup</button>
				</div>
			</div>
			<?php $fp_index++; endforeach; ?>
			<!-- Costing details card -->
			<div class="lp-pricing-card lp-pricing-card--costing">
				<?php if ( $costing_img_url ) : ?>
				<img class="lp-pricing-card__img" src="<?php echo esc_url( $costing_img_url ); ?>" alt="Complete Costing Details" loading="lazy">
				<?php endif; ?>
				<div class="lp-pricing-card__body">
					<?php if ( ! $costing_img_url ) : ?>
					<svg class="lp-pricing-card--costing-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 8h20"/><path d="M9 12h6"/><path d="M9 15h4"/></svg>
					<?php endif; ?>
					<div class="lp-pricing-card--costing-label">Complete Costing Details</div>
					<div class="lp-pricing-card--costing-sub">All Breakup</div>
					<button class="lp-pricing-card__breakup js-lp-modal" data-title="Send Me Costing Details" data-cta="Send Now">Download</button>
				</div>
			</div>
		</div>
		<?php if ( $pricing_note ) : ?>
		<div class="lp-pricing-note"><?php echo esc_html( $pricing_note ); ?></div>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<div class="lp-divider"><div class="lp-divider__line"></div></div>

<!-- ═══ 8 & 9. Plans ═══ -->
<section class="lp-plans-section lp-reveal" id="plans">
	<div class="lp-plans-section__inner">
		<h2 class="lp-plans-section__title"><?php echo esc_html( $project_name ); ?> Master Plan &amp; Floor Plan</h2>
		<div class="lp-plans">
			<!-- Master Plan -->
			<div class="lp-plan-card">
				<div class="lp-plan-card__label">Master Plan Layout</div>
				<?php if ( ! empty( $masterplan_urls ) ) : ?>
				<div class="lp-plan-card__img js-lp-modal" data-title="Show Master Layout Plan" data-cta="View Plan">
					<img src="<?php echo esc_url( $masterplan_urls[0] ); ?>" alt="Master Plan" loading="lazy">
					<div class="lp-plan-card__zoom"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg></div>
				</div>
				<?php else : ?>
				<div class="lp-plan-card__img lp-plan-card__img--placeholder js-lp-modal" data-title="Show Master Layout Plan" data-cta="View Plan">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 3v18"/><path d="M14 14h4v4h-4z"/></svg>
				</div>
				<?php endif; ?>
				<div class="lp-plan-card__cta">
					<button class="lp-plan-card__btn js-lp-modal" data-title="Show Master Layout Plan" data-cta="View Plan">Master Plan</button>
				</div>
			</div>
			<!-- Floor / Unit Plan -->
			<div class="lp-plan-card">
				<div class="lp-plan-card__label">Unit Plan Layout</div>
				<?php if ( ! empty( $floorplan_urls ) ) : ?>
				<div class="lp-plan-card__img js-lp-modal" data-title="Show Unit Plan Layout" data-cta="View Plan">
					<img src="<?php echo esc_url( $floorplan_urls[0] ); ?>" alt="Floor Plan" loading="lazy">
					<div class="lp-plan-card__zoom"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg></div>
				</div>
				<?php else : ?>
				<div class="lp-plan-card__img lp-plan-card__img--placeholder js-lp-modal" data-title="Show Unit Plan Layout" data-cta="View Plan">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 3v18"/><path d="M14 14h4v4h-4z"/></svg>
				</div>
				<?php endif; ?>
				<div class="lp-plan-card__cta">
					<button class="lp-plan-card__btn js-lp-modal" data-title="Show Unit Plan Layout" data-cta="View Plan">Floor Plan</button>
				</div>
			</div>
		</div>
	</div>
</section>

<div class="lp-divider"><div class="lp-divider__line"></div></div>

<!-- ═══ 10. Amenities ═══ -->
<?php if ( ! empty( $amenities ) ) : ?>
<section class="lp-amenities lp-reveal" id="amenities">
	<div class="lp-amenities__inner">
		<h2 class="lp-amenities__title"><?php echo esc_html( $project_name ); ?> Amenities</h2>
		<div class="lp-amenities-grid">
			<?php
			// Stock images for amenities — auto-matched by keyword
			$amenity_stock = array(
				'pool'      => '/wp-content/uploads/swimming-pool.jpg',
				'swim'      => '/wp-content/uploads/swimming-pool.jpg',
				'gym'       => '/wp-content/uploads/gymnasium.jpg',
				'fitness'   => '/wp-content/uploads/gymnasium.jpg',
				'garden'    => '/wp-content/uploads/garden.jpg',
				'landscape' => '/wp-content/uploads/garden.jpg',
				'green'     => '/wp-content/uploads/garden.jpg',
				'park'      => '/wp-content/uploads/garden.jpg',
				'club'      => '/wp-content/uploads/clubhouse.jpg',
				'banquet'   => '/wp-content/uploads/clubhouse.jpg',
				'hall'      => '/wp-content/uploads/clubhouse.jpg',
				'community' => '/wp-content/uploads/clubhouse.jpg',
				'play'      => '/wp-content/uploads/children-play-area.jpg',
				'kid'       => '/wp-content/uploads/children-play-area.jpg',
				'child'     => '/wp-content/uploads/children-play-area.jpg',
				'toddler'   => '/wp-content/uploads/children-play-area.jpg',
				'jog'       => '/wp-content/uploads/jogging-track.jpg',
				'track'     => '/wp-content/uploads/jogging-track.jpg',
				'walk'      => '/wp-content/uploads/jogging-track.jpg',
				'running'   => '/wp-content/uploads/jogging-track.jpg',
			);

			// SVG icon fallback when no stock image matches
			$amenity_icons = array(
				// Water & Pool
				'pool'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="8" width="48" height="36" rx="4"/><path d="M8 32c4-3 8-3 12 0s8 3 12 0 8-3 12 0 8 3 12 0"/><path d="M8 38c4-3 8-3 12 0s8 3 12 0 8-3 12 0 8 3 12 0"/><path d="M24 8v24M40 8v24"/><rect x="12" y="48" width="40" height="8" rx="2" fill="currentColor" opacity="0.1"/></svg>',
				'swim'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="8" width="48" height="36" rx="4"/><path d="M8 32c4-3 8-3 12 0s8 3 12 0 8-3 12 0 8 3 12 0"/><path d="M8 38c4-3 8-3 12 0s8 3 12 0 8-3 12 0 8 3 12 0"/><rect x="12" y="48" width="40" height="8" rx="2" fill="currentColor" opacity="0.1"/></svg>',
				// Fitness
				'gym'         => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M8 32h48"/><rect x="12" y="22" width="8" height="20" rx="2"/><rect x="44" y="22" width="8" height="20" rx="2"/><rect x="6" y="26" width="6" height="12" rx="1.5"/><rect x="52" y="26" width="6" height="12" rx="1.5"/><circle cx="32" cy="14" r="4" fill="currentColor" opacity="0.15"/><path d="M28 52h8" stroke-width="3"/></svg>',
				'fitness'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M8 32h48"/><rect x="12" y="22" width="8" height="20" rx="2"/><rect x="44" y="22" width="8" height="20" rx="2"/><rect x="6" y="26" width="6" height="12" rx="1.5"/><rect x="52" y="26" width="6" height="12" rx="1.5"/></svg>',
				// Green spaces
				'garden'      => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 56V28"/><path d="M22 36c-6-2-10-8-10-14 6 0 12 4 14 8" fill="currentColor" opacity="0.08"/><path d="M42 36c6-2 10-8 10-14-6 0-12 4-14 8" fill="currentColor" opacity="0.08"/><path d="M22 36c-6-2-10-8-10-14 6 0 12 4 14 8"/><path d="M42 36c6-2 10-8 10-14-6 0-12 4-14 8"/><path d="M26 48c-4 0-8-2-10-6 4-2 8 0 10 2"/><path d="M38 48c4 0 8-2 10-6-4-2-8 0-10 2"/><ellipse cx="32" cy="18" rx="8" ry="10" fill="currentColor" opacity="0.08"/><ellipse cx="32" cy="18" rx="8" ry="10"/></svg>',
				'park'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 56V28"/><path d="M20 28c0-8 5.4-16 12-16s12 8 12 16" fill="currentColor" opacity="0.08"/><path d="M20 28c0-8 5.4-16 12-16s12 8 12 16"/><path d="M16 36c0-6 7.2-12 16-12s16 6 16 12" fill="currentColor" opacity="0.08"/><path d="M16 36c0-6 7.2-12 16-12s16 6 16 12"/><rect x="8" y="54" width="48" height="4" rx="2" fill="currentColor" opacity="0.1"/></svg>',
				'landscape'   => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 56V28"/><path d="M22 36c-6-2-10-8-10-14 6 0 12 4 14 8"/><path d="M42 36c6-2 10-8 10-14-6 0-12 4-14 8"/><rect x="8" y="54" width="48" height="4" rx="2" fill="currentColor" opacity="0.1"/></svg>',
				'green'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 56V28"/><path d="M22 36c-6-2-10-8-10-14 6 0 12 4 14 8"/><path d="M42 36c6-2 10-8 10-14-6 0-12 4-14 8"/></svg>',
				// Clubhouse
				'club'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 28L32 10l26 18" fill="currentColor" opacity="0.05"/><path d="M6 28L32 10l26 18"/><rect x="10" y="28" width="44" height="28" rx="2" fill="currentColor" opacity="0.05"/><rect x="10" y="28" width="44" height="28" rx="2"/><rect x="24" y="40" width="16" height="16" rx="1"/><path d="M32 40v16"/><path d="M16 34h6v8h-6zM42 34h6v8h-6z"/></svg>',
				'banquet'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 28L32 10l26 18"/><rect x="10" y="28" width="44" height="28" rx="2"/><rect x="24" y="40" width="16" height="16" rx="1"/><path d="M32 40v16"/></svg>',
				// Kids & Play
				'play'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 56V20l16-8v44"/><path d="M32 12l16 8v36"/><path d="M16 20h32"/><path d="M24 20c0 4-2 8-4 10M40 20c0 4 2 8 4 10"/><circle cx="24" cy="44" r="6"/><circle cx="40" cy="44" r="6"/><path d="M24 38v-8M40 38v-8"/></svg>',
				'kid'         => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="14" r="6"/><path d="M32 20v14"/><path d="M22 56l10-22 10 22"/><path d="M20 32h24"/><path d="M24 32l-6 10M40 32l6 10"/></svg>',
				'child'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="14" r="6"/><path d="M32 20v14"/><path d="M22 56l10-22 10 22"/><path d="M20 32h24"/></svg>',
				'toddler'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="14" r="6"/><path d="M32 20v14"/><path d="M22 56l10-22 10 22"/><path d="M20 32h24"/></svg>',
				// Sports
				'jog'         => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="36" cy="10" r="5"/><path d="M20 56l8-18 8 6 10-24"/><path d="M28 44l-8 2"/><path d="M46 56l-6-14"/><path d="M8 56h48" stroke-dasharray="4 4"/></svg>',
				'track'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><ellipse cx="32" cy="36" rx="24" ry="16"/><ellipse cx="32" cy="36" rx="16" ry="8"/><path d="M32 20v32"/></svg>',
				'tennis'      => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="16" width="48" height="32" rx="2"/><path d="M32 16v32"/><path d="M8 32h48"/><circle cx="42" cy="12" r="6"/><path d="M38 8l8 8M46 8l-8 8"/></svg>',
				'badminton'   => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="16" width="48" height="32" rx="2"/><path d="M32 16v32"/><circle cx="20" cy="14" r="4"/><path d="M20 10l-6-6M20 10l6-6"/></svg>',
				'cricket'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="20" r="8"/><path d="M24 28l-8 28M40 28l8 28"/><path d="M32 28v28"/><path d="M20 56h24"/></svg>',
				'basketball'  => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="32" r="14"/><path d="M18 32h28"/><path d="M32 18v28"/><path d="M20 22c4 4 4 16 0 20"/><path d="M44 22c-4 4-4 16 0 20"/></svg>',
				'sport'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="32" r="14"/><path d="M18 32h28"/><path d="M32 18v28"/></svg>',
				'yoga'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="12" r="5"/><path d="M32 17v16"/><path d="M18 28l14 5 14-5"/><path d="M24 56l8-23 8 23"/><path d="M12 56h40" stroke-dasharray="4 4"/></svg>',
				'meditation'  => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="12" r="5"/><path d="M32 17v10"/><path d="M20 38c0-8 5.4-11 12-11s12 3 12 11"/><path d="M16 56c0-8 7.2-12 16-12s16 4 16 12"/></svg>',
				// Parking & Transport
				'parking'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="10" y="10" width="44" height="44" rx="6" fill="currentColor" opacity="0.05"/><rect x="10" y="10" width="44" height="44" rx="6"/><path d="M24 46V18h10a8 8 0 110 16H24" stroke-width="3.5"/></svg>',
				'car'         => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="10" y="10" width="44" height="44" rx="6"/><path d="M24 46V18h10a8 8 0 110 16H24" stroke-width="3"/></svg>',
				// Security
				'security'    => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 6l18 10v14c0 13-8 22-18 26C22 52 14 43 14 30V16L32 6z" fill="currentColor" opacity="0.05"/><path d="M32 6l18 10v14c0 13-8 22-18 26C22 52 14 43 14 30V16L32 6z"/><path d="M24 32l6 6 10-12" stroke-width="3"/></svg>',
				'cctv'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="14" y="18" width="24" height="16" rx="3"/><path d="M38 24l12-6v20l-12-6"/><rect x="26" y="34" width="4" height="12"/><rect x="20" y="46" width="16" height="4" rx="1"/><circle cx="22" cy="26" r="2" fill="currentColor"/></svg>',
				'guard'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 6l18 10v14c0 13-8 22-18 26C22 52 14 43 14 30V16L32 6z"/><path d="M24 32l6 6 10-12" stroke-width="3"/></svg>',
				// Utilities
				'lift'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="14" y="6" width="36" height="52" rx="4"/><path d="M32 6v52"/><path d="M22 26l-4-6 4-6" stroke-width="2"/><path d="M42 32l4 6-4 6" stroke-width="2"/></svg>',
				'elevator'    => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="14" y="6" width="36" height="52" rx="4"/><path d="M32 6v52"/><path d="M22 26l-4-6 4-6"/><path d="M42 32l4 6-4 6"/></svg>',
				'power'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M36 6L14 34h18l-4 24 24-30H34l2-22z" fill="currentColor" opacity="0.08"/><path d="M36 6L14 34h18l-4 24 24-30H34l2-22z"/></svg>',
				'backup'      => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M36 6L14 34h18l-4 24 24-30H34l2-22z"/></svg>',
				'water'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 8C32 8 16 28 16 40a16 16 0 0032 0C48 28 32 8 32 8z" fill="currentColor" opacity="0.06"/><path d="M32 8C32 8 16 28 16 40a16 16 0 0032 0C48 28 32 8 32 8z"/><path d="M26 42c0-4 2.7-8 6-8s6 4 6 8"/></svg>',
				'rain'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 8C32 8 16 28 16 40a16 16 0 0032 0C48 28 32 8 32 8z"/></svg>',
				// Indoor
				'indoor'      => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="14" width="48" height="36" rx="3"/><path d="M8 24h48"/><path d="M24 24v26M40 24v26"/><circle cx="32" cy="34" r="6"/></svg>',
				'game'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="18" width="48" height="28" rx="4"/><circle cx="24" cy="32" r="6"/><circle cx="42" cy="28" r="3"/><circle cx="42" cy="36" r="3"/><circle cx="38" cy="32" r="3"/><circle cx="46" cy="32" r="3"/></svg>',
				'table tennis' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="22" cy="28" r="12"/><path d="M30 36l14 14"/><circle cx="46" cy="18" r="4"/><path d="M32 32h-2"/></svg>',
				// Religious
				'temple'      => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 6l-22 18h44L32 6z" fill="currentColor" opacity="0.05"/><path d="M32 6l-22 18h44L32 6z"/><rect x="14" y="24" width="36" height="30"/><rect x="26" y="36" width="12" height="18" rx="6"/><path d="M32 6v-2M30 4h4"/></svg>',
				'mandir'      => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 6l-22 18h44L32 6z"/><rect x="14" y="24" width="36" height="30"/><rect x="26" y="36" width="12" height="18" rx="6"/></svg>',
				// Multipurpose
				'hall'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="16" width="48" height="38" rx="3"/><path d="M8 24h48"/><rect x="16" y="30" width="12" height="8" rx="1"/><rect x="36" y="30" width="12" height="8" rx="1"/><rect x="16" y="44" width="32" height="4" rx="1"/></svg>',
				'community'   => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="20" cy="18" r="6"/><circle cx="44" cy="18" r="6"/><circle cx="32" cy="14" r="6"/><path d="M8 42c0-8 5.4-14 12-14M44 28c6.6 0 12 6 12 14"/><path d="M14 42c0-10 8-16 18-16s18 6 18 16"/><rect x="8" y="48" width="48" height="6" rx="2" fill="currentColor" opacity="0.1"/></svg>',
				'multipurpose' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="16" width="48" height="38" rx="3"/><path d="M8 24h48"/><rect x="16" y="30" width="12" height="8" rx="1"/><rect x="36" y="30" width="12" height="8" rx="1"/></svg>',
				// Senior
				'senior'      => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="28" cy="14" r="6"/><path d="M28 20v18"/><path d="M18 56l10-18"/><path d="M38 56l-10-18"/><path d="M42 24l-14 8"/><path d="M38 38v18" stroke-linecap="round"/></svg>',
				'elder'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="28" cy="14" r="6"/><path d="M28 20v18"/><path d="M18 56l10-18"/><path d="M38 56l-10-18"/><path d="M38 38v18"/></svg>',
				// Amphitheatre
				'amphitheatre' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><ellipse cx="32" cy="44" rx="24" ry="10"/><path d="M12 38c0-4 9-8 20-8s20 4 20 8"/><path d="M16 32c0-4 7-7 16-7s16 3 16 7"/><rect x="26" y="46" width="12" height="8" rx="1"/></svg>',
				'theatre'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="12" width="48" height="40" rx="4"/><path d="M8 20h48"/><rect x="16" y="28" width="32" height="16" rx="2"/><path d="M24 36h16"/></svg>',
				// Salon & Spa
				'salon'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="20" r="10"/><path d="M22 20c0-6 4.5-10 10-10"/><path d="M42 20c0-6-4.5-10-10-10"/><rect x="20" y="36" width="24" height="20" rx="4"/><path d="M28 44h8"/><path d="M28 50h8"/></svg>',
				'spa'         => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 8c0 12-16 16-16 28a16 16 0 0032 0c0-12-16-16-16-28z" fill="currentColor" opacity="0.06"/><path d="M32 8c0 12-16 16-16 28a16 16 0 0032 0c0-12-16-16-16-28z"/><path d="M32 32v16M26 38l6-6 6 6"/></svg>',
				// Library
				'library'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="10" y="12" width="10" height="40" rx="1"/><rect x="22" y="16" width="10" height="36" rx="1"/><rect x="34" y="10" width="10" height="42" rx="1"/><rect x="46" y="14" width="10" height="38" rx="1"/><path d="M6 54h52"/></svg>',
				'reading'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M8 12l24 8 24-8v40l-24 6-24-6V12z"/><path d="M32 20v38"/></svg>',
				// Terrace
				'terrace'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="28" width="48" height="4"/><path d="M12 32v22M52 32v22M8 54h48"/><path d="M24 28V16M40 28V16"/><path d="M16 16h32" stroke-dasharray="4 4"/><circle cx="32" cy="10" r="4"/></svg>',
				'roof'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="28" width="48" height="4"/><path d="M12 32v22M52 32v22M8 54h48"/><path d="M20 28V16h24v12"/></svg>',
				// Intercom
				'intercom'    => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="16" y="8" width="32" height="48" rx="4"/><circle cx="32" cy="24" r="8"/><rect x="24" y="40" width="16" height="6" rx="2"/><circle cx="26" cy="52" r="2" fill="currentColor"/><circle cx="38" cy="52" r="2" fill="currentColor"/></svg>',
				'video door'  => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="16" y="8" width="32" height="48" rx="4"/><circle cx="32" cy="24" r="8"/><rect x="24" y="40" width="16" height="6" rx="2"/></svg>',
				// Fire
				'fire'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M32 6c0 10 14 16 14 30a14 14 0 01-28 0C18 22 32 16 32 6z" fill="currentColor" opacity="0.06"/><path d="M32 6c0 10 14 16 14 30a14 14 0 01-28 0C18 22 32 16 32 6z"/><path d="M32 30c0 4 6 6 6 14a6 6 0 01-12 0c0-8 6-10 6-14z"/></svg>',
				// Vastu
				'vastu'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="12" y="12" width="40" height="40" rx="2"/><path d="M12 32h40M32 12v40"/><path d="M12 12l40 40M52 12L12 52" stroke-dasharray="4 4"/><circle cx="32" cy="32" r="6"/></svg>',
				// Piped gas
				'gas'         => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 48h24v8H20z"/><path d="M26 32v16M38 32v16"/><path d="M22 32h20" stroke-width="3"/><path d="M28 18c0-4 1.8-8 4-8s4 4 4 8c0 6-4 8-4 14M32 18c0 6 4 8 4 14"/></svg>',
				'piped'       => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 48h24v8H20z"/><path d="M26 32v16M38 32v16"/><path d="M22 32h20"/><path d="M28 18c0-4 2-8 4-8s4 4 4 8"/></svg>',
				// Wi-Fi
				'wifi'        => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="50" r="3" fill="currentColor"/><path d="M22 42c2.8-2.8 6.2-4 10-4s7.2 1.2 10 4"/><path d="M14 34c5-5 11-8 18-8s13 3 18 8"/><path d="M6 26c7.2-7.2 16-10 26-10s18.8 2.8 26 10"/></svg>',
				'internet'    => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="50" r="3" fill="currentColor"/><path d="M22 42c2.8-2.8 6.2-4 10-4s7.2 1.2 10 4"/><path d="M14 34c5-5 11-8 18-8s13 3 18 8"/></svg>',
				// Cycling
				'cycling'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="18" cy="44" r="10"/><circle cx="46" cy="44" r="10"/><path d="M18 44l10-18h10l8 18"/><path d="M28 26l4 18"/><circle cx="32" cy="22" r="4"/></svg>',
				'bicycle'     => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="18" cy="44" r="10"/><circle cx="46" cy="44" r="10"/><path d="M18 44l10-18h10l8 18"/><path d="M28 26l4 18"/></svg>',
				// Squash
				'squash'      => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="8" width="48" height="48" rx="2"/><path d="M8 8v48"/><path d="M8 32h48" stroke-dasharray="4 4"/><circle cx="36" cy="24" r="4"/></svg>',
				// AC
				'air condition' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="8" y="14" width="48" height="22" rx="4"/><path d="M16 36c0 6 4 12 8 14M32 36c0 6 0 12 0 16M48 36c0 6-4 12-8 14"/><path d="M14 28h36" stroke-dasharray="3 3"/></svg>',
			);
			$default_icon = '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="32" cy="32" r="20" fill="currentColor" opacity="0.05"/><path d="M22 34l8 8 14-16" stroke-width="3"/></svg>';
			foreach ( array_slice( $amenities, 0, 6 ) as $ai => $amenity ) :
				$has_img = ! empty( $amenity_imgs[ $ai ] );
				$stock_img = '';
				$icon = $default_icon;
				$amenity_lower = strtolower( $amenity );
				if ( ! $has_img ) {
					// Try stock image first
					foreach ( $amenity_stock as $kw => $img_path ) {
						if ( strpos( $amenity_lower, $kw ) !== false ) { $stock_img = $img_path; break; }
					}
					// Then try SVG icon
					if ( ! $stock_img ) {
						foreach ( $amenity_icons as $kw => $svg ) {
							if ( strpos( $amenity_lower, $kw ) !== false ) { $icon = $svg; break; }
						}
					}
				}
			?>
			<div class="lp-amenity">
				<?php if ( $has_img ) : ?>
				<div class="lp-amenity__img"><img src="<?php echo esc_url( $amenity_imgs[ $ai ] ); ?>" alt="<?php echo esc_attr( $amenity ); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;"></div>
				<?php elseif ( $stock_img ) : ?>
				<div class="lp-amenity__img"><img src="<?php echo esc_url( $stock_img ); ?>" alt="<?php echo esc_attr( $amenity ); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;"></div>
				<?php else : ?>
				<div class="lp-amenity__img"><?php echo $icon; ?></div>
				<?php endif; ?>
				<div class="lp-amenity__label"><?php echo esc_html( $amenity ); ?></div>
			</div>
			<?php endforeach; ?>
		</div>
		<!-- Mobile carousel (hidden on desktop, shown on mobile) -->
		<div class="lp-amenities-carousel" id="amenities-carousel">
			<div class="lp-amenities-carousel__track">
				<?php foreach ( array_slice( $amenities, 0, 6 ) as $ai => $amenity ) :
					$has_img = ! empty( $amenity_imgs[ $ai ] );
					$stock_img = '';
					$icon = $default_icon;
					$amenity_lower = strtolower( $amenity );
					if ( ! $has_img ) {
						foreach ( $amenity_stock as $kw => $img_path ) {
							if ( strpos( $amenity_lower, $kw ) !== false ) { $stock_img = $img_path; break; }
						}
						if ( ! $stock_img ) {
							foreach ( $amenity_icons as $kw => $svg ) {
								if ( strpos( $amenity_lower, $kw ) !== false ) { $icon = $svg; break; }
							}
						}
					}
				?>
				<div class="lp-amenities-carousel__slide">
					<div class="lp-amenity">
						<?php if ( $has_img ) : ?>
						<div class="lp-amenity__img"><img src="<?php echo esc_url( $amenity_imgs[ $ai ] ); ?>" alt="<?php echo esc_attr( $amenity ); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;"></div>
						<?php elseif ( $stock_img ) : ?>
						<div class="lp-amenity__img"><img src="<?php echo esc_url( $stock_img ); ?>" alt="<?php echo esc_attr( $amenity ); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;"></div>
						<?php else : ?>
						<div class="lp-amenity__img"><?php echo $icon; ?></div>
						<?php endif; ?>
						<div class="lp-amenity__label"><?php echo esc_html( $amenity ); ?></div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="lp-amenities-carousel__dots">
				<?php for ( $d = 0; $d < min( count( $amenities ), 6 ); $d++ ) : ?>
				<button class="lp-amenities-carousel__dot<?php echo $d === 0 ? ' active' : ''; ?>" data-index="<?php echo $d; ?>"></button>
				<?php endfor; ?>
			</div>
		</div>
		<div class="lp-amenities__cta">
			<button class="lp-amenities__btn js-lp-modal" data-title="Download Full Gallery" data-cta="Download Now">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
				Download Full Gallery
			</button>
		</div>
	</div>
</section>
<?php endif; ?>

<div class="lp-divider"><div class="lp-divider__line"></div></div>

<!-- ═══ 11. Location ═══ -->
<?php if ( ! empty( $loc_advantages ) || $maps_embed || $google_biz_name ) : ?>
<section class="lp-location lp-reveal" id="location">
	<div class="lp-location__inner">
		<h2 class="lp-location__title"><?php echo esc_html( $project_name ); ?> Location Advantage</h2>

		<?php if ( $loc_brief ) : ?>
		<p class="lp-location__desc"><?php echo esc_html( $loc_brief ); ?></p>
		<?php endif; ?>

		<div class="lp-location__grid">
			<!-- Left: Map -->
			<div class="lp-location__map">
				<?php if ( $maps_embed && stripos( $maps_embed, '<iframe' ) !== false ) : ?>
				<?php echo wp_kses( $maps_embed, array( 'iframe' => array( 'src' => true, 'width' => true, 'height' => true, 'style' => true, 'frameborder' => true, 'allowfullscreen' => true, 'loading' => true, 'referrerpolicy' => true ) ) ); ?>
				<?php elseif ( $google_biz_name ) : ?>
				<iframe src="https://maps.google.com/maps?q=<?php echo urlencode( $google_biz_name ); ?>&output=embed&z=15" width="100%" height="400" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
				<?php else : ?>
				<div class="lp-location__map-placeholder">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0L6.343 16.657a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
				</div>
				<?php endif; ?>
			</div>

			<!-- Right: Connectivity Card -->
			<div class="lp-location__card">
				<div class="lp-location__card-title">Best Connectivity At</div>
				<div class="lp-location__card-sub"><?php echo esc_html( $location ); ?></div>
				<?php if ( ! empty( $loc_advantages ) ) : ?>
				<ul class="lp-location__list">
					<?php foreach ( array_slice( $loc_advantages, 0, 8 ) as $la ) : ?>
					<li>
						<span class="lp-location__list-place"><?php echo esc_html( $la['place'] ?? '' ); ?></span>
						<span class="lp-location__list-dist"><?php echo esc_html( $la['distance'] ?? '' ); ?></span>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
				<button class="lp-location__brochure js-lp-modal" data-title="Download Brochure" data-cta="Download Now">
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
					Download Brochure
				</button>
				<?php if ( $social_proof ) : ?>
				<div class="lp-location__social"><?php echo esc_html( $social_proof ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<div class="lp-divider"><div class="lp-divider__line"></div></div>

<!-- ═══ 12. Virtual Site Visit (Image-only, lead-gen) ═══ -->
<?php
// Use dedicated virtual tour images if uploaded, otherwise default IDs 188 & 189.
$vt_thumbs = $g( 'lp_virtual_tour_ids' );
if ( empty( $vt_thumbs ) ) {
	$_vt1 = wp_get_attachment_image_url( 188, 'full' );
	$_vt2 = wp_get_attachment_image_url( 189, 'full' );
	if ( $_vt1 ) { $vt_thumbs[] = $_vt1; }
	if ( $_vt2 ) { $vt_thumbs[] = $_vt2; }
}
?>
<section class="lp-tour lp-reveal" id="virtual-tour">
	<div class="lp-tour__inner">
		<h2 class="lp-tour__title"><?php echo esc_html( $project_name ); ?> Virtual Site Visit</h2>
		<div class="lp-tour-grid">
			<div class="lp-tour-card js-lp-modal" data-title="Virtual Site Visit" data-cta="Start Tour">
				<div class="lp-tour-card__label">Sample Flat Tour</div>
				<div class="lp-tour-card__media">
					<?php if ( ! empty( $vt_thumbs[0] ) ) : ?>
					<img class="lp-tour-card__thumb" src="<?php echo esc_url( $vt_thumbs[0] ); ?>" alt="Sample Flat Tour" loading="lazy">
					<?php else : ?>
					<div class="lp-tour-card__thumb-placeholder"></div>
					<?php endif; ?>
					<div class="lp-tour-card__play">
						<svg viewBox="0 0 68 48"><path d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55C3.97 2.33 2.27 4.81 1.48 7.74.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z" fill="red"/><path d="M45 24L27 14v20" fill="#fff"/></svg>
					</div>
				</div>
			</div>
			<div class="lp-tour-card js-lp-modal" data-title="Virtual Site Visit" data-cta="Start Tour">
				<div class="lp-tour-card__label">360° Drone Video</div>
				<div class="lp-tour-card__media">
					<?php if ( ! empty( $vt_thumbs[1] ) ) : ?>
					<img class="lp-tour-card__thumb" src="<?php echo esc_url( $vt_thumbs[1] ); ?>" alt="360° Drone Video" loading="lazy">
					<?php elseif ( ! empty( $vt_thumbs[0] ) ) : ?>
					<img class="lp-tour-card__thumb" src="<?php echo esc_url( $vt_thumbs[0] ); ?>" alt="360° Drone Video" loading="lazy">
					<?php else : ?>
					<div class="lp-tour-card__thumb-placeholder"></div>
					<?php endif; ?>
					<div class="lp-tour-card__play">
						<svg viewBox="0 0 68 48"><path d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55C3.97 2.33 2.27 4.81 1.48 7.74.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z" fill="red"/><path d="M45 24L27 14v20" fill="#fff"/></svg>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<div class="lp-divider"><div class="lp-divider__line"></div></div>

<!-- ═══ 13. Site Visit Form ═══ -->
<section class="lp-sitevisit lp-reveal" id="sitevisit">
	<div class="lp-sitevisit__inner">
		<h2 class="lp-sitevisit__title"><?php echo esc_html( $sv_heading ); ?> for <?php echo esc_html( $project_name ); ?></h2>
		<?php if ( $sv_social ) : ?>
		<p class="lp-sitevisit__social"><?php echo esc_html( $sv_social ); ?></p>
		<?php endif; ?>
		<form class="lp-sitevisit__form" id="lp-sv-form" autocomplete="off">
			<input type="text" name="name" class="lp-input" placeholder="Name" autocomplete="name">
			<input type="tel" name="phone" class="lp-input" placeholder="Mobile Number" maxlength="10" inputmode="numeric" autocomplete="tel" required>
			<button type="submit" class="lp-btn">Book Free Visit Now!</button>
		</form>
		<div class="lp-sitevisit__msg" id="lp-sv-msg">Thank you! We'll call you to confirm your visit.</div>
	</div>
</section>

<!-- ═══ 14. Footer ═══ -->
<footer class="lp-footer">
	<div class="lp-footer__inner">
		<div class="lp-footer__top">
			<?php if ( $dev_logo ) : ?>
			<div class="lp-footer__logo"><img src="<?php echo esc_url( $dev_logo ); ?>" alt="<?php echo esc_attr( $developer_name ); ?>"></div>
			<?php endif; ?>
			<nav class="lp-footer__nav">
				<a href="#pricing">Area &amp; Price</a>
				<a href="#amenities">Amenities</a>
				<a href="#location">Location</a>
				<a href="#virtual-tour">Virtual Tour</a>
				<a href="#sitevisit">Site Visit</a>
			</nav>
		</div>
		<?php if ( $dev_about ) : ?>
		<div class="lp-footer__about"><?php echo esc_html( wp_strip_all_tags( wp_trim_words( $dev_about, 60, '...' ) ) ); ?></div>
		<?php endif; ?>
		<?php if ( $rera_number ) : ?>
		<div class="lp-footer__rera">
			RERA: <?php echo esc_html( $rera_number ); ?>
			<?php if ( $rera_link ) : ?> &mdash; <a href="<?php echo esc_url( $rera_link ); ?>" target="_blank" rel="noopener">Verify</a><?php endif; ?>
		</div>
		<?php endif; ?>
		<?php if ( $disclaimer ) : ?>
		<div class="lp-footer__disclaimer"><?php echo wp_kses_post( $disclaimer ); ?></div>
		<?php endif; ?>
		<div class="lp-footer__copy">&copy; <?php echo gmdate( 'Y' ); ?> <?php echo esc_html( $site_name ); ?>. All rights reserved.</div>
	</div>
</footer>

<!-- ═══ 15. Sticky Mobile CTA ═══ -->
<?php if ( $phone || $whatsapp ) : ?>
<div class="lp-sticky">
	<div class="lp-sticky__inner">
		<?php if ( $phone ) : ?>
		<a href="tel:<?php echo esc_attr( $phone ); ?>" class="lp-sticky__btn lp-sticky__btn--call">
			<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
			Call Now
		</a>
		<?php endif; ?>
		<?php if ( $whatsapp ) : ?>
		<a href="https://wa.me/<?php echo esc_attr( $whatsapp ); ?>?text=<?php echo rawurlencode( 'Hi, I am interested in ' . $project_name ); ?>" target="_blank" rel="noopener" class="lp-sticky__btn lp-sticky__btn--wa">
			<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
			WhatsApp
		</a>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

<!-- ═══ Popup Modal ═══ -->
<div id="lp-modal" class="lp-modal">
	<div class="lp-modal__backdrop" id="lp-modal-backdrop"></div>
	<div class="lp-modal__panel">
		<button class="lp-modal__close" id="lp-modal-close">&times;</button>
		<div class="lp-modal__header">
			<?php if ( $dev_logo ) : ?>
			<img class="lp-modal__logo" src="<?php echo esc_url( $dev_logo ); ?>" alt="<?php echo esc_attr( $developer_name ); ?>">
			<?php endif; ?>
			<div class="lp-modal__header-text">
				<h3 class="lp-modal__title" id="lp-modal-title">Register Here And Avail The Best Offers!!</h3>
				<p class="lp-modal__sub"><?php echo esc_html( $project_name ); ?> &mdash; <?php echo esc_html( $location ); ?></p>
			</div>
		</div>
		<div class="lp-modal__content">
			<div class="lp-modal__promises">
				<div class="lp-modal__promises-title">We <span>Promise</span></div>
				<div class="lp-modal__promise">
					<div class="lp-modal__promise-icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
					</div>
					<div class="lp-modal__promise-text">
						<h4>Instant Call Back</h4>
						<p>Get a callback within 5 minutes</p>
					</div>
				</div>
				<div class="lp-modal__promise">
					<div class="lp-modal__promise-icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
					</div>
					<div class="lp-modal__promise-text">
						<h4>Free Site Visit</h4>
						<p>Complimentary pickup &amp; drop</p>
					</div>
				</div>
				<div class="lp-modal__promise">
					<div class="lp-modal__promise-icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
					</div>
					<div class="lp-modal__promise-text">
						<h4>Unmatched Price</h4>
						<p>Best deal guaranteed, no brokerage</p>
					</div>
				</div>
			</div>
			<div class="lp-modal__body">
				<div class="lp-modal__form-title" id="lp-modal-form-label">Fill in your details</div>
				<form class="lp-modal__form" id="lp-modal-form" autocomplete="off">
					<input type="text" name="name" class="lp-input" placeholder="Name" autocomplete="name">
					<input type="email" name="email" class="lp-input" placeholder="Email (Optional)" autocomplete="email">
					<div class="lp-modal__phone-row">
						<div class="lp-modal__phone-prefix">
							<img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 900 600'%3E%3Crect fill='%23f93' width='900' height='200'/%3E%3Crect fill='%23fff' y='200' width='900' height='200'/%3E%3Crect fill='%23128807' y='400' width='900' height='200'/%3E%3Ccircle fill='%23008' cx='450' cy='300' r='60'/%3E%3Ccircle fill='%23fff' cx='450' cy='300' r='50'/%3E%3Ccircle fill='%23008' cx='450' cy='300' r='16'/%3E%3C/svg%3E" alt="IN">
							+91
						</div>
						<input type="tel" name="phone" class="lp-input" placeholder="Mobile Number" maxlength="10" inputmode="numeric" autocomplete="tel" required>
					</div>
					<button type="submit" class="lp-btn" id="lp-modal-cta">Get Instant Call Back</button>
				</form>
				<?php if ( $social_proof ) : ?>
				<div class="lp-modal__social"><?php echo esc_html( $social_proof ); ?></div>
				<?php endif; ?>
				<div class="lp-modal__msg" id="lp-modal-msg">Thank you! We'll contact you shortly.</div>
			</div>
		</div>
		<?php if ( $phone ) : ?>
		<div class="lp-modal__footer">
			<a href="tel:<?php echo esc_attr( $phone ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
				<?php echo esc_html( $phone ); ?>
			</a>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- ═══ Inline JS ═══ -->
<script>
(function(){
	'use strict';

	var ajaxUrl     = <?php echo wp_json_encode( $ajax_url ); ?>;
	var nonce       = <?php echo wp_json_encode( $nonce ); ?>;
	var leadSource  = <?php echo wp_json_encode( $lead_source ); ?>;
	var projectName = <?php echo wp_json_encode( $project_name ); ?>;
	var pageUrl     = window.location.href;
	var thankYouUrl = <?php echo wp_json_encode( home_url( '/thank-you/' ) ); ?>;

	/* ── Tracking Data Collector ── */
	function getTrackingData() {
		var params = new URLSearchParams(window.location.search);
		return {
			utm_source:   params.get('utm_source') || '',
			utm_medium:   params.get('utm_medium') || '',
			utm_campaign: params.get('utm_campaign') || '',
			utm_term:     params.get('utm_term') || '',
			utm_content:  params.get('utm_content') || '',
			referrer:     document.referrer || '',
			device:       /Mobi|Android/i.test(navigator.userAgent) ? 'mobile' : 'desktop',
			browser:      (function() {
				var ua = navigator.userAgent;
				if (ua.indexOf('Chrome') > -1 && ua.indexOf('Edg') === -1) return 'Chrome';
				if (ua.indexOf('Safari') > -1 && ua.indexOf('Chrome') === -1) return 'Safari';
				if (ua.indexOf('Firefox') > -1) return 'Firefox';
				if (ua.indexOf('Edg') > -1) return 'Edge';
				return 'Other';
			})(),
			os: (function() {
				var ua = navigator.userAgent;
				if (/iPhone|iPad|iPod/.test(ua)) return 'iOS';
				if (/Android/.test(ua)) return 'Android';
				if (/Windows/.test(ua)) return 'Windows';
				if (/Mac OS/.test(ua)) return 'macOS';
				if (/Linux/.test(ua)) return 'Linux';
				return 'Other';
			})(),
			screen: screen.width + 'x' + screen.height,
			landing_page: window.location.pathname
		};
	}

	function appendTracking(formData) {
		var t = getTrackingData();
		for (var k in t) { if (t[k]) formData.append(k, t[k]); }
	}

	/* ── Hero Gallery Carousel ── */
	var galTrack = document.getElementById('lp-gallery-track');
	var galDots  = document.querySelectorAll('#lp-gal-dots .lp-gallery__dot');
	if (galTrack && galTrack.children.length > 1) {
		var gCur = 0;
		var gTotal = galTrack.children.length;
		function galGo(i) {
			gCur = ((i % gTotal) + gTotal) % gTotal;
			galTrack.style.transform = 'translateX(-' + (gCur * 100) + '%)';
			galDots.forEach(function(d, idx) { d.classList.toggle('active', idx === gCur); });
		}
		galDots.forEach(function(d) {
			d.addEventListener('click', function() { galGo(parseInt(d.dataset.index)); });
		});
		/* Auto-scroll every 4 seconds */
		var galAutoTimer = setInterval(function() { galGo(gCur + 1); }, 4000);
		/* Pause auto-scroll on touch, resume after */
		galTrack.addEventListener('touchstart', function() { clearInterval(galAutoTimer); }, {passive: true});
		galTrack.addEventListener('touchend', function(e) {
			var diff = gStartX - e.changedTouches[0].clientX;
			if (Math.abs(diff) > 40) galGo(diff > 0 ? gCur + 1 : gCur - 1);
			galAutoTimer = setInterval(function() { galGo(gCur + 1); }, 4000);
		});
		/* Touch/swipe support */
		var gStartX = 0;
		galTrack.addEventListener('touchstart', function(e) { gStartX = e.touches[0].clientX; }, {passive: true});
	}

	/* ── Generic Form Submit ── */
	function submitForm(formEl, msgEl, fallbackCta) {
		formEl.addEventListener('submit', function(e) {
			e.preventDefault();
			var name  = formEl.querySelector('[name="name"]').value.trim();
			var phone = formEl.querySelector('[name="phone"]').value.replace(/\D/g, '');
			if (phone.length < 10) {
				formEl.querySelector('[name="phone"]').style.borderColor = '#ef4444';
				return;
			}
			var btn = formEl.querySelector('button[type="submit"]');
			btn.disabled = true;
			btn.textContent = 'Submitting...';

			var emailEl = formEl.querySelector('[name="email"]');
			var email = emailEl ? emailEl.value.trim() : '';

			var data = new FormData();
			data.append('action', 'tp_sidebar_lead');
			data.append('nonce', nonce);
			data.append('name', name);
			data.append('phone', phone);
			data.append('email', email);
			data.append('project_name', projectName);
			data.append('page_url', pageUrl);
			data.append('lead_source', leadSource);
			appendTracking(data);

			var tyRedirect = thankYouUrl + '?name=' + encodeURIComponent(name) + '&project=' + encodeURIComponent(projectName);
			fetch(ajaxUrl, { method: 'POST', body: data })
				.then(function(r) { return r.json(); })
				.then(function() {
					window.location.href = tyRedirect;
				})
				.catch(function() {
					/* Redirect anyway — lead is likely saved */
					window.location.href = tyRedirect;
				});
		});
	}

	/* Inline lead form */
	var heroForm = document.getElementById('lp-lead-form');
	var heroMsg  = document.getElementById('lp-form-msg');
	if (heroForm) submitForm(heroForm, heroMsg, <?php echo wp_json_encode( $form_cta ); ?>);

	/* Site visit form */
	var svForm = document.getElementById('lp-sv-form');
	var svMsg  = document.getElementById('lp-sv-msg');
	if (svForm) submitForm(svForm, svMsg, 'Book Free Visit Now!');

	/* ── Popup Modal ── */
	var modal      = document.getElementById('lp-modal');
	var modalTitle  = document.getElementById('lp-modal-title');
	var modalCta    = document.getElementById('lp-modal-cta');
	var modalLabel  = document.getElementById('lp-modal-form-label');
	var modalForm   = document.getElementById('lp-modal-form');
	var modalMsg    = document.getElementById('lp-modal-msg');
	var modalClose  = document.getElementById('lp-modal-close');
	var modalBg     = document.getElementById('lp-modal-backdrop');

	function openModal(title, cta) {
		if (modalLabel) modalLabel.textContent = title;
		modalCta.textContent = cta;
		modalForm.style.display = '';
		modalMsg.style.display = 'none';
		modalForm.reset();
		modalCta.disabled = false;
		modal.classList.add('active');
		document.body.style.overflow = 'hidden';
	}

	function closeModal() {
		modal.classList.remove('active');
		document.body.style.overflow = '';
	}

	/* Bind all modal triggers */
	document.querySelectorAll('.js-lp-modal').forEach(function(btn) {
		btn.addEventListener('click', function(e) {
			e.preventDefault();
			openModal(btn.dataset.title || 'Get Details', btn.dataset.cta || 'Submit');
		});
	});

	if (modalClose) modalClose.addEventListener('click', closeModal);
	if (modalBg) modalBg.addEventListener('click', closeModal);
	document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

	/* Modal form submit */
	if (modalForm) submitForm(modalForm, modalMsg, 'Submit');

	/* ── Amenities mobile carousel dots ── */
	var amCarousel = document.getElementById('amenities-carousel');
	if (amCarousel) {
		var amTrack = amCarousel.querySelector('.lp-amenities-carousel__track');
		var amDots = amCarousel.querySelectorAll('.lp-amenities-carousel__dot');
		amDots.forEach(function(dot) {
			dot.addEventListener('click', function() {
				var idx = parseInt(dot.dataset.index);
				var slide = amTrack.children[idx];
				if (slide) amTrack.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
			});
		});
		amTrack.addEventListener('scroll', function() {
			var scrollLeft = amTrack.scrollLeft;
			var w = amTrack.offsetWidth;
			var active = Math.round(scrollLeft / w);
			amDots.forEach(function(d, i) { d.classList.toggle('active', i === active); });
		}, {passive: true});
		/* Auto-scroll every 3 seconds */
		var amTotal = amTrack.children.length;
		var amCur = 0;
		var amAutoTimer = setInterval(function() {
			amCur = (amCur + 1) % amTotal;
			var slide = amTrack.children[amCur];
			if (slide) amTrack.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
		}, 3000);
		/* Pause on touch, resume after */
		amTrack.addEventListener('touchstart', function() { clearInterval(amAutoTimer); }, {passive: true});
		amTrack.addEventListener('touchend', function() {
			amAutoTimer = setInterval(function() {
				var w = amTrack.offsetWidth;
				amCur = Math.round(amTrack.scrollLeft / w);
				amCur = (amCur + 1) % amTotal;
				var slide = amTrack.children[amCur];
				if (slide) amTrack.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
			}, 3000);
		}, {passive: true});
	}

	/* ── Mobile menu ── */
	var lpNav = document.getElementById('lp-nav');
	var lpHamburger = document.getElementById('lp-hamburger');
	if (lpNav && lpHamburger) {
		lpHamburger.addEventListener('click', function() {
			var isOpen = lpNav.classList.toggle('open');
			lpHamburger.setAttribute('aria-expanded', isOpen);
			lpHamburger.innerHTML = isOpen
				? '<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M6 18L18 6"/></svg>'
				: '<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>';
		});
		lpNav.querySelectorAll('a').forEach(function(a) {
			a.addEventListener('click', function() {
				lpNav.classList.remove('open');
				lpHamburger.setAttribute('aria-expanded', 'false');
				lpHamburger.innerHTML = '<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>';
			});
		});
	}

	/* ── Smooth scroll with header offset ── */
	document.querySelectorAll('a[href^="#"]').forEach(function(a) {
		a.addEventListener('click', function(e) {
			var target = document.querySelector(a.getAttribute('href'));
			if (target) {
				e.preventDefault();
				var headerH = document.querySelector('.lp-header') ? document.querySelector('.lp-header').offsetHeight : 0;
				var y = target.getBoundingClientRect().top + window.pageYOffset - headerH - 16;
				window.scrollTo({ top: y, behavior: 'smooth' });
			}
		});
	});

	/* ── Header scroll shadow ── */
	var header = document.querySelector('.lp-header');
	if (header) {
		var lastScroll = 0;
		window.addEventListener('scroll', function() {
			var st = window.pageYOffset || document.documentElement.scrollTop;
			header.classList.toggle('scrolled', st > 20);
			lastScroll = st;
		}, {passive: true});
	}

	/* ── Scroll Reveal ── */
	if (window.innerWidth > 767 && 'IntersectionObserver' in window) {
		var revealEls = document.querySelectorAll('.lp-reveal');
		var revealObs = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('revealed');
					revealObs.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
		revealEls.forEach(function(el) { revealObs.observe(el); });
	}
})();
</script>
<script>
/* Override chatbot config with LP data */
if (typeof tpChatbot !== 'undefined') {
	tpChatbot.page_title = <?php echo wp_json_encode( $project_name ); ?>;
<?php
	$lp_configs = array();
	if ( ! empty( $pricing_configs ) ) {
		foreach ( $pricing_configs as $pc ) {
			if ( ! empty( $pc['config'] ) ) {
				$lp_configs[] = $pc['config'];
			}
		}
		$lp_configs = array_values( array_unique( $lp_configs ) );
	}
	if ( ! empty( $lp_configs ) ) : ?>
	tpChatbot.configs = <?php echo wp_json_encode( $lp_configs ); ?>;
<?php endif; ?>
}
</script>
<?php get_template_part( 'template-parts/chatbot' ); ?>
<style>
/* Push chatbot above LP sticky bar on mobile */
@media (max-width: 767px) {
	.tp-cb-trigger { bottom: 84px !important; }
	.tp-cb-panel { bottom: 0 !important; }
}
</style>
<?php wp_footer(); ?>
</body>
</html>
