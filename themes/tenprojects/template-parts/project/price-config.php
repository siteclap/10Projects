<?php
/**
 * Price & Configuration Section
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id   = $args['post_id'] ?? get_the_ID();
$price_min = intval( tp_get_meta( $post_id, 'price_display_min' ) );

// Try to get configurations from the custom table.
global $wpdb;
$table = $wpdb->prefix . 'tp_project_configurations';
$configs = array();

// Check if table exists before querying.
if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table ) {
	$configs = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE project_id = %d ORDER BY price_min ASC",
		$post_id
	) );
}

// Fallback: parse from available_configs_text if no DB configs.
if ( empty( $configs ) ) {
	$configs_text = tp_get_meta( $post_id, 'available_configs_text' );
	$price_max    = intval( tp_get_meta( $post_id, 'price_display_max' ) );

	if ( ! $configs_text && ! $price_min ) {
		return; // Nothing to show.
	}
}
?>

<section class="tp-section" id="price">
	<h2>Price & Configuration</h2>

	<?php if ( ! empty( $configs ) ) : ?>
		<div class="tp-card" style="padding:0;overflow:hidden;">
			<table class="tp-price-table">
				<thead>
					<tr>
						<th>Type</th>
						<th>Carpet Area</th>
						<th>Price Range</th>
						<th>Availability</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $configs as $c ) : ?>
						<tr>
							<td style="font-weight:600;"><?php echo esc_html( $c->configuration ); ?></td>
							<td><?php echo esc_html( $c->carpet_area_min ); ?><?php echo $c->carpet_area_max && $c->carpet_area_max != $c->carpet_area_min ? ' – ' . esc_html( $c->carpet_area_max ) : ''; ?> sq.ft.</td>
							<td style="font-weight:600;"><?php echo esc_html( tp_format_price_range( $c->price_min, $c->price_max ) ); ?></td>
							<td>
								<?php
								$status = $c->inventory_status ?? 'Available';
								$color  = $status === 'Sold Out' ? 'var(--danger)' : 'var(--success)';
								?>
								<span style="color:<?php echo $color; ?>;font-weight:500;"><?php echo esc_html( $status ); ?></span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php elseif ( $price_min ) : ?>
		<div class="tp-card">
			<div class="tp-stats-grid">
				<?php if ( $configs_text ) : ?>
					<div>
						<div class="tp-stat__label">Configurations</div>
						<div class="tp-stat__value"><?php echo esc_html( $configs_text ); ?></div>
					</div>
				<?php endif; ?>
				<div>
					<div class="tp-stat__label">Price Range</div>
					<div class="tp-stat__value"><?php echo esc_html( tp_format_price_range( $price_min, $price_max ) ); ?></div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $price_min > 0 ) : ?>
		<?php
		$emi = tp_calculate_emi( $price_min );
		?>
		<div class="tp-card mt-xl">
			<h3 class="text-h4 mb-md">EMI Calculator</h3>
			<div class="tp-stats-grid">
				<div>
					<div class="tp-stat__label">Loan Amount (80%)</div>
					<div class="tp-stat__value"><?php echo esc_html( tp_format_price( $price_min * 0.8 ) ); ?></div>
				</div>
				<div>
					<div class="tp-stat__label">Interest Rate</div>
					<div class="tp-stat__value">8.5% p.a.</div>
				</div>
				<div>
					<div class="tp-stat__label">Tenure</div>
					<div class="tp-stat__value">20 years</div>
				</div>
				<div>
					<div class="tp-stat__label">Monthly EMI</div>
					<div class="tp-stat__value" style="color:var(--brand-primary);font-size:16px;">
						₹<?php echo number_format( tp_calculate_emi( $price_min * 0.8 ) ); ?>/mo
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</section>
