<?php
/**
 * Notification Service — multi-channel notification dispatch (SMS, Email, WhatsApp, In-app).
 *
 * Reuses the same SMS provider (MSG91/Twilio) configured for OTP delivery.
 * WhatsApp Business API integration is a placeholder for Phase 4+.
 * All notifications are logged in the tp_notifications table for audit trail.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

class Notification_Service {

	/**
	 * Supported notification channels.
	 *
	 * @var string[]
	 */
	private const CHANNELS = array( 'sms', 'email', 'whatsapp', 'in_app' );

	/**
	 * Message templates keyed by template name.
	 *
	 * @var array
	 */
	private const TEMPLATES = array(
		'lead_assigned'        => 'New lead from 10Projects! {customer_name} interested in {project_name}. Budget: {budget}. Contact: {phone}. Reply within 30 min.',
		'site_visit_confirmed' => 'Your site visit to {project_name} is confirmed for {date} at {time}. Address: {address}. Contact advisor: {advisor_phone}.',
		'results_ready'        => 'Your top 10 projects are ready! View your personalized recommendations: {link}',
		'lead_accepted'        => 'Good news! An expert advisor has been assigned for {project_name}. They\'ll contact you within {sla_hours} hours.',
	);

	/**
	 * Send a notification through the specified channel.
	 *
	 * Dispatches to the appropriate channel handler and logs the notification
	 * in the tp_notifications table regardless of delivery outcome.
	 *
	 * @param string $channel   Delivery channel: 'sms', 'email', 'whatsapp', or 'in_app'.
	 * @param string $recipient Phone number (sms/whatsapp), email address (email), or customer ID (in_app).
	 * @param string $template  Template key (e.g. 'lead_assigned', 'otp', 'site_visit_confirmed').
	 * @param array  $data      Template variables and additional context.
	 * @return bool True if notification was dispatched successfully.
	 */
	public function send( $channel, $recipient, $template, $data = array() ) {
		if ( ! in_array( $channel, self::CHANNELS, true ) ) {
			error_log( sprintf( '[TenProjects] Notification send failed: unsupported channel "%s".', $channel ) );
			return false;
		}

		// Render the message body from template.
		$message = $this->render_template( $template, $data );
		$subject = $data['subject'] ?? $this->default_subject( $template );

		// Dispatch to channel handler.
		$success = false;

		switch ( $channel ) {
			case 'sms':
				$success = $this->send_sms( $recipient, $message );
				break;

			case 'email':
				$success = $this->send_email( $recipient, $subject, $message );
				break;

			case 'whatsapp':
				$whatsapp_template = $data['whatsapp_template'] ?? $template;
				$whatsapp_params   = $data['whatsapp_params'] ?? $data;
				$success           = $this->send_whatsapp( $recipient, $whatsapp_template, $whatsapp_params );
				break;

			case 'in_app':
				$customer_id = is_numeric( $recipient ) ? absint( $recipient ) : 0;
				$title       = $data['title'] ?? $subject;
				$link        = $data['link'] ?? '';
				$success     = $this->send_in_app( $customer_id, $title, $message, $link );
				break;
		}

		// Log the notification to the database.
		$this->log_notification( $channel, $recipient, $template, $subject, $message, $data, $success );

		return $success;
	}

	/**
	 * Send SMS via MSG91 or Twilio.
	 *
	 * Reuses the same provider and API key configured for OTP delivery.
	 * Falls back to error_log in dev mode (WP_DEBUG + no API key).
	 *
	 * @param string $phone   Phone number (10 digits or with +91 prefix).
	 * @param string $message SMS body text.
	 * @return bool True if sent successfully.
	 */
	public function send_sms( $phone, $message ) {
		$phone   = $this->format_phone( $phone );
		$api_key = get_option( 'tp_otp_api_key', '' );

		// Dev mode: log instead of sending.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && empty( $api_key ) ) {
			error_log( sprintf(
				'[TenProjects DEV] SMS to %s: %s (dev mode — not actually sent)',
				$phone,
				$message
			) );
			return true;
		}

		$provider = get_option( 'tp_otp_provider', 'msg91' );

		switch ( $provider ) {
			case 'twilio':
				return $this->send_sms_twilio( $phone, $message );

			case 'msg91':
			default:
				return $this->send_sms_msg91( $phone, $message );
		}
	}

	/**
	 * Send email via wp_mail with a branded HTML template wrapper.
	 *
	 * @param string $to      Recipient email address.
	 * @param string $subject Email subject line.
	 * @param string $body    Email body content (plain text; wrapped in HTML template).
	 * @param array  $headers Additional email headers.
	 * @return bool True if wp_mail succeeded.
	 */
	public function send_email( $to, $subject, $body, $headers = array() ) {
		if ( ! is_email( $to ) ) {
			error_log( sprintf( '[TenProjects] Email send failed: invalid email "%s".', $to ) );
			return false;
		}

		$from_email = get_option( 'tp_notification_email_from', get_option( 'admin_email' ) );
		$from_name  = get_option( 'tp_notification_email_name', '10Projects' );

		$default_headers = array(
			'Content-Type: text/html; charset=UTF-8',
			sprintf( 'From: %s <%s>', $from_name, $from_email ),
		);

		$headers  = ! empty( $headers ) ? array_merge( $default_headers, $headers ) : $default_headers;
		$html_body = $this->wrap_email_html( $subject, $body );

		return wp_mail( $to, $subject, $html_body, $headers );
	}

	/**
	 * Send notification via WhatsApp Business API.
	 *
	 * Placeholder for Phase 4+ integration. Currently logs the intent
	 * and returns true to avoid blocking notification flows.
	 *
	 * @param string $phone         Phone number with country code.
	 * @param string $template_name WhatsApp-approved template name.
	 * @param array  $params        Template parameters for variable substitution.
	 * @return bool Always returns true (placeholder).
	 */
	public function send_whatsapp( $phone, $template_name, $params = array() ) {
		error_log( sprintf(
			'[TenProjects] WhatsApp notification queued (Phase 4 placeholder): phone=%s, template=%s, params=%s',
			$this->format_phone( $phone ),
			$template_name,
			wp_json_encode( $params )
		) );

		return true;
	}

	/**
	 * Create an in-app notification for a customer.
	 *
	 * Inserts directly into the tp_notifications table with channel='in_app'
	 * and status='unread' for real-time retrieval via the customer dashboard.
	 *
	 * @param int    $customer_id Customer ID from tp_customers.
	 * @param string $title       Short notification title.
	 * @param string $message     Notification body text.
	 * @param string $link        Optional deep link URL.
	 * @return bool True if insert succeeded.
	 */
	public function send_in_app( $customer_id, $title, $message, $link = '' ) {
		global $wpdb;

		if ( empty( $customer_id ) ) {
			error_log( '[TenProjects] In-app notification failed: no customer_id provided.' );
			return false;
		}

		$result = $wpdb->insert(
			$wpdb->prefix . 'tp_notifications',
			array(
				'recipient_type'    => 'customer',
				'recipient_id'      => absint( $customer_id ),
				'channel'           => 'in_app',
				'notification_type' => 'in_app',
				'subject'           => sanitize_text_field( $title ),
				'body'              => sanitize_textarea_field( $message ),
				'data'              => wp_json_encode( array( 'link' => esc_url_raw( $link ) ) ),
				'status'            => 'unread',
				'sent_at'           => current_time( 'mysql' ),
				'created_at'        => current_time( 'mysql' ),
			)
		);

		return false !== $result;
	}

	/**
	 * Notify a channel partner about a new lead assignment.
	 *
	 * Sends both SMS and email to the partner with lead details.
	 * Retrieves partner contact info from the tp_partners table.
	 *
	 * @param int   $partner_id Partner ID from tp_partners.
	 * @param array $lead       Lead data array with keys: customer_name, project_name, budget, phone.
	 * @return bool True if at least one channel succeeded.
	 */
	public function notify_partner_new_lead( $partner_id, $lead ) {
		global $wpdb;

		$partner = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT phone, email, contact_person, company_name
				 FROM {$wpdb->prefix}tp_partners
				 WHERE id = %d AND is_active = 1",
				absint( $partner_id )
			)
		);

		if ( ! $partner ) {
			error_log( sprintf( '[TenProjects] Partner notification failed: partner #%d not found or inactive.', $partner_id ) );
			return false;
		}

		$data = array(
			'customer_name' => $lead['customer_name'] ?? 'Customer',
			'project_name'  => $lead['project_name'] ?? 'N/A',
			'budget'        => $lead['budget'] ?? 'N/A',
			'phone'         => $lead['phone'] ?? 'N/A',
		);

		$sms_sent   = false;
		$email_sent = false;

		// Send SMS to partner.
		if ( ! empty( $partner->phone ) ) {
			$sms_sent = $this->send( 'sms', $partner->phone, 'lead_assigned', $data );
		}

		// Send email to partner.
		if ( ! empty( $partner->email ) ) {
			$email_data = array_merge( $data, array(
				'subject' => sprintf( 'New Lead Assignment — %s | 10Projects', $data['project_name'] ),
			) );
			$email_sent = $this->send( 'email', $partner->email, 'lead_assigned', $email_data );
		}

		return $sms_sent || $email_sent;
	}

	/**
	 * Notify a customer that their project recommendations are ready.
	 *
	 * Sends an in-app notification with a link to view personalized results.
	 *
	 * @param int    $customer_id Customer ID from tp_customers.
	 * @param string $share_token Share token for the recommendation results page.
	 * @return bool True if notification was sent.
	 */
	public function notify_customer_results_ready( $customer_id, $share_token ) {
		$link = home_url( '/my-results/?token=' . rawurlencode( $share_token ) );

		$data = array(
			'title' => 'Your Top 10 Projects Are Ready!',
			'link'  => $link,
		);

		return $this->send( 'in_app', (string) $customer_id, 'results_ready', $data );
	}

	/**
	 * Notify a customer about a confirmed site visit.
	 *
	 * Sends both SMS and in-app notification with visit details.
	 *
	 * @param int   $customer_id Customer ID from tp_customers.
	 * @param array $visit_data  Visit details: project_name, date, time, address, advisor_phone.
	 * @return bool True if at least one channel succeeded.
	 */
	public function notify_site_visit_confirmed( $customer_id, $visit_data ) {
		global $wpdb;

		$data = array(
			'project_name' => $visit_data['project_name'] ?? 'N/A',
			'date'         => $visit_data['date'] ?? 'N/A',
			'time'         => $visit_data['time'] ?? 'N/A',
			'address'      => $visit_data['address'] ?? 'N/A',
			'advisor_phone' => $visit_data['advisor_phone'] ?? 'N/A',
			'title'        => 'Site Visit Confirmed',
			'link'         => $visit_data['link'] ?? '',
		);

		$sms_sent    = false;
		$in_app_sent = false;

		// Get customer phone for SMS.
		$customer = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT phone FROM {$wpdb->prefix}tp_customers
				 WHERE id = %d AND deleted_at IS NULL",
				absint( $customer_id )
			)
		);

		// Send SMS.
		if ( $customer && ! empty( $customer->phone ) ) {
			$sms_sent = $this->send( 'sms', $customer->phone, 'site_visit_confirmed', $data );
		}

		// Send in-app.
		$in_app_sent = $this->send( 'in_app', (string) $customer_id, 'site_visit_confirmed', $data );

		return $sms_sent || $in_app_sent;
	}

	/**
	 * Get all unread in-app notifications for a customer.
	 *
	 * @param int $customer_id Customer ID.
	 * @return array Array of notification objects ordered by most recent first.
	 */
	public function get_unread( $customer_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, subject, body, data, created_at
				 FROM {$wpdb->prefix}tp_notifications
				 WHERE recipient_type = 'customer'
				   AND recipient_id = %d
				   AND channel = 'in_app'
				   AND status = 'unread'
				 ORDER BY created_at DESC",
				absint( $customer_id )
			)
		);
	}

	/**
	 * Mark an in-app notification as read.
	 *
	 * @param int $notification_id Notification ID from tp_notifications.
	 * @return bool True if update succeeded.
	 */
	public function mark_read( $notification_id ) {
		global $wpdb;

		return false !== $wpdb->update(
			$wpdb->prefix . 'tp_notifications',
			array(
				'status'  => 'read',
				'read_at' => current_time( 'mysql' ),
			),
			array( 'id' => absint( $notification_id ) )
		);
	}

	/**
	 * Get a message template string by key.
	 *
	 * Returns the raw template with {placeholder} tokens for variable substitution.
	 * Returns an empty string for unknown template keys.
	 *
	 * @param string $template_key Template identifier (e.g. 'lead_assigned').
	 * @return string Template string or empty string if not found.
	 */
	public function get_template( $template_key ) {
		return self::TEMPLATES[ $template_key ] ?? '';
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Send SMS via MSG91 transactional API.
	 *
	 * Uses the MSG91 Send SMS endpoint (not OTP endpoint) for general
	 * notification messages with template-based delivery.
	 *
	 * @param string $phone   Phone number with +91 prefix.
	 * @param string $message SMS body text.
	 * @return bool True if API call succeeded.
	 */
	private function send_sms_msg91( $phone, $message ) {
		$api_key     = get_option( 'tp_otp_api_key', '' );
		$template_id = get_option( 'tp_msg91_template_id', '' );

		if ( empty( $api_key ) || empty( $template_id ) ) {
			error_log( '[TenProjects] MSG91 SMS send failed: API key or template ID not configured.' );
			return false;
		}

		$response = wp_remote_post(
			'https://control.msg91.com/api/v5/flow/',
			array(
				'timeout' => 15,
				'headers' => array(
					'authkey'      => $api_key,
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				'body'    => wp_json_encode( array(
					'template_id' => $template_id,
					'recipients'  => array(
						array(
							'mobiles' => $phone,
							'message' => $message,
						),
					),
				) ),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( '[TenProjects] MSG91 SMS send error: ' . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status_code < 200 || $status_code >= 300 ) {
			error_log( sprintf(
				'[TenProjects] MSG91 SMS send failed: HTTP %d — %s',
				$status_code,
				wp_json_encode( $body )
			) );
			return false;
		}

		if ( isset( $body['type'] ) && 'success' === $body['type'] ) {
			return true;
		}

		error_log( '[TenProjects] MSG91 SMS unexpected response: ' . wp_json_encode( $body ) );
		return false;
	}

	/**
	 * Send SMS via Twilio Messages API.
	 *
	 * @param string $phone   Phone number with +91 prefix.
	 * @param string $message SMS body text.
	 * @return bool True if API call succeeded.
	 */
	private function send_sms_twilio( $phone, $message ) {
		$account_sid = get_option( 'tp_twilio_sid', '' );
		$auth_token  = get_option( 'tp_otp_api_key', '' );
		$from_number = get_option( 'tp_twilio_from', '' );

		if ( empty( $account_sid ) || empty( $auth_token ) || empty( $from_number ) ) {
			error_log( '[TenProjects] Twilio SMS send failed: Account SID, auth token, or From number not configured.' );
			return false;
		}

		$url = sprintf(
			'https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json',
			rawurlencode( $account_sid )
		);

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 15,
				'headers' => array(
					// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'Authorization' => 'Basic ' . base64_encode( $account_sid . ':' . $auth_token ),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'To'   => $phone,
					'From' => $from_number,
					'Body' => $message,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( '[TenProjects] Twilio SMS send error: ' . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status_code < 200 || $status_code >= 300 ) {
			error_log( sprintf(
				'[TenProjects] Twilio SMS send failed: HTTP %d — %s',
				$status_code,
				$body['message'] ?? wp_json_encode( $body )
			) );
			return false;
		}

		return ! empty( $body['sid'] );
	}

	/**
	 * Render a template by replacing {placeholder} tokens with data values.
	 *
	 * @param string $template_key Template key or raw template string.
	 * @param array  $data         Key-value pairs for placeholder substitution.
	 * @return string Rendered message.
	 */
	private function render_template( $template_key, array $data ) {
		$template = $this->get_template( $template_key );

		// If no template found by key, use the key itself as a raw message.
		if ( empty( $template ) ) {
			$template = $template_key;
		}

		// Replace {placeholder} tokens with data values.
		foreach ( $data as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$template = str_replace( '{' . $key . '}', (string) $value, $template );
			}
		}

		return $template;
	}

	/**
	 * Generate a default subject line for a template.
	 *
	 * @param string $template_key Template identifier.
	 * @return string Subject line.
	 */
	private function default_subject( $template_key ) {
		$subjects = array(
			'lead_assigned'        => 'New Lead Assignment — 10Projects',
			'site_visit_confirmed' => 'Site Visit Confirmed — 10Projects',
			'results_ready'        => 'Your Top 10 Projects Are Ready!',
			'lead_accepted'        => 'Advisor Assigned — 10Projects',
		);

		return $subjects[ $template_key ] ?? '10Projects Notification';
	}

	/**
	 * Wrap plain-text email body in a branded HTML template.
	 *
	 * @param string $subject Email subject (used as heading).
	 * @param string $body    Plain-text body content.
	 * @return string Complete HTML email.
	 */
	private function wrap_email_html( $subject, $body ) {
		$body_html = nl2br( esc_html( $body ) );

		return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . esc_html( $subject ) . '</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f7;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f4f7;">
<tr><td align="center" style="padding:40px 20px;">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;">
<!-- Header -->
<tr><td style="background-color:#1e40af;padding:24px 32px;">
<h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:600;">10Projects</h1>
</td></tr>
<!-- Body -->
<tr><td style="padding:32px;">
<h2 style="margin:0 0 16px;color:#1a1a2e;font-size:18px;">' . esc_html( $subject ) . '</h2>
<div style="color:#4a4a68;font-size:15px;line-height:1.6;">' . $body_html . '</div>
</td></tr>
<!-- Footer -->
<tr><td style="padding:24px 32px;background-color:#f8f9fa;border-top:1px solid #e5e7eb;">
<p style="margin:0;color:#9ca3af;font-size:12px;text-align:center;">
&copy; ' . gmdate( 'Y' ) . ' 10Projects.com &mdash; AI-Powered Real Estate Discovery
</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
	}

	/**
	 * Log a notification to the tp_notifications table.
	 *
	 * @param string $channel   Delivery channel.
	 * @param string $recipient Recipient identifier.
	 * @param string $template  Template key.
	 * @param string $subject   Subject line.
	 * @param string $body      Rendered message body.
	 * @param array  $data      Original data payload.
	 * @param bool   $success   Whether the delivery succeeded.
	 */
	private function log_notification( $channel, $recipient, $template, $subject, $body, $data, $success ) {
		global $wpdb;

		// Determine recipient type and ID.
		$recipient_type = 'unknown';
		$recipient_id   = 0;

		if ( 'in_app' === $channel ) {
			// In-app notifications already logged by send_in_app; skip double-logging.
			return;
		}

		// Try to resolve recipient to a known entity.
		if ( is_email( $recipient ) ) {
			$recipient_type = 'email';
		} elseif ( is_numeric( $recipient ) ) {
			$recipient_type = 'customer';
			$recipient_id   = absint( $recipient );
		} else {
			$recipient_type = 'phone';
		}

		$wpdb->insert(
			$wpdb->prefix . 'tp_notifications',
			array(
				'recipient_type'    => $recipient_type,
				'recipient_id'      => $recipient_id,
				'channel'           => $channel,
				'notification_type' => sanitize_text_field( $template ),
				'subject'           => sanitize_text_field( $subject ),
				'body'              => sanitize_textarea_field( $body ),
				'data'              => wp_json_encode( array(
					'recipient' => $recipient,
					'template'  => $template,
					'params'    => $data,
				) ),
				'status'            => $success ? 'sent' : 'failed',
				'sent_at'           => $success ? current_time( 'mysql' ) : null,
				'created_at'        => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Format phone number with +91 country code prefix.
	 *
	 * @param string $phone Phone number (10 digits or already prefixed).
	 * @return string Phone number with +91 prefix.
	 */
	private function format_phone( $phone ) {
		$digits = preg_replace( '/\D/', '', $phone );

		// Already 12 digits starting with 91.
		if ( strlen( $digits ) === 12 && str_starts_with( $digits, '91' ) ) {
			return '+' . $digits;
		}

		// 10-digit Indian number.
		if ( strlen( $digits ) === 10 ) {
			return '+91' . $digits;
		}

		return '+' . $digits;
	}
}
