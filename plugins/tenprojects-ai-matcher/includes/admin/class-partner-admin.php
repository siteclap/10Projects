<?php
/**
 * Partner management admin page.
 *
 * Custom admin page for managing channel partners stored in the tp_partners
 * custom table. Supports listing, creating, editing, credit management,
 * and performance statistics.
 *
 * @package TenProjects\Admin
 * @since   1.0.0
 */

namespace TenProjects\Admin;

defined( 'ABSPATH' ) || exit;

class Partner_Admin {

	/**
	 * Database table name (without prefix).
	 *
	 * @var string
	 */
	const TABLE = 'tp_partners';

	/**
	 * Render the partner management page.
	 */
	public function render_page(): void {
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'list'; // phpcs:ignore WordPress.Security.NonceVerification

		switch ( $action ) {
			case 'add':
				$this->render_add_form();
				break;

			case 'edit':
				$partner_id = isset( $_REQUEST['partner_id'] ) ? absint( $_REQUEST['partner_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$this->render_edit_form( $partner_id );
				break;

			case 'credits':
				$partner_id = isset( $_REQUEST['partner_id'] ) ? absint( $_REQUEST['partner_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$this->render_credit_management( $partner_id );
				break;

			case 'save':
				$this->handle_save();
				break;

			case 'save_credits':
				$this->handle_save_credits();
				break;

			default:
				$this->render_list();
				break;
		}
	}

	/**
	 * Render the partner list.
	 */
	private function render_list(): void {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$partners = $wpdb->get_results(
			"SELECT * FROM {$table} ORDER BY created_at DESC"
		);

		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html__( 'Partners', 'tenprojects-ai-matcher' ) . '</h1>';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=tenprojects-partners&action=add' ) ) . '" class="page-title-action">'
			. esc_html__( 'Add New Partner', 'tenprojects-ai-matcher' ) . '</a>';
		echo '</div>';

		// Summary stats.
		$active_count  = 0;
		$total_leads   = 0;
		$total_booked  = 0;

		foreach ( $partners as $p ) {
			if ( $p->is_active ) {
				$active_count++;
			}
			$total_leads  += (int) $p->total_leads_received;
			$total_booked += (int) $p->total_leads_converted;
		}

		echo '<div class="tp-dashboard-grid" style="grid-template-columns:repeat(3,1fr);">';
		$this->render_stat_card( __( 'Active Partners', 'tenprojects-ai-matcher' ), $active_count );
		$this->render_stat_card( __( 'Total Leads Sent', 'tenprojects-ai-matcher' ), $total_leads );
		$this->render_stat_card( __( 'Total Conversions', 'tenprojects-ai-matcher' ), $total_booked );
		echo '</div>';

		// List table.
		echo '<table class="widefat striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Name', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Company', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Phone', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Email', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Credits', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Leads (Total / Converted)', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Rating', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Status', 'tenprojects-ai-matcher' ) . '</th>';
		echo '<th>' . esc_html__( 'Actions', 'tenprojects-ai-matcher' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		if ( empty( $partners ) ) {
			echo '<tr><td colspan="9">' . esc_html__( 'No partners found.', 'tenprojects-ai-matcher' ) . '</td></tr>';
		}

		foreach ( $partners as $partner ) {
			$status_badge = $partner->is_active
				? '<span class="tp-badge tp-badge--active">' . esc_html__( 'Active', 'tenprojects-ai-matcher' ) . '</span>'
				: '<span class="tp-badge tp-badge--sold-out">' . esc_html__( 'Inactive', 'tenprojects-ai-matcher' ) . '</span>';

			$edit_url    = admin_url( 'admin.php?page=tenprojects-partners&action=edit&partner_id=' . $partner->id );
			$credits_url = admin_url( 'admin.php?page=tenprojects-partners&action=credits&partner_id=' . $partner->id );

			echo '<tr>';
			echo '<td><strong>' . esc_html( $partner->contact_person ?: '—' ) . '</strong></td>';
			echo '<td>' . esc_html( $partner->company_name ) . '</td>';
			echo '<td>' . esc_html( $partner->phone ) . '</td>';
			echo '<td>' . esc_html( $partner->email ) . '</td>';
			echo '<td><strong>' . esc_html( $partner->lead_credits ) . '</strong></td>';
			echo '<td>' . esc_html( $partner->total_leads_received . ' / ' . $partner->total_leads_converted ) . '</td>';
			echo '<td>' . esc_html( number_format( (float) $partner->rating, 2 ) ) . '</td>';
			echo '<td>' . $status_badge . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<td>';
			echo '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'tenprojects-ai-matcher' ) . '</a>';
			echo ' | ';
			echo '<a href="' . esc_url( $credits_url ) . '">' . esc_html__( 'Credits', 'tenprojects-ai-matcher' ) . '</a>';
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Render the "Add New Partner" form.
	 */
	private function render_add_form(): void {
		$this->render_partner_form( null );
	}

	/**
	 * Render the "Edit Partner" form.
	 *
	 * @param int $partner_id Partner ID.
	 */
	private function render_edit_form( int $partner_id ): void {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$partner = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $partner_id )
		);

		if ( ! $partner ) {
			echo '<div class="wrap"><div class="notice notice-error"><p>'
				. esc_html__( 'Partner not found.', 'tenprojects-ai-matcher' ) . '</p></div></div>';
			return;
		}

		$this->render_partner_form( $partner );
	}

	/**
	 * Render the partner form (add or edit).
	 *
	 * @param object|null $partner Existing partner data, or null for new.
	 */
	private function render_partner_form( ?object $partner ): void {
		$is_edit = null !== $partner;
		$title   = $is_edit
			? sprintf( __( 'Edit Partner: %s', 'tenprojects-ai-matcher' ), $partner->company_name )
			: __( 'Add New Partner', 'tenprojects-ai-matcher' );

		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html( $title ) . '</h1>';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=tenprojects-partners' ) ) . '" class="page-title-action">'
			. esc_html__( 'Back to Partners', 'tenprojects-ai-matcher' ) . '</a>';
		echo '</div>';

		echo '<form method="post" action="' . esc_url( admin_url( 'admin.php?page=tenprojects-partners&action=save' ) ) . '">';
		wp_nonce_field( 'tp_save_partner', 'tp_partner_nonce' );

		if ( $is_edit ) {
			echo '<input type="hidden" name="partner_id" value="' . esc_attr( $partner->id ) . '" />';
		}

		echo '<table class="form-table">';

		// Company Name.
		$this->form_row(
			__( 'Company Name', 'tenprojects-ai-matcher' ),
			'<input type="text" name="company_name" value="' . esc_attr( $partner->company_name ?? '' )
				. '" class="regular-text" required />'
		);

		// Contact Person.
		$this->form_row(
			__( 'Contact Person', 'tenprojects-ai-matcher' ),
			'<input type="text" name="contact_person" value="' . esc_attr( $partner->contact_person ?? '' )
				. '" class="regular-text" />'
		);

		// Phone.
		$this->form_row(
			__( 'Phone', 'tenprojects-ai-matcher' ),
			'<input type="text" name="phone" value="' . esc_attr( $partner->phone ?? '' )
				. '" class="regular-text" required />'
		);

		// Email.
		$this->form_row(
			__( 'Email', 'tenprojects-ai-matcher' ),
			'<input type="email" name="email" value="' . esc_attr( $partner->email ?? '' )
				. '" class="regular-text" required />'
		);

		// Partner Type.
		$types        = array( 'channel_partner', 'mandate_firm', 'developer_sales', 'broker' );
		$type_options = '';
		foreach ( $types as $type ) {
			$selected      = ( ( $partner->partner_type ?? '' ) === $type ) ? ' selected' : '';
			$type_options .= '<option value="' . esc_attr( $type ) . '"' . $selected . '>'
				. esc_html( ucwords( str_replace( '_', ' ', $type ) ) ) . '</option>';
		}
		$this->form_row(
			__( 'Partner Type', 'tenprojects-ai-matcher' ),
			'<select name="partner_type" required>' . $type_options . '</select>'
		);

		// RERA Number.
		$this->form_row(
			__( 'RERA Number', 'tenprojects-ai-matcher' ),
			'<input type="text" name="rera_number" value="' . esc_attr( $partner->rera_number ?? '' ) . '" class="regular-text" />'
		);

		// Areas Served (JSON).
		$locations_val = '';
		if ( $is_edit && $partner->locations ) {
			$locations_val = is_string( $partner->locations ) ? $partner->locations : wp_json_encode( $partner->locations );
		}
		$this->form_row(
			__( 'Areas Served (JSON)', 'tenprojects-ai-matcher' ),
			'<textarea name="locations" rows="3" class="large-text" placeholder=\'["Panvel","Kharghar","Ulwe"]\'>'
				. esc_textarea( $locations_val ) . '</textarea>'
				. '<p class="description">' . esc_html__( 'JSON array of location slugs.', 'tenprojects-ai-matcher' ) . '</p>'
		);

		// Configurations (JSON).
		$configs_val = '';
		if ( $is_edit && $partner->configurations ) {
			$configs_val = is_string( $partner->configurations ) ? $partner->configurations : wp_json_encode( $partner->configurations );
		}
		$this->form_row(
			__( 'Configurations (JSON)', 'tenprojects-ai-matcher' ),
			'<textarea name="configurations" rows="2" class="large-text" placeholder=\'["1BHK","2BHK","3BHK"]\'>'
				. esc_textarea( $configs_val ) . '</textarea>'
		);

		// Budget Range.
		$this->form_row(
			__( 'Budget Range', 'tenprojects-ai-matcher' ),
			'<input type="number" name="budget_range_min" value="' . esc_attr( $partner->budget_range_min ?? '' )
				. '" placeholder="' . esc_attr__( 'Min', 'tenprojects-ai-matcher' ) . '" style="width:140px;" /> — '
				. '<input type="number" name="budget_range_max" value="' . esc_attr( $partner->budget_range_max ?? '' )
				. '" placeholder="' . esc_attr__( 'Max', 'tenprojects-ai-matcher' ) . '" style="width:140px;" />'
		);

		// Lead Limits.
		$this->form_row(
			__( 'Monthly Lead Limit', 'tenprojects-ai-matcher' ),
			'<input type="number" name="monthly_lead_limit" value="' . esc_attr( $partner->monthly_lead_limit ?? 0 )
				. '" min="0" />'
		);

		$this->form_row(
			__( 'Daily Lead Limit', 'tenprojects-ai-matcher' ),
			'<input type="number" name="daily_lead_limit" value="' . esc_attr( $partner->daily_lead_limit ?? 0 )
				. '" min="0" />'
		);

		// Response SLA.
		$this->form_row(
			__( 'Response SLA (hours)', 'tenprojects-ai-matcher' ),
			'<input type="number" name="response_sla_hours" value="' . esc_attr( $partner->response_sla_hours ?? 24 )
				. '" min="1" />'
		);

		// Exclusivity.
		$excl_checked = ( $partner->exclusivity ?? 0 ) ? ' checked' : '';
		$this->form_row(
			__( 'Exclusivity', 'tenprojects-ai-matcher' ),
			'<label><input type="checkbox" name="exclusivity" value="1"' . $excl_checked . ' /> '
				. esc_html__( 'Exclusive lead access', 'tenprojects-ai-matcher' ) . '</label>'
		);

		// Active.
		$active_checked = ( ! $is_edit || $partner->is_active ) ? ' checked' : '';
		$this->form_row(
			__( 'Active', 'tenprojects-ai-matcher' ),
			'<label><input type="checkbox" name="is_active" value="1"' . $active_checked . ' /> '
				. esc_html__( 'Partner is active and can receive leads', 'tenprojects-ai-matcher' ) . '</label>'
		);

		echo '</table>';

		submit_button( $is_edit ? __( 'Update Partner', 'tenprojects-ai-matcher' ) : __( 'Add Partner', 'tenprojects-ai-matcher' ) );

		echo '</form>';
		echo '</div>';
	}

	/**
	 * Handle partner save (create or update).
	 */
	private function handle_save(): void {
		if ( ! isset( $_POST['tp_partner_nonce'] )
			|| ! wp_verify_nonce( $_POST['tp_partner_nonce'], 'tp_save_partner' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'tenprojects-ai-matcher' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'tenprojects-ai-matcher' ) );
		}

		global $wpdb;

		$table      = $wpdb->prefix . self::TABLE;
		$partner_id = isset( $_POST['partner_id'] ) ? absint( $_POST['partner_id'] ) : 0;

		$data = array(
			'company_name'      => sanitize_text_field( wp_unslash( $_POST['company_name'] ?? '' ) ),
			'contact_person'    => sanitize_text_field( wp_unslash( $_POST['contact_person'] ?? '' ) ),
			'phone'             => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'email'             => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			'partner_type'      => sanitize_text_field( wp_unslash( $_POST['partner_type'] ?? '' ) ),
			'rera_number'       => sanitize_text_field( wp_unslash( $_POST['rera_number'] ?? '' ) ),
			'locations'         => sanitize_textarea_field( wp_unslash( $_POST['locations'] ?? '' ) ),
			'configurations'    => sanitize_textarea_field( wp_unslash( $_POST['configurations'] ?? '' ) ),
			'budget_range_min'  => absint( $_POST['budget_range_min'] ?? 0 ),
			'budget_range_max'  => absint( $_POST['budget_range_max'] ?? 0 ),
			'monthly_lead_limit' => absint( $_POST['monthly_lead_limit'] ?? 0 ),
			'daily_lead_limit'  => absint( $_POST['daily_lead_limit'] ?? 0 ),
			'response_sla_hours' => absint( $_POST['response_sla_hours'] ?? 24 ),
			'exclusivity'       => isset( $_POST['exclusivity'] ) ? 1 : 0,
			'is_active'         => isset( $_POST['is_active'] ) ? 1 : 0,
		);

		if ( $partner_id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update( $table, $data, array( 'id' => $partner_id ) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->insert( $table, $data );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=tenprojects-partners&saved=1' ) );
		exit;
	}

	/**
	 * Render credit management page for a partner.
	 *
	 * @param int $partner_id Partner ID.
	 */
	private function render_credit_management( int $partner_id ): void {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$partner = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $partner_id )
		);

		if ( ! $partner ) {
			echo '<div class="wrap"><div class="notice notice-error"><p>'
				. esc_html__( 'Partner not found.', 'tenprojects-ai-matcher' ) . '</p></div></div>';
			return;
		}

		echo '<div class="wrap">';
		echo '<div class="tp-admin-header">';
		echo '<h1>' . esc_html( sprintf( __( 'Credits: %s', 'tenprojects-ai-matcher' ), $partner->company_name ) ) . '</h1>';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=tenprojects-partners' ) ) . '" class="page-title-action">'
			. esc_html__( 'Back to Partners', 'tenprojects-ai-matcher' ) . '</a>';
		echo '</div>';

		// Current balance.
		echo '<div class="tp-dashboard-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">';
		$this->render_stat_card( __( 'Current Credits', 'tenprojects-ai-matcher' ), $partner->lead_credits );
		$this->render_stat_card( __( 'Leads Received', 'tenprojects-ai-matcher' ), $partner->total_leads_received );
		$this->render_stat_card( __( 'Conversion Rate', 'tenprojects-ai-matcher' ), number_format( (float) $partner->conversion_rate, 1 ) . '%' );
		echo '</div>';

		// Credit adjustment form.
		echo '<div class="tp-stat-card" style="max-width:500px;">';
		echo '<div class="tp-field-group__title">' . esc_html__( 'Adjust Credits', 'tenprojects-ai-matcher' ) . '</div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin.php?page=tenprojects-partners&action=save_credits' ) ) . '">';
		wp_nonce_field( 'tp_adjust_credits', 'tp_credits_nonce' );
		echo '<input type="hidden" name="partner_id" value="' . esc_attr( $partner->id ) . '" />';

		echo '<table class="form-table">';
		$this->form_row(
			__( 'Adjustment Type', 'tenprojects-ai-matcher' ),
			'<select name="credit_type">'
				. '<option value="add">' . esc_html__( 'Add Credits', 'tenprojects-ai-matcher' ) . '</option>'
				. '<option value="deduct">' . esc_html__( 'Deduct Credits', 'tenprojects-ai-matcher' ) . '</option>'
			. '</select>'
		);
		$this->form_row(
			__( 'Amount', 'tenprojects-ai-matcher' ),
			'<input type="number" name="credit_amount" min="1" value="1" required />'
		);
		$this->form_row(
			__( 'Note', 'tenprojects-ai-matcher' ),
			'<textarea name="credit_note" rows="2" class="large-text" placeholder="'
				. esc_attr__( 'Reason for adjustment...', 'tenprojects-ai-matcher' ) . '"></textarea>'
		);
		echo '</table>';

		submit_button( __( 'Apply', 'tenprojects-ai-matcher' ) );

		echo '</form>';
		echo '</div>';

		// Credit history from audit log.
		$audit_table = $wpdb->prefix . 'tp_audit_log';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$history = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$audit_table} WHERE entity_type = 'partner' AND entity_id = %d AND action LIKE 'credit%%' ORDER BY created_at DESC LIMIT 20",
				$partner_id
			)
		);

