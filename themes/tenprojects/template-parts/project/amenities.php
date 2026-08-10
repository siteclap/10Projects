<?php
/**
 * Amenities Section
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id = $args['post_id'] ?? get_the_ID();

// Get amenities from taxonomy terms.
$terms = wp_get_object_terms( $post_id, 'tp_amenity' );
$amenities = array();
if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
	foreach ( $terms as $t ) {
		$amenities[] = $t->name;
	}
}

// Fallback to highlights JSON.
if ( empty( $amenities ) ) {
	$amenities = tp_parse_json_meta( $post_id, 'highlights' );
}

if ( empty( $amenities ) ) return;

$show_initial = 12;
$total = count( $amenities );
?>

<section class="tp-section" id="amenities">
	<h2>Amenities</h2>

	<div class="tp-amenities" id="amenityList">
		<?php foreach ( $amenities as $i => $amenity ) : ?>
			<span class="tp-amenity-pill<?php echo $i >= $show_initial ? ' hidden' : ''; ?>"
				<?php echo $i >= $show_initial ? 'data-hidden-amenity' : ''; ?>>
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
				<?php echo esc_html( $amenity ); ?>
			</span>
		<?php endforeach; ?>
	</div>

	<?php if ( $total > $show_initial ) : ?>
		<button class="tp-show-more" onclick="toggleAmenities(this)">
			See All <?php echo $total; ?> Amenities
			<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
		</button>
	<?php endif; ?>
</section>
