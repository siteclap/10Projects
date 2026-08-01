<?php
/**
 * Migration 014: Create analytics events table.
 *
 * Stores granular frontend and backend events for conversion funnel
 * analysis, engagement tracking, and attribution. Each event carries
 * full session, visitor, device, and UTM context.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_analytics_events (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  event_name varchar(100) NOT NULL,
  session_id varchar(64) DEFAULT NULL,
  customer_id bigint(20) unsigned DEFAULT NULL,
  visitor_id varchar(64) DEFAULT NULL,
  page_url text DEFAULT NULL,
  referrer text DEFAULT NULL,
  event_data longtext DEFAULT NULL,
  utm_source varchar(255) DEFAULT NULL,
  utm_medium varchar(255) DEFAULT NULL,
  utm_campaign varchar(255) DEFAULT NULL,
  device_type varchar(50) DEFAULT NULL,
  browser varchar(100) DEFAULT NULL,
  ip_address varchar(45) DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY idx_event (event_name),
  KEY idx_session (session_id),
  KEY idx_customer (customer_id),
  KEY idx_visitor (visitor_id),
  KEY idx_created (created_at)
) {charset_collate};";
