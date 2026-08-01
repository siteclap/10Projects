<?php
/**
 * Geographic calculation helper.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Helpers;

defined( 'ABSPATH' ) || exit;

class Geo_Helper {

    /** @var float Earth radius in kilometers. */
    private const EARTH_RADIUS_KM = 6371;

    /**
     * Calculate distance between two coordinates using Haversine formula.
     *
     * @param float $lat1 Latitude of point 1.
     * @param float $lng1 Longitude of point 1.
     * @param float $lat2 Latitude of point 2.
     * @param float $lng2 Longitude of point 2.
     * @return float Distance in kilometers.
     */
    public static function distance( $lat1, $lng1, $lat2, $lng2 ) {
        $lat1_rad = deg2rad( $lat1 );
        $lat2_rad = deg2rad( $lat2 );
        $delta_lat = deg2rad( $lat2 - $lat1 );
        $delta_lng = deg2rad( $lng2 - $lng1 );

        $a = sin( $delta_lat / 2 ) * sin( $delta_lat / 2 )
           + cos( $lat1_rad ) * cos( $lat2_rad )
           * sin( $delta_lng / 2 ) * sin( $delta_lng / 2 );

        $c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );

        return round( self::EARTH_RADIUS_KM * $c, 2 );
    }

    /**
     * Estimate commute time.
     *
     * @param float  $distance_km Distance in km.
     * @param string $mode        Transport mode: 'car', 'train', 'auto', 'metro', 'walk'.
     * @return int Estimated minutes.
     */
    public static function commute_time( $distance_km, $mode = 'car' ) {
        // Average speeds in km/h for Indian cities.
        $speeds = array(
            'car'   => 25, // Peak hour: ~20, off-peak: ~35.
            'train' => 40, // Mumbai locals / suburban rail.
            'metro' => 35, // Metro rail.
            'auto'  => 20, // Auto-rickshaw.
            'bus'   => 18, // City bus.
            'walk'  => 5,  // Walking.
            'bike'  => 30, // Two-wheeler.
        );

        $speed = $speeds[ $mode ] ?? $speeds['car'];

        // Add buffer: 5 min for parking/waiting + 10% for traffic variance.
        $time = ( $distance_km / $speed ) * 60;
        $time = $time * 1.1 + 5;

        return intval( round( $time ) );
    }

    /**
     * Format distance for display.
     *
     * @param float $km Distance in kilometers.
     * @return string Formatted distance.
     */
    public static function format_distance( $km ) {
        if ( $km < 1 ) {
            return intval( $km * 1000 ) . ' m';
        }
        return number_format( $km, 1 ) . ' km';
    }

    /**
     * Find projects within a radius.
     *
     * @param float $lat    Center latitude.
     * @param float $lng    Center longitude.
     * @param float $radius Radius in km.
     * @return array Array of post IDs with distances.
     */
    public static function find_projects_within( $lat, $lng, $radius = 10 ) {
        global $wpdb;

        // Use a bounding box for initial filter, then Haversine for precision.
        $lat_delta = $radius / 111.0;
        $lng_delta = $radius / ( 111.0 * cos( deg2rad( $lat ) ) );

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID,
                        lat.meta_value AS latitude,
                        lng.meta_value AS longitude,
                        (
                            {$wpdb->prepare_expression(self::EARTH_RADIUS_KM)} * 2 * ASIN(SQRT(
                                POWER(SIN((%f - ABS(CAST(lat.meta_value AS DECIMAL(10,7)))) * PI() / 180 / 2), 2)
                                + COS(%f * PI() / 180)
                                * COS(ABS(CAST(lat.meta_value AS DECIMAL(10,7))) * PI() / 180)
                                * POWER(SIN((%f - CAST(lng.meta_value AS DECIMAL(10,7))) * PI() / 180 / 2), 2)
                            ))
                        ) AS distance
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} lat ON p.ID = lat.post_id AND lat.meta_key = '_tp_latitude'
                 INNER JOIN {$wpdb->postmeta} lng ON p.ID = lng.post_id AND lng.meta_key = '_tp_longitude'
                 WHERE p.post_type = 'tp_project'
                   AND p.post_status = 'publish'
                   AND CAST(lat.meta_value AS DECIMAL(10,7)) BETWEEN %f AND %f
                   AND CAST(lng.meta_value AS DECIMAL(10,7)) BETWEEN %f AND %f
                 HAVING distance <= %f
                 ORDER BY distance ASC",
                $lat, $lat, $lng,
                $lat - $lat_delta, $lat + $lat_delta,
                $lng - $lng_delta, $lng + $lng_delta,
                $radius
            )
        );

        if ( ! $results ) {
            return array();
        }

        $projects = array();
        foreach ( $results as $row ) {
            $projects[] = array(
                'post_id'  => intval( $row->ID ),
                'distance' => round( floatval( $row->distance ), 2 ),
            );
        }

        return $projects;
    }

    /**
     * Get nearest infrastructure of a type.
     *
     * @param float  $lat  Project latitude.
     * @param float  $lng  Project longitude.
     * @param string $type Infrastructure type ('metro', 'railway', 'highway', etc.).
     * @param int    $limit Max results.
     * @return array Nearest infrastructure posts with distances.
     */
    public static function nearest_infrastructure( $lat, $lng, $type = '', $limit = 3 ) {
        $args = array(
            'post_type'      => 'tp_infrastructure',
            'post_status'    => 'publish',
            'posts_per_page' => 50, // Get pool then sort by distance.
            'meta_query'     => array(),
        );

        if ( $type ) {
            $args['meta_query'][] = array(
                'key'   => '_tp_infra_type',
                'value' => $type,
            );
        }

        $query = new \WP_Query( $args );
        $results = array();

        foreach ( $query->posts as $post ) {
            $infra_lat = floatval( get_post_meta( $post->ID, '_tp_infra_latitude', true ) );
            $infra_lng = floatval( get_post_meta( $post->ID, '_tp_infra_longitude', true ) );

            if ( ! $infra_lat || ! $infra_lng ) {
                continue;
            }

            $dist = self::distance( $lat, $lng, $infra_lat, $infra_lng );
            $results[] = array(
                'post_id'  => $post->ID,
                'title'    => $post->post_title,
                'type'     => get_post_meta( $post->ID, '_tp_infra_type', true ),
                'status'   => get_post_meta( $post->ID, '_tp_infra_status', true ),
                'distance' => $dist,
            );
        }

        // Sort by distance.
        usort( $results, fn( $a, $b ) => $a['distance'] <=> $b['distance'] );

        return array_slice( $results, 0, $limit );
    }
}
