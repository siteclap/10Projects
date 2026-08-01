<?php
/**
 * Lead management admin page.
 *
 * Custom admin page for managing leads stored in the tp_leads custom table.
 * Uses the WP_List_Table pattern for a consistent WordPress admin experience
 * with filtering, sorting, search, pagination, and bulk actions.
 *
 * @package TenProjects\Admin
 * @since   1.0.0
 */

namespace TenProjects\Admin;

defined( 'ABSPATH' ) || exit;

// WP_List_Table is loaded on-demand.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Lead list table extending WP_List_Table.
 */
class Lead_List_Table extends \WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'lead',
			'plural'   => 'leads',
			'ajax'     => false,
		) );
	}

	/**
	 * Define table columns.
	 *
	 * @return array Column slug => label.
	 */
	public function get_columns(): array {
		return array(
			'cb'              => '<input type="checkbox" />',
			'id'              => __( 'ID', 'tenprojects-ai-matcher' ),
			'customer_name'   => __( 'Customer Name', 'tenprojects-ai-matcher' ),
			'customer_phone'  => __( 'Phone', 'tenprojects-ai-matcher' ),
			'project'         => __( 'Project', 'tenprojects-ai-matcher' ),
			'lead_type'       => __( 'Type', 'tenprojects-ai-matcher' ),
			'quality_score'   => __( 'Quality Score', 'tenprojects-ai-matcher' ),
			'classification'  => __( 'Classification', 'tenprojects-ai-matcher' ),
			'status'          => __( 'Status', 'tenprojects-ai-matcher' ),
			'partner'         => __( 'Partner', 'tenprojects-ai-matcher' ),
			'created_at'      => __( 'Created', 'tenprojects-ai-matcher' ),
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @return array Column slug => [ orderby, default_desc ].
	 */
	public function get_sortable_columns(): array {
		return array(
			'id'            => array( 'id', true ),
			'quality_score' => array( 'quality_score', true ),
			'created_at'    => array( 'created_at', true ),
		);
	}

	/**
	 * Checkbox column.
	 *
	 * @param object $item Row data.
	 * @return string
	 */
	public function column_cb( $item ): string {
		return sprintf( '<input type="checkbox" name="lead_ids[]" value="%d" />', $item->id );
	}

	/**
	 * Default column renderer.
	 *
	 * @param object $item        Row data.
	 * @param string $column_name Column slug.
	 * @return string
	 */
	public function column_default( $item, $column_name ): string {
		switch ( $column_name ) {
			case 'id':
				return esc_html( $item->id );

			case 'customer_name':
				$name = $item->customer_name ?: __( '(unnamed)', 'tenprojects-ai-matcher' );
				$actions = array(
					'view' => sprintf(
						'<a href="%s">%s</a>',
						esc_url( admin_url( 'admin.php?page=tenprojects-leads&action=view&lead_id=' . $item->id ) ),
						__( 'View', 'tenprojects-ai-matcher' )
					),
					'change_status' => sprintf(
						'<a href="%s">%s</a>',
						esc_url( admin_url( 'admin.php?page=tenprojects-leads&action=change_status&lead_id=' . $item->id ) ),
						__( 'Change Status', 'tenprojects-ai-matcher' )
					),
					'assign' => sprintf(
						'<a href="%s">%s</a>',
						esc_url( admin_url( 'admin.php?page=tenprojects-leads&action=assign&lead_id=' . $item->id ) ),
						__( 'Assign Partner', 'tenprojects-ai-matcher' )
					),
				);
				return esc_html( $name ) . $this->row_actions( $actions );

			case 'customer_phone':
				return esc_html( $item->customer_phone ?: '—' );

			case 'project':
				if ( $item->project_id ) {
					$title = get_the_title( (int) $item->project_id );
					return $title ? esc_html( $title ) : '—';
				}
				return '—';

			case 'lead_type':
				return esc_html( ucfirst( str_replace( '_', ' ', $item->lead_type ?: '—' ) ) );

			case 'quality_score':
				$score = (int) $item->quality_score;
				$color = $score >= 70 ? '#10B981' : ( $score >= 40 ? '#F59E0B' : '#EF4444' );
				return sprintf(
					'<strong style="color:%s;">%d</strong>',
					esc_attr( $color ),
					$score
				);

			case 'classification':
				$class_colors = array(
					'hot'         => 'hot',
					'warm'        => 'warm',
					'cold'        => 'cold',
					'researching' => 'cold',
					'ready'       => 'hot',
				);
				$badge = $class_colors[ $item->classification ] ?? 'cold';
				return '<span class="tp-badge tp-badge--' . esc_attr( $badge ) . '">'
					. esc_html( ucfirst( $item->classification ?: '—' ) ) . '</span>';

			case 'status':
				$status_colors = array(
					'new'       => '#1A56DB',
					'contacted' => '#F59E0B',
					'qualified' => '#6366F1',
					'site_visit' => '#8B5CF6',
					'booked'    => '#10B981',
					'lost'      => '#EF4444',
					'duplicate' => '#6B7280',
				);
				$color = $status_colors[ $item->status ] ?? '#6B7280';
				return sprintf(
					'<span class="tp-badge" style="background:%s20;color:%s;">%s</span>',
					esc_attr( $color ),
					esc_attr( $color ),
					esc_html( ucfirst( str_replace( '_', ' ', $item->status ?: '—' ) ) )
				);

			case 'partner':
				if ( $item->partner_company ) {
					return esc_html( $item->partner_company );
				}
				return '<em style="color:#6B7280;">' . esc_html__( 'Unassigned', 'tenprojects-ai-matcher' ) . '</em>';

			case 'created_at':
				return esc_html( $item->created_at ? wp_date( 'M j, Y g:i A', strtotime( $item->created_at ) ) : '—' );

			default:
				return '—';
		}
	}

	/**
	 * Define available bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions(): array {
		return array(
			'export_csv'       => __( 'Export CSV', 'tenprojects-ai-matcher' ),
			'status_contacted' => __( 'Mark as Contacted', 'tenprojects-ai-matcher' ),
			'status_qualified' => __( 'Mark as Qualified', 'tenprojects-ai-matcher' ),
			'status_lost'      => __( 'Mark as Lost', 'tenprojects-ai-matcher' ),
		);
	}

	/**
	 * Extra table navigation — filters.
	 *
	 * @param string $which Top or bottom.
	 */
	public function extra_tablenav( $which ): void {
		if ( 'top' !== $which ) {
			return;
		}

		$current_status         = isset( $_REQUEST['tp_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tp_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$current_classification = isset( $_REQUEST['tp_classification'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tp_classification'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$date_from              = isset( $_REQUEST['tp_date_from'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tp_date_from'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$date_to                = isset( $_REQUEST['tp_date_to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tp_date_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$current_location       = isset( $_REQUEST['tp_location'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tp_location'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		$statuses = array(
			''           => __( 'All Statuses', 'tenprojects-ai-matcher' ),
			'new'        => __( 'New', 'tenprojects-ai-matcher' ),
			'contacted'  => __( 'Contacted', 'tenprojects-ai-matcher' ),
			'qualified'  => __( 'Qualified', 'tenprojects-ai-matcher' ),
			'site_visit' => __( 'Site Visit', 'tenprojects-ai-matcher' ),
			'booked'     => __( 'Booked', 'tenprojects-ai-matcher' ),
			'lost'       => __( 'Lost', 'tenprojects-ai-matcher' ),
			'duplicate'  => __( 'Duplicate', 'tenprojects-ai-matcher' ),
		);

		$classifications = array(
			''            => __( 'All Classifications', 'tenprojects-ai-matcher' ),
			'hot'         => __( 'Hot', 'tenprojects-ai-matcher' ),
			'warm'        => __( 'Warm', 'tenprojects-ai-matcher' ),
			'cold'        => __( 'Cold', 'tenprojects-ai-matcher' ),
			'researching' => __( 'Researching', 'tenprojects-ai-matcher' ),
			'ready'       => __( 'Ready', 'tenprojects-ai-matcher' ),
		);

		echo '<div class="alignleft actions">';

		// Status dropdown.
		echo '<select name="tp_status">';
		foreach ( $statuses as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $current_status, $value, false ) . '>'
				. esc_html( $label ) . '</option>';
		}
		echo '</select>';

		// Classification dropdown.
		echo '<select name="tp_classification">';
		foreach ( $classifications as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $current_classification, $value, false ) . '>'
				. esc_html( $label ) . '</option>';
		}
		echo '</select>';

		// Date range.
		echo '<input type="date" name="tp_date_from" value="' . esc_attr( $date_from ) . '" placeholder="'
			. esc_attr__( 'From date', 'tenprojects-ai-matcher' ) . '" />';
		echo '<input type="date" name="tp_date_to" value="' . esc_attr( $date_to ) . '" placeholder="'
			. esc_attr__( 'To date', 'tenprojects-ai-matcher' ) . '" />';

		// Location filter.
		echo '<input type="text" name="tp_location" value="' . esc_attr( $current_location ) . '" placeholder="'
			. esc_attr__( 'Location', 'tenprojects-ai-matcher' ) . '" style="width:120px;" />';

		submit_button( __( 'Filter', 'tenprojects-ai-matcher' ), '', 'filter_action', false );

		echo '</div>';
	}

	/**
	 * Prepare items for the list table.
	 */
	public function prepare_items(): void {
		global $wpdb;

		$per_page = 20;
		$table    = $wpdb->prefix . 'tp_leads';
		$p_table  = $wpdb->prefix . 'tp_partners';
		$a_table  = $wpdb->prefix . 'tp_lead_assignments';

		// Build WHERE clauses.
		$where  = array( '1=1' );
		$values = array();

		// Status filter.
		if ( ! empty( $_REQUEST['tp_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$where[]  = 'l.status = %s';
			$values[] = sanitize_text_field( wp_unslash( $_REQUEST['tp_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		// Classification filter.
		if ( ! empty( $_REQUEST['tp_classification'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$where[]  = 'l.classification = %s';
			$values[] = sanitize_text_field( wp_unslash( $_REQUEST['tp_classification'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		// Date range.
		if ( ! empty( $_REQUEST['tp_date_from'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$where[]  = 'l.created_at >= %s';
			$values[] = sanitize_text_field( wp_unslash( $_REQUEST['tp_date_from'] ) ) . ' 00:00:00'; // phpcs:ignore WordPress.Security.NonceVerification
		}
		if ( ! empty( $_REQUEST['tp_date_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$where[]  = 'l.created_at <= %s';
			$values[] = sanitize_text_field( wp_unslash( $_REQUEST['tp_date_to'] ) ) . ' 23:59:59'; // phpcs:ignore WordPress.Security.NonceVerification
		}

		// Location filter.
		if ( ! empty( $_REQUEST['tp_location'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$where[]  = 'l.customer_city LIKE %s';
			$values[] = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['tp_location'] ) ) ) . '%'; // phpcs:ignore WordPress.Security.NonceVerification
		}

		// Search.
		if ( ! empty( $_REQUEST['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$search   = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%'; // phpcs:ignore WordPress.Security.NonceVerification
			$where[]  = '(l.customer_name LIKE %s OR l.customer_phone LIKE %s OR l.customer_email LIKE %s)';
			$values[] = $search;
			$values[] = $search;
			$values[] = $search;
		}

		$where_sql = implode( ' AND ', $where );

		// Sorting.
		$allowed_orderby = array( 'id', 'quality_score', 'created_at' );
		$orderby         = isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], $allowed_orderby, true ) // phpcs:ignore WordPress.Security.NonceVerification
			? $_REQUEST['orderby'] // phpcs:ignore WordPress.Security.NonceVerification
			: 'created_at';
		$order           = isset( $_REQUEST['order'] ) && 'asc' === strtolower( $_REQUEST['order'] ) // phpcs:ignore WordPress.Security.NonceVerification
			? 'ASC'
			: 'DESC';

		// Count total items.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = empty( $values )
			? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} l WHERE {$where_sql}" ) // phpcs:ignore WordPress.DB.PreparedSQL
			: (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} l WHERE {$where_sql}", ...$values ) ); // phpcs:ignore WordPress.DB.PreparedSQL

		// Pagination.
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// Query.
		$sql = "SELECT l.*,
				p.company_name AS partner_company
			FROM {$table} l
			LEFT JOIN {$a_table} a ON l.id = a.lead_id
			LEFT JOIN {$p_table} p ON a.partner_id = p.id
			WHERE {$where_sql}
			GROUP BY l.id
			ORDER BY l.{$orderby} {$order}
			LIMIT %d OFFSET %d";

		$query_values   = array_merge( $values, array( $per_page, $offset ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$this->items    = $wpdb->get_results( $wpdb->prepare( $sql, ...$query_values ) );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
	}
}

/**
 * Lead Admin page controller.
 */
class Lead_Admin {

	/**
	 * Render the lead management page.
	 */
	public function render_page(): void {
		// Handle bulk actions.
		$this->process_bulk_actions();

		// Handle single-item actions.
		$this->process_single_actions();

		$table = new Lead_List_Table();
		$table->prepare_items();

		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html__( 'Lead Management', 'tenprojects-ai-matcher' ) . '</h1>';
		echo '</div>';

		// Search box.
		echo '<form method="get">';
		echo '<input type="hidden" name="page" value="tenprojects-leads" />';
		$table->search_box( __( 'Search Leads', 'tenprojects-ai-matcher' ), 'tp-lead-search' );
		$table->display();
		echo '</form>';

		echo '</div>';
	}

	/**
	 * Process bulk actions (CSV export, status changes).
	 */
	private function process_bulk_actions(): void {
		if ( empty( $_REQUEST['lead_ids'] ) || empty( $_REQUEST['action'] ) ) {
			return;
		}

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-leads' ) ) {
			return;
		}

		$action   = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
		$lead_ids = array_map( 'absint', (array) $_REQUEST['lead_ids'] );

		if ( empty( $lead_ids ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'tp_leads';

		// CSV export.
		if ( 'export_csv' === $action ) {
			$this->export_csv( $lead_ids );
			return;
		}

		// Status changes.
		$status_map = array(
			'status_contacted' => 'contacted',
			'status_qualified' => 'qualified',
			'status_lost'      => 'lost',
		);

		if ( isset( $status_map[ $action ] ) ) {
			$new_status = $status_map[ $action ];
			$placeholders = implode( ',', array_fill( 0, count( $lead_ids ), '%d' ) );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET status = %s, updated_at = NOW() WHERE id IN ({$placeholders})",
					$new_status,
					...$lead_ids
				)
			);

			add_action( 'admin_notices', function () use ( $lead_ids, $new_status ) {
				printf(
					'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
					esc_html( sprintf(
						/* translators: 1: count, 2: status */
						__( '%1$d lead(s) marked as %2$s.', 'tenprojects-ai-matcher' ),
						count( $lead_ids ),
						$new_status
					) )
				);
			} );
		}
	}

	/**
	 * Process single-item row actions.
	 */
	private function process_single_actions(): void {
		$action  = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$lead_id = isset( $_REQUEST['lead_id'] ) ? absint( $_REQUEST['lead_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

		if ( ! $action || ! $lead_id ) {
			return;
		}

		if ( 'view' === $action ) {
			$this->render_lead_detail( $lead_id );
			exit; // Stop further rendering — detail view replaces the list.
		}
	}

	/**
	 * Render a single lead detail view.
	 *
	 * @param int $lead_id Lead ID.
	 */
	private function render_lead_detail( int $lead_id ): void {
		global $wpdb;

		$table = $wpdb->prefix . 'tp_leads';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$lead = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $lead_id )
		);

		if ( ! $lead ) {
			echo '<div class="wrap"><div class="notice notice-error"><p>'
				. esc_html__( 'Lead not found.', 'tenprojects-ai-matcher' ) . '</p></div></div>';
			return;
		}

		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html( sprintf( __( 'Lead #%d — %s', 'tenprojects-ai-matcher' ), $lead->id, $lead->customer_name ?: __( 'Unnamed', 'tenprojects-ai-matcher' ) ) ) . '</h1>';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=tenprojects-leads' ) ) . '" class="page-title-action">'
			. esc_html__( 'Back to Leads', 'tenprojects-ai-matcher' ) . '</a>';
		echo '</div>';

		echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">';

		// Contact info.
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Contact Information', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<table class="widefat">';
		echo '<tr><th>' . esc_html__( 'Name', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->customer_name ?: '—' ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Phone', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->customer_phone ?: '—' ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Email', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->customer_email ?: '—' ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'City', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->customer_city ?: '—' ) . '</td></tr>';
		echo '</table>';
		echo '</div>';

		// Lead details.
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Lead Details', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<table class="widefat">';
		echo '<tr><th>' . esc_html__( 'Status', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( ucfirst( $lead->status ) ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Classification', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( ucfirst( $lead->classification ) ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Quality Score', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->quality_score ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Readiness Score', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->readiness_score ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Type', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->lead_type ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Source', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->lead_source ?: '—' ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Created', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->created_at ) . '</td></tr>';
		echo '</table>';
		echo '</div>';

		// Requirements.
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Requirements', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<table class="widefat">';
		echo '<tr><th>' . esc_html__( 'Budget', 'tenprojects-ai-matcher' ) . '</th><td>'
			. esc_html( ( $lead->budget_comfortable ? number_format( $lead->budget_comfortable ) : '—' )
			. ' — '
			. ( $lead->budget_maximum ? number_format( $lead->budget_maximum ) : '—' ) ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Configuration', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->configuration ?: '—' ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Purpose', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( ucfirst( $lead->purpose ?: '—' ) ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Timeline', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( $lead->purchase_timeline ?: '—' ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Risk Profile', 'tenprojects-ai-matcher' ) . '</th><td>' . esc_html( ucfirst( $lead->risk_profile ?: '—' ) ) . '</td></tr>';
		echo '</table>';
		echo '</div>';

		// AI Summary.
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'AI Summary', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<p>' . esc_html( $lead->ai_lead_summary ?: __( 'No AI summary available.', 'tenprojects-ai-matcher' ) ) . '</p>';
		echo '</div>';

		echo '</div>'; // grid.
		echo '</div>'; // wrap.
	}

	/**
	 * Export selected leads as CSV.
	 *
	 * @param int[] $lead_ids Lead IDs to export.
	 */
	private function export_csv( array $lead_ids ): void {
		global $wpdb;

		$table        = $wpdb->prefix . 'tp_leads';
		$placeholders = implode( ',', array_fill( 0, count( $lead_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$leads = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id IN ({$placeholders}) ORDER BY created_at DESC",
				...$lead_ids
			),
			ARRAY_A
		);

		if ( empty( $leads ) ) {
			return;
		}

		$filename = 'tenprojects-leads-' . gmdate( 'Y-m-d-His' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// Header row.
		fputcsv( $output, array_keys( $leads[0] ) );

		foreach ( $leads as $lead ) {
			fputcsv( $output, $lead );
		}

		fclose( $output );
		exit;
	}
}
