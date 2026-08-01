<?php
/**
 * Review Custom Post Type.
 *
 * Registers the tp_review CPT with meta fields
 * and a tabbed admin meta box for review data entry.
 *
 * @package TenProjects\CPT
 * @since   1.0.0
 */

namespace TenProjects\CPT;

defined( 'ABSPATH' ) || exit;

class Review_CPT {

	/**
	 * Post type key.
	 *
	 * @var string
	 */
	const POST_TYPE = 'tp_review';

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
	 * Register the Review post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Reviews', 'Post type general name', 'tenprojects-ai-matcher' ),
			'singular_name'         => _x( 'Review', 'Post type singular name', 'tenprojects-ai-matcher' ),
			'menu_name'             => _x( 'Reviews', 'Admin Menu text', 'tenprojects-ai-matcher' ),
			'name_admin_bar'        => _x( 'Review', 'Add New on Toolbar', 'tenprojects-ai-matcher' ),
			'add_new'               => __( 'Add New', 'tenprojects-ai-matcher' ),
			'add_new_item'          => __( 'Add New Review', 'tenprojects-ai-matcher' ),
			'new_item'              => __( 'New Review', 'tenprojects-ai-matcher' ),
			'edit_item'             => __( 'Edit Review', 'tenprojects-ai-matcher' ),
			'view_item'             => __( 'View Review', 'tenprojects-ai-matcher' ),
			'all_items'             => __( 'All Reviews', 'tenprojects-ai-matcher' ),
			'search_items'          => __( 'Search Reviews', 'tenprojects-ai-matcher' ),
			'parent_item_colon'     => __( 'Parent Reviews:', 'tenprojects-ai-matcher' ),
			'not_found'             => __( 'No reviews found.', 'tenprojects-ai-matcher' ),
			'not_found_in_trash'    => __( 'No reviews found in Trash.', 'tenprojects-ai-matcher' ),
			'featured_image'        => __( 'Reviewer Photo', 'tenprojects-ai-matcher' ),
			'set_featured_image'    => __( 'Set reviewer photo', 'tenprojects-ai-matcher' ),
			'remove_featured_image' => __( 'Remove reviewer photo', 'tenprojects-ai-matcher' ),
			'use_featured_image'    => __( 'Use as reviewer photo', 'tenprojects-ai-matcher' ),
			'archives'              => __( 'Review Archives', 'tenprojects-ai-matcher' ),
			'filter_items_list'     => __( 'Filter reviews list', 'tenprojects-ai-matcher' ),
			'items_list_navigation' => __( 'Reviews list navigation', 'tenprojects-ai-matcher' ),
			'items_list'            => __( 'Reviews list', 'tenprojects-ai-matcher' ),
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
				'slug'       => 'reviews',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 9,
			'menu_icon'           => 'dashicons-star-filled',
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
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
			'review_project_id' => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'review_rating'     => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'review_buyer_type' => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'review_verified'   => array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean' ),
			'review_source'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
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
			'tp_review_details',
			__( 'Review Details', 'tenprojects-ai-matcher' ),
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
		wp_nonce_field( 'tp_review_meta', 'tp_review_meta_nonce' );

		$tabs = array(
			'review'       => __( 'Review Info', 'tenprojects-ai-matcher' ),
			'verification' => __( 'Verification', 'tenprojects-ai-matcher' ),
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
		$this->render_review_panel( $post );
		$this->render_verification_panel( $post );

		echo '</div>';
	}

	/**
	 * Render a text/number input field.
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
	 * Render Review Info panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_review_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel active" data-panel="review">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Review Information', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'review_project_id', __( 'Project ID', 'tenprojects-ai-matcher' ), 'number', __( 'Post ID of the reviewed project.', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'review_rating', __( 'Rating (0-5)', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'review_buyer_type', __( 'Buyer Type', 'tenprojects-ai-matcher' ), array(
			'first_time'    => __( 'First-Time Buyer', 'tenprojects-ai-matcher' ),
			'upgrader'      => __( 'Upgrader', 'tenprojects-ai-matcher' ),
			'investor'      => __( 'Investor', 'tenprojects-ai-matcher' ),
			'nri'           => __( 'NRI', 'tenprojects-ai-matcher' ),
			'retired'       => __( 'Retired', 'tenprojects-ai-matcher' ),
			'second_home'   => __( 'Second Home', 'tenprojects-ai-matcher' ),
		) );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render Verification panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_verification_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="verification">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Verification & Source', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_checkbox( $post->ID, 'review_verified', __( 'Verified Review', 'tenprojects-ai-matcher' ) );
		$this->render_select( $post->ID, 'review_source', __( 'Source', 'tenprojects-ai-matcher' ), array(
			'website'  => __( 'Website', 'tenprojects-ai-matcher' ),
			'google'   => __( 'Google Reviews', 'tenprojects-ai-matcher' ),
			'facebook' => __( 'Facebook', 'tenprojects-ai-matcher' ),
			'manual'   => __( 'Manual Entry', 'tenprojects-ai-matcher' ),
			'partner'  => __( 'Channel Partner', 'tenprojects-ai-matcher' ),
		) );
		echo '</div>';
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
		if ( ! isset( $_POST['tp_review_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['tp_review_meta_nonce'], 'tp_review_meta' ) ) {
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

			if ( 'boolean' === $schema['type'] ) {
				$value = isset( $_POST[ $meta_key ] ) ? '1' : '0';
				update_post_meta( $post_id, $meta_key, $value );
				continue;
			}

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
