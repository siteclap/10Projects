<?php
/**
 * Assessment session service — manages AI chat sessions, phase progression, requirement extraction.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

use TenProjects\Helpers\Sanitizer;

class Session_Service {

    /** @var array Phase question counts. */
    private const PHASE_QUESTIONS = array(
        1 => 3, // Config, Location, Budget
        2 => 4, // Purpose, Timeline, Funding, Priorities
        3 => 4, // EMI, Possession, Must-haves, Notes
    );

    /** @var array Budget range to numeric mapping. */
    private const BUDGET_MAP = array(
        'under_50l'  => array( 'comfortable' => 4000000, 'maximum' => 5000000 ),
        '50l_75l'    => array( 'comfortable' => 5000000, 'maximum' => 7500000 ),
        '75l_1cr'    => array( 'comfortable' => 7500000, 'maximum' => 10000000 ),
        '1cr_1.5cr'  => array( 'comfortable' => 10000000, 'maximum' => 15000000 ),
        '1.5cr_2cr'  => array( 'comfortable' => 15000000, 'maximum' => 20000000 ),
        '2cr_plus'   => array( 'comfortable' => 20000000, 'maximum' => 30000000 ),
    );

    /** @var array EMI range to numeric mapping. */
    private const EMI_MAP = array(
        '20k_30k' => 25000,
        '30k_50k' => 40000,
        '50k_70k' => 60000,
        '70k_1l'  => 85000,
        '1l_plus' => 120000,
    );

    /**
     * Start a new assessment session.
     *
     * @param int    $customer_id Customer ID.
     * @param string $city        City slug.
     * @return object Session record.
     */
    public function start( $customer_id, $city = 'navi_mumbai' ) {
        global $wpdb;

        $uuid = wp_generate_uuid4();

        $wpdb->insert(
            $wpdb->prefix . 'tp_ai_sessions',
            array(
                'uuid'            => $uuid,
                'customer_id'     => absint( $customer_id ),
                'current_phase'   => 1,
                'current_question' => 1,
                'phases_completed' => 0,
                'match_accuracy'  => 0,
                'messages'        => wp_json_encode( array() ),
                'profile_data'    => wp_json_encode( array( 'city' => $city ) ),
                'status'          => 'active',
                'started_at'      => current_time( 'mysql' ),
                'updated_at'      => current_time( 'mysql' ),
            )
        );

        return $this->find_by_uuid( $uuid );
    }

    /**
     * Find session by UUID.
     *
     * @param string $uuid Session UUID.
     * @return object|null
     */
    public function find_by_uuid( $uuid ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_ai_sessions WHERE uuid = %s",
                sanitize_text_field( $uuid )
            )
        );
    }

    /**
     * Find session by ID.
     *
     * @param int $id Session ID.
     * @return object|null
     */
    public function find( $id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_ai_sessions WHERE id = %d",
                absint( $id )
            )
        );
    }

    /**
     * Get active session for a customer.
     *
     * @param int $customer_id Customer ID.
     * @return object|null
     */
    public function get_active_session( $customer_id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_ai_sessions
                 WHERE customer_id = %d AND status = 'active'
                 ORDER BY started_at DESC LIMIT 1",
                absint( $customer_id )
            )
        );
    }

    /**
     * Process a user answer and advance the session.
     *
     * @param string $session_uuid Session UUID.
     * @param array  $answer       Answer data (question_key, value).
     * @return array Response with next_question, phase_complete, results_ready.
     */
    public function process_answer( $session_uuid, array $answer ) {
        $session = $this->find_by_uuid( $session_uuid );
        if ( ! $session || $session->status !== 'active' ) {
            return array( 'error' => 'Invalid or completed session.' );
        }

        $profile  = json_decode( $session->profile_data, true ) ?: array();
        $messages = json_decode( $session->messages, true ) ?: array();

        $question_key = sanitize_text_field( $answer['question_key'] ?? '' );
        $value        = $answer['value'] ?? null;

        // Store the answer in profile.
        $profile = $this->apply_answer( $profile, $question_key, $value );

        // Record message.
        $messages[] = array(
            'role'      => 'user',
            'question'  => $question_key,
            'value'     => $value,
            'timestamp' => current_time( 'mysql' ),
        );

        // Determine next question and phase status.
        $phase    = (int) $session->current_phase;
        $question = (int) $session->current_question;
        $next_q   = $question + 1;
        $phase_complete = false;

        // Check if current phase is complete.
        $max_q = self::PHASE_QUESTIONS[ $phase ] ?? 3;
        if ( $next_q > $max_q ) {
            $phase_complete = true;
            $phases_completed = $phase;
            $match_accuracy = $this->calculate_accuracy( $phases_completed );
        } else {
            $phases_completed = max( 0, $phase - 1 );
            $match_accuracy = $session->match_accuracy;
        }

        // Update session.
        $update = array(
            'messages'        => wp_json_encode( $messages ),
            'profile_data'    => wp_json_encode( $profile ),
            'match_accuracy'  => $match_accuracy,
            'updated_at'      => current_time( 'mysql' ),
        );

        if ( $phase_complete ) {
            $update['phases_completed'] = $phases_completed;
            if ( $phase < 3 ) {
                $update['current_phase']    = $phase + 1;
                $update['current_question'] = 1;
            } else {
                $update['status'] = 'completed';
                $update['completed_at'] = current_time( 'mysql' );
            }
        } else {
            $update['current_question'] = $next_q;
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'tp_ai_sessions',
            $update,
            array( 'id' => $session->id )
        );

        // Build requirement record if phase completed.
        $requirement_id = null;
        if ( $phase_complete ) {
            $requirement_id = $this->save_requirement( $session->customer_id, $profile, $phases_completed );
        }

        return array(
            'session_uuid'     => $session_uuid,
            'phase'            => $phase_complete ? $phase : $phase,
            'phase_complete'   => $phase_complete,
            'phases_completed' => $phase_complete ? $phases_completed : $phases_completed,
            'match_accuracy'   => $match_accuracy,
            'next_phase'       => $phase_complete && $phase < 3 ? $phase + 1 : null,
            'next_question'    => ! $phase_complete ? $this->get_question_key( $phase, $next_q ) : null,
            'results_ready'    => $phase_complete,
            'requirement_id'   => $requirement_id,
            'profile'          => $profile,
        );
    }

    /**
     * Advance session to next phase (user opted in).
     *
     * @param string $session_uuid Session UUID.
     * @return array First question of next phase.
     */
    public function advance_phase( $session_uuid ) {
        $session = $this->find_by_uuid( $session_uuid );
        if ( ! $session ) {
            return array( 'error' => 'Session not found.' );
        }

        $phase = (int) $session->current_phase;

        return array(
            'phase'         => $phase,
            'question_key'  => $this->get_question_key( $phase, 1 ),
            'question_data' => $this->get_question_data( $phase, 1 ),
        );
    }

    /**
     * Apply an answer to the profile data.
     *
     * @param array  $profile      Current profile.
     * @param string $question_key Question key.
     * @param mixed  $value        Answer value.
     * @return array Updated profile.
     */
    private function apply_answer( array $profile, $question_key, $value ) {
        switch ( $question_key ) {
            case 'configuration':
                $profile['configuration'] = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array( sanitize_text_field( $value ) );
                break;

            case 'location':
                $profile['preferred_locations'] = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array( sanitize_text_field( $value ) );
                break;

            case 'budget':
                $range = sanitize_text_field( $value );
                $profile['budget_range'] = $range;
                if ( isset( self::BUDGET_MAP[ $range ] ) ) {
                    $profile['budget_comfortable'] = self::BUDGET_MAP[ $range ]['comfortable'];
                    $profile['budget_maximum']     = self::BUDGET_MAP[ $range ]['maximum'];
                }
                break;

            case 'purpose':
                $profile['purpose'] = sanitize_text_field( $value );
                break;

            case 'timeline':
                $map = array(
                    'asap'          => 1,
                    '1_3_months'    => 3,
                    '3_6_months'    => 6,
                    '6_12_months'   => 12,
                    'just_researching' => 24,
                );
                $key = sanitize_text_field( $value );
                $profile['purchase_timeline']        = $key;
                $profile['purchase_timeline_months']  = $map[ $key ] ?? 6;
                break;

            case 'funding':
                $profile['funding_type'] = sanitize_text_field( $value );
                break;

            case 'priorities':
                $profile['priorities'] = array();
                if ( is_array( $value ) ) {
                    foreach ( $value as $rank => $factor ) {
                        $profile['priorities'][] = array(
                            'factor' => sanitize_text_field( $factor ),
                            'rank'   => $rank + 1,
                        );
                    }
                }
                break;

            case 'emi_comfort':
                $key = sanitize_text_field( $value );
                $profile['emi_comfort_range']  = $key;
                $profile['monthly_emi_comfort'] = self::EMI_MAP[ $key ] ?? 50000;
                break;

            case 'possession':
                $map = array(
                    'ready_now'  => (int) gmdate( 'Y' ),
                    '1_2_years'  => (int) gmdate( 'Y' ) + 2,
                    '2_3_years'  => (int) gmdate( 'Y' ) + 3,
                    '3_5_years'  => (int) gmdate( 'Y' ) + 5,
                    'can_wait'   => (int) gmdate( 'Y' ) + 8,
                );
                $key = sanitize_text_field( $value );
                $profile['possession_preference'] = $key;
                $profile['max_possession_year']   = $map[ $key ] ?? ( (int) gmdate( 'Y' ) + 5 );
                break;

            case 'must_haves':
                $profile['must_haves'] = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
                break;

            case 'additional_notes':
                $profile['additional_notes'] = Sanitizer::textarea( $value ?? '' );
                break;
        }

        return $profile;
    }

    /**
     * Get the question key for a given phase and question number.
     *
     * @param int $phase    Phase number (1-3).
     * @param int $question Question number within phase.
     * @return string Question key.
     */
    private function get_question_key( $phase, $question ) {
        $map = array(
            1 => array( 1 => 'configuration', 2 => 'location', 3 => 'budget' ),
            2 => array( 1 => 'purpose', 2 => 'timeline', 3 => 'funding', 4 => 'priorities' ),
            3 => array( 1 => 'emi_comfort', 2 => 'possession', 3 => 'must_haves', 4 => 'additional_notes' ),
        );

        return $map[ $phase ][ $question ] ?? '';
    }

    /**
     * Get question display data for the frontend.
     *
     * @param int $phase    Phase.
     * @param int $question Question number.
     * @return array Question data (title, type, options).
     */
    private function get_question_data( $phase, $question ) {
        $key = $this->get_question_key( $phase, $question );

        $questions = array(
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
                'title'   => 'Anything else we should know?',
                'type'    => 'textarea',
                'multi'   => false,
                'placeholder' => 'e.g., "I need east-facing flat", "Ground floor for elderly parents"',
            ),
        );

        return $questions[ $key ] ?? array();
    }

    /**
     * Calculate match accuracy based on phases completed.
     *
     * @param int $phases_completed Number of completed phases.
     * @return int Accuracy percentage.
     */
    private function calculate_accuracy( $phases_completed ) {
        $map = array( 1 => 72, 2 => 89, 3 => 96 );
        return $map[ $phases_completed ] ?? 0;
    }

    /**
     * Save/update customer requirement from profile data.
     *
     * @param int   $customer_id      Customer ID.
     * @param array $profile          Profile data.
     * @param int   $phases_completed Phases completed.
     * @return int Requirement ID.
     */
    private function save_requirement( $customer_id, array $profile, $phases_completed ) {
        global $wpdb;

        $table = $wpdb->prefix . 'tp_customer_requirements';

        // Check for existing requirement for this customer.
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table} WHERE customer_id = %d ORDER BY created_at DESC LIMIT 1",
                absint( $customer_id )
            )
        );

        $data = array(
            'customer_id'        => absint( $customer_id ),
            'city'               => $profile['city'] ?? 'navi_mumbai',
            'configurations'     => wp_json_encode( $profile['configuration'] ?? array() ),
            'preferred_locations' => wp_json_encode( $profile['preferred_locations'] ?? array() ),
            'budget_comfortable' => $profile['budget_comfortable'] ?? 0,
            'budget_maximum'     => $profile['budget_maximum'] ?? 0,
            'purpose'            => $profile['purpose'] ?? 'both',
            'purchase_timeline_months' => $profile['purchase_timeline_months'] ?? 6,
            'funding_type'       => $profile['funding_type'] ?? 'home_loan',
            'monthly_emi_comfort' => $profile['monthly_emi_comfort'] ?? 0,
            'max_possession_year' => $profile['max_possession_year'] ?? ( (int) gmdate( 'Y' ) + 5 ),
            'priorities'         => wp_json_encode( $profile['priorities'] ?? array() ),
            'must_haves'         => wp_json_encode( $profile['must_haves'] ?? array() ),
            'additional_notes'   => $profile['additional_notes'] ?? '',
            'phases_completed'   => $phases_completed,
            'updated_at'         => current_time( 'mysql' ),
        );

        if ( $existing ) {
            $wpdb->update( $table, $data, array( 'id' => $existing ) );
            return (int) $existing;
        }

        $data['uuid']       = wp_generate_uuid4();
        $data['created_at'] = current_time( 'mysql' );
        $wpdb->insert( $table, $data );

        return $wpdb->insert_id;
    }

    /**
     * Get all sessions for a customer.
     *
     * @param int $customer_id Customer ID.
     * @return array Sessions.
     */
    public function get_customer_sessions( $customer_id ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, uuid, current_phase, phases_completed, match_accuracy, status, started_at, completed_at
                 FROM {$wpdb->prefix}tp_ai_sessions
                 WHERE customer_id = %d ORDER BY started_at DESC",
                absint( $customer_id )
            )
        );
    }
}
