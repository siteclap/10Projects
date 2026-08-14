<?php
/**
 * Template Name: About Us
 * Slug: about
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
	max-width: 800px;
	margin: 0 auto;
	padding: 48px 24px 80px;
}
.tp-page-content h2 {
	font-size: 24px;
	font-weight: 700;
	color: var(--gray-900);
	margin: 40px 0 16px;
}
.tp-page-content h2:first-child {
	margin-top: 0;
}
.tp-page-content p,
.tp-page-content li {
	font-size: 16px;
	line-height: 1.8;
	color: var(--gray-600);
}
.tp-page-content ul {
	list-style: none;
	padding: 0;
}
.tp-page-content ul li {
	padding: 8px 0 8px 28px;
	position: relative;
}
.tp-page-content ul li::before {
	content: '✓';
	position: absolute;
	left: 0;
	color: var(--success);
	font-weight: 700;
}
.tp-stats-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
	gap: 24px;
	margin: 32px 0;
}
.tp-stat-card {
	background: var(--gray-50);
	border-radius: var(--radius-md);
	padding: 24px;
	text-align: center;
}
.tp-stat-card .number {
	font-size: 32px;
	font-weight: 800;
	color: var(--brand-primary);
}
.tp-stat-card .label {
	font-size: 14px;
	color: var(--gray-500);
	margin-top: 4px;
}
.tp-contact-box {
	background: var(--brand-primary-bg);
	border-radius: var(--radius-md);
	padding: 32px;
	margin-top: 40px;
}
.tp-contact-box h3 {
	font-size: 20px;
	font-weight: 600;
	margin-bottom: 16px;
	color: var(--gray-900);
}
.tp-contact-box p {
	margin: 8px 0;
}
.tp-contact-box a {
	color: var(--brand-primary);
	font-weight: 500;
}
</style>

<div class="tp-page-hero">
	<div class="tp-container">
		<h1>About <?php echo esc_html( $brand ); ?></h1>
		<p>Helping you find the right home in Navi Mumbai — powered by data, driven by trust.</p>
	</div>
</div>

<div class="tp-page-content">
	<h2>Who We Are</h2>
	<p><?php echo esc_html( $brand ); ?> is Navi Mumbai's trusted property discovery platform. We combine deep local market knowledge with data-driven analysis to help homebuyers find the right property — not just any property.</p>
	<p>Unlike traditional listing portals that overwhelm you with hundreds of options, <?php echo esc_html( $brand ); ?> curates the <strong>10 best-fit projects</strong> based on your specific needs, budget, and lifestyle preferences.</p>

	<h2>What Makes Us Different</h2>
	<ul>
		<li><strong>AI-Powered Matching</strong> — Our proprietary Fit Score algorithm evaluates projects across 20 parameters to find your perfect match.</li>
		<li><strong>Verified Data Only</strong> — All project information is sourced from RERA registrations and verified developer data.</li>
		<li><strong>Transparent Analysis</strong> — We show you the pros AND cons of every project — no hidden agendas.</li>
		<li><strong>Local Expertise</strong> — Deep knowledge of every micro-market in Navi Mumbai, from Kharghar to Ulwe.</li>
		<li><strong>Zero Spam Promise</strong> — Your contact information is never shared without your explicit consent.</li>
	</ul>

	<h2>Our Numbers</h2>
	<div class="tp-stats-grid">
		<div class="tp-stat-card">
			<div class="number">100+</div>
			<div class="label">Verified Projects</div>
		</div>
		<div class="tp-stat-card">
			<div class="number">15+</div>
			<div class="label">Locations Covered</div>
		</div>
		<div class="tp-stat-card">
			<div class="number">50+</div>
			<div class="label">Trusted Developers</div>
		</div>
		<div class="tp-stat-card">
			<div class="number">20</div>
			<div class="label">Fit Score Parameters</div>
		</div>
	</div>

	<h2>Our Mission</h2>
	<p>Buying a home is one of the biggest decisions of your life. <?php echo esc_html( $brand ); ?> exists to make that decision easier, more informed, and less stressful. We believe every buyer deserves honest, data-backed guidance — not sales pressure.</p>

	<?php if ( $phone || $email || $address ) : ?>
	<div class="tp-contact-box">
		<h3>Get in Touch</h3>
		<?php if ( $phone ) : ?>
			<p>Phone: <a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a></p>
		<?php endif; ?>
		<?php if ( $email ) : ?>
			<p>Email: <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
		<?php endif; ?>
		<?php if ( $address ) : ?>
			<p>Address: <?php echo esc_html( $address ); ?></p>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
