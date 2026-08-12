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
}
