<?php
/**
 * Project Custom Post Type.
 *
 * Registers the tp_project CPT with all meta fields, taxonomies,
 * and a tabbed admin meta box styled like NewPropertyz.
 *
 * Tabs: Graphics & Media, Contact Details, About Developer,
 *       Offers, Carpet Area & Price, Construction, Location Details,
 *       Scoring & Editorial
 *
 * @package TenProjects\CPT
 * @since   1.0.0
 */

namespace TenProjects\CPT;

defined( 'ABSPATH' ) || exit;

class Project_CPT {

	/**
	 * Post type key.
	 *
	 * @var string
	 */
	const POST_TYPE = 'tp_project';

	/**
	 * Meta key prefix.
	 *
	 * @var string
	 */
	const META_PREFIX = '_tp_';

	/**
	 * Register CPT, meta, and meta box hooks.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta_fields' ) );
		add_action( 'init', array( $this, 'register_type_rewrite_rules' ), 20 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'project_permalink' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_scripts' ) );
		add_action( 'template_redirect', array( $this, 'redirect_old_project_urls' ) );

		// Force classic editor for projects — tabbed meta boxes don't work well with Gutenberg.
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg' ), 10, 2 );
	}

	/**
	 * Disable Gutenberg for the project CPT so custom meta boxes render correctly.
	 *
	 * @param bool   $use_block_editor Whether to use block editor.
	 * @param string $post_type        Post type being checked.
	 * @return bool
	 */
	public function disable_gutenberg( $use_block_editor, $post_type ) {
		if ( self::POST_TYPE === $post_type ) {
			return false;
		}
		return $use_block_editor;
	}

	/**
	 * Enqueue WordPress media uploader on project edit screens.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_media_scripts( $hook_suffix ) {
		$screen = get_current_screen();
		if ( $screen && self::POST_TYPE === $screen->post_type && in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Register the Project post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Projects', 'Post type general name', 'tenprojects-ai-matcher' ),
			'singular_name'         => _x( 'Project', 'Post type singular name', 'tenprojects-ai-matcher' ),
			'menu_name'             => _x( 'Projects', 'Admin Menu text', 'tenprojects-ai-matcher' ),
			'name_admin_bar'        => _x( 'Project', 'Add New on Toolbar', 'tenprojects-ai-matcher' ),
			'add_new'               => __( 'Add New', 'tenprojects-ai-matcher' ),
			'add_new_item'          => __( 'Add New Project', 'tenprojects-ai-matcher' ),
			'new_item'              => __( 'New Project', 'tenprojects-ai-matcher' ),
			'edit_item'             => __( 'Edit Project', 'tenprojects-ai-matcher' ),
			'view_item'             => __( 'View Project', 'tenprojects-ai-matcher' ),
			'all_items'             => __( 'All Projects', 'tenprojects-ai-matcher' ),
			'search_items'          => __( 'Search Projects', 'tenprojects-ai-matcher' ),
			'parent_item_colon'     => __( 'Parent Projects:', 'tenprojects-ai-matcher' ),
			'not_found'             => __( 'No projects found.', 'tenprojects-ai-matcher' ),
			'not_found_in_trash'    => __( 'No projects found in Trash.', 'tenprojects-ai-matcher' ),
			'featured_image'        => __( 'Project Featured Image', 'tenprojects-ai-matcher' ),
			'set_featured_image'    => __( 'Set project image', 'tenprojects-ai-matcher' ),
			'remove_featured_image' => __( 'Remove project image', 'tenprojects-ai-matcher' ),
			'use_featured_image'    => __( 'Use as project image', 'tenprojects-ai-matcher' ),
			'archives'              => __( 'Project Archives', 'tenprojects-ai-matcher' ),
			'filter_items_list'     => __( 'Filter projects list', 'tenprojects-ai-matcher' ),
			'items_list_navigation' => __( 'Projects list navigation', 'tenprojects-ai-matcher' ),
			'items_list'            => __( 'Projects list', 'tenprojects-ai-matcher' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_rest'        => true,
			'query_var'           => true,
			'rewrite'             => array(
				'slug'       => 'buy/navi-mumbai/%tp_location_area%',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => 'projects',
			'hierarchical'        => false,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-building',
			'supports'            => array( 'title', 'thumbnail', 'excerpt' ),
			'taxonomies'          => array(
				'tp_city',
				'tp_location_area',
				'tp_micro_location',
				'tp_configuration',
				'tp_budget_range',
				'tp_construction_stage',
				'tp_possession_year',
				'tp_property_type',
				'tp_amenity',
			),
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Replace %tp_location_area% in the permalink with the actual term slug.
	 *
	 * @param string   $post_link The post permalink.
	 * @param \WP_Post $post      The post object.
	 * @return string
	 */
	public function project_permalink( $post_link, $post ) {
		if ( self::POST_TYPE !== $post->post_type ) {
			return $post_link;
		}

		// Replace location area placeholder.
		$loc_terms = wp_get_object_terms( $post->ID, 'tp_location_area' );
		$loc_slug  = ( ! is_wp_error( $loc_terms ) && ! empty( $loc_terms ) ) ? $loc_terms[0]->slug : 'uncategorized';
		$post_link = str_replace( '%tp_location_area%', $loc_slug, $post_link );

		// Replace the default 'buy' prefix with the actual property type slug.
		$type_terms = wp_get_object_terms( $post->ID, 'tp_property_type', array( 'fields' => 'slugs' ) );
		$type_slug  = ( ! is_wp_error( $type_terms ) && ! empty( $type_terms ) ) ? $type_terms[0] : 'buy';
		if ( 'buy' !== $type_slug ) {
			$post_link = preg_replace( '#^(https?://[^/]+)/buy/#', '$1/' . $type_slug . '/', $post_link );
		}

		return $post_link;
	}

