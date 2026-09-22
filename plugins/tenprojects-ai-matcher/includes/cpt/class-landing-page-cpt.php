<?php
/**
 * Landing Page Custom Post Type.
 *
 * Registers the tp_landing_page CPT with all meta fields and
 * a tabbed admin meta box for conversion-focused landing pages.
 *
 * Tabs: Banner Images, Project Overview, Contact & Lead,
 *       Highlights & RERA, Developer Info, Pricing Table,
 *       Plans & Layouts, Amenities, Location
 *
 * @package TenProjects\CPT
 * @since   1.0.0
 */

namespace TenProjects\CPT;

defined( 'ABSPATH' ) || exit;

class Landing_Page_CPT {

	const POST_TYPE  = 'tp_landing_page';
	const META_PREFIX = '_tp_';

	/**
	 * Register CPT, meta, and meta box hooks.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta_fields' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_scripts' ) );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'landing_page_permalink' ), 10, 2 );

		// Resolve root-level LP slugs on 404.
		add_action( 'template_redirect', array( $this, 'resolve_landing_page' ), 5 );
	}

	public function disable_gutenberg( $use_block_editor, $post_type ) {
		if ( self::POST_TYPE === $post_type ) {
			return false;
		}
		return $use_block_editor;
	}

	/**
	 * Generate clean root-level permalink for landing pages.
	 */
	public function landing_page_permalink( $post_link, $post ) {
		if ( self::POST_TYPE !== $post->post_type ) {
			return $post_link;
		}
		return home_url( '/' . $post->post_name . '/' );
	}

	/**
	 * On 404, check if the slug matches a published landing page.
	 * If yes, override the query to serve the LP template.
	 */
	public function resolve_landing_page() {
		if ( ! is_404() ) {
			return;
		}

		// Get the requested slug from the URL.
		$path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

		// Skip multi-segment paths (those belong to other routes).
		if ( empty( $path ) || strpos( $path, '/' ) !== false ) {
			return;
		}

		// Check if a published landing page exists with this slug.
		$lp = get_posts( array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'name'           => sanitize_title( $path ),
			'posts_per_page' => 1,
		) );

		if ( empty( $lp ) ) {
			return;
		}

		// Override the main query to serve this landing page.
		global $wp_query, $post;
		$wp_query = new \WP_Query( array(
			'post_type' => self::POST_TYPE,
			'p'         => $lp[0]->ID,
		) );
		$post = $lp[0];
		setup_postdata( $post );

		// Pass post ID via global for reliable access in template.
		$GLOBALS['tp_lp_post_id'] = $lp[0]->ID;

