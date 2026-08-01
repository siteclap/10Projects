<?php
/**
 * Consent service — collection, verification, audit trail.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

class Consent_Service {

    /** @var string[] Valid consent types. */
    private const TYPES = array(
        'data_collection',
        'marketing_sms',
        'marketing_email',
        'marketing_whatsapp',
        'share_with_partner',
        'terms_accepted',
    );

    /**
     * Record a consent event.
     *
     * @param int    $customer_id Customer ID.
     * @param string $type        Consent type.
     * @param string $action      'given' or 'withdrawn'.
     * @param string $ip          Client IP address.
     * @param string $context     Where consent was collected (e.g. 'otp_screen', 'settings').
     * @return int|false Consent log ID or false.
     */
    public function record( $customer_id, $type, $action, $ip = '', $context = '' ) {
        global $wpdb;

        if ( ! in_array( $type, self::TYPES, true ) ) {
            return false;
        }

        if ( ! in_array( $action, array( 'given', 'withdrawn' ), true ) ) {
            return false;
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'tp_consent_log',
            array(
                'customer_id'  => absint( $customer_id ),
                'consent_type' => $type,
                'action'       => $action,
                'ip_address'   => sanitize_text_field( $ip ),
                'user_agent'   => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
                'context'      => sanitize_text_field( $context ),
                'created_at'   => current_time( 'mysql' ),
            )
        );

        // Update customer consent flag.
        if ( $type === 'data_collection' ) {
            $wpdb->update(
                $wpdb->prefix . 'tp_customers',
                array(
                    'consent_given' => $action === 'given' ? 1 : 0,
                    'updated_at'    => current_time( 'mysql' ),
                ),
                array( 'id' => absint( $customer_id ) )
            );
        }

        return false !== $result ? $wpdb->insert_id : false;
    }

    /**
     * Record multiple consents at once (e.g., during OTP verification).
     *
     * @param int    $customer_id Customer ID.
     * @param array  $types       Consent types to grant.
     * @param string $ip          Client IP.
     * @param string $context     Context string.
     */
    public function grant_bulk( $customer_id, array $types, $ip = '', $context = '' ) {
        foreach ( $types as $type ) {
            $this->record( $customer_id, $type, 'given', $ip, $context );
        }
    }

    /**
     * Check if customer has active consent for a type.
     *
     * @param int    $customer_id Customer ID.
     * @param string $type        Consent type.
     * @return bool
     */
    public function has_consent( $customer_id, $type ) {
        global $wpdb;

        $latest = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT action FROM {$wpdb->prefix}tp_consent_log
                 WHERE customer_id = %d AND consent_type = %s
                 ORDER BY created_at DESC LIMIT 1",
                absint( $customer_id ),
                $type
            )
        );

        return $latest && $latest->action === 'given';
    }

    /**
     * Withdraw all marketing consents for a customer.
     *
     * @param int    $customer_id Customer ID.
     * @param string $ip          Client IP.
     */
    public function withdraw_all_marketing( $customer_id, $ip = '' ) {
        $marketing = array( 'marketing_sms', 'marketing_email', 'marketing_whatsapp' );

        foreach ( $marketing as $type ) {
            if ( $this->has_consent( $customer_id, $type ) ) {
                $this->record( $customer_id, $type, 'withdrawn', $ip, 'user_opt_out' );
            }
        }
    }

    /**
     * Get full consent history for a customer.
     *
     * @param int $customer_id Customer ID.
     * @return array Consent log entries.
     */
    public function get_history( $customer_id ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_consent_log
                 WHERE customer_id = %d ORDER BY created_at DESC",
                absint( $customer_id )
            )
        );
    }

    /**
     * Get current consent status for all types.
     *
     * @param int $customer_id Customer ID.
     * @return array Associative array of type => bool.
     */
    public function get_status( $customer_id ) {
        $status = array();
        foreach ( self::TYPES as $type ) {
            $status[ $type ] = $this->has_consent( $customer_id, $type );
        }
        return $status;
    }
}
