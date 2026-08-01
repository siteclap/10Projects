<?php
/**
 * Guide Custom Post Type.
 *
 * Registers the tp_guide CPT with the tp_guide_type taxonomy.
 * Guides are standard editorial posts (buyer guides, area guides, checklists, etc.)
 * with minimal custom meta — content lives in the block editor.
 *
 * @package TenProjects\CPT
 * @since   1.0.0
 */

namespace TenProjects\CPT;

defined( 'ABSPATH' ) || exit;

class Guide_CPT {

	/**
	 * Post type key.
	 *
	 * @var string
	 */
	const POST_TYPE = 'tp_guide';

	/**
	 * Register CPT and taxonomy hooks.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_guide_type_taxonomy' ) );
	}

	/**
	 * Register the Guide post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Guides', 'Post type general name', 'tenprojects-ai-matcher' ),
			'singular_name'         => _x( 'Guide', 'Post type singular name', 'tenprojects-ai-matcher' ),
			'menu_name'             => _x( 'Guides', 'Admin Menu text', 'tenprojects-ai-matcher' ),
			'name_admin_bar'        => _x( 'Guide', 'Add New on Toolbar', 'tenprojects-ai-matcher' ),
			'add_new'               => __( 'Add New', 'tenprojects-ai-matcher' ),
			'add_new_item'          => __( 'Add New Guide', 'tenprojects-ai-matcher' ),
			'new_item'              => __( 'New Guide', 'tenprojects-ai-matcher' ),
			'edit_item'             => __( 'Edit Guide', 'tenprojects-ai-matcher' ),
			'view_item'             => __( 'View Guide', 'tenprojects-ai-matcher' ),
			'all_items'             => __( 'All Guides', 'tenprojects-ai-matcher' ),
			'search_items'          => __( 'Search Guides', 'tenprojects-ai-matcher' ),
			'parent_item_colon'     => __( 'Parent Guides:', 'tenprojects-ai-matcher' ),
			'not_found'             => __( 'No guides found.', 'tenprojects-ai-matcher' ),
			'not_found_in_trash'    => __( 'No guides found in Trash.', 'tenprojects-ai-matcher' ),
			'featured_image'        => __( 'Guide Cover Image', 'tenprojects-ai-matcher' ),
			'set_featured_image'    => __( 'Set guide cover image', 'tenprojects-ai-matcher' ),
			'remove_featured_image' => __( 'Remove guide cover image', 'tenprojects-ai-matcher' ),
			'use_featured_image'    => __( 'Use as guide cover image', 'tenprojects-ai-matcher' ),
			'archives'              => __( 'Guide Archives', 'tenprojects-ai-matcher' ),
			'filter_items_list'     => __( 'Filter guides list', 'tenprojects-ai-matcher' ),
			'items_list_navigation' => __( 'Guides list navigation', 'tenprojects-ai-matcher' ),
			'items_list'            => __( 'Guides list', 'tenprojects-ai-matcher' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'query_var'           => true,
			'rewrite'             => array(
				'slug'       => 'guides',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 8,
			'menu_icon'           => 'dashicons-book',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'revisions' ),
			'taxonomies'          => array( 'tp_guide_type' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Register the Guide Type taxonomy.
	 *
	 * This is a classification taxonomy specific to Guides
	 * (e.g., Buyer Guide, Area Guide, Checklist, FAQ, How-To).
	 */
	public function register_guide_type_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Guide Types', 'Taxonomy general name', 'tenprojects-ai-matcher' ),
			'singular_name'              => _x( 'Guide Type', 'Taxonomy singular name', 'tenprojects-ai-matcher' ),
			'search_items'               => __( 'Search Guide Types', 'tenprojects-ai-matcher' ),
			'popular_items'              => __( 'Popular Guide Types', 'tenprojects-ai-matcher' ),
			'all_items'                  => __( 'All Guide Types', 'tenprojects-ai-matcher' ),
			'parent_item'                => __( 'Parent Guide Type', 'tenprojects-ai-matcher' ),
			'parent_item_colon'          => __( 'Parent Guide Type:', 'tenprojects-ai-matcher' ),
			'edit_item'                  => __( 'Edit Guide Type', 'tenprojects-ai-matcher' ),
			'update_item'                => __( 'Update Guide Type', 'tenprojects-ai-matcher' ),
			'add_new_item'               => __( 'Add New Guide Type', 'tenprojects-ai-matcher' ),
			'new_item_name'              => __( 'New Guide Type Name', 'tenprojects-ai-matcher' ),
			'separate_items_with_commas' => __( 'Separate guide types with commas', 'tenprojects-ai-matcher' ),
			'add_or_remove_items'        => __( 'Add or remove guide types', 'tenprojects-ai-matcher' ),
			'choose_from_most_used'      => __( 'Choose from the most used guide types', 'tenprojects-ai-matcher' ),
			'not_found'                  => __( 'No guide types found.', 'tenprojects-ai-matcher' ),
			'menu_name'                  => __( 'Guide Types', 'tenprojects-ai-matcher' ),
			'back_to_items'              => __( '&larr; Back to Guide Types', 'tenprojects-ai-matcher' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'       => 'guide-type',
				'with_front' => false,
			),
		);

		register_taxonomy( 'tp_guide_type', self::POST_TYPE, $args );
	}
}
