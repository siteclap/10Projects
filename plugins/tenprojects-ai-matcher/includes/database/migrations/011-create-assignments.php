<?php
/**
 * Migration 011: Create lead assignments table.
 *
 * Tracks which leads have been assigned to which partners, including
 * the full lifecycle from sent through contact, site visit, and booking.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_lead_assignments (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  lead_id bigint(20) unsigned NOT NULL,
  partner_id bigint(20) unsigned NOT NULL,
  assignment_type varchar(20) DEFAULT 'shared',
  credits_charged int(11) DEFAULT 1,
  status varchar(30) DEFAULT 'sent',
  sent_at datetime DEFAULT CURRENT_TIMESTAMP,
  opened_at datetime DEFAULT NULL,
  accepted_at datetime DEFAULT NULL,
  first_contact_at datetime DEFAULT NULL,
  contact_attempts int(11) DEFAULT 0,
  site_visit_at datetime DEFAULT NULL,
  booking_at datetime DEFAULT NULL,
  rejection_reason text DEFAULT NULL,
  dispute_reason text DEFAULT NULL,
  refund_eligible tinyint(1) DEFAULT 0,
  partner_notes text DEFAULT NULL,
  response_time_minutes int(11) DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY idx_lead (lead_id),
  KEY idx_partner (partner_id),
  KEY idx_status (status),
  KEY idx_sent (sent_at)
) {charset_collate};";
