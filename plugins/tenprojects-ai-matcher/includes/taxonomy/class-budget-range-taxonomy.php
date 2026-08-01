<?php
/**
 * Budget Range taxonomy registration.
 *
 * Flat taxonomy for price bracket filtering.
 *
 * @package TenProjects\Taxonomy
 * @since   1.0.0
 */

namespace TenProjects\Taxonomy;

defined( 'ABSPATH' ) || exit;

class Budget_Range_Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	const TAXONOMY = 'tp_budget_range';

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
		'Under ₹50L',
		'₹50L–₹75L',
		'₹75L–₹1Cr',
		'₹1Cr–₹1.5Cr',
		'₹1.5Cr–₹2Cr',
		'₹2Cr–₹3Cr',
		'₹3Cr+',
	);

	/**
	 * Hook into WordPress to register the taxonomy.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the tp_budget_range taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Budget Ranges', 'taxonomy general name', 'flavor-starter' ),
			'singular_name'              => _x( 'Budget Range', 'taxonomy singular name', 'flavor-starter' ),
			'search_items'               => __( 'Search Budget Ranges', 'flavor-starter' ),
			'popular_items'              => __( 'Popular Budget Ranges', 'flavor-starter' ),
			'all_items'                  => __( 'All Budget Ranges', 'flavor-starter' ),
			'edit_item'                  => __( 'Edit Budget Range', 'flavor-starter' ),
			'view_item'                  => __( 'View Budget Range', 'flavor-starter' ),
			'update_item'                => __( 'Update Budget Range', 'flavor-starter' ),
			'add_new_item'               => __( 'Add New Budget Range', 'flavor-starter' ),
			'new_item_name'              => __( 'New Budget Range Name', 'flavor-starter' ),
			'separate_items_with_commas' => __( 'Separate budget ranges with commas', 'flavor-starter' ),
			'add_or_remove_items'        => __( 'Add or remove budget ranges', 'flavor-starter' ),
			'choose_from_most_used'      => __( 'Choose from the most used budget ranges', 'flavor-starter' ),
			'not_found'                  => __( 'No budget ranges found.', 'flavor-starter' ),
			'no_terms'                   => __( 'No budget ranges', 'flavor-starter' ),
			'menu_name'                  => __( 'Budget Ranges', 'flavor-starter' ),
			'items_list_navigation'      => __( 'Budget Ranges list navigation', 'flavor-starter' ),
			'items_list'                 => __( 'Budget Ranges list', 'flavor-starter' ),
			'back_to_items'              => __( '&larr; Back to Budget Ranges', 'flavor-starter' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Price bracket ranges for project filtering.', 'flavor-starter' ),
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
			'rest_base'          => 'tp-budget-ranges',
			'rewrite'            => array(
				'slug'       => 'budget',
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
