<?php
/**
 * Template Name: Disclaimer
 * Slug: disclaimer
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$brand      = get_option( 'tp_brand_name', 'LeadMAAXX' );
$email      = get_option( 'tp_brand_email', '' );
$address    = get_option( 'tp_brand_address', '' );
$rera_agent = get_option( 'tp_brand_rera_agent', '' );
$rera_legal = get_option( 'tp_brand_rera_legal_name', '' );

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
.tp-rera-box {
	background: var(--accent-pale);
	border: 1px solid var(--accent);
	border-radius: var(--radius-md);
	padding: 24px;
	margin: 32px 0;
}
.tp-rera-box h3 {
	font-size: 18px;
	font-weight: 600;
	color: var(--gray-900);
	margin-bottom: 12px;
}
.tp-rera-box p {
	margin-bottom: 6px;
}
</style>

<div class="tp-page-hero">
	<div class="tp-container">
		<h1>Disclaimer</h1>
		<p>Important information about the data and services provided by <?php echo esc_html( $brand ); ?>.</p>
	</div>
</div>

<div class="tp-legal">
	<p class="tp-legal-updated">Last updated: <?php echo esc_html( date( 'F j, Y' ) ); ?></p>

	<h2>1. General Disclaimer</h2>
	<p><?php echo esc_html( $brand ); ?> is a property discovery and advisory platform. We are <strong>NOT</strong> a real estate developer, builder, or construction company. We do not sell, construct, or deliver any property. <?php echo esc_html( $brand ); ?> acts solely as an information aggregator and property recommendation service.</p>

	<h2>2. Information Accuracy</h2>
	<p>All property information displayed on <?php echo esc_html( $brand ); ?> — including but not limited to prices, specifications, floor plans, amenities, possession dates, and RERA details — is sourced from:</p>
	<ul>
		<li>MahaRERA (Maharashtra Real Estate Regulatory Authority) public database.</li>
		<li>Official developer websites and marketing materials.</li>
		<li>Verified channel partner networks.</li>
		<li>Government and municipal body records.</li>
	</ul>
	<p>While we make every effort to ensure accuracy, <?php echo esc_html( $brand ); ?> <strong>does not guarantee</strong> the completeness, reliability, or currency of any information. Property details are subject to change by developers without prior notice.</p>

	<h2>3. Fit Score Disclaimer</h2>
	<p>The <?php echo esc_html( $brand ); ?> Fit Score is a proprietary algorithmic rating based on 20 evaluation parameters. It is designed to help buyers compare projects based on their stated preferences. The Fit Score:</p>
	<ul>
		<li>Is NOT a quality certification, endorsement, or guarantee of any project.</li>
		<li>Should NOT be the sole basis for making a purchase decision.</li>
		<li>Is based on available data at the time of calculation and may change as new information becomes available.</li>
		<li>Does not account for subjective preferences like personal taste, family-specific needs, or future market conditions.</li>
	</ul>

	<h2>4. Price & Availability</h2>
	<p>All prices mentioned on <?php echo esc_html( $brand ); ?> are indicative and subject to change. Actual prices may vary based on:</p>
	<ul>
		<li>Floor, unit type, and facing preferences.</li>
		<li>Applicable taxes (GST, stamp duty, registration charges).</li>
		<li>Developer offers, discounts, or payment plan changes.</li>
		<li>Market conditions and regulatory changes.</li>
	</ul>
	<p>Always verify current pricing directly with the developer before making any financial commitment.</p>

	<h2>5. Investment Disclaimer</h2>
	<p>Property is a significant financial commitment. <?php echo esc_html( $brand ); ?> does not provide financial advice. Past performance of any location or project type is not indicative of future returns. We strongly recommend consulting with a qualified financial advisor before making any property investment decision.</p>

	<?php if ( $rera_agent || $rera_legal ) : ?>
	<div class="tp-rera-box">
		<h3>RERA Information</h3>
		<?php if ( $rera_legal ) : ?>
			<p><strong>Legal Entity:</strong> <?php echo esc_html( $rera_legal ); ?></p>
		<?php endif; ?>
		<?php if ( $rera_agent ) : ?>
			<p><strong>RERA Agent Registration:</strong> <?php echo esc_html( $rera_agent ); ?></p>
		<?php endif; ?>
		<p style="font-size:13px;color:var(--gray-500);margin-top:12px;">All project RERA numbers are mentioned on their respective project pages. Buyers are advised to verify RERA registration on <a href="https://maharera.maharashtra.gov.in/" target="_blank" rel="noopener">maharera.maharashtra.gov.in</a>.</p>
	</div>
	<?php endif; ?>

	<h2>6. Third-Party Links</h2>
	<p>Our Service may link to external websites including developer portals, government sites, and financial tools. <?php echo esc_html( $brand ); ?> has no control over the content, privacy practices, or availability of these external sites and is not responsible for any loss or damage arising from their use.</p>

	<h2>7. Limitation of Liability</h2>
	<p><?php echo esc_html( $brand ); ?> and its owners, employees, and affiliates shall not be held liable for any direct, indirect, incidental, or consequential damages arising from:</p>
	<ul>
		<li>Your reliance on any information provided through our platform.</li>
		<li>Any transaction or agreement between you and any developer, builder, or channel partner.</li>
		<li>Delays, defects, or non-delivery by any developer or builder.</li>
		<li>Errors or omissions in the information displayed on our platform.</li>
	</ul>

	<h2>8. Contact</h2>
	<p>For any clarifications regarding this disclaimer, please contact:</p>
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
