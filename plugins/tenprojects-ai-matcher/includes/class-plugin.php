<?php
/**
 * Main plugin orchestrator class.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects;

defined( 'ABSPATH' ) || exit;

class Plugin {

    /**
     * Singleton instance.
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return Plugin
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize all plugin components.
     */
    public function init() {
        self::$instance = $this;

        $this->register_cpts();
        $this->register_taxonomies();
        $this->register_services();
        $this->register_api();
        $this->register_admin();
        $this->register_shortcodes();
        $this->register_blocks();

        // Register custom block category.
        add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );

        // Enqueue admin assets.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Check DB version on admin init.
        add_action( 'admin_init', array( $this, 'check_db_version' ) );
    }

    /**
     * Register Custom Post Types.
     */
    private function register_cpts() {
        $cpts = array(
            'CPT\\Project_CPT',
            'CPT\\Developer_CPT',
            'CPT\\Location_CPT',
            'CPT\\Guide_CPT',
            'CPT\\Review_CPT',
            'CPT\\Infrastructure_CPT',
            'CPT\\Market_Report_CPT',
        );

        foreach ( $cpts as $cpt_class ) {
            $class = 'TenProjects\\' . $cpt_class;
            if ( class_exists( $class ) ) {
                $instance = new $class();
                $instance->register();
            }
        }
    }

    /**
     * Register Taxonomies.
     */
    private function register_taxonomies() {
        $taxonomies = array(
            'Taxonomy\\City_Taxonomy',
            'Taxonomy\\Location_Taxonomy',
            'Taxonomy\\Micro_Location_Taxonomy',
            'Taxonomy\\Configuration_Taxonomy',
            'Taxonomy\\Budget_Range_Taxonomy',
            'Taxonomy\\Construction_Stage_Taxonomy',
            'Taxonomy\\Possession_Year_Taxonomy',
            'Taxonomy\\Property_Type_Taxonomy',
            'Taxonomy\\Buyer_Type_Taxonomy',
            'Taxonomy\\Amenity_Taxonomy',
        );

        foreach ( $taxonomies as $tax_class ) {
            $class = 'TenProjects\\' . $tax_class;
            if ( class_exists( $class ) ) {
                $instance = new $class();
                $instance->register();
            }
        }
    }

    /**
     * Register business logic services.
     */
    private function register_services() {
        // Services are loaded on-demand via the autoloader.
        // Hooks that need early registration go here.
    }

    /**
     * Register REST API endpoints.
     */
    private function register_api() {
        add_action( 'rest_api_init', function () {
            $controllers = array(
                'API\\Assessment_API',
                'API\\Recommendation_API',
                'API\\Project_API',
                'API\\Customer_API',
                'API\\Auth_API',
                'API\\Lead_API',
                'API\\Comparison_API',
                'API\\Saved_Projects_API',
                'API\\Site_Visit_API',
                'API\\Analytics_API',
                'API\\Partner_API',
            );

            foreach ( $controllers as $controller_class ) {
                $class = 'TenProjects\\' . $controller_class;
                if ( class_exists( $class ) ) {
                    $controller = new $class();
                    $controller->register_routes();
                }
            }
        } );
    }

    /**
     * Register admin pages and meta boxes.
     */
    private function register_admin() {
        if ( ! is_admin() ) {
            return;
        }

        add_action( 'admin_menu', function () {
            $admin_class = 'TenProjects\\Admin\\Admin';
            if ( class_exists( $admin_class ) ) {
                $admin = new $admin_class();
                $admin->register_menus();
            }
        } );
    }

    /**
     * Register shortcodes.
     */
    private function register_shortcodes() {
        $shortcodes_class = 'TenProjects\\Shortcodes\\Shortcodes';
        if ( class_exists( $shortcodes_class ) ) {
            $shortcodes = new $shortcodes_class();
            $shortcodes->register();
        }
    }

    /**
     * Register Gutenberg blocks.
     */
    private function register_blocks() {
        $blocks = array(
            'ai-assessment',
            'project-card',
            'fit-score',
            'emi-calculator',
            'cta-assessment',
            'trust-indicators',
        );

        add_action( 'init', function () use ( $blocks ) {
            foreach ( $blocks as $block ) {
                $block_dir = TP_PLUGIN_DIR . 'includes/blocks/' . $block;
                if ( file_exists( $block_dir . '/block.json' ) ) {
                    register_block_type( $block_dir );
                }
            }
        } );
    }

    /**
     * Register custom block category for TenProjects blocks.
     *
     * @param array                    $categories Existing block categories.
     * @param \WP_Block_Editor_Context $context    Block editor context.
     * @return array Modified block categories.
     */
    public function register_block_category( $categories, $context ) {
        return array_merge(
            array(
                array(
                    'slug'  => 'tenprojects',
                    'title' => __( '10Projects', 'tenprojects' ),
                    'icon'  => 'admin-home',
                ),
            ),
            $categories
        );
    }

    /**
     * Enqueue admin CSS and JS.
     *
     * @param string $hook_suffix Current admin page.
     */
    public function enqueue_admin_assets( $hook_suffix ) {
        // Load on all admin pages for now; can be scoped later.
        wp_enqueue_style(
            'tp-admin',
            TP_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            TP_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'tp-admin',
            TP_PLUGIN_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            TP_PLUGIN_VERSION,
            true
        );

        wp_localize_script( 'tp-admin', 'tpAdmin', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'tp_admin_nonce' ),
        ) );
    }

    /**
     * Check if DB needs updating.
     */
    public function check_db_version() {
        $installed_version = get_option( 'tp_db_version', '0' );
        if ( version_compare( $installed_version, TP_DB_VERSION, '<' ) ) {
            $db_manager = new Database\DB_Manager();
            $db_manager->run_migrations();
            update_option( 'tp_db_version', TP_DB_VERSION );
        }
    }
}
