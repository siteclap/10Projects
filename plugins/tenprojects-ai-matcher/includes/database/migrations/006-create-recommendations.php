<?php
/**
 * Migration 006: Create recommendations table.
 *
 * Stores the top-10 recommendation result sets generated for each customer
 * requirement. Includes shareable token for public result pages.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_recommendations (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  customer_id bigint(20) unsigned NOT NULL,
  requirement_id bigint(20) unsigned NOT NULL,
  session_id bigint(20) unsigned DEFAULT NULL,
  projects_analysed int(10) unsigned DEFAULT NULL,
  ai_confidence decimal(3,2) DEFAULT NULL,
  results longtext DEFAULT NULL,
  requirement_summary text DEFAULT NULL,
  share_token varchar(64) DEFAULT NULL,
  viewed_at datetime DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY idx_share (share_token),
  KEY idx_customer (customer_id),
  KEY idx_requirement (requirement_id)
) {charset_collate};";
