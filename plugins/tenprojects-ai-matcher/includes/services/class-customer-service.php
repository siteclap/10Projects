<?php
/**
 * Customer service — CRUD, cookie tracking, profile management.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

use TenProjects\Helpers\Sanitizer;
use TenProjects\Helpers\Validator;

class Customer_Service {

    /** @var string Cookie name for anonymous visitor tracking. */
    private const VISITOR_COOKIE = 'tp_visitor_id';

    /** @var int Cookie lifetime in seconds (1 year). */
    private const COOKIE_LIFETIME = 31536000;

    /**
     * Create a new customer record.
     *
     * @param array $data Customer data.
     * @return int|false Customer ID or false on failure.
     */
    public function create( array $data ) {
        global $wpdb;

        $table = $wpdb->prefix . 'tp_customers';

        $insert = array(
            'uuid'         => wp_generate_uuid4(),
            'phone'        => isset( $data['phone'] ) ? Sanitizer::phone( $data['phone'] ) : null,
            'phone_verified' => 0,
            'email'        => isset( $data['email'] ) ? Sanitizer::email( $data['email'] ) : null,
            'full_name'    => isset( $data['full_name'] ) ? Sanitizer::text( $data['full_name'] ) : null,
            'visitor_id'   => $data['visitor_id'] ?? $this->get_or_create_visitor_id(),
            'utm_source'   => Sanitizer::text( $data['utm_source'] ?? '' ),
            'utm_medium'   => Sanitizer::text( $data['utm_medium'] ?? '' ),
            'utm_campaign' => Sanitizer::text( $data['utm_campaign'] ?? '' ),
            'utm_content'  => Sanitizer::text( $data['utm_content'] ?? '' ),
            'utm_term'     => Sanitizer::text( $data['utm_term'] ?? '' ),
            'landing_page' => Sanitizer::url( $data['landing_page'] ?? '' ),
            'referrer'     => Sanitizer::url( $data['referrer'] ?? '' ),
            'consent_given' => ! empty( $data['consent_given'] ) ? 1 : 0,
            'created_at'   => current_time( 'mysql' ),
            'updated_at'   => current_time( 'mysql' ),
        );

        $result = $wpdb->insert( $table, $insert );

        if ( false === $result ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Find customer by phone number.
     *
     * @param string $phone Phone number.
     * @return object|null Customer row or null.
     */
    public function find_by_phone( $phone ) {
        global $wpdb;

        $clean = Sanitizer::phone( $phone );
        if ( empty( $clean ) ) {
            return null;
        }

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_customers
                 WHERE phone = %s AND deleted_at IS NULL",
                $clean
            )
        );
    }

    /**
     * Find customer by email.
     *
     * @param string $email Email.
     * @return object|null
     */
    public function find_by_email( $email ) {
        global $wpdb;

        $clean = Sanitizer::email( $email );
        if ( empty( $clean ) ) {
            return null;
        }

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_customers
                 WHERE email = %s AND deleted_at IS NULL",
                $clean
            )
        );
    }

    /**
     * Find customer by visitor ID (anonymous cookie).
     *
     * @param string $visitor_id Visitor ID.
     * @return object|null
     */
    public function find_by_visitor_id( $visitor_id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_customers
                 WHERE visitor_id = %s AND deleted_at IS NULL",
                sanitize_text_field( $visitor_id )
            )
        );
    }

    /**
     * Find customer by ID.
     *
     * @param int $id Customer ID.
     * @return object|null
     */
    public function find( $id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_customers
                 WHERE id = %d AND deleted_at IS NULL",
                absint( $id )
            )
        );
    }

    /**
     * Find customer by UUID.
     *
     * @param string $uuid UUID.
     * @return object|null
     */
    public function find_by_uuid( $uuid ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_customers
                 WHERE uuid = %s AND deleted_at IS NULL",
                sanitize_text_field( $uuid )
            )
        );
    }

    /**
     * Update customer data.
     *
     * @param int   $id   Customer ID.
     * @param array $data Fields to update.
     * @return bool Success.
     */
    public function update( $id, array $data ) {
        global $wpdb;

        $allowed = array(
            'phone', 'email', 'full_name', 'phone_verified',
            'wp_user_id', 'login_token', 'token_expires_at',
            'consent_given', 'preferred_contact', 'preferred_time',
        );

        $update = array( 'updated_at' => current_time( 'mysql' ) );

        foreach ( $allowed as $field ) {
            if ( array_key_exists( $field, $data ) ) {
                $update[ $field ] = $data[ $field ];
            }
        }

        return false !== $wpdb->update(
            $wpdb->prefix . 'tp_customers',
            $update,
            array( 'id' => absint( $id ) )
        );
    }

    /**
     * Soft-delete customer.
     *
     * @param int $id Customer ID.
     * @return bool
     */
    public function delete( $id ) {
        global $wpdb;

        return false !== $wpdb->update(
            $wpdb->prefix . 'tp_customers',
            array( 'deleted_at' => current_time( 'mysql' ) ),
            array( 'id' => absint( $id ) )
        );
    }

    /**
     * Find or create customer from visitor context.
     * If phone is provided, looks up by phone first. Otherwise uses visitor_id cookie.
     *
     * @param array $data Customer data (phone, email, visitor_id, UTM params).
     * @return object Customer record.
     */
    public function find_or_create( array $data ) {
        // Try phone first.
        if ( ! empty( $data['phone'] ) ) {
            $customer = $this->find_by_phone( $data['phone'] );
            if ( $customer ) {
                return $customer;
            }
        }

        // Try email.
        if ( ! empty( $data['email'] ) ) {
            $customer = $this->find_by_email( $data['email'] );
            if ( $customer ) {
                return $customer;
            }
        }

        // Try visitor ID.
        $visitor_id = $data['visitor_id'] ?? $this->get_or_create_visitor_id();
        $customer   = $this->find_by_visitor_id( $visitor_id );

        if ( $customer ) {
            // Merge new data into existing anonymous record.
            $merge = array();
            if ( ! empty( $data['phone'] ) && empty( $customer->phone ) ) {
                $merge['phone'] = Sanitizer::phone( $data['phone'] );
            }
            if ( ! empty( $data['email'] ) && empty( $customer->email ) ) {
                $merge['email'] = Sanitizer::email( $data['email'] );
            }
            if ( ! empty( $data['full_name'] ) && empty( $customer->full_name ) ) {
                $merge['full_name'] = Sanitizer::text( $data['full_name'] );
            }
            if ( ! empty( $merge ) ) {
                $this->update( $customer->id, $merge );
                $customer = $this->find( $customer->id );
            }
            return $customer;
        }

        // Create new record.
        $data['visitor_id'] = $visitor_id;
        $id = $this->create( $data );

        return $this->find( $id );
    }

    /**
     * Merge an anonymous customer into a verified one (after OTP).
     *
     * @param int $anonymous_id Anonymous customer ID.
     * @param int $verified_id  Verified customer ID.
     */
    public function merge( $anonymous_id, $verified_id ) {
        global $wpdb;
        $prefix = $wpdb->prefix;

        // Move sessions.
        $wpdb->update(
            $prefix . 'tp_ai_sessions',
            array( 'customer_id' => $verified_id ),
            array( 'customer_id' => $anonymous_id )
        );

        // Move requirements.
        $wpdb->update(
            $prefix . 'tp_customer_requirements',
            array( 'customer_id' => $verified_id ),
            array( 'customer_id' => $anonymous_id )
        );

        // Move saved projects.
        $wpdb->update(
            $prefix . 'tp_saved_projects',
            array( 'customer_id' => $verified_id ),
            array( 'customer_id' => $anonymous_id )
        );

        // Move comparisons.
        $wpdb->update(
            $prefix . 'tp_comparisons',
            array( 'customer_id' => $verified_id ),
            array( 'customer_id' => $anonymous_id )
        );

        // Soft-delete anonymous record.
        $this->delete( $anonymous_id );
    }

    /**
     * Generate and store a login token for the customer.
     *
     * @param int $customer_id Customer ID.
     * @return string Token.
     */
    public function generate_login_token( $customer_id ) {
        $token   = wp_generate_password( 64, false );
        $expires = gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS * 30 );

        $this->update( $customer_id, array(
            'login_token'      => $token,
            'token_expires_at' => $expires,
        ) );

        return $token;
    }

    /**
     * Get or create anonymous visitor ID via cookie.
     *
     * @return string Visitor ID.
     */
    public function get_or_create_visitor_id() {
        if ( isset( $_COOKIE[ self::VISITOR_COOKIE ] ) ) {
            return sanitize_text_field( $_COOKIE[ self::VISITOR_COOKIE ] );
        }

        $visitor_id = wp_generate_uuid4();

        if ( ! headers_sent() ) {
            setcookie(
                self::VISITOR_COOKIE,
                $visitor_id,
                time() + self::COOKIE_LIFETIME,
                COOKIEPATH,
                COOKIE_DOMAIN,
                is_ssl(),
                true
            );
        }

        return $visitor_id;
    }

    /**
     * Get customer's requirements.
     *
     * @param int $customer_id Customer ID.
     * @return array Requirements rows.
     */
    public function get_requirements( $customer_id ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_customer_requirements
                 WHERE customer_id = %d ORDER BY created_at DESC",
                absint( $customer_id )
            )
        );
    }

    /**
     * Get customer's latest requirement.
     *
     * @param int $customer_id Customer ID.
     * @return object|null
     */
    public function get_latest_requirement( $customer_id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_customer_requirements
                 WHERE customer_id = %d ORDER BY created_at DESC LIMIT 1",
                absint( $customer_id )
            )
        );
    }
}
