<?php
/**
 * Performance optimizations for 10Projects theme.
 *
 * @package TenProjects
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add resource hints for critical assets.
 */
function tenprojects_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type ) {
        $urls[] = array(
            'href'        => 'https://fonts.googleapis.com',
            'crossorigin' => 'anonymous',
        );
        $urls[] = array(
            'href'        => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
    }
    return $urls;
}
add_filter( 'wp_resource_hints', 'tenprojects_resource_hints', 10, 2 );

/**
 * Add fetchpriority to hero images.
 */
function tenprojects_hero_image_priority( $attr, $attachment, $size ) {
    if ( is_front_page() && $size === 'tp-gallery-large' ) {
        $attr['fetchpriority'] = 'high';
        $attr['decoding']      = 'async';
    }
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'tenprojects_hero_image_priority', 10, 3 );

/**
 * Defer non-critical scripts.
 */
function tenprojects_defer_scripts( $tag, $handle, $src ) {
    $defer_handles = array(
        'tenprojects-ai-chat',
        'tenprojects-analytics',
        'tenprojects-comparison',
        'tenprojects-project-actions',
    );

    if ( in_array( $handle, $defer_handles, true ) ) {
        return str_replace( ' src', ' defer src', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'tenprojects_defer_scripts', 10, 3 );

/**
 * Remove unnecessary WordPress head items.
 */
function tenprojects_cleanup_head() {
    remove_action( 'wp_head', 'wp_generator' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'after_setup_theme', 'tenprojects_cleanup_head' );

/**
 * Add loading="lazy" to iframes.
 */
function tenprojects_lazy_iframes( $content ) {
    if ( is_admin() || is_feed() ) {
        return $content;
    }

    $content = preg_replace(
        '/<iframe((?!.*loading)[^>]*)>/i',
        '<iframe$1 loading="lazy">',
        $content
    );

    return $content;
}
add_filter( 'the_content', 'tenprojects_lazy_iframes' );
