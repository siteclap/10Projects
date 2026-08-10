<?php
/**
 * Overview Section
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id     = $args['post_id'] ?? get_the_ID();
$description = get_the_excerpt() ?: tp_get_meta( $post_id, 'short_overview' );
$stage       = tp_get_meta( $post_id, 'construction_stage' );
$possession  = tp_get_meta( $post_id, 'expected_possession' );
$rera        = tp_get_meta( $post_id, 'rera_number' );
$price_min   = intval( tp_get_meta( $post_id, 'price_display_min' ) );
$price_max   = intval( tp_get_meta( $post_id, 'price_display_max' ) );
$configs     = tp_get_meta( $post_id, 'available_configs_text' );
$pros        = tp_parse_json_meta( $post_id, 'pros' );
?>

<section class="tp-section" id="overview">
	<h2>Overview</h2>

	<?php if ( $description ) : ?>
		<div class="tp-card mb-xl">
			<p style="font-size:15px;color:var(--gray-600);line-height:1.7;">
				<?php echo wp_kses_post( $description ); ?>
			</p>

			<div class="tp-stats-grid mt-xl">
				<?php if ( $configs ) : ?>
					<div>
						<div class="tp-stat__label">Configuration</div>
						<div class="tp-stat__value"><?php echo esc_html( $configs ); ?></div>
					</div>
				<?php endif; ?>
				<div>
					<div class="tp-stat__label">Status</div>
					<div class="tp-stat__value"><?php echo esc_html( $stage ?: 'N/A' ); ?><?php echo $possession ? ' / ' . esc_html( $possession ) : ''; ?></div>
				</div>
				<?php if ( $price_min ) : ?>
					<div>
						<div class="tp-stat__label">Average Price</div>
						<div class="tp-stat__value"><?php echo esc_html( tp_format_price_range( $price_min, $price_max ) ); ?></div>
					</div>
				<?php endif; ?>
				<?php if ( $rera ) : ?>
					<div>
						<div class="tp-stat__label">RERA Number</div>
						<div class="tp-stat__value"><?php echo esc_html( $rera ); ?></div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $pros ) ) : ?>
		<div class="tp-card tp-card--accent">
			<h3 class="text-h4 mb-lg">Why Consider <?php the_title(); ?>?</h3>
			<div class="tp-stats-grid">
				<?php foreach ( array_slice( $pros, 0, 6 ) as $pro ) : ?>
					<div style="display:flex;align-items:flex-start;gap:8px;">
						<span style="color:var(--accent);font-size:8px;margin-top:6px;">■</span>
						<span style="font-size:14px;color:var(--gray-700);"><?php echo esc_html( $pro ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
