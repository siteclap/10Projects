<?php
/**
 * Similar Projects Section
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id = $args['post_id'] ?? get_the_ID();
$similar = tp_get_similar_projects( $post_id, 4 );

if ( empty( $similar ) ) return;
?>

<section class="tp-section">
	<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--xl);">
		<h2 style="margin-bottom:0;">Similar Projects Nearby</h2>
		<a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>" style="font-size:14px;font-weight:500;">View All →</a>
	</div>

	<div class="tp-similar-grid">
		<?php foreach ( $similar as $p ) :
			$p_id       = $p->ID;
			$p_thumb    = get_the_post_thumbnail_url( $p_id, 'medium' );
			$p_dev      = tp_get_meta( $p_id, 'developer_name' ) ?: '';
			$p_loc      = tp_get_location_term( $p_id );
			$p_loc_name = $p_loc ? $p_loc->name : '';
			$p_min      = intval( tp_get_meta( $p_id, 'price_display_min' ) );
			$p_max      = intval( tp_get_meta( $p_id, 'price_display_max' ) );
			$p_configs  = tp_get_meta( $p_id, 'available_configs_text' );
		?>
			<div class="tp-project-card">
				<div class="tp-project-card__img">
					<?php if ( $p_thumb ) : ?>
						<img src="<?php echo esc_url( $p_thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $p_id ) ); ?>" loading="lazy">
					<?php else : ?>
						<div style="width:100%;height:100%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--gray-400);">
							<svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
						</div>
					<?php endif; ?>
					<?php if ( $p_dev ) : ?>
						<span style="position:absolute;top:8px;left:8px;background:rgba(0,0,0,0.6);color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;"><?php echo esc_html( $p_dev ); ?></span>
					<?php endif; ?>
				</div>
				<div class="tp-project-card__body">
					<div class="tp-project-card__title">
						<a href="<?php echo esc_url( get_permalink( $p_id ) ); ?>"><?php echo esc_html( get_the_title( $p_id ) ); ?></a>
					</div>
					<div class="tp-project-card__location"><?php echo esc_html( $p_loc_name ); ?></div>
					<?php if ( $p_min ) : ?>
						<div class="tp-project-card__price"><?php echo esc_html( tp_format_price_range( $p_min, $p_max ) ); ?></div>
					<?php endif; ?>
					<?php if ( $p_configs ) : ?>
						<div class="tp-project-card__meta"><?php echo esc_html( $p_configs ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
