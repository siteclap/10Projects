<?php
/**
 * Site Header
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;
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
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-logo">
			<svg width="32" height="32" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
				<rect width="40" height="40" rx="10" fill="#4B1CB0"/>
				<text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" fill="white" font-family="Inter, sans-serif" font-weight="800" font-size="18">10</text>
			</svg>
			<span>Projects</span>
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
