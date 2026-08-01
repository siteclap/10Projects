<?php
/**
 * Template helper functions for 10Projects theme.
 *
 * @package TenProjects
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Format price in Indian number system (lakhs/crores).
 *
 * @param int    $amount Amount in rupees.
 * @param string $suffix Optional suffix like '/sqft'.
 * @return string Formatted price.
 */
function tp_format_price( $amount, $suffix = '' ) {
    if ( $amount <= 0 ) {
        return 'Price on request';
    }

    if ( $amount >= 10000000 ) {
        $formatted = number_format( $amount / 10000000, 2 ) . ' Cr';
    } elseif ( $amount >= 100000 ) {
        $formatted = number_format( $amount / 100000, 2 ) . ' L';
    } else {
        $formatted = number_format( $amount );
    }

    // Remove trailing zeros: "1.00 Cr" → "1 Cr", "1.50 Cr" → "1.50 Cr"
    $formatted = preg_replace( '/\.00\b/', '', $formatted );

    $price = '₹' . $formatted;
    if ( $suffix ) {
        $price .= $suffix;
    }

    return $price;
}

/**
 * Format price range.
 *
 * @param int $min Min price.
 * @param int $max Max price.
 * @return string Formatted range.
 */
function tp_format_price_range( $min, $max ) {
    if ( $min === $max || ! $max ) {
        return tp_format_price( $min );
    }
    return tp_format_price( $min ) . ' – ' . tp_format_price( $max );
}

/**
 * Calculate and format EMI.
 *
 * @param int   $principal  Loan amount.
 * @param float $rate       Annual interest rate (e.g. 8.5).
 * @param int   $tenure     Tenure in years (default 20).
 * @return string Formatted EMI string.
 */
function tp_format_emi( $principal, $rate = 8.5, $tenure = 20 ) {
    if ( $principal <= 0 ) {
        return '';
    }

    // Assume 80% loan.
    $loan_amount    = $principal * 0.8;
    $monthly_rate   = ( $rate / 100 ) / 12;
    $total_months   = $tenure * 12;

    if ( $monthly_rate <= 0 ) {
        $emi = $loan_amount / $total_months;
    } else {
        $emi = $loan_amount * $monthly_rate * pow( 1 + $monthly_rate, $total_months ) / ( pow( 1 + $monthly_rate, $total_months ) - 1 );
    }

    return 'EMI ~₹' . number_format( round( $emi ) ) . '/mo';
}

/**
 * Get Fit Score badge HTML.
 *
 * @param int    $score      Fit score 0-100.
 * @param bool   $static     Whether to render in static (non-absolute) position.
 * @param string $size       Size variant: 'default' or 'large'.
 * @return string HTML output.
 */
function tp_fit_score_badge( $score, $static = false, $size = 'default' ) {
    $score = intval( $score );

    // Determine variant class.
    $variant = '';
    if ( $score >= 90 ) {
        $variant = ''; // Default gold.
    } elseif ( $score >= 80 ) {
        $variant = ''; // Still gold.
    } elseif ( $score >= 70 ) {
        $variant = ' fit-badge--good';
    } elseif ( $score >= 60 ) {
        $variant = ' fit-badge--moderate';
    } else {
        $variant = ' fit-badge--weak';
    }

    $static_class = $static ? ' fit-badge--static' : '';
    $size_class   = $size === 'large' ? ' fit-badge--large' : '';

    return sprintf(
        '<div class="fit-badge%s%s%s">
            <span class="fit-badge__score">%d</span>
            <span class="fit-badge__label">Fit</span>
        </div>',
        $variant,
        $static_class,
        $size_class,
        $score
    );
}

/**
 * Get construction stage label.
 *
 * @param string $stage Stage key.
 * @return string Human-readable label.
 */
function tp_construction_stage_label( $stage ) {
    $labels = array(
        'new_launch'  => 'New Launch',
        'foundation'  => 'Foundation',
        'structure'   => 'Structure',
        'finishing'   => 'Finishing',
        'ready'       => 'Ready to Move',
    );
    return $labels[ $stage ] ?? ucfirst( str_replace( '_', ' ', $stage ) );
}

/**
 * Get possession timeline label.
 *
 * @param string $date Expected possession date (Y-m-d).
 * @return string Human-readable label.
 */
function tp_possession_label( $date ) {
    if ( ! $date ) {
        return 'Contact for details';
    }

    $timestamp = strtotime( $date );
    if ( ! $timestamp ) {
        return $date;
    }

    if ( $timestamp <= time() ) {
        return 'Ready to Move';
    }

    $months = (int) ( ( $timestamp - time() ) / ( 30 * 24 * 60 * 60 ) );

    if ( $months <= 6 ) {
        return 'Nearing Possession';
    }

    return date( 'M Y', $timestamp );
}

/**
 * Render SVG icon inline.
 *
 * @param string $name Icon name.
 * @param int    $size Icon size in px.
 * @return string SVG HTML.
 */
