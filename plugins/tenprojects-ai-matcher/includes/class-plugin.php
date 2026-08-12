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

        $this->setup_cors();
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

        // ISR revalidation on CPT save.
        $this->register_isr_hooks();
    }

    /**
     * Initialize CORS handler for headless frontend.
     */
    private function setup_cors() {
        new CORS();
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

        // Register settings on admin_init so options.php whitelists them.
        add_action( 'admin_init', function () {
            $settings = new Admin\Settings_Admin();
            $settings->register_settings();
        } );

        // Purge all caches when any tp_ brand/social option is updated.
        add_action( 'updated_option', function ( $option ) {
            if ( strpos( $option, 'tp_brand_' ) === 0 || strpos( $option, 'tp_social_' ) === 0 ) {
                if ( class_exists( 'LiteSpeed\\Purge' ) ) {
                    \LiteSpeed\Purge::purge_all();
                }
                wp_cache_flush();
            }
        } );

        // Category menu items (Buy, Rent, Commercial, Resale, Plot).
        ( new Admin\Category_Menu() )->register();
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
        // Load WP media library on Brand Settings page (needed for logo/banner uploads).
        if ( 'toplevel_page_tenprojects-brand' === $hook_suffix || strpos( $hook_suffix, 'tenprojects' ) !== false ) {
            wp_enqueue_media();
        }

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

    /**
     * Register save_post hooks for ISR revalidation on CPT updates.
     *
     * When a supported CPT is published or updated, a non-blocking POST
     * request is sent to the Next.js revalidation endpoint so that
     * statically-generated pages are refreshed without a full rebuild.
     */
    private function register_isr_hooks() {
        $cpt_types = array(
            'tp_project',
            'tp_developer',
            'tp_location',
            'tp_guide',
        );

        foreach ( $cpt_types as $cpt ) {
            add_action( "save_post_{$cpt}", array( $this, 'trigger_isr_revalidation' ), 20, 2 );
        }
    }

    /**
     * Send a non-blocking revalidation request to the Next.js frontend.
     *
     * Only fires for published posts. Uses wp_remote_post with a 0.01s
     * timeout and blocking disabled so the WordPress admin does not wait
     * for the Next.js response.
     *
     * @param int      $post_id Post ID being saved.
     * @param \WP_Post $post    Post object being saved.
     */
    public function trigger_isr_revalidation( $post_id, $post ) {
        // Bail on autosaves and revisions.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Only revalidate published posts.
        if ( 'publish' !== $post->post_status ) {
            return;
        }

        $revalidate_url = defined( 'TP_NEXTJS_REVALIDATE_URL' )
            ? TP_NEXTJS_REVALIDATE_URL
            : '';

        $revalidate_secret = defined( 'TP_NEXTJS_REVALIDATE_SECRET' )
            ? TP_NEXTJS_REVALIDATE_SECRET
            : '';

        if ( empty( $revalidate_url ) || empty( $revalidate_secret ) ) {
            return;
        }

        $slug      = $post->post_name;
        $post_type = $post->post_type;

        // Build the explicit path for project pages (location/project).
        $path = null;
        if ( 'tp_project' === $post_type ) {
            $locations = wp_get_post_terms( $post_id, 'tp_location', array( 'fields' => 'slugs' ) );
            if ( ! is_wp_error( $locations ) && ! empty( $locations ) ) {
                $path = '/navi-mumbai/' . $locations[0] . '/' . $slug;
            }
        }

        $body = array(
            'secret' => $revalidate_secret,
            'type'   => $post_type,
            'slug'   => $slug,
        );

        if ( $path ) {
            $body['path'] = $path;
        }

        // Non-blocking request — fire and forget.
        wp_remote_post( $revalidate_url, array(
            'body'     => wp_json_encode( $body ),
            'headers'  => array(
                'Content-Type' => 'application/json',
            ),
            'timeout'  => 0.01,
            'blocking' => false,
        ) );
    }
}
