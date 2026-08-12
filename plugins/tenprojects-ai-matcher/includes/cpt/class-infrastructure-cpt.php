<?php
/**
 * Infrastructure Custom Post Type.
 *
 * Registers the tp_infrastructure CPT with all meta fields
 * and a tabbed admin meta box for infrastructure project data entry.
 *
 * @package TenProjects\CPT
 * @since   1.0.0
 */

namespace TenProjects\CPT;

defined( 'ABSPATH' ) || exit;

class Infrastructure_CPT {

	/**
	 * Post type key.
	 *
	 * @var string
	 */
	const POST_TYPE = 'tp_infrastructure';

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
	 * Register the Infrastructure post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Infrastructure Updates', 'Post type general name', 'tenprojects-ai-matcher' ),
			'singular_name'         => _x( 'Infrastructure', 'Post type singular name', 'tenprojects-ai-matcher' ),
			'menu_name'             => _x( 'Infrastructure', 'Admin Menu text', 'tenprojects-ai-matcher' ),
			'name_admin_bar'        => _x( 'Infrastructure', 'Add New on Toolbar', 'tenprojects-ai-matcher' ),
			'add_new'               => __( 'Add New', 'tenprojects-ai-matcher' ),
			'add_new_item'          => __( 'Add New Infrastructure Update', 'tenprojects-ai-matcher' ),
			'new_item'              => __( 'New Infrastructure Update', 'tenprojects-ai-matcher' ),
			'edit_item'             => __( 'Edit Infrastructure Update', 'tenprojects-ai-matcher' ),
			'view_item'             => __( 'View Infrastructure Update', 'tenprojects-ai-matcher' ),
			'all_items'             => __( 'All Infrastructure Updates', 'tenprojects-ai-matcher' ),
			'search_items'          => __( 'Search Infrastructure Updates', 'tenprojects-ai-matcher' ),
			'parent_item_colon'     => __( 'Parent Infrastructure:', 'tenprojects-ai-matcher' ),
			'not_found'             => __( 'No infrastructure updates found.', 'tenprojects-ai-matcher' ),
			'not_found_in_trash'    => __( 'No infrastructure updates found in Trash.', 'tenprojects-ai-matcher' ),
			'featured_image'        => __( 'Infrastructure Image', 'tenprojects-ai-matcher' ),
			'set_featured_image'    => __( 'Set infrastructure image', 'tenprojects-ai-matcher' ),
			'remove_featured_image' => __( 'Remove infrastructure image', 'tenprojects-ai-matcher' ),
			'use_featured_image'    => __( 'Use as infrastructure image', 'tenprojects-ai-matcher' ),
			'archives'              => __( 'Infrastructure Archives', 'tenprojects-ai-matcher' ),
			'filter_items_list'     => __( 'Filter infrastructure list', 'tenprojects-ai-matcher' ),
			'items_list_navigation' => __( 'Infrastructure list navigation', 'tenprojects-ai-matcher' ),
			'items_list'            => __( 'Infrastructure list', 'tenprojects-ai-matcher' ),
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
				'slug'       => 'infrastructure',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 10,
			'menu_icon'           => 'dashicons-hammer',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
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
			'infra_type'                => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'infra_status'              => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'infra_expected_completion' => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'infra_impact_radius_km'    => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'infra_latitude'            => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'infra_longitude'           => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'infra_affected_locations'  => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
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
			'tp_infrastructure_details',
			__( 'Infrastructure Details', 'tenprojects-ai-matcher' ),
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
		wp_nonce_field( 'tp_infrastructure_meta', 'tp_infrastructure_meta_nonce' );

		$tabs = array(
			'project'  => __( 'Project Info', 'tenprojects-ai-matcher' ),
			'location' => __( 'Location & Impact', 'tenprojects-ai-matcher' ),
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
		$this->render_project_panel( $post );
		$this->render_location_panel( $post );

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
	 * Render Project Info panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_project_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel active" data-panel="project">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Infrastructure Project Details', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'infra_type', __( 'Infrastructure Type', 'tenprojects-ai-matcher' ), array(
			'metro'      => __( 'Metro', 'tenprojects-ai-matcher' ),
			'highway'    => __( 'Highway / Expressway', 'tenprojects-ai-matcher' ),
			'railway'    => __( 'Railway', 'tenprojects-ai-matcher' ),
			'airport'    => __( 'Airport', 'tenprojects-ai-matcher' ),
			'bridge'     => __( 'Bridge / Flyover', 'tenprojects-ai-matcher' ),
			'road'       => __( 'Road Widening', 'tenprojects-ai-matcher' ),
			'water'      => __( 'Water Supply', 'tenprojects-ai-matcher' ),
			'sewage'     => __( 'Sewage Treatment', 'tenprojects-ai-matcher' ),
			'commercial' => __( 'Commercial / IT Park', 'tenprojects-ai-matcher' ),
			'other'      => __( 'Other', 'tenprojects-ai-matcher' ),
		) );
		$this->render_select( $post->ID, 'infra_status', __( 'Status', 'tenprojects-ai-matcher' ), array(
			'proposed'       => __( 'Proposed', 'tenprojects-ai-matcher' ),
			'approved'       => __( 'Approved', 'tenprojects-ai-matcher' ),
			'under_progress' => __( 'Under Progress', 'tenprojects-ai-matcher' ),
			'completed'      => __( 'Completed', 'tenprojects-ai-matcher' ),
			'stalled'        => __( 'Stalled', 'tenprojects-ai-matcher' ),
		) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'infra_expected_completion', __( 'Expected Completion', 'tenprojects-ai-matcher' ), 'date' );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render Location & Impact panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_location_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="location">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Coordinates & Impact', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'infra_latitude', __( 'Latitude', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'infra_longitude', __( 'Longitude', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'infra_impact_radius_km', __( 'Impact Radius (km)', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Affected Areas', 'tenprojects-ai-matcher' ) . '</div>';
		$this->render_textarea( $post->ID, 'infra_affected_locations', __( 'Affected Locations', 'tenprojects-ai-matcher' ), __( 'Comma-separated list of location names or post IDs that will be impacted.', 'tenprojects-ai-matcher' ) );
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
		if ( ! isset( $_POST['tp_infrastructure_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['tp_infrastructure_meta_nonce'], 'tp_infrastructure_meta' ) ) {
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
