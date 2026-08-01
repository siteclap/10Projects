<?php
/**
 * Migration 015: Create notifications table.
 *
 * Multi-channel notification queue for customers, partners, and admins.
 * Supports email, SMS, WhatsApp, and in-app channels with delivery
 * and read tracking.
 *
 * @package TenProjects\Database
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

return "CREATE TABLE {prefix}tp_notifications (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  recipient_type varchar(20) NOT NULL,
  recipient_id bigint(20) unsigned NOT NULL,
  channel varchar(20) NOT NULL,
  notification_type varchar(100) NOT NULL,
  subject varchar(255) DEFAULT NULL,
  body text DEFAULT NULL,
  data longtext DEFAULT NULL,
  status varchar(20) DEFAULT 'pending',
  sent_at datetime DEFAULT NULL,
  read_at datetime DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY idx_recipient (recipient_type,recipient_id),
  KEY idx_status (status),
  KEY idx_type (notification_type),
  KEY idx_created (created_at)
) {charset_collate};";
