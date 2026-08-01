<?php
/**
 * Migration 005: Create project scores table.
 *
 * Stores the 20-category Fit Score breakdown for each project matched
 * against a customer requirement. Includes weight profile used, ranking,
 * match reasons, and trade-off explanations.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_project_scores (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  requirement_id bigint(20) unsigned NOT NULL,
  project_id bigint(20) unsigned NOT NULL,
  config_id bigint(20) unsigned DEFAULT NULL,
  total_fit_score int(10) unsigned NOT NULL,
  budget_fit int(10) unsigned DEFAULT 0,
  location_fit int(10) unsigned DEFAULT 0,
  config_fit int(10) unsigned DEFAULT 0,
  carpet_area_fit int(10) unsigned DEFAULT 0,
  possession_fit int(10) unsigned DEFAULT 0,
  emi_fit int(10) unsigned DEFAULT 0,
  commute_fit int(10) unsigned DEFAULT 0,
  lifestyle_fit int(10) unsigned DEFAULT 0,
  developer_reliability int(10) unsigned DEFAULT 0,
  construction_stage_fit int(10) unsigned DEFAULT 0,
  legal_confidence int(10) unsigned DEFAULT 0,
  resale_liquidity int(10) unsigned DEFAULT 0,
  rental_potential int(10) unsigned DEFAULT 0,
  appreciation_drivers int(10) unsigned DEFAULT 0,
  risk_compatibility int(10) unsigned DEFAULT 0,
  infrastructure_potential int(10) unsigned DEFAULT 0,
  family_suitability int(10) unsigned DEFAULT 0,
  urgency_match int(10) unsigned DEFAULT 0,
  inventory_availability int(10) unsigned DEFAULT 0,
  proximity_score int(10) unsigned DEFAULT 0,
  weight_profile varchar(50) DEFAULT NULL,
  weights_used longtext DEFAULT NULL,
  ranking int(10) unsigned DEFAULT NULL,
  match_reasons longtext DEFAULT NULL,
  trade_offs longtext DEFAULT NULL,
  ai_explanation text DEFAULT NULL,
  calculated_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY idx_requirement (requirement_id),
  KEY idx_project (project_id),
  KEY idx_score (total_fit_score),
  KEY idx_ranking (requirement_id,ranking)
) {charset_collate};";
