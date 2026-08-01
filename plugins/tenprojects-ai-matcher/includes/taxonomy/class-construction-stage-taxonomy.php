<?php
/**
 * Construction Stage taxonomy registration.
 *
 * Flat taxonomy for project construction status.
 *
 * @package TenProjects\Taxonomy
 * @since   1.0.0
 */

namespace TenProjects\Taxonomy;

defined( 'ABSPATH' ) || exit;

class Construction_Stage_Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	const TAXONOMY = 'tp_construction_stage';

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
		'New Launch',
		'Under Construction',
		'Nearing Completion',
		'Ready Possession',
	);

	/**
	 * Hook into WordPress to register the taxonomy.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the tp_construction_stage taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Construction Stages', 'taxonomy general name', 'flavor-starter' ),
			'singular_name'              => _x( 'Construction Stage', 'taxonomy singular name', 'flavor-starter' ),
			'search_items'               => __( 'Search Construction Stages', 'flavor-starter' ),
			'popular_items'              => __( 'Popular Construction Stages', 'flavor-starter' ),
			'all_items'                  => __( 'All Construction Stages', 'flavor-starter' ),
			'edit_item'                  => __( 'Edit Construction Stage', 'flavor-starter' ),
			'view_item'                  => __( 'View Construction Stage', 'flavor-starter' ),
			'update_item'                => __( 'Update Construction Stage', 'flavor-starter' ),
			'add_new_item'               => __( 'Add New Construction Stage', 'flavor-starter' ),
			'new_item_name'              => __( 'New Construction Stage Name', 'flavor-starter' ),
			'separate_items_with_commas' => __( 'Separate construction stages with commas', 'flavor-starter' ),
			'add_or_remove_items'        => __( 'Add or remove construction stages', 'flavor-starter' ),
			'choose_from_most_used'      => __( 'Choose from the most used construction stages', 'flavor-starter' ),
			'not_found'                  => __( 'No construction stages found.', 'flavor-starter' ),
			'no_terms'                   => __( 'No construction stages', 'flavor-starter' ),
			'menu_name'                  => __( 'Construction Stages', 'flavor-starter' ),
			'items_list_navigation'      => __( 'Construction Stages list navigation', 'flavor-starter' ),
			'items_list'                 => __( 'Construction Stages list', 'flavor-starter' ),
			'back_to_items'              => __( '&larr; Back to Construction Stages', 'flavor-starter' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Construction progress status for projects.', 'flavor-starter' ),
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
			'rest_base'          => 'tp-construction-stages',
			'rewrite'            => array(
				'slug'       => 'stage',
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
