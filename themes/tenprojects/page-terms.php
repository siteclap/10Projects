<?php
/**
 * Template Name: Terms of Service
 * Slug: terms
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$brand   = get_option( 'tp_brand_name', 'LeadMAAXX' );
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
.tp-page-hero h1 { font-size: 36px; font-weight: 700; margin-bottom: 12px; }
.tp-page-hero p { font-size: 18px; opacity: 0.85; max-width: 600px; margin: 0 auto; }
.tp-legal {
	max-width: 800px;
	margin: 0 auto;
	padding: 48px 24px 80px;
}
.tp-legal h2 {
	font-size: 22px;
	font-weight: 700;
	color: var(--gray-900);
	margin: 36px 0 12px;
}
.tp-legal h2:first-child { margin-top: 0; }
.tp-legal p,
.tp-legal li {
	font-size: 15px;
	line-height: 1.85;
	color: var(--gray-600);
	margin-bottom: 12px;
}
.tp-legal ul {
	padding-left: 24px;
	margin-bottom: 16px;
}
.tp-legal ul li { margin-bottom: 6px; }
.tp-legal strong { color: var(--gray-700); }
.tp-legal a { color: var(--brand-primary); }
.tp-legal-updated {
	font-size: 13px;
	color: var(--gray-400);
	margin-bottom: 32px;
}
</style>

<div class="tp-page-hero">
	<div class="tp-container">
		<h1>Terms of Service</h1>
		<p>Please read these terms carefully before using <?php echo esc_html( $brand ); ?>.</p>
	</div>
</div>

<div class="tp-legal">
	<p class="tp-legal-updated">Last updated: <?php echo esc_html( date( 'F j, Y' ) ); ?></p>

	<h2>1. Acceptance of Terms</h2>
	<p>By accessing or using the <?php echo esc_html( $brand ); ?> website at <?php echo esc_html( home_url() ); ?> ("Service"), you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our Service.</p>

	<h2>2. Description of Service</h2>
	<p><?php echo esc_html( $brand ); ?> is a property discovery and recommendation platform focused on Navi Mumbai. We provide:</p>
	<ul>
		<li>AI-powered property matching based on buyer preferences.</li>
		<li>Verified project listings with RERA-sourced data.</li>
		<li>Fit Score analysis across 20 parameters.</li>
		<li>EMI calculators and buyer guides.</li>
		<li>Connection to verified channel partners and developers.</li>
	</ul>

	<h2>3. User Responsibilities</h2>
	<p>By using our Service, you agree to:</p>
	<ul>
		<li>Provide accurate and truthful information when using our assessment tool or contact forms.</li>
		<li>Not use the Service for any unlawful purpose or in violation of any applicable laws.</li>
		<li>Not attempt to scrape, crawl, or extract data from our website using automated means.</li>
		<li>Not impersonate any person or entity or misrepresent your affiliation with any person or entity.</li>
	</ul>

	<h2>4. Property Information</h2>
	<p><?php echo esc_html( $brand ); ?> aggregates property information from publicly available sources including MahaRERA, developer websites, and verified channel partners. While we strive for accuracy:</p>
	<ul>
		<li>Property prices, specifications, and availability are subject to change without notice.</li>
		<li>All information should be independently verified before making any purchase decision.</li>
		<li><?php echo esc_html( $brand ); ?> is NOT a real estate developer or builder. We are a property advisory and discovery platform.</li>
		<li>Fit Scores are algorithmic recommendations and should not be the sole basis for property decisions.</li>
	</ul>

	<h2>5. Lead Sharing & Consent</h2>
	<p>When you express interest in a specific property or request a callback:</p>
	<ul>
		<li>Your contact information may be shared with the relevant developer or channel partner ONLY with your explicit consent.</li>
		<li>You may receive follow-up communications related to your property inquiry.</li>
		<li>You can withdraw consent at any time by contacting us.</li>
	</ul>

	<h2>6. Intellectual Property</h2>
	<p>All content on <?php echo esc_html( $brand ); ?> — including text, graphics, logos, Fit Score methodology, AI algorithms, and software — is the property of <?php echo esc_html( $brand ); ?> and is protected by intellectual property laws. You may not reproduce, distribute, or create derivative works without written permission.</p>

	<h2>7. Limitation of Liability</h2>
	<p><?php echo esc_html( $brand ); ?> shall not be liable for:</p>
	<ul>
		<li>Any inaccuracies in property listings or project information.</li>
		<li>Any loss or damage arising from your reliance on information provided through our Service.</li>
		<li>Actions or omissions of developers, builders, or channel partners connected through our platform.</li>
		<li>Any indirect, incidental, or consequential damages arising from use of the Service.</li>
	</ul>

	<h2>8. Indemnification</h2>
	<p>You agree to indemnify and hold harmless <?php echo esc_html( $brand ); ?>, its owners, employees, and affiliates from any claims, damages, or expenses arising from your use of the Service or violation of these Terms.</p>

	<h2>9. Termination</h2>
	<p>We reserve the right to suspend or terminate your access to the Service at any time, without prior notice, for any reason including violation of these Terms.</p>

	<h2>10. Governing Law</h2>
	<p>These Terms shall be governed by and construed in accordance with the laws of India. Any disputes shall be subject to the exclusive jurisdiction of the courts in Navi Mumbai, Maharashtra.</p>

	<h2>11. Changes to Terms</h2>
	<p>We reserve the right to modify these Terms at any time. Changes will be effective upon posting to this page. Continued use of the Service constitutes acceptance of the revised Terms.</p>

	<h2>12. Contact Us</h2>
	<p>If you have questions about these Terms of Service, please contact us:</p>
	<ul>
		<?php if ( $email ) : ?>
			<li><strong>Email:</strong> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
		<?php endif; ?>
		<?php if ( $address ) : ?>
			<li><strong>Address:</strong> <?php echo esc_html( $brand ); ?>, <?php echo esc_html( $address ); ?></li>
		<?php endif; ?>
	</ul>
</div>

<?php get_footer(); ?>
