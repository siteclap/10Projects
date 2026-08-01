<?php
/**
 * Server-side render for Fit Score Badge block.
 *
 * @package TenProjects
 * @since 1.0.0
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$score  = absint( $attributes['score'] ?? 0 );
$size   = sanitize_key( $attributes['size'] ?? 'medium' );
$static = (bool) ( $attributes['static'] ?? true );

if ( $score <= 0 ) {
    return;
}

// Map block size attribute to template function size parameter.
$size_map = array(
    'small'  => 'default',
    'medium' => 'default',
    'large'  => 'large',
);
$template_size = $size_map[ $size ] ?? 'default';

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'wp-block-tenprojects-fit-score fit-score--' . $size,
) );

?>
<div <?php echo $wrapper_attributes; ?>>
    <?php
    if ( function_exists( 'tp_fit_score_badge' ) ) {
        echo tp_fit_score_badge( $score, $static, $template_size );
    } else {
        printf(
            '<div class="fit-badge fit-badge--static"><span class="fit-badge__score">%d</span><span class="fit-badge__label">Fit</span></div>',
            $score
        );
    }
    ?>
</div>
