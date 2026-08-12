<?php
/**
 * FAQ Section
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id   = $args['post_id'] ?? get_the_ID();
$title     = get_the_title();
$location  = tp_get_location_term( $post_id );
$loc_name  = $location ? $location->name : 'Navi Mumbai';
$rera      = tp_get_meta( $post_id, 'rera_number' );
$price_min = intval( tp_get_meta( $post_id, 'price_display_min' ) );
$stage     = tp_get_meta( $post_id, 'construction_stage' );
$possession = tp_get_meta( $post_id, 'expected_possession' );
$configs   = tp_get_meta( $post_id, 'available_configs_text' );

// Auto-generated FAQs from project data.
$faqs = array();

if ( $rera ) {
	$faqs[] = array(
		'q' => "Is {$title} RERA registered?",
		'a' => "Yes, {$title} is registered under MahaRERA with registration number {$rera}. You can verify this on the MahaRERA website.",
	);
}

if ( $price_min ) {
	$faqs[] = array(
		'q' => "What is the price of apartments in {$title}?",
		'a' => "Prices at {$title} start from " . tp_format_price( $price_min ) . ". Contact us for the latest offers and payment plans.",
	);
}

if ( $configs ) {
	$faqs[] = array(
		'q' => "What configurations are available at {$title}?",
		'a' => "{$title} offers {$configs} configurations. Contact us for detailed floor plans and availability.",
	);
}

if ( $stage ) {
	$faqs[] = array(
		'q' => "What is the construction status of {$title}?",
		'a' => "{$title} is currently '{$stage}'." . ( $possession ? " Expected possession is {$possession}." : '' ),
	);
}

$faqs[] = array(
	'q' => "Where is {$title} located?",
	'a' => "{$title} is located in {$loc_name}, Navi Mumbai. The project enjoys excellent connectivity to major roads, railway stations, and upcoming infrastructure.",
);

$faqs[] = array(
	'q' => "How to book a site visit for {$title}?",
	'a' => "You can book a free site visit by clicking the 'Book Site Visit' button on this page or contacting us on WhatsApp. We offer free cab pickup for site visits.",
);

if ( empty( $faqs ) ) return;
?>

<section class="tp-section" id="faq">
	<h2>FAQ about <?php echo esc_html( $title ); ?><?php echo $loc_name ? ', ' . esc_html( $loc_name ) : ''; ?></h2>

	<div class="tp-faq">
		<?php foreach ( $faqs as $faq ) : ?>
			<details>
				<summary>
					<?php echo esc_html( $faq['q'] ); ?>
					<svg class="chevron" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
				</summary>
				<div class="answer"><?php echo esc_html( $faq['a'] ); ?></div>
			</details>
		<?php endforeach; ?>
	</div>
</section>
