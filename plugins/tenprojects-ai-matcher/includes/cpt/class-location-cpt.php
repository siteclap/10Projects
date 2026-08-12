<?php
/**
 * Location Custom Post Type.
 *
 * Registers the tp_location CPT with all meta fields
 * and a tabbed admin meta box for location data entry.
 *
 * @package TenProjects\CPT
 * @since   1.0.0
 */

namespace TenProjects\CPT;

defined( 'ABSPATH' ) || exit;

class Location_CPT {

	/**
	 * Post type key.
	 *
	 * @var string
	 */
	const POST_TYPE = 'tp_location';

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
	}

	/**
	 * Register the Location post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Locations', 'Post type general name', 'tenprojects-ai-matcher' ),
			'singular_name'         => _x( 'Location', 'Post type singular name', 'tenprojects-ai-matcher' ),
			'menu_name'             => _x( 'Locations', 'Admin Menu text', 'tenprojects-ai-matcher' ),
			'name_admin_bar'        => _x( 'Location', 'Add New on Toolbar', 'tenprojects-ai-matcher' ),
			'add_new'               => __( 'Add New', 'tenprojects-ai-matcher' ),
			'add_new_item'          => __( 'Add New Location', 'tenprojects-ai-matcher' ),
			'new_item'              => __( 'New Location', 'tenprojects-ai-matcher' ),
			'edit_item'             => __( 'Edit Location', 'tenprojects-ai-matcher' ),
			'view_item'             => __( 'View Location', 'tenprojects-ai-matcher' ),
			'all_items'             => __( 'All Locations', 'tenprojects-ai-matcher' ),
			'search_items'          => __( 'Search Locations', 'tenprojects-ai-matcher' ),
			'parent_item_colon'     => __( 'Parent Location:', 'tenprojects-ai-matcher' ),
			'not_found'             => __( 'No locations found.', 'tenprojects-ai-matcher' ),
			'not_found_in_trash'    => __( 'No locations found in Trash.', 'tenprojects-ai-matcher' ),
			'featured_image'        => __( 'Location Image', 'tenprojects-ai-matcher' ),
			'set_featured_image'    => __( 'Set location image', 'tenprojects-ai-matcher' ),
			'remove_featured_image' => __( 'Remove location image', 'tenprojects-ai-matcher' ),
			'use_featured_image'    => __( 'Use as location image', 'tenprojects-ai-matcher' ),
			'archives'              => __( 'Location Archives', 'tenprojects-ai-matcher' ),
			'filter_items_list'     => __( 'Filter locations list', 'tenprojects-ai-matcher' ),
			'items_list_navigation' => __( 'Locations list navigation', 'tenprojects-ai-matcher' ),
			'items_list'            => __( 'Locations list', 'tenprojects-ai-matcher' ),
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
				'slug'       => 'locations',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 7,
			'menu_icon'           => 'dashicons-location',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'taxonomies'          => array( 'tp_city' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Get all meta field definitions.
	 *
	 * @return array[] Associative array of meta_key => schema args.
	 */
	private function get_meta_fields(): array {
		return array(
			// Basic.
			'loc_city'              => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'loc_type'              => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'loc_parent_id'         => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'loc_latitude'          => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'loc_longitude'         => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),

			// Market Data.
			'loc_avg_price_sqft'    => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'loc_price_trend_3yr'   => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'loc_total_projects'    => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'loc_rental_demand'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'loc_supply_pipeline'   => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'loc_resale_liquidity'  => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// Scores.
			'loc_liveability_score' => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'loc_investment_score'  => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'loc_risk_score'        => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),

			// Issues.
			'loc_water_issues'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'loc_traffic_issues'    => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'loc_environmental'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'loc_infrastructure'    => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),

			// Editorial.
			'loc_overview'            => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'loc_who_should_consider' => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'loc_who_should_avoid'    => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),

			// Nearby.
			'loc_commute_details'   => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'loc_schools'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'loc_hospitals'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'loc_offices'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
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
			'tp_location_details',
			__( 'Location Details', 'tenprojects-ai-matcher' ),
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
		wp_nonce_field( 'tp_location_meta', 'tp_location_meta_nonce' );

		$tabs = array(
			'basic'     => __( 'Basic Info', 'tenprojects-ai-matcher' ),
			'market'    => __( 'Market Data', 'tenprojects-ai-matcher' ),
			'scores'    => __( 'Scores', 'tenprojects-ai-matcher' ),
			'issues'    => __( 'Issues & Risks', 'tenprojects-ai-matcher' ),
			'editorial' => __( 'Editorial', 'tenprojects-ai-matcher' ),
			'nearby'    => __( 'Nearby Facilities', 'tenprojects-ai-matcher' ),
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
		$this->render_market_panel( $post );
		$this->render_scores_panel( $post );
		$this->render_issues_panel( $post );
		$this->render_editorial_panel( $post );
		$this->render_nearby_panel( $post );

		echo '</div>';
	}

	/**
	 * Render a text/number/date input field.
	 *
	 * @param int    $post_id     Post ID.
	 * @param string $key         Meta key without prefix.
	 * @param string $label       Field label.
	 * @param string $type        Input type.
	 * @param string $description Optional description.
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
	 * Render Basic Info panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_basic_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel active" data-panel="basic">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Location Details', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'loc_city', __( 'City', 'tenprojects-ai-matcher' ) );
		$this->render_select( $post->ID, 'loc_type', __( 'Location Type', 'tenprojects-ai-matcher' ), array(
			'city'           => __( 'City', 'tenprojects-ai-matcher' ),
			'area'           => __( 'Area / Zone', 'tenprojects-ai-matcher' ),
			'micro_location' => __( 'Micro Location', 'tenprojects-ai-matcher' ),
		) );
		$this->render_field( $post->ID, 'loc_parent_id', __( 'Parent Location ID', 'tenprojects-ai-matcher' ), 'number', __( 'Post ID of the parent location.', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Coordinates', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'loc_latitude', __( 'Latitude', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'loc_longitude', __( 'Longitude', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render Market Data panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_market_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="market">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Pricing & Supply', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'loc_avg_price_sqft', __( 'Avg Price (₹/sqft)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'loc_price_trend_3yr', __( '3-Year Price Trend (%)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'loc_total_projects', __( 'Total Projects', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'loc_rental_demand', __( 'Rental Demand', 'tenprojects-ai-matcher' ), array(
			'low'    => __( 'Low', 'tenprojects-ai-matcher' ),
			'medium' => __( 'Medium', 'tenprojects-ai-matcher' ),
			'high'   => __( 'High', 'tenprojects-ai-matcher' ),
		) );
		$this->render_field( $post->ID, 'loc_supply_pipeline', __( 'Supply Pipeline (Units)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_select( $post->ID, 'loc_resale_liquidity', __( 'Resale Liquidity', 'tenprojects-ai-matcher' ), array(
			'low'    => __( 'Low', 'tenprojects-ai-matcher' ),
			'medium' => __( 'Medium', 'tenprojects-ai-matcher' ),
			'high'   => __( 'High', 'tenprojects-ai-matcher' ),
		) );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render Scores panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_scores_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="scores">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Location Scores (0-100)', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'loc_liveability_score', __( 'Liveability Score', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'loc_investment_score', __( 'Investment Score', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'loc_risk_score', __( 'Risk Score', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render Issues & Risks panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_issues_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="issues">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Known Issues', 'tenprojects-ai-matcher' ) . '</div>';
		$this->render_textarea( $post->ID, 'loc_water_issues', __( 'Water Issues', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'loc_traffic_issues', __( 'Traffic Issues', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'loc_environmental', __( 'Environmental Concerns', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'loc_infrastructure', __( 'Infrastructure Notes', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render Editorial panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_editorial_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="editorial">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Editorial Content', 'tenprojects-ai-matcher' ) . '</div>';
		$this->render_textarea( $post->ID, 'loc_overview', __( 'Location Overview', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'loc_who_should_consider', __( 'Who Should Consider This Location', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'loc_who_should_avoid', __( 'Who Should Avoid This Location', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render Nearby Facilities panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_nearby_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="nearby">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Nearby Facilities & Commute', 'tenprojects-ai-matcher' ) . '</div>';
		$this->render_textarea( $post->ID, 'loc_commute_details', __( 'Commute Details', 'tenprojects-ai-matcher' ), __( 'Key commute routes, travel times, and transport options.', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'loc_schools', __( 'Schools', 'tenprojects-ai-matcher' ), __( 'Nearby schools and educational institutions.', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'loc_hospitals', __( 'Hospitals', 'tenprojects-ai-matcher' ), __( 'Nearby hospitals and healthcare facilities.', 'tenprojects-ai-matcher' ) );
		$this->render_textarea( $post->ID, 'loc_offices', __( 'Office / IT Parks', 'tenprojects-ai-matcher' ), __( 'Nearby office spaces and employment zones.', 'tenprojects-ai-matcher' ) );
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
		if ( ! isset( $_POST['tp_location_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['tp_location_meta_nonce'], 'tp_location_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = $this->get_meta_fields();

		foreach ( $fields as $key => $schema ) {
			$meta_key = self::META_PREFIX . $key;

			if ( ! isset( $_POST[ $meta_key ] ) ) {
				continue;
			}

			$value = $_POST[ $meta_key ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

			if ( is_callable( $schema['sanitize_callback'] ) ) {
				$value = call_user_func( $schema['sanitize_callback'], $value );
			}

			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}
