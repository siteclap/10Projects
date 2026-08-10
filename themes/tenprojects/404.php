<?php
/**
 * 404 template.
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
<title>Page Not Found — <?php bloginfo( 'name' ); ?></title>
<style>
	body { font-family: Inter, system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #F9FAFB; color: #374151; }
	.container { text-align: center; max-width: 480px; padding: 2rem; }
	h1 { font-size: 4rem; font-weight: 800; color: #4B1CB0; margin: 0; }
	p { font-size: 1.125rem; line-height: 1.75; color: #6B7280; }
	a { color: #4B1CB0; text-decoration: none; font-weight: 600; }
	a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="container">
	<h1>404</h1>
	<p>The page you&rsquo;re looking for doesn&rsquo;t exist.</p>
	<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Go to Homepage</a></p>
</div>
</body>
</html>
