<?php
/**
 * Structured Data — JSON-LD output for all page types.
 *
 * Generates schema.org structured data:
 *  - Organization (homepage + developer pages)
 *  - RealEstateListing + AggregateOffer (project pages)
 *  - FAQPage (project pages, auto-generated from project data)
 *  - BreadcrumbList (all pages)
 *  - Place (location pages)
 *
 * @package TenProjects
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output all structured data as JSON-LD in wp_footer.
 */
function tp_structured_data_output() {
	$schemas = array();

	// BreadcrumbList — always present.
	$breadcrumbs = tp_get_breadcrumbs();
	if ( ! empty( $breadcrumbs ) ) {
		$schemas[] = tp_schema_breadcrumb_list( $breadcrumbs );
	}

	// Homepage: Organization.
	if ( is_front_page() ) {
		$schemas[] = tp_schema_organization_site();
	}

	// Single project: RealEstateListing + FAQPage.
	if ( is_singular( 'tp_project' ) ) {
		$schemas[] = tp_schema_real_estate_listing( get_the_ID() );
		$faq       = tp_schema_faq_page( get_the_ID() );
		if ( $faq ) {
			$schemas[] = $faq;
		}
	}

	// Single developer: Organization for the developer.
	if ( is_singular( 'tp_developer' ) ) {
		$schemas[] = tp_schema_developer_organization( get_the_ID() );
	}

	// Single location: Place.
	if ( is_singular( 'tp_location' ) ) {
		$schemas[] = tp_schema_place( get_the_ID() );
	}

	// Render each schema in its own script tag for clarity.
	foreach ( $schemas as $schema ) {
		if ( empty( $schema ) ) {
			continue;
		}
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n" . '</script>' . "\n";
	}
}
add_action( 'wp_footer', 'tp_structured_data_output', 99 );

/**
 * ---------------------------------------------------------------------------
 * BreadcrumbList schema
 * ---------------------------------------------------------------------------
 */
function tp_schema_breadcrumb_list( array $items ) {
	$list_items = array();
	foreach ( $items as $i => $item ) {
		$entry = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $item['name'],
		);
		if ( ! empty( $item['url'] ) ) {
			$entry['item'] = $item['url'];
		}
		$list_items[] = $entry;
	}

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $list_items,
	);
}

/**
 * ---------------------------------------------------------------------------
 * Organization schema (site-wide, homepage)
 * ---------------------------------------------------------------------------
 */
function tp_schema_organization_site() {
	$logo_url = TENPROJECTS_THEME_URI . '/assets/images/logo.svg';

	return array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Organization',
		'name'        => '10Projects',
		'alternateName' => '10Projects.com',
		'url'         => home_url( '/' ),
		'logo'        => $logo_url,
		'description' => 'AI-powered real estate discovery platform. We find the 10 best-fit projects for every buyer across 20 scoring categories.',
		'sameAs'      => array_filter( array(
			get_option( 'tp_social_instagram', '' ),
			get_option( 'tp_social_linkedin', '' ),
			get_option( 'tp_social_youtube', '' ),
			get_option( 'tp_social_twitter', '' ),
		) ),
		'contactPoint' => array(
			'@type'             => 'ContactPoint',
			'contactType'       => 'customer service',
			'availableLanguage' => array( 'English', 'Hindi' ),
		),
		'areaServed'  => array(
			'@type' => 'City',
			'name'  => 'Navi Mumbai',
		),
	);
}

/**
 * ---------------------------------------------------------------------------
 * RealEstateListing schema (project pages)
 * ---------------------------------------------------------------------------
 */
