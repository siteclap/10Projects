<?php
/**
 * Migration 009: Create leads table.
 *
 * Central lead record combining customer data, requirement snapshot,
 * engagement scoring, AI-generated summaries, quality classification,
 * consent flags, and full UTM attribution.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_leads (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  lead_uuid varchar(36) NOT NULL,
  customer_id bigint(20) unsigned NOT NULL,
  requirement_id bigint(20) unsigned DEFAULT NULL,
  project_id bigint(20) unsigned DEFAULT NULL,
  lead_type varchar(30) NOT NULL,
  lead_source varchar(100) DEFAULT NULL,
  lead_medium varchar(100) DEFAULT NULL,
  lead_campaign varchar(255) DEFAULT NULL,
  customer_name varchar(255) DEFAULT NULL,
  customer_phone varchar(20) NOT NULL,
  customer_email varchar(255) DEFAULT NULL,
  customer_city varchar(100) DEFAULT NULL,
  location_preference longtext DEFAULT NULL,
  budget_comfortable bigint(20) unsigned DEFAULT NULL,
  budget_maximum bigint(20) unsigned DEFAULT NULL,
  configuration longtext DEFAULT NULL,
  funding_type varchar(20) DEFAULT NULL,
  down_payment bigint(20) unsigned DEFAULT NULL,
  emi_comfort int(10) unsigned DEFAULT NULL,
  purchase_timeline varchar(50) DEFAULT NULL,
  possession_preference varchar(50) DEFAULT NULL,
  risk_profile varchar(20) DEFAULT NULL,
  purpose varchar(20) DEFAULT NULL,
  top_matched_projects longtext DEFAULT NULL,
  engagement_score int(10) unsigned DEFAULT 0,
  conversation_summary text DEFAULT NULL,
  ai_lead_summary text DEFAULT NULL,
  questions_asked longtext DEFAULT NULL,
  site_visit_intent tinyint(1) DEFAULT 0,
  loan_required tinyint(1) DEFAULT 0,
  quality_score int(10) unsigned DEFAULT 0,
  readiness_score int(10) unsigned DEFAULT 0,
  classification varchar(30) DEFAULT 'researching',
  duplicate_of bigint(20) unsigned DEFAULT NULL,
  is_duplicate tinyint(1) DEFAULT 0,
  consent_contact tinyint(1) DEFAULT 0,
  consent_share tinyint(1) DEFAULT 0,
  consent_timestamp datetime DEFAULT NULL,
  status varchar(30) DEFAULT 'new',
  utm_source varchar(255) DEFAULT NULL,
  utm_medium varchar(255) DEFAULT NULL,
  utm_campaign varchar(255) DEFAULT NULL,
  utm_content varchar(255) DEFAULT NULL,
  utm_term varchar(255) DEFAULT NULL,
  landing_page text DEFAULT NULL,
  referrer text DEFAULT NULL,
  device_type varchar(50) DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY idx_uuid (lead_uuid),
  KEY idx_customer (customer_id),
  KEY idx_project (project_id),
  KEY idx_status (status),
  KEY idx_type (lead_type),
  KEY idx_classification (classification),
  KEY idx_quality (quality_score),
  KEY idx_created (created_at),
  KEY idx_phone (customer_phone),
  KEY idx_duplicate (duplicate_of)
) {charset_collate};";
