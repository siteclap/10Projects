<?php
/**
 * Micro Location taxonomy registration.
 *
 * Hierarchical taxonomy for granular micro-location areas within a location.
 * Term hierarchy: City > Location > Micro Location (managed via parent terms).
 *
 * @package TenProjects\Taxonomy
 * @since   1.0.0
 */

namespace TenProjects\Taxonomy;

defined( 'ABSPATH' ) || exit;

class Micro_Location_Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	const TAXONOMY = 'tp_micro_location';

	/**
	 * Post types this taxonomy is registered for.
	 *
	 * @var string[]
	 */
	const POST_TYPES = array( 'tp_project' );

	/**
	 * Hook into WordPress to register the taxonomy.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the tp_micro_location taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Micro Locations', 'taxonomy general name', 'flavor-starter' ),
			'singular_name'              => _x( 'Micro Location', 'taxonomy singular name', 'flavor-starter' ),
			'search_items'               => __( 'Search Micro Locations', 'flavor-starter' ),
			'popular_items'              => __( 'Popular Micro Locations', 'flavor-starter' ),
			'all_items'                  => __( 'All Micro Locations', 'flavor-starter' ),
			'parent_item'                => __( 'Parent Micro Location', 'flavor-starter' ),
			'parent_item_colon'          => __( 'Parent Micro Location:', 'flavor-starter' ),
			'edit_item'                  => __( 'Edit Micro Location', 'flavor-starter' ),
			'view_item'                  => __( 'View Micro Location', 'flavor-starter' ),
			'update_item'                => __( 'Update Micro Location', 'flavor-starter' ),
			'add_new_item'               => __( 'Add New Micro Location', 'flavor-starter' ),
			'new_item_name'              => __( 'New Micro Location Name', 'flavor-starter' ),
			'separate_items_with_commas' => __( 'Separate micro locations with commas', 'flavor-starter' ),
			'add_or_remove_items'        => __( 'Add or remove micro locations', 'flavor-starter' ),
			'choose_from_most_used'      => __( 'Choose from the most used micro locations', 'flavor-starter' ),
			'not_found'                  => __( 'No micro locations found.', 'flavor-starter' ),
			'no_terms'                   => __( 'No micro locations', 'flavor-starter' ),
			'menu_name'                  => __( 'Micro Locations', 'flavor-starter' ),
			'items_list_navigation'      => __( 'Micro Locations list navigation', 'flavor-starter' ),
			'items_list'                 => __( 'Micro Locations list', 'flavor-starter' ),
			'back_to_items'              => __( '&larr; Back to Micro Locations', 'flavor-starter' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Granular micro-location areas within a location.', 'flavor-starter' ),
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
			'rest_base'          => 'tp-micro-locations',
			'rewrite'            => array(
				'slug'         => 'micro-location',
				'with_front'   => false,
				'hierarchical' => true,
			),
			'query_var'          => true,
		);

		register_taxonomy( self::TAXONOMY, self::POST_TYPES, $args );
	}
}
