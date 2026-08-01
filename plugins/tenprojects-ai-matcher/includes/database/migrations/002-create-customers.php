<?php
/**
 * Migration 002: Create customers table.
 *
 * Stores customer profiles, authentication state, consent flags,
 * and first-touch attribution data.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_customers (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  wp_user_id bigint(20) unsigned DEFAULT NULL,
  name varchar(255) DEFAULT NULL,
  phone varchar(20) NOT NULL,
  phone_verified tinyint(1) DEFAULT 0,
  email varchar(255) DEFAULT NULL,
  email_verified tinyint(1) DEFAULT 0,
  city varchar(100) DEFAULT NULL,
  auth_method varchar(20) DEFAULT 'otp',
  login_token varchar(255) DEFAULT NULL,
  token_expires_at datetime DEFAULT NULL,
  profile_data longtext DEFAULT NULL,
  consent_lead_sharing tinyint(1) DEFAULT 0,
  consent_marketing tinyint(1) DEFAULT 0,
  consent_whatsapp tinyint(1) DEFAULT 0,
  consent_collected_at datetime DEFAULT NULL,
  utm_source varchar(255) DEFAULT NULL,
  utm_medium varchar(255) DEFAULT NULL,
  utm_campaign varchar(255) DEFAULT NULL,
  utm_content varchar(255) DEFAULT NULL,
  utm_term varchar(255) DEFAULT NULL,
  first_visit_url text DEFAULT NULL,
  referrer text DEFAULT NULL,
  device_type varchar(50) DEFAULT NULL,
  ip_address varchar(45) DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at datetime DEFAULT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY idx_phone (phone),
  KEY idx_wp_user (wp_user_id),
  KEY idx_email (email),
  KEY idx_created (created_at)
) {charset_collate};";
