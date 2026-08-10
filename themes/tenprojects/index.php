<?php
/**
 * Main template file.
 *
 * Fallback template for pages without specific templates.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="tp-container" style="padding:var(--4xl) var(--lg);text-align:center;">
	<h1 style="font-size:28px;font-weight:700;margin-bottom:var(--lg);">10Projects</h1>
	<p style="color:var(--gray-500);font-size:16px;">AI-powered real estate discovery for Navi Mumbai</p>
	<p style="margin-top:var(--xl);">
		<a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>" class="tp-btn tp-btn--primary">Browse Projects</a>
	</p>
</div>

<?php get_footer(); ?>
