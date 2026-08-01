<?php
/**
 * Lead Service — creation, deduplication, quality scoring, status management.
 *
 * Handles the full lead lifecycle from creation through status updates.
 * Integrates with Lead_Qualifier for classification and Lead_Router for
 * automatic partner routing.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

use TenProjects\Helpers\Sanitizer;

class Lead_Service {

    /** @var string[] Valid lead types matching tp_leads ENUM. */
    private const LEAD_TYPES = array(
        'ai_recommendation',
        'site_visit',
        'best_price',
        'callback',
        'advisor',
        'whatsapp',
        'report_download',
        'emi_check',
        'organic',
        'direct',
    );

    /** @var string[] Valid lead statuses matching tp_leads ENUM. */
    private const STATUSES = array(
        'new',
        'assigned',
        'contacted',
        'site_visit_scheduled',
        'site_visit_done',
        'negotiating',
        'booked',
        'lost',
        'invalid',
        'duplicate',
        'refunded',
    );

    /**
     * Create a new lead.
     *
     * Runs deduplication, calculates quality score, classifies the lead,
     * stores it in tp_leads, fires the `tp_lead_created` action, and
     * returns the lead object.
     *
     * @param array $data {
     *     Lead data.
     *
     *     @type int    $customer_id  Required. Customer ID.
     *     @type int    $project_id   Optional. Associated project CPT ID.
     *     @type string $lead_type    Required. One of LEAD_TYPES.
     *     @type string $source_page  Optional. Page type where lead was captured.
     *     @type string $source_url   Optional. Full URL where lead was captured.
     * }
     * @return object|\WP_Error Lead row on success, WP_Error on failure or duplicate.
     */
    public function create( array $data ) {
        global $wpdb;

        // --- Validate required fields ----------------------------------------
        if ( empty( $data['customer_id'] ) ) {
            return new \WP_Error( 'missing_customer', __( 'Customer ID is required.', 'tenprojects' ) );
        }

        if ( empty( $data['lead_type'] ) || ! in_array( $data['lead_type'], self::LEAD_TYPES, true ) ) {
            return new \WP_Error( 'invalid_lead_type', __( 'Invalid lead type.', 'tenprojects' ) );
        }

        // --- Fetch customer --------------------------------------------------
        $customer = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_customers WHERE id = %d AND deleted_at IS NULL",
                absint( $data['customer_id'] )
            )
        );

        if ( ! $customer ) {
            return new \WP_Error( 'customer_not_found', __( 'Customer not found.', 'tenprojects' ) );
        }

        if ( empty( $customer->phone ) ) {
            return new \WP_Error( 'phone_required', __( 'Customer phone is required to create a lead.', 'tenprojects' ) );
        }

        // --- Deduplication ---------------------------------------------------
        $project_id = ! empty( $data['project_id'] ) ? absint( $data['project_id'] ) : null;

        if ( $this->is_duplicate( $customer->phone, $project_id ) ) {
            return new \WP_Error(
                'duplicate_lead',
                __( 'A lead for this phone and project already exists within the last 30 days.', 'tenprojects' )
            );
        }

        // --- Fetch latest requirement ----------------------------------------
        $requirement = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_customer_requirements
                 WHERE customer_id = %d AND is_active = 1
                 ORDER BY created_at DESC LIMIT 1",
                absint( $data['customer_id'] )
            )
        );

        // --- Quality score ---------------------------------------------------
        $quality_score = $this->calculate_quality_score( $data['customer_id'], $data );

        // --- Classification --------------------------------------------------
        $qualifier      = new Lead_Qualifier();
        $classification = $qualifier->classify(
            (object) array_merge( $data, array( 'quality_score' => $quality_score ) ),
            $customer,
            $requirement
        );

        // --- Build insert row ------------------------------------------------
        $now   = current_time( 'mysql' );
        $table = $wpdb->prefix . 'tp_leads';

        $insert = array(
            'lead_uuid'            => wp_generate_uuid4(),
            'customer_id'          => absint( $data['customer_id'] ),
            'requirement_id'       => $requirement ? absint( $requirement->id ) : null,
            'project_id'           => $project_id,
            'lead_type'            => sanitize_text_field( $data['lead_type'] ),
            'lead_source'          => isset( $data['source_page'] ) ? sanitize_text_field( $data['source_page'] ) : null,
            'customer_name'        => ! empty( $customer->name ) ? $customer->name : ( $customer->full_name ?? null ),
            'customer_phone'       => $customer->phone,
            'customer_email'       => $customer->email ?? null,
            'customer_city'        => $customer->city ?? null,
            'quality_score'        => $quality_score,
            'classification'       => $classification['primary'],
            'site_visit_intent'    => ( 'site_visit' === $data['lead_type'] ) ? 1 : 0,
            'status'               => 'new',
            'consent_contact'      => ! empty( $customer->consent_lead_sharing ) ? 1 : 0,
            'consent_share'        => ! empty( $customer->consent_lead_sharing ) ? 1 : 0,
            'consent_timestamp'    => $customer->consent_collected_at ?? null,
            'utm_source'           => $customer->utm_source ?? null,
            'utm_medium'           => $customer->utm_medium ?? null,
            'utm_campaign'         => $customer->utm_campaign ?? null,
            'utm_content'          => $customer->utm_content ?? null,
            'utm_term'             => $customer->utm_term ?? null,
            'landing_page'         => isset( $data['source_url'] ) ? esc_url_raw( $data['source_url'] ) : ( $customer->first_visit_url ?? null ),
            'referrer'             => $customer->referrer ?? null,
            'device_type'          => $customer->device_type ?? null,
            'created_at'           => $now,
            'updated_at'           => $now,
        );

        // Enrich from requirement if available.
        if ( $requirement ) {
            $insert['location_preference']   = $requirement->preferred_locations;
            $insert['budget_comfortable']    = $requirement->budget_comfortable;
            $insert['budget_maximum']        = $requirement->budget_maximum;
            $insert['configuration']         = $requirement->configuration;
            $insert['funding_type']          = $requirement->funding_type;
            $insert['down_payment']          = $requirement->down_payment;
            $insert['emi_comfort']           = $requirement->monthly_emi_comfort;
            $insert['purchase_timeline']     = $requirement->purchase_timeline_months
                ? $requirement->purchase_timeline_months . '_months'
                : null;
            $insert['possession_preference'] = $requirement->possession_preference;
            $insert['risk_profile']          = $requirement->risk_tolerance;
            $insert['purpose']               = $requirement->purpose;
        }

        $result = $wpdb->insert( $table, $insert );

        if ( false === $result ) {
            return new \WP_Error( 'db_error', __( 'Failed to create lead.', 'tenprojects' ) );
        }

        $lead_id = $wpdb->insert_id;
        $lead    = $this->find( $lead_id );

        // --- Audit log -------------------------------------------------------
        $this->log_audit( 'lead_created', 'lead', $lead_id, null, array(
            'lead_type'      => $lead->lead_type,
            'quality_score'  => $lead->quality_score,
            'classification' => $lead->classification,
        ) );

        /**
         * Fires after a lead is successfully created.
         *
         * @since 1.0.0
         *
         * @param object $lead           The lead row object.
         * @param array  $classification Classification result with primary + tags.
         */
        do_action( 'tp_lead_created', $lead, $classification );

        return $lead;
    }

    /**
     * Check if a lead is a duplicate.
     *
     * A lead is duplicate if the same phone + project combination already
     * exists within the given time window. If project_id is null, checks
     * for any lead with the same phone within the window.
     *
     * @param string   $phone       Customer phone number.
     * @param int|null $project_id  Project ID (null for project-agnostic check).
     * @param int      $window_days Deduplication window in days.
     * @return bool True if a duplicate exists.
     */
    public function is_duplicate( $phone, $project_id = null, $window_days = 30 ) {
        global $wpdb;

        $clean_phone = Sanitizer::phone( $phone );
        if ( empty( $clean_phone ) ) {
            return false;
        }

        $cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$window_days} days" ) );

        if ( null !== $project_id ) {
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}tp_leads
                     WHERE customer_phone = %s
                       AND project_id = %d
                       AND is_duplicate = 0
                       AND created_at >= %s
                     LIMIT 1",
                    $clean_phone,
                    absint( $project_id ),
                    $cutoff
                )
            );
        } else {
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}tp_leads
                     WHERE customer_phone = %s
                       AND is_duplicate = 0
                       AND created_at >= %s
                     LIMIT 1",
                    $clean_phone,
                    $cutoff
                )
            );
        }

        return ! empty( $existing );
    }

    /**
     * Calculate lead quality score (0-100).
     *
     * Scoring formula:
     *   Completeness (max 30): phone_verified +10, email +5, full profile +15
     *   Intent (max 40):       site_visit +15, best_price +10, advisor +5,
     *                          assessment_completed +5, multiple_views +3, comparison +2
     *   Engagement (max 20):   time_spent >3min +5, return_visit +5,
     *                          saved_projects +5, report_downloaded +5
     *   Profile (max 10):      timeline <3mo +5, loan_preapproved +3, clear_budget +2
     *
     * @param int   $customer_id Customer ID.
     * @param array $lead_data   Lead creation data (includes lead_type).
     * @return int Quality score 0-100.
     */
    public function calculate_quality_score( $customer_id, array $lead_data ) {
        global $wpdb;

        $prefix      = $wpdb->prefix;
        $customer_id = absint( $customer_id );

        // Fetch customer.
        $customer = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$prefix}tp_customers WHERE id = %d AND deleted_at IS NULL",
                $customer_id
            )
        );

        if ( ! $customer ) {
            return 0;
        }

        // Fetch latest active requirement.
        $requirement = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$prefix}tp_customer_requirements
                 WHERE customer_id = %d AND is_active = 1
                 ORDER BY created_at DESC LIMIT 1",
                $customer_id
            )
        );

        // Fetch latest AI session.
        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$prefix}tp_ai_sessions
                 WHERE customer_id = %d
                 ORDER BY started_at DESC LIMIT 1",
                $customer_id
            )
        );

        // ── Completeness (max 30) ──────────────────────────────────────────
        $completeness = 0;

        if ( ! empty( $customer->phone_verified ) ) {
            $completeness += 10;
        }

        if ( ! empty( $customer->email ) ) {
            $completeness += 5;
        }

        // Full requirement profile: has budget, configuration, location, and timeline.
        if ( $requirement
            && ! empty( $requirement->budget_maximum )
            && ! empty( $requirement->configuration )
            && ! empty( $requirement->preferred_locations )
            && ! empty( $requirement->purchase_timeline_months )
        ) {
            $completeness += 15;
        }

        $completeness = min( 30, $completeness );

        // ── Intent Signals (max 40) ─────────────────────────────────────────
        $intent    = 0;
        $lead_type = $lead_data['lead_type'] ?? '';

        if ( 'site_visit' === $lead_type ) {
            $intent += 15;
        }

        if ( 'best_price' === $lead_type ) {
            $intent += 10;
        }

        if ( 'advisor' === $lead_type ) {
            $intent += 5;
        }

        // Assessment completed.
        if ( $session && 'completed' === $session->status ) {
            $intent += 5;
        }

        // Multiple projects viewed (3+ distinct project page views).
        $views_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT( DISTINCT JSON_UNQUOTE( JSON_EXTRACT( event_data, '$.project_id' ) ) )
                 FROM {$prefix}tp_analytics_events
                 WHERE customer_id = %d
                   AND event_name = 'project_viewed'",
                $customer_id
            )
        );

        if ( $views_count >= 3 ) {
            $intent += 3;
        }

        // Comparison created.
        $has_comparison = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$prefix}tp_comparisons WHERE customer_id = %d",
                $customer_id
            )
        );

        if ( $has_comparison > 0 ) {
            $intent += 2;
        }

        $intent = min( 40, $intent );

        // ── Engagement (max 20) ─────────────────────────────────────────────
        $engagement = 0;

        // Time spent on assessment > 3 minutes.
        if ( $session && $session->started_at && $session->completed_at ) {
            $duration = strtotime( $session->completed_at ) - strtotime( $session->started_at );
            if ( $duration > 180 ) {
                $engagement += 5;
            }
        }

        // Return visit (more than one session on different days).
        $visit_days = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT( DISTINCT DATE( created_at ) )
                 FROM {$prefix}tp_analytics_events
                 WHERE customer_id = %d
                   AND event_name = 'page_view'",
                $customer_id
            )
        );

        if ( $visit_days >= 2 ) {
            $engagement += 5;
        }

        // Saved projects.
        $saved_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$prefix}tp_saved_projects WHERE customer_id = %d",
                $customer_id
            )
        );

        if ( $saved_count > 0 ) {
            $engagement += 5;
        }

        // Report downloaded.
        $report_downloaded = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$prefix}tp_analytics_events
                 WHERE customer_id = %d AND event_name = 'report_downloaded'",
                $customer_id
            )
        );

        if ( $report_downloaded > 0 ) {
            $engagement += 5;
        }

        $engagement = min( 20, $engagement );

        // ── Profile (max 10) ────────────────────────────────────────────────
        $profile = 0;

        if ( $requirement ) {
            // Timeline < 3 months.
            if ( ! empty( $requirement->purchase_timeline_months )
                && (int) $requirement->purchase_timeline_months <= 3
            ) {
                $profile += 5;
            }

            // Loan pre-approved.
            if ( 'yes' === ( $requirement->loan_preapproved ?? '' ) ) {
                $profile += 3;
            }

            // Clear budget range.
            if ( ! empty( $requirement->budget_maximum ) ) {
                $profile += 2;
            }
        }

        $profile = min( 10, $profile );

        return $completeness + $intent + $engagement + $profile;
    }

    /**
     * Update lead status with audit trail.
     *
     * @param int    $lead_id Lead ID.
     * @param string $status  New status (one of STATUSES).
     * @param string $notes   Optional status-change notes.
     * @return bool|\WP_Error True on success, WP_Error on failure.
     */
    public function update_status( $lead_id, $status, $notes = '' ) {
        global $wpdb;

        $lead_id = absint( $lead_id );

        if ( ! in_array( $status, self::STATUSES, true ) ) {
            return new \WP_Error( 'invalid_status', __( 'Invalid lead status.', 'tenprojects' ) );
        }

        $lead = $this->find( $lead_id );

        if ( ! $lead ) {
            return new \WP_Error( 'lead_not_found', __( 'Lead not found.', 'tenprojects' ) );
        }

        $old_status = $lead->status;

        $updated = $wpdb->update(
            $wpdb->prefix . 'tp_leads',
            array(
                'status'     => $status,
                'updated_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $lead_id )
        );

        if ( false === $updated ) {
            return new \WP_Error( 'db_error', __( 'Failed to update lead status.', 'tenprojects' ) );
        }

        // Audit log.
        $this->log_audit( 'lead_status_changed', 'lead', $lead_id,
            array( 'status' => $old_status ),
            array( 'status' => $status, 'notes' => sanitize_textarea_field( $notes ) )
        );

        /**
         * Fires after a lead status is updated.
         *
         * @since 1.0.0
         *
         * @param int    $lead_id    Lead ID.
         * @param string $status     New status.
         * @param string $old_status Previous status.
         * @param string $notes      Change notes.
         */
        do_action( 'tp_lead_status_changed', $lead_id, $status, $old_status, $notes );

        return true;
    }

    /**
     * Find a lead by ID.
     *
     * @param int $id Lead ID.
     * @return object|null Lead row or null.
     */
    public function find( $id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_leads WHERE id = %d",
                absint( $id )
            )
        );
    }

    /**
     * Get all leads for a given customer.
     *
     * @param int $customer_id Customer ID.
     * @return array Array of lead row objects.
     */
    public function find_by_customer( $customer_id ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tp_leads
                 WHERE customer_id = %d
                 ORDER BY created_at DESC",
                absint( $customer_id )
            )
        );
    }

    /**
     * Query leads with filters for admin views.
     *
     * @param array $filters {
     *     Optional. Filters to apply.
     *
     *     @type string $status         Lead status.
     *     @type string $classification Lead classification.
     *     @type string $date_from      Start date (Y-m-d).
     *     @type string $date_to        End date (Y-m-d).
     *     @type string $location       Location slug (matched against location_preference JSON).
     *     @type int    $partner_id     Filter by assigned partner ID.
     *     @type string $lead_type      Lead type filter.
     *     @type string $search         Search by name, phone, or email.
     *     @type int    $quality_min    Minimum quality score.
     *     @type int    $quality_max    Maximum quality score.
     * }
     * @param int   $page     Page number (1-based).
     * @param int   $per_page Results per page.
     * @return array {
     *     @type array $leads  Array of lead row objects.
     *     @type int   $total  Total matching leads.
     *     @type int   $pages  Total pages.
     * }
     */
    public function get_leads( array $filters = array(), $page = 1, $per_page = 20 ) {
        global $wpdb;

        $prefix = $wpdb->prefix;
        $where  = array( '1=1' );
        $values = array();

        // Status.
        if ( ! empty( $filters['status'] ) && in_array( $filters['status'], self::STATUSES, true ) ) {
            $where[]  = 'l.status = %s';
            $values[] = $filters['status'];
        }

        // Classification.
        if ( ! empty( $filters['classification'] ) ) {
            $where[]  = 'l.classification = %s';
            $values[] = sanitize_text_field( $filters['classification'] );
        }

        // Date range.
        if ( ! empty( $filters['date_from'] ) ) {
            $where[]  = 'l.created_at >= %s';
            $values[] = sanitize_text_field( $filters['date_from'] ) . ' 00:00:00';
        }

        if ( ! empty( $filters['date_to'] ) ) {
            $where[]  = 'l.created_at <= %s';
            $values[] = sanitize_text_field( $filters['date_to'] ) . ' 23:59:59';
        }

        // Location (JSON search in location_preference).
        if ( ! empty( $filters['location'] ) ) {
            $where[]  = "JSON_CONTAINS( l.location_preference, %s )";
            $values[] = wp_json_encode( sanitize_text_field( $filters['location'] ) );
        }

        // Partner ID (via assignments join).
        $join = '';
        if ( ! empty( $filters['partner_id'] ) ) {
            $join     = "INNER JOIN {$prefix}tp_lead_assignments la ON la.lead_id = l.id";
            $where[]  = 'la.partner_id = %d';
            $values[] = absint( $filters['partner_id'] );
        }

        // Lead type.
        if ( ! empty( $filters['lead_type'] ) && in_array( $filters['lead_type'], self::LEAD_TYPES, true ) ) {
            $where[]  = 'l.lead_type = %s';
            $values[] = $filters['lead_type'];
        }

        // Search (name, phone, email).
        if ( ! empty( $filters['search'] ) ) {
            $like     = '%' . $wpdb->esc_like( sanitize_text_field( $filters['search'] ) ) . '%';
            $where[]  = '( l.customer_name LIKE %s OR l.customer_phone LIKE %s OR l.customer_email LIKE %s )';
            $values[] = $like;
            $values[] = $like;
            $values[] = $like;
        }

        // Quality score range.
        if ( isset( $filters['quality_min'] ) && is_numeric( $filters['quality_min'] ) ) {
            $where[]  = 'l.quality_score >= %d';
            $values[] = absint( $filters['quality_min'] );
        }

        if ( isset( $filters['quality_max'] ) && is_numeric( $filters['quality_max'] ) ) {
            $where[]  = 'l.quality_score <= %d';
            $values[] = absint( $filters['quality_max'] );
        }

        $where_clause = implode( ' AND ', $where );

        // Count total.
        $count_sql = "SELECT COUNT( DISTINCT l.id ) FROM {$prefix}tp_leads l {$join} WHERE {$where_clause}";

        if ( ! empty( $values ) ) {
            $count_sql = $wpdb->prepare( $count_sql, $values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        $total = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        // Pagination.
        $page     = max( 1, absint( $page ) );
        $per_page = max( 1, min( 100, absint( $per_page ) ) );
        $offset   = ( $page - 1 ) * $per_page;

        // Fetch rows.
        $query = "SELECT DISTINCT l.* FROM {$prefix}tp_leads l {$join}
                  WHERE {$where_clause}
                  ORDER BY l.created_at DESC
                  LIMIT %d OFFSET %d";

        $values[] = $per_page;
        $values[] = $offset;

        $leads = $wpdb->get_results(
            $wpdb->prepare( $query, $values ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        );

        return array(
            'leads' => $leads,
            'total' => $total,
            'pages' => (int) ceil( $total / $per_page ),
        );
    }

    /**
     * Insert a row into the tp_audit_log table.
     *
     * @param string     $action      Action identifier.
     * @param string     $entity_type Entity type (e.g. 'lead').
     * @param int        $entity_id   Entity ID.
     * @param array|null $old_values  Previous values (null for creation).
     * @param array|null $new_values  New values.
     * @return void
     */
    private function log_audit( $action, $entity_type, $entity_id, $old_values = null, $new_values = null ) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'tp_audit_log',
            array(
                'user_id'     => get_current_user_id() ?: null,
                'user_type'   => get_current_user_id() ? 'admin' : 'system',
                'action'      => sanitize_text_field( $action ),
                'entity_type' => sanitize_text_field( $entity_type ),
                'entity_id'   => absint( $entity_id ),
                'old_values'  => $old_values ? wp_json_encode( $old_values ) : null,
                'new_values'  => $new_values ? wp_json_encode( $new_values ) : null,
                'ip_address'  => isset( $_SERVER['REMOTE_ADDR'] )
                    ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
                    : null,
                'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] )
                    ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
                    : null,
                'created_at'  => current_time( 'mysql' ),
            )
        );
    }
}