function tp_schema_real_estate_listing( $post_id ) {
	$title     = get_the_title( $post_id );
	$permalink = get_permalink( $post_id );
	$content   = wp_strip_all_tags( get_the_content( null, false, $post_id ) );
	$content   = wp_trim_words( $content, 50, '...' );

	// Location.
	$locations = wp_get_post_terms( $post_id, 'tp_location_area', array( 'fields' => 'names' ) );
	$location  = is_array( $locations ) && ! empty( $locations ) ? $locations[0] : 'Navi Mumbai';

	// Coordinates.
	$lat = get_post_meta( $post_id, '_tp_latitude', true );
	$lng = get_post_meta( $post_id, '_tp_longitude', true );

	// Prices from configurations.
	$price_min = (int) get_post_meta( $post_id, '_tp_price_display_min', true );
	$price_max = (int) get_post_meta( $post_id, '_tp_price_display_max', true );

	// Developer.
	$developer_name = tp_seo_get_developer_name( $post_id );

	// RERA.
	$rera = get_post_meta( $post_id, '_tp_rera_number', true );

	// Possession.
	$possession = get_post_meta( $post_id, '_tp_expected_possession', true );

	// Image.
	$image_url = '';
	if ( has_post_thumbnail( $post_id ) ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'large' );
		if ( $img ) {
			$image_url = $img[0];
		}
	}

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'RealEstateListing',
		'name'        => $title,
		'url'         => $permalink,
		'description' => $content,
		'datePosted'  => get_the_date( 'c', $post_id ),
		'dateModified' => get_the_modified_date( 'c', $post_id ),
	);

	// Image.
	if ( $image_url ) {
		$schema['image'] = $image_url;
	}

	// Address.
	$schema['address'] = array(
		'@type'           => 'PostalAddress',
		'addressLocality' => $location,
		'addressRegion'   => 'Maharashtra',
		'addressCountry'  => 'IN',
	);

	// Geo coordinates.
	if ( $lat && $lng ) {
		$schema['geo'] = array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) $lat,
			'longitude' => (float) $lng,
		);
	}

	// Offers (AggregateOffer from configurations).
	if ( $price_min > 0 ) {
		$offer = array(
			'@type'         => 'AggregateOffer',
			'priceCurrency' => 'INR',
			'lowPrice'      => $price_min,
			'availability'  => 'https://schema.org/InStock',
		);
		if ( $price_max > 0 && $price_max !== $price_min ) {
			$offer['highPrice'] = $price_max;
		}
		$schema['offers'] = $offer;
	}

	// Author (developer as Organization).
	$schema['author'] = array(
		'@type' => 'Organization',
		'name'  => $developer_name,
	);

	// Additional properties.
	$additional = array();

	if ( $rera ) {
		$additional[] = array(
			'@type' => 'PropertyValue',
			'name'  => 'RERA Number',
			'value' => $rera,
		);
	}

	if ( $possession ) {
		$additional[] = array(
			'@type' => 'PropertyValue',
			'name'  => 'Expected Possession',
			'value' => $possession,
		);
	}

	$stage = get_post_meta( $post_id, '_tp_construction_stage', true );
	if ( $stage ) {
		$additional[] = array(
			'@type' => 'PropertyValue',
			'name'  => 'Construction Stage',
			'value' => tp_construction_stage_label( $stage ),
		);
	}

	if ( ! empty( $additional ) ) {
		$schema['additionalProperty'] = $additional;
	}

	return $schema;
}

/**
 * ---------------------------------------------------------------------------
 * FAQPage schema (auto-generated from project data)
 * ---------------------------------------------------------------------------
 */