		status_header( 200 );
		nocache_headers();
		// Prevent LiteSpeed from caching LP pages (its own cache ignores WP's no-cache headers).
		header( 'X-LiteSpeed-Cache-Control: no-cache' );
		include get_theme_file_path( 'single-tp_landing_page.php' );
		exit;
	}

	public function enqueue_media_scripts( $hook_suffix ) {
		if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Register the Landing Page post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => 'Landing Pages',
			'singular_name'      => 'Landing Page',
			'menu_name'          => 'Landing Pages',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Landing Page',
			'edit_item'          => 'Edit Landing Page',
			'new_item'           => 'New Landing Page',
			'view_item'          => 'View Landing Page',
			'all_items'          => 'All Landing Pages',
			'search_items'       => 'Search Landing Pages',
			'not_found'          => 'No landing pages found.',
			'not_found_in_trash' => 'No landing pages found in Trash.',
		);

		register_post_type( self::POST_TYPE, array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_rest'       => true,
			'query_var'          => true,
			'rewrite'            => false, // Routing handled by resolve_landing_page() on 404.
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-welcome-widgets-menus',
			'supports'           => array( 'title', 'thumbnail' ),
		) );
	}

	/**
	 * All meta field definitions.
	 */
	private function get_meta_fields(): array {
		return array(
			// ── Banner Images ──
			'lp_banner_desktop_ids' => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── Project Overview ──
			'lp_project_name'       => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_developer_name'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_developer_logo_id'  => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_location'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_configs'            => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_total_floors'       => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_land_parcel'        => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_possession'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_price_display_min'  => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_offer_text'         => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── Contact & Lead ──
			'lp_phone'              => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_whatsapp_number'    => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── Highlights & RERA ──
			'lp_highlights'         => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'lp_rera_number'        => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_rera_link'          => array( 'type' => 'string',  'sanitize_callback' => 'esc_url_raw' ),

			// ── Developer Info ──
			'lp_about_heading'           => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_developer_about'         => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'lp_developer_established'   => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_developer_projects_count' => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── Pricing Table ──
			'lp_pricing_configs'    => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'lp_pricing_note'       => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_costing_image_id'   => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── Plans & Layouts ──
			'lp_masterplan_ids'     => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_floorplan_ids'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_virtual_tour_ids'   => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── Amenities ──
			'lp_amenities'          => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'lp_amenity_image_ids'  => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),

			// ── Location ──
			'lp_location_brief'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ),
			'lp_location_advantages' => array( 'type' => 'string',  'sanitize_callback' => 'wp_kses_post' ),
			'lp_google_business_name' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),

			// ── Color Settings ──
			'lp_brand_color_bg'      => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_hex_color' ),
			'lp_brand_color_button'  => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_hex_color' ),
			'lp_brand_color_text'    => array( 'type' => 'string',  'sanitize_callback' => 'sanitize_hex_color' ),
		);
	}

	/**
	 * Register meta fields with the REST API.
	 */
	public function register_meta_fields() {
		foreach ( $this->get_meta_fields() as $key => $schema ) {
			register_post_meta(
				self::POST_TYPE,
				self::META_PREFIX . $key,
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => $schema['type'],
					'sanitize_callback' => $schema['sanitize_callback'],
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	public function sanitize_float( $value ): float {
		return (float) $value;
	}

	/**
	 * Add the tabbed meta box.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'tp_landing_page_details',
			'Landing Page Settings',
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the tabbed meta box.
	 */
	public function render_meta_box( \WP_Post $post ) {
		wp_enqueue_media();
		wp_nonce_field( 'tp_landing_page_meta', 'tp_landing_page_meta_nonce' );

		echo '<style>
			#tp_landing_page_details .inside { padding: 0 !important; margin: 0 !important; }
			.tp-meta-box { display: flex !important; flex-direction: row !important; min-height: 420px; border: 1px solid #ddd; background: #fff; }
			.tp-meta-tabs { display: flex !important; flex-direction: column !important; width: 200px !important; flex-shrink: 0 !important; border-right: 1px solid #ddd; background: #fff; margin: 0; padding: 0; }
			.tp-meta-tab { display: block; padding: 13px 18px; font-size: 13px; font-weight: 500; color: #444; cursor: pointer; border: none; border-left: 3px solid transparent; border-bottom: 1px solid #f0f0f1; text-align: right; background: none; transition: all 0.15s; white-space: normal; text-decoration: none; line-height: 1.4; }
			.tp-meta-tab:hover { color: #1A56DB; background: #f8f9ff; }
			.tp-meta-tab:focus { outline: none; box-shadow: none; }
			.tp-meta-tab.active { color: #1A56DB; border-left-color: #1A56DB !important; font-weight: 600; background: #f8f9ff; }
			.tp-meta-panels { flex: 1 !important; min-width: 0; }
			.tp-meta-panel { display: none; padding: 20px 24px; }
			.tp-meta-panel.active { display: block; }
			.tp-field-group { margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 4px; background: #fff; overflow: hidden; }
			.tp-field-group__title { font-size: 13px; font-weight: 600; color: #1e293b; padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; }
			.tp-field-row { display: grid !important; grid-template-columns: repeat(3, 1fr); gap: 0; }
			.tp-field { padding: 14px 16px; border-right: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }
			.tp-field:last-child { border-right: none; }
			.tp-field label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
			.tp-field input[type="text"],
			.tp-field input[type="number"],
			.tp-field input[type="url"],
			.tp-field select { width: 100%; padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 13px; background: #fff; box-sizing: border-box; }
			.tp-field input:focus, .tp-field select:focus, .tp-field textarea:focus { border-color: #1A56DB; outline: none; box-shadow: 0 0 0 1px #1A56DB; }
			.tp-field textarea { width: 100%; padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 13px; background: #fff; box-sizing: border-box; min-height: 80px; resize: vertical; }
			.tp-field .description { display: block; font-size: 11px; color: #6b7280; margin-top: 4px; font-style: normal; }
			.tp-field input[type="checkbox"] { width: auto; margin-right: 6px; }
			.tp-gallery-field { min-width: 0; }
			.tp-gallery-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
			.tp-gallery-item { position: relative; width: 80px; height: 80px; border: 1px solid #d1d5db; border-radius: 4px; overflow: hidden; }
			.tp-gallery-item img { width: 100%; height: 100%; object-fit: cover; }
			.tp-gallery-remove { position: absolute; top: 2px; right: 2px; width: 20px; height: 20px; border: none; background: rgba(0,0,0,0.6); color: #fff; font-size: 14px; line-height: 18px; text-align: center; cursor: pointer; border-radius: 50%; padding: 0; }
			.tp-gallery-remove:hover { background: #ef4444; }
			.tp-gallery-add.button { display: inline-flex; align-items: center; gap: 4px; }
			.tp-json-field { }
			.tp-json-list { margin-bottom: 8px; }
			.tp-json-item { display: flex; gap: 6px; margin-bottom: 6px; align-items: center; }
			.tp-json-item input[type="text"] { flex: 1; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 13px; }
			.tp-json-remove { color: #ef4444 !important; border-color: #fca5a5 !important; min-height: 30px; }
			.tp-json-add { color: #1A56DB !important; border-color: #93c5fd !important; }
			.tp-field-row.tp-cols-2 { grid-template-columns: repeat(2, 1fr) !important; }
			.tp-field-row.tp-cols-4 { grid-template-columns: repeat(4, 1fr) !important; }
			.tp-field-row.tp-cols-1 { grid-template-columns: 1fr !important; }
			.tp-graphics-grid { display: grid !important; grid-template-columns: 1fr 1fr; gap: 20px; }
			.tp-pricing-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
			.tp-pricing-table th { padding: 8px 12px; background: #f8fafc; border: 1px solid #e5e7eb; font-size: 12px; font-weight: 600; color: #374151; text-align: left; }
			.tp-pricing-table td { padding: 6px 12px; border: 1px solid #e5e7eb; }
			.tp-pricing-table input { width: 100%; padding: 5px 8px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 13px; box-sizing: border-box; }
			.tp-pricing-table input:focus { border-color: #1A56DB; outline: none; }
			.tp-pricing-remove-row { color: #ef4444; border: none; background: none; cursor: pointer; font-size: 16px; }
			.tp-pricing-fp-cell { position: relative; text-align: center; vertical-align: middle; }
			.tp-pricing-fp-cell input[type="hidden"] { display: none; }
			.tp-pricing-fp-preview { position: relative; display: inline-block; }
			.tp-loc-adv-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
			.tp-loc-adv-table th { padding: 8px 12px; background: #f8fafc; border: 1px solid #e5e7eb; font-size: 12px; font-weight: 600; color: #374151; text-align: left; }
			.tp-loc-adv-table td { padding: 6px 12px; border: 1px solid #e5e7eb; }
			.tp-loc-adv-table input { width: 100%; padding: 5px 8px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 13px; box-sizing: border-box; }
		</style>';

		$tabs = array(
			'hero'        => 'Banner Images',
			'colors'      => 'Color Settings',
			'overview'    => 'Project Overview',
			'lead_form'   => 'Contact & Lead',
			'highlights'  => 'Highlights & RERA',
			'dev_info'    => 'Developer Info',
			'pricing'     => 'Pricing Table',
			'plans'       => 'Plans & Layouts',
			'amenities'   => 'Amenities',
			'location'    => 'Location',
			'virtual'     => 'Virtual Tour',
		);

		echo '<div class="tp-meta-box" data-post-id="' . esc_attr( $post->ID ) . '">';

		// Tab navigation.
		echo '<div class="tp-meta-tabs">';
		$first = true;
		foreach ( $tabs as $slug => $label ) {
			$active = $first ? ' active' : '';
			echo '<button type="button" class="tp-meta-tab' . $active . '" data-tab="' . esc_attr( $slug ) . '">'
				. esc_html( $label ) . '</button>';
			$first = false;
		}
		echo '</div>';

		// Tab panels.
		echo '<div class="tp-meta-panels">';
		$this->render_hero_panel( $post );
		$this->render_colors_panel( $post );
		$this->render_overview_panel( $post );
		$this->render_lead_form_panel( $post );
		$this->render_highlights_panel( $post );
		$this->render_dev_info_panel( $post );
		$this->render_pricing_panel( $post );
		$this->render_plans_panel( $post );
		$this->render_amenities_panel( $post );
		$this->render_location_panel( $post );
		$this->render_virtual_panel( $post );
		echo '</div>';

		echo '</div>';

		// Inline JS — tab switching + gallery + JSON fields + pricing table + location advantages.
		echo '<script>
		/* ── Pricing row floor plan image picker ── */
		function tpPricingFpOpen(cell){
			if(typeof wp === "undefined" || typeof wp.media !== "function") return;
			var input = cell.querySelector("input[data-col=\"floorplan_id\"]");
			var preview = cell.querySelector(".tp-pricing-fp-preview");
			var frame = wp.media({ title:"Select Unit Plan Image", button:{text:"Use This Image"}, multiple:false, library:{type:"image"} });
			frame.on("select", function(){
				var att = frame.state().get("selection").first().toJSON();
				var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
				input.value = att.id;
				var img = document.createElement("img");
				img.src = thumb;
				img.style.cssText = "width:60px;height:60px;object-fit:cover;border-radius:4px;cursor:pointer;";
				img.className = "tp-fp-img-click";
				var rm = document.createElement("button");
				rm.type = "button";
				rm.className = "tp-pricing-fp-remove tp-fp-rm-click";
				rm.style.cssText = "position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:11px;cursor:pointer;line-height:18px;padding:0;";
				rm.innerHTML = "&times;";
				preview.innerHTML = "";
				preview.appendChild(img);
				preview.appendChild(rm);
			});
			frame.open();
		}
		function tpPricingFpRemove(cell){
			var input = cell.querySelector("input[data-col=\"floorplan_id\"]");
			var preview = cell.querySelector(".tp-pricing-fp-preview");
			input.value = "";
			var btn = document.createElement("button");
			btn.type = "button";
			btn.className = "button button-small tp-fp-add-click";
			btn.style.fontSize = "11px";
			btn.textContent = "+ Image";
			preview.innerHTML = "";
			preview.appendChild(btn);
		}

		/* ── Gallery open — global so onclick="" can reach it ── */
		function tpGalleryOpen(btn){
			if(typeof wp === "undefined" || typeof wp.media !== "function") return;
			var field = btn.closest(".tp-gallery-field");
			if(!field) return;
			var input = field.querySelector(".tp-gallery-ids");
			var grid = field.querySelector(".tp-gallery-grid");
			var frame = wp.media({ title:"Choose Media", button:{text:"Add to Gallery"}, multiple:true, library:{type:"image"} });
			frame.on("select", function(){
				var sel = frame.state().get("selection");
				var ids = input.value ? input.value.split(",").filter(Boolean) : [];
				sel.forEach(function(att){
					var a = att.toJSON();
					if(ids.indexOf(String(a.id)) === -1){
						ids.push(String(a.id));
						var thumb = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
						var div = document.createElement("div");
						div.className = "tp-gallery-item";
						div.setAttribute("data-id", a.id);
						div.innerHTML = "<img src=\""+thumb+"\" alt=\"\" /><button type=\"button\" class=\"tp-gallery-remove\" title=\"Remove\">&times;</button>";
						grid.appendChild(div);
					}
				});
				input.value = ids.join(",");
			});
			frame.open();
		}

		(function(){
			/* Tab switching */
			document.querySelectorAll("#tp_landing_page_details .tp-meta-tab").forEach(function(tab){
				tab.addEventListener("click", function(e){
					e.preventDefault();
					var target = this.getAttribute("data-tab");
					var box = this.closest(".tp-meta-box");
					box.querySelectorAll(".tp-meta-tab").forEach(function(t){ t.classList.remove("active"); });
					this.classList.add("active");
					box.querySelectorAll(".tp-meta-panel").forEach(function(p){ p.classList.remove("active"); });
					var panel = box.querySelector(".tp-meta-panel[data-panel=\""+target+"\"]");
					if(panel) panel.classList.add("active");
					try{ localStorage.setItem("tp_lp_tab_"+document.querySelector("#tp_landing_page_details .tp-meta-box").dataset.postId, target); }catch(ex){}
				});
			});
			/* Restore last tab */
			try{
				var box = document.querySelector("#tp_landing_page_details .tp-meta-box");
				if(box){
					var saved = localStorage.getItem("tp_lp_tab_"+box.dataset.postId);
					if(saved){ var t = box.querySelector(".tp-meta-tab[data-tab=\""+saved+"\"]"); if(t) t.click(); }
				}
			}catch(ex){}

			/* Gallery remove (event delegation) */
			document.addEventListener("click", function(e){
				if(!e.target.classList.contains("tp-gallery-remove")) return;
				var item = e.target.closest(".tp-gallery-item");
				if(!item) return;
				var field = item.closest(".tp-gallery-field");
				if(!field) return;
				var input = field.querySelector(".tp-gallery-ids");
				var removeId = String(item.getAttribute("data-id"));
				item.remove();
				input.value = input.value.split(",").filter(function(id){ return id && id !== removeId; }).join(",");
			});

			/* JSON field add/remove */
			document.addEventListener("click", function(e){
				if(e.target.classList.contains("tp-json-add")){
					e.preventDefault();
					var list = e.target.parentElement.querySelector(".tp-json-list");
					var div = document.createElement("div");
					div.className = "tp-json-item";
					div.innerHTML = "<input type=\"text\" value=\"\" /><button type=\"button\" class=\"button tp-json-remove\">&times;</button>";
					list.appendChild(div);
				}
				if(e.target.classList.contains("tp-json-remove")){
					e.preventDefault();
					e.target.closest(".tp-json-item").remove();
				}
			});

			/* Pricing table add/remove row */
			document.addEventListener("click", function(e){
				if(e.target.classList.contains("tp-pricing-add-row")){
					e.preventDefault();
					var tbody = e.target.parentElement.querySelector(".tp-pricing-table tbody");
					var tr = document.createElement("tr");
					tr.innerHTML = "<td><input type=\"text\" data-col=\"config\" /></td><td><input type=\"text\" data-col=\"area\" /></td><td><input type=\"text\" data-col=\"price\" /></td><td><input type=\"text\" data-col=\"breakup\" /></td><td class=\"tp-pricing-fp-cell\"><input type=\"hidden\" data-col=\"floorplan_id\" value=\"\" /><div class=\"tp-pricing-fp-preview\"><button type=\"button\" class=\"button button-small tp-fp-add-click\" style=\"font-size:11px;\">+ Image</button></div></td><td><button type=\"button\" class=\"tp-pricing-remove-row\">&times;</button></td>";
					tbody.appendChild(tr);
				}
				if(e.target.classList.contains("tp-pricing-remove-row")){
					e.preventDefault();
					e.target.closest("tr").remove();
				}
				/* Delegated clicks for floor plan image/add/remove in pricing rows */
				if(e.target.classList.contains("tp-fp-img-click") || e.target.classList.contains("tp-fp-add-click")){
					var fpCell = e.target.closest(".tp-pricing-fp-cell");
					if(fpCell) tpPricingFpOpen(fpCell);
				}
				if(e.target.classList.contains("tp-fp-rm-click")){
					var fpCell = e.target.closest(".tp-pricing-fp-cell");
					if(fpCell) tpPricingFpRemove(fpCell);
				}
			});

			/* Location advantages add/remove row (max 8) */
			function tpLocAdvUpdateCount(){
				var tbody = document.querySelector(".tp-loc-adv-table tbody");
				if(!tbody) return;
				var count = tbody.querySelectorAll("tr").length;
				var btn = document.querySelector(".tp-loc-adv-add-row");
				var counter = document.querySelector(".tp-loc-adv-count");
				if(counter) counter.textContent = count + " / 8 max";
				if(btn){
					btn.disabled = count >= 8;
					btn.style.opacity = count >= 8 ? ".5" : "";
					btn.style.pointerEvents = count >= 8 ? "none" : "";
				}
			}
			document.addEventListener("click", function(e){
				if(e.target.classList.contains("tp-loc-adv-add-row")){
					e.preventDefault();
					var tbody = document.querySelector(".tp-loc-adv-table tbody");
					if(!tbody || tbody.querySelectorAll("tr").length >= 8) return;
					var tr = document.createElement("tr");
					tr.innerHTML = "<td><input type=\"text\" data-col=\"place\" /></td><td><input type=\"text\" data-col=\"distance\" /></td><td><button type=\"button\" class=\"tp-pricing-remove-row\">&times;</button></td>";
					tbody.appendChild(tr);
					tpLocAdvUpdateCount();
				}
				if(e.target.classList.contains("tp-pricing-remove-row") && e.target.closest(".tp-loc-adv-table")){
					setTimeout(tpLocAdvUpdateCount, 50);
				}
			});

			/* Serialize all JSON + pricing + location on form submit */
			var form = document.getElementById("post");
			if(form){
				form.addEventListener("submit", function(){
					/* JSON fields */
					document.querySelectorAll("#tp_landing_page_details .tp-json-field").forEach(function(field){
						var hidden = field.querySelector("input.tp-json-value");
						var items = [];
						field.querySelectorAll(".tp-json-list input[type=\"text\"]").forEach(function(inp){
							var v = inp.value.trim();
							if(v) items.push(v);
						});
						if(hidden) hidden.value = JSON.stringify(items);
					});

					/* Pricing table */
					var pricingHidden = document.querySelector("input[name=\"_tp_lp_pricing_configs\"]");
					if(pricingHidden){
						var rows = [];
						document.querySelectorAll(".tp-pricing-table tbody tr").forEach(function(tr){
							var obj = {};
							tr.querySelectorAll("input").forEach(function(inp){
								obj[inp.getAttribute("data-col")] = inp.value.trim();
							});
							if(obj.config || obj.area || obj.price) rows.push(obj);
						});
						pricingHidden.value = JSON.stringify(rows);
					}

					/* Location advantages */
					var locAdvHidden = document.querySelector("input[name=\"_tp_lp_location_advantages\"]");
					if(locAdvHidden){
						var rows = [];
						document.querySelectorAll(".tp-loc-adv-table tbody tr").forEach(function(tr){
							var obj = {};
							tr.querySelectorAll("input").forEach(function(inp){
								obj[inp.getAttribute("data-col")] = inp.value.trim();
							});
							if(obj.place || obj.distance) rows.push(obj);
						});
						locAdvHidden.value = JSON.stringify(rows);
					}

				});
			}
		})();
		</script>';
	}

	/* =====================================================================
	   Field Render Helpers
	   ===================================================================== */

	private function render_field( int $post_id, string $key, string $label, string $type = 'text', string $description = '' ) {
		$meta_key = self::META_PREFIX . $key;
		$value    = get_post_meta( $post_id, $meta_key, true );

		echo '<div class="tp-field">';
		echo '<label for="' . esc_attr( $meta_key ) . '">' . esc_html( $label ) . '</label>';

		$step = ( 'number' === $type ) ? ' step="any"' : '';
		echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $meta_key ) . '" '
			. 'name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $value ) . '"' . $step . ' />';

		if ( $description ) {
			echo '<span class="description">' . esc_html( $description ) . '</span>';
		}
		echo '</div>';
	}

	private function render_textarea( int $post_id, string $key, string $label, string $description = '' ) {
		$meta_key = self::META_PREFIX . $key;
		$value    = get_post_meta( $post_id, $meta_key, true );

		echo '<div class="tp-field">';
		echo '<label for="' . esc_attr( $meta_key ) . '">' . esc_html( $label ) . '</label>';
		echo '<textarea id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" rows="4">'
			. esc_textarea( $value ) . '</textarea>';
		if ( $description ) {
			echo '<span class="description">' . esc_html( $description ) . '</span>';
		}
		echo '</div>';
	}

	private function render_checkbox( int $post_id, string $key, string $label ) {
		$meta_key = self::META_PREFIX . $key;
		$value    = get_post_meta( $post_id, $meta_key, true );

		echo '<div class="tp-field">';
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr( $meta_key ) . '" value="1"' . checked( $value, '1', false ) . ' /> ';
		echo esc_html( $label );
		echo '</label>';
		echo '</div>';
	}

	private function render_json_field( int $post_id, string $key, string $label ) {
		$meta_key = self::META_PREFIX . $key;
		$raw      = get_post_meta( $post_id, $meta_key, true );
		$items    = json_decode( $raw, true );
		if ( ! is_array( $items ) ) {
			$items = array();
		}

		echo '<div class="tp-field tp-json-field">';
		echo '<label>' . esc_html( $label ) . '</label>';
		echo '<input type="hidden" class="tp-json-value" name="' . esc_attr( $meta_key ) . '" '
			. 'value="' . esc_attr( wp_json_encode( $items ) ) . '" />';
		echo '<div class="tp-json-list">';
		foreach ( $items as $item ) {
			echo '<div class="tp-json-item">';
			echo '<input type="text" value="' . esc_attr( $item ) . '" />';
			echo '<button type="button" class="button tp-json-remove">&times;</button>';
			echo '</div>';
		}
		echo '</div>';
		echo '<button type="button" class="button tp-json-add">+ Add Item</button>';
		echo '</div>';
	}

	private function render_gallery_field( int $post_id, string $key, string $label ) {
		$meta_key = self::META_PREFIX . $key;
		$ids_str  = get_post_meta( $post_id, $meta_key, true );
		$ids      = array_filter( array_map( 'intval', explode( ',', $ids_str ) ) );

		echo '<div class="tp-field tp-gallery-field" data-field-key="' . esc_attr( $meta_key ) . '">';
		if ( $label ) {
			echo '<label>' . esc_html( $label ) . '</label>';
		}
		echo '<input type="hidden" class="tp-gallery-ids" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $ids_str ) . '" />';
		echo '<div class="tp-gallery-grid">';
		foreach ( $ids as $att_id ) {
			$thumb_url = wp_get_attachment_image_url( $att_id, 'thumbnail' );
			if ( $thumb_url ) {
				echo '<div class="tp-gallery-item" data-id="' . esc_attr( $att_id ) . '">';
				echo '<img src="' . esc_url( $thumb_url ) . '" alt="" />';
				echo '<button type="button" class="tp-gallery-remove" title="Remove">&times;</button>';
				echo '</div>';
			}
		}
		echo '</div>';
		echo '<button type="button" class="button tp-gallery-add" onclick="tpGalleryOpen(this)">';
		echo '<span class="dashicons dashicons-admin-media" style="vertical-align:middle;margin-right:4px;"></span>';
		echo 'Choose Media';
		echo '</button>';
		echo '</div>';
	}

	/**
	 * Render pricing table repeater field.
	 */
	private function render_pricing_table_field( int $post_id ) {
		$meta_key = self::META_PREFIX . 'lp_pricing_configs';
		$raw      = get_post_meta( $post_id, $meta_key, true );
		$rows     = json_decode( $raw, true );
		if ( ! is_array( $rows ) ) {
			$rows = array();
		}

		echo '<input type="hidden" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( wp_json_encode( $rows ) ) . '" />';
		echo '<table class="tp-pricing-table">';
		echo '<thead><tr><th>Configuration</th><th>Carpet Area</th><th>Price</th><th>Breakup</th><th style="width:100px;">Unit Plan</th><th></th></tr></thead>';
		echo '<tbody>';
		foreach ( $rows as $row ) {
			$fp_id  = $row['floorplan_id'] ?? '';
			$fp_url = $fp_id ? wp_get_attachment_image_url( intval( $fp_id ), 'thumbnail' ) : '';
			echo '<tr>';
			echo '<td><input type="text" data-col="config" value="' . esc_attr( $row['config'] ?? '' ) . '" /></td>';
			echo '<td><input type="text" data-col="area" value="' . esc_attr( $row['area'] ?? '' ) . '" /></td>';
			echo '<td><input type="text" data-col="price" value="' . esc_attr( $row['price'] ?? '' ) . '" /></td>';
			echo '<td><input type="text" data-col="breakup" value="' . esc_attr( $row['breakup'] ?? '' ) . '" /></td>';
			echo '<td class="tp-pricing-fp-cell">';
			echo '<input type="hidden" data-col="floorplan_id" value="' . esc_attr( $fp_id ) . '" />';
			if ( $fp_url ) {
				echo '<div class="tp-pricing-fp-preview"><img src="' . esc_url( $fp_url ) . '" class="tp-fp-img-click" style="width:60px;height:60px;object-fit:cover;border-radius:4px;cursor:pointer;" /><button type="button" class="tp-pricing-fp-remove tp-fp-rm-click" style="position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:11px;cursor:pointer;line-height:18px;padding:0;">&times;</button></div>';
			} else {
				echo '<div class="tp-pricing-fp-preview"><button type="button" class="button button-small tp-fp-add-click" style="font-size:11px;">+ Image</button></div>';
			}
			echo '</td>';
			echo '<td><button type="button" class="tp-pricing-remove-row">&times;</button></td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
		echo '<button type="button" class="button tp-pricing-add-row">+ Add Row</button>';
	}

	/**
	 * Render location advantages table repeater field.
	 */
	private function render_location_advantages_field( int $post_id ) {
		$meta_key = self::META_PREFIX . 'lp_location_advantages';
		$raw      = get_post_meta( $post_id, $meta_key, true );
		$rows     = json_decode( $raw, true );
		if ( ! is_array( $rows ) ) {
			$rows = array();
		}

		echo '<input type="hidden" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( wp_json_encode( $rows ) ) . '" />';
		echo '<table class="tp-loc-adv-table">';
		echo '<thead><tr><th>Place / Landmark</th><th>Distance</th><th></th></tr></thead>';
		echo '<tbody>';
		foreach ( $rows as $row ) {
			echo '<tr>';
			echo '<td><input type="text" data-col="place" value="' . esc_attr( $row['place'] ?? '' ) . '" /></td>';
			echo '<td><input type="text" data-col="distance" value="' . esc_attr( $row['distance'] ?? '' ) . '" /></td>';
			echo '<td><button type="button" class="tp-pricing-remove-row">&times;</button></td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
		$count = count( $rows );
		echo '<div style="display:flex;align-items:center;gap:12px;margin-top:8px;">';
		echo '<button type="button" class="button tp-loc-adv-add-row"' . ( $count >= 8 ? ' disabled style="opacity:.5;pointer-events:none"' : '' ) . '>+ Add Row</button>';
		echo '<span class="tp-loc-adv-count" style="font-size:12px;color:#666;">' . $count . ' / 8 max</span>';
		echo '</div>';
	}

	/* =====================================================================
	   Tab Panel Renderers
	   ===================================================================== */

	private function render_hero_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel active" data-panel="hero">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Developer Logo</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'lp_developer_logo_id', 'Developer Logo' );
		echo '<span class="description">Used in header, footer, and favicon.</span>';
		echo '</div></div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Banner Images</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'lp_banner_desktop_ids', 'Banner Images (600×900 Vertical)' );
		echo '<span class="description">Upload vertical banner images — recommended size: 600×900 pixels. These are also used as thumbnails in the Virtual Tour section.</span>';
		echo '</div></div>';

		echo '</div>';
	}

	private function render_colors_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="colors">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Brand Colors</div>';
		echo '<p style="padding:10px 16px 0;margin:0;font-size:12px;color:#6b7280;">Set the brand colors for this landing page. These control buttons, accents, and highlights. Match these to the developer\'s logo/brand for best results.</p>';

		$color_bg   = get_post_meta( $post->ID, '_tp_lp_brand_color_bg', true ) ?: '#c8943e';
		$color_btn  = get_post_meta( $post->ID, '_tp_lp_brand_color_button', true ) ?: '#c8943e';
		$color_text = get_post_meta( $post->ID, '_tp_lp_brand_color_text', true ) ?: '#111827';

		echo '<div style="padding:14px 16px;display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">';

		// Background / Accent Color
		echo '<div>';
		echo '<label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">Background / Accent</label>';
		echo '<div style="display:flex;align-items:center;gap:10px;">';
		echo '<input type="color" name="_tp_lp_brand_color_bg" value="' . esc_attr( $color_bg ) . '" style="width:50px;height:36px;padding:2px;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;">';
		echo '<input type="text" value="' . esc_attr( $color_bg ) . '" style="width:90px;padding:7px 10px;border:1px solid #d1d5db;border-radius:3px;font-size:13px;font-family:monospace;" onchange="this.previousElementSibling.value=this.value" oninput="this.previousElementSibling.value=this.value">';
		echo '</div>';
		echo '<span class="description" style="display:block;font-size:11px;color:#6b7280;margin-top:4px;">Header, buttons, offer box, price color</span>';
		echo '</div>';

		// Button Color
		echo '<div>';
		echo '<label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">Button Color</label>';
		echo '<div style="display:flex;align-items:center;gap:10px;">';
		echo '<input type="color" name="_tp_lp_brand_color_button" value="' . esc_attr( $color_btn ) . '" style="width:50px;height:36px;padding:2px;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;">';
		echo '<input type="text" value="' . esc_attr( $color_btn ) . '" style="width:90px;padding:7px 10px;border:1px solid #d1d5db;border-radius:3px;font-size:13px;font-family:monospace;" onchange="this.previousElementSibling.value=this.value" oninput="this.previousElementSibling.value=this.value">';
		echo '</div>';
		echo '<span class="description" style="display:block;font-size:11px;color:#6b7280;margin-top:4px;">CTA buttons, form submit, dots</span>';
		echo '</div>';

		// Text Color
		echo '<div>';
		echo '<label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">Text Color</label>';
		echo '<div style="display:flex;align-items:center;gap:10px;">';
		echo '<input type="color" name="_tp_lp_brand_color_text" value="' . esc_attr( $color_text ) . '" style="width:50px;height:36px;padding:2px;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;">';
		echo '<input type="text" value="' . esc_attr( $color_text ) . '" style="width:90px;padding:7px 10px;border:1px solid #d1d5db;border-radius:3px;font-size:13px;font-family:monospace;" onchange="this.previousElementSibling.value=this.value" oninput="this.previousElementSibling.value=this.value">';
		echo '</div>';
		echo '<span class="description" style="display:block;font-size:11px;color:#6b7280;margin-top:4px;">Headings, body text</span>';
		echo '</div>';

		echo '</div>';

		// Live preview swatch
		echo '<div style="padding:0 16px 14px;">';
		echo '<div style="display:flex;gap:12px;align-items:center;padding:14px 18px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;">';
		echo '<div style="width:40px;height:40px;border-radius:8px;background:' . esc_attr( $color_bg ) . ';border:1px solid rgba(0,0,0,0.1);"></div>';
		echo '<div style="width:40px;height:40px;border-radius:8px;background:' . esc_attr( $color_btn ) . ';border:1px solid rgba(0,0,0,0.1);"></div>';
		echo '<div style="width:40px;height:40px;border-radius:8px;background:' . esc_attr( $color_text ) . ';border:1px solid rgba(0,0,0,0.1);"></div>';
		echo '<span style="font-size:12px;color:#6b7280;margin-left:8px;">Preview swatches — save & refresh LP to see changes</span>';
		echo '</div>';
		echo '</div>';

		echo '</div>';
		echo '</div>';
	}

	private function render_overview_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="overview">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Project Details</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'lp_project_name', 'Project Name' );
		$this->render_field( $post->ID, 'lp_developer_name', 'Developer Name' );
		$this->render_field( $post->ID, 'lp_location', 'Location' );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'lp_configs', 'Configurations', 'text', 'e.g., 1, 2 & 3 BHK' );
		$this->render_field( $post->ID, 'lp_land_parcel', 'Land Parcel', 'text', 'e.g., 5.5 Acres' );
		$this->render_field( $post->ID, 'lp_total_floors', 'Total Floors' );
		echo '</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'lp_possession', 'Possession', 'text', 'e.g., Dec 2027' );
		$this->render_field( $post->ID, 'lp_offer_text', 'Offer Text', 'text', 'Separate multiple by | e.g., Offer 1 | Offer 2' );
		echo '<div class="tp-field"></div>';
		echo '</div>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Starting Price</div>';
		echo '<div class="tp-field-row tp-cols-1">';
		$this->render_field( $post->ID, 'lp_price_display_min', 'Starting Price', 'text', 'Shown on page hero — e.g., ₹96 Lacs*' );
		echo '</div></div>';

		echo '</div>';
	}


	private function render_lead_form_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="lead_form">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Contact Numbers</div>';
		echo '<div class="tp-field-row">';
		$this->render_field( $post->ID, 'lp_phone', 'Phone Number', 'text', 'Shown in header & sticky CTA' );
		$this->render_field( $post->ID, 'lp_whatsapp_number', 'WhatsApp Number', 'text', 'With country code, no +' );
		echo '<div class="tp-field"></div>';
		echo '</div></div>';

		echo '</div>';
	}

	private function render_highlights_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="highlights">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Project Highlights</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_json_field( $post->ID, 'lp_highlights', 'Highlights' );
		echo '</div></div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">RERA Details</div>';
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_field( $post->ID, 'lp_rera_number', 'RERA Number' );
		$this->render_field( $post->ID, 'lp_rera_link', 'RERA Link', 'url' );
		echo '</div></div>';

		echo '</div>';
	}

	private function render_dev_info_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="dev_info">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Developer Information</div>';
		echo '<div class="tp-field-row tp-cols-1">';
		$this->render_field( $post->ID, 'lp_about_heading', 'About Section Heading', 'text', 'e.g., Welcome To Neelsidhi Centrio Panvel' );
		echo '</div>';
		echo '<div class="tp-field-row tp-cols-2">';
		$this->render_field( $post->ID, 'lp_developer_established', 'Established Year' );
		$this->render_field( $post->ID, 'lp_developer_projects_count', 'Total Projects', 'text', 'e.g., 50+ Projects' );
		echo '</div>';
		echo '<div class="tp-field-row tp-cols-1">';
		$this->render_textarea( $post->ID, 'lp_developer_about', 'About Developer', 'HTML allowed' );
		echo '</div></div>';

		echo '</div>';
	}

	private function render_pricing_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="pricing">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Pricing Configurations</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_pricing_table_field( $post->ID );
		echo '</div></div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Pricing Note</div>';
		echo '<div class="tp-field-row tp-cols-1">';
		$this->render_field( $post->ID, 'lp_pricing_note', 'Note', 'text', 'e.g., * Prices are exclusive of GST, registration and other charges' );
		echo '</div></div>';

		// Costing details image.
		$costing_meta = self::META_PREFIX . 'lp_costing_image_id';
		$costing_id   = get_post_meta( $post->ID, $costing_meta, true );
		$costing_url  = $costing_id ? wp_get_attachment_image_url( intval( $costing_id ), 'medium' ) : '';
		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Complete Costing Details Image</div>';
		echo '<div style="padding:14px 16px;">';
		echo '<div class="tp-pricing-fp-cell" style="max-width:300px;position:relative;">';
		echo '<input type="hidden" data-col="floorplan_id" name="' . esc_attr( $costing_meta ) . '" value="' . esc_attr( $costing_id ) . '" />';
		if ( $costing_url ) {
			echo '<div class="tp-pricing-fp-preview"><img src="' . esc_url( $costing_url ) . '" class="tp-fp-img-click" alt="" style="max-width:100%;height:auto;border-radius:4px;cursor:pointer;" /></div>';
			echo '<div style="margin-top:8px;"><button type="button" class="button tp-fp-add-click">Change Image</button> ';
			echo '<button type="button" class="button tp-fp-rm-click" style="color:#ef4444;">Remove</button></div>';
		} else {
			echo '<div class="tp-pricing-fp-preview"></div>';
			echo '<button type="button" class="button tp-fp-add-click">Upload Costing Image</button>';
		}
		echo '</div>';
		echo '</div></div>';

		echo '</div>';
	}

	private function render_plans_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="plans">';

		echo '<div class="tp-graphics-grid">';

		echo '<div class="tp-field-group" style="margin-bottom:0;">';
		echo '<div class="tp-field-group__title">Master Plan</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'lp_masterplan_ids', 'Master Plan Images' );
		echo '</div></div>';

		echo '<div class="tp-field-group" style="margin-bottom:0;">';
		echo '<div class="tp-field-group__title">Floor Plans</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'lp_floorplan_ids', 'Floor Plan Images' );
		echo '</div></div>';

		echo '</div>';

		echo '</div>';
	}

	private function render_amenities_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="amenities">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Amenities List</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_json_field( $post->ID, 'lp_amenities', 'Amenities' );
		echo '<span class="description">Add one amenity per row (e.g., Swimming Pool, Gymnasium, Clubhouse)</span>';
		echo '</div></div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Amenity Images</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'lp_amenity_image_ids', 'Amenity Images' );
		echo '<span class="description">Upload images for each amenity (in same order as amenities list above). Used on desktop grid &amp; mobile carousel.</span>';
		echo '</div></div>';

		echo '</div>';
	}

	private function render_location_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="location">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Google Business Name</div>';
		echo '<div class="tp-field-row tp-cols-1">';
		$this->render_field( $post->ID, 'lp_google_business_name', 'Business Name', 'text', 'e.g. Millennium Celesta - by Millennium Group' );
		echo '</div>';
		echo '<p class="description" style="padding:0 16px 14px;margin:0;color:#646970;">Enter the exact Google My Business name. The map will be auto-generated on the frontend. No embed code needed.</p>';
		echo '</div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Location Brief</div>';
		echo '<div class="tp-field-row tp-cols-1">';
		$this->render_textarea( $post->ID, 'lp_location_brief', 'Location Description', 'Short paragraph about the project location shown below the heading' );
		echo '</div></div>';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Location Advantages</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_location_advantages_field( $post->ID );
		echo '</div></div>';

		echo '</div>';
	}

	private function render_virtual_panel( \WP_Post $post ) {
		echo '<div class="tp-meta-panel" data-panel="virtual">';

		echo '<div class="tp-field-group">';
		echo '<div class="tp-field-group__title">Virtual Site Visit Images</div>';
		echo '<div style="padding:14px 16px;">';
		$this->render_gallery_field( $post->ID, 'lp_virtual_tour_ids', 'Virtual Tour Images' );
		echo '<span class="description">Upload 2 images for the Virtual Site Visit section (e.g., sample flat interior, drone view). If empty, banner images are used as fallback.</span>';
		echo '</div></div>';

		echo '</div>';
	}

	/* =====================================================================
	   Save Meta
	   ===================================================================== */

	public function save_meta( int $post_id, \WP_Post $post ) {
		if ( ! isset( $_POST['tp_landing_page_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['tp_landing_page_meta_nonce'], 'tp_landing_page_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = $this->get_meta_fields();

		foreach ( $fields as $key => $schema ) {
			$meta_key = self::META_PREFIX . $key;

			if ( 'boolean' === $schema['type'] ) {
				$value = isset( $_POST[ $meta_key ] ) ? '1' : '0';
				update_post_meta( $post_id, $meta_key, $value );
				continue;
			}

			if ( ! isset( $_POST[ $meta_key ] ) ) {
				continue;
			}

			$value = $_POST[ $meta_key ];

			if ( is_callable( $schema['sanitize_callback'] ) ) {
				$value = call_user_func( $schema['sanitize_callback'], $value );
			}

			update_post_meta( $post_id, $meta_key, $value );
		}

	}
}
