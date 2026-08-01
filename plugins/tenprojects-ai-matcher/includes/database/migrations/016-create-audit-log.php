<?php
/**
 * Migration 016: Create audit log table.
 *
 * Immutable record of all significant data changes across the platform.
 * Stores old and new values as JSON for full change tracking, with
 * user identification, IP, and user agent for accountability.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_audit_log (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(20) unsigned DEFAULT NULL,
  user_type varchar(20) NOT NULL,
  action varchar(100) NOT NULL,
  entity_type varchar(50) DEFAULT NULL,
  entity_id bigint(20) unsigned DEFAULT NULL,
  old_values longtext DEFAULT NULL,
  new_values longtext DEFAULT NULL,
  ip_address varchar(45) DEFAULT NULL,
  user_agent text DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY idx_user (user_id,user_type),
  KEY idx_action (action),
  KEY idx_entity (entity_type,entity_id),
  KEY idx_created (created_at)
) {charset_collate};";
