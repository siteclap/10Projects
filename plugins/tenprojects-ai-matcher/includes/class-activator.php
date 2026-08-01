<?php
/**
 * Plugin activation handler.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects;

defined( 'ABSPATH' ) || exit;

class Activator {

    /**
     * Run on plugin activation.
     */
    public static function activate() {
        self::create_tables();
        self::create_roles();
        self::set_default_options();
        self::seed_taxonomy_terms();

        // Flush rewrite rules after CPTs are registered.
        // CPTs haven't been registered yet during activation,
        // so we set a flag and flush on next admin_init.
        set_transient( 'tp_flush_rewrites', true, 60 );

        // Track activation time.
        if ( ! get_option( 'tp_activated_at' ) ) {
            update_option( 'tp_activated_at', current_time( 'mysql' ) );
        }
    }

    /**
     * Create custom database tables.
     */
    private static function create_tables() {
        $db_manager = new Database\DB_Manager();
        $db_manager->run_migrations();
        update_option( 'tp_db_version', TP_DB_VERSION );
    }

    /**
     * Create custom user roles and capabilities.
     */
    private static function create_roles() {
        // TP Super Admin (extends Administrator).
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            $tp_caps = array(
                'manage_tp_settings',
                'manage_tp_partners',
                'view_tp_analytics',
                'export_tp_leads',
                'manage_tp_routing',
                'delete_tp_data',
                'edit_tp_projects',
                'edit_tp_developers',
                'edit_tp_locations',
                'import_tp_projects',
                'manage_tp_inventory',
                'view_tp_leads',
                'edit_tp_guides',
                'edit_tp_reviews',
                'edit_tp_market_reports',
                'edit_tp_infrastructure',
                'edit_tp_project_editorial',
                'manage_tp_leads',
                'assign_tp_leads',
            );
            foreach ( $tp_caps as $cap ) {
                $admin->add_cap( $cap );
            }
        }

        // Project Manager.
        add_role( 'tp_project_manager', 'Project Manager', array(
            'read'                     => true,
            'upload_files'             => true,
            'edit_tp_projects'         => true,
            'edit_tp_developers'       => true,
            'edit_tp_locations'        => true,
            'import_tp_projects'       => true,
            'manage_tp_inventory'      => true,
            'view_tp_leads'            => true,
        ) );

        // Content Editor.
        add_role( 'tp_content_editor', 'Content Editor', array(
            'read'                       => true,
            'upload_files'               => true,
            'edit_tp_guides'             => true,
            'edit_tp_reviews'            => true,
            'edit_tp_market_reports'     => true,
            'edit_tp_infrastructure'     => true,
            'edit_tp_project_editorial'  => true,
        ) );

        // Lead Manager.
        add_role( 'tp_lead_manager', 'Lead Manager', array(
            'read'              => true,
            'view_tp_leads'     => true,
            'manage_tp_leads'   => true,
            'assign_tp_leads'   => true,
            'export_tp_leads'   => true,
            'view_tp_analytics' => true,
        ) );

        // Channel Partner.
        add_role( 'tp_channel_partner', 'Channel Partner', array(
            'read'           => true,
            'view_tp_leads'  => true,
        ) );

        // Customer (frontend only).
        add_role( 'tp_customer', 'Customer', array(
            'read' => true,
        ) );

        // Viewer (read-only analytics).
        add_role( 'tp_viewer', 'Viewer', array(
            'read'              => true,
            'view_tp_analytics' => true,
        ) );
    }

    /**
     * Seed default terms for taxonomies that require them.
     *
     * Taxonomies must be registered before terms can be inserted.
     * Each taxonomy class checks for existing terms to avoid duplicates.
     */
    private static function seed_taxonomy_terms() {
        // Register taxonomies first so terms can be inserted.
        $taxonomy_classes = array(
            'Taxonomy\\Configuration_Taxonomy',
            'Taxonomy\\Budget_Range_Taxonomy',
            'Taxonomy\\Construction_Stage_Taxonomy',
            'Taxonomy\\Possession_Year_Taxonomy',
            'Taxonomy\\Property_Type_Taxonomy',
            'Taxonomy\\Buyer_Type_Taxonomy',
            'Taxonomy\\Amenity_Taxonomy',
        );

        foreach ( $taxonomy_classes as $tax_class ) {
            $class = 'TenProjects\\' . $tax_class;
            if ( class_exists( $class ) ) {
                // Register the taxonomy so wp_insert_term() works.
                $instance = new $class();
                $instance->register_taxonomy();

                // Insert default terms.
                $class::insert_default_terms();
            }
        }
    }

    /**
     * Set default plugin options.
     */
    private static function set_default_options() {
        $defaults = array(
            'tp_ai_provider'           => 'claude',
            'tp_ai_model'              => 'claude-sonnet-4-6',
            'tp_scoring_weights'       => 'default',
            'tp_otp_provider'          => 'msg91',
            'tp_lead_auto_assign'      => true,
            'tp_assessment_phases'     => 3,
            'tp_max_recommendations'   => 10,
            'tp_rate_limit_api'        => 60,
            'tp_rate_limit_otp'        => 3,
            'tp_default_city'          => 'navi-mumbai',
            'tp_consent_required'      => true,
            'tp_whatsapp_enabled'      => true,
        );

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                update_option( $key, $value );
            }
        }
    }
}
