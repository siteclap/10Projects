<?php
/**
 * Rate limiting helper using WordPress transients.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Helpers;

defined( 'ABSPATH' ) || exit;

class Rate_Limiter {

    /**
     * Check if action is rate limited.
     *
     * @param string $key       Unique key (e.g., 'otp_9876543210').
     * @param int    $max       Max attempts allowed.
     * @param int    $window    Time window in seconds.
     * @return bool True if rate limited (should block).
     */
    public static function is_limited( $key, $max = 3, $window = 600 ) {
        $transient_key = 'tp_rl_' . md5( $key );
        $data = get_transient( $transient_key );

        if ( false === $data ) {
            return false;
        }

        return $data['count'] >= $max;
    }

    /**
     * Record an attempt.
     *
     * @param string $key    Unique key.
     * @param int    $window Time window in seconds.
     */
    public static function record( $key, $window = 600 ) {
        $transient_key = 'tp_rl_' . md5( $key );
        $data = get_transient( $transient_key );

        if ( false === $data ) {
            $data = array(
                'count'      => 0,
                'first_at'   => time(),
            );
        }

        $data['count']++;
        $data['last_at'] = time();

        // Calculate remaining TTL.
        $elapsed   = time() - $data['first_at'];
        $remaining = max( 1, $window - $elapsed );

        set_transient( $transient_key, $data, $remaining );
    }

    /**
     * Check and record in one call. Returns false if allowed, true if blocked.
     *
     * @param string $key    Unique key.
     * @param int    $max    Max attempts.
     * @param int    $window Time window in seconds.
     * @return bool True if rate limited (blocked).
     */
    public static function throttle( $key, $max = 3, $window = 600 ) {
        if ( self::is_limited( $key, $max, $window ) ) {
            return true;
        }

        self::record( $key, $window );
        return false;
    }

    /**
     * Reset rate limit for a key.
     *
     * @param string $key Unique key.
     */
    public static function reset( $key ) {
        delete_transient( 'tp_rl_' . md5( $key ) );
    }

    /**
     * Get remaining attempts.
     *
     * @param string $key Unique key.
     * @param int    $max Max attempts.
     * @return int Remaining attempts.
     */
    public static function remaining( $key, $max = 3 ) {
        $transient_key = 'tp_rl_' . md5( $key );
        $data = get_transient( $transient_key );

        if ( false === $data ) {
            return $max;
        }

        return max( 0, $max - $data['count'] );
    }

    /**
     * Get seconds until rate limit resets.
     *
     * @param string $key    Unique key.
     * @param int    $window Time window.
     * @return int Seconds remaining, 0 if not limited.
     */
    public static function retry_after( $key, $window = 600 ) {
        $transient_key = 'tp_rl_' . md5( $key );
        $data = get_transient( $transient_key );

        if ( false === $data ) {
            return 0;
        }

        $elapsed = time() - $data['first_at'];
        return max( 0, $window - $elapsed );
    }

    // --- Preset methods for common use cases ---

    /**
     * Rate limit OTP requests: 3 per 10 minutes per phone.
     */
    public static function throttle_otp( $phone ) {
        return self::throttle( 'otp_' . $phone, 3, 600 );
    }

    /**
     * Rate limit API requests: 60 per minute per IP.
     */
    public static function throttle_api( $ip ) {
        return self::throttle( 'api_' . $ip, 60, 60 );
    }

    /**
     * Rate limit assessment starts: 10 per hour per IP.
     */
    public static function throttle_assessment( $ip ) {
        return self::throttle( 'assess_' . $ip, 10, 3600 );
    }

    /**
     * Rate limit lead submissions: 5 per hour per phone.
     */
    public static function throttle_lead( $phone ) {
        return self::throttle( 'lead_' . $phone, 5, 3600 );
    }
}
