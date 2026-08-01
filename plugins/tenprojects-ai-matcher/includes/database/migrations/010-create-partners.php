<?php
/**
 * Migration 010: Create partners (channel partners) table.
 *
 * Stores channel partner profiles, subscription details, lead preferences,
 * capacity limits, performance metrics, and business hours.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_partners (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  wp_user_id bigint(20) unsigned DEFAULT NULL,
  company_name varchar(255) NOT NULL,
  contact_person varchar(255) DEFAULT NULL,
  phone varchar(20) NOT NULL,
  email varchar(255) NOT NULL,
  partner_type varchar(30) NOT NULL,
  rera_number varchar(100) DEFAULT NULL,
  cities longtext DEFAULT NULL,
  locations longtext DEFAULT NULL,
  projects longtext DEFAULT NULL,
  budget_range_min bigint(20) unsigned DEFAULT NULL,
  budget_range_max bigint(20) unsigned DEFAULT NULL,
  configurations longtext DEFAULT NULL,
  lead_types longtext DEFAULT NULL,
  subscription_plan varchar(50) DEFAULT NULL,
  lead_credits int(11) DEFAULT 0,
  monthly_lead_limit int(11) DEFAULT 0,
  daily_lead_limit int(11) DEFAULT 0,
  exclusivity tinyint(1) DEFAULT 0,
  response_sla_hours int(11) DEFAULT 24,
  is_active tinyint(1) DEFAULT 1,
  rating decimal(3,2) DEFAULT 0.00,
  total_leads_received int(11) DEFAULT 0,
  total_leads_converted int(11) DEFAULT 0,
  conversion_rate decimal(5,2) DEFAULT 0.00,
  avg_response_minutes int(11) DEFAULT 0,
  business_hours_start time DEFAULT '09:00:00',
  business_hours_end time DEFAULT '21:00:00',
  business_days longtext DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY idx_type (partner_type),
  KEY idx_active (is_active),
  KEY idx_wp_user (wp_user_id)
) {charset_collate};";
