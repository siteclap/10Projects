<?php
/**
 * Cache abstraction layer.
 * Works with both WordPress transients and object cache (Redis/Memcached).
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Helpers;

defined( 'ABSPATH' ) || exit;

class Cache_Helper {

    /** @var string Cache key prefix. */
    private const PREFIX = 'tp_';

    /**
     * Get a cached value.
     *
     * @param string $key Cache key.
     * @param string $group Cache group (for object cache).
     * @return mixed Cached value or false.
     */
    public static function get( $key, $group = 'tenprojects' ) {
        // Try object cache first (Redis/Memcached).
        if ( wp_using_ext_object_cache() ) {
            return wp_cache_get( self::PREFIX . $key, $group );
        }

        // Fallback to transients.
        return get_transient( self::PREFIX . $key );
    }

    /**
     * Set a cached value.
     *
     * @param string $key        Cache key.
     * @param mixed  $value      Value to cache.
     * @param int    $expiration TTL in seconds (default 1 hour).
     * @param string $group      Cache group.
     * @return bool Success.
     */
    public static function set( $key, $value, $expiration = 3600, $group = 'tenprojects' ) {
        if ( wp_using_ext_object_cache() ) {
            return wp_cache_set( self::PREFIX . $key, $value, $group, $expiration );
        }

        return set_transient( self::PREFIX . $key, $value, $expiration );
    }

    /**
     * Delete a cached value.
     *
     * @param string $key   Cache key.
     * @param string $group Cache group.
     * @return bool Success.
     */
    public static function delete( $key, $group = 'tenprojects' ) {
        if ( wp_using_ext_object_cache() ) {
            return wp_cache_delete( self::PREFIX . $key, $group );
        }

        return delete_transient( self::PREFIX . $key );
    }

    /**
     * Get or set — return cached value, or compute and cache it.
     *
     * @param string   $key        Cache key.
     * @param callable $callback   Function to compute value.
     * @param int      $expiration TTL in seconds.
     * @param string   $group      Cache group.
     * @return mixed Cached or computed value.
     */
    public static function remember( $key, callable $callback, $expiration = 3600, $group = 'tenprojects' ) {
        $value = self::get( $key, $group );

        if ( false !== $value ) {
            return $value;
        }

        $value = $callback();

        if ( null !== $value ) {
            self::set( $key, $value, $expiration, $group );
        }

        return $value;
    }

    /**
     * Flush all plugin caches.
     *
     * @return void
     */
    public static function flush_all() {
        global $wpdb;

        if ( wp_using_ext_object_cache() ) {
            wp_cache_flush();
            return;
        }

        // Delete all tp_ transients.
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_tp_%' ) );
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_timeout_tp_%' ) );
    }

    /**
     * Flush caches by pattern.
     *
     * @param string $pattern Key pattern (e.g., 'project_' to flush all project caches).
     */
    public static function flush_pattern( $pattern ) {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options}
                 WHERE option_name LIKE %s
                 OR option_name LIKE %s",
                '_transient_' . self::PREFIX . $pattern . '%',
                '_transient_timeout_' . self::PREFIX . $pattern . '%'
            )
        );
    }

    // --- Preset cache keys ---

    /**
     * Cache project data.
     */
    public static function get_project( $project_id ) {
        return self::get( 'project_' . $project_id );
    }

    public static function set_project( $project_id, $data ) {
        return self::set( 'project_' . $project_id, $data, 3600 );
    }

    /**
     * Cache scoring results.
     */
    public static function get_scores( $requirement_id ) {
        return self::get( 'scores_' . $requirement_id );
    }

    public static function set_scores( $requirement_id, $data ) {
        return self::set( 'scores_' . $requirement_id, $data, 1800 ); // 30 min
    }

    /**
     * Cache location data.
     */
    public static function get_location( $location_slug ) {
        return self::get( 'location_' . $location_slug );
    }

    public static function set_location( $location_slug, $data ) {
        return self::set( 'location_' . $location_slug, $data, 7200 ); // 2 hours
    }
}
