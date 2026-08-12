<?php
/**
 * Project Brochure — Clean PDF-ready page
 * Loaded via ?brochure=1 on any tp_project single page.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id      = get_the_ID();
$title        = get_the_title();
$location     = tp_get_location_term( $post_id );
$loc_name     = $location ? $location->name : '';
$price_min    = intval( tp_get_meta( $post_id, 'price_display_min' ) );
$price_max    = intval( tp_get_meta( $post_id, 'price_display_max' ) );
$rera         = tp_get_meta( $post_id, 'rera_number' );
$stage        = tp_get_meta( $post_id, 'construction_stage' );
$possession   = tp_get_meta( $post_id, 'expected_possession' );
$developer    = tp_get_meta( $post_id, 'developer_name' ) ?: $title;
$configs_text = tp_get_meta( $post_id, 'available_configs_text' );
$thumbnail    = get_the_post_thumbnail_url( $post_id, 'large' );
$amenities    = tp_parse_json_meta( $post_id, 'highlights' );
$pros         = tp_parse_json_meta( $post_id, 'pros' );
$overview     = tp_get_meta( $post_id, 'overview_description' );
$url          = get_permalink( $post_id );
$site_url     = home_url( '/' );

// Configs from DB
global $wpdb;
$table = $wpdb->prefix . 'tp_project_configurations';
$configs = array();
if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table ) {
	$configs = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE project_id = %d ORDER BY price_min ASC",
		$post_id
	) );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( $title ); ?> — Project Brochure | 10Projects</title>
	<meta name="robots" content="noindex, nofollow">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<style>
		*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
		body { font-family: 'Inter', -apple-system, system-ui, sans-serif; color: #111827; background: #f9fafb; -webkit-font-smoothing: antialiased; }
		a { color: #1A56DB; text-decoration: none; }

		.brochure { max-width: 800px; margin: 0 auto; background: #fff; }

		/* Header */
		.br-header {
			background: linear-gradient(135deg, #1A56DB 0%, #1E40AF 100%);
			color: #fff;
			padding: 32px 40px;
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
		}
		.br-header__logo { font-size: 22px; font-weight: 700; }
		.br-header__logo span { opacity: 0.7; font-weight: 400; font-size: 13px; display: block; margin-top: 2px; }
		.br-header__meta { text-align: right; font-size: 12px; opacity: 0.8; }

		/* Hero */
		.br-hero { position: relative; }
		.br-hero img { width: 100%; height: 280px; object-fit: cover; display: block; }
		.br-hero__overlay {
			position: absolute; bottom: 0; left: 0; right: 0;
			background: linear-gradient(transparent, rgba(0,0,0,0.7));
			padding: 24px 40px;
			color: #fff;
		}
		.br-hero__title { font-size: 26px; font-weight: 700; line-height: 1.3; }
		.br-hero__loc { font-size: 14px; opacity: 0.85; margin-top: 4px; }

		/* Body */
		.br-body { padding: 32px 40px; }

		/* Quick facts strip */
		.br-facts {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
			gap: 16px;
			padding: 20px;
			background: #f8fafc;
			border: 1px solid #e5e7eb;
			border-radius: 10px;
			margin-bottom: 28px;
		}
		.br-fact-label { font-size: 10px; text-transform: uppercase; color: #9ca3af; font-weight: 600; letter-spacing: 0.5px; }
		.br-fact-value { font-size: 14px; font-weight: 600; color: #111827; margin-top: 2px; }
		.br-fact-value.price { color: #1A56DB; font-size: 16px; }

		/* Section */
		.br-section { margin-bottom: 24px; }
		.br-section-title { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #1A56DB; display: inline-block; }

		/* Overview */
		.br-overview { font-size: 14px; line-height: 1.7; color: #4b5563; }

		/* Config table */
		.br-table { width: 100%; border-collapse: collapse; font-size: 13px; }
		.br-table th { background: #f3f4f6; padding: 10px 14px; text-align: left; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
		.br-table td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; }
		.br-table tr:last-child td { border-bottom: none; }
		.br-table .price { font-weight: 600; color: #1A56DB; }
		.br-table .status { font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; }
		.br-table .status--available { background: #D1FAE5; color: #065F46; }
		.br-table .status--sold { background: #FEE2E2; color: #991B1B; }

		/* Amenities */
		.br-amenities { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
		.br-amenity { font-size: 13px; color: #374151; padding: 6px 0; display: flex; align-items: center; gap: 6px; }
		.br-amenity::before { content: ''; width: 6px; height: 6px; background: #1A56DB; border-radius: 50%; flex-shrink: 0; }

		/* Pros */
		.br-pros { list-style: none; }
		.br-pros li { font-size: 13px; color: #374151; padding: 5px 0; padding-left: 20px; position: relative; }
		.br-pros li::before { content: '✓'; position: absolute; left: 0; color: #10B981; font-weight: 700; }

		/* CTA Banner */
		.br-cta {
			background: linear-gradient(135deg, #1A56DB 0%, #1E40AF 100%);
			color: #fff;
			padding: 28px 40px;
			text-align: center;
		}
		.br-cta__title { font-size: 18px; font-weight: 700; margin-bottom: 6px; }
		.br-cta__sub { font-size: 13px; opacity: 0.85; margin-bottom: 16px; }
		.br-cta__link {
			display: inline-block;
			background: #fff;
			color: #1A56DB;
			font-weight: 600;
			padding: 10px 28px;
			border-radius: 8px;
			font-size: 14px;
		}

		/* Footer */
		.br-footer {
			padding: 20px 40px;
			background: #f9fafb;
			border-top: 1px solid #e5e7eb;
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 12px;
			color: #9ca3af;
		}
		.br-footer a { color: #1A56DB; font-weight: 600; }

		/* Download button (hidden in print) */
		.br-download {
			position: fixed;
			bottom: 24px;
			right: 24px;
			background: #1A56DB;
			color: #fff;
			border: none;
			padding: 12px 24px;
			border-radius: 10px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			box-shadow: 0 4px 16px rgba(26,86,219,0.35);
			display: flex;
			align-items: center;
			gap: 8px;
			font-family: inherit;
			z-index: 100;
		}
		.br-download:hover { background: #1E40AF; }

		@media print {
			body { background: #fff; }
			.br-download { display: none !important; }
			.brochure { box-shadow: none; }
			.br-hero img { height: 200px; }
		}

		@media (max-width: 600px) {
			.br-header, .br-body, .br-cta, .br-footer { padding-left: 20px; padding-right: 20px; }
			.br-hero img { height: 180px; }
			.br-hero__overlay { padding: 16px 20px; }
			.br-hero__title { font-size: 20px; }
			.br-amenities { grid-template-columns: repeat(2, 1fr); }
			.br-facts { grid-template-columns: repeat(2, 1fr); }
		}
	</style>
</head>
<body>

<button class="br-download" onclick="window.print()">
	<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
	Download PDF
</button>

<div class="brochure">

	<!-- Header -->
	<div class="br-header">
		<div>
			<div class="br-header__logo">
				10Projects
				<span>Your Home, Our Mission</span>
			</div>
		</div>
		<div class="br-header__meta">
			Project Brochure<br>
			<?php echo date( 'F Y' ); ?>
		</div>
	</div>

	<!-- Hero Image -->
	<?php if ( $thumbnail ) : ?>
	<div class="br-hero">
		<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $title ); ?>">
		<div class="br-hero__overlay">
			<div class="br-hero__title"><?php echo esc_html( $title ); ?></div>
			<?php if ( $loc_name ) : ?>
				<div class="br-hero__loc"><?php echo esc_html( $loc_name ); ?>, Navi Mumbai</div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<div class="br-body">

		<!-- Quick Facts -->
		<div class="br-facts">
			<?php if ( $price_min ) : ?>
			<div>
				<div class="br-fact-label">Price</div>
				<div class="br-fact-value price"><?php echo esc_html( tp_format_price_range( $price_min, $price_max ) ); ?></div>
			</div>
			<?php endif; ?>
			<?php if ( $configs_text ) : ?>
			<div>
				<div class="br-fact-label">Configurations</div>
				<div class="br-fact-value"><?php echo esc_html( $configs_text ); ?></div>
			</div>
			<?php endif; ?>
			<?php if ( $stage ) : ?>
			<div>
				<div class="br-fact-label">Status</div>
				<div class="br-fact-value"><?php echo esc_html( tp_format_stage( $stage ) ); ?></div>
			</div>
			<?php endif; ?>
			<?php if ( $possession ) : ?>
			<div>
				<div class="br-fact-label">Possession</div>
				<div class="br-fact-value"><?php echo esc_html( tp_format_possession( $possession ) ); ?></div>
			</div>
			<?php endif; ?>
			<div>
				<div class="br-fact-label">Developer</div>
				<div class="br-fact-value"><?php echo esc_html( $developer ); ?></div>
			</div>
			<?php if ( $rera ) : ?>
			<div>
				<div class="br-fact-label">RERA</div>
				<div class="br-fact-value"><?php echo esc_html( $rera ); ?></div>
			</div>
			<?php endif; ?>
		</div>

		<!-- Overview -->
		<?php if ( $overview ) : ?>
		<div class="br-section">
			<div class="br-section-title">About <?php echo esc_html( $title ); ?></div>
			<div class="br-overview"><?php echo wp_kses_post( $overview ); ?></div>
		</div>
		<?php endif; ?>

		<!-- Configurations Table -->
		<?php if ( ! empty( $configs ) ) : ?>
		<div class="br-section">
			<div class="br-section-title">Price & Configuration</div>
			<table class="br-table">
				<thead>
					<tr>
						<th>Type</th>
						<th>Carpet Area</th>
						<th>Price</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $configs as $c ) :
						$status = $c->inventory_status ?? 'Available';
						$is_sold = $status === 'Sold Out';
					?>
					<tr>
						<td><?php echo esc_html( $c->configuration ); ?></td>
						<td><?php echo esc_html( $c->carpet_area_min ); ?><?php echo $c->carpet_area_max && $c->carpet_area_max != $c->carpet_area_min ? ' – ' . esc_html( $c->carpet_area_max ) : ''; ?> sq.ft.</td>
						<td class="price"><?php echo esc_html( tp_format_price_range( $c->price_min, $c->price_max ) ); ?></td>
						<td><span class="status <?php echo $is_sold ? 'status--sold' : 'status--available'; ?>"><?php echo esc_html( $status ); ?></span></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>

		<!-- Amenities -->
		<?php if ( ! empty( $amenities ) ) : ?>
		<div class="br-section">
			<div class="br-section-title">Key Amenities</div>
			<div class="br-amenities">
				<?php foreach ( array_slice( $amenities, 0, 15 ) as $a ) : ?>
					<div class="br-amenity"><?php echo esc_html( $a ); ?></div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<!-- Why this project -->
		<?php if ( ! empty( $pros ) ) : ?>
		<div class="br-section">
			<div class="br-section-title">Why <?php echo esc_html( $title ); ?>?</div>
			<ul class="br-pros">
				<?php foreach ( $pros as $p ) : ?>
					<li><?php echo esc_html( $p ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

	</div>

	<!-- CTA Banner -->
	<div class="br-cta">
		<div class="br-cta__title">Interested in <?php echo esc_html( $title ); ?>?</div>
		<div class="br-cta__sub">Get the best price, free site visit, and zero brokerage</div>
		<a href="<?php echo esc_url( $url ); ?>" class="br-cta__link">View Full Details on 10Projects.com</a>
	</div>

	<!-- Footer -->
	<div class="br-footer">
		<span>Generated by <a href="<?php echo esc_url( $site_url ); ?>">10Projects.com</a></span>
		<span>Lowest Price Guaranteed | Zero Brokerage</span>
	</div>

</div>

</body>
</html>
