<?php
/**
 * Server-side render for Project Card block.
 *
 * @package TenProjects
 * @since 1.0.0
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$project_id = absint( $attributes['project_id'] ?? 0 );
$show_score = (bool) ( $attributes['show_score'] ?? false );
$score      = absint( $attributes['score'] ?? 0 );

if ( ! $project_id || 'publish' !== get_post_status( $project_id ) ) {
    return;
}

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'wp-block-tenprojects-project-card',
) );

$options = array(
    'show_fit_score' => $show_score,
    'fit_score'      => $score,
);

?>
<div <?php echo $wrapper_attributes; ?>>
    <?php
    if ( function_exists( 'tp_project_card' ) ) {
        echo tp_project_card( $project_id, $options );
    } else {
        printf(
            '<div class="project-card"><div class="project-card__body"><div class="project-card__name">%s</div></div></div>',
            esc_html( get_the_title( $project_id ) )
        );
    }
    ?>
</div>
