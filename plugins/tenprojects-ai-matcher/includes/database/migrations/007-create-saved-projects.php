<?php
/**
 * Migration 007: Create saved projects (shortlist) table.
 *
 * Allows customers to bookmark projects with optional notes and custom ordering.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_saved_projects (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  customer_id bigint(20) unsigned NOT NULL,
  project_id bigint(20) unsigned NOT NULL,
  notes text DEFAULT NULL,
  sort_order int(11) DEFAULT 0,
  saved_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY idx_customer_project (customer_id,project_id)
) {charset_collate};";
