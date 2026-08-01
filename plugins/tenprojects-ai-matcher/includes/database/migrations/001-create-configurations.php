<?php
/**
 * Migration 001: Create project configurations table.
 *
 * Stores unit-level configuration data (BHK types, areas, prices) for each project.
 * A single project post (CPT) can have multiple configurations.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_project_configurations (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  project_id bigint(20) unsigned NOT NULL,
  configuration varchar(20) NOT NULL,
  carpet_area_min decimal(10,2) DEFAULT NULL,
  carpet_area_max decimal(10,2) DEFAULT NULL,
  built_up_area_min decimal(10,2) DEFAULT NULL,
  built_up_area_max decimal(10,2) DEFAULT NULL,
  price_min bigint(20) unsigned DEFAULT NULL,
  price_max bigint(20) unsigned DEFAULT NULL,
  price_per_sqft int(10) unsigned DEFAULT NULL,
  floor_availability varchar(255) DEFAULT NULL,
  inventory_status varchar(20) DEFAULT 'available',
  unit_count int(10) unsigned DEFAULT NULL,
  views_available varchar(255) DEFAULT NULL,
  facing varchar(255) DEFAULT NULL,
  balconies tinyint(3) unsigned DEFAULT 0,
  bathrooms tinyint(3) unsigned DEFAULT 1,
  parking_included tinyint(3) unsigned DEFAULT 1,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY idx_project (project_id),
  KEY idx_config (configuration),
  KEY idx_price (price_min,price_max),
  KEY idx_area (carpet_area_min,carpet_area_max),
  KEY idx_status (inventory_status)
) {charset_collate};";