	/**
	 * Register rewrite rules for non-buy property types + taxonomy archives.
	 * 'buy' project URLs are handled automatically by the CPT's own rewrite slug.
	 * Taxonomy archives need 'top' priority so standard WP post/attachment rules don't take precedence.
	 */
	public function register_type_rewrite_rules() {
		// Property type taxonomy archives — must be 'top' so WP attachment rules don't intercept.
		add_rewrite_rule(
			'^properties/([^/]+)/page/?([0-9]{1,})/?$',
			'index.php?tp_property_type=$matches[1]&paged=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'^properties/([^/]+)/?$',
			'index.php?tp_property_type=$matches[1]',
			'top'
		);

		// Non-buy property type project URLs.
		add_rewrite_rule(
			'^(rent|resale|commercial|plot|pg)/navi-mumbai/([^/]+)/([^/]+)/?$',
			'index.php?tp_project=$matches[3]&tp_location_area=$matches[2]',
			'top'
		);

		// Location area taxonomy archives — must be 'top' so standard WP attachment rules don't win.
		add_rewrite_rule(
			'^navi-mumbai/([^/]+)/?$',
			'index.php?tp_location_area=$matches[1]',
			'top'
		);
	}

	/**
	 * 301-redirect old project URLs (/navi-mumbai/{loc}/{slug}/) to new typed URLs.
	 */
	public function redirect_old_project_urls() {
		$uri = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
		// Match: navi-mumbai/{location}/{project-slug}
		if ( ! preg_match( '#^navi-mumbai/([^/]+)/([^/]+)$#', $uri, $m ) ) {
			return;
		}
		$post = get_page_by_path( $m[2], OBJECT, self::POST_TYPE );
		if ( ! $post ) {
			return;
		}
		$new_url = get_permalink( $post->ID );
		if ( $new_url && $new_url !== home_url( '/' . $uri . '/' ) ) {
			wp_redirect( $new_url, 301 );
			exit;
		}
	}

	/**
	 * Get all meta field definitions.
	 *
	 * @return array[] Associative array of meta_key => schema args.
	 */
	private function get_meta_fields(): array {
		return array(
			// --- Graphics & Media ---
			'banner_desktop_ids'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'banner_mobile_ids'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'gallery_ids'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'developer_logo_id'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// --- Contact Details ---
			'phone'                  => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'email'                  => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_email' ),
			'sales_office_address'   => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),

			// --- About Developer / Project Overview ---
			'project_name'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'developer_id'           => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'developer_about'        => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'developer_name'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'project_location'       => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'land_parcel'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'floors_display'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'qr_code_id'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'short_overview'         => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'google_review_rating'   => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'available_configs_text' => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// --- Offers ---
			'sponsored'              => array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean' ),
			'sponsor_label'          => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'offers'                 => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),

