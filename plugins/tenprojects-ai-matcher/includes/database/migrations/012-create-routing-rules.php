<?php
/**
 * Migration 012: Create lead routing rules table.
 *
 * Defines configurable rules for automatic lead distribution to partners.
 * Rules include location, budget, and configuration conditions, plus
 * daily/monthly volume caps with automatic reset tracking.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_lead_routing_rules (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  partner_id bigint(20) unsigned NOT NULL,
  rule_name varchar(255) DEFAULT NULL,
  priority int(11) DEFAULT 0,
  is_active tinyint(1) DEFAULT 1,
  conditions longtext NOT NULL,
  max_leads_per_day int(11) DEFAULT 0,
  max_leads_per_month int(11) DEFAULT 0,
  current_daily_count int(11) DEFAULT 0,
  current_monthly_count int(11) DEFAULT 0,
  last_reset_daily date DEFAULT NULL,
  last_reset_monthly date DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY idx_partner (partner_id),
  KEY idx_active (is_active),
  KEY idx_priority (priority)
) {charset_collate};";
