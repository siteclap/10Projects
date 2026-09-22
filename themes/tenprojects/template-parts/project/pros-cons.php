<?php
/**
 * Pros & Cons Section — Polished
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$pros = $args['pros'] ?? array();
$cons = $args['cons'] ?? array();

if ( empty( $pros ) && empty( $cons ) ) return;
?>

<section class="tp-section" id="pros-cons">
	<h2>Pros & Cons of <?php the_title(); ?></h2>

	<div class="tp-pc">
		<?php if ( ! empty( $pros ) ) : ?>
			<div class="tp-pc__card tp-pc__card--pro">
				<div class="tp-pc__header">
					<div class="tp-pc__icon">
						<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
					</div>
					<span class="tp-pc__title">Strengths</span>
					<span class="tp-pc__count"><?php echo count( $pros ); ?></span>
				</div>
				<ul class="tp-pc__list">
					<?php foreach ( $pros as $pro ) : ?>
						<li class="tp-pc__item">
							<svg class="tp-pc__check" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
							<span><?php echo esc_html( $pro ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $cons ) ) : ?>
			<div class="tp-pc__card tp-pc__card--con">
				<div class="tp-pc__header">
					<div class="tp-pc__icon">
						<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 9v2m0 4h.01"/><circle cx="12" cy="12" r="10"/></svg>
					</div>
					<span class="tp-pc__title">Considerations</span>
					<span class="tp-pc__count"><?php echo count( $cons ); ?></span>
				</div>
				<ul class="tp-pc__list">
					<?php foreach ( $cons as $con ) : ?>
						<li class="tp-pc__item">
							<svg class="tp-pc__check" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01"/><circle cx="12" cy="12" r="10"/></svg>
							<span><?php echo esc_html( $con ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</section>
