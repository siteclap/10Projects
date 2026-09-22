<?php
/**
 * Category Menu — Each property category (Buy, Rent, Commercial, etc.)
 * behaves like its own independent CPT in the admin.
 *
 * Uses a single tp_project CPT under the hood, but dynamically changes
 * labels, headings, URLs, and menus so each category feels completely separate.
 *
 * @package TenProjects\Admin
 * @since   1.0.0
 */

namespace TenProjects\Admin;

defined( 'ABSPATH' ) || exit;

class Category_Menu {

	const CATEGORIES = array(
		'buy'        => array( 'Buy',        'dashicons-building',        4 ),
		'commercial' => array( 'Commercial', 'dashicons-store',           5 ),
		'resale'     => array( 'Resale',     'dashicons-update',          6 ),
		'rent'       => array( 'Rent',       'dashicons-admin-home',      7 ),
		'plot'       => array( 'Plot',       'dashicons-layout',          8 ),
		'pg'         => array( 'PG',         'dashicons-admin-multisite', 9 ),
	);

	public function register() {
		add_action( 'admin_menu', array( $this, 'add_category_menus' ) );
		add_action( 'current_screen', array( $this, 'rebrand_post_type' ) );
		add_action( 'save_post_tp_project', array( $this, 'auto_assign_category' ), 10, 2 );
		add_action( 'admin_head', array( $this, 'highlight_active_menu' ) );
		add_action( 'admin_head', array( $this, 'admin_head_fixes' ) );
		add_filter( 'redirect_post_location', array( $this, 'preserve_category_on_redirect' ), 10, 2 );
	}

	/* ══════════════════════════════════════════════════════════
	 * HELPER — detect active category slug
	 * ══════════════════════════════════════════════════════════ */

