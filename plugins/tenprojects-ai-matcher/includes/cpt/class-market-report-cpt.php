<?php
/**
 * Market Report Custom Post Type.
 *
 * Registers the tp_market_report CPT with meta fields
 * and a tabbed admin meta box for market report data entry.
 *
 * @package TenProjects\CPT
 * @since   1.0.0
 */

namespace TenProjects\CPT;

defined( 'ABSPATH' ) || exit;

class Market_Report_CPT {

	/**
	 * Post type key.
	 *
	 * @var string
	 */
	const POST_TYPE = 'tp_market_report';

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
	 * Register the Market Report post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Market Reports', 'Post type general name', 'tenprojects-ai-matcher' ),
			'singular_name'         => _x( 'Market Report', 'Post type singular name', 'tenprojects-ai-matcher' ),
			'menu_name'             => _x( 'Market Reports', 'Admin Menu text', 'tenprojects-ai-matcher' ),
			'name_admin_bar'        => _x( 'Market Report', 'Add New on Toolbar', 'tenprojects-ai-matcher' ),
			'add_new'               => __( 'Add New', 'tenprojects-ai-matcher' ),
			'add_new_item'          => __( 'Add New Market Report', 'tenprojects-ai-matcher' ),
			'new_item'              => __( 'New Market Report', 'tenprojects-ai-matcher' ),
			'edit_item'             => __( 'Edit Market Report', 'tenprojects-ai-matcher' ),
			'view_item'             => __( 'View Market Report', 'tenprojects-ai-matcher' ),
			'all_items'             => __( 'All Market Reports', 'tenprojects-ai-matcher' ),
			'search_items'          => __( 'Search Market Reports', 'tenprojects-ai-matcher' ),
			'parent_item_colon'     => __( 'Parent Market Reports:', 'tenprojects-ai-matcher' ),
			'not_found'             => __( 'No market reports found.', 'tenprojects-ai-matcher' ),
			'not_found_in_trash'    => __( 'No market reports found in Trash.', 'tenprojects-ai-matcher' ),
			'featured_image'        => __( 'Report Cover Image', 'tenprojects-ai-matcher' ),
			'set_featured_image'    => __( 'Set report cover image', 'tenprojects-ai-matcher' ),
			'remove_featured_image' => __( 'Remove report cover image', 'tenprojects-ai-matcher' ),
			'use_featured_image'    => __( 'Use as report cover image', 'tenprojects-ai-matcher' ),
			'archives'              => __( 'Market Report Archives', 'tenprojects-ai-matcher' ),
			'filter_items_list'     => __( 'Filter market reports list', 'tenprojects-ai-matcher' ),
			'items_list_navigation' => __( 'Market reports list navigation', 'tenprojects-ai-matcher' ),
			'items_list'            => __( 'Market reports list', 'tenprojects-ai-matcher' ),
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
				'slug'       => 'market-reports',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 11,
			'menu_icon'           => 'dashicons-chart-bar',
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
			'report_city'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'report_quarter' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'report_data'    => array( 'type' => 'string', 'sanitize_callback' => 'wp_kses_post' ),
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
	 * Add the tabbed meta box.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'tp_market_report_details',
			__( 'Market Report Details', 'tenprojects-ai-matcher' ),
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
		wp_nonce_field( 'tp_market_report_meta', 'tp_market_report_meta_nonce' );

		$tabs = array(
			'report' => __( 'Report Info', 'tenprojects-ai-matcher' ),
			'data'   => __( 'Report Data', 'tenprojects-ai-matcher' ),
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
		$this->render_report_panel( $post );
		$this->render_data_panel( $post );

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
		echo '<textarea id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" rows="5">'
			. esc_textarea( $value ) . '</textarea>';

		if ( $description ) {
			echo '<span class="description">' . esc_html( $description ) . '</span>';
		}

		echo '</div>';
	}

	/**
	 * Render Report Info panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_report_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel active" data-panel="report">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Report Classification', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'report_city', __( 'City', 'tenprojects-ai-matcher' ) );
		$this->render_select( $post->ID, 'report_quarter', __( 'Quarter', 'tenprojects-ai-matcher' ), array(
			'Q1-2024' => __( 'Q1 2024 (Jan-Mar)', 'tenprojects-ai-matcher' ),
			'Q2-2024' => __( 'Q2 2024 (Apr-Jun)', 'tenprojects-ai-matcher' ),
			'Q3-2024' => __( 'Q3 2024 (Jul-Sep)', 'tenprojects-ai-matcher' ),
			'Q4-2024' => __( 'Q4 2024 (Oct-Dec)', 'tenprojects-ai-matcher' ),
			'Q1-2025' => __( 'Q1 2025 (Jan-Mar)', 'tenprojects-ai-matcher' ),
			'Q2-2025' => __( 'Q2 2025 (Apr-Jun)', 'tenprojects-ai-matcher' ),
			'Q3-2025' => __( 'Q3 2025 (Jul-Sep)', 'tenprojects-ai-matcher' ),
			'Q4-2025' => __( 'Q4 2025 (Oct-Dec)', 'tenprojects-ai-matcher' ),
			'Q1-2026' => __( 'Q1 2026 (Jan-Mar)', 'tenprojects-ai-matcher' ),
			'Q2-2026' => __( 'Q2 2026 (Apr-Jun)', 'tenprojects-ai-matcher' ),
			'Q3-2026' => __( 'Q3 2026 (Jul-Sep)', 'tenprojects-ai-matcher' ),
			'Q4-2026' => __( 'Q4 2026 (Oct-Dec)', 'tenprojects-ai-matcher' ),
		) );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render Report Data panel.
	 *
	 * @param \WP_Post $post Current post.
	 */
	private function render_data_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="data">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Structured Report Data', 'tenprojects-ai-matcher' ) . '</div>';
		$this->render_textarea(
			$post->ID,
			'report_data',
			__( 'Report Data (JSON)', 'tenprojects-ai-matcher' ),
			__( 'JSON-encoded market data: pricing trends, supply stats, demand indicators, etc.', 'tenprojects-ai-matcher' )
		);
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
		if ( ! isset( $_POST['tp_market_report_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['tp_market_report_meta_nonce'], 'tp_market_report_meta' ) ) {
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
