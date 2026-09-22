<?php
/**
 * Template Name: Contact
 * Slug: contact
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$brand    = get_option( 'tp_brand_name', 'LeadMAAXX' );
$phone    = get_option( 'tp_brand_phone', '' );
$email    = get_option( 'tp_brand_email', '' );
$address  = get_option( 'tp_brand_address', '' );
$whatsapp = get_option( 'tp_social_whatsapp', '' );
if ( $whatsapp && strpos( $whatsapp, 'http' ) !== 0 ) {
	$whatsapp = 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $whatsapp );
}

get_header();
?>

<style>
/* ── Contact Hero ── */
.tp-contact-hero {
	background: linear-gradient(135deg, #111827 0%, #1E1147 50%, #111827 100%);
	color: #fff;
	padding: 80px 0 64px;
	text-align: center;
}
.tp-contact-hero__tag {
	display: inline-block;
	background: rgba(75,28,176,0.25);
	color: #c4b5fd;
	font-size: 12px;
	font-weight: 600;
	padding: 5px 16px;
	border-radius: 20px;
	letter-spacing: 0.5px;
	text-transform: uppercase;
	margin-bottom: 16px;
}
.tp-contact-hero h1 {
	font-size: 40px;
	font-weight: 800;
	margin-bottom: 16px;
	line-height: 1.2;
}
.tp-contact-hero p {
	font-size: 18px;
	color: rgba(255,255,255,0.65);
	max-width: 520px;
	margin: 0 auto;
	line-height: 1.7;
}

/* ── Shared Section ── */
.tp-contact-section {
	padding: 64px 0;
}
.tp-contact-section--gray {
	background: var(--gray-50);
}
.tp-contact-section__header {
	text-align: center;
	max-width: 600px;
	margin: 0 auto 40px;
}
.tp-contact-section__label {
	display: inline-block;
	font-size: 12px;
	font-weight: 700;
	color: var(--brand-primary);
	text-transform: uppercase;
	letter-spacing: 1px;
	margin-bottom: 10px;
}
.tp-contact-section__title {
	font-size: 28px;
	font-weight: 800;
	color: #111827;
	margin-bottom: 12px;
	line-height: 1.3;
}
.tp-contact-section__desc {
	font-size: 16px;
	color: var(--gray-500);
	line-height: 1.7;
}

/* ── Contact Cards ── */
.tp-contact-cards {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 24px;
	max-width: 900px;
	margin: 0 auto;
}
.tp-contact-card {
	background: #fff;
	border: 1px solid var(--gray-200);
	border-radius: 14px;
	padding: 32px 24px;
	text-align: center;
	transition: box-shadow 0.2s, transform 0.2s;
}
.tp-contact-card:hover {
	box-shadow: 0 8px 30px rgba(0,0,0,0.08);
	transform: translateY(-2px);
}
.tp-contact-card__icon {
	width: 56px;
	height: 56px;
	border-radius: 14px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto 16px;
}
.tp-contact-card__icon--phone {
	background: #EEF2FF;
	color: var(--brand-primary);
}
.tp-contact-card__icon--email {
	background: #FEF3C7;
	color: #D97706;
}
.tp-contact-card__icon--whatsapp {
	background: #DCFCE7;
	color: #16A34A;
}
.tp-contact-card__title {
	font-size: 17px;
	font-weight: 700;
	color: #111827;
	margin-bottom: 6px;
}
.tp-contact-card__desc {
	font-size: 13px;
	color: var(--gray-500);
	line-height: 1.6;
	margin-bottom: 16px;
}
.tp-contact-card__link {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	font-size: 15px;
	font-weight: 600;
	color: var(--brand-primary);
	text-decoration: none;
	transition: gap 0.2s;
}
.tp-contact-card__link:hover {
	gap: 10px;
}
.tp-contact-card__link--green {
	color: #16A34A;
}

/* ── Office + Form Grid ── */
.tp-contact-main {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 32px;
	max-width: 900px;
	margin: 0 auto;
}

/* ── Office Info ── */
.tp-contact-office {
	background: #fff;
	border: 1px solid var(--gray-200);
	border-radius: 16px;
	padding: 36px 32px;
}
.tp-contact-office__title {
	font-size: 20px;
	font-weight: 700;
	color: #111827;
	margin-bottom: 20px;
	display: flex;
	align-items: center;
	gap: 10px;
}
.tp-contact-office__title-icon {
	width: 36px;
	height: 36px;
	border-radius: 8px;
	background: #EEF2FF;
	color: var(--brand-primary);
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}
.tp-contact-office__row {
	display: flex;
	gap: 14px;
	margin-bottom: 20px;
}
.tp-contact-office__row-icon {
	width: 20px;
	flex-shrink: 0;
	color: var(--gray-400);
	margin-top: 2px;
}
.tp-contact-office__row-label {
	font-size: 12px;
	font-weight: 600;
	color: var(--gray-400);
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 4px;
}
.tp-contact-office__row-value {
	font-size: 15px;
	color: #111827;
	line-height: 1.6;
}
.tp-contact-office__hours {
	border-top: 1px solid var(--gray-100);
	padding-top: 20px;
	margin-top: 4px;
}
.tp-contact-office__hours-row {
	display: flex;
	justify-content: space-between;
	padding: 6px 0;
}
.tp-contact-office__hours-day {
	font-size: 14px;
	font-weight: 600;
	color: var(--gray-700);
}
.tp-contact-office__hours-time {
	font-size: 14px;
	color: var(--gray-500);
}

/* ── Contact Form ── */
.tp-contact-form-card {
	background: #fff;
	border: 1px solid var(--gray-200);
	border-radius: 16px;
	padding: 36px 32px;
}
.tp-contact-form-card__title {
	font-size: 20px;
	font-weight: 700;
	color: #111827;
	margin-bottom: 6px;
}
.tp-contact-form-card__desc {
	font-size: 14px;
	color: var(--gray-500);
	margin-bottom: 24px;
}
.tp-contact-form__group {
	margin-bottom: 16px;
}
.tp-contact-form__label {
	display: block;
	font-size: 13px;
	font-weight: 600;
	color: var(--gray-700);
	margin-bottom: 6px;
}
.tp-contact-form__input,
.tp-contact-form__textarea {
	width: 100%;
	padding: 10px 14px;
	border: 1px solid var(--gray-200);
	border-radius: 10px;
	font-size: 14px;
	color: #111827;
	background: var(--gray-50);
	transition: border-color 0.2s, box-shadow 0.2s;
	font-family: inherit;
	box-sizing: border-box;
}
.tp-contact-form__input:focus,
.tp-contact-form__textarea:focus {
	outline: none;
	border-color: var(--brand-primary);
	box-shadow: 0 0 0 3px rgba(26,86,219,0.1);
}
.tp-contact-form__textarea {
	resize: vertical;
	min-height: 100px;
}
.tp-contact-form__btn {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	background: var(--brand-primary);
	color: #fff;
	font-weight: 600;
	font-size: 15px;
	padding: 12px 28px;
	border-radius: 10px;
	border: none;
	cursor: pointer;
	transition: background 0.2s, transform 0.1s;
	width: 100%;
	justify-content: center;
}
.tp-contact-form__btn:hover {
	background: var(--brand-primary-dark, #1544B0);
}
.tp-contact-form__btn:active {
	transform: scale(0.98);
}

/* ── Quick Connect Strip ── */
.tp-contact-quick {
	max-width: 900px;
	margin: 0 auto;
	text-align: center;
}
.tp-contact-quick__inner {
	background: linear-gradient(135deg, #111827 0%, #1E1147 50%, #111827 100%);
	border-radius: 20px;
	padding: 48px 40px;
	color: #fff;
}
.tp-contact-quick__title {
	font-size: 24px;
	font-weight: 800;
	margin-bottom: 10px;
}
.tp-contact-quick__desc {
	font-size: 15px;
	color: rgba(255,255,255,0.6);
	margin-bottom: 28px;
}
.tp-contact-quick__btns {
	display: flex;
	gap: 16px;
	justify-content: center;
	flex-wrap: wrap;
}
.tp-contact-quick__btn {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	font-size: 15px;
	font-weight: 600;
	padding: 13px 28px;
	border-radius: 12px;
	text-decoration: none;
	transition: transform 0.2s, box-shadow 0.2s;
}
.tp-contact-quick__btn:hover {
	transform: translateY(-2px);
	box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}
.tp-contact-quick__btn--phone {
	background: #fff;
	color: #111827;
}
.tp-contact-quick__btn--wa {
	background: #25D366;
	color: #fff;
}

/* ── Responsive ── */
@media (max-width: 768px) {
	.tp-contact-hero h1 { font-size: 28px; }
	.tp-contact-hero { padding: 56px 0 40px; }
	.tp-contact-cards { grid-template-columns: 1fr; max-width: 400px; margin: 0 auto; }
	.tp-contact-main { grid-template-columns: 1fr; }
	.tp-contact-section { padding: 48px 0; }
	.tp-contact-quick__inner { padding: 36px 24px; }
	.tp-contact-quick__btns { flex-direction: column; align-items: center; }
}
</style>

<!-- Hero -->
<section class="tp-contact-hero">
	<div class="tp-container">
		<span class="tp-contact-hero__tag">Get in Touch</span>
		<h1>We'd Love to<br>Hear From You</h1>
		<p>Have a question about a property or need expert guidance? Our team is here to help.</p>
	</div>
</section>

<!-- Contact Cards -->
<section class="tp-contact-section">
	<div class="tp-container">
		<div class="tp-contact-section__header">
			<span class="tp-contact-section__label">Reach Us</span>
			<h2 class="tp-contact-section__title">Choose How to Connect</h2>
		</div>
		<div class="tp-contact-cards">
			<?php if ( $phone ) : ?>
			<div class="tp-contact-card">
				<div class="tp-contact-card__icon tp-contact-card__icon--phone">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
				</div>
				<div class="tp-contact-card__title">Call Us</div>
				<p class="tp-contact-card__desc">Speak directly with our property experts.</p>
				<a href="tel:<?php echo esc_attr( $phone ); ?>" class="tp-contact-card__link">
					<?php echo esc_html( $phone ); ?>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
				</a>
			</div>
			<?php endif; ?>

			<?php if ( $email ) : ?>
			<div class="tp-contact-card">
				<div class="tp-contact-card__icon tp-contact-card__icon--email">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
				</div>
				<div class="tp-contact-card__title">Email Us</div>
				<p class="tp-contact-card__desc">We typically respond within 24 hours.</p>
				<a href="mailto:<?php echo esc_attr( $email ); ?>" class="tp-contact-card__link">
					<?php echo esc_html( $email ); ?>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
				</a>
			</div>
			<?php endif; ?>

			<?php if ( $whatsapp ) : ?>
			<div class="tp-contact-card">
				<div class="tp-contact-card__icon tp-contact-card__icon--whatsapp">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
				</div>
				<div class="tp-contact-card__title">WhatsApp</div>
				<p class="tp-contact-card__desc">Chat with us instantly on WhatsApp.</p>
				<a href="<?php echo esc_url( $whatsapp ); ?>" target="_blank" rel="noopener" class="tp-contact-card__link tp-contact-card__link--green">
					Message Us
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
				</a>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- Office + Form -->
<section class="tp-contact-section tp-contact-section--gray">
	<div class="tp-container">
		<div class="tp-contact-section__header">
			<span class="tp-contact-section__label">Details</span>
			<h2 class="tp-contact-section__title">Office & Enquiry</h2>
		</div>
		<div class="tp-contact-main">

			<!-- Office Info -->
			<div class="tp-contact-office">
				<div class="tp-contact-office__title">
					<div class="tp-contact-office__title-icon">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1118 0z"/><circle cx="12" cy="10" r="3"/></svg>
					</div>
					Our Office
				</div>

				<div class="tp-contact-office__row">
					<div class="tp-contact-office__row-icon">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5"/></svg>
					</div>
					<div>
						<div class="tp-contact-office__row-label">Company</div>
						<div class="tp-contact-office__row-value"><?php echo esc_html( $brand ); ?></div>
					</div>
				</div>

				<?php if ( $address ) : ?>
				<div class="tp-contact-office__row">
					<div class="tp-contact-office__row-icon">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1118 0z"/><circle cx="12" cy="10" r="3"/></svg>
					</div>
					<div>
						<div class="tp-contact-office__row-label">Address</div>
						<div class="tp-contact-office__row-value"><?php echo esc_html( $address ); ?></div>
					</div>
				</div>
				<?php endif; ?>

				<div class="tp-contact-office__hours">
					<div class="tp-contact-office__row-label" style="margin-bottom:12px;">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
						Business Hours
					</div>
					<div class="tp-contact-office__hours-row">
						<span class="tp-contact-office__hours-day">Mon – Sat</span>
						<span class="tp-contact-office__hours-time">10:00 AM – 7:00 PM</span>
					</div>
					<div class="tp-contact-office__hours-row">
						<span class="tp-contact-office__hours-day">Sunday</span>
						<span class="tp-contact-office__hours-time">By Appointment</span>
					</div>
				</div>
			</div>

			<!-- Contact Form -->
			<div class="tp-contact-form-card">
				<div class="tp-contact-form-card__title">Send Us a Message</div>
				<p class="tp-contact-form-card__desc">Fill in the form and we'll get back to you shortly.</p>
				<form id="contactForm">
					<?php wp_nonce_field( 'tp_contact_form_nonce', 'contact_nonce' ); ?>
					<div class="tp-contact-form__group">
						<label class="tp-contact-form__label">Full Name</label>
						<input type="text" name="name" class="tp-contact-form__input" placeholder="Your name" required>
					</div>
					<div class="tp-contact-form__group">
						<label class="tp-contact-form__label">Phone Number</label>
						<input type="tel" name="phone" class="tp-contact-form__input" placeholder="10-digit mobile number" maxlength="10" pattern="[0-9]{10}" required>
					</div>
					<div class="tp-contact-form__group">
						<label class="tp-contact-form__label">Message</label>
						<textarea name="message" class="tp-contact-form__textarea" placeholder="How can we help you?" rows="4"></textarea>
					</div>
					<button type="submit" class="tp-contact-form__btn">
						Send Message
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4z"/></svg>
					</button>
				</form>
				<script>
				document.getElementById('contactForm').addEventListener('submit',function(e){
					e.preventDefault();
					var f=this,btn=f.querySelector('.tp-contact-form__btn');
					var name=f.querySelector('[name="name"]').value.trim();
					var phone=f.querySelector('[name="phone"]').value.replace(/\D/g,'');
					var msg=f.querySelector('[name="message"]').value.trim();
					if(phone.length<10)return;
					btn.textContent='Sending...';btn.disabled=true;
					var fd=new FormData();
					fd.append('action','tp_contact_form_lead');
					fd.append('nonce',f.querySelector('#contact_nonce').value);
					fd.append('name',name);
					fd.append('phone',phone);
					fd.append('message',msg);
					fd.append('page_url',location.href);
					fetch('<?php echo esc_url( admin_url("admin-ajax.php") ); ?>',{method:'POST',body:fd})
					.then(function(r){return r.json();})
					.then(function(d){
						if(d.success){btn.textContent='Message Sent!';f.reset();}
						else{btn.textContent='Try Again';btn.disabled=false;}
					})
					.catch(function(){btn.textContent='Try Again';btn.disabled=false;});
				});
				</script>
			</div>

		</div>
	</div>
</section>

<!-- Quick Connect CTA -->
<section class="tp-contact-section">
	<div class="tp-container">
		<div class="tp-contact-quick">
			<div class="tp-contact-quick__inner">
				<h2 class="tp-contact-quick__title">Prefer a Quick Chat?</h2>
				<p class="tp-contact-quick__desc">Skip the form — call us or message on WhatsApp for instant support.</p>
				<div class="tp-contact-quick__btns">
					<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( $phone ); ?>" class="tp-contact-quick__btn tp-contact-quick__btn--phone">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
						Call <?php echo esc_html( $phone ); ?>
					</a>
					<?php endif; ?>
					<?php if ( $whatsapp ) : ?>
					<a href="<?php echo esc_url( $whatsapp ); ?>" target="_blank" rel="noopener" class="tp-contact-quick__btn tp-contact-quick__btn--wa">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
						WhatsApp Us
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
