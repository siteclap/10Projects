<?php
/**
 * Seed 10 Bandra Under-Construction Projects
 *
 * Usage: curl "https://leadmax.siteclap.com/seed-bandra-projects.php?key=bandra2026seed"
 * Self-deletes after execution.
 */

// Security check.
if ( ! isset( $_GET['key'] ) || $_GET['key'] !== 'bandra2026seed' ) {
	http_response_code( 403 );
	die( 'Forbidden' );
}

// Bootstrap WordPress.
define( 'WP_USE_THEMES', false );
require __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

header( 'Content-Type: text/html; charset=utf-8' );
echo '<h1>Seeding 10 Bandra Projects</h1><pre>';

// ─── Helper: ensure taxonomy term exists ────────────────────────────
function tp_seed_term( $name, $taxonomy, $args = array() ) {
	$exists = term_exists( $name, $taxonomy );
	if ( $exists ) {
		return (int) ( is_array( $exists ) ? $exists['term_id'] : $exists );
	}
	$result = wp_insert_term( $name, $taxonomy, $args );
	if ( is_wp_error( $result ) ) {
		echo "  WARN term '{$name}' in {$taxonomy}: " . $result->get_error_message() . "\n";
		// Try to get existing term by slug fallback.
		$term = get_term_by( 'name', $name, $taxonomy );
		return $term ? $term->term_id : 0;
	}
	echo "  Created term: {$name} (ID {$result['term_id']}) in {$taxonomy}\n";
	return (int) $result['term_id'];
}

// ─── Helper: sideload image ─────────────────────────────────────────
function tp_seed_image( $url, $post_id, $desc ) {
	$id = media_sideload_image( $url, $post_id, $desc, 'id' );
	if ( is_wp_error( $id ) ) {
		echo "  WARN image: " . $id->get_error_message() . "\n";
		return 0;
	}
	return (int) $id;
}

// ═══════════════════════════════════════════════════════════════════
// STEP 1: Create taxonomy terms
// ═══════════════════════════════════════════════════════════════════
echo "\n── Creating Taxonomy Terms ──\n";

// City.
$mumbai_id = tp_seed_term( 'Mumbai', 'tp_city' );

// Location area: Bandra → Bandra East, Bandra West.
$bandra_id   = tp_seed_term( 'Bandra', 'tp_location_area' );
$bandra_e_id = tp_seed_term( 'Bandra East', 'tp_location_area', array( 'parent' => $bandra_id ) );
$bandra_w_id = tp_seed_term( 'Bandra West', 'tp_location_area', array( 'parent' => $bandra_id ) );

// Budget ranges for luxury market.
tp_seed_term( '₹5Cr–₹10Cr', 'tp_budget_range' );
tp_seed_term( '₹10Cr–₹20Cr', 'tp_budget_range' );
tp_seed_term( '₹20Cr+', 'tp_budget_range' );

// Ensure core terms exist.
$core_amenities = array( 'Swimming Pool', 'Gym', 'Clubhouse', 'Garden', 'Jogging Track', 'Kids Play Area', 'Senior Area', 'EV Charging', 'Library', 'Theatre', 'Yoga Room', 'Sports Court', 'Pet Area' );
foreach ( $core_amenities as $a ) { tp_seed_term( $a, 'tp_amenity' ); }

$core_configs = array( '1 BHK', '2 BHK', '3 BHK', '4 BHK', '5 BHK', 'Penthouse', 'Duplex' );
foreach ( $core_configs as $c ) { tp_seed_term( $c, 'tp_configuration' ); }

tp_seed_term( 'Under Construction', 'tp_construction_stage' );
tp_seed_term( 'Buy', 'tp_property_type' );

$possession_years = array( '2026', '2027', '2028', '2029', '2030', '2031+' );
foreach ( $possession_years as $y ) { tp_seed_term( $y, 'tp_possession_year' ); }

$buyer_types = array( 'Upgrade Buyer', 'Investor', 'NRI' );
foreach ( $buyer_types as $b ) { tp_seed_term( $b, 'tp_buyer_type' ); }

// ═══════════════════════════════════════════════════════════════════
// STEP 2: Define all 10 projects
// ═══════════════════════════════════════════════════════════════════

$unsplash = array(
	'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1515263487990-61b07816b324?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1567684014761-b65e2e59b9eb?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=1200&h=800&fit=crop',
);

