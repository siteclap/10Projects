<?php
/**
 * 10Projects Theme Functions
 *
 * @package TenProjects
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

define( 'TENPROJECTS_THEME_VERSION', '1.0.0' );
define( 'TENPROJECTS_THEME_DIR', get_template_directory() );
define( 'TENPROJECTS_THEME_URI', get_template_directory_uri() );

/**
 * Theme setup.
 */
function tenprojects_setup() {
    add_theme_support( 'wp-block-template-part' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

    add_image_size( 'tp-card-thumb', 380, 220, true );
    add_image_size( 'tp-gallery-large', 960, 540, true );
    add_image_size( 'tp-location-thumb', 400, 280, true );
    add_image_size( 'tp-developer-logo', 240, 120, false );

    register_nav_menus( array(
        'primary'         => __( 'Primary Navigation', 'tenprojects' ),
        'footer-explore'  => __( 'Footer - Explore', 'tenprojects' ),
        'footer-company'  => __( 'Footer - Company', 'tenprojects' ),
        'footer-legal'    => __( 'Footer - Legal', 'tenprojects' ),
    ) );
}
add_action( 'after_setup_theme', 'tenprojects_setup' );

/**
 * Enqueue frontend styles and scripts.
 */
function tenprojects_enqueue_assets() {
    wp_enqueue_style(
        'tenprojects-global',
        TENPROJECTS_THEME_URI . '/assets/css/global.css',
        array(),
        TENPROJECTS_THEME_VERSION
    );

    wp_enqueue_style(
        'tenprojects-components',
        TENPROJECTS_THEME_URI . '/assets/css/components.css',
        array( 'tenprojects-global' ),
        TENPROJECTS_THEME_VERSION
    );

    // Page-specific CSS.
    if ( is_front_page() ) {
        wp_enqueue_style( 'tenprojects-homepage', TENPROJECTS_THEME_URI . '/assets/css/homepage.css', array( 'tenprojects-components' ), TENPROJECTS_THEME_VERSION );
    }

    if ( is_singular( 'tp_project' ) ) {
        wp_enqueue_style( 'tenprojects-project-detail', TENPROJECTS_THEME_URI . '/assets/css/project-detail.css', array( 'tenprojects-components' ), TENPROJECTS_THEME_VERSION );
    }

    if ( is_page_template( 'page-results' ) || is_page( 'results' ) ) {
        wp_enqueue_style( 'tenprojects-results', TENPROJECTS_THEME_URI . '/assets/css/results.css', array( 'tenprojects-components' ), TENPROJECTS_THEME_VERSION );
    }

    if ( is_page_template( 'page-start' ) || is_page( 'start' ) ) {
        wp_enqueue_style( 'tenprojects-ai-chat', TENPROJECTS_THEME_URI . '/assets/css/ai-chat.css', array( 'tenprojects-components' ), TENPROJECTS_THEME_VERSION );
    }

    // Utility JS.
    wp_enqueue_script(
        'tenprojects-utils',
        TENPROJECTS_THEME_URI . '/assets/js/utils.js',
        array(),
        TENPROJECTS_THEME_VERSION,
        true
    );

    wp_localize_script( 'tenprojects-utils', 'tenprojectsData', array(
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'restUrl'  => rest_url( 'tenprojects/v1/' ),
        'nonce'    => wp_create_nonce( 'wp_rest' ),
        'themeUrl' => TENPROJECTS_THEME_URI,
    ) );

    // Analytics — deferred, non-blocking.
    wp_enqueue_script(
        'tenprojects-analytics',
        TENPROJECTS_THEME_URI . '/assets/js/analytics.js',
        array( 'tenprojects-utils' ),
        TENPROJECTS_THEME_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'tenprojects_enqueue_assets' );

/**
 * Enqueue editor styles.
 */
function tenprojects_editor_assets() {
    wp_enqueue_style(
        'tenprojects-editor',
        TENPROJECTS_THEME_URI . '/assets/css/global.css',
        array(),
        TENPROJECTS_THEME_VERSION
    );
}
add_action( 'enqueue_block_editor_assets', 'tenprojects_editor_assets' );

/**
 * Register block pattern categories.
 */
function tenprojects_register_pattern_categories() {
    register_block_pattern_category( 'tenprojects', array(
        'label' => __( '10Projects', 'tenprojects' ),
    ) );
    register_block_pattern_category( 'tenprojects-homepage', array(
        'label' => __( '10Projects - Homepage', 'tenprojects' ),
    ) );
}
add_action( 'init', 'tenprojects_register_pattern_categories' );

// Include template functions and helpers.
require_once TENPROJECTS_THEME_DIR . '/inc/template-functions.php';
require_once TENPROJECTS_THEME_DIR . '/inc/performance.php';
require_once TENPROJECTS_THEME_DIR . '/inc/seo.php';
require_once TENPROJECTS_THEME_DIR . '/inc/structured-data.php';
