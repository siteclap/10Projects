<?php
/**
 * Listing Card — Shared horizontal card for search, location & property-type archives.
 *
 * Args (via get_template_part $args):
 *   post_id       int     Required. Post ID.
 *   location_name string  Optional. Override location label (taxonomy pages pass their term name).
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id      = $args['post_id']       ?? get_the_ID();
$loc_override = array_key_exists( 'location_name', $args ) ? $args['location_name'] : null;

$title        = get_the_title( $post_id );
$permalink    = get_permalink( $post_id );
$p_dev        = tp_get_meta( $post_id, 'developer_name' );
$p_dev_logo   = tp_get_developer_logo( $post_id );
$p_min        = floatval( tp_get_meta( $post_id, 'price_display_min' ) );
$p_max        = floatval( tp_get_meta( $post_id, 'price_display_max' ) );
$p_configs    = tp_get_meta( $post_id, 'available_configs_text' );
$p_stage      = tp_get_meta( $post_id, 'construction_stage' );
$p_possession = tp_get_meta( $post_id, 'expected_possession' );
$p_floors     = tp_get_meta( $post_id, 'floors_display' );
$p_rera        = tp_get_meta( $post_id, 'rera_number' );
$p_land_parcel = tp_get_meta( $post_id, 'land_parcel' );

// Image — prefer first banner, fall back to featured image.
$p_banner_urls = tp_get_banner_urls( $post_id, 'desktop' );
$p_img         = ! empty( $p_banner_urls ) ? $p_banner_urls[0] : get_the_post_thumbnail_url( $post_id, 'medium_large' );

// Location label.
if ( null === $loc_override ) {
	$p_loc    = tp_get_location_term( $post_id );
	$loc_name = $p_loc ? $p_loc->name : '';
} else {
	$loc_name = $loc_override;
}

// Stage pill colour modifier.
$stage_mod = 'tp-lp-card__pill--neutral';
if ( $p_stage ) {
	if ( stripos( $p_stage, 'ready' ) !== false ) {
		$stage_mod = 'tp-lp-card__pill--success';
	} elseif ( stripos( $p_stage, 'launch' ) !== false || stripos( $p_stage, 'pre' ) !== false ) {
		$stage_mod = 'tp-lp-card__pill--amber';
	} else {
		$stage_mod = 'tp-lp-card__pill--blue';
	}
}
?>

<div class="tp-lp-card">

	<!-- Image panel -->
	<a href="<?php echo esc_url( $permalink ); ?>" class="tp-lp-card__img-link" aria-hidden="true" tabindex="-1">
		<div class="tp-lp-card__img">
			<?php if ( $p_img ) : ?>
				<img src="<?php echo esc_url( $p_img ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
			<?php else : ?>
				<div class="tp-lp-card__img-placeholder">
					<svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
				</div>
			<?php endif; ?>
			<?php if ( $p_stage ) : ?>
				<span class="tp-lp-card__img-badge<?php echo $p_stage === 'Ready to Move' ? ' tp-lp-card__img-badge--green' : ''; ?>">
					<?php echo esc_html( tp_format_stage( $p_stage ) ); ?>
				</span>
			<?php endif; ?>
		</div>
	</a>

	<!-- Content panel -->
	<div class="tp-lp-card__body">

		<!-- 1. Price -->
		<div class="tp-lp-card__price">
			<?php if ( $p_min ) : ?>
				<?php echo esc_html( tp_format_price_range( $p_min, $p_max ) ); ?>
			<?php else : ?>
				Price on Request
			<?php endif; ?>
		</div>

		<!-- 2. Title + Developer -->
		<div class="tp-lp-card__title-row">
			<?php if ( $p_dev_logo ) : ?>
				<img src="<?php echo esc_url( $p_dev_logo ); ?>" alt="<?php echo esc_attr( $p_dev ); ?>" class="tp-lp-card__dev-logo">
			<?php endif; ?>
			<div class="tp-lp-card__title-meta">
				<a href="<?php echo esc_url( $permalink ); ?>" class="tp-lp-card__title"><?php echo esc_html( $title ); ?></a>
				<?php if ( $p_dev ) : ?>
					<span class="tp-lp-card__dev">by <?php echo esc_html( $p_dev ); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<!-- 3. Tagline -->
		<?php if ( $p_configs ) : ?>
			<p class="tp-lp-card__tagline">
				<?php echo esc_html( $p_configs ); ?>
				<?php if ( $loc_name ) : ?>
					for sale in <?php echo esc_html( $loc_name ); ?>, Navi Mumbai
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<!-- 4. Highlight pills -->
		<?php if ( $p_possession || $p_stage || $p_floors ) : ?>
			<div class="tp-lp-card__pills">
				<?php if ( $p_possession ) : ?>
					<span class="tp-lp-card__pill tp-lp-card__pill--neutral">
						<svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
						<?php echo esc_html( tp_format_possession( $p_possession ) ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $p_stage ) : ?>
					<span class="tp-lp-card__pill <?php echo esc_attr( $stage_mod ); ?>">
						<svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
						<?php echo esc_html( tp_format_stage( $p_stage ) ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $p_floors ) : ?>
					<span class="tp-lp-card__pill tp-lp-card__pill--neutral">
						<svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
						<?php echo esc_html( $p_floors ); ?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- 5. Short Overview toggle (dynamically generated) -->
		<?php
		$dyn = array();

		// Sentence 1: project name + configs + location.
		$s1 = $title . ' is an exclusive residential project';
		if ( $p_configs && $loc_name ) {
			$s1 .= ' offering ' . $p_configs . ' for sale in ' . $loc_name . ', Navi Mumbai.';
		} elseif ( $p_configs ) {
			$s1 .= ' offering ' . $p_configs . '.';
		} elseif ( $loc_name ) {
			$s1 .= ' located in ' . $loc_name . ', Navi Mumbai.';
		} else {
			$s1 .= '.';
		}
		$dyn[] = $s1;

		// Sentence 2: land parcel.
		if ( $p_land_parcel ) {
			$dyn[] = 'Spread across ' . $p_land_parcel . ', this project is designed to cater to the lifestyle and space requirements of modern families.';
		} else {
			$dyn[] = 'This project is designed to cater to the lifestyle and space requirements of modern families.';
		}

		// Sentence 3: stage + possession.
		if ( $p_stage || $p_possession ) {
			$s3 = '';
			if ( $p_stage ) {
				$s3 .= 'It is currently ' . tp_format_stage( $p_stage ) . '. ';
			}
			if ( $p_possession ) {
				$s3 .= $title . ' is expected to be ready for possession by ' . tp_format_possession( $p_possession ) . '.';
			}
			$dyn[] = trim( $s3 );
		}

		$dynamic_overview = implode( ' ', array_filter( $dyn ) );
		?>
		<?php if ( $dynamic_overview ) : ?>
			<div class="tp-lp-card__overview">
				<button class="tp-lp-card__overview-toggle" type="button" aria-expanded="false">
					Short Overview
					<svg class="tp-lp-card__chevron" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 9l-7 7-7-7"/></svg>
				</button>
				<p class="tp-lp-card__overview-text">
					<?php echo esc_html( $dynamic_overview ); ?>
				</p>
			</div>
		<?php endif; ?>

		<!-- 6. Footer: CTAs + RERA -->
		<div class="tp-lp-card__footer">
			<div class="tp-lp-card__ctas">
				<a href="<?php echo esc_url( $permalink ); ?>" class="tp-lp-card__btn tp-lp-card__btn--outline">View Details</a>
				<button type="button" class="tp-lp-card__btn tp-lp-card__btn--primary js-open-lead-popup" data-source="listing_card">
					<svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
					Get Best Price
				</button>
			</div>
			<?php if ( $p_rera ) : ?>
				<span class="tp-lp-card__rera">RERA: <?php echo esc_html( $p_rera ); ?></span>
			<?php endif; ?>
		</div>

	</div><!-- /.tp-lp-card__body -->
</div><!-- /.tp-lp-card -->
