<?php
/**
 * Offers Strip — Compact promotional banner right after gallery
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$offers = $args['offers'] ?? array();
if ( empty( $offers ) ) return;
?>

<div class="tp-offers-strip">
	<div class="tp-offers-strip__label">
		<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
		Offers
	</div>
	<div class="tp-offers-strip__scroll">
		<?php foreach ( $offers as $i => $offer ) : ?>
			<?php if ( $i > 0 ) : ?><span class="tp-offers-strip__dot"></span><?php endif; ?>
			<span class="tp-offers-strip__item"><?php echo esc_html( $offer ); ?></span>
		<?php endforeach; ?>
	</div>
</div>
