<?php
/**
 * Floor Plans Section
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id   = $args['post_id'] ?? get_the_ID();
$configs_text = tp_get_meta( $post_id, 'available_configs_text' );
$price_min = intval( tp_get_meta( $post_id, 'price_display_min' ) );

// Get configs from DB.
global $wpdb;
$table = $wpdb->prefix . 'tp_project_configurations';
$configs = array();
if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table ) {
	$configs = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE project_id = %d ORDER BY price_min ASC",
		$post_id
	) );
}

// Fallback: parse configs_text into simple cards.
if ( empty( $configs ) && ! $configs_text ) {
	return;
}
?>

<section class="tp-section" id="floor-plans">
	<h2>Floor Plans</h2>

	<div class="tp-floor-plans-grid">
		<?php if ( ! empty( $configs ) ) : ?>
			<?php foreach ( $configs as $c ) : ?>
				<div class="tp-floor-card">
					<div class="tp-floor-card__img">
						<svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="var(--gray-300)" stroke-width="1.5">
							<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
						</svg>
					</div>
					<div class="tp-floor-card__body">
						<div class="tp-floor-card__type"><?php echo esc_html( $c->configuration ); ?></div>
						<div class="tp-floor-card__area"><?php echo esc_html( $c->carpet_area_min ); ?> sq.ft.</div>
						<div class="tp-floor-card__price"><?php echo esc_html( tp_format_price( $c->price_min ) ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php elseif ( $configs_text ) : ?>
			<?php
			$types = array_map( 'trim', explode( ',', $configs_text ) );
			foreach ( $types as $type ) :
			?>
				<div class="tp-floor-card">
					<div class="tp-floor-card__img">
						<svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="var(--gray-300)" stroke-width="1.5">
							<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
						</svg>
					</div>
					<div class="tp-floor-card__body">
						<div class="tp-floor-card__type"><?php echo esc_html( $type ); ?></div>
						<div class="tp-floor-card__area">Floor plan available</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<div class="tp-floor-cta">
		<div>
			<h3>Want detailed floor plans for <?php the_title(); ?>?</h3>
			<p>Get high-resolution floor plans with dimensions, vastu direction & pricing</p>
		</div>
		<a href="#" class="tp-btn tp-btn--white">Get Floor Plans Free</a>
	</div>
</section>
