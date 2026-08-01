<?php
/**
 * Migration 008: Create comparisons table.
 *
 * Stores side-by-side project comparison sets with computed comparison data
 * and a shareable token for public comparison pages.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_comparisons (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  customer_id bigint(20) unsigned DEFAULT NULL,
  project_ids longtext NOT NULL,
  comparison_data longtext DEFAULT NULL,
  share_token varchar(64) DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY idx_share (share_token),
  KEY idx_customer (customer_id)
) {charset_collate};";
