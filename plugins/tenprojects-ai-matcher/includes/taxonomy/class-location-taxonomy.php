<?php
/**
 * Location taxonomy registration.
 *
 * Hierarchical taxonomy for location areas within a city.
 * Parent terms should be tp_city terms conceptually, but WordPress
 * taxonomy hierarchy is self-contained. City-location nesting is
 * achieved via hierarchical term parents within this taxonomy, with
 * city association managed through shared term assignment.
 *
 * @package TenProjects\Taxonomy
 * @since   1.0.0
 */

namespace TenProjects\Taxonomy;

defined( 'ABSPATH' ) || exit;

class Location_Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	const TAXONOMY = 'tp_location_area';

	/**
	 * Post types this taxonomy is registered for.
	 *
	 * @var string[]
	 */
	const POST_TYPES = array( 'tp_project', 'tp_location' );

	/**
	 * Hook into WordPress to register the taxonomy.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( self::TAXONOMY . '_add_form_fields', array( $this, 'add_form_fields' ) );
		add_action( self::TAXONOMY . '_edit_form_fields', array( $this, 'edit_form_fields' ), 10, 2 );
		add_action( 'created_' . self::TAXONOMY, array( $this, 'save_term_meta' ) );
		add_action( 'edited_' . self::TAXONOMY, array( $this, 'save_term_meta' ) );
		add_filter( 'manage_edit-' . self::TAXONOMY . '_columns', array( $this, 'add_columns' ) );
		add_filter( 'manage_' . self::TAXONOMY . '_custom_column', array( $this, 'render_column' ), 10, 3 );
	}

	/**
	 * Register the tp_location_area taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Locations', 'taxonomy general name', 'flavor-starter' ),
			'singular_name'              => _x( 'Location', 'taxonomy singular name', 'flavor-starter' ),
			'search_items'               => __( 'Search Locations', 'flavor-starter' ),
			'popular_items'              => __( 'Popular Locations', 'flavor-starter' ),
			'all_items'                  => __( 'All Locations', 'flavor-starter' ),
			'parent_item'                => __( 'Parent Location', 'flavor-starter' ),
			'parent_item_colon'          => __( 'Parent Location:', 'flavor-starter' ),
			'edit_item'                  => __( 'Edit Location', 'flavor-starter' ),
			'view_item'                  => __( 'View Location', 'flavor-starter' ),
			'update_item'                => __( 'Update Location', 'flavor-starter' ),
			'add_new_item'               => __( 'Add New Location', 'flavor-starter' ),
			'new_item_name'              => __( 'New Location Name', 'flavor-starter' ),
			'separate_items_with_commas' => __( 'Separate locations with commas', 'flavor-starter' ),
			'add_or_remove_items'        => __( 'Add or remove locations', 'flavor-starter' ),
			'choose_from_most_used'      => __( 'Choose from the most used locations', 'flavor-starter' ),
			'not_found'                  => __( 'No locations found.', 'flavor-starter' ),
			'no_terms'                   => __( 'No locations', 'flavor-starter' ),
			'menu_name'                  => __( 'Locations', 'flavor-starter' ),
			'items_list_navigation'      => __( 'Locations list navigation', 'flavor-starter' ),
			'items_list'                 => __( 'Locations list', 'flavor-starter' ),
			'back_to_items'              => __( '&larr; Back to Locations', 'flavor-starter' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Location areas within a city for projects.', 'flavor-starter' ),
			'hierarchical'       => true,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_nav_menus'  => true,
			'show_tagcloud'      => false,
			'show_in_quick_edit' => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
			'rest_base'          => 'tp-locations',
			'rewrite'            => array(
				'slug'         => 'navi-mumbai',
				'with_front'   => false,
				'hierarchical' => true,
			),
			'query_var'          => true,
		);

		register_taxonomy( self::TAXONOMY, self::POST_TYPES, $args );
	}

	/**
	 * "Add New" form — checkbox field.
	 */
	public function add_form_fields() {
		?>
		<div class="form-field">
			<label><input type="checkbox" name="tp_show_on_homepage" value="1"> Show near search bar on homepage</label>
			<p>When checked, this location appears as a popular chip on the homepage hero.</p>
		</div>
		<?php
	}

	/**
	 * "Edit" form — checkbox field.
	 */
	public function edit_form_fields( $term ) {
		$checked = get_term_meta( $term->term_id, 'tp_show_on_homepage', true );
		?>
		<tr class="form-field">
			<th scope="row"><label for="tp_show_on_homepage">Homepage Search Bar</label></th>
			<td>
				<label><input type="checkbox" name="tp_show_on_homepage" id="tp_show_on_homepage" value="1" <?php checked( $checked, '1' ); ?>> Show near search bar on homepage</label>
				<p class="description">When checked, this location appears as a popular chip on the homepage hero.</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save term meta on create/edit.
	 */
	public function save_term_meta( $term_id ) {
		$value = isset( $_POST['tp_show_on_homepage'] ) ? '1' : '';
		update_term_meta( $term_id, 'tp_show_on_homepage', $value );
	}

	/**
	 * Add "Homepage" column to term list table.
	 */
	public function add_columns( $columns ) {
		$columns['tp_homepage'] = 'Homepage';
		return $columns;
	}

	/**
	 * Render the "Homepage" column.
	 */
	public function render_column( $content, $column_name, $term_id ) {
		if ( 'tp_homepage' === $column_name ) {
			return get_term_meta( $term_id, 'tp_show_on_homepage', true ) ? '&#10003;' : '—';
		}
		return $content;
	}
}