		if ( ! empty( $history ) ) {
			echo '<h3>' . esc_html__( 'Credit History', 'tenprojects-ai-matcher' ) . '</h3>';
			echo '<table class="widefat striped">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__( 'Date', 'tenprojects-ai-matcher' ) . '</th>';
			echo '<th>' . esc_html__( 'Action', 'tenprojects-ai-matcher' ) . '</th>';
			echo '<th>' . esc_html__( 'Details', 'tenprojects-ai-matcher' ) . '</th>';
			echo '</tr></thead><tbody>';

			foreach ( $history as $entry ) {
				echo '<tr>';
				echo '<td>' . esc_html( $entry->created_at ) . '</td>';
				echo '<td>' . esc_html( $entry->action ) . '</td>';
				echo '<td>' . esc_html( $entry->details ?? '—' ) . '</td>';
				echo '</tr>';
			}

			echo '</tbody></table>';
		}

		echo '</div>';
	}

	/**
	 * Handle credit adjustment save.
	 */
	private function handle_save_credits(): void {
		if ( ! isset( $_POST['tp_credits_nonce'] )
			|| ! wp_verify_nonce( $_POST['tp_credits_nonce'], 'tp_adjust_credits' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'tenprojects-ai-matcher' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'tenprojects-ai-matcher' ) );
		}

		global $wpdb;

		$partner_id = absint( $_POST['partner_id'] ?? 0 );
		$type       = sanitize_text_field( wp_unslash( $_POST['credit_type'] ?? 'add' ) );
		$amount     = absint( $_POST['credit_amount'] ?? 0 );
		$note       = sanitize_textarea_field( wp_unslash( $_POST['credit_note'] ?? '' ) );

		if ( ! $partner_id || ! $amount ) {
			wp_safe_redirect( admin_url( 'admin.php?page=tenprojects-partners&action=credits&partner_id=' . $partner_id . '&error=1' ) );
			exit;
		}

		$table = $wpdb->prefix . self::TABLE;

		if ( 'deduct' === $type ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET lead_credits = GREATEST(0, lead_credits - %d) WHERE id = %d",
					$amount,
					$partner_id
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET lead_credits = lead_credits + %d WHERE id = %d",
					$amount,
					$partner_id
				)
			);
		}

		// Log to audit table.
		$audit_table = $wpdb->prefix . 'tp_audit_log';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->insert( $audit_table, array(
			'user_id'     => get_current_user_id(),
			'action'      => 'credit_' . $type,
			'entity_type' => 'partner',
			'entity_id'   => $partner_id,
			'details'     => wp_json_encode( array(
				'amount' => $amount,
				'type'   => $type,
				'note'   => $note,
			) ),
			'created_at'  => current_time( 'mysql' ),
		) );

		wp_safe_redirect( admin_url( 'admin.php?page=tenprojects-partners&action=credits&partner_id=' . $partner_id . '&updated=1' ) );
		exit;
	}

	/**
	 * Render a stat card.
	 *
	 * @param string     $label Card label.
	 * @param string|int $value Card value.
	 */
	private function render_stat_card( string $label, $value ): void {
		echo '<div class="tp-stat-card">';
		echo '<div class="tp-stat-card__label">' . esc_html( $label ) . '</div>';
		echo '<div class="tp-stat-card__value">' . esc_html( $value ) . '</div>';
		echo '</div>';
	}

	/**
	 * Output a form-table row.
	 *
	 * @param string $label Label text.
	 * @param string $field HTML field content (pre-escaped).
	 */
	private function form_row( string $label, string $field ): void {
		echo '<tr>';
		echo '<th scope="row"><label>' . esc_html( $label ) . '</label></th>';
		echo '<td>' . $field . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in caller
		echo '</tr>';
	}
}
