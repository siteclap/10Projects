<?php
/**
 * City taxonomy registration.
 *
 * Hierarchical taxonomy for grouping projects and locations by city.
 *
 * @package TenProjects\Taxonomy
 * @since   1.0.0
 */

namespace TenProjects\Taxonomy;

defined( 'ABSPATH' ) || exit;

class City_Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	const TAXONOMY = 'tp_city';

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
	}

	/**
	 * Register the tp_city taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Cities', 'taxonomy general name', 'flavor-starter' ),
			'singular_name'              => _x( 'City', 'taxonomy singular name', 'flavor-starter' ),
			'search_items'               => __( 'Search Cities', 'flavor-starter' ),
			'popular_items'              => __( 'Popular Cities', 'flavor-starter' ),
			'all_items'                  => __( 'All Cities', 'flavor-starter' ),
			'parent_item'                => __( 'Parent City', 'flavor-starter' ),
			'parent_item_colon'          => __( 'Parent City:', 'flavor-starter' ),
			'edit_item'                  => __( 'Edit City', 'flavor-starter' ),
			'view_item'                  => __( 'View City', 'flavor-starter' ),
			'update_item'                => __( 'Update City', 'flavor-starter' ),
			'add_new_item'               => __( 'Add New City', 'flavor-starter' ),
			'new_item_name'              => __( 'New City Name', 'flavor-starter' ),
			'separate_items_with_commas' => __( 'Separate cities with commas', 'flavor-starter' ),
			'add_or_remove_items'        => __( 'Add or remove cities', 'flavor-starter' ),
			'choose_from_most_used'      => __( 'Choose from the most used cities', 'flavor-starter' ),
			'not_found'                  => __( 'No cities found.', 'flavor-starter' ),
			'no_terms'                   => __( 'No cities', 'flavor-starter' ),
			'menu_name'                  => __( 'Cities', 'flavor-starter' ),
			'items_list_navigation'      => __( 'Cities list navigation', 'flavor-starter' ),
			'items_list'                 => __( 'Cities list', 'flavor-starter' ),
			'back_to_items'              => __( '&larr; Back to Cities', 'flavor-starter' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'City grouping for projects and locations.', 'flavor-starter' ),
			'hierarchical'       => true,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_tagcloud'      => false,
			'show_in_quick_edit' => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
			'rest_base'          => 'tp-cities',
			'rewrite'            => array(
				'slug'         => 'city',
				'with_front'   => false,
				'hierarchical' => true,
			),
			'query_var'          => true,
		);

		register_taxonomy( self::TAXONOMY, self::POST_TYPES, $args );
	}
}
