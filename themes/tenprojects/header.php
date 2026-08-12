<?php
/**
 * Site Header — Dynamic brand settings from WP Admin.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$site_name  = get_option( 'tp_brand_name', 'LeadMAAXX' );
$logo_light = get_option( 'tp_brand_logo_light', '' );
$logo       = $logo_light ?: get_template_directory_uri() . '/assets/images/logo.png';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="tp-header">
	<div class="tp-header-inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-logo" aria-label="<?php echo esc_attr( $site_name ); ?> home">
			<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" height="48">
		</a>

		<nav class="tp-nav">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
			<a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>">Projects</a>
			<a href="<?php echo esc_url( home_url( '/navi-mumbai/' ) ); ?>">Navi Mumbai</a>
			<a href="<?php echo esc_url( home_url( '/developers/' ) ); ?>">Developers</a>
			<a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>">Guides</a>
		</nav>

		<button class="tp-hamburger" onclick="document.querySelector('.tp-mobile-menu').classList.toggle('active')" aria-label="Menu">
			<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
				<path d="M4 6h16M4 12h16M4 18h16"/>
			</svg>
		</button>
	</div>
</header>

<div class="tp-mobile-menu">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
	<a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>">Projects</a>
	<a href="<?php echo esc_url( home_url( '/navi-mumbai/' ) ); ?>">Navi Mumbai</a>
	<a href="<?php echo esc_url( home_url( '/developers/' ) ); ?>">Developers</a>
	<a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>">Guides</a>
</div>
