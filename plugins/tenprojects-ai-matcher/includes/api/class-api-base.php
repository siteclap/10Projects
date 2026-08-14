<?php
/**
 * Base REST API controller.
 * All API controllers extend this class.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

use TenProjects\Helpers\Rate_Limiter;
use TenProjects\Helpers\Sanitizer;

class API_Base extends \WP_REST_Controller {

    /**
     * API namespace.
     *
     * @var string
     */
    protected $namespace = 'tenprojects/v1';

    /**
     * Register routes — child classes must override this.
     */
    public function register_routes() {
        // Override in child class.
    }

    /**
     * Check if rate limited and return error if so.
     *
     * @param \WP_REST_Request $request Request object.
     * @return bool|\WP_Error True if allowed, WP_Error if limited.
     */
    protected function check_rate_limit( $request ) {
        $ip = $this->get_client_ip( $request );

        if ( Rate_Limiter::throttle_api( $ip ) ) {
            $retry_after = Rate_Limiter::retry_after( 'api_' . $ip, 60 );

            return new \WP_Error(
                'rate_limited',
                'Too many requests. Please try again later.',
                array(
                    'status'      => 429,
                    'retry_after' => $retry_after,
                )
            );
        }

        return true;
    }

    /**
     * Get client IP address.
     *
     * @param \WP_REST_Request $request Request.
     * @return string IP address.
     */
    protected function get_client_ip( $request ) {
        // Only trust REMOTE_ADDR — X-Forwarded-For can be spoofed by clients
        // to bypass rate limiting. If behind a trusted reverse proxy (e.g.
        // Cloudflare, Nginx), configure the proxy to set REMOTE_ADDR instead.
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';

        return $ip;
    }

    /**
     * Success response.
     *
     * @param mixed $data    Response data.
     * @param int   $status  HTTP status code.
     * @return \WP_REST_Response
     */
    protected function success( $data = null, $status = 200 ) {
        $response = array( 'success' => true );

        if ( null !== $data ) {
            $response['data'] = $data;
        }

        return new \WP_REST_Response( $response, $status );
    }

    /**
     * Error response.
     *
     * @param string $code    Error code.
     * @param string $message Error message.
     * @param int    $status  HTTP status code.
     * @param array  $data    Additional error data.
     * @return \WP_Error
     */
    protected function error( $code, $message, $status = 400, $data = array() ) {
        $data['status'] = $status;
        return new \WP_Error( $code, $message, $data );
    }

    /**
     * Validate required fields in request.
     *
     * @param \WP_REST_Request $request  Request.
     * @param array            $required Required field names.
     * @return true|\WP_Error True if valid, WP_Error if missing fields.
     */
    protected function validate_required( $request, array $required ) {
        $params  = $request->get_json_params() ?: $request->get_params();
        $missing = array();

        foreach ( $required as $field ) {
            if ( ! isset( $params[ $field ] ) || ( is_string( $params[ $field ] ) && trim( $params[ $field ] ) === '' ) ) {
                $missing[] = $field;
            }
        }

        if ( ! empty( $missing ) ) {
            return $this->error(
                'missing_fields',
                'Required fields missing: ' . implode( ', ', $missing ),
                400,
                array( 'missing' => $missing )
            );
        }

        return true;
    }

    /**
     * Get current customer from request (by auth token or cookie).
     *
     * @param \WP_REST_Request $request Request.
     * @return object|null Customer object or null.
     */
    protected function get_current_customer( $request ) {
        global $wpdb;

        // Check for auth token in header.
        $token = $request->get_header( 'X-TP-Auth-Token' );
        if ( $token ) {
            $customer = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}tp_customers
                     WHERE login_token = %s AND token_expires_at > NOW()
                     AND deleted_at IS NULL",
                    sanitize_text_field( $token )
                )
            );
            return $customer ?: null;
        }

        // Check WP user.
        $user_id = get_current_user_id();
        if ( $user_id ) {
            $customer = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}tp_customers
                     WHERE wp_user_id = %d AND deleted_at IS NULL",
                    $user_id
                )
            );
            return $customer ?: null;
        }

        return null;
    }

    /**
     * Permission check: public endpoint (no auth needed, rate limited).
     *
     * @param \WP_REST_Request $request Request.
     * @return true|\WP_Error
     */
    public function public_permissions( $request ) {
        // Require X-Requested-With header to deter trivial curl/scraper access
        $xhr = $request->get_header( 'X-Requested-With' );
        if ( ! $xhr || strtolower( $xhr ) !== 'xmlhttprequest' ) {
            return $this->error( 'forbidden', 'Invalid request.', 403 );
        }

        return $this->check_rate_limit( $request );
    }

    /**
     * Permission check: authenticated customer.
     *
     * @param \WP_REST_Request $request Request.
     * @return true|\WP_Error
     */
    public function customer_permissions( $request ) {
        $rate_check = $this->check_rate_limit( $request );
        if ( is_wp_error( $rate_check ) ) {
            return $rate_check;
        }

        $customer = $this->get_current_customer( $request );
        if ( ! $customer ) {
            return $this->error( 'unauthorized', 'Authentication required.', 401 );
        }

        return true;
    }

    /**
     * Permission check: admin user.
     *
     * @param \WP_REST_Request $request Request.
     * @return true|\WP_Error
     */
    public function admin_permissions( $request ) {
        if ( ! current_user_can( 'manage_tp_settings' ) ) {
            return $this->error( 'forbidden', 'Admin access required.', 403 );
        }
        return true;
    }

    /**
     * Permission check: partner user.
     *
     * @param \WP_REST_Request $request Request.
     * @return true|\WP_Error
     */
    public function partner_permissions( $request ) {
        $rate_check = $this->check_rate_limit( $request );
        if ( is_wp_error( $rate_check ) ) {
            return $rate_check;
        }

        if ( ! current_user_can( 'view_tp_leads' ) && ! current_user_can( 'manage_tp_settings' ) ) {
            return $this->error( 'forbidden', 'Partner access required.', 403 );
        }

        return true;
    }

    /**
     * Get pagination parameters from request.
     *
     * @param \WP_REST_Request $request Request.
     * @return array page, per_page, offset.
     */
    protected function get_pagination( $request ) {
        $page     = max( 1, absint( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = min( 100, max( 1, absint( $request->get_param( 'per_page' ) ?: 20 ) ) );
        $offset   = ( $page - 1 ) * $per_page;

        return array(
            'page'     => $page,
            'per_page' => $per_page,
            'offset'   => $offset,
        );
    }

    /**
     * Add pagination headers to response.
     *
     * @param \WP_REST_Response $response  Response object.
     * @param int               $total     Total items.
     * @param int               $per_page  Items per page.
     * @param int               $page      Current page.
     * @return \WP_REST_Response
     */
    protected function add_pagination_headers( $response, $total, $per_page, $page ) {
        $total_pages = ceil( $total / $per_page );

        $response->header( 'X-WP-Total', $total );
        $response->header( 'X-WP-TotalPages', $total_pages );

        return $response;
    }
}
