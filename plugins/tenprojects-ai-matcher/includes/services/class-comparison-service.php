<?php
/**
 * Comparison Service — side-by-side project comparison logic.
 *
 * Creates comparison sets of 2–4 projects, loads all comparable fields
 * organized by category, and generates comparison highlights to help
 * customers make informed decisions.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

use TenProjects\Helpers\EMI_Calculator;

class Comparison_Service {

	/**
	 * Minimum number of projects in a comparison.
	 *
	 * @var int
	 */
	private const MIN_PROJECTS = 2;

	/**
	 * Maximum number of projects in a comparison.
	 *
	 * @var int
	 */
	private const MAX_PROJECTS = 4;

	/**
	 * Create a new comparison set.
	 *
	 * Validates that 2–4 projects are provided, generates a shareable token,
	 * and inserts the comparison record into tp_comparisons.
	 *
	 * @param int   $customer_id Customer ID from tp_customers.
	 * @param int[] $project_ids Array of project post IDs to compare.
	 * @return object|false Comparison record object or false on failure.
	 */
	public function create( $customer_id, $project_ids ) {
		global $wpdb;

		// Validate project count.
		$project_ids = array_map( 'absint', array_unique( $project_ids ) );
		$project_ids = array_filter( $project_ids );

		if ( count( $project_ids ) < self::MIN_PROJECTS ) {
			error_log( sprintf(
				'[TenProjects] Comparison create failed: minimum %d projects required, got %d.',
				self::MIN_PROJECTS,
				count( $project_ids )
			) );
			return false;
		}

		if ( count( $project_ids ) > self::MAX_PROJECTS ) {
			error_log( sprintf(
				'[TenProjects] Comparison create failed: maximum %d projects allowed, got %d.',
				self::MAX_PROJECTS,
				count( $project_ids )
			) );
			return false;
		}

		// Validate that all projects exist and are published.
		foreach ( $project_ids as $pid ) {
			$post = get_post( $pid );
			if ( ! $post || 'tp_project' !== $post->post_type || 'publish' !== $post->post_status ) {
				error_log( sprintf( '[TenProjects] Comparison create failed: project #%d is not a valid published project.', $pid ) );
				return false;
			}
		}

		$share_token = substr( md5( wp_generate_uuid4() ), 0, 12 );

		$result = $wpdb->insert(
			$wpdb->prefix . 'tp_comparisons',
			array(
				'customer_id'     => absint( $customer_id ),
				'project_ids'     => wp_json_encode( array_values( $project_ids ) ),
				'comparison_data' => null,
				'share_token'     => $share_token,
				'created_at'      => current_time( 'mysql' ),
			)
		);

		if ( false === $result ) {
			error_log( '[TenProjects] Comparison create failed: database insert error.' );
			return false;
		}

		return $this->find( $wpdb->insert_id );
	}

	/**
	 * Get full comparison data for a comparison set.
	 *
	 * Loads all comparable fields for each project organized by category:
	 * Basic, Pricing, Size, Developer, Location, Legal, Proximity, Amenities.
	 *
	 * @param int $comparison_id Comparison ID from tp_comparisons.
	 * @return array|false Structured comparison data or false if not found.
	 */
	public function get_comparison_data( $comparison_id ) {
		$comparison = $this->find( $comparison_id );
		if ( ! $comparison ) {
			return false;
		}

		$project_ids = json_decode( $comparison->project_ids, true );
		if ( ! is_array( $project_ids ) || empty( $project_ids ) ) {
			return false;
		}

		$projects = array();

		foreach ( $project_ids as $project_id ) {
			$project_id = absint( $project_id );
			$post       = get_post( $project_id );

			if ( ! $post ) {
				continue;
			}

			$projects[] = $this->load_project_comparison_data( $project_id, $post );
		}

		return array(
			'comparison_id' => $comparison->id,
			'share_token'   => $comparison->share_token,
			'created_at'    => $comparison->created_at,
			'projects'      => $projects,
		);
	}

	/**
	 * Analyze comparison data and return highlights.
	 *
	 * Identifies the "best" project in key categories to help customers
	 * quickly understand relative strengths.
	 *
	 * @param array $comparison_data Structured comparison data from get_comparison_data().
	 * @return array Highlights with category labels and winning project info.
	 */
	public function get_comparison_highlights( $comparison_data ) {
		if ( empty( $comparison_data['projects'] ) ) {
			return array();
		}

		$projects   = $comparison_data['projects'];
		$highlights = array();

		// Best Value: lowest price per sqft.
		$best_value    = null;
		$best_value_id = null;
		foreach ( $projects as $p ) {
			$psf = $p['pricing']['price_per_sqft'] ?? 0;
			if ( $psf > 0 && ( null === $best_value || $psf < $best_value ) ) {
				$best_value    = $psf;
				$best_value_id = $p['basic']['project_id'];
			}
		}
		if ( null !== $best_value_id ) {
			$highlights[] = array(
				'category'   => 'Best Value',
				'project_id' => $best_value_id,
				'project'    => $this->find_project_name( $projects, $best_value_id ),
				'detail'     => sprintf( 'Lowest price per sq.ft at Rs. %s', number_format( $best_value ) ),
			);
		}

		// Most Trusted Developer: highest developer reputation_score.
		$best_dev    = null;
		$best_dev_id = null;
		foreach ( $projects as $p ) {
			$rep = $p['developer']['reputation_score'] ?? 0;
			if ( $rep > 0 && ( null === $best_dev || $rep > $best_dev ) ) {
				$best_dev    = $rep;
				$best_dev_id = $p['basic']['project_id'];
			}
		}
		if ( null !== $best_dev_id ) {
			$highlights[] = array(
				'category'   => 'Most Trusted Developer',
				'project_id' => $best_dev_id,
				'project'    => $this->find_project_name( $projects, $best_dev_id ),
				'detail'     => sprintf( 'Developer reputation score: %s/100', $best_dev ),
			);
		}

		// Best Location: highest liveability_score.
		$best_loc    = null;
		$best_loc_id = null;
		foreach ( $projects as $p ) {
			$live = $p['location']['liveability_score'] ?? 0;
			if ( $live > 0 && ( null === $best_loc || $live > $best_loc ) ) {
				$best_loc    = $live;
				$best_loc_id = $p['basic']['project_id'];
			}
		}
		if ( null !== $best_loc_id ) {
			$highlights[] = array(
				'category'   => 'Best Location',
				'project_id' => $best_loc_id,
				'project'    => $this->find_project_name( $projects, $best_loc_id ),
				'detail'     => sprintf( 'Liveability score: %s/100', $best_loc ),
			);
		}

		// Ready Soonest: earliest possession date.
		$soonest    = null;
		$soonest_id = null;
		foreach ( $projects as $p ) {
			$poss = $p['basic']['possession'] ?? '';
			if ( ! empty( $poss ) ) {
				$poss_time = strtotime( $poss );
				if ( $poss_time && ( null === $soonest || $poss_time < $soonest ) ) {
					$soonest    = $poss_time;
					$soonest_id = $p['basic']['project_id'];
				}
			}
		}
		if ( null !== $soonest_id ) {
			$highlights[] = array(
				'category'   => 'Ready Soonest',
				'project_id' => $soonest_id,
				'project'    => $this->find_project_name( $projects, $soonest_id ),
				'detail'     => sprintf( 'Expected possession: %s', gmdate( 'M Y', $soonest ) ),
			);
		}

		// Best Connected: closest to railway station.
		$best_rail    = null;
		$best_rail_id = null;
		foreach ( $projects as $p ) {
			$rail = $p['proximity']['railway_km'] ?? null;
			if ( null !== $rail && $rail > 0 && ( null === $best_rail || $rail < $best_rail ) ) {
				$best_rail    = $rail;
				$best_rail_id = $p['basic']['project_id'];
			}
		}
		if ( null !== $best_rail_id ) {
			$highlights[] = array(
				'category'   => 'Best Connected',
				'project_id' => $best_rail_id,
				'project'    => $this->find_project_name( $projects, $best_rail_id ),
				'detail'     => sprintf( 'Railway station: %s km', $best_rail ),
			);
		}

		return $highlights;
	}

	/**
	 * Find a comparison record by ID.
	 *
	 * @param int $id Comparison ID.
	 * @return object|null Comparison row or null.
	 */
	public function find( $id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_comparisons WHERE id = %d",
				absint( $id )
			)
		);
	}

	/**
	 * Find a comparison by its shareable token.
	 *
	 * @param string $token Share token (12-character hex string).
	 * @return object|null Comparison row or null.
	 */
	public function find_by_token( $token ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_comparisons WHERE share_token = %s",
				sanitize_text_field( $token )
			)
		);
	}

	/**
	 * Get all comparisons for a customer.
	 *
	 * @param int $customer_id Customer ID.
	 * @return array Array of comparison records ordered by most recent first.
	 */
	public function get_customer_comparisons( $customer_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_comparisons
				 WHERE customer_id = %d
				 ORDER BY created_at DESC",
				absint( $customer_id )
			)
		);
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Load all comparable data for a single project, organized by category.
	 *
	 * @param int      $project_id Project post ID.
	 * @param \WP_Post $post       Project post object.
	 * @return array Structured project data by category.
	 */
	private function load_project_comparison_data( $project_id, $post ) {
		global $wpdb;

		$meta = get_post_meta( $project_id );

		// Helper to get a single meta value.
		$get = function ( $key ) use ( $meta ) {
			$meta_key = '_tp_' . $key;
			return isset( $meta[ $meta_key ] ) ? $meta[ $meta_key ][0] : null;
		};

		// --- Basic info ---
		$location_terms = wp_get_post_terms( $project_id, 'tp_location_area', array( 'fields' => 'names' ) );
		$location_name  = ( is_array( $location_terms ) && ! empty( $location_terms ) )
			? implode( ', ', $location_terms )
			: '';

		$basic = array(
			'project_id'         => $project_id,
			'name'               => $post->post_title,
			'permalink'          => get_permalink( $project_id ),
			'thumbnail'          => get_the_post_thumbnail_url( $project_id, 'tp-card-thumb' ),
			'developer'          => '',
			'developer_id'       => absint( $get( 'developer_id' ) ),
			'location'           => $location_name,
			'construction_stage' => $get( 'construction_stage' ),
			'possession'         => $get( 'expected_possession' ),
		);

		// Resolve developer name.
		if ( $basic['developer_id'] ) {
			$basic['developer'] = get_the_title( $basic['developer_id'] );
		}

		// --- Pricing ---
		$prices = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT MIN(base_price) as min_price, MAX(base_price) as max_price
				 FROM {$wpdb->prefix}tp_project_configurations
				 WHERE project_id = %d AND base_price > 0",
				$project_id
			)
		);

		$min_price = (int) ( $prices->min_price ?? 0 );
		$max_price = (int) ( $prices->max_price ?? 0 );

		// Average price per sq.ft from configurations.
		$avg_psf = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND(AVG(base_price / NULLIF(carpet_area, 0)))
				 FROM {$wpdb->prefix}tp_project_configurations
				 WHERE project_id = %d AND base_price > 0 AND carpet_area > 0",
				$project_id
			)
		);

		// EMI estimate based on average price.
		$avg_price    = ( $min_price + $max_price ) > 0 ? intval( ( $min_price + $max_price ) / 2 ) : 0;
		$emi_estimate = $avg_price > 0 ? EMI_Calculator::calculate( $avg_price ) : 0;

		$pricing = array(
			'min_price'     => $min_price,
			'max_price'     => $max_price,
			'price_per_sqft' => (int) ( $avg_psf ?? 0 ),
			'emi_estimate'  => $emi_estimate,
		);

		// --- Size ---
		$configs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT config_type, carpet_area
				 FROM {$wpdb->prefix}tp_project_configurations
				 WHERE project_id = %d",
				$project_id
			)
		);

		$config_types = array();
		$areas        = array();
		foreach ( $configs as $cfg ) {
			if ( ! empty( $cfg->config_type ) ) {
				$config_types[] = $cfg->config_type;
			}
			if ( ! empty( $cfg->carpet_area ) && $cfg->carpet_area > 0 ) {
				$areas[] = (float) $cfg->carpet_area;
			}
		}

		$size = array(
			'configurations' => array_unique( $config_types ),
			'carpet_area_min' => ! empty( $areas ) ? min( $areas ) : 0,
			'carpet_area_max' => ! empty( $areas ) ? max( $areas ) : 0,
		);

		// --- Developer ---
		$developer = array(
			'name'               => $basic['developer'],
			'tier'               => null,
			'reputation_score'   => null,
			'ontime_rate'        => null,
			'completed_projects' => null,
		);

		if ( $basic['developer_id'] ) {
			$dev_meta = get_post_meta( $basic['developer_id'] );

			$dev_get = function ( $key ) use ( $dev_meta ) {
				$meta_key = '_tp_' . $key;
				return isset( $dev_meta[ $meta_key ] ) ? $dev_meta[ $meta_key ][0] : null;
			};

			$developer['tier']               = $dev_get( 'dev_tier' );
			$developer['reputation_score']   = (int) $dev_get( 'dev_reputation_score' );
			$developer['ontime_rate']        = (float) $dev_get( 'dev_ontime_rate' );
			$developer['completed_projects'] = (int) $dev_get( 'dev_completed_projects' );
		}

		// --- Location ---
		$location = array(
			'avg_price_sqft'    => (int) ( $get( 'micro_market_price' ) ?? 0 ),
			'growth_3yr'        => null,
			'liveability_score' => null,
		);

		$loc_slugs = wp_get_post_terms( $project_id, 'tp_location_area', array( 'fields' => 'slugs' ) );
		if ( is_array( $loc_slugs ) && ! empty( $loc_slugs ) ) {
			$loc_query = new \WP_Query( array(
				'post_type'      => 'tp_location',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'name'           => $loc_slugs[0],
			) );

			if ( $loc_query->have_posts() ) {
				$loc_id   = $loc_query->posts[0]->ID;
				$loc_meta = get_post_meta( $loc_id );

				$location['growth_3yr']        = isset( $loc_meta['_tp_loc_price_trend_3yr'] )
					? (float) $loc_meta['_tp_loc_price_trend_3yr'][0]
					: null;
				$location['liveability_score'] = isset( $loc_meta['_tp_loc_liveability_score'] )
					? (int) $loc_meta['_tp_loc_liveability_score'][0]
					: null;
			}
		}

		// --- Legal ---
		$bank_approvals = $get( 'bank_approved' );
		$bank_count     = 0;
		if ( ! empty( $bank_approvals ) ) {
			$banks      = json_decode( $bank_approvals, true );
			$bank_count = is_array( $banks ) ? count( $banks ) : ( is_numeric( $bank_approvals ) ? (int) $bank_approvals : 0 );
		}

		$legal = array(
			'rera_number'       => $get( 'rera_number' ),
			'bank_approvals'    => $bank_count,
			'litigation_status' => $get( 'litigation_status' ),
		);

		// --- Proximity (all in km) ---
		$proximity = array(
			'railway_km'  => $get( 'railway_distance_km' ) !== null ? (float) $get( 'railway_distance_km' ) : null,
			'school_km'   => $get( 'school_distance_km' ) !== null ? (float) $get( 'school_distance_km' ) : null,
			'hospital_km' => $get( 'hospital_distance_km' ) !== null ? (float) $get( 'hospital_distance_km' ) : null,
			'mall_km'     => $get( 'mall_distance_km' ) !== null ? (float) $get( 'mall_distance_km' ) : null,
		);

		// --- Amenities ---
		$amenity_terms = wp_get_post_terms( $project_id, 'tp_amenity', array( 'fields' => 'names' ) );
		$amenities     = is_array( $amenity_terms ) ? $amenity_terms : array();

		return array(
			'basic'     => $basic,
			'pricing'   => $pricing,
			'size'      => $size,
			'developer' => $developer,
			'location'  => $location,
			'legal'     => $legal,
			'proximity' => $proximity,
			'amenities' => $amenities,
		);
	}

	/**
	 * Find a project name from the comparison data by project ID.
	 *
	 * @param array $projects   Array of project comparison data.
	 * @param int   $project_id Project ID to find.
	 * @return string Project name or 'Unknown'.
	 */
	private function find_project_name( array $projects, $project_id ) {
		foreach ( $projects as $p ) {
			if ( ( $p['basic']['project_id'] ?? 0 ) === $project_id ) {
				return $p['basic']['name'] ?? 'Unknown';
			}
		}
		return 'Unknown';
	}
}
