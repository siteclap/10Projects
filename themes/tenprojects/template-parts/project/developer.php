<?php
/**
 * About Developer Section — Simplified
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id        = $args['post_id'] ?? get_the_ID();
$dev_name       = tp_get_meta( $post_id, 'developer_name' );
$dev_logo       = tp_get_developer_logo( $post_id );
$google_rating  = tp_get_meta( $post_id, 'google_review_rating' );
$overview       = tp_get_meta( $post_id, 'developer_about' );
$qr_urls        = tp_get_qr_urls( $post_id );
$rera            = tp_get_meta( $post_id, 'rera_number' );
$offers         = tp_parse_json_meta( $post_id, 'offers' );

if ( ! $dev_name ) return;
?>

<section class="tp-section" id="developer">
	<h2>About <?php echo esc_html( $dev_name ); ?></h2>

	<div class="tp-dev-card">
		<div class="tp-dev-card__header">
			<div class="tp-dev-card__header-left">
				<?php if ( $dev_logo ) : ?>
					<img src="<?php echo esc_url( $dev_logo ); ?>" alt="<?php echo esc_attr( $dev_name ); ?>" class="tp-dev-card__logo">
				<?php else : ?>
					<div class="tp-dev-card__initials">
						<?php echo esc_html( strtoupper( substr( $dev_name, 0, 2 ) ) ); ?>
					</div>
				<?php endif; ?>
				<div>
					<div class="tp-dev-card__name"><?php echo esc_html( $dev_name ); ?></div>
					<?php if ( $google_rating ) : ?>
						<div class="tp-dev-card__rating">
							<svg width="14" height="14" fill="var(--accent)" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
							<?php echo esc_html( $google_rating ); ?> Google Rating
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( ! empty( $qr_urls ) ) :
					$rera_url = $rera ? 'https://maharera.maharashtra.gov.in/projects-search-result?certificate_no=' . rawurlencode( $rera ) : '';
				?>
				<a href="<?php echo esc_url( $rera_url ); ?>" target="_blank" rel="noopener noreferrer" class="tp-dev-card__qr"<?php echo ! $rera_url ? ' onclick="return false;"' : ''; ?>>
					<?php foreach ( $qr_urls as $qr ) : ?>
						<img src="<?php echo esc_url( $qr ); ?>" alt="RERA QR Code">
					<?php endforeach; ?>
					<span class="tp-dev-card__qr-label">RERA QR</span>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( $overview ) : ?>
			<div class="tp-dev-card__overview">
				<?php echo wp_kses_post( $overview ); ?>
			</div>
		<?php endif; ?>
	</div>

</section>
