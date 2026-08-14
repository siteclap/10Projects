<?php
/**
 * Template Name: Privacy Policy
 * Slug: privacy
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
.tp-legal ul li {
	margin-bottom: 6px;
}
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
		<h1>Privacy Policy</h1>
		<p>Your privacy matters to us. Here's how <?php echo esc_html( $brand ); ?> handles your data.</p>
	</div>
</div>

<div class="tp-legal">
	<p class="tp-legal-updated">Last updated: <?php echo esc_html( date( 'F j, Y' ) ); ?></p>

	<h2>1. Introduction</h2>
	<p><?php echo esc_html( $brand ); ?> ("we", "us", or "our") operates the website <?php echo esc_html( home_url() ); ?> (the "Service"). This Privacy Policy explains how we collect, use, disclose, and protect your information when you use our Service.</p>

	<h2>2. Information We Collect</h2>
	<p>We may collect the following types of information:</p>
	<ul>
		<li><strong>Personal Information:</strong> Name, phone number, email address, and property preferences that you voluntarily provide when using our assessment tool, contact forms, or callback requests.</li>
		<li><strong>Usage Data:</strong> Pages visited, time spent on pages, browser type, device information, and IP address collected automatically through cookies and analytics tools.</li>
		<li><strong>Assessment Data:</strong> Your responses to our AI-powered property matching questionnaire, including budget, location preferences, configuration needs, and lifestyle preferences.</li>
	</ul>

	<h2>3. How We Use Your Information</h2>
	<p>We use the collected information for the following purposes:</p>
	<ul>
		<li>To provide personalized property recommendations based on your preferences.</li>
		<li>To connect you with relevant property developers or channel partners (only with your explicit consent).</li>
		<li>To improve our AI matching algorithm and enhance our Service.</li>
		<li>To send you property updates, newsletters, or promotional content (you can opt out at any time).</li>
		<li>To respond to your inquiries and provide customer support.</li>
		<li>To comply with legal obligations and protect our rights.</li>
	</ul>

	<h2>4. Information Sharing</h2>
	<p><?php echo esc_html( $brand ); ?> does NOT sell your personal information. We may share your data with:</p>
	<ul>
		<li><strong>Channel Partners & Developers:</strong> Only when you explicitly request to be connected with a specific project or developer.</li>
		<li><strong>Service Providers:</strong> Third-party services that help us operate our platform (hosting, analytics, email delivery).</li>
		<li><strong>Legal Requirements:</strong> When required by law, regulation, or legal process.</li>
	</ul>

	<h2>5. Data Security</h2>
	<p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction. However, no method of electronic transmission or storage is 100% secure.</p>

	<h2>6. Cookies</h2>
	<p>We use cookies and similar tracking technologies to enhance your experience. You can control cookies through your browser settings. Essential cookies are required for the Service to function properly.</p>

	<h2>7. Third-Party Links</h2>
	<p>Our Service may contain links to third-party websites (developer websites, RERA portals, etc.). We are not responsible for the privacy practices of these external sites.</p>

	<h2>8. Your Rights</h2>
	<p>You have the right to:</p>
	<ul>
		<li>Access the personal data we hold about you.</li>
		<li>Request correction or deletion of your personal data.</li>
		<li>Opt out of marketing communications at any time.</li>
		<li>Withdraw consent for data processing.</li>
	</ul>

	<h2>9. Data Retention</h2>
	<p>We retain your personal information only for as long as necessary to fulfil the purposes outlined in this policy, or as required by law. Assessment data is retained for up to 12 months to enable continuity in your property search.</p>

	<h2>10. Changes to This Policy</h2>
	<p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date.</p>

	<h2>11. Contact Us</h2>
	<p>If you have any questions about this Privacy Policy, please contact us:</p>
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
