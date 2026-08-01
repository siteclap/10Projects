<?php
/**
 * Assessment REST API controller.
 *
 * Handles the AI assessment flow: starting sessions, processing answers,
 * retrieving session state, and advancing through phases.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\API;

defined( 'ABSPATH' ) || exit;

use TenProjects\Services\Customer_Service;
use TenProjects\Services\Session_Service;
use TenProjects\Services\Recommendation_Engine;

/**
 * Class Assessment_API
 *
 * Routes:
 *  POST   /assessment/start                   — Start a new assessment session.
 *  POST   /assessment/answer                  — Submit an answer and get next question.
 *  GET    /assessment/session/<uuid>           — Retrieve session status.
 *  POST   /assessment/advance                 — Advance to next phase.
 */
class Assessment_API extends API_Base {

	/**
	 * Customer service instance.
	 *
	 * @var Customer_Service
	 */
	private $customer_service;

	/**
	 * Session service instance.
	 *
	 * @var Session_Service
	 */
	private $session_service;

	/**
	 * Recommendation engine instance.
	 *
	 * @var Recommendation_Engine
	 */
	private $recommendation_engine;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->customer_service      = new Customer_Service();
		$this->session_service       = new Session_Service();
		$this->recommendation_engine = new Recommendation_Engine();
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {

		// POST /tenprojects/v1/assessment/start
		register_rest_route(
			$this->namespace,
			'/assessment/start',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'start_assessment' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => array(
						'visitor_id'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'city'         => array(
							'type'              => 'string',
							'default'           => 'navi_mumbai',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'utm_source'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'utm_medium'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'utm_campaign' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'utm_content'  => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'utm_term'     => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'landing_page' => array(
							'type'              => 'string',
							'sanitize_callback' => 'esc_url_raw',
						),
						'referrer'     => array(
							'type'              => 'string',
							'sanitize_callback' => 'esc_url_raw',
						),
					),
				),
			)
		);

