<?php
/**
 * Migration 003: Create customer requirements table.
 *
 * Stores the full buyer requirement profile gathered during AI assessment.
 * Each customer can have multiple requirement sets (e.g., for different searches).
 * JSON-encoded fields use longtext for WordPress/MySQL compatibility.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_customer_requirements (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  customer_id bigint(20) unsigned NOT NULL,
  label varchar(255) DEFAULT 'My Requirements',
  is_active tinyint(1) DEFAULT 1,
  purpose varchar(20) DEFAULT NULL,
  property_type varchar(50) DEFAULT NULL,
  configuration longtext DEFAULT NULL,
  config_flexible tinyint(1) DEFAULT 0,
  city varchar(100) DEFAULT NULL,
  construction_stage longtext DEFAULT NULL,
  preferred_locations longtext DEFAULT NULL,
  alternative_locations longtext DEFAULT NULL,
  location_flexible tinyint(1) DEFAULT 0,
  workplace_location varchar(255) DEFAULT NULL,
  max_commute_minutes int(11) DEFAULT NULL,
  commute_mode varchar(50) DEFAULT NULL,
  proximity_requirements longtext DEFAULT NULL,
  budget_comfortable bigint(20) unsigned DEFAULT NULL,
  budget_maximum bigint(20) unsigned DEFAULT NULL,
  funding_type varchar(20) DEFAULT NULL,
  down_payment bigint(20) unsigned DEFAULT NULL,
  monthly_emi_comfort int(10) unsigned DEFAULT NULL,
  loan_preapproved varchar(20) DEFAULT NULL,
  existing_emi int(10) unsigned DEFAULT 0,
  payment_plan_preference varchar(50) DEFAULT NULL,
  purchase_timeline_months int(11) DEFAULT NULL,
  possession_preference varchar(50) DEFAULT NULL,
  max_possession_year int(11) DEFAULT NULL,
  family_adults tinyint(3) unsigned DEFAULT NULL,
  family_children tinyint(3) unsigned DEFAULT NULL,
  children_ages longtext DEFAULT NULL,
  family_seniors tinyint(3) unsigned DEFAULT NULL,
  pets varchar(50) DEFAULT NULL,
  risk_tolerance varchar(20) DEFAULT 'balanced',
  developer_preference varchar(50) DEFAULT NULL,
  specific_developer varchar(255) DEFAULT NULL,
  priorities longtext DEFAULT NULL,
  deal_breakers longtext DEFAULT NULL,
  carpet_area_min int(10) unsigned DEFAULT NULL,
  carpet_area_max int(10) unsigned DEFAULT NULL,
  amenity_preferences longtext DEFAULT NULL,
  additional_notes text DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY idx_customer (customer_id),
  KEY idx_active (is_active)
) {charset_collate};";