	private function detect_active_slug() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['tp_property_type'] ) ) {
			return sanitize_text_field( wp_unslash( $_GET['tp_property_type'] ) );
		}
		if ( ! empty( $_GET['tp_default_type'] ) ) {
			return sanitize_text_field( wp_unslash( $_GET['tp_default_type'] ) );
		}
		if ( ! empty( $_GET['post'] ) ) {
			$terms = wp_get_object_terms( absint( $_GET['post'] ), 'tp_property_type', array( 'fields' => 'slugs' ) );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				return $terms[0];
			}
		}
		// phpcs:enable
		return '';
	}

	/* ══════════════════════════════════════════════════════════
	 * 1. ADMIN MENUS — one per category
	 * ══════════════════════════════════════════════════════════ */

	public function add_category_menus() {
		foreach ( self::CATEGORIES as $slug => $cat ) {
			list( $label, $icon, $position ) = $cat;

			add_menu_page(
				$label . ' Projects',
				$label,
				'edit_posts',
				'edit.php?post_type=tp_project&tp_property_type=' . $slug,
				'',
				$icon,
				$position
			);

			add_submenu_page(
				'edit.php?post_type=tp_project&tp_property_type=' . $slug,
				'All ' . $label,
				'All ' . $label,
				'edit_posts',
				'edit.php?post_type=tp_project&tp_property_type=' . $slug
			);

			add_submenu_page(
				'edit.php?post_type=tp_project&tp_property_type=' . $slug,
				'Add New ' . $label,
				'Add New',
				'edit_posts',
				'post-new.php?post_type=tp_project&tp_default_type=' . $slug
			);
		}
	}

	/* ══════════════════════════════════════════════════════════
	 * 2. REBRAND — change CPT labels server-side BEFORE page renders
	 *    This fires on `current_screen` (before any HTML output)
	 * ══════════════════════════════════════════════════════════ */

	public function rebrand_post_type() {
		$screen = get_current_screen();
		if ( ! $screen || 'tp_project' !== $screen->post_type ) {
			return;
		}

		$active_slug = $this->detect_active_slug();
		if ( empty( $active_slug ) || ! array_key_exists( $active_slug, self::CATEGORIES ) ) {
			$active_slug = 'buy';
		}

		$label = self::CATEGORIES[ $active_slug ][0];

		// Change the registered post type labels so WordPress renders
		// the correct heading, button text, etc. natively.
		global $wp_post_types;
		if ( ! isset( $wp_post_types['tp_project'] ) ) {
			return;
		}

		$pt = $wp_post_types['tp_project'];

		$pt->label                = $label . ' Projects';
		$pt->labels->name         = $label . ' Projects';
		$pt->labels->singular_name = $label . ' Project';
		$pt->labels->add_new      = 'Add New';
		$pt->labels->add_new_item = 'Add New ' . $label . ' Project';
		$pt->labels->edit_item    = 'Edit ' . $label . ' Project';
		$pt->labels->new_item     = 'New ' . $label . ' Project';
		$pt->labels->view_item    = 'View ' . $label . ' Project';
		$pt->labels->all_items    = 'All ' . $label;
		$pt->labels->search_items = 'Search ' . $label . ' Projects';
		$pt->labels->not_found    = 'No ' . strtolower( $label ) . ' projects found.';
		$pt->labels->not_found_in_trash = 'No ' . strtolower( $label ) . ' projects found in Trash.';
		$pt->labels->menu_name    = $label;
	}

	/* ══════════════════════════════════════════════════════════
	 * 3. ADMIN HEAD — fix "Add New" button URL + hide taxonomy panel
	 * ══════════════════════════════════════════════════════════ */

	public function admin_head_fixes() {
		$screen = get_current_screen();
		if ( ! $screen || 'tp_project' !== $screen->post_type ) {
			return;
		}

		$active_slug = $this->detect_active_slug();
		if ( empty( $active_slug ) || ! array_key_exists( $active_slug, self::CATEGORIES ) ) {
			$active_slug = 'buy';
		}

		// Hide taxonomy metabox — category is auto-assigned.
		echo '<style>#tp_property_typediv{display:none!important}</style>';

		// Fix "Add New" button URL on the list page (WordPress hardcodes post-new.php?post_type=X).
		$slug_js = esc_js( $active_slug );
		echo '<script>document.addEventListener("DOMContentLoaded",function(){';
		echo 'document.querySelectorAll("a.page-title-action").forEach(function(a){';
		echo 'if(a.href&&a.href.indexOf("post-new.php")>-1&&a.href.indexOf("tp_default_type")===-1){';
		echo 'a.href+="&tp_default_type=' . $slug_js . '"}});';
		echo '});</script>';
	}

	/* ══════════════════════════════════════════════════════════
	 * 4. SIDEBAR HIGHLIGHT — correct menu stays active
	 * ══════════════════════════════════════════════════════════ */

	public function highlight_active_menu() {
		global $parent_file, $submenu_file;

		$screen = get_current_screen();
		if ( ! $screen || 'tp_project' !== $screen->post_type ) {
			return;
		}

		$active_slug = $this->detect_active_slug();
		if ( empty( $active_slug ) ) {
			$active_slug = 'buy';
		}

		if ( array_key_exists( $active_slug, self::CATEGORIES ) ) {
			$parent_file  = 'edit.php?post_type=tp_project&tp_property_type=' . $active_slug;
			$submenu_file = ( 'edit' === $screen->base )
				? 'edit.php?post_type=tp_project&tp_property_type=' . $active_slug
				: 'post-new.php?post_type=tp_project&tp_default_type=' . $active_slug;
		}
	}

	/* ══════════════════════════════════════════════════════════
	 * 5. REDIRECT — keep category after save/publish
	 * ══════════════════════════════════════════════════════════ */

	public function preserve_category_on_redirect( $location, $post_id ) {
		if ( get_post_type( $post_id ) !== 'tp_project' ) {
			return $location;
		}

		if ( strpos( $location, 'tp_default_type' ) !== false ) {
			return $location;
		}

		$slug = '';
		$terms = wp_get_object_terms( $post_id, 'tp_property_type', array( 'fields' => 'slugs' ) );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$slug = $terms[0];
		}

		if ( empty( $slug ) && ! empty( $_POST['tp_property_type_override'] ) ) { // phpcs:ignore
			$slug = sanitize_text_field( wp_unslash( $_POST['tp_property_type_override'] ) );
		}

		if ( ! empty( $slug ) && array_key_exists( $slug, self::CATEGORIES ) ) {
			$location = add_query_arg( 'tp_default_type', $slug, $location );
		}

		return $location;
	}

	/* ══════════════════════════════════════════════════════════
	 * 6. AUTO-ASSIGN — set property type on first save
	 * ══════════════════════════════════════════════════════════ */

	public function auto_assign_category( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Already has a property type? Keep it.
		$existing = wp_get_object_terms( $post_id, 'tp_property_type', array( 'fields' => 'slugs' ) );
		if ( ! is_wp_error( $existing ) && ! empty( $existing ) ) {
			return;
		}

		$type_slug = '';

		// From hidden form field.
		if ( ! empty( $_POST['tp_property_type_override'] ) ) { // phpcs:ignore
			$type_slug = sanitize_text_field( wp_unslash( $_POST['tp_property_type_override'] ) );
		}

		// From referer URL.
		if ( empty( $type_slug ) ) {
			$referer = wp_get_referer();
			if ( $referer ) {
				$query = wp_parse_url( $referer, PHP_URL_QUERY );
				if ( $query ) {
					parse_str( $query, $params );
					if ( ! empty( $params['tp_default_type'] ) ) {
						$type_slug = sanitize_text_field( $params['tp_default_type'] );
					}
					if ( empty( $type_slug ) && ! empty( $params['tp_property_type'] ) ) {
						$type_slug = sanitize_text_field( $params['tp_property_type'] );
					}
				}
			}
		}

		if ( empty( $type_slug ) || ! array_key_exists( $type_slug, self::CATEGORIES ) ) {
			return;
		}

		$term = get_term_by( 'slug', $type_slug, 'tp_property_type' );
		if ( $term ) {
			wp_set_object_terms( $post_id, array( $term->term_id ), 'tp_property_type' );
		}
	}
}
