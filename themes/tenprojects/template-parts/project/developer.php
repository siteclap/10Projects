<?php
/**
 * About Developer Section
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id        = $args['post_id'] ?? get_the_ID();
$dev_name       = tp_get_meta( $post_id, 'developer_name' );
$dev_logo       = tp_get_developer_logo( $post_id );
$google_rating  = tp_get_meta( $post_id, 'google_review_rating' );
$overview       = tp_get_meta( $post_id, 'short_overview' );
$qr_urls        = tp_get_qr_urls( $post_id );
$offers         = tp_parse_json_meta( $post_id, 'offers' );
$location       = tp_get_location_term( $post_id );
$loc_name       = $location ? $location->name : '';

// Project details.
$details = array(
	'Project Location'  => tp_get_meta( $post_id, 'project_location' ) ?: $loc_name,
	'Land Parcel'       => tp_get_meta( $post_id, 'land_parcel' ),
	'Floors'            => tp_get_meta( $post_id, 'floors_display' ),
	'Possession'        => tp_get_meta( $post_id, 'expected_possession' ),
	'RERA Number'       => tp_get_meta( $post_id, 'rera_number' ),
	'Configurations'    => tp_get_meta( $post_id, 'available_configs_text' ),
	'Construction'      => tp_get_meta( $post_id, 'construction_stage' ),
	'Price Range'       => tp_format_price_range(
		intval( tp_get_meta( $post_id, 'price_display_min' ) ),
		intval( tp_get_meta( $post_id, 'price_display_max' ) )
	),
);
$details = array_filter( $details );

if ( ! $dev_name ) return;
?>

<section class="tp-section" id="developer">
	<h2>About the Developer</h2>

	<!-- Developer Header -->
	<div class="tp-developer-header">
		<?php if ( $dev_logo ) : ?>
			<img src="<?php echo esc_url( $dev_logo ); ?>" alt="<?php echo esc_attr( $dev_name ); ?>" class="tp-developer-logo">
		<?php else : ?>
			<div class="tp-developer-initials">
				<?php echo esc_html( strtoupper( substr( $dev_name, 0, 2 ) ) ); ?>
			</div>
		<?php endif; ?>
		<div>
			<div class="text-h4"><?php echo esc_html( $dev_name ); ?></div>
			<?php if ( $google_rating ) : ?>
				<div class="text-sm text-gray-500" style="margin-top:2px;">
					⭐ <?php echo esc_html( $google_rating ); ?> Google Rating
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Project Details Grid -->
	<?php if ( ! empty( $details ) ) : ?>
		<div class="tp-details-grid">
			<?php foreach ( $details as $label => $value ) : ?>
				<div>
					<div class="tp-stat__label"><?php echo esc_html( $label ); ?></div>
					<div class="tp-stat__value"><?php echo esc_html( $value ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<!-- Overview -->
	<?php if ( $overview ) : ?>
		<div class="tp-card mb-xl">
			<h3 style="font-size:16px;font-weight:600;margin-bottom:var(--md);">Project Overview</h3>
			<p style="font-size:14px;color:var(--gray-600);line-height:1.7;">
				<?php echo wp_kses_post( $overview ); ?>
			</p>
		</div>
	<?php endif; ?>

	<!-- QR Code -->
	<?php if ( ! empty( $qr_urls ) ) : ?>
		<div class="tp-card mb-xl">
			<h3 style="font-size:16px;font-weight:600;margin-bottom:var(--md);">RERA QR Code</h3>
			<div class="tp-qr-codes">
				<?php foreach ( $qr_urls as $qr ) : ?>
					<img src="<?php echo esc_url( $qr ); ?>" alt="RERA QR Code">
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- Offers -->
	<?php if ( ! empty( $offers ) ) : ?>
		<div class="tp-card tp-card--accent">
			<h3 style="font-size:16px;font-weight:600;margin-bottom:var(--lg);">Current Offers</h3>
			<div class="tp-offers-grid">
				<?php foreach ( $offers as $offer ) : ?>
					<div class="tp-offer-card">
						<span class="tag-icon">🏷️</span>
						<?php echo esc_html( $offer ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