function tp_schema_faq_page( $post_id ) {
	$title     = get_the_title( $post_id );
	$location  = tp_seo_get_primary_location();
	$developer = tp_seo_get_developer_name( $post_id );
	$price_min = get_post_meta( $post_id, '_tp_price_display_min', true );
	$price_max = get_post_meta( $post_id, '_tp_price_display_max', true );
	$rera      = get_post_meta( $post_id, '_tp_rera_number', true );
	$possession = get_post_meta( $post_id, '_tp_expected_possession', true );
	$config    = get_post_meta( $post_id, '_tp_primary_config', true );

	$faqs = array();

	// FAQ 1: Price range.
	if ( $price_min ) {
		$price_text = tp_format_price_range( (int) $price_min, (int) $price_max );
		$faqs[] = array(
			'question' => sprintf( 'What is the price range of %s?', $title ),
			'answer'   => sprintf(
				'%s is priced from %s. EMI starts at approximately %s. Prices may vary by configuration and floor.',
				$title,
				$price_text,
				tp_format_emi( (int) $price_min )
			),
		);
	}

	// FAQ 2: RERA registration.
	if ( $rera ) {
		$faqs[] = array(
			'question' => sprintf( 'Is %s RERA registered?', $title ),
			'answer'   => sprintf(
				'Yes, %s is RERA registered with number %s. You can verify this on the MahaRERA website.',
				$title,
				$rera
			),
		);
	}

	// FAQ 3: Possession date.
	if ( $possession ) {
		$label = tp_possession_label( $possession );
		$faqs[] = array(
			'question' => sprintf( 'What is the expected possession date of %s?', $title ),
			'answer'   => sprintf(
				'The expected possession of %s is %s. Construction status and updates are tracked on 10Projects.',
				$title,
				$label
			),
		);
	}

	// FAQ 4: Developer.
	$faqs[] = array(
		'question' => sprintf( 'Who is the developer of %s?', $title ),
		'answer'   => sprintf(
			'%s is developed by %s. You can view their full track record, delivery history, and other projects on 10Projects.',
			$title,
			$developer
		),
	);

	// FAQ 5: Configurations.
	if ( $config ) {
		$faqs[] = array(
			'question' => sprintf( 'What configurations are available in %s?', $title ),
			'answer'   => sprintf(
				'%s offers %s configurations. Visit the project page on 10Projects for floor plans, carpet areas, and pricing per configuration.',
				$title,
				$config
			),
		);
	}

	if ( empty( $faqs ) ) {
		return null;
	}

	$main_entity = array();
	foreach ( $faqs as $faq ) {
		$main_entity[] = array(
			'@type'          => 'Question',
			'name'           => $faq['question'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $faq['answer'],
			),
		);
	}

	return array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $main_entity,
	);
}

/**
 * ---------------------------------------------------------------------------
 * Developer Organization schema
 * ---------------------------------------------------------------------------
 */
function tp_schema_developer_organization( $post_id ) {
	$name      = get_the_title( $post_id );
	$url       = get_permalink( $post_id );
	$content   = wp_strip_all_tags( get_the_content( null, false, $post_id ) );
	$content   = wp_trim_words( $content, 30, '...' );

	$established = get_post_meta( $post_id, '_tp_established_year', true );
	$hq_city     = get_post_meta( $post_id, '_tp_headquarters_city', true );

	$logo_url = '';
	if ( has_post_thumbnail( $post_id ) ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'tp-developer-logo' );
		if ( $img ) {
			$logo_url = $img[0];
		}
	}

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Organization',
		'name'        => $name,
		'url'         => $url,
		'description' => $content,
	);

	if ( $logo_url ) {
		$schema['logo'] = $logo_url;
	}

	if ( $established ) {
		$schema['foundingDate'] = (string) $established;
	}

	if ( $hq_city ) {
		$schema['address'] = array(
			'@type'           => 'PostalAddress',
			'addressLocality' => $hq_city,
			'addressRegion'   => 'Maharashtra',
			'addressCountry'  => 'IN',
		);
	}

	return $schema;
}

/**
 * ---------------------------------------------------------------------------
 * Place schema (location pages)
 * ---------------------------------------------------------------------------
 */
function tp_schema_place( $post_id ) {
	$name = get_the_title( $post_id );
	$url  = get_permalink( $post_id );
	$city = get_post_meta( $post_id, '_tp_city', true ) ?: 'Navi Mumbai';
	$lat  = get_post_meta( $post_id, '_tp_latitude', true );
	$lng  = get_post_meta( $post_id, '_tp_longitude', true );

	$content = wp_strip_all_tags( get_the_content( null, false, $post_id ) );
	$content = wp_trim_words( $content, 30, '...' );

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Place',
		'name'        => $name,
		'url'         => $url,
		'description' => $content,
		'address'     => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => $name,
			'addressRegion'   => 'Maharashtra',
			'addressCountry'  => 'IN',
		),
		'containedInPlace' => array(
			'@type' => 'City',
			'name'  => $city,
		),
	);

	if ( $lat && $lng ) {
		$schema['geo'] = array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) $lat,
			'longitude' => (float) $lng,
		);
	}

	return $schema;
}

