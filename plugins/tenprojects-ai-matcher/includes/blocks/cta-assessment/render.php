<?php
/**
 * Server-side render for Assessment CTA block.
 *
 * @package TenProjects
 * @since 1.0.0
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$heading     = wp_kses_post( $attributes['heading'] ?? __( 'Find Your Perfect Home', 'tenprojects' ) );
$subheading  = wp_kses_post( $attributes['subheading'] ?? '' );
$button_text = esc_html( $attributes['button_text'] ?? __( 'Find My Top 10', 'tenprojects' ) );
$variant     = sanitize_key( $attributes['variant'] ?? 'gradient' );

$start_url = home_url( '/start/' );

$variant_class = 'cta-assessment--' . $variant;

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'cta-assessment ' . $variant_class,
) );

?>
<section <?php echo $wrapper_attributes; ?>>
    <div class="container cta-assessment__inner">
        <?php if ( $heading ) : ?>
            <h2 class="cta-assessment__heading"><?php echo $heading; ?></h2>
        <?php endif; ?>

        <?php if ( $subheading ) : ?>
            <p class="cta-assessment__subheading"><?php echo $subheading; ?></p>
        <?php endif; ?>

        <a href="<?php echo esc_url( $start_url ); ?>" class="cta-assessment__button btn btn--primary btn--lg">
            <?php echo $button_text; ?>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
</section>
