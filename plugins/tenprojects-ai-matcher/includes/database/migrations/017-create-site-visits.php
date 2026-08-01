<?php
/**
 * Migration 017: Create site visits table.
 *
 * Manages the full site visit lifecycle from customer request through
 * partner confirmation, visit completion, and post-visit feedback.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_site_visits (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  customer_id bigint(20) unsigned NOT NULL,
  project_id bigint(20) unsigned NOT NULL,
  lead_id bigint(20) unsigned DEFAULT NULL,
  partner_id bigint(20) unsigned DEFAULT NULL,
  requested_date date DEFAULT NULL,
  requested_time_slot varchar(50) DEFAULT NULL,
  confirmed_date date DEFAULT NULL,
  confirmed_time varchar(50) DEFAULT NULL,
  status varchar(20) DEFAULT 'requested',
  customer_notes text DEFAULT NULL,
  partner_notes text DEFAULT NULL,
  feedback_rating tinyint(3) unsigned DEFAULT NULL,
  feedback_text text DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY idx_customer (customer_id),
  KEY idx_project (project_id),
  KEY idx_status (status),
  KEY idx_date (requested_date)
) {charset_collate};";