			// --- Carpet Area & Price ---
			'rera_number'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'rera_phase'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'status'                 => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'launch_date'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'verified'               => array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean' ),
			'is_new_launch'          => array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean' ),
			'is_featured'            => array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean' ),
			'price_display_min'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'price_display_max'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// --- Project Scale ---
			'total_towers'           => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'total_floors'           => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'total_units'            => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),

			// --- Location ---
			'latitude'               => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'longitude'              => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'address'                => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'address_pin'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'location_advantage_1'   => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'location_advantage_2'   => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'location_brief'         => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'railway_distance_km'    => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'metro_distance_km'      => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'highway_distance_km'    => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'airport_distance_km'    => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'school_distance_km'     => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'hospital_distance_km'   => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'mall_distance_km'       => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'employment_hub_km'      => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),

			// --- Scoring & Editorial ---
			'legal_confidence'       => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'possession_confidence'  => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'bank_approved'          => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'litigation_status'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'micro_market_price'     => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'rental_range_min'       => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'rental_range_max'       => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'vacancy_risk'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'appreciation_score'     => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'rental_yield_pct'       => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'highlights'             => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'pros'                   => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'cons'                   => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'risks'                  => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'best_for'               => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'not_for'                => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),

			// --- Infrastructure ---
			'open_space_ratio'       => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'density_rating'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'maintenance_estimate'   => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'parking_info'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'water_source'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'power_backup'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// --- Verification ---
			'verification_checklist' => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'sources'                => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'last_verified'          => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'reviewed_by'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── Rental Details ──
			'monthly_rent'           => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'security_deposit'       => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'maintenance_charges'    => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'lock_in_period'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'notice_period'          => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'available_from'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'tenant_preferred'       => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'furnishing_status'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'furnishing_details'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'pets_allowed'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'nonveg_allowed'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'water_supply'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'brokerage'              => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── Commercial Details ──
			'commercial_type'        => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'building_grade'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'fitout_status'          => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'commercial_carpet'      => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'price_per_sqft'         => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'cam_charges'            => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'power_load'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'seating_capacity'       => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'cabins_count'           => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'washrooms_count'        => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'hvac_type'              => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'parking_bays'           => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'fire_noc'               => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lease_term'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'escalation_clause'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── Plot Details ──
			'plot_type'              => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'plot_area'              => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'plot_width'             => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'plot_depth'             => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'corner_plot'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'road_width'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'sides_open'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'boundary_wall'          => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'topography'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'fsi'                    => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'permissible_floors'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'water_connection'       => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'electricity_connection' => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'sewage_connection'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'gated_community'        => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── PG Details ──
			'pg_gender'              => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_occupant'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_single_rent'         => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'pg_double_rent'         => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'pg_triple_rent'         => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'pg_deposit'             => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'pg_notice_period'       => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_meals'               => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_meal_type'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_kitchen'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_wifi'                => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_laundry'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_housekeeping'        => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_ac'                  => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_smoking'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_drinking'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_guests'              => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'pg_curfew'              => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
		);
	}

	/**
	 * Register meta fields with the REST API.
	 */
	public function register_meta_fields() {
		foreach ( $this->get_meta_fields() as $key => $schema ) {
			register_post_meta(
				self::POST_TYPE,
				self::META_PREFIX . $key,
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => $schema['type'],
					'sanitize_callback' => $schema['sanitize_callback'],
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * Sanitize float values.
	 *
	 * @param mixed $value Raw value.
	 * @return float
	 */
	public function sanitize_float( $value ): float {
		return (float) $value;
	}

	/**
	 * Add the tabbed meta box.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'tp_project_details',
			__( 'Settings', 'tenprojects-ai-matcher' ),
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		// Hide default metaboxes — handled in custom panels.
		remove_meta_box( 'postimagediv', self::POST_TYPE, 'side' );
		remove_meta_box( 'postexcerpt', self::POST_TYPE, 'normal' );
	}

	/**
	 * Render the tabbed meta box (NewPropertyz-style tabs).
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public function render_meta_box( \WP_Post $post ) {
		wp_nonce_field( 'tp_project_meta', 'tp_project_meta_nonce' );

		// Inline critical styles to bypass CSS file caching (Cloudflare / browser).
		echo '<style>
			#tp_project_details .inside { padding: 0 !important; margin: 0 !important; }
			.tp-meta-box { display: flex !important; flex-direction: row !important; min-height: 420px; border: 1px solid #ddd; background: #fff; }
			.tp-meta-tabs { display: flex !important; flex-direction: column !important; width: 200px !important; flex-shrink: 0 !important; border-right: 1px solid #ddd; background: #fff; margin: 0; padding: 0; }
			.tp-meta-tab { display: block; padding: 13px 18px; font-size: 13px; font-weight: 500; color: #444; cursor: pointer; border: none; border-left: 3px solid transparent; border-bottom: 1px solid #f0f0f1; text-align: right; background: none; transition: all 0.15s; white-space: normal; text-decoration: none; line-height: 1.4; }
			.tp-meta-tab:hover { color: #1A56DB; background: #f8f9ff; }
			.tp-meta-tab:focus { outline: none; box-shadow: none; }
			.tp-meta-tab.active { color: #1A56DB; border-left-color: #1A56DB !important; font-weight: 600; background: #f8f9ff; }
			.tp-meta-panels { flex: 1 !important; min-width: 0; }
			.tp-meta-panel { display: none; padding: 20px 24px; }
			.tp-meta-panel.active { display: block; }

			/* Field layout — horizontal grid rows */
			.tp-field-group { margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 4px; background: #fff; overflow: hidden; }
			.tp-field-group__title { font-size: 13px; font-weight: 600; color: #1e293b; padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; }
			.tp-field-row { display: grid !important; grid-template-columns: repeat(3, 1fr); gap: 0; }
			.tp-field { padding: 14px 16px; border-right: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }
			.tp-field:last-child { border-right: none; }
			.tp-field label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: none; }
			.tp-field input[type="text"],
			.tp-field input[type="number"],
			.tp-field input[type="date"],
			.tp-field input[type="email"],
			.tp-field input[type="url"],
			.tp-field select { width: 100%; padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 13px; background: #fff; box-sizing: border-box; }
			.tp-field input:focus,
			.tp-field select:focus,
			.tp-field textarea:focus { border-color: #1A56DB; outline: none; box-shadow: 0 0 0 1px #1A56DB; }
			.tp-field textarea { width: 100%; padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 13px; background: #fff; box-sizing: border-box; min-height: 80px; resize: vertical; }
			.tp-field .description { display: block; font-size: 11px; color: #6b7280; margin-top: 4px; font-style: normal; }

			/* Gallery field */
			.tp-gallery-field { min-width: 0; }
			.tp-gallery-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
			.tp-gallery-item { position: relative; width: 80px; height: 80px; border: 1px solid #d1d5db; border-radius: 4px; overflow: hidden; }
			.tp-gallery-item img { width: 100%; height: 100%; object-fit: cover; }
			.tp-gallery-remove { position: absolute; top: 2px; right: 2px; width: 20px; height: 20px; border: none; background: rgba(0,0,0,0.6); color: #fff; font-size: 14px; line-height: 18px; text-align: center; cursor: pointer; border-radius: 50%; padding: 0; }
			.tp-gallery-remove:hover { background: #ef4444; }
			.tp-gallery-add.button { display: inline-flex; align-items: center; gap: 4px; }

			/* JSON list field */
			.tp-json-field { }
			.tp-json-list { margin-bottom: 8px; }
			.tp-json-item { display: flex; gap: 6px; margin-bottom: 6px; align-items: center; }
			.tp-json-item input[type="text"] { flex: 1; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 13px; }
			.tp-json-remove { color: #ef4444 !important; border-color: #fca5a5 !important; min-height: 30px; }
			.tp-json-add { color: #1A56DB !important; border-color: #93c5fd !important; }

			/* 2-column row override */
			.tp-field-row.tp-cols-2 { grid-template-columns: repeat(2, 1fr) !important; }
			/* 4-column row override */
			.tp-field-row.tp-cols-4 { grid-template-columns: repeat(4, 1fr) !important; }

			/* Checkbox inline style */
			.tp-field input[type="checkbox"] { width: auto; margin-right: 6px; }

			/* Graphics 2x2 grid */
			.tp-graphics-grid { display: grid !important; grid-template-columns: 1fr 1fr; gap: 20px; }
		</style>';

		$tabs = array(
			'graphics'     => __( 'Graphics & Media', 'tenprojects-ai-matcher' ),
			'developer'    => __( 'Project Overview', 'tenprojects-ai-matcher' ),
			'contact'      => __( 'Contact', 'tenprojects-ai-matcher' ),
			'offers'       => __( 'Offers', 'tenprojects-ai-matcher' ),
			'location'     => __( 'Location', 'tenprojects-ai-matcher' ),
			'rental'       => __( 'Rental Details', 'tenprojects-ai-matcher' ),
			'commercial'   => __( 'Commercial Details', 'tenprojects-ai-matcher' ),
			'plot'         => __( 'Plot Details', 'tenprojects-ai-matcher' ),
			'pg'           => __( 'PG Details', 'tenprojects-ai-matcher' ),
		);

		echo '<div class="tp-meta-box" data-post-id="' . esc_attr( $post->ID ) . '">';

		// Tab navigation.
		echo '<div class="tp-meta-tabs">';
		$first = true;
		foreach ( $tabs as $slug => $label ) {
			$active = $first ? ' active' : '';
			echo '<button type="button" class="tp-meta-tab' . $active . '" data-tab="' . esc_attr( $slug ) . '">'
				. esc_html( $label ) . '</button>';
			$first = false;
		}
		echo '</div>';

		// Tab panels wrapper (flex right side).
		echo '<div class="tp-meta-panels">';
		$this->render_graphics_panel( $post );
		$this->render_developer_panel( $post );
		$this->render_contact_panel( $post );
		$this->render_offers_panel( $post );
		$this->render_location_panel( $post );
		$this->render_rental_panel( $post );
		$this->render_commercial_panel( $post );
		$this->render_plot_panel( $post );
		$this->render_pg_panel( $post );
		echo '</div>';

		echo '</div>';

		// Detect current property type for JS tab visibility.
		// URL parameter takes priority (new posts via category menu).
		$current_type = '';
		if ( ! empty( $_GET['tp_default_type'] ) ) { // phpcs:ignore
			$current_type = sanitize_text_field( wp_unslash( $_GET['tp_default_type'] ) );
		}
		// Fallback: read stored term (existing projects).
		if ( empty( $current_type ) && $post->ID ) {
			$pt_terms     = wp_get_object_terms( $post->ID, 'tp_property_type', array( 'fields' => 'slugs' ) );
			$current_type = ( ! is_wp_error( $pt_terms ) && ! empty( $pt_terms ) ) ? $pt_terms[0] : '';
		}
		if ( empty( $current_type ) ) {
			$current_type = 'buy';
		}

		// Hidden field so property type is submitted with the save form.
		echo '<input type="hidden" name="tp_property_type_override" value="' . esc_attr( $current_type ) . '" />';

		// Inline JS — tab switching + gallery upload (bypasses cached admin.js).
		echo '<script>
		(function(){
			var tpPropertyType = "' . esc_js( $current_type ) . '";
			/* --- Tab switching --- */
			document.querySelectorAll(".tp-meta-tab").forEach(function(tab){
				tab.addEventListener("click", function(e){
					e.preventDefault();
					var target = this.getAttribute("data-tab");
					var box = this.closest(".tp-meta-box");
					box.querySelectorAll(".tp-meta-tab").forEach(function(t){ t.classList.remove("active"); });
					this.classList.add("active");
					box.querySelectorAll(".tp-meta-panel").forEach(function(p){ p.classList.remove("active"); });
					var panel = box.querySelector(".tp-meta-panel[data-panel=\""+target+"\"]");
					if(panel) panel.classList.add("active");
					try{ localStorage.setItem("tp_tab_"+document.querySelector(".tp-meta-box").dataset.postId, target); }catch(ex){}
				});
			});
			/* Restore last tab */
			try{
				var box = document.querySelector(".tp-meta-box");
				if(box){
					var saved = localStorage.getItem("tp_tab_"+box.dataset.postId);
					if(saved){ var t = box.querySelector(".tp-meta-tab[data-tab=\""+saved+"\"]"); if(t) t.click(); }
				}
			}catch(ex){}

			/* --- Category-based tab visibility (uses PHP-injected tpPropertyType) --- */
			var catTabs = {
				buy:        { show: ["pricing","construction"], hide: ["rental","commercial","plot","pg"] },
				resale:     { show: ["pricing","construction"], hide: ["rental","commercial","plot","pg"] },
				rent:       { show: ["pricing","rental"],       hide: ["construction","commercial","plot","pg","offers","location"] },
				commercial: { show: ["pricing","commercial","construction"], hide: ["rental","plot","pg"] },
				plot:       { show: ["plot"],                   hide: ["pricing","construction","rental","commercial","pg"] },
				pg:         { show: ["pg"],                     hide: ["pricing","construction","rental","commercial","plot"] }
			};
			function updateCatTabs(){
				var rule = catTabs[tpPropertyType] || catTabs["buy"];
				var allCat = ["pricing","construction","rental","commercial","plot","pg"];
				allCat.forEach(function(tab){
					var tabEl = document.querySelector(".tp-meta-tab[data-tab=\""+tab+"\"]");
					if(!tabEl) return;
					if(rule.show.indexOf(tab) >= 0){ tabEl.style.display = ""; }
					else if(rule.hide.indexOf(tab) >= 0){ tabEl.style.display = "none"; }
				});
				/* If active tab is hidden, click first visible tab */
				var activeTab = document.querySelector(".tp-meta-tab.active");
				if(activeTab && activeTab.style.display === "none"){
					var first = document.querySelector(".tp-meta-tab:not([style*=\"display: none\"])");
					if(first) first.click();
				}
			}
			/* Run immediately */
			updateCatTabs();

			/* --- Gallery / Choose Media --- */
			document.querySelectorAll(".tp-gallery-add").forEach(function(btn){
				btn.addEventListener("click", function(e){
					e.preventDefault();
					var field = this.closest(".tp-gallery-field");
					var input = field.querySelector(".tp-gallery-ids");
					var grid = field.querySelector(".tp-gallery-grid");
					var frame = wp.media({ title:"Choose Media", button:{text:"Add to Gallery"}, multiple:true, library:{type:"image"} });
					frame.on("select", function(){
						var sel = frame.state().get("selection");
						var ids = input.value ? input.value.split(",").filter(Boolean) : [];
						sel.forEach(function(att){
							var a = att.toJSON();
							if(ids.indexOf(String(a.id)) === -1){
								ids.push(String(a.id));
								var thumb = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
								var div = document.createElement("div");
								div.className = "tp-gallery-item";
								div.setAttribute("data-id", a.id);
								div.innerHTML = "<img src=\""+thumb+"\" alt=\"\" /><button type=\"button\" class=\"tp-gallery-remove\" title=\"Remove\">&times;</button>";
								grid.appendChild(div);
							}
						});
						input.value = ids.join(",");
					});
					frame.open();
				});
			});

			/* Remove gallery item */
			document.addEventListener("click", function(e){
				if(!e.target.classList.contains("tp-gallery-remove")) return;
				e.preventDefault();
				var item = e.target.closest(".tp-gallery-item");
				var field = item.closest(".tp-gallery-field");
				var input = field.querySelector(".tp-gallery-ids");
				var removeId = String(item.getAttribute("data-id"));
				item.remove();
				input.value = input.value.split(",").filter(function(id){ return id && id !== removeId; }).join(",");
			});

			/* --- Featured Image picker --- */
			(function(){
				var setBtn = document.getElementById("tp-set-featured-img");
				var removeBtn = document.getElementById("tp-remove-featured-img");
				var hiddenInput = document.getElementById("tp-featured-img-id");
				var wrap = document.getElementById("tp-featured-img-wrap");
				if(!setBtn) return;
				setBtn.addEventListener("click", function(e){
					e.preventDefault();
					var frame = wp.media({ title:"Set Featured Image", button:{text:"Set Image"}, multiple:false, library:{type:"image"} });
					frame.on("select", function(){
						var att = frame.state().get("selection").first().toJSON();
						var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
						hiddenInput.value = att.id;
						wrap.innerHTML = "<img src=\""+url+"\" style=\"max-width:100%;height:auto;border-radius:8px;margin-bottom:8px;display:block;\">";
						setBtn.textContent = "Change Image";
						if(!removeBtn){
							removeBtn = document.createElement("button");
							removeBtn.type = "button";
							removeBtn.className = "button";
							removeBtn.id = "tp-remove-featured-img";
							removeBtn.style.color = "#a00";
							removeBtn.textContent = "Remove";
							setBtn.after(document.createTextNode(" "), removeBtn);
						}
					});
					frame.open();
				});
				document.addEventListener("click", function(e){
					if(e.target && e.target.id === "tp-remove-featured-img"){
						e.preventDefault();
						hiddenInput.value = "";
						wrap.innerHTML = "";
						setBtn.textContent = "Set Featured Image";
						e.target.remove();
						removeBtn = null;
					}
				});
			})();

			/* --- JSON field add/remove --- */
			document.addEventListener("click", function(e){
				if(e.target.classList.contains("tp-json-add")){
					e.preventDefault();
					var list = e.target.parentElement.querySelector(".tp-json-list");
					var div = document.createElement("div");
					div.className = "tp-json-item";
					div.innerHTML = "<input type=\"text\" value=\"\" /><button type=\"button\" class=\"button tp-json-remove\">&times;</button>";
					list.appendChild(div);
				}
				if(e.target.classList.contains("tp-json-remove")){
					e.preventDefault();
					e.target.closest(".tp-json-item").remove();
				}
			});

			/* Serialize JSON fields on form submit */
			var form = document.getElementById("post");
			if(form){
				form.addEventListener("submit", function(){
					document.querySelectorAll(".tp-json-field").forEach(function(field){
						var hidden = field.querySelector("input.tp-json-value");
						var items = [];
						field.querySelectorAll(".tp-json-list input[type=\"text\"]").forEach(function(inp){
							var v = inp.value.trim();
							if(v) items.push(v);
						});
						if(hidden) hidden.value = JSON.stringify(items);
					});
				});
			}
		})();
		</script>';
	}

	/* =====================================================================
	   Field Render Helpers
	   ===================================================================== */

	/**
	 * Render a text input field.
	 */
	private function render_field( int $post_id, string $key, string $label, string $type = 'text', string $description = '' ) {
		$meta_key = self::META_PREFIX . $key;
		$value    = get_post_meta( $post_id, $meta_key, true );

		echo '<div class="tp-field">';
		echo '<label for="' . esc_attr( $meta_key ) . '">' . esc_html( $label ) . '</label>';

		$step = ( 'number' === $type ) ? ' step="any"' : '';

		echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $meta_key ) . '" '
			. 'name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $value ) . '"' . $step . ' />';

		if ( $description ) {
			echo '<span class="description">' . esc_html( $description ) . '</span>';
		}

		echo '</div>';
	}

	/**
	 * Render a select field.
	 */
	private function render_select( int $post_id, string $key, string $label, array $options ) {
		$meta_key = self::META_PREFIX . $key;
		$value    = get_post_meta( $post_id, $meta_key, true );

		echo '<div class="tp-field">';
		echo '<label for="' . esc_attr( $meta_key ) . '">' . esc_html( $label ) . '</label>';
		echo '<select id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '">';
		echo '<option value="">' . esc_html__( '— Select —', 'tenprojects-ai-matcher' ) . '</option>';

		foreach ( $options as $opt_value => $opt_label ) {
			echo '<option value="' . esc_attr( $opt_value ) . '"' . selected( $value, $opt_value, false ) . '>'
				. esc_html( $opt_label ) . '</option>';
		}

		echo '</select>';
		echo '</div>';
	}

	/**
	 * Render a textarea field.
	 */
	private function render_textarea( int $post_id, string $key, string $label, string $description = '' ) {
		$meta_key = self::META_PREFIX . $key;
		$value    = get_post_meta( $post_id, $meta_key, true );

		echo '<div class="tp-field">';
		echo '<label for="' . esc_attr( $meta_key ) . '">' . esc_html( $label ) . '</label>';
		echo '<textarea id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" rows="3">'
			. esc_textarea( $value ) . '</textarea>';

		if ( $description ) {
			echo '<span class="description">' . esc_html( $description ) . '</span>';
		}

		echo '</div>';
	}

	/**
	 * Render a checkbox field.
	 */
	private function render_checkbox( int $post_id, string $key, string $label ) {
		$meta_key = self::META_PREFIX . $key;
		$value    = get_post_meta( $post_id, $meta_key, true );

		echo '<div class="tp-field">';
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr( $meta_key ) . '" value="1"' . checked( $value, '1', false ) . ' /> ';
		echo esc_html( $label );
		echo '</label>';
		echo '</div>';
	}

	/**
	 * Render a JSON list field (for pros, cons, highlights, etc.).
	 */
	private function render_json_field( int $post_id, string $key, string $label ) {
		$meta_key = self::META_PREFIX . $key;
		$raw      = get_post_meta( $post_id, $meta_key, true );
		$items    = json_decode( $raw, true );

		if ( ! is_array( $items ) ) {
			$items = array();
		}

		echo '<div class="tp-field tp-json-field">';
		echo '<label>' . esc_html( $label ) . '</label>';
		echo '<input type="hidden" class="tp-json-value" name="' . esc_attr( $meta_key ) . '" '
			. 'value="' . esc_attr( wp_json_encode( $items ) ) . '" />';
		echo '<div class="tp-json-list">';

		foreach ( $items as $item ) {
			echo '<div class="tp-json-item">';
			echo '<input type="text" value="' . esc_attr( $item ) . '" />';
			echo '<button type="button" class="button tp-json-remove">&times;</button>';
			echo '</div>';
		}

		echo '</div>';
		echo '<button type="button" class="button tp-json-add">'
			. esc_html__( '+ Add Item', 'tenprojects-ai-matcher' ) . '</button>';
		echo '</div>';
	}

	/**
	 * Render a gallery/image uploader field.
	 *
	 * Shows a grid of thumbnails with a "Choose Media" button.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key without prefix.
	 * @param string $label   Field label.
	 */
	private function render_gallery_field( int $post_id, string $key, string $label ) {
		$meta_key = self::META_PREFIX . $key;
		$ids_str  = get_post_meta( $post_id, $meta_key, true );
		$ids      = array_filter( array_map( 'intval', explode( ',', $ids_str ) ) );

		echo '<div class="tp-field tp-gallery-field" data-field-key="' . esc_attr( $meta_key ) . '">';
		echo '<label>' . esc_html( $label ) . '</label>';
		echo '<input type="hidden" class="tp-gallery-ids" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $ids_str ) . '" />';

		echo '<div class="tp-gallery-grid">';
		foreach ( $ids as $att_id ) {
			$thumb_url = wp_get_attachment_image_url( $att_id, 'thumbnail' );
			if ( $thumb_url ) {
				echo '<div class="tp-gallery-item" data-id="' . esc_attr( $att_id ) . '">';
				echo '<img src="' . esc_url( $thumb_url ) . '" alt="" />';
				echo '<button type="button" class="tp-gallery-remove" title="Remove">&times;</button>';
				echo '</div>';
			}
		}
		echo '</div>';

		echo '<button type="button" class="button tp-gallery-add">';
		echo '<span class="dashicons dashicons-admin-media" style="vertical-align:middle;margin-right:4px;"></span>';
		echo esc_html__( 'Choose Media', 'tenprojects-ai-matcher' );
		echo '</button>';
		echo '</div>';
	}

	/* =====================================================================
	   Tab Panel Renderers
	   ===================================================================== */

	/**
	 * Graphics & Media panel — banner images, gallery, developer logo.
	 */
	private function render_graphics_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel active" data-panel="graphics">';

		// 2×2 horizontal grid for gallery sections.
		echo '<div class="tp-graphics-grid">';

		echo '<div class="tp-field-group" style="margin-bottom:0;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Banner — Desktop', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'banner_desktop_ids', __( 'Desktop Banner (1400×600 recommended)', 'tenprojects-ai-matcher' ) );
		echo '<span class="description" style="margin-top:6px;display:block;">Used in the gallery strip on PDP. Add 2-5 project images.</span>';
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group" style="margin-bottom:0;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Featured Image', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div style="padding:14px 16px;">';
		$thumb_id = get_post_thumbnail_id( $post->ID );
		$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';
		echo '<div id="tp-featured-img-wrap">';
		if ( $thumb_url ) {
			echo '<img src="' . esc_url( $thumb_url ) . '" style="max-width:100%;height:auto;border-radius:8px;margin-bottom:8px;display:block;">';
		}
		echo '</div>';
		echo '<button type="button" class="button" id="tp-set-featured-img">' . ( $thumb_id ? 'Change Image' : 'Set Featured Image' ) . '</button>';
		if ( $thumb_id ) {
			echo ' <button type="button" class="button" id="tp-remove-featured-img" style="color:#a00;">Remove</button>';
		}
		echo '<input type="hidden" name="_thumbnail_id" id="tp-featured-img-id" value="' . esc_attr( $thumb_id ?: '' ) . '">';
		echo '<span class="description" style="margin-top:6px;display:block;">Used as thumbnail in listings, SEO, and social sharing (1200×630 recommended).</span>';
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group" style="margin-bottom:0;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Developer Logo', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'developer_logo_id', __( 'Logo (200×200, transparent PNG)', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group" style="margin-bottom:0;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'QR Code', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'qr_code_id', __( 'RERA QR Code (100×100 pixels)', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		echo '</div>';

		echo '</div>';
	}

	/**
	 * Contact Details panel — phone, email, sales office.
	 */
	private function render_contact_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="contact">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Contact Details', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row" style="grid-template-columns:1fr;">';
		$this->render_field( $post->ID, 'phone', __( 'Contact Number', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * About Developer panel.
	 */
	private function render_developer_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="developer">';

		// Row 1: Project Name, Developer, Location (3 columns).
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'project_name', __( 'Project Name', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'developer_name', __( 'By Developer', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'project_location', __( 'Project Location', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		// Row 2: Land Parcel, Floors, Possession (3 columns).
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'land_parcel', __( 'Land Parcel', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., 5.5 Acres', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'floors_display', __( 'Floors', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., 3 Towers | G + 45 Floors', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'expected_possession', __( 'Possession', 'tenprojects-ai-matcher' ), 'text', __( 'Estimated possession date.', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		// Row 3: RERA Number, RERA Verified.
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_field( $post->ID, 'rera_number', __( 'RERA Number', 'tenprojects-ai-matcher' ) );
		$this->render_checkbox( $post->ID, 'verified', __( 'RERA Verified', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		// Row 3b: Homepage flags.
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_checkbox( $post->ID, 'is_new_launch', __( 'New Launch (homepage carousel)', 'tenprojects-ai-matcher' ) );
		$this->render_checkbox( $post->ID, 'is_featured', __( 'Featured Project (homepage carousel)', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		// Row 4: Price Range (2 columns).
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_field( $post->ID, 'price_display_min', __( 'Price Min', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., 88 Lacs or 2.18 Cr', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'price_display_max', __( 'Price Max', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., 1.50 Cr or 3.50 Cr', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		// Row 5: Short Overview, About Developer (2 columns).
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_textarea( $post->ID, 'short_overview', __( 'Short Overview of Project', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'developer_about', __( 'About Developer', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		// Row 6: Google Review, Available Configurations (2 columns).
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_field( $post->ID, 'google_review_rating', __( 'Google Customer Review', 'tenprojects-ai-matcher' ), 'text', __( 'Out of 5 (On Google)', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'available_configs_text', __( 'Available Configurations', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., Luxurious 2 & 3 BHK', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		// Row 7: Project Scale (3 columns).
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Project Scale', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'total_towers', __( 'Total Towers', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'total_floors', __( 'Total Floors', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'total_units', __( 'Total Units', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		// Row 8: Pros & Cons.
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Pros & Cons', 'tenprojects-ai-matcher' ) . '</div>';
		$this->render_json_field( $post->ID, 'pros', __( 'Pros', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'cons', __( 'Cons', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Offers panel — sponsorship, special offers.
	 */
	private function render_offers_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="offers">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Special Offers & Sponsorship', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_checkbox( $post->ID, 'sponsored', __( 'Sponsored Project', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'sponsor_label', __( 'Sponsor Label', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., "Featured", "Premium"', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '<div class="tp-field-row" style="grid-template-columns:1fr;">';
		echo '<div class="tp-field">';
		$this->render_json_field( $post->ID, 'offers', __( 'Current Offers', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}



	/**
	 * Location Details panel.
	 */
	private function render_location_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="location">';

		// Row 1: Google Maps Address.
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Google Maps', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row" style="grid-template-columns:1fr;">';
		$this->render_field( $post->ID, 'address_pin', __( 'Google My Business Name', 'tenprojects-ai-matcher' ), 'text', __( 'Enter the exact Google My Business name. The map will be auto-generated on the frontend.', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		// Row 2: Location Brief.
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Location Description', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row" style="grid-template-columns:1fr;">';
		$this->render_textarea( $post->ID, 'location_brief', __( 'About the Location', 'tenprojects-ai-matcher' ), __( 'Short paragraph about the area — shown above the map on PDP.', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		// Row 3: Location Advantages (side by side).
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Location Advantages', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_textarea( $post->ID, 'location_advantage_1', __( 'Location Advantages [Max 8 Pointers]', 'tenprojects-ai-matcher' ), __( 'HTML list: <ul><li>Vashi Railway Station - 5 min</li></ul>', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'location_advantage_2', __( 'Nearby Connectivity [Max 4 Pointers]', 'tenprojects-ai-matcher' ), __( 'HTML list: <ul><li>Inorbit Mall - 5 min</li></ul>', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		// Row 4: Distance to Key Points.
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Distance to Key Points (km)', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row tp-cols-4">';
		$this->render_field( $post->ID, 'railway_distance_km', __( 'Railway Station', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'metro_distance_km', __( 'Metro Station', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'highway_distance_km', __( 'Highway', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'airport_distance_km', __( 'Airport', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '<div class="tp-field-row tp-cols-4">';
		$this->render_field( $post->ID, 'school_distance_km', __( 'School', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'hospital_distance_km', __( 'Hospital', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'mall_distance_km', __( 'Mall / Shopping', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'employment_hub_km', __( 'Employment Hub', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Scoring & Editorial panel — scoring metrics, editorial content, verification.
	 */

	/**
	 * Save meta box data.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	/* =====================================================================
	   Category-Specific Panels
	   ===================================================================== */

	/**
	 * Rental Details panel.
	 */
	private function render_rental_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="rental">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Rent & Deposit</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'monthly_rent', 'Monthly Rent (₹)', 'number', 'e.g. 25000' );
		$this->render_field( $post->ID, 'security_deposit', 'Security Deposit (₹)', 'number' );
		$this->render_field( $post->ID, 'maintenance_charges', 'Maintenance (₹/mo)', 'number' );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'brokerage', 'Brokerage', 'text', 'e.g. 1 Month or Zero' );
		$this->render_field( $post->ID, 'lock_in_period', 'Lock-in Period', 'text', 'e.g. 6 Months' );
		$this->render_field( $post->ID, 'notice_period', 'Notice Period', 'text', 'e.g. 1 Month' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Tenant & Availability</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'available_from', 'Available From', 'date' );
		$this->render_select( $post->ID, 'tenant_preferred', 'Tenant Preferred', array(
			'family'  => 'Family',
			'bachelor' => 'Bachelor',
			'company' => 'Company',
			'any'     => 'Any',
		) );
		$this->render_select( $post->ID, 'furnishing_status', 'Furnishing Status', array(
			'furnished'      => 'Fully Furnished',
			'semi-furnished' => 'Semi-Furnished',
			'unfurnished'    => 'Unfurnished',
		) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'pets_allowed', 'Pets Allowed', array( 'yes' => 'Yes', 'no' => 'No' ) );
		$this->render_select( $post->ID, 'nonveg_allowed', 'Non-veg Allowed', array( 'yes' => 'Yes', 'no' => 'No' ) );
		$this->render_select( $post->ID, 'water_supply', 'Water Supply', array( '24_hours' => '24 Hours', 'timed' => 'Timed' ) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Furnishing Details</div>';
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_textarea( $post->ID, 'furnishing_details', 'Furnishing Details', 'e.g. 2 AC, Geyser, Fridge, Washing Machine, Sofa, 2 Beds' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Google Maps</div>';
		echo '<div class="tp-field-row" style="grid-template-columns:1fr;">';
		$this->render_field( $post->ID, 'address_pin', 'Google My Business Name', 'text', 'Enter the exact Google My Business name. The map will be auto-generated on the frontend.' );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Commercial Details panel.
	 */
	private function render_commercial_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="commercial">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Property Type & Grade</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'commercial_type', 'Property Sub-type', array(
			'office'     => 'Office Space',
			'retail'     => 'Retail Shop',
			'showroom'   => 'Showroom',
			'warehouse'  => 'Warehouse',
			'coworking'  => 'Co-working',
			'it_park'    => 'IT Park',
		) );
		$this->render_select( $post->ID, 'building_grade', 'Building Grade', array(
			'grade_a' => 'Grade A',
			'grade_b' => 'Grade B',
			'grade_c' => 'Grade C',
		) );
		$this->render_select( $post->ID, 'fitout_status', 'Fit-out Status', array(
			'bare_shell'     => 'Bare Shell',
			'warm_shell'     => 'Warm Shell',
			'plug_and_play'  => 'Plug and Play',
		) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Area & Pricing</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'commercial_carpet', 'Carpet Area (sqft)', 'number' );
		$this->render_field( $post->ID, 'price_per_sqft', 'Price Per Sqft (₹)', 'number' );
		$this->render_field( $post->ID, 'cam_charges', 'CAM Charges (₹/sqft/mo)', 'number' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Specifications</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'power_load', 'Power Load (KVA)', 'text' );
		$this->render_field( $post->ID, 'seating_capacity', 'Seating Capacity', 'number' );
		$this->render_field( $post->ID, 'cabins_count', 'Cabins/Rooms', 'number' );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'washrooms_count', 'Washrooms', 'number' );
		$this->render_field( $post->ID, 'parking_bays', 'Parking Bays', 'number' );
		$this->render_select( $post->ID, 'hvac_type', 'HVAC / AC', array(
			'centralized' => 'Centralized AC',
			'split'       => 'Split AC',
			'none'        => 'None',
		) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'fire_noc', 'Fire NOC', array( 'yes' => 'Yes', 'no' => 'No' ) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Lease Terms</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'lease_term', 'Lease Term', 'text', 'e.g. 5 Years' );
		$this->render_field( $post->ID, 'lock_in_period', 'Lock-in Period', 'text', 'e.g. 3 Years' );
		$this->render_field( $post->ID, 'escalation_clause', 'Escalation Clause', 'text', 'e.g. 5% per year' );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Plot Details panel.
	 */
	private function render_plot_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="plot">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Plot Type & Area</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'plot_type', 'Plot Type', array(
			'residential'  => 'Residential',
			'commercial'   => 'Commercial',
			'agricultural' => 'Agricultural',
			'mixed_use'    => 'Mixed-use',
		) );
		$this->render_field( $post->ID, 'plot_area', 'Plot Area (sqft)', 'number' );
		$this->render_field( $post->ID, 'fsi', 'FSI / FAR', 'number', 'e.g. 1.5' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Dimensions & Layout</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'plot_width', 'Width / Frontage (ft)', 'number' );
		$this->render_field( $post->ID, 'plot_depth', 'Depth / Length (ft)', 'number' );
		$this->render_select( $post->ID, 'corner_plot', 'Corner Plot', array( 'yes' => 'Yes', 'no' => 'No' ) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'road_width', 'Road Width Facing (ft)', 'text', 'e.g. 30 ft' );
		$this->render_select( $post->ID, 'sides_open', 'Sides Open', array(
			'1' => '1 Side', '2' => '2 Sides', '3' => '3 Sides', '4' => '4 Sides (All)',
		) );
		$this->render_select( $post->ID, 'topography', 'Topography', array( 'flat' => 'Flat', 'sloped' => 'Sloped' ) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Infrastructure & Approvals</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'boundary_wall', 'Boundary Wall', array(
			'full' => 'Fully Constructed', 'partial' => 'Partial', 'none' => 'None',
		) );
		$this->render_field( $post->ID, 'permissible_floors', 'Permissible Floors', 'text', 'e.g. G+3' );
		$this->render_select( $post->ID, 'gated_community', 'Gated Community', array( 'yes' => 'Yes', 'no' => 'No' ) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'water_connection', 'Water Connection', array( 'yes' => 'Available', 'no' => 'Not Available' ) );
		$this->render_select( $post->ID, 'electricity_connection', 'Electricity Connection', array( 'yes' => 'Available', 'no' => 'Not Available' ) );
		$this->render_select( $post->ID, 'sewage_connection', 'Sewage Connection', array( 'yes' => 'Available', 'no' => 'Not Available' ) );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * PG Details panel.
	 */
	private function render_pg_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="pg">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">PG For & Occupant</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'pg_gender', 'PG For', array(
			'boys'   => 'Boys Only',
			'girls'  => 'Girls Only',
			'unisex' => 'Unisex (Co-ed)',
		) );
		$this->render_select( $post->ID, 'pg_occupant', 'Occupant Type', array(
			'students'     => 'Students',
			'working'      => 'Working Professionals',
			'any'          => 'Any',
		) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Rent by Sharing</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'pg_single_rent', 'Single Sharing (₹/mo)', 'number' );
		$this->render_field( $post->ID, 'pg_double_rent', 'Double Sharing (₹/mo)', 'number' );
		$this->render_field( $post->ID, 'pg_triple_rent', 'Triple Sharing (₹/mo)', 'number' );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'pg_deposit', 'Security Deposit (₹)', 'number' );
		$this->render_field( $post->ID, 'pg_notice_period', 'Notice Period', 'text', 'e.g. 1 Month' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Food & Kitchen</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'pg_meals', 'Meals Included', array(
			'none'              => 'None',
			'breakfast_dinner'  => 'Breakfast + Dinner',
			'all_meals'         => 'All Meals (3)',
		) );
		$this->render_select( $post->ID, 'pg_meal_type', 'Meal Type', array(
			'veg_only'    => 'Veg Only',
			'veg_nonveg'  => 'Veg + Non-veg',
		) );
		$this->render_select( $post->ID, 'pg_kitchen', 'Kitchen Access', array( 'yes' => 'Yes', 'no' => 'No' ) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Facilities</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'pg_wifi', 'Wi-Fi', array(
			'included' => 'Included', 'paid' => 'Paid Extra', 'none' => 'None',
		) );
		$this->render_select( $post->ID, 'pg_laundry', 'Laundry', array(
			'included' => 'Included', 'shared' => 'Shared Machine', 'paid' => 'Paid Service', 'none' => 'None',
		) );
		$this->render_select( $post->ID, 'pg_housekeeping', 'Housekeeping', array(
			'daily' => 'Daily', 'weekly' => 'Weekly', 'none' => 'None',
		) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'pg_ac', 'AC Rooms', array(
			'yes' => 'Yes', 'no' => 'No', 'optional' => 'Optional (Extra)',
		) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">House Rules</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'pg_smoking', 'Smoking', array( 'yes' => 'Allowed', 'no' => 'Not Allowed' ) );
		$this->render_select( $post->ID, 'pg_drinking', 'Drinking', array( 'yes' => 'Allowed', 'no' => 'Not Allowed' ) );
		$this->render_select( $post->ID, 'pg_guests', 'Guests', array(
			'yes' => 'Allowed', 'common' => 'In Common Areas Only', 'no' => 'Not Allowed',
		) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'pg_curfew', 'Curfew Time', 'text', 'e.g. 10 PM or No Curfew' );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/* =====================================================================
	   Save
	   ===================================================================== */

	public function save_meta( int $post_id, \WP_Post $post ) {
		// Verify nonce.
		if ( ! isset( $_POST['tp_project_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['tp_project_meta_nonce'], 'tp_project_meta' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save featured image (thumbnail).
		if ( isset( $_POST['_thumbnail_id'] ) ) {
			$thumb_id = absint( $_POST['_thumbnail_id'] );
			if ( $thumb_id ) {
				set_post_thumbnail( $post_id, $thumb_id );
			} else {
				delete_post_thumbnail( $post_id );
			}
		}

		$fields = $this->get_meta_fields();

		foreach ( $fields as $key => $schema ) {
			$meta_key = self::META_PREFIX . $key;

			if ( 'boolean' === $schema['type'] ) {
				$value = isset( $_POST[ $meta_key ] ) ? '1' : '0';
				update_post_meta( $post_id, $meta_key, $value );
				continue;
			}

			if ( ! isset( $_POST[ $meta_key ] ) ) {
				continue;
			}

			$value = $_POST[ $meta_key ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized below.

			// Apply the registered sanitize callback.
			if ( is_callable( $schema['sanitize_callback'] ) ) {
				$value = call_user_func( $schema['sanitize_callback'], $value );
			}

			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}
