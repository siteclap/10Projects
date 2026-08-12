<?php
/**
 * Pros & Cons Section
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

	<div class="tp-pros-cons">
		<?php if ( ! empty( $pros ) ) : ?>
			<div class="tp-pros tp-card">
				<h3>
					<span class="icon">✓</span>
					Strengths
				</h3>
				<ul>
					<?php foreach ( $pros as $pro ) : ?>
						<li>
							<span class="icon">✓</span>
							<?php echo esc_html( $pro ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $cons ) ) : ?>
			<div class="tp-cons tp-card">
				<h3>
					<span class="icon">!</span>
					Considerations
				</h3>
				<ul>
					<?php foreach ( $cons as $con ) : ?>
						<li>
							<span class="icon">!</span>
							<?php echo esc_html( $con ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</section>
