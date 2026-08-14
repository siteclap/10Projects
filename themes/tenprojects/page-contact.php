<?php
/**
 * Template Name: Contact
 * Slug: contact
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$brand   = get_option( 'tp_brand_name', 'LeadMAAXX' );
$phone   = get_option( 'tp_brand_phone', '' );
$email   = get_option( 'tp_brand_email', '' );
$address = get_option( 'tp_brand_address', '' );
$whatsapp = get_option( 'tp_social_whatsapp', '' );

get_header();
?>

<style>
.tp-page-hero {
	background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
	color: #fff;
	padding: 80px 0 60px;
	text-align: center;
}
.tp-page-hero h1 {
	font-size: 36px;
	font-weight: 700;
	margin-bottom: 12px;
}
.tp-page-hero p {
	font-size: 18px;
	opacity: 0.85;
	max-width: 600px;
	margin: 0 auto;
}
.tp-page-content {
	max-width: 900px;
	margin: 0 auto;
	padding: 48px 24px 80px;
}
.tp-contact-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
	gap: 24px;
	margin-bottom: 48px;
}
.tp-contact-card {
	background: #fff;
	border: 1px solid var(--gray-200);
	border-radius: var(--radius-md);
	padding: 32px;
	text-align: center;
	transition: box-shadow 0.2s;
}
.tp-contact-card:hover {
	box-shadow: var(--shadow-hover);
}
.tp-contact-card .icon {
	width: 56px;
	height: 56px;
	background: var(--brand-primary-pale);
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto 16px;
}
.tp-contact-card .icon svg {
	width: 24px;
	height: 24px;
	color: var(--brand-primary);
}
.tp-contact-card h3 {
	font-size: 18px;
	font-weight: 600;
	color: var(--gray-900);
	margin-bottom: 8px;
}
.tp-contact-card p {
	font-size: 15px;
	color: var(--gray-500);
	line-height: 1.6;
	margin-bottom: 12px;
}
.tp-contact-card a {
	color: var(--brand-primary);
	font-weight: 600;
	font-size: 15px;
}
.tp-contact-card a:hover {
	text-decoration: underline;
}
.tp-office-section {
	background: var(--gray-50);
	border-radius: var(--radius-lg);
	padding: 40px;
	margin-top: 16px;
}
.tp-office-section h2 {
	font-size: 24px;
	font-weight: 700;
	color: var(--gray-900);
	margin-bottom: 16px;
}
.tp-office-section p {
	font-size: 16px;
	line-height: 1.8;
	color: var(--gray-600);
	margin-bottom: 8px;
}
.tp-office-hours {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 8px;
	margin-top: 24px;
	max-width: 360px;
}
.tp-office-hours dt {
	font-weight: 600;
	color: var(--gray-700);
	font-size: 14px;
}
.tp-office-hours dd {
	color: var(--gray-500);
	font-size: 14px;
	margin: 0;
}
</style>

<div class="tp-page-hero">
	<div class="tp-container">
		<h1>Contact <?php echo esc_html( $brand ); ?></h1>
		<p>Have a question or need help finding the right property? We're here to help.</p>
	</div>
</div>

<div class="tp-page-content">
	<div class="tp-contact-grid">
		<?php if ( $phone ) : ?>
		<div class="tp-contact-card">
			<div class="icon">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
				</svg>
			</div>
			<h3>Call Us</h3>
			<p>Speak directly with our property experts.</p>
			<a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a>
		</div>
		<?php endif; ?>

		<?php if ( $email ) : ?>
		<div class="tp-contact-card">
			<div class="icon">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<rect width="20" height="16" x="2" y="4" rx="2"/>
					<path d="m22 7-8.97 5.7a1.94 1.94 0 01-2.06 0L2 7"/>
				</svg>
			</div>
			<h3>Email Us</h3>
			<p>We typically respond within 24 hours.</p>
			<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
		</div>
		<?php endif; ?>

		<?php if ( $whatsapp ) : ?>
		<?php
			$wa_url = $whatsapp;
			if ( strpos( $wa_url, 'http' ) !== 0 ) {
				$wa_url = 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $wa_url );
			}
		?>
		<div class="tp-contact-card">
			<div class="icon" style="background: #DCFCE7;">
				<svg viewBox="0 0 24 24" fill="#25D366" style="color:#25D366;">
					<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
				</svg>
			</div>
			<h3>WhatsApp</h3>
			<p>Chat with us instantly on WhatsApp.</p>
			<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener">Message on WhatsApp</a>
		</div>
		<?php endif; ?>
	</div>

	<?php if ( $address ) : ?>
	<div class="tp-office-section">
		<h2>Our Office</h2>
		<p><strong><?php echo esc_html( $brand ); ?></strong></p>
		<p><?php echo esc_html( $address ); ?></p>

		<dl class="tp-office-hours">
			<dt>Mon – Sat</dt>
			<dd>10:00 AM – 7:00 PM</dd>
			<dt>Sunday</dt>
			<dd>By Appointment</dd>
		</dl>
	</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
