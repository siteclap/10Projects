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
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'project_permalink' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_scripts' ) );

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
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'query_var'           => true,
			'rewrite'             => array(
				'slug'       => 'navi-mumbai/%tp_location_area%',
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

		$terms = wp_get_object_terms( $post->ID, 'tp_location_area' );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$post_link = str_replace( '%tp_location_area%', $terms[0]->slug, $post_link );
		} else {
			$post_link = str_replace( '%tp_location_area%', 'uncategorized', $post_link );
		}

		return $post_link;
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
			'price_display_min'      => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'price_display_max'      => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'primary_config'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// --- Construction ---
			'rera_registration_date' => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'construction_start'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'construction_stage'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'construction_progress'  => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'promised_possession'    => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'rera_possession'        => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'expected_possession'    => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
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
			'graphics'    => __( 'Graphics & Media', 'tenprojects-ai-matcher' ),
			'contact'     => __( 'Contact Details', 'tenprojects-ai-matcher' ),
			'developer'   => __( 'About Developer', 'tenprojects-ai-matcher' ),
			'offers'      => __( 'Offers', 'tenprojects-ai-matcher' ),
			'pricing'     => __( 'Carpet Area & Price', 'tenprojects-ai-matcher' ),
			'construction' => __( 'Construction', 'tenprojects-ai-matcher' ),
			'location'    => __( 'Location Details', 'tenprojects-ai-matcher' ),
			'scoring'     => __( 'Scoring & Editorial', 'tenprojects-ai-matcher' ),
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
		$this->render_contact_panel( $post );
		$this->render_developer_panel( $post );
		$this->render_offers_panel( $post );
		$this->render_pricing_panel( $post );
		$this->render_construction_panel( $post );
		$this->render_location_panel( $post );
		$this->render_scoring_panel( $post );
		echo '</div>';

		echo '</div>';

		// Inline JS — tab switching + gallery upload (bypasses cached admin.js).
		echo '<script>
		(function(){
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
		$this->render_gallery_field( $post->ID, 'banner_desktop_ids', __( 'Desktop Banner (1920×800)', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group" style="margin-bottom:0;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Banner — Mobile', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'banner_mobile_ids', __( 'Mobile Banner (768×600)', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group" style="margin-bottom:0;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Project Gallery', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'gallery_ids', __( 'Gallery Images', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group" style="margin-bottom:0;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Developer Logo', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'developer_logo_id', __( 'Developer Logo', 'tenprojects-ai-matcher' ) );
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

		// Row 1: Developer, Location, Developer ID (3 columns).
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'developer_name', __( 'By Developer', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'project_location', __( 'Project Location', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'developer_id', __( 'Developer ID', 'tenprojects-ai-matcher' ), 'number', __( 'Post ID of the developer CPT.', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		// Row 2: Land Parcel, Floors, Possession (3 columns).
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'land_parcel', __( 'Land Parcel', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., 5.5 Acres', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'floors_display', __( 'Floors', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., 3 Towers | G + 45 Floors', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'expected_possession', __( 'Possession', 'tenprojects-ai-matcher' ), 'text', __( 'Estimated possession date.', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		// Row 3: RERA Number, QR Code (2 columns).
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_field( $post->ID, 'rera_number', __( 'RERA Number', 'tenprojects-ai-matcher' ) );
		echo '<div class="tp-field">';
		echo '<label>' . esc_html__( 'QR Code', 'tenprojects-ai-matcher' ) . '</label>';
		echo '<span class="description" style="margin-bottom:4px;">' . esc_html__( '100 x 100 pixels', 'tenprojects-ai-matcher' ) . '</span>';
		$this->render_gallery_field( $post->ID, 'qr_code_id', '' );
		echo '</div>';
		echo '</div>';

		// Row 4: Short Overview, About Developer (2 columns).
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_textarea( $post->ID, 'short_overview', __( 'Short Overview of Project', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'developer_about', __( 'About Developer', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		// Row 5: Google Review, Available Configurations (2 columns).
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_field( $post->ID, 'google_review_rating', __( 'Google Customer Review', 'tenprojects-ai-matcher' ), 'text', __( 'Out of 5 (On Google)', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'available_configs_text', __( 'Available Configurations', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., Luxurious 2 & 3 BHK', 'tenprojects-ai-matcher' ) );
		echo '</div>';
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
	 * Carpet Area & Price panel — pricing, RERA, configurations.
	 */
	private function render_pricing_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="pricing">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'RERA & Status', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'rera_number', __( 'RERA Number', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'rera_phase', __( 'RERA Phase', 'tenprojects-ai-matcher' ) );
		$this->render_select( $post->ID, 'status', __( 'Status', 'tenprojects-ai-matcher' ), array(
			'active'   => __( 'Active', 'tenprojects-ai-matcher' ),
			'paused'   => __( 'Paused', 'tenprojects-ai-matcher' ),
			'sold_out' => __( 'Sold Out', 'tenprojects-ai-matcher' ),
			'delisted' => __( 'Delisted', 'tenprojects-ai-matcher' ),
		) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Pricing & Configuration', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'price_display_min', __( 'Price Display Min (₹)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'price_display_max', __( 'Price Display Max (₹)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'primary_config', __( 'Primary Configuration', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., 2 BHK, 3 BHK', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_field( $post->ID, 'launch_date', __( 'Launch Date', 'tenprojects-ai-matcher' ), 'date' );
		$this->render_checkbox( $post->ID, 'verified', __( 'RERA Verified', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Construction panel.
	 */
	private function render_construction_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="construction">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Construction Status', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'construction_stage', __( 'Construction Stage', 'tenprojects-ai-matcher' ), array(
			'pre_launch'        => __( 'Pre-Launch', 'tenprojects-ai-matcher' ),
			'excavation'        => __( 'Excavation', 'tenprojects-ai-matcher' ),
			'foundation'        => __( 'Foundation', 'tenprojects-ai-matcher' ),
			'plinth'            => __( 'Plinth', 'tenprojects-ai-matcher' ),
			'superstructure'    => __( 'Superstructure', 'tenprojects-ai-matcher' ),
			'brickwork'         => __( 'Brickwork', 'tenprojects-ai-matcher' ),
			'internal_plaster'  => __( 'Internal Plaster', 'tenprojects-ai-matcher' ),
			'external_plaster'  => __( 'External Plaster', 'tenprojects-ai-matcher' ),
			'flooring'          => __( 'Flooring', 'tenprojects-ai-matcher' ),
			'finishing'         => __( 'Finishing', 'tenprojects-ai-matcher' ),
			'ready_to_move'     => __( 'Ready to Move', 'tenprojects-ai-matcher' ),
		) );
		$this->render_field( $post->ID, 'construction_progress', __( 'Construction Progress (%)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'construction_start', __( 'Construction Start', 'tenprojects-ai-matcher' ), 'date' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Possession Dates', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row tp-cols-4">';
		$this->render_field( $post->ID, 'rera_registration_date', __( 'RERA Registration Date', 'tenprojects-ai-matcher' ), 'date' );
		$this->render_field( $post->ID, 'promised_possession', __( 'Promised Possession', 'tenprojects-ai-matcher' ), 'date' );
		$this->render_field( $post->ID, 'rera_possession', __( 'RERA Possession', 'tenprojects-ai-matcher' ), 'date' );
		$this->render_field( $post->ID, 'expected_possession', __( 'Expected Possession', 'tenprojects-ai-matcher' ), 'date' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Project Scale', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'total_towers', __( 'Total Towers', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'total_floors', __( 'Total Floors', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'total_units', __( 'Total Units', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Location Details panel.
	 */
	private function render_location_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="location">';

		// Row 1: Address To Pin (Google Maps embed address).
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Address & Map', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row" style="grid-template-columns:1fr;">';
		$this->render_field( $post->ID, 'address_pin', __( 'Address To Pin', 'tenprojects-ai-matcher' ), 'text', __( 'Google My Business headline — auto-shows on Google Maps.', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_field( $post->ID, 'latitude', __( 'Latitude', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'longitude', __( 'Longitude', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '<div class="tp-field-row" style="grid-template-columns:1fr;">';
		$this->render_textarea( $post->ID, 'address', __( 'Full Address', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		// Row 2: Location Advantage Text — Part 1 & Part 2 (side by side).
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Location Advantages', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_textarea( $post->ID, 'location_advantage_1', __( 'Location Advantage Text — Part 1 [Max 8 Pointers]', 'tenprojects-ai-matcher' ), __( 'e.g., Mumbra-Panvel Highway - 3 min', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'location_advantage_2', __( 'Location Advantage Text — Part 2 [Max 4 Pointers]', 'tenprojects-ai-matcher' ), __( 'e.g., Proposed Metro Line - 5 min', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		// Row 3: Small Location Brief.
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Location Description', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row" style="grid-template-columns:1fr;">';
		$this->render_textarea( $post->ID, 'location_brief', __( 'Small Location Brief', 'tenprojects-ai-matcher' ), __( 'A short paragraph describing the location and its connectivity.', 'tenprojects-ai-matcher' ) );
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

		// Row 5: Infrastructure & Utilities.
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Infrastructure & Utilities', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'water_source', __( 'Water Source', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'power_backup', __( 'Power Backup', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'parking_info', __( 'Parking Info', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'open_space_ratio', __( 'Open Space Ratio (%)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_select( $post->ID, 'density_rating', __( 'Density Rating', 'tenprojects-ai-matcher' ), array(
			'low'    => __( 'Low', 'tenprojects-ai-matcher' ),
			'medium' => __( 'Medium', 'tenprojects-ai-matcher' ),
			'high'   => __( 'High', 'tenprojects-ai-matcher' ),
		) );
		$this->render_field( $post->ID, 'maintenance_estimate', __( 'Maintenance Estimate (₹/month)', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Scoring & Editorial panel — scoring metrics, editorial content, verification.
	 */
	private function render_scoring_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="scoring">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Legal & Trust', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'legal_confidence', __( 'Legal Confidence (0-100)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'possession_confidence', __( 'Possession Confidence (0-100)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_textarea( $post->ID, 'bank_approved', __( 'Bank Approvals', 'tenprojects-ai-matcher' ), __( 'Comma-separated list of approved banks.', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '<div class="tp-field-row" style="grid-template-columns:1fr;">';
		$this->render_select( $post->ID, 'litigation_status', __( 'Litigation Status', 'tenprojects-ai-matcher' ), array(
			'none'    => __( 'None', 'tenprojects-ai-matcher' ),
			'minor'   => __( 'Minor', 'tenprojects-ai-matcher' ),
			'major'   => __( 'Major', 'tenprojects-ai-matcher' ),
			'unknown' => __( 'Unknown', 'tenprojects-ai-matcher' ),
		) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Investment Metrics', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'micro_market_price', __( 'Micro Market Price (₹/sqft)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'rental_range_min', __( 'Rental Range Min (₹)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'rental_range_max', __( 'Rental Range Max (₹)', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'vacancy_risk', __( 'Vacancy Risk', 'tenprojects-ai-matcher' ), array(
			'low'    => __( 'Low', 'tenprojects-ai-matcher' ),
			'medium' => __( 'Medium', 'tenprojects-ai-matcher' ),
			'high'   => __( 'High', 'tenprojects-ai-matcher' ),
		) );
		$this->render_field( $post->ID, 'appreciation_score', __( 'Appreciation Score (0-100)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'rental_yield_pct', __( 'Rental Yield (%)', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Editorial Content', 'tenprojects-ai-matcher' ) . '</div>';
		$this->render_json_field( $post->ID, 'highlights', __( 'Highlights', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'pros', __( 'Pros', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'cons', __( 'Cons', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'risks', __( 'Risks', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'best_for', __( 'Best For', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'not_for', __( 'Not Ideal For', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Verification', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'last_verified', __( 'Last Verified', 'tenprojects-ai-matcher' ), 'date' );
		$this->render_field( $post->ID, 'reviewed_by', __( 'Reviewed By', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		$this->render_textarea( $post->ID, 'verification_checklist', __( 'Verification Checklist', 'tenprojects-ai-matcher' ), __( 'JSON or comma-separated checklist.', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'sources', __( 'Sources', 'tenprojects-ai-matcher' ), __( 'Links and references used for verification.', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Save meta box data.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
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
