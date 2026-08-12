<?php
/**
 * Plot Details Section — Area, Dimensions, FSI, Connections
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id                = $args['post_id'] ?? get_the_ID();
$plot_type              = tp_get_meta( $post_id, 'plot_type' );
$plot_area              = intval( tp_get_meta( $post_id, 'plot_area' ) );
$plot_width             = tp_get_meta( $post_id, 'plot_width' );
$plot_depth             = tp_get_meta( $post_id, 'plot_depth' );
$corner_plot            = tp_get_meta( $post_id, 'corner_plot' );
$road_width             = tp_get_meta( $post_id, 'road_width' );
$sides_open             = tp_get_meta( $post_id, 'sides_open' );
$boundary_wall          = tp_get_meta( $post_id, 'boundary_wall' );
$topography             = tp_get_meta( $post_id, 'topography' );
$fsi                    = tp_get_meta( $post_id, 'fsi' );
$permissible_floors     = tp_get_meta( $post_id, 'permissible_floors' );
$water_connection       = tp_get_meta( $post_id, 'water_connection' );
$electricity_connection = tp_get_meta( $post_id, 'electricity_connection' );
$sewage_connection      = tp_get_meta( $post_id, 'sewage_connection' );
$gated_community        = tp_get_meta( $post_id, 'gated_community' );
$price_min              = intval( tp_get_meta( $post_id, 'price_display_min' ) );
$price_per_sqft         = intval( tp_get_meta( $post_id, 'price_per_sqft' ) );

$has_data = $plot_area || $plot_type || $price_min;
if ( ! $has_data ) {
	return;
}
?>

<section class="tp-section" id="plot">
	<h2>Plot Details & Pricing</h2>

	<div class="tp-detail-table">
		<?php if ( $plot_type ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Plot Type</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $plot_type ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $plot_area ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Plot Area</span>
				<span class="tp-detail-table__value"><?php echo number_format( $plot_area ); ?> sqft</span>
			</div>
		<?php endif; ?>
		<?php if ( $plot_width && $plot_depth ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Dimensions</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $plot_width . ' ft × ' . $plot_depth . ' ft' ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $price_min ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Price</span>
				<span class="tp-detail-table__value tp-detail-table__value--highlight"><?php echo esc_html( tp_format_price( $price_min ) ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $price_per_sqft ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Price per sqft</span>
				<span class="tp-detail-table__value">₹<?php echo number_format( $price_per_sqft ); ?>/sqft</span>
			</div>
		<?php endif; ?>
		<?php if ( $corner_plot ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Corner Plot</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $corner_plot ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $road_width ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Road Width</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $road_width ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $sides_open ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Sides Open</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $sides_open ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $topography ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Topography</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $topography ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $boundary_wall ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Boundary Wall</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $boundary_wall ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $fsi ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">FSI / FAR</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $fsi ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $permissible_floors ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Permissible Floors</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $permissible_floors ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $gated_community ) : ?>
			<div class="tp-detail-table__row">
				<span class="tp-detail-table__label">Gated Community</span>
				<span class="tp-detail-table__value"><?php echo esc_html( $gated_community ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $water_connection || $electricity_connection || $sewage_connection ) : ?>
		<h3 style="margin-top:1.5rem;">Utility Connections</h3>
		<div class="tp-utility-grid">
			<?php
			$utilities = array(
				array( 'label' => 'Water', 'value' => $water_connection ),
				array( 'label' => 'Electricity', 'value' => $electricity_connection ),
				array( 'label' => 'Sewage', 'value' => $sewage_connection ),
			);
			foreach ( $utilities as $util ) :
				if ( ! $util['value'] ) continue;
				$is_yes = strtolower( $util['value'] ) === 'yes';
			?>
				<div class="tp-utility-grid__item">
					<span class="tp-utility-grid__icon <?php echo $is_yes ? 'tp-utility-grid__icon--yes' : 'tp-utility-grid__icon--no'; ?>">
						<?php if ( $is_yes ) : ?>
							<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
						<?php else : ?>
							<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
						<?php endif; ?>
					</span>
					<span class="tp-utility-grid__label"><?php echo esc_html( $util['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
