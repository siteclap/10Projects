<?php
/**
 * Data validation helper.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Helpers;

defined( 'ABSPATH' ) || exit;

class Validator {

    /**
     * Validate Indian phone number.
     *
     * @param string $phone Phone number.
     * @return bool True if valid.
     */
    public static function is_valid_phone( $phone ) {
        $clean = Sanitizer::phone( $phone );
        return ! empty( $clean );
    }

    /**
     * Validate email address.
     *
     * @param string $email Email address.
     * @return bool True if valid.
     */
    public static function is_valid_email( $email ) {
        return (bool) is_email( $email );
    }

    /**
     * Validate budget is within reasonable range.
     *
     * @param int $budget Budget in rupees.
     * @return bool True if valid (10L to 100Cr).
     */
    public static function is_valid_budget( $budget ) {
        $budget = absint( $budget );
        return $budget >= 1000000 && $budget <= 10000000000; // 10L to 100Cr
    }

    /**
     * Validate RERA number format.
     * Maharashtra format: P52100XXXXXX
     *
     * @param string $rera RERA number.
     * @return bool True if valid.
     */
    public static function is_valid_rera( $rera ) {
        if ( empty( $rera ) ) {
            return false;
        }
        // Basic format check — alphanumeric, 10-20 chars.
        return (bool) preg_match( '/^[A-Z0-9]{10,20}$/i', $rera );
    }

    /**
     * Validate latitude.
     *
     * @param float $lat Latitude.
     * @return bool True if valid.
     */
    public static function is_valid_latitude( $lat ) {
        return is_numeric( $lat ) && $lat >= -90 && $lat <= 90;
    }

    /**
     * Validate longitude.
     *
     * @param float $lng Longitude.
     * @return bool True if valid.
     */
    public static function is_valid_longitude( $lng ) {
        return is_numeric( $lng ) && $lng >= -180 && $lng <= 180;
    }

    /**
     * Validate a score is 0-100.
     *
     * @param int $score Score value.
     * @return bool True if valid.
     */
    public static function is_valid_score( $score ) {
        return is_numeric( $score ) && $score >= 0 && $score <= 100;
    }

    /**
     * Validate a configuration (BHK).
     *
     * @param string $config Configuration value.
     * @return bool True if valid.
     */
    public static function is_valid_configuration( $config ) {
        $valid = array(
            '1 BHK', '1.5 BHK', '2 BHK', '2.5 BHK', '3 BHK',
            '3.5 BHK', '4 BHK', '5 BHK', 'Penthouse', 'Duplex',
        );
        return in_array( $config, $valid, true );
    }

    /**
     * Validate a construction stage.
     *
     * @param string $stage Stage value.
     * @return bool True if valid.
     */
    public static function is_valid_construction_stage( $stage ) {
        $valid = array( 'new_launch', 'foundation', 'structure', 'finishing', 'ready' );
        return in_array( $stage, $valid, true );
    }

    /**
     * Validate OTP code format.
     *
     * @param string $otp OTP code.
     * @return bool True if valid (4-6 digits).
     */
    public static function is_valid_otp( $otp ) {
        return (bool) preg_match( '/^\d{4,6}$/', $otp );
    }

    /**
     * Validate UUID format.
     *
     * @param string $uuid UUID string.
     * @return bool True if valid.
     */
    public static function is_valid_uuid( $uuid ) {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    /**
     * Validate a date string.
     *
     * @param string $date Date string.
     * @param string $format Expected format (default Y-m-d).
     * @return bool True if valid.
     */
    public static function is_valid_date( $date, $format = 'Y-m-d' ) {
        $d = \DateTime::createFromFormat( $format, $date );
        return $d && $d->format( $format ) === $date;
    }

    /**
     * Validate required fields in an array.
     *
     * @param array $data     Data to validate.
     * @param array $required Required field names.
     * @return array Missing field names (empty if all present).
     */
    public static function check_required( array $data, array $required ) {
        $missing = array();
        foreach ( $required as $field ) {
            if ( ! isset( $data[ $field ] ) || ( is_string( $data[ $field ] ) && trim( $data[ $field ] ) === '' ) ) {
                $missing[] = $field;
            }
        }
        return $missing;
    }
}
