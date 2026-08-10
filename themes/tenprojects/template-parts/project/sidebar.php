<?php
/**
 * Sidebar — Price, CTA, Advisor, Trust Shield
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id   = $args['post_id'] ?? get_the_ID();
$price_min = $args['price_min'] ?? intval( tp_get_meta( $post_id, 'price_display_min' ) );
$price_max = $args['price_max'] ?? intval( tp_get_meta( $post_id, 'price_display_max' ) );
$phone     = tp_get_meta( $post_id, 'phone' );
$wa_number = $phone ? preg_replace( '/[^0-9]/', '', $phone ) : '919999999999';
$wa_msg    = rawurlencode( 'Hi, I am interested in ' . get_the_title( $post_id ) );
?>

<!-- Price & CTA Card -->
<div class="tp-sidebar-card">
	<?php if ( $price_min ) : ?>
		<div class="tp-sidebar-card__price-label">STARTING FROM</div>
		<div class="tp-sidebar-card__price"><?php echo esc_html( tp_format_price( $price_min ) ); ?></div>
		<?php
		$emi = tp_calculate_emi( $price_min * 0.8 );
		if ( $emi > 0 ) :
		?>
			<div class="tp-sidebar-card__emi">EMI from ₹<?php echo number_format( $emi ); ?>/mo</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="tp-sidebar-card__price">Price on Request</div>
	<?php endif; ?>

	<div class="tp-cta-stack">
		<a href="https://wa.me/<?php echo esc_attr( $wa_number ); ?>?text=<?php echo $wa_msg; ?>" class="tp-btn tp-btn--whatsapp tp-btn--block" target="_blank" rel="noopener">
			<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
			WhatsApp
		</a>
		<a href="#" class="tp-btn tp-btn--primary tp-btn--block">Get Best Price</a>
		<a href="#" class="tp-btn tp-btn--outline tp-btn--block">Book Site Visit</a>
	</div>
</div>

<!-- Advisor Card -->
<div class="tp-sidebar-card">
	<div class="tp-advisor">
		<div class="tp-advisor__avatar">PS</div>
		<div>
			<div class="tp-advisor__name">Priya Sharma</div>
			<div class="tp-advisor__role">Property Advisor</div>
		</div>
	</div>
	<div class="tp-advisor-btns">
		<a href="#" class="tp-btn tp-btn--outline">Contact</a>
		<a href="https://wa.me/<?php echo esc_attr( $wa_number ); ?>?text=<?php echo $wa_msg; ?>" class="tp-btn tp-btn--whatsapp" target="_blank" rel="noopener">WhatsApp</a>
	</div>
</div>

<!-- Trust Shield -->
<div class="tp-sidebar-card">
	<div class="tp-trust-grid">
		<div class="tp-trust-item">
			<div class="tp-trust-item__icon" style="background:var(--success-light);color:var(--success);">🛡️</div>
			<div class="tp-trust-item__label">RERA Verified</div>
		</div>
		<div class="tp-trust-item">
			<div class="tp-trust-item__icon" style="background:var(--brand-primary-pale);color:var(--brand-primary);">🔍</div>
			<div class="tp-trust-item__label">Transparent Analysis</div>
		</div>
		<div class="tp-trust-item">
			<div class="tp-trust-item__icon" style="background:var(--brand-primary-pale);color:var(--brand-primary);">🕐</div>
			<div class="tp-trust-item__label">No Spam Guarantee</div>
		</div>
		<div class="tp-trust-item">
			<div class="tp-trust-item__icon" style="background:var(--success-light);color:var(--success);">✅</div>
			<div class="tp-trust-item__label">Verified Data</div>
		</div>
	</div>
</div>
