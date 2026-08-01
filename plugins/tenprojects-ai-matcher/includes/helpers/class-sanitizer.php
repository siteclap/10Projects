<?php
/**
 * Input sanitization helper.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Helpers;

defined( 'ABSPATH' ) || exit;

class Sanitizer {

    /**
     * Sanitize Indian phone number.
     * Accepts: +91XXXXXXXXXX, 91XXXXXXXXXX, 0XXXXXXXXXX, XXXXXXXXXX
     * Returns: 10-digit number or empty string.
     *
     * @param string $phone Raw phone input.
     * @return string Sanitized 10-digit phone number.
     */
    public static function phone( $phone ) {
        // Remove all non-digit characters.
        $digits = preg_replace( '/\D/', '', $phone );

        // Remove country code prefix.
        if ( strlen( $digits ) === 12 && str_starts_with( $digits, '91' ) ) {
            $digits = substr( $digits, 2 );
        }

        // Remove leading 0.
        if ( strlen( $digits ) === 11 && str_starts_with( $digits, '0' ) ) {
            $digits = substr( $digits, 1 );
        }

        // Validate: must be 10 digits starting with 6-9.
        if ( strlen( $digits ) === 10 && preg_match( '/^[6-9]/', $digits ) ) {
            return $digits;
        }

        return '';
    }

    /**
     * Sanitize email address.
     *
     * @param string $email Raw email.
     * @return string Sanitized email or empty string.
     */
    public static function email( $email ) {
        $email = sanitize_email( $email );
        return is_email( $email ) ? $email : '';
    }

    /**
     * Sanitize budget amount.
     * Accepts: "1.5 Cr", "75 L", "75,00,000", "7500000"
     *
     * @param string|int $budget Raw budget input.
     * @return int Amount in rupees.
     */
    public static function budget( $budget ) {
        if ( is_numeric( $budget ) ) {
            return absint( $budget );
        }

        $budget = strtolower( trim( $budget ) );

        // Remove currency symbol and commas.
        $budget = preg_replace( '/[₹,\s]/', '', $budget );

        // Handle "Cr" / "cr" / "crore"
        if ( preg_match( '/^([\d.]+)\s*(cr|crore)/i', $budget, $m ) ) {
            return intval( floatval( $m[1] ) * 10000000 );
        }

        // Handle "L" / "lakh"
        if ( preg_match( '/^([\d.]+)\s*(l|lakh)/i', $budget, $m ) ) {
            return intval( floatval( $m[1] ) * 100000 );
        }

        // Plain number.
        return absint( preg_replace( '/\D/', '', $budget ) );
    }

    /**
     * Sanitize a string for safe output.
     *
     * @param string $value Raw string.
     * @return string Sanitized string.
     */
    public static function text( $value ) {
        return sanitize_text_field( wp_unslash( $value ) );
    }

    /**
     * Sanitize textarea content.
     *
     * @param string $value Raw textarea.
     * @return string Sanitized text with line breaks preserved.
     */
    public static function textarea( $value ) {
        return sanitize_textarea_field( wp_unslash( $value ) );
    }

    /**
     * Sanitize HTML content (allow basic formatting).
     *
     * @param string $html Raw HTML.
     * @return string Sanitized HTML.
     */
    public static function html( $html ) {
        return wp_kses_post( $html );
    }

    /**
     * Sanitize a JSON string.
     *
     * @param string $json Raw JSON.
     * @return string Valid JSON string or '[]'.
     */
    public static function json( $json ) {
        if ( empty( $json ) ) {
            return '[]';
        }

        $decoded = json_decode( wp_unslash( $json ), true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return '[]';
        }

        return wp_json_encode( $decoded );
    }

    /**
     * Sanitize a URL.
     *
     * @param string $url Raw URL.
     * @return string Sanitized URL.
     */
    public static function url( $url ) {
        return esc_url_raw( $url );
    }

    /**
     * Sanitize a slug.
     *
     * @param string $slug Raw slug.
     * @return string Sanitized slug.
     */
    public static function slug( $slug ) {
        return sanitize_title( $slug );
    }

    /**
     * Sanitize and validate an enum value.
     *
     * @param string $value   Raw value.
     * @param array  $allowed Allowed values.
     * @param string $default Default if invalid.
     * @return string Validated value.
     */
    public static function enum( $value, array $allowed, $default = '' ) {
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : $default;
    }
}
