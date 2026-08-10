<?php
/**
 * Standalone migration runner for 10Projects AI Matcher plugin.
 *
 * Bootstraps WordPress and calls the plugin's Activator to create
 * database tables, roles, default options, and taxonomy terms.
 *
 * Usage: Visit this URL with ?key=YOUR_SECRET_KEY
 * IMPORTANT: Delete this file immediately after running.
 *
 * @package TenProjects
 */

// Secret key — change this before uploading.
$secret_key = 'tp_migrate_2024_x9k';

if ( ! isset( $_GET['key'] ) || $_GET['key'] !== $secret_key ) {
    http_response_code( 403 );
    die( 'Forbidden. Provide ?key=YOUR_SECRET_KEY' );
}

// Find wp-load.php — adjust path if needed.
$wp_load_paths = [
    __DIR__ . '/wp-load.php',
    dirname( __DIR__ ) . '/wp-load.php',
    __DIR__ . '/../wp-load.php',
];

$wp_load = null;
foreach ( $wp_load_paths as $path ) {
    if ( file_exists( $path ) ) {
        $wp_load = $path;
        break;
    }
}

if ( ! $wp_load ) {
    die( 'Could not find wp-load.php. Place this file in the WordPress root directory.' );
}

// Bootstrap WordPress.
require_once $wp_load;

header( 'Content-Type: text/html; charset=utf-8' );

echo '<html><head><title>10Projects Migration</title>';
echo '<style>body{font-family:monospace;padding:40px;max-width:800px;margin:0 auto;background:#1a1a2e;color:#e0e0e0;}';
echo '.ok{color:#10B981;}.err{color:#EF4444;}.warn{color:#F59E0B;}.info{color:#60A5FA;}';
echo 'h1{color:#fff;border-bottom:1px solid #333;padding-bottom:10px;}';
echo '.step{margin:8px 0;padding:8px 12px;background:#252540;border-radius:4px;}</style></head><body>';
echo '<h1>10Projects Migration Runner</h1>';

// Check if plugin file exists.
$plugin_file = WP_PLUGIN_DIR . '/tenprojects-ai-matcher/tenprojects-ai-matcher.php';

if ( ! file_exists( $plugin_file ) ) {
    echo '<p class="err">Plugin file not found at: ' . esc_html( $plugin_file ) . '</p>';
    echo '<p class="info">Upload the plugin to wp-content/plugins/tenprojects-ai-matcher/ first.</p>';
    echo '</body></html>';
    exit;
}

echo '<div class="step"><span class="ok">&#10003;</span> Plugin file found</div>';

// Load the plugin (defines constants + autoloader).
require_once $plugin_file;

echo '<div class="step"><span class="ok">&#10003;</span> Plugin loaded (v' . TP_PLUGIN_VERSION . ')</div>';

// Run activation.
echo '<h2 style="color:#fff;margin-top:24px;">Running Activation...</h2>';

try {
    TenProjects\Activator::activate();
    echo '<div class="step"><span class="ok">&#10003;</span> Activator::activate() completed successfully</div>';
} catch ( Exception $e ) {
    echo '<div class="step"><span class="err">&#10007;</span> Activation error: ' . esc_html( $e->getMessage() ) . '</div>';
}

// Verify tables.
global $wpdb;

echo '<h2 style="color:#fff;margin-top:24px;">Verifying Tables...</h2>';

$expected_tables = [
    'tp_customer_profiles',
    'tp_customer_requirements',
    'tp_project_configurations',
    'tp_project_scores',
    'tp_assessment_sessions',
    'tp_assessment_responses',
    'tp_leads',
    'tp_lead_events',
    'tp_lead_routing_rules',
    'tp_partner_profiles',
    'tp_partner_credits',
    'tp_otp_verifications',
    'tp_search_logs',
    'tp_page_views',
    'tp_ab_experiments',
    'tp_ab_participants',
    'tp_ai_cache',
];

$found = 0;
$missing = 0;

foreach ( $expected_tables as $table ) {
    $full_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $full_name ) );

    if ( $exists ) {
        echo '<div class="step"><span class="ok">&#10003;</span> ' . esc_html( $full_name ) . '</div>';
        $found++;
    } else {
        echo '<div class="step"><span class="err">&#10007;</span> ' . esc_html( $full_name ) . ' — MISSING</div>';
        $missing++;
    }
}

echo '<h2 style="color:#fff;margin-top:24px;">Summary</h2>';
echo '<div class="step"><span class="info">Tables found: ' . $found . ' / ' . count( $expected_tables ) . '</span></div>';

if ( $missing > 0 ) {
    echo '<div class="step"><span class="warn">&#9888; ' . $missing . ' table(s) missing. Check DB_Manager migrations.</span></div>';
} else {
    echo '<div class="step"><span class="ok">&#10003; All tables created successfully!</span></div>';
}

// Verify options.
echo '<h2 style="color:#fff;margin-top:24px;">Verifying Options...</h2>';

$options_to_check = [
    'tp_ai_provider',
    'tp_default_city',
    'tp_brand_name',
    'tp_search_cat_buy',
    'tp_activated_at',
    'tp_db_version',
];

foreach ( $options_to_check as $opt ) {
    $val = get_option( $opt, '__NOT_SET__' );
    if ( $val !== '__NOT_SET__' ) {
        $display = is_string( $val ) ? $val : json_encode( $val );
        echo '<div class="step"><span class="ok">&#10003;</span> ' . esc_html( $opt ) . ' = ' . esc_html( $display ) . '</div>';
    } else {
        echo '<div class="step"><span class="warn">&#9888;</span> ' . esc_html( $opt ) . ' — not set</div>';
    }
}

// Verify roles.
echo '<h2 style="color:#fff;margin-top:24px;">Verifying Roles...</h2>';

$roles_to_check = [ 'tp_project_manager', 'tp_content_editor', 'tp_lead_manager', 'tp_channel_partner', 'tp_customer', 'tp_viewer' ];

foreach ( $roles_to_check as $role_name ) {
    $role = get_role( $role_name );
    if ( $role ) {
        echo '<div class="step"><span class="ok">&#10003;</span> ' . esc_html( $role_name ) . '</div>';
    } else {
        echo '<div class="step"><span class="err">&#10007;</span> ' . esc_html( $role_name ) . ' — MISSING</div>';
    }
}

// Activate plugin if not active.
if ( ! is_plugin_active( 'tenprojects-ai-matcher/tenprojects-ai-matcher.php' ) ) {
    $result = activate_plugin( 'tenprojects-ai-matcher/tenprojects-ai-matcher.php' );
    if ( is_wp_error( $result ) ) {
        echo '<div class="step"><span class="err">&#10007;</span> Plugin activation failed: ' . esc_html( $result->get_error_message() ) . '</div>';
    } else {
        echo '<div class="step"><span class="ok">&#10003;</span> Plugin activated in WordPress</div>';
    }
} else {
    echo '<div class="step"><span class="ok">&#10003;</span> Plugin already active</div>';
}

// Flush rewrite rules.
flush_rewrite_rules();
echo '<div class="step"><span class="ok">&#10003;</span> Rewrite rules flushed</div>';

echo '<h2 style="color:#fff;margin-top:24px;">Done!</h2>';
echo '<div class="step"><span class="warn">&#9888; DELETE THIS FILE IMMEDIATELY!</span></div>';
echo '</body></html>';
