<?php
/**
 * Configuration taxonomy registration.
 *
 * Flat taxonomy for BHK configurations and unit types.
 *
 * @package TenProjects\Taxonomy
 * @since   1.0.0
 */

namespace TenProjects\Taxonomy;

defined( 'ABSPATH' ) || exit;

class Configuration_Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	const TAXONOMY = 'tp_configuration';

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
		'1 BHK',
		'1.5 BHK',
		'2 BHK',
		'2.5 BHK',
		'3 BHK',
		'3.5 BHK',
		'4 BHK',
		'5 BHK',
		'Penthouse',
		'Duplex',
	);

	/**
	 * Hook into WordPress to register the taxonomy.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the tp_configuration taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Configurations', 'taxonomy general name', 'flavor-starter' ),
			'singular_name'              => _x( 'Configuration', 'taxonomy singular name', 'flavor-starter' ),
			'search_items'               => __( 'Search Configurations', 'flavor-starter' ),
			'popular_items'              => __( 'Popular Configurations', 'flavor-starter' ),
			'all_items'                  => __( 'All Configurations', 'flavor-starter' ),
			'edit_item'                  => __( 'Edit Configuration', 'flavor-starter' ),
			'view_item'                  => __( 'View Configuration', 'flavor-starter' ),
			'update_item'                => __( 'Update Configuration', 'flavor-starter' ),
			'add_new_item'               => __( 'Add New Configuration', 'flavor-starter' ),
			'new_item_name'              => __( 'New Configuration Name', 'flavor-starter' ),
			'separate_items_with_commas' => __( 'Separate configurations with commas', 'flavor-starter' ),
			'add_or_remove_items'        => __( 'Add or remove configurations', 'flavor-starter' ),
			'choose_from_most_used'      => __( 'Choose from the most used configurations', 'flavor-starter' ),
			'not_found'                  => __( 'No configurations found.', 'flavor-starter' ),
			'no_terms'                   => __( 'No configurations', 'flavor-starter' ),
			'menu_name'                  => __( 'Configurations', 'flavor-starter' ),
			'items_list_navigation'      => __( 'Configurations list navigation', 'flavor-starter' ),
			'items_list'                 => __( 'Configurations list', 'flavor-starter' ),
			'back_to_items'              => __( '&larr; Back to Configurations', 'flavor-starter' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'BHK configurations and unit types for projects.', 'flavor-starter' ),
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
			'rest_base'          => 'tp-configurations',
			'rewrite'            => array(
				'slug'       => 'configuration',
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
