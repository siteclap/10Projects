<?php
/**
 * Possession Year taxonomy registration.
 *
 * Flat taxonomy for expected possession/handover year.
 *
 * @package TenProjects\Taxonomy
 * @since   1.0.0
 */

namespace TenProjects\Taxonomy;

defined( 'ABSPATH' ) || exit;

class Possession_Year_Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	const TAXONOMY = 'tp_possession_year';

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
		'2026',
		'2027',
		'2028',
		'2029',
		'2030',
		'2031+',
	);

	/**
	 * Hook into WordPress to register the taxonomy.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the tp_possession_year taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Possession Years', 'taxonomy general name', 'flavor-starter' ),
			'singular_name'              => _x( 'Possession Year', 'taxonomy singular name', 'flavor-starter' ),
			'search_items'               => __( 'Search Possession Years', 'flavor-starter' ),
			'popular_items'              => __( 'Popular Possession Years', 'flavor-starter' ),
			'all_items'                  => __( 'All Possession Years', 'flavor-starter' ),
			'edit_item'                  => __( 'Edit Possession Year', 'flavor-starter' ),
			'view_item'                  => __( 'View Possession Year', 'flavor-starter' ),
			'update_item'                => __( 'Update Possession Year', 'flavor-starter' ),
			'add_new_item'               => __( 'Add New Possession Year', 'flavor-starter' ),
			'new_item_name'              => __( 'New Possession Year Name', 'flavor-starter' ),
			'separate_items_with_commas' => __( 'Separate possession years with commas', 'flavor-starter' ),
			'add_or_remove_items'        => __( 'Add or remove possession years', 'flavor-starter' ),
			'choose_from_most_used'      => __( 'Choose from the most used possession years', 'flavor-starter' ),
			'not_found'                  => __( 'No possession years found.', 'flavor-starter' ),
			'no_terms'                   => __( 'No possession years', 'flavor-starter' ),
			'menu_name'                  => __( 'Possession Years', 'flavor-starter' ),
			'items_list_navigation'      => __( 'Possession Years list navigation', 'flavor-starter' ),
			'items_list'                 => __( 'Possession Years list', 'flavor-starter' ),
			'back_to_items'              => __( '&larr; Back to Possession Years', 'flavor-starter' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Expected possession year for projects.', 'flavor-starter' ),
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
			'rest_base'          => 'tp-possession-years',
			'rewrite'            => array(
				'slug'       => 'possession',
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
