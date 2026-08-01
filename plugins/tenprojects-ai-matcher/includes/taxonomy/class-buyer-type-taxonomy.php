<?php
/**
 * Buyer Type taxonomy registration.
 *
 * Flat taxonomy for categorising content by target buyer persona.
 *
 * @package TenProjects\Taxonomy
 * @since   1.0.0
 */

namespace TenProjects\Taxonomy;

defined( 'ABSPATH' ) || exit;

class Buyer_Type_Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	const TAXONOMY = 'tp_buyer_type';

	/**
	 * Post types this taxonomy is registered for.
	 *
	 * @var string[]
	 */
	const POST_TYPES = array( 'tp_project', 'tp_review', 'tp_guide' );

	/**
	 * Default terms to seed on activation.
	 *
	 * @var string[]
	 */
	const DEFAULT_TERMS = array(
		'First-time Buyer',
		'Upgrade Buyer',
		'Investor',
		'NRI',
		'Senior',
	);

	/**
	 * Hook into WordPress to register the taxonomy.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the tp_buyer_type taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Buyer Types', 'taxonomy general name', 'flavor-starter' ),
			'singular_name'              => _x( 'Buyer Type', 'taxonomy singular name', 'flavor-starter' ),
			'search_items'               => __( 'Search Buyer Types', 'flavor-starter' ),
			'popular_items'              => __( 'Popular Buyer Types', 'flavor-starter' ),
			'all_items'                  => __( 'All Buyer Types', 'flavor-starter' ),
			'edit_item'                  => __( 'Edit Buyer Type', 'flavor-starter' ),
			'view_item'                  => __( 'View Buyer Type', 'flavor-starter' ),
			'update_item'                => __( 'Update Buyer Type', 'flavor-starter' ),
			'add_new_item'               => __( 'Add New Buyer Type', 'flavor-starter' ),
			'new_item_name'              => __( 'New Buyer Type Name', 'flavor-starter' ),
			'separate_items_with_commas' => __( 'Separate buyer types with commas', 'flavor-starter' ),
			'add_or_remove_items'        => __( 'Add or remove buyer types', 'flavor-starter' ),
			'choose_from_most_used'      => __( 'Choose from the most used buyer types', 'flavor-starter' ),
			'not_found'                  => __( 'No buyer types found.', 'flavor-starter' ),
			'no_terms'                   => __( 'No buyer types', 'flavor-starter' ),
			'menu_name'                  => __( 'Buyer Types', 'flavor-starter' ),
			'items_list_navigation'      => __( 'Buyer Types list navigation', 'flavor-starter' ),
			'items_list'                 => __( 'Buyer Types list', 'flavor-starter' ),
			'back_to_items'              => __( '&larr; Back to Buyer Types', 'flavor-starter' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Target buyer persona categories.', 'flavor-starter' ),
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
			'rest_base'          => 'tp-buyer-types',
			'rewrite'            => array(
				'slug'       => 'buyer-type',
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
