<?php
/**
 * Property Type taxonomy registration.
 *
 * Flat taxonomy for classifying project property types.
 *
 * @package TenProjects\Taxonomy
 * @since   1.0.0
 */

namespace TenProjects\Taxonomy;

defined( 'ABSPATH' ) || exit;

class Property_Type_Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	const TAXONOMY = 'tp_property_type';

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
		'Residential Apartment',
		'Villa',
		'Row House',
		'Commercial Office',
		'Commercial Shop',
		'Plot',
	);

	/**
	 * Hook into WordPress to register the taxonomy.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the tp_property_type taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Property Types', 'taxonomy general name', 'flavor-starter' ),
			'singular_name'              => _x( 'Property Type', 'taxonomy singular name', 'flavor-starter' ),
			'search_items'               => __( 'Search Property Types', 'flavor-starter' ),
			'popular_items'              => __( 'Popular Property Types', 'flavor-starter' ),
			'all_items'                  => __( 'All Property Types', 'flavor-starter' ),
			'edit_item'                  => __( 'Edit Property Type', 'flavor-starter' ),
			'view_item'                  => __( 'View Property Type', 'flavor-starter' ),
			'update_item'                => __( 'Update Property Type', 'flavor-starter' ),
			'add_new_item'               => __( 'Add New Property Type', 'flavor-starter' ),
			'new_item_name'              => __( 'New Property Type Name', 'flavor-starter' ),
			'separate_items_with_commas' => __( 'Separate property types with commas', 'flavor-starter' ),
			'add_or_remove_items'        => __( 'Add or remove property types', 'flavor-starter' ),
			'choose_from_most_used'      => __( 'Choose from the most used property types', 'flavor-starter' ),
			'not_found'                  => __( 'No property types found.', 'flavor-starter' ),
			'no_terms'                   => __( 'No property types', 'flavor-starter' ),
			'menu_name'                  => __( 'Property Types', 'flavor-starter' ),
			'items_list_navigation'      => __( 'Property Types list navigation', 'flavor-starter' ),
			'items_list'                 => __( 'Property Types list', 'flavor-starter' ),
			'back_to_items'              => __( '&larr; Back to Property Types', 'flavor-starter' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Property type classification for projects.', 'flavor-starter' ),
			'hierarchical'       => false,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_tagcloud'      => false,
			'show_in_quick_edit' => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
			'rest_base'          => 'tp-property-types',
			'rewrite'            => array(
				'slug'       => 'type',
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
