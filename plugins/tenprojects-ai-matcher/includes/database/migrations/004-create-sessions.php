<?php
/**
 * Migration 004: Create AI assessment sessions table.
 *
 * Tracks each AI-guided assessment conversation including phase progress,
 * answers, token usage, and the resulting customer profile JSON.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_ai_sessions (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  session_uuid varchar(36) NOT NULL,
  customer_id bigint(20) unsigned DEFAULT NULL,
  requirement_id bigint(20) unsigned DEFAULT NULL,
  status varchar(20) DEFAULT 'in_progress',
  current_phase tinyint(3) unsigned DEFAULT 1,
  completion_rate decimal(3,2) DEFAULT 0.00,
  answers longtext DEFAULT NULL,
  ai_provider varchar(50) DEFAULT NULL,
  ai_model varchar(100) DEFAULT NULL,
  conversation_log longtext DEFAULT NULL,
  customer_profile_json longtext DEFAULT NULL,
  total_tokens_used int(10) unsigned DEFAULT 0,
  started_at datetime DEFAULT CURRENT_TIMESTAMP,
  completed_at datetime DEFAULT NULL,
  abandoned_at datetime DEFAULT NULL,
  ip_address varchar(45) DEFAULT NULL,
  user_agent text DEFAULT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY idx_uuid (session_uuid),
  KEY idx_customer (customer_id),
  KEY idx_status (status),
  KEY idx_started (started_at)
) {charset_collate};";
