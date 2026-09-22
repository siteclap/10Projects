<?php
/**
 * Template Name: About Us
 * Slug: about
 *
 * Polished about page with founders, mission, stats, and differentiators.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$brand   = get_option( 'tp_brand_name', 'LeadMAAXX' );
$phone   = get_option( 'tp_brand_phone', '' );
$email   = get_option( 'tp_brand_email', '' );
$address = get_option( 'tp_brand_address', '' );

get_header();
?>

<style>
/* ── About Hero ── */
.tp-about-hero {
	background: linear-gradient(135deg, #111827 0%, #1E1147 50%, #111827 100%);
	color: #fff;
	padding: 80px 0 64px;
	text-align: center;
}
.tp-about-hero__tag {
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
.tp-about-hero h1 {
	font-size: 40px;
	font-weight: 800;
	margin-bottom: 16px;
	line-height: 1.2;
}
.tp-about-hero p {
	font-size: 18px;
	color: rgba(255,255,255,0.65);
	max-width: 560px;
	margin: 0 auto;
	line-height: 1.7;
}

/* ── About Content Wrapper ── */
.tp-about-section {
	padding: 64px 0;
}
.tp-about-section--gray {
	background: var(--gray-50);
}
.tp-about-section__header {
	text-align: center;
	max-width: 600px;
	margin: 0 auto 40px;
}
.tp-about-section__label {
	display: inline-block;
	font-size: 12px;
	font-weight: 700;
	color: var(--brand-primary);
	text-transform: uppercase;
	letter-spacing: 1px;
	margin-bottom: 10px;
}
.tp-about-section__title {
	font-size: 28px;
	font-weight: 800;
	color: #111827;
	margin-bottom: 12px;
	line-height: 1.3;
}
.tp-about-section__desc {
	font-size: 16px;
	color: var(--gray-500);
	line-height: 1.7;
}

/* ── Who We Are (intro) ── */
.tp-about-intro {
	max-width: 760px;
	margin: 0 auto;
	text-align: center;
}
.tp-about-intro p {
	font-size: 17px;
	line-height: 1.8;
	color: var(--gray-600);
	margin-bottom: 16px;
}
.tp-about-intro strong {
	color: #111827;
}

/* ── Founders ── */
.tp-founders-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 32px;
	max-width: 800px;
	margin: 0 auto;
}
.tp-founder-card {
	background: #fff;
	border: 1px solid var(--gray-200);
	border-radius: 16px;
	padding: 32px 24px;
	text-align: center;
	transition: box-shadow 0.3s;
}
.tp-founder-card:hover {
	box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}
.tp-founder-card__photo {
	width: 120px;
	height: 120px;
	border-radius: 50%;
	margin: 0 auto 20px;
	background: linear-gradient(135deg, #EEF2FF, #E0E7FF);
	border: 3px solid var(--gray-200);
	display: flex;
	align-items: center;
	justify-content: center;
	overflow: hidden;
}
.tp-founder-card__photo img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	border-radius: 50%;
}
.tp-founder-card__photo-placeholder {
	color: var(--gray-300);
}
.tp-founder-card__name {
	font-size: 20px;
	font-weight: 700;
	color: #111827;
	margin-bottom: 4px;
}
.tp-founder-card__role {
	font-size: 13px;
	font-weight: 600;
	color: var(--brand-primary);
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 16px;
}
.tp-founder-card__divider {
	width: 40px;
	height: 3px;
	background: var(--brand-primary);
	border-radius: 2px;
	margin: 0 auto 16px;
}
.tp-founder-card__bio {
	font-size: 14px;
	color: var(--gray-500);
	line-height: 1.7;
	margin-bottom: 16px;
}
.tp-founder-card__handles {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
	justify-content: center;
}
.tp-founder-card__tag {
	display: inline-block;
	font-size: 11px;
	font-weight: 600;
	padding: 4px 10px;
	border-radius: 20px;
	background: var(--brand-primary-bg, #EEF2FF);
	color: var(--brand-primary);
}

/* ── Differentiators ── */
.tp-diff-grid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 24px;
	max-width: 960px;
	margin: 0 auto;
}
.tp-diff-card {
	background: #fff;
	border: 1px solid var(--gray-200);
	border-radius: 12px;
	padding: 28px 20px;
	text-align: center;
	transition: box-shadow 0.2s;
}
.tp-diff-card:hover {
	box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.tp-diff-card__icon {
	width: 52px;
	height: 52px;
	border-radius: 12px;
	background: var(--brand-primary-bg, #EEF2FF);
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto 14px;
	color: var(--brand-primary);
}
.tp-diff-card__title {
	font-size: 15px;
	font-weight: 700;
	color: #111827;
	margin-bottom: 6px;
}
.tp-diff-card__text {
	font-size: 13px;
	color: var(--gray-500);
	line-height: 1.6;
}

/* ── Stats ── */
.tp-about-stats {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 24px;
	max-width: 800px;
	margin: 0 auto;
}
.tp-about-stat {
	text-align: center;
	padding: 24px 16px;
	background: #fff;
	border-radius: 12px;
	border: 1px solid var(--gray-200);
}
.tp-about-stat__num {
	font-size: 36px;
	font-weight: 800;
	color: var(--brand-primary);
	line-height: 1;
	margin-bottom: 6px;
}
.tp-about-stat__label {
	font-size: 13px;
	color: var(--gray-500);
	font-weight: 500;
}

/* ── Mission & Vision ── */
.tp-about-mv-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 28px;
	max-width: 900px;
	margin: 0 auto;
}
.tp-about-mission {
	text-align: center;
	background: linear-gradient(135deg, #EEF2FF 0%, #F5F3FF 100%);
	border-radius: 16px;
	padding: 40px 32px;
	border: 1px solid #E0E7FF;
}
.tp-about-mission__icon {
	width: 56px;
	height: 56px;
	border-radius: 50%;
	background: var(--brand-primary);
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto 20px;
}
.tp-about-mission h2 {
	font-size: 22px;
	font-weight: 800;
	color: #111827;
	margin-bottom: 14px;
}
.tp-about-mission p {
	font-size: 15px;
	color: var(--gray-600);
	line-height: 1.8;
}

/* ── Contact ── */
.tp-about-contact {
	max-width: 700px;
	margin: 0 auto;
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
	gap: 20px;
}
.tp-about-contact__item {
	background: #fff;
	border: 1px solid var(--gray-200);
	border-radius: 12px;
	padding: 24px 20px;
	text-align: center;
}
.tp-about-contact__icon {
	width: 44px;
	height: 44px;
	border-radius: 10px;
	background: var(--brand-primary-bg, #EEF2FF);
	color: var(--brand-primary);
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto 12px;
}
.tp-about-contact__label {
	font-size: 12px;
	font-weight: 600;
	color: var(--gray-400);
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 4px;
}
.tp-about-contact__value {
	font-size: 14px;
	font-weight: 600;
	color: #111827;
}
.tp-about-contact__value a {
	color: var(--brand-primary);
	text-decoration: none;
}
.tp-about-contact__value a:hover {
	text-decoration: underline;
}

/* ── Responsive ── */
@media (max-width: 768px) {
	.tp-about-hero h1 { font-size: 28px; }
	.tp-about-hero { padding: 56px 0 40px; }
	.tp-founders-grid { grid-template-columns: 1fr; max-width: 380px; }
	.tp-about-stats { grid-template-columns: repeat(2, 1fr); }
	.tp-about-section { padding: 48px 0; }
	.tp-about-mv-grid { grid-template-columns: 1fr; }
	.tp-about-mission { padding: 32px 24px; }
	.tp-diff-grid { grid-template-columns: 1fr 1fr; }
}
</style>

<!-- Hero -->
<section class="tp-about-hero">
	<div class="tp-container">
		<span class="tp-about-hero__tag">About Us</span>
		<h1>Building Trust in<br>Real Estate</h1>
		<p>Helping you find the right home in Navi Mumbai — powered by data, driven by trust.</p>
	</div>
</section>

<!-- Meet the Founders -->
<section class="tp-about-section tp-about-section--gray">
	<div class="tp-container">
		<div class="tp-about-section__header">
			<span class="tp-about-section__label">Leadership</span>
			<h2 class="tp-about-section__title">Meet the Founders</h2>
			<p class="tp-about-section__desc">Two industry insiders who decided homebuyers deserve better.</p>
		</div>
		<div class="tp-founders-grid">

			<!-- Founder 1 -->
			<div class="tp-founder-card">
				<div class="tp-founder-card__photo">
					<!-- Replace src with actual photo -->
					<svg class="tp-founder-card__photo-placeholder" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
				</div>
				<div class="tp-founder-card__name">Founder Name</div>
				<div class="tp-founder-card__role">Co-Founder & CEO</div>
				<div class="tp-founder-card__divider"></div>
				<p class="tp-founder-card__bio">With 10+ years in Navi Mumbai real estate, brings deep market knowledge and developer relationships to ensure every project listing is verified and trustworthy.</p>
				<div class="tp-founder-card__handles">
					<span class="tp-founder-card__tag">Business Strategy</span>
					<span class="tp-founder-card__tag">Developer Relations</span>
					<span class="tp-founder-card__tag">Market Analysis</span>
				</div>
			</div>

			<!-- Founder 2 -->
			<div class="tp-founder-card">
				<div class="tp-founder-card__photo">
					<!-- Replace src with actual photo -->
					<svg class="tp-founder-card__photo-placeholder" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
				</div>
				<div class="tp-founder-card__name">Founder Name</div>
				<div class="tp-founder-card__role">Co-Founder & CTO</div>
				<div class="tp-founder-card__divider"></div>
				<p class="tp-founder-card__bio">A technology enthusiast who built the AI-powered Fit Score engine. Passionate about using data science to simplify the homebuying journey for every Indian family.</p>
				<div class="tp-founder-card__handles">
					<span class="tp-founder-card__tag">Technology & AI</span>
					<span class="tp-founder-card__tag">Product Development</span>
					<span class="tp-founder-card__tag">Data Science</span>
				</div>
			</div>

		</div>
	</div>
</section>

<!-- Who We Are -->
<section class="tp-about-section">
	<div class="tp-container">
		<div class="tp-about-section__header">
			<span class="tp-about-section__label">Who We Are</span>
			<h2 class="tp-about-section__title">More Than a Listing Portal</h2>
		</div>
		<div class="tp-about-intro">
			<p><?php echo esc_html( $brand ); ?> is Navi Mumbai's trusted property discovery platform. We combine deep local market knowledge with data-driven analysis to help homebuyers find the right property — not just any property.</p>
			<p>Unlike traditional portals that overwhelm you with hundreds of options, we curate the <strong>10 best-fit projects</strong> based on your specific needs, budget, and lifestyle preferences.</p>
		</div>
	</div>
</section>

<!-- What Makes Us Different -->
<section class="tp-about-section">
	<div class="tp-container">
		<div class="tp-about-section__header">
			<span class="tp-about-section__label">Why Choose Us</span>
			<h2 class="tp-about-section__title">What Makes Us Different</h2>
		</div>
		<div class="tp-diff-grid">
			<div class="tp-diff-card">
				<div class="tp-diff-card__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
				</div>
				<div class="tp-diff-card__title">AI-Powered Matching</div>
				<p class="tp-diff-card__text">Our Fit Score algorithm evaluates projects across 20 parameters to find your perfect match.</p>
			</div>
			<div class="tp-diff-card">
				<div class="tp-diff-card__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
				</div>
				<div class="tp-diff-card__title">Verified Data Only</div>
				<p class="tp-diff-card__text">All information is sourced from RERA registrations and verified developer data.</p>
			</div>
			<div class="tp-diff-card">
				<div class="tp-diff-card__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
				</div>
				<div class="tp-diff-card__title">Transparent Analysis</div>
				<p class="tp-diff-card__text">We show you the pros AND cons of every project — no hidden agendas.</p>
			</div>
			<div class="tp-diff-card">
				<div class="tp-diff-card__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1118 0z"/><circle cx="12" cy="10" r="3"/></svg>
				</div>
				<div class="tp-diff-card__title">Local Expertise</div>
				<p class="tp-diff-card__text">Deep knowledge of every micro-market in Navi Mumbai, from Kharghar to Ulwe.</p>
			</div>
		</div>
	</div>
</section>

<!-- Numbers -->
<section class="tp-about-section tp-about-section--gray">
	<div class="tp-container">
		<div class="tp-about-section__header">
			<span class="tp-about-section__label">Our Impact</span>
			<h2 class="tp-about-section__title">Numbers That Matter</h2>
		</div>
		<div class="tp-about-stats">
			<div class="tp-about-stat">
				<div class="tp-about-stat__num">100+</div>
				<div class="tp-about-stat__label">Verified Projects</div>
			</div>
			<div class="tp-about-stat">
				<div class="tp-about-stat__num">15+</div>
				<div class="tp-about-stat__label">Locations Covered</div>
			</div>
			<div class="tp-about-stat">
				<div class="tp-about-stat__num">50+</div>
				<div class="tp-about-stat__label">Trusted Developers</div>
			</div>
			<div class="tp-about-stat">
				<div class="tp-about-stat__num">20</div>
				<div class="tp-about-stat__label">Fit Score Parameters</div>
			</div>
		</div>
	</div>
</section>

<!-- Mission & Vision -->
<section class="tp-about-section">
	<div class="tp-container">
		<div class="tp-about-mv-grid">
			<div class="tp-about-mission">
				<div class="tp-about-mission__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
				</div>
				<h2>Our Vision</h2>
				<p>To become India's most trusted property platform — where every homebuyer gets personalized, data-driven recommendations and every decision is backed by transparency, not marketing.</p>
			</div>
			<div class="tp-about-mission">
				<div class="tp-about-mission__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
				</div>
				<h2>Our Mission</h2>
				<p>Buying a home is one of the biggest decisions of your life. <?php echo esc_html( $brand ); ?> exists to make that decision easier, more informed, and less stressful. We believe every buyer deserves honest, data-backed guidance — not sales pressure.</p>
			</div>
		</div>
	</div>
</section>

<!-- Contact -->
<?php if ( $phone || $email || $address ) : ?>
<section class="tp-about-section tp-about-section--gray">
	<div class="tp-container">
		<div class="tp-about-section__header">
			<span class="tp-about-section__label">Reach Out</span>
			<h2 class="tp-about-section__title">Get in Touch</h2>
		</div>
		<div class="tp-about-contact">
			<?php if ( $phone ) : ?>
			<div class="tp-about-contact__item">
				<div class="tp-about-contact__icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
				</div>
				<div class="tp-about-contact__label">Phone</div>
				<div class="tp-about-contact__value"><a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a></div>
			</div>
			<?php endif; ?>
			<?php if ( $email ) : ?>
			<div class="tp-about-contact__item">
				<div class="tp-about-contact__icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
				</div>
				<div class="tp-about-contact__label">Email</div>
				<div class="tp-about-contact__value"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></div>
			</div>
			<?php endif; ?>
			<?php if ( $address ) : ?>
			<div class="tp-about-contact__item">
				<div class="tp-about-contact__icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1118 0z"/><circle cx="12" cy="10" r="3"/></svg>
				</div>
				<div class="tp-about-contact__label">Office</div>
				<div class="tp-about-contact__value"><?php echo esc_html( $address ); ?></div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
