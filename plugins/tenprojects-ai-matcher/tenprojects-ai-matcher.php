<?php
/**
 * Plugin Name: 10Projects AI Matcher
 * Plugin URI: https://10projects.com
 * Description: AI-powered real estate matching engine — scoring, recommendations, lead routing, and assessment chat.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: 10Projects
 * Author URI: https://10projects.com
 * License: Proprietary
 * Text Domain: tenprojects-ai-matcher
 * Domain Path: /languages
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'TP_PLUGIN_VERSION', '1.0.0' );
define( 'TP_PLUGIN_FILE', __FILE__ );
define( 'TP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'TP_DB_VERSION', '1.0.0' );

// Minimum requirements.
define( 'TP_MIN_PHP', '8.0' );
define( 'TP_MIN_WP', '6.4' );

/**
 * Autoloader for plugin classes.
 *
 * Maps class names to file paths following the convention:
 * TenProjects\CPT\Project_CPT → includes/cpt/class-project-cpt.php
 * TenProjects\Services\Scoring_Engine → includes/services/class-scoring-engine.php
 *
 * @param string $class Full class name.
 */
spl_autoload_register( function ( $class ) {
    $prefix = 'TenProjects\\';

    if ( strpos( $class, $prefix ) !== 0 ) {
        return;
    }

    // Remove prefix.
    $relative = substr( $class, strlen( $prefix ) );

    // Convert namespace to directory path.
    $parts    = explode( '\\', $relative );
    $filename = array_pop( $parts );

    // Convert CamelCase class name to kebab-case filename.
    $filename = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', $filename ) );
    $filename = 'class-' . $filename . '.php';

    // Convert namespace parts to lowercase directories.
    $subdir = '';
    if ( ! empty( $parts ) ) {
        $subdir = strtolower( implode( '/', $parts ) ) . '/';
    }

    $file = TP_PLUGIN_DIR . 'includes/' . $subdir . $filename;

    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

/**
 * Check requirements before activation.
 */
function tp_check_requirements() {
    $errors = array();

    if ( version_compare( PHP_VERSION, TP_MIN_PHP, '<' ) ) {
        $errors[] = sprintf(
            '10Projects AI Matcher requires PHP %s or higher. You are running PHP %s.',
            TP_MIN_PHP,
            PHP_VERSION
        );
    }

    if ( version_compare( get_bloginfo( 'version' ), TP_MIN_WP, '<' ) ) {
        $errors[] = sprintf(
            '10Projects AI Matcher requires WordPress %s or higher.',
            TP_MIN_WP
        );
    }

    return $errors;
}

/**
 * Activation hook.
 */
function tp_activate() {
    $errors = tp_check_requirements();
    if ( ! empty( $errors ) ) {
        wp_die( implode( '<br>', $errors ), 'Plugin Activation Error', array( 'back_link' => true ) );
    }

    require_once TP_PLUGIN_DIR . 'includes/class-activator.php';
    TenProjects\Activator::activate();
}
register_activation_hook( __FILE__, 'tp_activate' );

/**
 * Deactivation hook.
 */
function tp_deactivate() {
    require_once TP_PLUGIN_DIR . 'includes/class-deactivator.php';
    TenProjects\Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'tp_deactivate' );

/**
 * Initialize the plugin.
 */
function tp_init() {
    $errors = tp_check_requirements();
    if ( ! empty( $errors ) ) {
        add_action( 'admin_notices', function () use ( $errors ) {
            echo '<div class="notice notice-error"><p>' . implode( '<br>', array_map( 'esc_html', $errors ) ) . '</p></div>';
        } );
        return;
    }

    $plugin = new TenProjects\Plugin();
    $plugin->init();
}
add_action( 'plugins_loaded', 'tp_init' );
