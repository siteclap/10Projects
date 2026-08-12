<?php
/**
 * Developer Custom Post Type.
 *
 * Registers the tp_developer CPT with all meta fields
 * and a tabbed admin meta box for developer data entry.
 *
 * @package TenProjects\CPT
 * @since   1.0.0
 */

namespace TenProjects\CPT;

defined( 'ABSPATH' ) || exit;

class Developer_CPT {

	/**
	 * Post type key.
	 *
	 * @var string
	 */
	const POST_TYPE = 'tp_developer';

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
	 * Register the Developer post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Developers', 'Post type general name', 'tenprojects-ai-matcher' ),
			'singular_name'         => _x( 'Developer', 'Post type singular name', 'tenprojects-ai-matcher' ),
			'menu_name'             => _x( 'Developers', 'Admin Menu text', 'tenprojects-ai-matcher' ),
			'name_admin_bar'        => _x( 'Developer', 'Add New on Toolbar', 'tenprojects-ai-matcher' ),
			'add_new'               => __( 'Add New', 'tenprojects-ai-matcher' ),
			'add_new_item'          => __( 'Add New Developer', 'tenprojects-ai-matcher' ),
			'new_item'              => __( 'New Developer', 'tenprojects-ai-matcher' ),
			'edit_item'             => __( 'Edit Developer', 'tenprojects-ai-matcher' ),
			'view_item'             => __( 'View Developer', 'tenprojects-ai-matcher' ),
			'all_items'             => __( 'All Developers', 'tenprojects-ai-matcher' ),
			'search_items'          => __( 'Search Developers', 'tenprojects-ai-matcher' ),
			'parent_item_colon'     => __( 'Parent Developers:', 'tenprojects-ai-matcher' ),
			'not_found'             => __( 'No developers found.', 'tenprojects-ai-matcher' ),
			'not_found_in_trash'    => __( 'No developers found in Trash.', 'tenprojects-ai-matcher' ),
			'featured_image'        => __( 'Developer Logo', 'tenprojects-ai-matcher' ),
			'set_featured_image'    => __( 'Set developer logo', 'tenprojects-ai-matcher' ),
			'remove_featured_image' => __( 'Remove developer logo', 'tenprojects-ai-matcher' ),
			'use_featured_image'    => __( 'Use as developer logo', 'tenprojects-ai-matcher' ),
			'archives'              => __( 'Developer Archives', 'tenprojects-ai-matcher' ),
			'filter_items_list'     => __( 'Filter developers list', 'tenprojects-ai-matcher' ),
			'items_list_navigation' => __( 'Developers list navigation', 'tenprojects-ai-matcher' ),
			'items_list'            => __( 'Developers list', 'tenprojects-ai-matcher' ),
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
				'slug'       => 'developers',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 6,
			'menu_icon'           => 'dashicons-businessman',
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
			'dev_established_year'   => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'dev_headquarters'       => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'dev_website'            => array( 'type' => 'string',  'sanitize_callback' => 'esc_url_raw' ),
			'dev_total_projects'     => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'dev_completed_projects' => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'dev_ongoing_projects'   => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'dev_delayed_projects'   => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'dev_ontime_rate'        => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'dev_avg_delay_months'   => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'dev_litigation_flags'   => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'dev_financial_risk'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'dev_reputation_score'   => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'dev_tier'               => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'dev_delivery_history'   => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_textarea_field' ),
			'dev_customer_rating'    => array( 'type' => 'number',  'sanitize_callback' => array( $this, 'sanitize_float' ) ),
			'dev_overview'           => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
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
			'tp_developer_details',
			__( 'Developer Details', 'tenprojects-ai-matcher' ),
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
		wp_nonce_field( 'tp_developer_meta', 'tp_developer_meta_nonce' );

		$tabs = array(
			'company'     => __( 'Company Info', 'tenprojects-ai-matcher' ),
			'track'       => __( 'Track Record', 'tenprojects-ai-matcher' ),
			'risk'        => __( 'Risk & Reputation', 'tenprojects-ai-matcher' ),
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
		$this->render_company_panel( $post );
		$this->render_track_panel( $post );
		$this->render_risk_panel( $post );

		echo '</div>';
	}

	/**
	 * Render a text/number/date/url input field.
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
	 * Render Company Info panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_company_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel active" data-panel="company">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Company Details', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'dev_established_year', __( 'Established Year', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'dev_headquarters', __( 'Headquarters', 'tenprojects-ai-matcher' ) );
		$this->render_field( $post->ID, 'dev_website', __( 'Website', 'tenprojects-ai-matcher' ), 'url' );
		echo '</div>';
		$this->render_textarea( $post->ID, 'dev_overview', __( 'Developer Overview', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render Track Record panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_track_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="track">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Project Statistics', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'dev_total_projects', __( 'Total Projects', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'dev_completed_projects', __( 'Completed Projects', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'dev_ongoing_projects', __( 'Ongoing Projects', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'dev_delayed_projects', __( 'Delayed Projects', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Delivery Performance', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'dev_ontime_rate', __( 'On-Time Delivery Rate (%)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'dev_avg_delay_months', __( 'Avg Delay (Months)', 'tenprojects-ai-matcher' ), 'number' );
		echo '</div>';
		$this->render_textarea( $post->ID, 'dev_delivery_history', __( 'Delivery History', 'tenprojects-ai-matcher' ), __( 'Notable delivery milestones and track record details.', 'tenprojects-ai-matcher' ) );
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render Risk & Reputation panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_risk_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="risk">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Risk Indicators', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_textarea( $post->ID, 'dev_litigation_flags', __( 'Litigation Flags', 'tenprojects-ai-matcher' ), __( 'Any legal cases or compliance issues.', 'tenprojects-ai-matcher' ) );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_select( $post->ID, 'dev_financial_risk', __( 'Financial Risk', 'tenprojects-ai-matcher' ), array(
			'low'    => __( 'Low', 'tenprojects-ai-matcher' ),
			'medium' => __( 'Medium', 'tenprojects-ai-matcher' ),
			'high'   => __( 'High', 'tenprojects-ai-matcher' ),
		) );
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Reputation', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'dev_reputation_score', __( 'Reputation Score (0-100)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_field( $post->ID, 'dev_customer_rating', __( 'Customer Rating (0-5)', 'tenprojects-ai-matcher' ), 'number' );
		$this->render_select( $post->ID, 'dev_tier', __( 'Developer Tier', 'tenprojects-ai-matcher' ), array(
			'tier_1' => __( 'Tier 1 — National Brand', 'tenprojects-ai-matcher' ),
			'tier_2' => __( 'Tier 2 — Regional Established', 'tenprojects-ai-matcher' ),
			'tier_3' => __( 'Tier 3 — Local Builder', 'tenprojects-ai-matcher' ),
			'tier_4' => __( 'Tier 4 — New / Unrated', 'tenprojects-ai-matcher' ),
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
		if ( ! isset( $_POST['tp_developer_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['tp_developer_meta_nonce'], 'tp_developer_meta' ) ) {
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