$projects = array(

	// ── 1. Adani Ten BKC ─────────────────────────────────────────
	array(
		'title'       => 'Adani Ten BKC',
		'slug'        => 'adani-ten-bkc',
		'location'    => 'east',
		'image_idx'   => 0,
		'content'     => '<p>Adani Ten BKC is a landmark residential development by Adani Realty, located in the heart of Bandra-Kurla Complex. Spread across 5 acres with 15 towers, this project offers spacious 3, 4, and 5 BHK apartments with world-class amenities and stunning city views.</p><p>With over 40 premium amenities including a 45,000 sq.ft. double-height lobby, swimming pool, spa, and landscaped gardens, Ten BKC redefines luxury living in Mumbai\'s most coveted business district. RERA registered and bank approved.</p>',
		'short_overview' => 'Luxury 3/4/5 BHK residences in the heart of BKC with 40+ amenities across 15 towers.',
		'developer_name' => 'Adani Realty',
		'developer_about' => 'Adani Realty is a part of the Adani Group, one of India\'s largest conglomerates. Known for developing premium residential and commercial projects across major Indian cities.',
		'rera_number'  => 'P51800004889',
		'price_min'    => 649,
		'price_max'    => 1269,
		'configs_text' => '3 BHK, 4 BHK, 5 BHK',
		'primary_config' => '3 BHK',
		'construction_stage' => 'superstructure',
		'construction_progress' => 65,
		'total_towers' => 15,
		'total_floors' => 29,
		'total_units'  => 722,
		'possession'   => '2027-12-31',
		'lat'          => 19.0590,
		'lng'          => 72.8615,
		'address'      => 'Kala Nagar, BKC, Bandra East, Mumbai 400051',
		'pin'          => '400051',
		'land_parcel'  => '5 Acres',
		'railway_km'   => 2.5,
		'metro_km'     => 1.8,
		'highway_km'   => 3.0,
		'airport_km'   => 18.0,
		'school_km'    => 1.2,
		'hospital_km'  => 2.0,
		'mall_km'      => 1.5,
		'employment_km' => 0.5,
		'highlights'   => array( 'Located in BKC — Mumbai\'s prime business district', '40+ world-class amenities', '45,000 sq.ft. double-height lobby', 'RERA registered & bank approved' ),
		'pros'         => array( 'Unbeatable BKC location with excellent connectivity', 'Reputed Adani Group developer', 'Multiple tower options with varied configurations', 'Premium amenities including spa and wellness centre' ),
		'cons'         => array( 'Premium pricing — starting at ₹6.49 Cr', 'BKC area can be congested during business hours', 'Construction noise from ongoing BKC development' ),
		'best_for'     => array( 'HNI buyers seeking premium address', 'NRIs looking for investment in prime Mumbai', 'Corporate executives working in BKC' ),
		'risks'        => array( 'High ticket size limits resale liquidity', 'Multiple towers may lead to extended construction timeline' ),
		'legal_confidence' => 92,
		'possession_confidence' => 78,
		'bank_approved' => 'SBI, HDFC, ICICI, Axis Bank, Kotak Mahindra',
		'appreciation_score' => 85,
		'parking_info' => '2 covered car parks per unit',
		'water_source' => 'BMC + Borewell backup',
		'power_backup' => '100% DG backup for common areas and apartments',
		'open_space'   => 65.0,
		'configs'      => array( '3 BHK', '4 BHK', '5 BHK' ),
		'amenities'    => array( 'Swimming Pool', 'Gym', 'Clubhouse', 'Garden', 'Jogging Track', 'Kids Play Area', 'EV Charging', 'Yoga Room', 'Sports Court' ),
		'budget_range' => '₹5Cr–₹10Cr',
		'possession_year' => '2027',
		'buyer_types'  => array( 'Upgrade Buyer', 'Investor', 'NRI' ),
		'floor_plans'  => array(
			array( 'label' => '3 BHK Type A', 'area' => '1,050 sq.ft.', 'price' => '₹6.49 Cr', 'image' => '' ),
			array( 'label' => '3 BHK Type B', 'area' => '1,453 sq.ft.', 'price' => '₹8.95 Cr', 'image' => '' ),
			array( 'label' => '4 BHK', 'area' => '2,080 sq.ft.', 'price' => '₹12.69 Cr', 'image' => '' ),
		),
		'phone'        => '+912226500000',
		'email'        => 'sales@adanirealty.com',
		'sales_office' => 'Adani Ten BKC Sales Gallery, BKC, Bandra East, Mumbai 400051',
	),

	// ── 2. Raymond Invictus by GS ───────────────────────────────
	array(
		'title'       => 'Raymond Invictus by GS',
		'slug'        => 'raymond-invictus-bandra',
		'location'    => 'east',
		'image_idx'   => 1,
		'content'     => '<p>Raymond Invictus by GS is a premium residential project by Raymond Realty, located near BKC Road in Kala Nagar, Bandra East. This development features 6 elegantly designed towers offering spacious 3 and 4 BHK apartments with modern amenities.</p><p>Spread across 2.6 acres, the project combines Raymond\'s legacy of craftsmanship with contemporary design. Residents enjoy amenities like a swimming pool, jogging track, sports courts, mini theatre, and beautifully landscaped gardens.</p>',
		'short_overview' => 'Premium 3/4 BHK residences by Raymond Realty near BKC with 6 towers and world-class amenities.',
		'developer_name' => 'Raymond Realty',
		'developer_about' => 'Raymond Realty, a subsidiary of the Raymond Group, brings the same precision and quality the brand is known for in textiles to the world of real estate development.',
		'rera_number'  => 'PR1180002501505',
		'price_min'    => 684,
		'price_max'    => 1198,
		'configs_text' => '3 BHK, 4 BHK',
		'primary_config' => '3 BHK',
		'construction_stage' => 'foundation',
		'construction_progress' => 25,
		'total_towers' => 6,
		'total_floors' => 24,
		'total_units'  => 450,
		'possession'   => '2029-12-31',
		'lat'          => 19.0585,
		'lng'          => 72.8620,
		'address'      => 'Near BKC Road, Kala Nagar, Bandra East, Mumbai 400051',
		'pin'          => '400051',
		'land_parcel'  => '2.6 Acres',
		'railway_km'   => 2.8,
		'metro_km'     => 2.0,
		'highway_km'   => 3.2,
		'airport_km'   => 17.5,
		'school_km'    => 1.5,
		'hospital_km'  => 2.2,
		'mall_km'      => 1.8,
		'employment_km' => 0.8,
		'highlights'   => array( 'By Raymond Group — trusted brand legacy', 'Prime BKC-adjacent location', '6 towers with premium specifications', 'Jogging track, sports courts, mini theatre' ),
		'pros'         => array( 'Raymond\'s brand reputation ensures build quality', 'Excellent connectivity to BKC and Western Express Highway', 'Well-designed 3 & 4 BHK layouts', 'Mini theatre and yoga zone on-site' ),
		'cons'         => array( 'Possession expected only by Dec 2029', 'Early construction stage — foundation level', 'Premium pricing for Bandra East micro-market' ),
		'best_for'     => array( 'Long-term investors seeking brand premium', 'Families wanting spacious homes near BKC', 'Upgrade buyers from western suburbs' ),
		'risks'        => array( 'Early-stage construction increases timeline risk', 'Raymond is relatively new to real estate' ),
		'legal_confidence' => 90,
		'possession_confidence' => 72,
		'bank_approved' => 'SBI, HDFC, ICICI, Bank of Baroda',
		'appreciation_score' => 82,
		'parking_info' => '2 covered car parks per unit',
		'water_source' => 'BMC supply with underground tank',
		'power_backup' => '100% power backup',
		'open_space'   => 60.0,
		'configs'      => array( '3 BHK', '4 BHK' ),
		'amenities'    => array( 'Swimming Pool', 'Gym', 'Clubhouse', 'Jogging Track', 'Kids Play Area', 'Sports Court', 'Theatre', 'Yoga Room' ),
		'budget_range' => '₹5Cr–₹10Cr',
		'possession_year' => '2029',
		'buyer_types'  => array( 'Upgrade Buyer', 'Investor' ),
		'floor_plans'  => array(
			array( 'label' => '3 BHK Type A', 'area' => '1,113 sq.ft.', 'price' => '₹6.84 Cr', 'image' => '' ),
			array( 'label' => '3 BHK Type B', 'area' => '1,397 sq.ft.', 'price' => '₹8.60 Cr', 'image' => '' ),
			array( 'label' => '4 BHK', 'area' => '1,928 sq.ft.', 'price' => '₹11.98 Cr', 'image' => '' ),
		),
		'phone'        => '+912226510000',
		'email'        => 'sales@raymondrealty.com',
		'sales_office' => 'Raymond Invictus Sales Office, Kala Nagar, Bandra East, Mumbai 400051',
	),

	// ── 3. Wadhwa Artek Park ────────────────────────────────────
	array(
		'title'       => 'Wadhwa Artek Park',
		'slug'        => 'wadhwa-artek-park-bandra',
		'location'    => 'east',
		'image_idx'   => 2,
		'content'     => '<p>Wadhwa Artek Park is a boutique luxury tower by The Wadhwa Group, strategically located on Sion-Bandra Link Road near BKC. This single-tower development features 3 BHK, 4 BHK apartments, and exclusive duplex penthouses on floors 21-25.</p><p>With 3 basements and 26 floors, Artek Park offers a refined living experience with premium amenities including a rooftop deck, swimming pool, landscaped gardens, and dedicated meditation areas.</p>',
		'short_overview' => 'Boutique luxury tower with 3/4 BHK and duplex penthouses near BKC by Wadhwa Group.',
		'developer_name' => 'The Wadhwa Group',
		'developer_about' => 'The Wadhwa Group is a leading Mumbai-based real estate developer with over 50 years of experience, known for delivering premium residential and commercial projects across MMR.',
		'rera_number'  => 'PR1180002500855',
		'price_min'    => 850,
		'price_max'    => 1150,
		'configs_text' => '3 BHK, 4 BHK, Duplex Penthouse',
		'primary_config' => '3 BHK',
		'construction_stage' => 'plinth',
		'construction_progress' => 30,
		'total_towers' => 1,
		'total_floors' => 26,
		'total_units'  => 85,
		'possession'   => '2028-12-31',
		'lat'          => 19.0560,
		'lng'          => 72.8590,
		'address'      => 'Sion-Bandra Link Road, Near BKC, Bandra East, Mumbai 400051',
		'pin'          => '400051',
		'land_parcel'  => '0.8 Acres',
		'railway_km'   => 2.2,
		'metro_km'     => 1.5,
		'highway_km'   => 2.8,
		'airport_km'   => 17.0,
		'school_km'    => 1.0,
		'hospital_km'  => 1.8,
		'mall_km'      => 2.0,
		'employment_km' => 1.0,
		'highlights'   => array( 'Boutique single-tower development near BKC', 'Exclusive duplex penthouses on floors 21-25', 'Wadhwa Group — 50+ years legacy', '3-level basement parking' ),
		'pros'         => array( 'Intimate community with only 85 units', 'Duplex penthouse option is rare in this micro-market', 'Excellent BKC proximity via Sion-Bandra Link Road', 'Wadhwa Group track record for timely delivery' ),
		'cons'         => array( 'Ultra-premium pricing above ₹8.5 Cr', 'Single tower — limited unit availability', 'Sion-Bandra Link Road traffic during peak hours' ),
		'best_for'     => array( 'Ultra-luxury buyers seeking exclusivity', 'Penthouse seekers near BKC', 'NRI investors in premium Mumbai real estate' ),
		'risks'        => array( 'Small project size may limit resale market', 'Early construction phase' ),
		'legal_confidence' => 93,
		'possession_confidence' => 80,
		'bank_approved' => 'SBI, HDFC, ICICI, Axis Bank',
		'appreciation_score' => 80,
		'parking_info' => '2-3 covered car parks (3-level basement)',
		'water_source' => 'BMC + Borewell',
		'power_backup' => '100% DG backup',
		'open_space'   => 55.0,
		'configs'      => array( '3 BHK', '4 BHK', 'Duplex' ),
		'amenities'    => array( 'Swimming Pool', 'Gym', 'Clubhouse', 'Garden', 'Kids Play Area', 'Senior Area', 'Yoga Room' ),
		'budget_range' => '₹5Cr–₹10Cr',
		'possession_year' => '2028',
		'buyer_types'  => array( 'Upgrade Buyer', 'NRI' ),
		'floor_plans'  => array(
			array( 'label' => '3 BHK', 'area' => '1,393 sq.ft.', 'price' => '₹8.50 Cr', 'image' => '' ),
			array( 'label' => '4 BHK', 'area' => '2,205 sq.ft.', 'price' => '₹11.50 Cr', 'image' => '' ),
		),
		'phone'        => '+912226520000',
		'email'        => 'sales@thewadhwagroup.com',
		'sales_office' => 'Wadhwa Artek Park Site Office, Sion-Bandra Link Road, Bandra East, Mumbai 400051',
	),

	// ── 4. Paranjape Athena ─────────────────────────────────────
	array(
		'title'       => 'Paranjape Athena',
		'slug'        => 'paranjape-athena-bandra',
		'location'    => 'east',
		'image_idx'   => 3,
		'content'     => '<p>Paranjape Athena is a thoughtfully designed residential project by Paranjape Schemes, located near Western Express Highway in Bandra East. With 5 buildings and 133 units, this project offers an attractive range of 1, 2, and 3 BHK apartments along with unique Jodi apartment configurations.</p><p>Starting at ₹1.68 Cr, it is one of the most accessible entry points into Bandra\'s real estate market. Amenities include a swimming pool, multipurpose turf, sky gazing deck, and reflexology pathways.</p>',
		'short_overview' => 'Affordable luxury 1/2/3 BHK in Bandra East with Jodi apartment options starting at ₹1.68 Cr.',
		'developer_name' => 'Paranjape Schemes (Construction) Ltd',
		'developer_about' => 'Paranjape Schemes is a Pune-based developer with over 40 years of experience, known for quality construction and innovative apartment layouts across Maharashtra.',
		'rera_number'  => 'P51800049529',
		'price_min'    => 168,
		'price_max'    => 400,
		'configs_text' => '1 BHK, 2 BHK, 3 BHK, Jodi Apartments',
		'primary_config' => '2 BHK',
		'construction_stage' => 'superstructure',
		'construction_progress' => 55,
		'total_towers' => 5,
		'total_floors' => 14,
		'total_units'  => 133,
		'possession'   => '2027-12-31',
		'lat'          => 19.0620,
		'lng'          => 72.8500,
		'address'      => 'Teachers Colony, Western Express Highway, Bandra East, Mumbai 400051',
		'pin'          => '400051',
		'land_parcel'  => '0.86 Acres',
		'railway_km'   => 1.5,
		'metro_km'     => 1.0,
		'highway_km'   => 0.2,
		'airport_km'   => 16.0,
		'school_km'    => 0.8,
		'hospital_km'  => 1.5,
		'mall_km'      => 2.0,
		'employment_km' => 2.5,
		'highlights'   => array( 'Most affordable entry into Bandra — from ₹1.68 Cr', 'Unique Jodi apartment configurations', 'Sky gazing deck and multipurpose turf', 'Direct Western Express Highway access' ),
		'pros'         => array( 'Competitive pricing for Bandra micro-market', 'Flexible configurations including Jodi apartments', 'Well-connected via WEH and metro', 'Compact project with only 133 units' ),
		'cons'         => array( 'Smaller carpet areas compared to competition', 'Highway-facing units may have noise concerns', 'Limited open space at 0.86 acres' ),
		'best_for'     => array( 'First-time buyers entering Bandra market', 'Young professionals near WEH corridor', 'Small families seeking 1-2 BHK in premium location' ),
		'risks'        => array( 'Small land parcel limits future expansion', 'Highway noise for lower floors' ),
		'legal_confidence' => 90,
		'possession_confidence' => 82,
		'bank_approved' => 'SBI, HDFC, ICICI, Kotak Mahindra, PNB',
		'appreciation_score' => 78,
		'parking_info' => '1 covered car park per unit',
		'water_source' => 'BMC supply',
		'power_backup' => '100% power backup for common areas, partial for flats',
		'open_space'   => 45.0,
		'configs'      => array( '1 BHK', '2 BHK', '3 BHK' ),
		'amenities'    => array( 'Swimming Pool', 'Gym', 'Jogging Track', 'Kids Play Area', 'Senior Area', 'Yoga Room', 'Sports Court' ),
		'budget_range' => '₹1.5Cr–₹2Cr',
		'possession_year' => '2027',
		'buyer_types'  => array( 'Upgrade Buyer', 'Investor' ),
		'floor_plans'  => array(
			array( 'label' => '1 BHK', 'area' => '446 sq.ft.', 'price' => '₹2.06 Cr', 'image' => '' ),
			array( 'label' => '2 BHK', 'area' => '616 sq.ft.', 'price' => '₹2.29 Cr', 'image' => '' ),
			array( 'label' => '3 BHK', 'area' => '897 sq.ft.', 'price' => '₹3.50 Cr', 'image' => '' ),
		),
		'phone'        => '+912226530000',
		'email'        => 'sales@paranjape.in',
		'sales_office' => 'Paranjape Athena Site Office, Teachers Colony, Bandra East, Mumbai 400051',
	),

	// ── 5. Rustomjee Seasons ────────────────────────────────────
	array(
		'title'       => 'Rustomjee Seasons',
		'slug'        => 'rustomjee-seasons-bandra',
		'location'    => 'east',
		'image_idx'   => 4,
		'content'     => '<p>Rustomjee Seasons is a prestigious residential development by Keystone Realtors (Rustomjee) near MIG Club in Kala Nagar, Bandra East. Featuring 6 wings, this project offers meticulously designed 3 and 4 BHK apartments with premium finishes.</p><p>Residents enjoy lifestyle amenities including a tennis court, cycling track, mini theatre, kids\' activity center, and a swimming pool. The project\'s proximity to BKC and excellent road connectivity make it ideal for professionals and families.</p>',
		'short_overview' => 'Premium 3/4 BHK residences by Rustomjee near MIG Club, Bandra East with tennis court and mini theatre.',
		'developer_name' => 'Rustomjee (Keystone Realtors)',
		'developer_about' => 'Rustomjee, developed by Keystone Realtors Pvt Ltd, is a publicly listed developer known for delivering quality residential projects across Mumbai Metropolitan Region.',
		'rera_number'  => 'P51800021028',
		'price_min'    => 560,
		'price_max'    => 773,
		'configs_text' => '3 BHK, 4 BHK',
		'primary_config' => '3 BHK',
		'construction_stage' => 'brickwork',
		'construction_progress' => 70,
		'total_towers' => 6,
		'total_floors' => 20,
		'total_units'  => 350,
		'possession'   => '2027-06-30',
		'lat'          => 19.0575,
		'lng'          => 72.8600,
		'address'      => 'Near MIG Club, Kala Nagar, Bandra East, Mumbai 400051',
		'pin'          => '400051',
		'land_parcel'  => '3.5 Acres',
		'railway_km'   => 2.5,
		'metro_km'     => 2.0,
		'highway_km'   => 2.5,
		'airport_km'   => 17.5,
		'school_km'    => 1.2,
		'hospital_km'  => 2.0,
		'mall_km'      => 1.5,
		'employment_km' => 1.0,
		'highlights'   => array( 'Rustomjee — listed developer with proven track record', 'Tennis court and cycling track on-site', '70% construction completed', 'Walking distance to BKC' ),
		'pros'         => array( 'Advanced construction stage — 70% complete', 'Listed developer with transparent governance', 'Large project with 3.5-acre land parcel', 'Tennis court, mini theatre, and kids\' activity center' ),
		'cons'         => array( 'Starting at ₹5.6 Cr — premium pricing', 'Some wings facing internal roads', 'Limited 4 BHK inventory remaining' ),
		'best_for'     => array( 'Families seeking near-ready homes in Bandra', 'Buyers who prefer listed developers', 'Sports enthusiasts — tennis court on-site' ),
		'risks'        => array( 'Later wings may face delayed delivery', 'Premium valuations in current market cycle' ),
		'legal_confidence' => 95,
		'possession_confidence' => 85,
		'bank_approved' => 'SBI, HDFC, ICICI, Axis Bank, LIC Housing',
		'appreciation_score' => 83,
		'parking_info' => '2 covered car parks per unit',
		'water_source' => 'BMC + underground storage tanks',
		'power_backup' => '100% DG backup',
		'open_space'   => 62.0,
		'configs'      => array( '3 BHK', '4 BHK' ),
		'amenities'    => array( 'Swimming Pool', 'Gym', 'Clubhouse', 'Jogging Track', 'Kids Play Area', 'Senior Area', 'Theatre', 'Sports Court' ),
		'budget_range' => '₹5Cr–₹10Cr',
		'possession_year' => '2027',
		'buyer_types'  => array( 'Upgrade Buyer', 'Investor' ),
		'floor_plans'  => array(
			array( 'label' => '3 BHK Type A', 'area' => '1,004 sq.ft.', 'price' => '₹5.60 Cr', 'image' => '' ),
			array( 'label' => '3 BHK Type B', 'area' => '1,057 sq.ft.', 'price' => '₹6.80 Cr', 'image' => '' ),
			array( 'label' => '4 BHK', 'area' => '1,410 sq.ft.', 'price' => '₹7.73 Cr', 'image' => '' ),
		),
		'phone'        => '+912226540000',
		'email'        => 'sales@rustomjee.com',
		'sales_office' => 'Rustomjee Seasons Experience Centre, Kala Nagar, Bandra East, Mumbai 400051',
	),

	// ── 6. Nine Ramaa Krishna ───────────────────────────────────
	array(
		'title'       => 'Nine Ramaa Krishna',
		'slug'        => 'nine-ramaa-krishna-bandra',
		'location'    => 'east',
		'image_idx'   => 5,
		'content'     => '<p>Nine Ramaa Krishna is a modern residential tower by Nine Dimensions Housing LLP, located on Ram Mandir Road, Bandra East. This 30-floor tower offers well-designed 2 and 3 BHK apartments at competitive prices for the Bandra micro-market.</p><p>With an average price of ₹40,600 per sq.ft. and possession expected by April 2027, this project offers an attractive opportunity for buyers looking to enter the Bandra market without ultra-premium pricing.</p>',
		'short_overview' => 'Modern 2/3 BHK tower on Ram Mandir Road, Bandra East at ₹40,600/sq.ft. — value entry into Bandra.',
		'developer_name' => 'Nine Dimensions Housing LLP',
		'developer_about' => 'Nine Dimensions Housing LLP is a Mumbai-based developer focused on delivering well-designed residential projects in prime western suburb locations.',
		'rera_number'  => 'P51800054095',
		'price_min'    => 208,
		'price_max'    => 350,
		'configs_text' => '2 BHK, 3 BHK',
		'primary_config' => '2 BHK',
		'construction_stage' => 'superstructure',
		'construction_progress' => 50,
		'total_towers' => 1,
		'total_floors' => 30,
		'total_units'  => 120,
		'possession'   => '2027-04-30',
		'lat'          => 19.0640,
		'lng'          => 72.8450,
		'address'      => 'Ram Mandir Road, Bandra East, Mumbai 400051',
		'pin'          => '400051',
		'land_parcel'  => '0.44 Acres',
		'railway_km'   => 1.0,
		'metro_km'     => 1.5,
		'highway_km'   => 1.8,
		'airport_km'   => 16.5,
		'school_km'    => 0.5,
		'hospital_km'  => 1.2,
		'mall_km'      => 1.5,
		'employment_km' => 2.0,
		'highlights'   => array( 'Value pricing at ₹40,600/sq.ft. for Bandra', 'Ram Mandir Road — well-connected location', 'Compact 2/3 BHK layouts', 'Possession by April 2027' ),
		'pros'         => array( 'Most affordable Bandra project — starting ₹2.08 Cr', 'Close to Bandra station and Ram Mandir', 'Good rental demand in this micro-market', 'Compact tower with manageable maintenance' ),
		'cons'         => array( 'Lesser-known developer compared to competition', 'Small land parcel — 0.44 acres', 'Limited amenities compared to premium projects' ),
		'best_for'     => array( 'Budget-conscious Bandra buyers', 'Young professionals near Bandra station', 'Investors seeking rental income' ),
		'risks'        => array( 'Small developer — limited track record', 'Compact site may affect living experience' ),
		'legal_confidence' => 85,
		'possession_confidence' => 75,
		'bank_approved' => 'SBI, HDFC, ICICI',
		'appreciation_score' => 75,
		'parking_info' => '1 covered car park per unit',
		'water_source' => 'BMC supply',
		'power_backup' => 'DG backup for common areas',
		'open_space'   => 40.0,
		'configs'      => array( '2 BHK', '3 BHK' ),
		'amenities'    => array( 'Gym', 'Kids Play Area', 'Garden' ),
		'budget_range' => '₹2Cr–₹3Cr',
		'possession_year' => '2027',
		'buyer_types'  => array( 'Investor', 'Upgrade Buyer' ),
		'floor_plans'  => array(
			array( 'label' => '2 BHK', 'area' => '667 sq.ft.', 'price' => '₹2.08 Cr', 'image' => '' ),
			array( 'label' => '3 BHK', 'area' => '866 sq.ft.', 'price' => '₹3.50 Cr', 'image' => '' ),
		),
		'phone'        => '+912226550000',
		'email'        => 'info@ninedimensions.in',
		'sales_office' => 'Nine Ramaa Krishna Site Office, Ram Mandir Road, Bandra East, Mumbai 400051',
	),

	// ── 7. DLH Signature ────────────────────────────────────────
	array(
		'title'       => 'DLH Signature',
		'slug'        => 'dlh-signature-bandra',
		'location'    => 'west',
		'image_idx'   => 6,
		'content'     => '<p>DLH Signature is an ultra-luxury sea-facing tower by DLH Group, located on Bandra Reclamation Road, Bandra West. This 34-floor tower offers premium 3, 4, and 5 BHK apartments with panoramic Arabian Sea views.</p><p>At an average of ₹63,559 per sq.ft., DLH Signature commands a premium for its unmatched sea-facing location, modern design, and proximity to Bandra\'s vibrant social scene. Possession is expected by December 2026.</p>',
		'short_overview' => 'Ultra-luxury sea-facing 3/4/5 BHK tower on Bandra Reclamation with Arabian Sea views.',
		'developer_name' => 'DLH Group',
		'developer_about' => 'DLH Group is a prominent Mumbai-based real estate developer with decades of experience in delivering residential and commercial projects across the city.',
		'rera_number'  => 'P51800046093',
		'price_min'    => 912,
		'price_max'    => 3078,
		'configs_text' => '3 BHK, 4 BHK, 5 BHK',
		'primary_config' => '3 BHK',
		'construction_stage' => 'finishing',
		'construction_progress' => 85,
		'total_towers' => 1,
		'total_floors' => 34,
		'total_units'  => 194,
		'possession'   => '2026-12-31',
		'lat'          => 19.0540,
		'lng'          => 72.8270,
		'address'      => 'Bandra Reclamation Road, Bandra West, Mumbai 400050',
		'pin'          => '400050',
		'land_parcel'  => '0.41 Acres',
		'railway_km'   => 1.5,
		'metro_km'     => 2.5,
		'highway_km'   => 4.0,
		'airport_km'   => 20.0,
		'school_km'    => 0.8,
		'hospital_km'  => 1.5,
		'mall_km'      => 1.0,
		'employment_km' => 3.0,
		'highlights'   => array( 'Sea-facing tower with Arabian Sea views', '85% construction complete — nearing possession', 'Bandra Reclamation — most prestigious address', '3/4/5 BHK luxury configurations' ),
		'pros'         => array( 'Unobstructed sea views from all apartments', 'Near-ready — 85% construction done', 'Bandra West\'s most prestigious micro-market', 'Walking distance to Bandstand, Carter Road' ),
		'cons'         => array( 'Ultra-premium pricing — 3 BHK starts at ₹9.12 Cr', 'Small land parcel at 0.41 acres', 'Limited parking due to compact site' ),
		'best_for'     => array( 'HNI buyers seeking sea-facing luxury', 'NRIs wanting a Bandra West address', 'End-users who value ocean views' ),
		'risks'        => array( 'Coastal regulation zone proximity', 'Very high per-sq.ft. cost limits appreciation ceiling' ),
		'legal_confidence' => 88,
		'possession_confidence' => 90,
		'bank_approved' => 'SBI, HDFC, ICICI, Axis Bank',
		'appreciation_score' => 75,
		'parking_info' => '1-2 covered car parks',
		'water_source' => 'BMC + Borewell',
		'power_backup' => '100% DG backup',
		'open_space'   => 35.0,
		'configs'      => array( '3 BHK', '4 BHK', '5 BHK' ),
		'amenities'    => array( 'Swimming Pool', 'Gym', 'Kids Play Area', 'Garden', 'EV Charging' ),
		'budget_range' => '₹10Cr–₹20Cr',
		'possession_year' => '2026',
		'buyer_types'  => array( 'NRI', 'Upgrade Buyer' ),
		'floor_plans'  => array(
			array( 'label' => '3 BHK', 'area' => '1,130 sq.ft.', 'price' => '₹9.12 Cr', 'image' => '' ),
			array( 'label' => '4 BHK', 'area' => '2,200 sq.ft.', 'price' => '₹18.00 Cr', 'image' => '' ),
			array( 'label' => '5 BHK', 'area' => '3,110 sq.ft.', 'price' => '₹30.78 Cr', 'image' => '' ),
		),
		'phone'        => '+912226560000',
		'email'        => 'sales@dlhgroup.com',
		'sales_office' => 'DLH Signature Sales Gallery, Bandra Reclamation, Bandra West, Mumbai 400050',
	),

	// ── 8. Hubtown 25 West ──────────────────────────────────────
	array(
		'title'       => 'Hubtown 25 West',
		'slug'        => 'hubtown-25-west-bandra',
		'location'    => 'west',
		'image_idx'   => 7,
		'content'     => '<p>Hubtown 25 West is an exclusive ultra-luxury development by Hubtown Limited near Mount Mary, Bandra West. Spread across a generous 3.75 acres, this project features 3 towers with just 65 units — offering only 4 BHK apartments with sweeping Arabian Sea views.</p><p>With 44+ amenities, a podium-level landscape, and construction at 70% completion, 25 West represents the pinnacle of luxury living in Bandra. Prices range from ₹27.70 Cr to ₹40.32 Cr.</p>',
		'short_overview' => 'Ultra-exclusive 4 BHK residences near Mount Mary with Arabian Sea views — only 65 units across 3 towers.',
		'developer_name' => 'Hubtown Limited',
		'developer_about' => 'Hubtown Limited is a publicly listed Mumbai-based real estate developer specializing in luxury residential and commercial projects in prime Mumbai locations.',
		'rera_number'  => 'P51800028736',
		'price_min'    => 2770,
		'price_max'    => 4032,
		'configs_text' => '4 BHK',
		'primary_config' => '4 BHK',
		'construction_stage' => 'internal_plaster',
		'construction_progress' => 70,
		'total_towers' => 3,
		'total_floors' => 23,
		'total_units'  => 65,
		'possession'   => '2027-12-31',
		'lat'          => 19.0500,
		'lng'          => 72.8300,
		'address'      => 'Mount Mary, Bandra West, Mumbai 400050',
		'pin'          => '400050',
		'land_parcel'  => '3.75 Acres',
		'railway_km'   => 1.8,
		'metro_km'     => 3.0,
		'highway_km'   => 4.5,
		'airport_km'   => 21.0,
		'school_km'    => 0.5,
		'hospital_km'  => 1.5,
		'mall_km'      => 0.8,
		'employment_km' => 3.5,
		'highlights'   => array( 'Only 65 units — ultra-exclusive community', '3.75-acre estate near Mount Mary', '70% construction complete', '44+ luxury amenities with Arabian Sea views' ),
		'pros'         => array( 'Massive 3.75-acre land parcel — rare in Bandra West', 'Ultra-exclusive — just 65 residences', 'Near Mount Mary — Bandra\'s heritage landmark', '70% complete — possession in sight' ),
		'cons'         => array( 'Starting at ₹27.70 Cr — ultra-HNI segment only', 'Only 4 BHK — no smaller configurations', 'Limited resale market due to ultra-premium pricing' ),
		'best_for'     => array( 'Ultra-HNI families seeking Bandra West lifestyle', 'NRIs wanting a legacy home in Mumbai', 'Business families seeking large-format apartments' ),
		'risks'        => array( 'Extremely high ticket size — ₹27+ Cr', 'Niche buyer pool limits resale flexibility' ),
		'legal_confidence' => 90,
		'possession_confidence' => 85,
		'bank_approved' => 'SBI, HDFC, ICICI, Kotak Mahindra',
		'appreciation_score' => 72,
		'parking_info' => '3 covered car parks per unit (podium)',
		'water_source' => 'BMC + dedicated storage',
		'power_backup' => '100% DG backup for all units',
		'open_space'   => 75.0,
		'configs'      => array( '4 BHK' ),
		'amenities'    => array( 'Swimming Pool', 'Gym', 'Clubhouse', 'Garden', 'Jogging Track', 'Kids Play Area', 'Senior Area', 'Yoga Room', 'Sports Court', 'Library', 'Theatre' ),
		'budget_range' => '₹20Cr+',
		'possession_year' => '2027',
		'buyer_types'  => array( 'NRI', 'Upgrade Buyer' ),
		'floor_plans'  => array(
			array( 'label' => '4 BHK Type A', 'area' => '2,217 sq.ft.', 'price' => '₹27.70 Cr', 'image' => '' ),
			array( 'label' => '4 BHK Type B', 'area' => '3,226 sq.ft.', 'price' => '₹40.32 Cr', 'image' => '' ),
		),
		'phone'        => '+912226570000',
		'email'        => 'sales@hubtown.co.in',
		'sales_office' => 'Hubtown 25 West Experience Centre, Mount Mary, Bandra West, Mumbai 400050',
	),

	// ── 9. Hiranandani Bay Heights ──────────────────────────────
	array(
		'title'       => 'Hiranandani Bay Heights',
		'slug'        => 'hiranandani-bay-heights-bandra',
		'location'    => 'west',
		'image_idx'   => 8,
		'content'     => '<p>Hiranandani Bay Heights is a prestigious residential tower by Hiranandani Communities, located on LK Mehta Marg, Bandra West Reclamation. This 12-floor boutique tower features just 63 units — offering 3 BHK, 4 BHK, and exclusive 5 BHK penthouse apartments.</p><p>Known for the Hiranandani signature quality, Bay Heights offers 25+ amenities including a rooftop swimming pool, world-class gymnasium, concierge service, and a rooftop lounge with panoramic sea views. Possession expected December 2030.</p>',
		'short_overview' => 'Boutique Hiranandani tower in Bandra Reclamation — 63 luxury units with rooftop pool and sea views.',
		'developer_name' => 'Hiranandani Communities',
		'developer_about' => 'Hiranandani Communities, part of the Hiranandani Group, is one of India\'s most respected real estate developers, known for creating self-sustained township ecosystems.',
		'rera_number'  => 'PR1180002501983',
		'price_min'    => 900,
		'price_max'    => 3480,
		'configs_text' => '3 BHK, 4 BHK, 5 BHK Penthouse',
		'primary_config' => '3 BHK',
		'construction_stage' => 'foundation',
		'construction_progress' => 20,
		'total_towers' => 1,
		'total_floors' => 12,
		'total_units'  => 63,
		'possession'   => '2030-12-31',
		'lat'          => 19.0530,
		'lng'          => 72.8260,
		'address'      => 'LK Mehta Marg, Reclamation, Bandra West, Mumbai 400050',
		'pin'          => '400050',
		'land_parcel'  => '0.5 Acres',
		'railway_km'   => 1.5,
		'metro_km'     => 2.5,
		'highway_km'   => 4.0,
		'airport_km'   => 20.5,
		'school_km'    => 0.8,
		'hospital_km'  => 1.2,
		'mall_km'      => 1.0,
		'employment_km' => 3.0,
		'highlights'   => array( 'Hiranandani brand — India\'s most trusted developer', 'Rooftop pool and lounge with sea views', 'Only 63 exclusive units', 'Concierge service included' ),
		'pros'         => array( 'Hiranandani build quality and township expertise', 'Rooftop swimming pool with Arabian Sea views', 'Boutique development — just 63 units', 'Concierge and premium lifestyle services' ),
		'cons'         => array( 'Foundation stage — possession Dec 2030', 'Ultra-premium — 5 BHK penthouses at ₹34.80 Cr', 'Long 4-year wait for possession' ),
		'best_for'     => array( 'Hiranandani loyalists seeking Bandra address', 'Penthouse seekers — 5 BHK with sea views', 'NRI buyers with long-term horizon' ),
		'risks'        => array( '4+ year construction timeline', 'Market cycle risk over long gestation period' ),
		'legal_confidence' => 92,
		'possession_confidence' => 68,
		'bank_approved' => 'SBI, HDFC, ICICI, Axis Bank, LIC Housing',
		'appreciation_score' => 80,
		'parking_info' => '2-3 covered car parks per unit',
		'water_source' => 'BMC + Borewell backup',
		'power_backup' => '100% DG backup with inverter',
		'open_space'   => 50.0,
		'configs'      => array( '3 BHK', '4 BHK', '5 BHK', 'Penthouse' ),
		'amenities'    => array( 'Swimming Pool', 'Gym', 'Clubhouse', 'Garden', 'Kids Play Area', 'Theatre', 'Yoga Room', 'Library' ),
		'budget_range' => '₹10Cr–₹20Cr',
		'possession_year' => '2030',
		'buyer_types'  => array( 'NRI', 'Upgrade Buyer', 'Investor' ),
		'floor_plans'  => array(
			array( 'label' => '3 BHK', 'area' => '1,300 sq.ft.', 'price' => '₹9.00 Cr', 'image' => '' ),
			array( 'label' => '4 BHK', 'area' => '2,900 sq.ft.', 'price' => '₹14.40 Cr', 'image' => '' ),
			array( 'label' => '5 BHK Penthouse', 'area' => '4,200 sq.ft.', 'price' => '₹34.80 Cr', 'image' => '' ),
		),
		'phone'        => '+912226580000',
		'email'        => 'sales@hiranandani.com',
		'sales_office' => 'Hiranandani Bay Heights Sales Lounge, LK Mehta Marg, Bandra West, Mumbai 400050',
	),

	// ── 10. Gurukrupa The Marque ─────────────────────────────────
	array(
		'title'       => 'Gurukrupa The Marque',
		'slug'        => 'gurukrupa-the-marque-bandra',
		'location'    => 'west',
		'image_idx'   => 9,
		'content'     => '<p>Gurukrupa The Marque (Codename 100 Reclamation) is a premium residential tower by Gurukrupa Group, located at 100 Reclamation, Bandra West. This 31-floor tower offers well-designed 2, 3, and 4 BHK apartments along with unique Jodi apartment configurations.</p><p>At a launch rate of ₹49,999 per sq.ft., The Marque offers competitive pricing for Bandra West. Amenities include rooftop swimming pool, squash courts, futsal court, simulator, and terrace jogging track. Expected possession by October 2028.</p>',
		'short_overview' => 'Premium 2/3/4 BHK tower at 100 Reclamation, Bandra West with rooftop pool and squash courts.',
		'developer_name' => 'Gurukrupa Group',
		'developer_about' => 'Gurukrupa Group is a Mumbai-based developer with a strong presence in the western suburbs, known for delivering quality residential projects at competitive pricing.',
		'rera_number'  => 'P51800078233',
		'price_min'    => 491,
		'price_max'    => 780,
		'configs_text' => '2 BHK, 3 BHK, 4 BHK, Jodi Apartments',
		'primary_config' => '3 BHK',
		'construction_stage' => 'plinth',
		'construction_progress' => 25,
		'total_towers' => 1,
		'total_floors' => 31,
		'total_units'  => 150,
		'possession'   => '2028-10-31',
		'lat'          => 19.0550,
		'lng'          => 72.8280,
		'address'      => '100 Reclamation, Bandra West, Mumbai 400050',
		'pin'          => '400050',
		'land_parcel'  => '0.5 Acres',
		'railway_km'   => 1.2,
		'metro_km'     => 2.5,
		'highway_km'   => 4.0,
		'airport_km'   => 20.0,
		'school_km'    => 0.6,
		'hospital_km'  => 1.0,
		'mall_km'      => 1.2,
		'employment_km' => 3.0,
		'highlights'   => array( 'Competitive pricing for Bandra West — from ₹4.91 Cr', 'Rooftop pool with city and sea views', 'Squash courts and futsal on-site', 'Jodi apartment configurations available' ),
		'pros'         => array( 'Most affordable Bandra West project — 2 BHK from ₹4.22 Cr', 'Diverse configurations including Jodi apartments', 'Rooftop amenities with panoramic views', 'Reclamation area — well-connected to Bandra station' ),
		'cons'         => array( 'Early construction stage — plinth level', 'Possession 2+ years away (Oct 2028)', 'Compact land parcel at 0.5 acres' ),
		'best_for'     => array( 'Value seekers wanting Bandra West address', 'Young professionals in western suburbs', 'Investors seeking Bandra West appreciation' ),
		'risks'        => array( 'Early-stage construction with 2+ year timeline', 'Reclamation area regulatory risks' ),
		'legal_confidence' => 87,
		'possession_confidence' => 72,
		'bank_approved' => 'SBI, HDFC, ICICI, Bank of Baroda',
		'appreciation_score' => 78,
		'parking_info' => '1-2 covered car parks (2 basements + podium)',
		'water_source' => 'BMC supply',
		'power_backup' => '100% DG backup',
		'open_space'   => 45.0,
		'configs'      => array( '2 BHK', '3 BHK', '4 BHK' ),
		'amenities'    => array( 'Swimming Pool', 'Gym', 'Clubhouse', 'Jogging Track', 'Kids Play Area', 'Senior Area', 'Sports Court', 'Yoga Room' ),
		'budget_range' => '₹5Cr–₹10Cr',
		'possession_year' => '2028',
		'buyer_types'  => array( 'Investor', 'Upgrade Buyer' ),
		'floor_plans'  => array(
			array( 'label' => '2 BHK', 'area' => '844 sq.ft.', 'price' => '₹4.22 Cr', 'image' => '' ),
			array( 'label' => '3 BHK', 'area' => '1,302 sq.ft.', 'price' => '₹4.91 Cr', 'image' => '' ),
			array( 'label' => '4 BHK', 'area' => '1,817 sq.ft.', 'price' => '₹7.80 Cr', 'image' => '' ),
		),
		'phone'        => '+912226590000',
		'email'        => 'sales@gurukrupagroup.com',
		'sales_office' => 'Gurukrupa The Marque Site Office, 100 Reclamation, Bandra West, Mumbai 400050',
	),

);

