<?php
/**
 * Template Name: Careers
 * Slug: careers
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$brand = get_option( 'tp_brand_name', 'LeadMAAXX' );
$email = get_option( 'tp_brand_email', '' );

get_header();
?>

<style>
/* ── Careers Hero ── */
.tp-careers-hero {
	background: linear-gradient(135deg, #111827 0%, #1E1147 50%, #111827 100%);
	color: #fff;
	padding: 80px 0 64px;
	text-align: center;
}
.tp-careers-hero__tag {
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
.tp-careers-hero h1 {
	font-size: 40px;
	font-weight: 800;
	margin-bottom: 16px;
	line-height: 1.2;
}
.tp-careers-hero p {
	font-size: 18px;
	color: rgba(255,255,255,0.65);
	max-width: 560px;
	margin: 0 auto;
	line-height: 1.7;
}

/* ── Shared Section ── */
.tp-careers-section {
	padding: 64px 0;
}
.tp-careers-section--gray {
	background: var(--gray-50);
}
.tp-careers-section__header {
	text-align: center;
	max-width: 600px;
	margin: 0 auto 40px;
}
.tp-careers-section__label {
	display: inline-block;
	font-size: 12px;
	font-weight: 700;
	color: var(--brand-primary);
	text-transform: uppercase;
	letter-spacing: 1px;
	margin-bottom: 10px;
}
.tp-careers-section__title {
	font-size: 28px;
	font-weight: 800;
	color: #111827;
	margin-bottom: 12px;
	line-height: 1.3;
}
.tp-careers-section__desc {
	font-size: 16px;
	color: var(--gray-500);
	line-height: 1.7;
}

/* ── Culture Intro ── */
.tp-careers-intro {
	max-width: 760px;
	margin: 0 auto;
	text-align: center;
}
.tp-careers-intro p {
	font-size: 17px;
	line-height: 1.8;
	color: var(--gray-600);
	margin-bottom: 16px;
}
.tp-careers-intro strong {
	color: #111827;
}

/* ── Perks Grid ── */
.tp-careers-perks {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 24px;
	max-width: 960px;
	margin: 0 auto;
}
.tp-careers-perk {
	background: #fff;
	border: 1px solid var(--gray-200);
	border-radius: 12px;
	padding: 28px 20px;
	text-align: center;
	transition: box-shadow 0.2s;
}
.tp-careers-perk:hover {
	box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.tp-careers-perk__icon {
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
.tp-careers-perk__title {
	font-size: 15px;
	font-weight: 700;
	color: #111827;
	margin-bottom: 6px;
}
.tp-careers-perk__text {
	font-size: 13px;
	color: var(--gray-500);
	line-height: 1.6;
}

/* ── Values Grid ── */
.tp-careers-values {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 24px;
	max-width: 900px;
	margin: 0 auto;
}
.tp-careers-value {
	background: #fff;
	border: 1px solid var(--gray-200);
	border-radius: 14px;
	padding: 32px 24px;
	text-align: center;
	transition: box-shadow 0.2s;
}
.tp-careers-value:hover {
	box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.tp-careers-value__num {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 36px;
	height: 36px;
	border-radius: 50%;
	background: var(--brand-primary);
	color: #fff;
	font-size: 14px;
	font-weight: 800;
	margin-bottom: 16px;
}
.tp-careers-value__title {
	font-size: 17px;
	font-weight: 700;
	color: #111827;
	margin-bottom: 8px;
}
.tp-careers-value__text {
	font-size: 14px;
	color: var(--gray-500);
	line-height: 1.7;
}

/* ── CTA ── */
.tp-careers-cta {
	max-width: 700px;
	margin: 0 auto;
	text-align: center;
	background: linear-gradient(135deg, #111827 0%, #1E1147 50%, #111827 100%);
	border-radius: 20px;
	padding: 56px 40px;
	color: #fff;
}
.tp-careers-cta__icon {
	width: 56px;
	height: 56px;
	border-radius: 50%;
	background: rgba(75,28,176,0.3);
	color: #c4b5fd;
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto 20px;
}
.tp-careers-cta h2 {
	font-size: 26px;
	font-weight: 800;
	margin-bottom: 12px;
}
.tp-careers-cta p {
	font-size: 16px;
	color: rgba(255,255,255,0.65);
	line-height: 1.7;
	max-width: 440px;
	margin: 0 auto 28px;
}
.tp-careers-cta__btn {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	background: #fff;
	color: #111827;
	font-weight: 700;
	font-size: 15px;
	padding: 14px 36px;
	border-radius: 12px;
	text-decoration: none;
	transition: transform 0.2s, box-shadow 0.2s;
}
.tp-careers-cta__btn:hover {
	transform: translateY(-2px);
	box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}
.tp-careers-cta__hint {
	font-size: 13px;
	color: rgba(255,255,255,0.4);
	margin-top: 16px;
}

/* ── Responsive ── */
@media (max-width: 768px) {
	.tp-careers-hero h1 { font-size: 28px; }
	.tp-careers-hero { padding: 56px 0 40px; }
	.tp-careers-perks { grid-template-columns: 1fr 1fr; }
	.tp-careers-values { grid-template-columns: 1fr; max-width: 400px; }
	.tp-careers-section { padding: 48px 0; }
	.tp-careers-cta { padding: 40px 24px; }
}
</style>

<!-- Hero -->
<section class="tp-careers-hero">
	<div class="tp-container">
		<span class="tp-careers-hero__tag">We're Hiring</span>
		<h1>Build the Future of<br>Real Estate</h1>
		<p>Join a small, high-impact team transforming how Indians discover and buy property.</p>
	</div>
</section>

<!-- Culture -->
<section class="tp-careers-section">
	<div class="tp-container">
		<div class="tp-careers-section__header">
			<span class="tp-careers-section__label">Who We Are</span>
			<h2 class="tp-careers-section__title">Not Your Typical Startup</h2>
		</div>
		<div class="tp-careers-intro">
			<p><?php echo esc_html( $brand ); ?> is on a mission to transform how Indians discover and buy real estate. We're a <strong>small, fast-moving team</strong> that values ownership, impact, and honest work over corporate bureaucracy.</p>
			<p>Here, your work directly reaches <strong>thousands of homebuyers</strong> making the biggest financial decision of their lives. If you want to build something meaningful — not just ship features — this is the place.</p>
		</div>
	</div>
</section>

<!-- Perks -->
<section class="tp-careers-section tp-careers-section--gray">
	<div class="tp-container">
		<div class="tp-careers-section__header">
			<span class="tp-careers-section__label">Benefits</span>
			<h2 class="tp-careers-section__title">Why You'll Love Working Here</h2>
		</div>
		<div class="tp-careers-perks">
			<div class="tp-careers-perk">
				<div class="tp-careers-perk__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
				</div>
				<div class="tp-careers-perk__title">High Impact Work</div>
				<p class="tp-careers-perk__text">Your work directly shapes the product and reaches thousands of homebuyers.</p>
			</div>
			<div class="tp-careers-perk">
				<div class="tp-careers-perk__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
				</div>
				<div class="tp-careers-perk__title">Growth-Stage Energy</div>
				<p class="tp-careers-perk__text">Move fast, ship often, and grow your skills with real challenges every week.</p>
			</div>
			<div class="tp-careers-perk">
				<div class="tp-careers-perk__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v4m0 14v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M1 12h4m14 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg>
				</div>
				<div class="tp-careers-perk__title">Competitive Pay</div>
				<p class="tp-careers-perk__text">Fair compensation with performance-based incentives and bonuses.</p>
			</div>
			<div class="tp-careers-perk">
				<div class="tp-careers-perk__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
				</div>
				<div class="tp-careers-perk__title">Flexible Work</div>
				<p class="tp-careers-perk__text">Hybrid setup — work from home or our office, whatever works best for you.</p>
			</div>
		</div>
	</div>
</section>

<!-- Our Values -->
<section class="tp-careers-section">
	<div class="tp-container">
		<div class="tp-careers-section__header">
			<span class="tp-careers-section__label">Culture</span>
			<h2 class="tp-careers-section__title">What We Stand For</h2>
		</div>
		<div class="tp-careers-values">
			<div class="tp-careers-value">
				<div class="tp-careers-value__num">1</div>
				<div class="tp-careers-value__title">Ownership Over Titles</div>
				<p class="tp-careers-value__text">We care about what you build, not what's on your business card. Take charge and make decisions.</p>
			</div>
			<div class="tp-careers-value">
				<div class="tp-careers-value__num">2</div>
				<div class="tp-careers-value__title">Transparency First</div>
				<p class="tp-careers-value__text">We show homebuyers the pros and cons of every project — and we're equally honest with each other.</p>
			</div>
			<div class="tp-careers-value">
				<div class="tp-careers-value__num">3</div>
				<div class="tp-careers-value__title">Data Beats Opinions</div>
				<p class="tp-careers-value__text">Every decision — from product features to marketing spend — is backed by real numbers, not gut feel.</p>
			</div>
		</div>
	</div>
</section>

<!-- CTA -->
<section class="tp-careers-section">
	<div class="tp-container">
		<div class="tp-careers-cta">
			<div class="tp-careers-cta__icon">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
			</div>
			<h2>Think You're a Great Fit?</h2>
			<p>Send us your resume and a short note about what excites you about real estate and technology.</p>
			<?php
			$mailto = $email ? $email : 'careers@10projects.com';
			$subject = rawurlencode( 'Career Inquiry — ' . $brand );
			?>
			<a href="mailto:<?php echo esc_attr( $mailto ); ?>?subject=<?php echo esc_attr( $subject ); ?>" class="tp-careers-cta__btn">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
				Send Your Resume
			</a>
			<p class="tp-careers-cta__hint">We typically respond within 2–3 business days</p>
		</div>
	</div>
</section>

<?php get_footer(); ?>