function tp_icon( $name, $size = 16 ) {
    $icons = array(
        'pin' => '<svg width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'search' => '<svg width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
        'heart' => '<svg width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
        'chevron-down' => '<svg width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>',
        'check' => '<svg width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
        'star' => '<svg width="%d" height="%d" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'share' => '<svg width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
        'phone' => '<svg width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    );

    if ( ! isset( $icons[ $name ] ) ) {
        return '';
    }

    return sprintf( $icons[ $name ], $size, $size );
}

/**
 * Get project card HTML.
 *
 * @param int   $post_id   Project post ID.
 * @param array $options   Display options.
 * @return string Card HTML.
 */
function tp_project_card( $post_id, $options = array() ) {
    $defaults = array(
        'show_fit_score'  => false,
        'fit_score'       => 0,
        'show_advisor'    => true,
        'show_save'       => true,
        'rank'            => 0,
    );
    $opts = wp_parse_args( $options, $defaults );

    $title     = get_the_title( $post_id );
    $permalink = get_permalink( $post_id );
    $stage     = get_post_meta( $post_id, '_tp_construction_stage', true );
    $config    = get_post_meta( $post_id, '_tp_primary_config', true );

    // Get price from configurations table (will be implemented in plugin).
    $price_min = get_post_meta( $post_id, '_tp_price_display_min', true );
    $price_max = get_post_meta( $post_id, '_tp_price_display_max', true );

    // Location.
    $locations = wp_get_post_terms( $post_id, 'tp_location_area', array( 'fields' => 'names' ) );
    $location  = is_array( $locations ) && ! empty( $locations ) ? $locations[0] : '';

    ob_start();
    ?>
    <div class="project-card">
        <a href="<?php echo esc_url( $permalink ); ?>" class="project-card__image">
            <?php if ( has_post_thumbnail( $post_id ) ) : ?>
                <?php echo get_the_post_thumbnail( $post_id, 'tp-card-thumb' ); ?>
            <?php else : ?>
                Project Image
            <?php endif; ?>

            <div class="project-card__badges">
                <?php if ( $opts['rank'] ) : ?>
                    <span class="project-card__badge project-card__badge--rank">#<?php echo intval( $opts['rank'] ); ?></span>
                <?php endif; ?>
                <?php if ( get_post_meta( $post_id, '_tp_verified', true ) ) : ?>
                    <span class="project-card__badge project-card__badge--verified">10P Verified</span>
                <?php endif; ?>
                <?php if ( $stage === 'new_launch' ) : ?>
                    <span class="project-card__badge project-card__badge--new">New Launch</span>
                <?php endif; ?>
            </div>

            <?php if ( $opts['show_save'] ) : ?>
                <button class="project-card__save" data-project-id="<?php echo intval( $post_id ); ?>" aria-label="Save project"><?php echo tp_icon( 'heart', 18 ); ?></button>
            <?php endif; ?>

            <?php if ( $opts['show_fit_score'] && $opts['fit_score'] > 0 ) : ?>
                <?php echo tp_fit_score_badge( $opts['fit_score'] ); ?>
            <?php endif; ?>
        </a>

        <div class="project-card__body">
            <?php if ( $config ) : ?>
                <div class="project-card__config"><?php echo esc_html( $config ); ?></div>
            <?php endif; ?>

            <?php if ( $price_min ) : ?>
                <div class="project-card__price"><?php echo esc_html( tp_format_price_range( $price_min, $price_max ) ); ?></div>
                <div class="project-card__emi"><?php echo esc_html( tp_format_emi( $price_min ) ); ?></div>
            <?php endif; ?>

            <div class="project-card__name"><?php echo esc_html( $title ); ?></div>

            <?php if ( $location ) : ?>
                <div class="project-card__location">
                    <?php echo tp_icon( 'pin', 12 ); ?>
                    <?php echo esc_html( $location ); ?>
                </div>
            <?php endif; ?>

            <div class="project-card__tags">
                <?php if ( $stage ) : ?>
                    <span class="project-card__tag project-card__tag--info"><?php echo esc_html( tp_construction_stage_label( $stage ) ); ?></span>
                <?php endif; ?>
                <?php
                $possession = get_post_meta( $post_id, '_tp_expected_possession', true );
                if ( $possession ) : ?>
                    <span class="project-card__tag project-card__tag--positive"><?php echo esc_html( tp_possession_label( $possession ) ); ?></span>
                <?php endif; ?>
            </div>

            <?php if ( $opts['show_advisor'] ) : ?>
                <div class="advisor-card">
                    <div class="advisor-card__avatar">RA</div>
                    <div class="advisor-card__info">
                        <div class="advisor-card__name">Property Advisor</div>
                        <div class="advisor-card__rating">★ 4.8</div>
                    </div>
                    <a href="<?php echo esc_url( $permalink ); ?>" class="advisor-card__cta">View Details</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
