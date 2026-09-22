<?php
/**
 * Price & Configuration Section — Table + Price Breakup + EMI Calculator
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id   = $args['post_id'] ?? get_the_ID();
$price_min = floatval( tp_get_meta( $post_id, 'price_display_min' ) );
$location  = tp_get_location_term( $post_id );
$loc_name  = $location ? $location->name : '';

// Try to get configurations from the custom table.
global $wpdb;
$table = $wpdb->prefix . 'tp_project_configurations';
$configs = array();

if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table ) {
	$configs = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE project_id = %d ORDER BY price_min ASC",
		$post_id
	) );
}

// No configs from DB — skip this section entirely.
if ( empty( $configs ) ) {
	return;
}
?>

<section class="tp-section" id="price">
	<?php if ( ! empty( $configs ) ) : ?>
		<h2>Price & Configuration of <?php the_title(); ?><?php echo $loc_name ? ', ' . esc_html( $loc_name ) : ''; ?></h2>
		<!-- Configuration Cards -->
		<div class="tp-config-cards">
			<?php foreach ( $configs as $c ) :
				$status = $c->inventory_status ?? 'Available';
				$is_sold = $status === 'Sold Out';
			?>
				<div class="tp-config-card<?php echo $is_sold ? ' tp-config-card--sold' : ''; ?>">
					<div class="tp-config-card__header">
						<div class="tp-config-card__type"><?php echo esc_html( $c->configuration ); ?></div>
						<span class="tp-config-card__status <?php echo $is_sold ? 'tp-config-card__status--sold' : 'tp-config-card__status--available'; ?>">
							<?php echo esc_html( $status ); ?>
						</span>
					</div>
					<div class="tp-config-card__details">
						<div class="tp-config-card__detail">
							<span class="label">Carpet Area</span>
							<span class="value"><?php echo esc_html( $c->carpet_area_min ); ?><?php echo $c->carpet_area_max && $c->carpet_area_max != $c->carpet_area_min ? ' – ' . esc_html( $c->carpet_area_max ) : ''; ?> sq.ft.</span>
						</div>
						<div class="tp-config-card__detail">
							<span class="label">Price</span>
							<span class="value price"><?php echo esc_html( tp_format_price_range( $c->price_min / 100, $c->price_max / 100 ) ); ?></span>
						</div>
						<?php
						$config_emi = tp_calculate_emi( $c->price_min / 100 * 0.8 );
						if ( $config_emi > 0 ) :
						?>
						<div class="tp-config-card__detail">
							<span class="label">EMI (approx)</span>
							<span class="value">~₹<?php echo number_format( $config_emi ); ?>/mo</span>
						</div>
						<?php endif; ?>
					</div>
					<?php if ( ! $is_sold ) : ?>
						<a href="#" class="tp-config-card__cta">Get Price Breakup</a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

	<?php endif; ?>
</section>