		// POST /tenprojects/v1/assessment/answer
		register_rest_route(
			$this->namespace,
			'/assessment/answer',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'submit_answer' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => array(
						'session_uuid' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $value );
							},
						),
						'question_key' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								$allowed = array(
									'configuration', 'location', 'budget',
									'purpose', 'timeline', 'funding', 'priorities',
									'emi_comfort', 'possession', 'must_haves', 'additional_notes',
								);
								return in_array( $value, $allowed, true );
							},
						),
						'value'        => array(
							'required' => true,
						),
					),
				),
			)
		);

		// GET /tenprojects/v1/assessment/session/<uuid>
		register_rest_route(
			$this->namespace,
			'/assessment/session/(?P<uuid>[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_session' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => array(
						'uuid' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);

		// POST /tenprojects/v1/assessment/advance
		register_rest_route(
			$this->namespace,
			'/assessment/advance',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'advance_phase' ),
					'permission_callback' => array( $this, 'public_permissions' ),
					'args'                => array(
						'session_uuid' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $value );
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Start a new assessment session.
	 *
	 * Gets or creates a customer from the visitor_id cookie and optional UTM
	 * parameters, then creates a new AI session and returns the first question.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function start_assessment( $request ) {
		$visitor_id = $request->get_param( 'visitor_id' ) ?: $this->customer_service->get_or_create_visitor_id();

		// Build customer data from request parameters.
		$customer_data = array(
			'visitor_id'   => $visitor_id,
			'utm_source'   => $request->get_param( 'utm_source' ) ?? '',
			'utm_medium'   => $request->get_param( 'utm_medium' ) ?? '',
			'utm_campaign' => $request->get_param( 'utm_campaign' ) ?? '',
			'utm_content'  => $request->get_param( 'utm_content' ) ?? '',
			'utm_term'     => $request->get_param( 'utm_term' ) ?? '',
			'landing_page' => $request->get_param( 'landing_page' ) ?? '',
			'referrer'     => $request->get_param( 'referrer' ) ?? '',
		);

		// Find or create the customer.
		$customer = $this->customer_service->find_or_create( $customer_data );
		if ( ! $customer ) {
			return $this->error( 'customer_error', 'Could not create customer record.', 500 );
		}

		// Check for an existing active session and return it.
		$active_session = $this->session_service->get_active_session( $customer->id );
		if ( $active_session ) {
			return $this->success( $this->format_session_start( $active_session, false ) );
		}

		// Start a new session.
		$city    = $request->get_param( 'city' ) ?: 'navi_mumbai';
		$session = $this->session_service->start( $customer->id, $city );

		if ( ! $session ) {
			return $this->error( 'session_error', 'Could not start assessment session.', 500 );
		}

		return $this->success( $this->format_session_start( $session, true ), 201 );
	}

	/**
	 * Submit an answer for the current question.
	 *
	 * Processes the answer through Session_Service and triggers recommendation
	 * generation when a phase completes.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function submit_answer( $request ) {
		// Validate required fields.
		$valid = $this->validate_required( $request, array( 'session_uuid', 'question_key', 'value' ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$session_uuid = $request->get_param( 'session_uuid' );
		$question_key = $request->get_param( 'question_key' );
		$value        = $request->get_param( 'value' );

		// Verify session exists.
		$session = $this->session_service->find_by_uuid( $session_uuid );
		if ( ! $session ) {
			return $this->error( 'session_not_found', 'Assessment session not found.', 404 );
		}

		if ( 'active' !== $session->status ) {
			return $this->error( 'session_completed', 'This assessment session has already been completed.', 409 );
		}

		// Sanitize array values if needed.
		if ( is_array( $value ) ) {
			$value = array_map( 'sanitize_text_field', $value );
		} elseif ( is_string( $value ) ) {
			$value = sanitize_text_field( $value );
		}

		// Process the answer.
		$result = $this->session_service->process_answer(
			$session_uuid,
			array(
				'question_key' => $question_key,
				'value'        => $value,
			)
		);

		// Check for service-level errors.
		if ( isset( $result['error'] ) ) {
			return $this->error( 'answer_error', $result['error'], 400 );
		}

		// If the phase is complete, generate recommendations.
		$recommendations = null;
		if ( ! empty( $result['phase_complete'] ) && ! empty( $result['requirement_id'] ) ) {
			$requirement = $this->get_requirement( $result['requirement_id'] );

			if ( $requirement ) {
				$phase = $result['phase'] ?? 1;

				if ( 1 === $phase ) {
					// Phase 1 complete: quick match (top 5).
					$recommendations = $this->recommendation_engine->quick_match( $requirement );
				} else {
					// Phase 2 or 3 complete: full recommendation (top 10).
					$recommendations = $this->recommendation_engine->generate( $requirement );
				}
			}
		}

		// Build response.
		$response_data = array(
			'session_uuid'     => $result['session_uuid'],
			'phase'            => $result['phase'],
			'phase_complete'   => $result['phase_complete'],
			'phases_completed' => $result['phases_completed'],
			'match_accuracy'   => $result['match_accuracy'],
		);

		if ( $result['phase_complete'] ) {
			$response_data['next_phase']   = $result['next_phase'];
			$response_data['results_ready'] = true;

			if ( $recommendations ) {
				$response_data['recommendation_id'] = $recommendations['recommendation_id'] ?? null;
				$response_data['total_candidates']  = $recommendations['total_candidates'] ?? 0;
				$response_data['total_eligible']    = $recommendations['total_eligible'] ?? 0;
				$response_data['results']           = $recommendations['results'] ?? array();
			}
		} else {
			$response_data['next_question'] = $this->get_next_question_data(
				$result['phase'],
				$result['next_question']
			);
		}

		return $this->success( $response_data );
	}

	/**
	 * Get session status by UUID.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_session( $request ) {
		$uuid = $request->get_param( 'uuid' );

		$session = $this->session_service->find_by_uuid( $uuid );
		if ( ! $session ) {
			return $this->error( 'session_not_found', 'Assessment session not found.', 404 );
		}

		$profile = json_decode( $session->profile_data, true ) ?: array();

		$response_data = array(
			'uuid'             => $session->uuid,
			'status'           => $session->status,
			'current_phase'    => (int) $session->current_phase,
			'current_question' => (int) $session->current_question,
			'phases_completed' => (int) $session->phases_completed,
			'match_accuracy'   => (int) $session->match_accuracy,
			'profile'          => $profile,
			'started_at'       => $session->started_at,
			'completed_at'     => $session->completed_at ?? null,
		);

		// Include current question data if session is still active.
		if ( 'active' === $session->status ) {
			$response_data['current_question_data'] = $this->get_next_question_data(
				(int) $session->current_phase,
				$this->get_question_key_from_number( (int) $session->current_phase, (int) $session->current_question )
			);
		}

		return $this->success( $response_data );
	}

	/**
	 * Advance the session to the next phase.
	 *
	 * Called when the user chooses to continue refining their results after
	 * viewing partial recommendations.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function advance_phase( $request ) {
		$valid = $this->validate_required( $request, array( 'session_uuid' ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$session_uuid = $request->get_param( 'session_uuid' );

		// Verify session exists.
		$session = $this->session_service->find_by_uuid( $session_uuid );
		if ( ! $session ) {
			return $this->error( 'session_not_found', 'Assessment session not found.', 404 );
		}

		if ( 'active' !== $session->status ) {
			return $this->error( 'session_completed', 'This assessment session has already been completed.', 409 );
		}

		$current_phase = (int) $session->current_phase;
		if ( $current_phase > 3 ) {
			return $this->error( 'no_more_phases', 'All assessment phases have been completed.', 409 );
		}

		// Advance through the Session_Service.
		$result = $this->session_service->advance_phase( $session_uuid );

		if ( isset( $result['error'] ) ) {
			return $this->error( 'advance_error', $result['error'], 400 );
		}

		$response_data = array(
			'session_uuid'  => $session_uuid,
			'phase'         => $result['phase'],
			'question_key'  => $result['question_key'],
			'question_data' => $result['question_data'],
		);

		return $this->success( $response_data );
	}

	/**
	 * Format session data for the start response.
	 *
	 * @param object $session    Session DB row.
	 * @param bool   $is_new     Whether this is a newly created session.
	 * @return array Formatted response data.
	 */
	private function format_session_start( $session, $is_new ) {
		$question_key = $this->get_question_key_from_number(
			(int) $session->current_phase,
			(int) $session->current_question
		);

		return array(
			'session_uuid'     => $session->uuid,
			'is_new_session'   => $is_new,
			'current_phase'    => (int) $session->current_phase,
			'current_question' => (int) $session->current_question,
			'phases_completed' => (int) $session->phases_completed,
			'match_accuracy'   => (int) $session->match_accuracy,
			'phase_info'       => array(
				'phase'       => (int) $session->current_phase,
				'title'       => $this->get_phase_title( (int) $session->current_phase ),
				'description' => $this->get_phase_description( (int) $session->current_phase ),
			),
			'first_question'   => array(
				'question_key'  => $question_key,
				'question_data' => $this->get_next_question_data(
					(int) $session->current_phase,
					$question_key
				),
			),
		);
	}

	/**
	 * Get question data for the next question.
	 *
	 * Uses Session_Service's question map via advance_phase pattern.
	 *
	 * @param int    $phase        Phase number.
	 * @param string $question_key Question key.
	 * @return array|null Question data or null.
	 */
	private function get_next_question_data( $phase, $question_key ) {
		if ( empty( $question_key ) ) {
			return null;
		}

		// Map question key back to question number in the phase.
		$question_number = $this->get_question_number( $phase, $question_key );
		if ( ! $question_number ) {
			return null;
		}

		// Create a temporary session to get question data via advance_phase.
		// We use a direct question data lookup approach instead.
		$questions = $this->get_all_question_data();

		if ( isset( $questions[ $question_key ] ) ) {
			return array_merge(
				$questions[ $question_key ],
				array(
					'question_key' => $question_key,
					'phase'        => $phase,
					'position'     => $question_number,
				)
			);
		}

		return null;
	}

	/**
	 * Map question key to question number within its phase.
	 *
	 * @param int    $phase Phase number.
	 * @param string $key   Question key.
	 * @return int|null Question number or null.
	 */
	private function get_question_number( $phase, $key ) {
		$map = array(
			1 => array( 'configuration' => 1, 'location' => 2, 'budget' => 3 ),
			2 => array( 'purpose' => 1, 'timeline' => 2, 'funding' => 3, 'priorities' => 4 ),
			3 => array( 'emi_comfort' => 1, 'possession' => 2, 'must_haves' => 3, 'additional_notes' => 4 ),
		);

		return $map[ $phase ][ $key ] ?? null;
	}

	/**
	 * Map phase and question number to question key.
	 *
	 * @param int $phase    Phase number (1-3).
	 * @param int $question Question number within phase.
	 * @return string Question key.
	 */
	private function get_question_key_from_number( $phase, $question ) {
		$map = array(
			1 => array( 1 => 'configuration', 2 => 'location', 3 => 'budget' ),
			2 => array( 1 => 'purpose', 2 => 'timeline', 3 => 'funding', 4 => 'priorities' ),
			3 => array( 1 => 'emi_comfort', 2 => 'possession', 3 => 'must_haves', 4 => 'additional_notes' ),
		);

		return $map[ $phase ][ $question ] ?? '';
	}

	/**
	 * Get phase title.
	 *
	 * @param int $phase Phase number.
	 * @return string Phase title.
	 */
	private function get_phase_title( $phase ) {
		$titles = array(
			1 => 'Quick Match',
			2 => 'Refined Match',
			3 => 'Perfect Match',
		);

		return $titles[ $phase ] ?? '';
	}

	/**
	 * Get phase description.
	 *
	 * @param int $phase Phase number.
	 * @return string Phase description.
	 */
	private function get_phase_description( $phase ) {
		$descriptions = array(
			1 => 'Answer 3 quick questions to get your initial project matches.',
			2 => 'Tell us more about your needs to refine your recommendations.',
			3 => 'Fine-tune your preferences for the most accurate matches.',
		);

		return $descriptions[ $phase ] ?? '';
	}

	/**
	 * Get all question definitions.
	 *
	 * Mirrors the question data in Session_Service for API response formatting.
	 *
	 * @return array Keyed question data.
	 */
	private function get_all_question_data() {
		return array(
			'configuration' => array(
				'title'   => 'What are you looking for?',
				'type'    => 'chips',
				'multi'   => false,
				'options' => array(
					array( 'value' => '1_bhk', 'label' => '1 BHK' ),
					array( 'value' => '2_bhk', 'label' => '2 BHK' ),
					array( 'value' => '3_bhk', 'label' => '3 BHK' ),
					array( 'value' => '4_bhk_plus', 'label' => '4 BHK+' ),
				),
			),
			'location' => array(
				'title'   => 'Where in Navi Mumbai?',
				'type'    => 'chips',
				'multi'   => true,
				'options' => array(
					array( 'value' => 'kharghar', 'label' => 'Kharghar' ),
					array( 'value' => 'panvel', 'label' => 'Panvel' ),
					array( 'value' => 'taloja', 'label' => 'Taloja' ),
					array( 'value' => 'ulwe', 'label' => 'Ulwe' ),
					array( 'value' => 'nerul', 'label' => 'Nerul' ),
					array( 'value' => 'seawoods', 'label' => 'Seawoods' ),
					array( 'value' => 'airoli', 'label' => 'Airoli' ),
					array( 'value' => 'vashi', 'label' => 'Vashi' ),
					array( 'value' => 'kamothe', 'label' => 'Kamothe' ),
					array( 'value' => 'ghansoli', 'label' => 'Ghansoli' ),
					array( 'value' => 'sanpada', 'label' => 'Sanpada' ),
					array( 'value' => 'kopar_khairane', 'label' => 'Kopar Khairane' ),
					array( 'value' => 'dronagiri', 'label' => 'Dronagiri' ),
					array( 'value' => 'new_panvel', 'label' => 'New Panvel' ),
					array( 'value' => 'roadpali', 'label' => 'Roadpali' ),
				),
			),
			'budget' => array(
				'title'   => "What's your budget?",
				'type'    => 'chips',
				'multi'   => false,
				'options' => array(
					array( 'value' => 'under_50l', 'label' => 'Under ₹50L' ),
					array( 'value' => '50l_75l', 'label' => '₹50L–₹75L' ),
					array( 'value' => '75l_1cr', 'label' => '₹75L–₹1Cr' ),
					array( 'value' => '1cr_1.5cr', 'label' => '₹1Cr–₹1.5Cr' ),
					array( 'value' => '1.5cr_2cr', 'label' => '₹1.5Cr–₹2Cr' ),
					array( 'value' => '2cr_plus', 'label' => '₹2Cr+' ),
				),
			),
			'purpose' => array(
				'title'   => 'Are you looking for a home or an investment?',
				'type'    => 'cards',
				'multi'   => false,
				'options' => array(
					array( 'value' => 'end_use', 'label' => 'Home', 'desc' => 'To live in' ),
					array( 'value' => 'investment', 'label' => 'Investment', 'desc' => 'For returns' ),
					array( 'value' => 'both', 'label' => 'Both', 'desc' => 'Live now, sell later' ),
				),
			),
			'timeline' => array(
				'title'   => 'When are you planning to buy?',
				'type'    => 'chips',
				'multi'   => false,
				'options' => array(
					array( 'value' => 'asap', 'label' => 'ASAP' ),
					array( 'value' => '1_3_months', 'label' => '1–3 months' ),
					array( 'value' => '3_6_months', 'label' => '3–6 months' ),
					array( 'value' => '6_12_months', 'label' => '6–12 months' ),
					array( 'value' => 'just_researching', 'label' => 'Just researching' ),
				),
			),
			'funding' => array(
				'title'   => 'How are you funding this?',
				'type'    => 'chips',
				'multi'   => false,
				'options' => array(
					array( 'value' => 'home_loan', 'label' => 'Home Loan' ),
					array( 'value' => 'self_funded', 'label' => 'Self-funded' ),
					array( 'value' => 'mix', 'label' => 'Mix of both' ),
				),
			),
			'priorities' => array(
				'title'   => 'What matters most? Pick your top 3.',
				'type'    => 'cards',
				'multi'   => true,
				'max'     => 3,
				'options' => array(
					array( 'value' => 'best_price', 'label' => 'Best Price' ),
					array( 'value' => 'best_location', 'label' => 'Best Location' ),
					array( 'value' => 'trusted_developer', 'label' => 'Trusted Developer' ),
					array( 'value' => 'early_possession', 'label' => 'Early Possession' ),
					array( 'value' => 'family_friendly', 'label' => 'Family Friendly' ),
					array( 'value' => 'low_risk', 'label' => 'Low Risk' ),
				),
			),
			'emi_comfort' => array(
				'title'   => 'What monthly EMI are you comfortable with?',
				'type'    => 'chips',
				'multi'   => false,
				'options' => array(
					array( 'value' => '20k_30k', 'label' => '₹20–30K' ),
					array( 'value' => '30k_50k', 'label' => '₹30–50K' ),
					array( 'value' => '50k_70k', 'label' => '₹50–70K' ),
					array( 'value' => '70k_1l', 'label' => '₹70K–1L' ),
					array( 'value' => '1l_plus', 'label' => '₹1L+' ),
				),
			),
			'possession' => array(
				'title'   => 'How long can you wait for possession?',
				'type'    => 'chips',
				'multi'   => false,
				'options' => array(
					array( 'value' => 'ready_now', 'label' => 'Ready now' ),
					array( 'value' => '1_2_years', 'label' => '1–2 years' ),
					array( 'value' => '2_3_years', 'label' => '2–3 years' ),
					array( 'value' => '3_5_years', 'label' => '3–5 years' ),
					array( 'value' => 'can_wait', 'label' => 'Can wait for value' ),
				),
			),
			'must_haves' => array(
				'title'   => 'Any must-haves? Select all that apply.',
				'type'    => 'chips',
				'multi'   => true,
				'options' => array(
					array( 'value' => 'near_railway', 'label' => 'Near railway station' ),
					array( 'value' => 'near_school', 'label' => 'Near school' ),
					array( 'value' => 'near_hospital', 'label' => 'Near hospital' ),
					array( 'value' => 'parking', 'label' => 'Parking included' ),
					array( 'value' => 'established_developer', 'label' => 'Established developer only' ),
					array( 'value' => 'rera_registered', 'label' => 'RERA registered only' ),
					array( 'value' => 'low_density', 'label' => 'Low density' ),
					array( 'value' => 'gated_community', 'label' => 'Gated community' ),
					array( 'value' => 'nothing_specific', 'label' => 'Nothing specific' ),
				),
			),
			'additional_notes' => array(
				'title'       => 'Anything else we should know?',
				'type'        => 'textarea',
				'multi'       => false,
				'placeholder' => 'e.g., "I need east-facing flat", "Ground floor for elderly parents"',
			),
		);
	}

	/**
	 * Retrieve a requirement record by ID.
	 *
	 * @param int $requirement_id Requirement ID.
	 * @return object|null Requirement DB row.
	 */
	private function get_requirement( $requirement_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tp_customer_requirements WHERE id = %d",
				absint( $requirement_id )
			)
		);
	}
}
