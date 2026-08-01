<?php
/**
 * Lead Qualifier — classification, urgency levels, auto-routing eligibility.
 *
 * Determines a lead's primary classification and secondary tags based on
 * quality score, customer data, and requirement profile. Provides urgency
 * levels for SLA enforcement and decides whether a lead should be auto-routed
 * or held for manual review.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

class Lead_Qualifier {

    /**
     * All possible classification values.
     *
     * The order matters: earlier entries take priority when determining
     * the primary classification.
     *
     * @var string[]
     */
    private const CLASSIFICATIONS = array(
        'site_visit_ready',
        'qualified',
        'warm',
        'investor',
        'nri',
        'high_budget',
        'loan_dependent',
        'researching',
        'long_term_nurture',
    );

    /**
     * Urgency mapping per classification.
     *
     * @var array<string, string>
     */
    private const URGENCY_MAP = array(
        'site_visit_ready'  => 'high',
        'qualified'         => 'high',
        'warm'              => 'medium',
        'investor'          => 'medium',
        'nri'               => 'medium',
        'high_budget'       => 'high',
        'loan_dependent'    => 'medium',
        'researching'       => 'low',
        'long_term_nurture' => 'low',
    );

    /**
     * Classifications eligible for automatic routing.
     *
     * @var string[]
     */
    private const AUTO_ROUTE = array(
        'site_visit_ready',
        'qualified',
        'high_budget',
        'investor',
    );

    /**
     * Classify a lead based on quality score, customer profile, and requirement.
     *
     * A lead receives one primary classification and zero or more secondary
     * tags. The primary is the highest-priority match from the ordered rules.
     *
     * Classification rules (evaluated in priority order):
     *   - site_visit_ready: quality >= 70 AND lead_type is site_visit
     *   - qualified:        quality >= 60 AND timeline <= 6 months AND budget provided AND phone verified
     *   - warm:             quality >= 40 AND assessment completed
     *   - investor:         purpose == 'investment'
     *   - nri:              phone starts with non-Indian code OR self-declared
     *   - high_budget:      budget_maximum >= 2 Cr (20000000)
     *   - loan_dependent:   funding_type == 'loan' AND loan_preapproved != 'yes'
     *   - researching:      timeline > 6 months OR quality < 40
     *   - long_term_nurture: timeline == 'just_researching' OR timeline > 12 months
     *
     * @param object      $lead        Lead data (must include quality_score, lead_type).
     * @param object      $customer    Customer row from tp_customers.
     * @param object|null $requirement Latest requirement from tp_customer_requirements.
     * @return array {
     *     @type string   $primary Primary classification.
     *     @type string[] $tags    All matching classifications (including primary).
     * }
     */
    public function classify( $lead, $customer, $requirement = null ) {
        $quality_score = isset( $lead->quality_score )
            ? (int) $lead->quality_score
            : 0;

        $lead_type = $lead->lead_type ?? '';

        // Requirement-derived values.
        $timeline_months  = $requirement && ! empty( $requirement->purchase_timeline_months )
            ? (int) $requirement->purchase_timeline_months
            : null;

        $budget_maximum   = $requirement && ! empty( $requirement->budget_maximum )
            ? (int) $requirement->budget_maximum
            : null;

        $purpose          = $requirement->purpose ?? null;
        $funding_type     = $requirement->funding_type ?? null;
        $loan_preapproved = $requirement->loan_preapproved ?? null;

        $phone_verified   = ! empty( $customer->phone_verified );
        $phone            = $customer->phone ?? '';

        // Check for completed assessment session.
        $assessment_completed = $this->has_completed_assessment( $customer );

        // ── Evaluate all classification rules ───────────────────────────────
        $tags = array();

        // Site visit ready.
        if ( $quality_score >= 70 && 'site_visit' === $lead_type ) {
            $tags[] = 'site_visit_ready';
        }

        // Qualified.
        if ( $quality_score >= 60
            && null !== $timeline_months && $timeline_months <= 6
            && null !== $budget_maximum
            && $phone_verified
        ) {
            $tags[] = 'qualified';
        }

        // Warm.
        if ( $quality_score >= 40 && $assessment_completed ) {
            $tags[] = 'warm';
        }

        // Investor.
        if ( 'investment' === $purpose ) {
            $tags[] = 'investor';
        }

        // NRI — phone starts with non-Indian country code.
        if ( $this->is_nri_phone( $phone ) ) {
            $tags[] = 'nri';
        }

        // High budget — 2 Cr = 20,000,000.
        if ( null !== $budget_maximum && $budget_maximum >= 20000000 ) {
            $tags[] = 'high_budget';
        }

        // Loan dependent.
        if ( 'loan' === $funding_type && 'yes' !== $loan_preapproved ) {
            $tags[] = 'loan_dependent';
        }

        // Researching.
        if ( ( null !== $timeline_months && $timeline_months > 6 ) || $quality_score < 40 ) {
            $tags[] = 'researching';
        }

        // Long-term nurture.
        if ( ( null !== $timeline_months && $timeline_months > 12 )
            || $this->is_just_researching_timeline( $requirement )
        ) {
            $tags[] = 'long_term_nurture';
        }

        // Deduplicate tags.
        $tags = array_unique( $tags );

        // Primary = first match in priority order.
        $primary = 'researching'; // default fallback.

        foreach ( self::CLASSIFICATIONS as $cls ) {
            if ( in_array( $cls, $tags, true ) ) {
                $primary = $cls;
                break;
            }
        }

        // Ensure primary is always in tags.
        if ( ! in_array( $primary, $tags, true ) ) {
            $tags[] = $primary;
        }

        return array(
            'primary' => $primary,
            'tags'    => array_values( $tags ),
        );
    }

    /**
     * Get the urgency level for a classification.
     *
     * Used to set SLA deadlines for partner response:
     *   - high:   Partner must respond within 2 hours.
     *   - medium: Partner must respond within 12 hours.
     *   - low:    Partner must respond within 48 hours.
     *
     * @param string $classification Lead classification.
     * @return string 'high', 'medium', or 'low'.
     */
    public function get_urgency_level( $classification ) {
        return self::URGENCY_MAP[ $classification ] ?? 'low';
    }

    /**
     * Determine if a lead should be auto-routed to partners.
     *
     * Auto-routed: site_visit_ready, qualified, high_budget, investor.
     * Manual review: researching, long_term_nurture, warm, nri, loan_dependent.
     *
     * @param string $classification Primary classification.
     * @return bool True if the lead should be auto-routed.
     */
    public function should_auto_route( $classification ) {
        return in_array( $classification, self::AUTO_ROUTE, true );
    }

    /**
     * Check if the customer has a completed AI assessment session.
     *
     * @param object $customer Customer row.
     * @return bool
     */
    private function has_completed_assessment( $customer ) {
        global $wpdb;

        if ( empty( $customer->id ) ) {
            return false;
        }

        $completed = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}tp_ai_sessions
                 WHERE customer_id = %d AND status = 'completed'",
                absint( $customer->id )
            )
        );

        return (int) $completed > 0;
    }

    /**
     * Detect NRI phone number (non-Indian country code).
     *
     * Indian numbers start with +91 or are 10 digits without a country code.
     * Any other international prefix is treated as NRI.
     *
     * @param string $phone Phone number.
     * @return bool True if the phone appears to be non-Indian.
     */
    private function is_nri_phone( $phone ) {
        if ( empty( $phone ) ) {
            return false;
        }

        $phone = preg_replace( '/[^0-9+]/', '', $phone );

        // If it starts with + and is NOT +91, it is an NRI number.
        if ( 0 === strpos( $phone, '+' ) && 0 !== strpos( $phone, '+91' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Check if the requirement timeline is "just researching".
     *
     * The purchase_timeline_months may be stored as 0 or null when the
     * customer selects a "just researching / not decided" option. We also
     * check if the raw timeline text indicates research-only intent.
     *
     * @param object|null $requirement Requirement row.
     * @return bool
     */
    private function is_just_researching_timeline( $requirement ) {
        if ( ! $requirement ) {
            return false;
        }

        // Timeline set to 0 or null with an active requirement = just researching.
        if ( isset( $requirement->purchase_timeline_months )
            && 0 === (int) $requirement->purchase_timeline_months
            && ! empty( $requirement->id )
        ) {
            return true;
        }

        // Check the possession_preference field for research keywords.
        $pref = strtolower( $requirement->possession_preference ?? '' );
        if ( false !== strpos( $pref, 'research' ) || false !== strpos( $pref, 'not decided' ) ) {
            return true;
        }

        return false;
    }
}
