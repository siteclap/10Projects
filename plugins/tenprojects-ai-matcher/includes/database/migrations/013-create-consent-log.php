<?php
/**
 * Migration 013: Create consent log table.
 *
 * Immutable audit trail of all customer consent actions (granted/revoked)
 * for GDPR-style compliance. Records include IP, user agent, and the
 * exact consent text shown at the time of collection.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_consent_log (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  customer_id bigint(20) unsigned NOT NULL,
  consent_type varchar(30) NOT NULL,
  granted tinyint(1) NOT NULL,
  consent_text text DEFAULT NULL,
  ip_address varchar(45) DEFAULT NULL,
  user_agent text DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY idx_customer (customer_id),
  KEY idx_type (consent_type)
) {charset_collate};";
