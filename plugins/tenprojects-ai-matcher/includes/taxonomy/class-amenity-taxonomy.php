<?php
/**
 * Amenity taxonomy registration.
 *
 * Flat taxonomy for project amenities and facilities.
 *
 * @package TenProjects\Taxonomy
 * @since   1.0.0
 */

namespace TenProjects\Taxonomy;

defined( 'ABSPATH' ) || exit;

class Amenity_Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	const TAXONOMY = 'tp_amenity';

	/**
	 * Post types this taxonomy is registered for.
	 *
	 * @var string[]
	 */
	const POST_TYPES = array( 'tp_project' );

	/**
	 * Default terms to seed on activation.
	 *
	 * @var string[]
	 */
	const DEFAULT_TERMS = array(
		'Swimming Pool',
		'Gym',
		'Clubhouse',
		'Garden',
		'Jogging Track',
		'Sports Court',
		'Kids Play Area',
		'Senior Area',
		'Pet Area',
		'EV Charging',
		'Library',
		'Theatre',
		'Yoga Room',
	);

	/**
	 * Hook into WordPress to register the taxonomy.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the tp_amenity taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Amenities', 'taxonomy general name', 'flavor-starter' ),
			'singular_name'              => _x( 'Amenity', 'taxonomy singular name', 'flavor-starter' ),
			'search_items'               => __( 'Search Amenities', 'flavor-starter' ),
			'popular_items'              => __( 'Popular Amenities', 'flavor-starter' ),
			'all_items'                  => __( 'All Amenities', 'flavor-starter' ),
			'edit_item'                  => __( 'Edit Amenity', 'flavor-starter' ),
			'view_item'                  => __( 'View Amenity', 'flavor-starter' ),
			'update_item'                => __( 'Update Amenity', 'flavor-starter' ),
			'add_new_item'               => __( 'Add New Amenity', 'flavor-starter' ),
			'new_item_name'              => __( 'New Amenity Name', 'flavor-starter' ),
			'separate_items_with_commas' => __( 'Separate amenities with commas', 'flavor-starter' ),
			'add_or_remove_items'        => __( 'Add or remove amenities', 'flavor-starter' ),
			'choose_from_most_used'      => __( 'Choose from the most used amenities', 'flavor-starter' ),
			'not_found'                  => __( 'No amenities found.', 'flavor-starter' ),
			'no_terms'                   => __( 'No amenities', 'flavor-starter' ),
			'menu_name'                  => __( 'Amenities', 'flavor-starter' ),
			'items_list_navigation'      => __( 'Amenities list navigation', 'flavor-starter' ),
			'items_list'                 => __( 'Amenities list', 'flavor-starter' ),
			'back_to_items'              => __( '&larr; Back to Amenities', 'flavor-starter' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Project amenities and facilities.', 'flavor-starter' ),
			'hierarchical'       => false,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_tagcloud'      => true,
			'show_in_quick_edit' => true,
			'show_admin_column'  => false,
			'show_in_rest'       => true,
			'rest_base'          => 'tp-amenities',
			'rewrite'            => array(
				'slug'       => 'amenity',
				'with_front' => false,
			),
			'query_var'          => true,
		);

		register_taxonomy( self::TAXONOMY, self::POST_TYPES, $args );
	}

	/**
	 * Insert default terms if they do not already exist.
	 *
	 * Called from the Activator class during plugin activation.
	 */
	public static function insert_default_terms() {
		foreach ( self::DEFAULT_TERMS as $term_name ) {
			if ( ! term_exists( $term_name, self::TAXONOMY ) ) {
				wp_insert_term( $term_name, self::TAXONOMY );
			}
		}
	}
}
