<?php
/**
 * Project Custom Post Type.
 *
 * Registers the tp_project CPT with all meta fields, taxonomies,
 * and a tabbed admin meta box for project data entry.
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
				'slug'       => 'projects/%tp_location_area%',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => 'projects',
			'hierarchical'        => false,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-building',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
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
			// Basic Info.
			'rera_number'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'rera_phase'             => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'developer_id'          => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'status'                 => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'sponsored'              => array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean' ),
			'sponsor_label'          => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'launch_date'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'verified'               => array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean' ),
			'price_display_min'      => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'price_display_max'      => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'primary_config'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// Construction.
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

			// Location.
			'latitude'               => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'longitude'              => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'address'                => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'railway_distance_km'    => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'metro_distance_km'      => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'highway_distance_km'    => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'airport_distance_km'    => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'school_distance_km'     => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'hospital_distance_km'   => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'mall_distance_km'       => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'employment_hub_km'      => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),

			// Scoring Data.
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

			// Editorial (JSON list fields).
			'highlights'             => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'pros'                   => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'cons'                   => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'risks'                  => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'best_for'               => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'not_for'                => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),

			// Infrastructure.
			'open_space_ratio'       => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'density_rating'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'maintenance_estimate'   => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'parking_info'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'water_source'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'power_backup'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// Verification.
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
			__( 'Project Details', 'tenprojects-ai-matcher' ),
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the tabbed meta box.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public function render_meta_box( \WP_Post $post ) {
		wp_nonce_field( 'tp_project_meta', 'tp_project_meta_nonce' );

		$tabs = array(
			'basic'          => __( 'Basic Info', 'tenprojects-ai-matcher' ),
			'construction'   => __( 'Construction', 'tenprojects-ai-matcher' ),
			'location'       => __( 'Location', 'tenprojects-ai-matcher' ),
			'scoring'        => __( 'Scoring Data', 'tenprojects-ai-matcher' ),
			'editorial'      => __( 'Editorial', 'tenprojects-ai-matcher' ),
			'infrastructure' => __( 'Infrastructure', 'tenprojects-ai-matcher' ),
			'verification'   => __( 'Verification', 'tenprojects-ai-matcher' ),
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

		// Tab panels.
		$this->render_basic_panel( $post );
		$this->render_construction_panel( $post );
		$this->render_location_panel( $post );
		$this->render_scoring_panel( $post );
		$this->render_editorial_panel( $post );
		$this->render_infrastructure_panel( $post );
		$this->render_verification_panel( $post );

		echo '</div>';
	}

	/**
	 * Render a text input field.
	 *
	 * @param int    $post_id     Post ID.
	 * @param string $key         Meta key without prefix.
	 * @param string $label       Field label.
	 * @param string $type        Input type (text, number, date).
	 * @param string $description Optional field description.
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
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key without prefix.
	 * @param string $label   Field label.
	 * @param array  $options Associative array of value => display.
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
	 *
	 * @param int    $post_id     Post ID.
	 * @param string $key         Meta key without prefix.
	 * @param string $label       Field label.
	 * @param string $description Optional description.
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
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key without prefix.
	 * @param string $label   Field label.
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
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key without prefix.
	 * @param string $label   Field label.
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
	 * Render the Basic Info panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_basic_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel active" data-panel="basic">';

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
		echo '<div class="tp-field-group__title">' . esc_html__( 'Dates & Pricing', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'launch_date', __( 'Launch Date', 'tenprojects-ai-matcher' ), 'date' );
		$this->render_field( $post->ID, 'developer_id', __( 'Developer ID', 'tenprojects-ai-matcher' ), 'number', __( 'Post ID of the developer.', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'primary_config', __( 'Primary Configuration', 'tenprojects-ai-matcher' ), 'text', __( 'e.g., 2 BHK, 3 BHK', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'price_display_min', __( 'Price Display Min (₹)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'price_display_max', __( 'Price Display Max (₹)', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Sponsorship & Verification', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_checkbox( $post->ID, 'sponsored', __( 'Sponsored Project', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'sponsor_label', __( 'Sponsor Label', 'tenprojects-ai-matcher' ) );
		$this->render_checkbox( $post->ID, 'verified', __( 'Verified', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render the Construction panel.
	 *
	 * @param \WP_Post $post Current post.
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
		echo '<div class="tp-field-row">';
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
	 * Render the Location panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_location_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="location">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Address & Coordinates', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_textarea( $post->ID, 'address', __( 'Full Address', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'latitude', __( 'Latitude', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'longitude', __( 'Longitude', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Distance to Key Points (km)', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'railway_distance_km', __( 'Railway Station', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'metro_distance_km', __( 'Metro Station', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'highway_distance_km', __( 'Highway', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'airport_distance_km', __( 'Airport', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'school_distance_km', __( 'School', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'hospital_distance_km', __( 'Hospital', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'mall_distance_km', __( 'Mall / Shopping', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'employment_hub_km', __( 'Employment Hub', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render the Scoring Data panel.
	 *
	 * @param \WP_Post $post Current post.
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
		echo '<div class="tp-field-row">';
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

		echo '</div>';
	}

	/**
	 * Render the Editorial panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_editorial_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="editorial">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Editorial Content (JSON Lists)', 'tenprojects-ai-matcher' ) . '</div>';
		$this->render_json_field( $post->ID, 'highlights', __( 'Highlights', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'pros', __( 'Pros', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'cons', __( 'Cons', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'risks', __( 'Risks', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'best_for', __( 'Best For', 'tenprojects-ai-matcher' ) );
		$this->render_json_field( $post->ID, 'not_for', __( 'Not Ideal For', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render the Infrastructure panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_infrastructure_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="infrastructure">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Utilities & Amenities', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'water_source', __( 'Water Source', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'power_backup', __( 'Power Backup', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'parking_info', __( 'Parking Info', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Density & Cost', 'tenprojects-ai-matcher' ) . '</div>';
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
	 * Render the Verification panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_verification_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="verification">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Verification Details', 'tenprojects-ai-matcher' ) . '</div>';
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
