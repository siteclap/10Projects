<?php
/**
 * Server-side render for Trust Indicators block.
 *
 * @package TenProjects
 * @since 1.0.0
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$variant = sanitize_key( $attributes['variant'] ?? 'full' );

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'wp-block-tenprojects-trust-indicators',
) );

$trust_items = array(
    array(
        'icon'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
        'title' => __( '100% Honest Analysis', 'tenprojects' ),
        'desc'  => __( 'Every project gets the same unbiased evaluation. No sponsored rankings, no hidden promotions.', 'tenprojects' ),
    ),
    array(
        'icon'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'title' => __( 'RERA Verified', 'tenprojects' ),
        'desc'  => __( 'We verify RERA registration, developer track records, and legal clearances before listing any project.', 'tenprojects' ),
    ),
    array(
        'icon'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
        'title' => __( 'AI-Powered, Human-Verified', 'tenprojects' ),
        'desc'  => __( 'Our AI analyses 20+ parameters per project. Property experts verify data and add on-ground insights.', 'tenprojects' ),
    ),
);

if ( 'compact' === $variant ) : ?>
    <div <?php echo $wrapper_attributes; ?>>
        <div class="trust-bar trust-bar--compact">
            <?php foreach ( $trust_items as $index => $item ) : ?>
                <?php if ( $index > 0 ) : ?>
                    <span class="trust-bar__separator">&bull;</span>
                <?php endif; ?>
                <span class="trust-item trust-item--compact">
                    <span class="trust-item__icon"><?php echo $item['icon']; ?></span>
                    <span class="trust-item__title"><?php echo esc_html( $item['title'] ); ?></span>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
<?php else : ?>
    <div <?php echo $wrapper_attributes; ?>>
        <div class="trust-bar">
            <?php foreach ( $trust_items as $item ) : ?>
                <div class="trust-item">
                    <div class="trust-item__icon"><?php echo $item['icon']; ?></div>
                    <div class="trust-item__title"><?php echo esc_html( $item['title'] ); ?></div>
                    <div class="trust-item__desc"><?php echo esc_html( $item['desc'] ); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