/**
 * ---------------------------------------------------------------------------
 * Helper: Build breadcrumb trail from current page hierarchy.
 * ---------------------------------------------------------------------------
 *
 * Returns an array of [ 'name' => '...', 'url' => '...' ] entries.
 * The last item in the array has no URL (current page).
 *
 * @return array Breadcrumb items.
 */
function tp_get_breadcrumbs() {
	$crumbs = array();

	// Home is always first.
	$crumbs[] = array(
		'name' => 'Home',
		'url'  => home_url( '/' ),
	);

	// Single project: Home > Location > Project.
	if ( is_singular( 'tp_project' ) ) {
		$locations = wp_get_post_terms( get_the_ID(), 'tp_location_area' );
		if ( is_array( $locations ) && ! empty( $locations ) ) {
			$loc_term = $locations[0];
			// Try to find the matching tp_location CPT post.
			$location_post = get_posts( array(
				'post_type'      => 'tp_location',
				'posts_per_page' => 1,
				'title'          => $loc_term->name,
				'post_status'    => 'publish',
			) );
			if ( ! empty( $location_post ) ) {
				$crumbs[] = array(
					'name' => $loc_term->name,
					'url'  => get_permalink( $location_post[0]->ID ),
				);
			} else {
				$crumbs[] = array(
					'name' => $loc_term->name,
					'url'  => '',
				);
			}
		}
		$crumbs[] = array(
			'name' => get_the_title(),
			'url'  => '',
		);
		return $crumbs;
	}

	// Single developer: Home > Developers > Developer Name.
	if ( is_singular( 'tp_developer' ) ) {
		$crumbs[] = array(
			'name' => 'Developers',
			'url'  => get_post_type_archive_link( 'tp_developer' ),
		);
		$crumbs[] = array(
			'name' => get_the_title(),
			'url'  => '',
		);
		return $crumbs;
	}

	// Single location: Home > Locations > Location Name.
	if ( is_singular( 'tp_location' ) ) {
		$crumbs[] = array(
			'name' => 'Locations',
			'url'  => get_post_type_archive_link( 'tp_location' ),
		);
		$crumbs[] = array(
			'name' => get_the_title(),
			'url'  => '',
		);
		return $crumbs;
	}

	// Single guide: Home > Guides > Guide Title.
	if ( is_singular( 'tp_guide' ) ) {
		$crumbs[] = array(
			'name' => 'Guides',
			'url'  => get_post_type_archive_link( 'tp_guide' ),
		);
		$crumbs[] = array(
			'name' => get_the_title(),
			'url'  => '',
		);
		return $crumbs;
	}

	// Archives.
	if ( is_post_type_archive( 'tp_project' ) ) {
		$crumbs[] = array( 'name' => 'Projects', 'url' => '' );
		return $crumbs;
	}

	if ( is_post_type_archive( 'tp_location' ) ) {
		$crumbs[] = array( 'name' => 'Locations', 'url' => '' );
		return $crumbs;
	}

	if ( is_post_type_archive( 'tp_developer' ) ) {
		$crumbs[] = array( 'name' => 'Developers', 'url' => '' );
		return $crumbs;
	}

	// Static pages.
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_the_ID() );
		$labels = array(
			'methodology' => 'Methodology',
			'dashboard'   => 'Dashboard',
			'compare'     => 'Compare Projects',
			'start'       => 'AI Assessment',
			'results'     => 'Results',
		);
		$label = $labels[ $slug ] ?? get_the_title();
		$crumbs[] = array( 'name' => $label, 'url' => '' );
		return $crumbs;
	}

	return $crumbs;
}