// ═══════════════════════════════════════════════════════════════════
// STEP 3: Insert projects
// ═══════════════════════════════════════════════════════════════════
echo "\n── Inserting Projects ──\n\n";

global $wpdb;
$created = 0;
$skipped = 0;

foreach ( $projects as $i => $p ) {
	$num = $i + 1;

	// Check if project already exists.
	$exists = $wpdb->get_var( $wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'tp_project' AND post_status = 'publish' LIMIT 1",
		$p['title']
	) );

	if ( $exists ) {
		echo "[{$num}/10] SKIP — '{$p['title']}' already exists (ID {$exists})\n";
		$skipped++;
		continue;
	}

	// Insert post.
	$post_id = wp_insert_post( array(
		'post_type'    => 'tp_project',
		'post_title'   => $p['title'],
		'post_name'    => $p['slug'],
		'post_content' => $p['content'],
		'post_status'  => 'publish',
		'post_author'  => 1,
	), true );

	if ( is_wp_error( $post_id ) ) {
		echo "[{$num}/10] ERROR — '{$p['title']}': " . $post_id->get_error_message() . "\n";
		continue;
	}

	echo "[{$num}/10] Created '{$p['title']}' (ID {$post_id})\n";

	// ── Meta fields ──
	$meta = array(
		'_tp_developer_name'       => $p['developer_name'],
		'_tp_developer_about'      => $p['developer_about'],
		'_tp_short_overview'       => $p['short_overview'],
		'_tp_rera_number'          => $p['rera_number'],
		'_tp_rera_phase'           => 'Phase 1',
		'_tp_status'               => 'active',
		'_tp_verified'             => '1',
		'_tp_launch_date'          => '2024-01-01',
		'_tp_price_display_min'    => $p['price_min'],
		'_tp_price_display_max'    => $p['price_max'],
		'_tp_available_configs_text' => $p['configs_text'],
		'_tp_primary_config'       => $p['primary_config'],
		'_tp_construction_stage'   => $p['construction_stage'],
		'_tp_construction_progress' => $p['construction_progress'],
		'_tp_total_towers'         => $p['total_towers'],
		'_tp_total_floors'         => $p['total_floors'],
		'_tp_total_units'          => $p['total_units'],
		'_tp_promised_possession'  => $p['possession'],
		'_tp_expected_possession'  => $p['possession'],
		'_tp_rera_possession'      => $p['possession'],
		'_tp_latitude'             => $p['lat'],
		'_tp_longitude'            => $p['lng'],
		'_tp_address'              => $p['address'],
		'_tp_address_pin'          => $p['pin'],
		'_tp_project_location'     => 'Bandra, Mumbai',
		'_tp_land_parcel'          => $p['land_parcel'],
		'_tp_floors_display'       => 'G+' . $p['total_floors'] . ' Floors',
		'_tp_railway_distance_km'  => $p['railway_km'],
		'_tp_metro_distance_km'    => $p['metro_km'],
		'_tp_highway_distance_km'  => $p['highway_km'],
		'_tp_airport_distance_km'  => $p['airport_km'],
		'_tp_school_distance_km'   => $p['school_km'],
		'_tp_hospital_distance_km' => $p['hospital_km'],
		'_tp_mall_distance_km'     => $p['mall_km'],
		'_tp_employment_hub_km'    => $p['employment_km'],
		'_tp_highlights'           => wp_json_encode( $p['highlights'] ),
		'_tp_pros'                 => wp_json_encode( $p['pros'] ),
		'_tp_cons'                 => wp_json_encode( $p['cons'] ),
		'_tp_best_for'             => wp_json_encode( $p['best_for'] ),
		'_tp_risks'                => wp_json_encode( $p['risks'] ),
		'_tp_not_for'              => wp_json_encode( array() ),
		'_tp_legal_confidence'     => $p['legal_confidence'],
		'_tp_possession_confidence' => $p['possession_confidence'],
		'_tp_bank_approved'        => $p['bank_approved'],
		'_tp_appreciation_score'   => $p['appreciation_score'],
		'_tp_parking_info'         => $p['parking_info'],
		'_tp_water_source'         => $p['water_source'],
		'_tp_power_backup'         => $p['power_backup'],
		'_tp_open_space_ratio'     => $p['open_space'],
		'_tp_density_rating'       => 'Low',
		'_tp_vacancy_risk'         => 'Low',
		'_tp_phone'                => $p['phone'],
		'_tp_email'                => $p['email'],
		'_tp_sales_office_address' => $p['sales_office'],
		'_tp_sponsored'            => '0',
		'_tp_google_review_rating' => '4.2',
		'_tp_maintenance_estimate' => 15000,
		'_tp_micro_market_price'   => round( $p['price_min'] * 100000 / 1000 ),
		'_tp_rental_yield_pct'     => 2.5,
		'_tp_last_verified'        => date( 'Y-m-d' ),
		'_tp_reviewed_by'          => 'LeadMAAXX Team',
	);

	foreach ( $meta as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}

	// Floor plans.
	update_post_meta( $post_id, '_tp_floor_plans', $p['floor_plans'] );

	// ── Taxonomy terms ──
	$location_term = ( $p['location'] === 'east' ) ? 'Bandra East' : 'Bandra West';
	wp_set_object_terms( $post_id, array( $location_term, 'Bandra' ), 'tp_location_area' );
	wp_set_object_terms( $post_id, 'Mumbai', 'tp_city' );
	wp_set_object_terms( $post_id, 'Buy', 'tp_property_type' );
	wp_set_object_terms( $post_id, $p['configs'], 'tp_configuration' );
	wp_set_object_terms( $post_id, 'Under Construction', 'tp_construction_stage' );
	wp_set_object_terms( $post_id, $p['amenities'], 'tp_amenity' );
	wp_set_object_terms( $post_id, $p['budget_range'], 'tp_budget_range' );
	wp_set_object_terms( $post_id, $p['possession_year'], 'tp_possession_year' );
	wp_set_object_terms( $post_id, $p['buyer_types'], 'tp_buyer_type' );

	echo "  Meta + Taxonomies set\n";

	// ── Featured image ──
	$img_url = $unsplash[ $p['image_idx'] ];
	$att_id  = tp_seed_image( $img_url, $post_id, $p['title'] );
	if ( $att_id ) {
		set_post_thumbnail( $post_id, $att_id );
		echo "  Featured image set (ID {$att_id})\n";
	}

	$created++;
	echo "\n";
}

echo "\n══════════════════════════════\n";
echo "Done! Created: {$created}, Skipped: {$skipped}\n";
echo "══════════════════════════════\n";
echo '</pre>';

// Self-delete.
@unlink( __FILE__ );
