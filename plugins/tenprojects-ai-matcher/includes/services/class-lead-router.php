<?php
/**
 * Lead Router — rule matching, partner selection, assignment, credit management.
 *
 * Routes qualified leads to the best-fit channel partners based on configurable
 * routing rules, partner capacity, availability, and performance metrics.
 * Supports both exclusive (1 partner) and shared (up to 3 partners) distribution.
 *
 * @package TenProjects
 * @since   1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

class Lead_Router {

    /** @var int Maximum partners for shared lead distribution. */
    private const MAX_SHARED_PARTNERS = 3;

    /**
     * SLA deadlines in hours by urgency level.
     *
     * @var array<string, int>
     */
    private const SLA_HOURS = array(
        'high'   => 2,
        'medium' => 12,
        'low'    => 48,
    );

    /**
     * Route a lead to matching partners.
     *
     * Finds matching routing rules, selects the best partner(s), creates
     * assignments, deducts credits, and fires notifications.
     *
     * @param object $lead Lead row from tp_leads.
     * @return array Array of assignment row objects, or empty array if no match.
     */
    public function route( $lead ) {
        if ( empty( $lead->id ) ) {
            return array();
        }

        // 1. Find matching routing rules.
        $matching_rules = $this->find_matching_rules( $lead );

        if ( empty( $matching_rules ) ) {
            $this->log_audit( 'lead_routing_no_match', 'lead', $lead->id, null, array(
                'reason' => 'No matching routing rules found.',
            ) );
            return array();
        }

        // 2. Select best partners from matching rules.
        $selected = $this->select_partners( $matching_rules, $lead );

        if ( empty( $selected ) ) {
            $this->log_audit( 'lead_routing_no_partner', 'lead', $lead->id, null, array(
                'reason'         => 'No eligible partners available.',
                'rules_matched'  => count( $matching_rules ),
            ) );
            return array();
        }

        // 3. Create assignments and deduct credits.
        $assignments = array();

        foreach ( $selected as $match ) {
            $assignment = $this->create_assignment(
                $lead->id,
                $match['partner_id'],
                $match['rule_id']
            );

            if ( $assignment ) {
                // Deduct credits from partner.
                $credit_cost = $this->get_credit_cost( $lead );
                $this->deduct_credits( $match['partner_id'], $credit_cost );

                // Increment rule daily/monthly counters.
                $this->increment_rule_counters( $match['rule_id'] );

                $assignments[] = $assignment;
            }
        }

        // 4. Update lead status to assigned if we created assignments.
        if ( ! empty( $assignments ) ) {
            global $wpdb;

            $wpdb->update(
                $wpdb->prefix . 'tp_leads',
                array(
                    'status'     => 'assigned',
                    'updated_at' => current_time( 'mysql' ),
                ),
                array( 'id' => absint( $lead->id ) )
            );

            $this->log_audit( 'lead_routed', 'lead', $lead->id, null, array(
                'partners_assigned' => count( $assignments ),
                'partner_ids'       => wp_list_pluck( $assignments, 'partner_id' ),
            ) );

            /**
             * Fires after a lead is routed to one or more partners.
             *
             * @since 1.0.0
             *
             * @param object $lead        The lead row object.
             * @param array  $assignments Array of assignment row objects.
             */
            do_action( 'tp_lead_routed', $lead, $assignments );
        }

        return $assignments;
    }

    /**
     * Find routing rules whose conditions match the given lead.
     *
     * Queries tp_lead_routing_rules for active rules, then evaluates each
     * rule's JSON conditions against the lead's properties. Rules are returned
     * sorted by priority (lower number = higher priority).
     *
     * Condition fields checked (all optional in the JSON — omitted = any):
     *   - cities, locations, configurations (array overlap)
     *   - budget_min, budget_max (range)
     *   - lead_types (array membership)
     *   - quality_score_min (minimum threshold)
     *   - classifications (array membership)
     *   - purposes (array membership)
     *
     * @param object $lead Lead row from tp_leads.
     * @return array Matching rule rows, sorted by priority ASC.
     */
    public function find_matching_rules( $lead ) {
        global $wpdb;

        $prefix = $wpdb->prefix;
        $today  = current_time( 'Y-m-d' );

        // Fetch all active rules, sorted by priority.
        $rules = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$prefix}tp_lead_routing_rules WHERE is_active = %d ORDER BY priority ASC",
                1
            )
        );

        if ( empty( $rules ) ) {
            return array();
        }

        // Decode lead's JSON fields for matching.
        $lead_locations = $this->decode_json_field( $lead->location_preference ?? null );
        $lead_configs   = $this->decode_json_field( $lead->configuration ?? null );
        $lead_city      = $lead->customer_city ?? '';

        $matched = array();

        foreach ( $rules as $rule ) {
            // Reset daily/monthly counters if dates have rolled over.
            $this->maybe_reset_counters( $rule, $today );

            // Check rule-level daily/monthly limits.
            if ( $rule->max_leads_per_day > 0 && $rule->current_daily_count >= $rule->max_leads_per_day ) {
                continue;
            }
            if ( $rule->max_leads_per_month > 0 && $rule->current_monthly_count >= $rule->max_leads_per_month ) {
                continue;
            }

            $conditions = json_decode( $rule->conditions, true );

            if ( ! is_array( $conditions ) ) {
                continue;
            }

            if ( ! $this->evaluate_conditions( $conditions, $lead, $lead_city, $lead_locations, $lead_configs ) ) {
                continue;
            }

            $matched[] = $rule;
        }

        return $matched;
    }

    /**
     * Select the best partner(s) from matching rules.
     *
     * For each matching rule, verifies the linked partner is eligible
     * (active, has credits, within capacity, business hours). Eligible
     * partners are ranked by: rating DESC, avg_response_minutes ASC,
     * conversion_rate DESC. Returns top N unique partners.
     *
     * @param array  $matching_rules Matching rule rows.
     * @param object $lead           Lead row.
     * @return array Array of [ 'partner_id' => int, 'rule_id' => int ].
     */
    public function select_partners( array $matching_rules, $lead ) {
        global $wpdb;

        $prefix = $wpdb->prefix;

        // Determine distribution type.
        $is_exclusive = ! empty( $lead->site_visit_intent ) || $this->is_exclusive_lead( $lead );
        $max_partners = $is_exclusive ? 1 : self::MAX_SHARED_PARTNERS;

        $candidates = array();
        $seen_partners = array();

        foreach ( $matching_rules as $rule ) {
            $partner_id = (int) $rule->partner_id;

            // Skip already-evaluated partners.
            if ( isset( $seen_partners[ $partner_id ] ) ) {
                continue;
            }
            $seen_partners[ $partner_id ] = true;

            // Fetch partner.
            $partner = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$prefix}tp_partners WHERE id = %d",
                    $partner_id
                )
            );

            if ( ! $partner ) {
                continue;
            }

            // Check eligibility.
            if ( ! $this->is_partner_eligible( $partner ) ) {
                continue;
            }

            $candidates[] = array(
                'partner_id'        => $partner_id,
                'rule_id'           => (int) $rule->id,
                'rating'            => (float) $partner->rating,
                'avg_response'      => (int) $partner->avg_response_minutes,
                'conversion_rate'   => (float) $partner->conversion_rate,
            );
        }

        if ( empty( $candidates ) ) {
            return array();
        }

        // Sort: rating DESC, avg_response ASC, conversion_rate DESC.
        usort( $candidates, function ( $a, $b ) {
            if ( $a['rating'] !== $b['rating'] ) {
                return $b['rating'] <=> $a['rating'];
            }
            if ( $a['avg_response'] !== $b['avg_response'] ) {
                return $a['avg_response'] <=> $b['avg_response'];
            }
            return $b['conversion_rate'] <=> $a['conversion_rate'];
        } );

        return array_slice(
            array_map( function ( $c ) {
                return array(
                    'partner_id' => $c['partner_id'],
                    'rule_id'    => $c['rule_id'],
                );
            }, $candidates ),
            0,
            $max_partners
        );
    }

    /**
     * Create a lead assignment record.
     *
     * Inserts into tp_lead_assignments with status 'sent' and sets the
     * SLA deadline based on the lead's urgency level.
     *
     * @param int $lead_id    Lead ID.
     * @param int $partner_id Partner ID.
     * @param int $rule_id    Routing rule ID that triggered this assignment.
     * @return object|null Assignment row on success, null on failure.
     */
    public function create_assignment( $lead_id, $partner_id, $rule_id ) {
        global $wpdb;

        $prefix  = $wpdb->prefix;
        $lead_id = absint( $lead_id );

        // Determine assignment type.
        $lead = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$prefix}tp_leads WHERE id = %d",
                $lead_id
            )
        );

        if ( ! $lead ) {
            return null;
        }

        // Check if this partner is already assigned to this lead.
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$prefix}tp_lead_assignments
                 WHERE lead_id = %d AND partner_id = %d",
                $lead_id,
                absint( $partner_id )
            )
        );

        if ( $existing ) {
            return null;
        }

        $is_exclusive    = $this->is_exclusive_lead( $lead );
        $assignment_type = $is_exclusive ? 'exclusive' : 'shared';
        $credit_cost     = $this->get_credit_cost( $lead );

        // Calculate SLA deadline.
        $qualifier     = new Lead_Qualifier();
        $urgency       = $qualifier->get_urgency_level( $lead->classification ?? 'researching' );
        $sla_hours     = self::SLA_HOURS[ $urgency ] ?? 48;

        $now = current_time( 'mysql' );

        $result = $wpdb->insert(
            $prefix . 'tp_lead_assignments',
            array(
                'lead_id'         => $lead_id,
                'partner_id'      => absint( $partner_id ),
                'assignment_type' => $assignment_type,
                'credits_charged' => $credit_cost,
                'status'          => 'sent',
                'sent_at'         => $now,
            )
        );

        if ( false === $result ) {
            return null;
        }

        $assignment_id = $wpdb->insert_id;

        $this->log_audit( 'lead_assigned', 'lead_assignment', $assignment_id, null, array(
            'lead_id'         => $lead_id,
            'partner_id'      => $partner_id,
            'rule_id'         => $rule_id,
            'assignment_type' => $assignment_type,
            'credits_charged' => $credit_cost,
            'sla_hours'       => $sla_hours,
        ) );

        /**
         * Fires after a single lead assignment is created.
         *
         * @since 1.0.0
         *
         * @param int    $assignment_id Assignment ID.
         * @param int    $lead_id       Lead ID.
         * @param int    $partner_id    Partner ID.
         * @param string $urgency       Urgency level (high/medium/low).
         * @param int    $sla_hours     SLA deadline in hours.
         */
        do_action( 'tp_lead_assigned', $assignment_id, $lead_id, $partner_id, $urgency, $sla_hours );

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$prefix}tp_lead_assignments WHERE id = %d",
                $assignment_id
            )
        );
    }

    /**
     * Check if a partner has remaining capacity (daily and monthly limits).
     *
     * @param int $partner_id Partner ID.
     * @return bool True if the partner is within both daily and monthly limits.
     */
    public function check_partner_capacity( $partner_id ) {
        global $wpdb;

        $prefix  = $wpdb->prefix;
        $partner = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$prefix}tp_partners WHERE id = %d",
                absint( $partner_id )
            )
        );

        if ( ! $partner ) {
            return false;
        }

        // Daily limit check.
        if ( (int) $partner->daily_lead_limit > 0 ) {
            $today_count = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$prefix}tp_lead_assignments
                     WHERE partner_id = %d
                       AND DATE( sent_at ) = %s",
                    absint( $partner_id ),
                    current_time( 'Y-m-d' )
                )
            );

            if ( $today_count >= (int) $partner->daily_lead_limit ) {
                return false;
            }
        }

        // Monthly limit check.
        if ( (int) $partner->monthly_lead_limit > 0 ) {
            $month_start  = current_time( 'Y-m' ) . '-01 00:00:00';
            $month_count  = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$prefix}tp_lead_assignments
                     WHERE partner_id = %d
                       AND sent_at >= %s",
                    absint( $partner_id ),
                    $month_start
                )
            );

            if ( $month_count >= (int) $partner->monthly_lead_limit ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Deduct lead credits from a partner's balance.
     *
     * @param int $partner_id Partner ID.
     * @param int $amount     Number of credits to deduct.
     * @return bool True on success.
     */
    public function deduct_credits( $partner_id, $amount = 1 ) {
        global $wpdb;

        $partner_id = absint( $partner_id );
        $amount     = max( 1, absint( $amount ) );

        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}tp_partners
                 SET lead_credits         = GREATEST( 0, lead_credits - %d ),
                     total_leads_received  = total_leads_received + 1,
                     updated_at            = %s
                 WHERE id = %d",
                $amount,
                current_time( 'mysql' ),
                $partner_id
            )
        );

        if ( $updated ) {
            $this->log_audit( 'partner_credits_deducted', 'partner', $partner_id, null, array(
                'credits_deducted' => $amount,
            ) );
        }

        return false !== $updated;
    }

    // ─── Private helpers ──────────────────────────────────────────────────

    /**
     * Evaluate a rule's condition set against a lead.
     *
     * Every condition key present in the JSON must match. Omitted keys are
     * treated as "any" (wildcard).
     *
     * @param array  $conditions     Decoded conditions array.
     * @param object $lead           Lead row.
     * @param string $lead_city      Lead's city.
     * @param array  $lead_locations Lead's location preferences (decoded).
     * @param array  $lead_configs   Lead's configurations (decoded).
     * @return bool True if all present conditions match.
     */
    private function evaluate_conditions( array $conditions, $lead, $lead_city, array $lead_locations, array $lead_configs ) {

        // Cities.
        if ( ! empty( $conditions['cities'] ) && is_array( $conditions['cities'] ) ) {
            $city_slug = sanitize_title( $lead_city );
            if ( ! in_array( $city_slug, $conditions['cities'], true ) && ! empty( $lead_city ) ) {
                // Also check if any condition city is in lead city string.
                $found = false;
                foreach ( $conditions['cities'] as $c ) {
                    if ( false !== stripos( $lead_city, str_replace( '_', ' ', $c ) ) ) {
                        $found = true;
                        break;
                    }
                }
                if ( ! $found ) {
                    return false;
                }
            } elseif ( empty( $lead_city ) ) {
                return false;
            }
        }

        // Locations (overlap check).
        if ( ! empty( $conditions['locations'] ) && is_array( $conditions['locations'] ) ) {
            if ( empty( $lead_locations ) ) {
                return false;
            }
            $overlap = array_intersect(
                array_map( 'sanitize_title', $lead_locations ),
                $conditions['locations']
            );
            if ( empty( $overlap ) ) {
                return false;
            }
        }

        // Budget range.
        $lead_budget = ! empty( $lead->budget_maximum )
            ? (int) $lead->budget_maximum
            : ( ! empty( $lead->budget_comfortable ) ? (int) $lead->budget_comfortable : null );

        if ( isset( $conditions['budget_min'] ) && null !== $lead_budget ) {
            if ( $lead_budget < (int) $conditions['budget_min'] ) {
                return false;
            }
        }

        if ( isset( $conditions['budget_max'] ) && null !== $lead_budget ) {
            if ( $lead_budget > (int) $conditions['budget_max'] ) {
                return false;
            }
        }

        // Configurations (overlap check).
        if ( ! empty( $conditions['configurations'] ) && is_array( $conditions['configurations'] ) ) {
            if ( empty( $lead_configs ) ) {
                return false;
            }
            $config_overlap = array_intersect(
                array_map( 'sanitize_title', $lead_configs ),
                $conditions['configurations']
            );
            if ( empty( $config_overlap ) ) {
                return false;
            }
        }

        // Lead types.
        if ( ! empty( $conditions['lead_types'] ) && is_array( $conditions['lead_types'] ) ) {
            if ( ! in_array( $lead->lead_type ?? '', $conditions['lead_types'], true ) ) {
                return false;
            }
        }

        // Quality score minimum.
        if ( isset( $conditions['quality_score_min'] ) ) {
            if ( (int) ( $lead->quality_score ?? 0 ) < (int) $conditions['quality_score_min'] ) {
                return false;
            }
        }

        // Classifications.
        if ( ! empty( $conditions['classifications'] ) && is_array( $conditions['classifications'] ) ) {
            if ( ! in_array( $lead->classification ?? '', $conditions['classifications'], true ) ) {
                return false;
            }
        }

        // Purposes.
        if ( ! empty( $conditions['purposes'] ) && is_array( $conditions['purposes'] ) ) {
            if ( ! in_array( $lead->purpose ?? '', $conditions['purposes'], true ) ) {
                return false;
            }
        }

        // Purchase timeline max months.
        if ( isset( $conditions['purchase_timeline_max_months'] ) && ! empty( $lead->purchase_timeline ) ) {
            $lead_months = (int) filter_var( $lead->purchase_timeline, FILTER_SANITIZE_NUMBER_INT );
            if ( $lead_months > 0 && $lead_months > (int) $conditions['purchase_timeline_max_months'] ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a partner is eligible for lead assignment.
     *
     * Eligible means: active, has credits, within daily/monthly limits,
     * and currently within business hours.
     *
     * @param object $partner Partner row from tp_partners.
     * @return bool
     */
    private function is_partner_eligible( $partner ) {
        // Must be active.
        if ( empty( $partner->is_active ) ) {
            return false;
        }

        // Must have credits.
        if ( (int) $partner->lead_credits <= 0 ) {
            return false;
        }

        // Capacity check.
        if ( ! $this->check_partner_capacity( (int) $partner->id ) ) {
            return false;
        }

        // Business hours check.
        if ( ! $this->is_within_business_hours( $partner ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check if the current time is within the partner's business hours.
     *
     * @param object $partner Partner row.
     * @return bool
     */
    private function is_within_business_hours( $partner ) {
        $now_time = current_time( 'H:i:s' );
        $now_day  = strtolower( current_time( 'D' ) ); // e.g. 'mon', 'tue'.

        // Check business days.
        $business_days = json_decode( $partner->business_days ?? '[]', true );
        if ( ! empty( $business_days ) && ! in_array( $now_day, $business_days, true ) ) {
            return false;
        }

        // Check business hours.
        $start = $partner->business_hours_start ?? '09:00:00';
        $end   = $partner->business_hours_end ?? '21:00:00';

        if ( $now_time < $start || $now_time > $end ) {
            return false;
        }

        return true;
    }

    /**
     * Determine if a lead qualifies for exclusive assignment.
     *
     * @param object $lead Lead row.
     * @return bool
     */
    private function is_exclusive_lead( $lead ) {
        // Site visit leads are typically exclusive.
        if ( 'site_visit' === ( $lead->lead_type ?? '' ) ) {
            return true;
        }

        // High-budget leads may be exclusive.
        if ( ! empty( $lead->budget_maximum ) && (int) $lead->budget_maximum >= 20000000 ) {
            return true;
        }

        return false;
    }

    /**
     * Get the credit cost for a lead based on its type and quality.
     *
     * @param object $lead Lead row.
     * @return int Number of credits.
     */
    private function get_credit_cost( $lead ) {
        $quality = (int) ( $lead->quality_score ?? 0 );

        // Higher-quality leads cost more credits.
        if ( $quality >= 70 ) {
            return 3;
        }

        if ( $quality >= 50 ) {
            return 2;
        }

        return 1;
    }

    /**
     * Decode a JSON column value into an array.
     *
     * Handles null, empty strings, and already-decoded arrays gracefully.
     *
     * @param mixed $value Raw value from the database.
     * @return array Decoded array, or empty array on failure.
     */
    private function decode_json_field( $value ) {
        if ( is_array( $value ) ) {
            return $value;
        }

        if ( empty( $value ) ) {
            return array();
        }

        $decoded = json_decode( $value, true );

        return is_array( $decoded ) ? $decoded : array();
    }

    /**
     * Reset daily/monthly counters on a routing rule if the date has rolled over.
     *
     * @param object $rule  Routing rule row (modified by reference via DB update).
     * @param string $today Today's date (Y-m-d).
     * @return void
     */
    private function maybe_reset_counters( $rule, $today ) {
        global $wpdb;

        $prefix   = $wpdb->prefix;
        $update   = array();
        $month_01 = substr( $today, 0, 7 ) . '-01';

        // Daily reset.
        if ( $rule->last_reset_daily !== $today ) {
            $update['current_daily_count'] = 0;
            $update['last_reset_daily']    = $today;
            $rule->current_daily_count     = 0;
            $rule->last_reset_daily        = $today;
        }

        // Monthly reset (first day of current month).
        if ( empty( $rule->last_reset_monthly ) || $rule->last_reset_monthly < $month_01 ) {
            $update['current_monthly_count'] = 0;
            $update['last_reset_monthly']    = $today;
            $rule->current_monthly_count     = 0;
            $rule->last_reset_monthly        = $today;
        }

        if ( ! empty( $update ) ) {
            $wpdb->update(
                $prefix . 'tp_lead_routing_rules',
                $update,
                array( 'id' => absint( $rule->id ) )
            );
        }
    }

    /**
     * Increment a routing rule's daily and monthly counters.
     *
     * @param int $rule_id Routing rule ID.
     * @return void
     */
    private function increment_rule_counters( $rule_id ) {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}tp_lead_routing_rules
                 SET current_daily_count   = current_daily_count + 1,
                     current_monthly_count = current_monthly_count + 1,
                     updated_at            = %s
                 WHERE id = %d",
                current_time( 'mysql' ),
                absint( $rule_id )
            )
        );
    }

    /**
     * Insert a row into the tp_audit_log table.
     *
     * @param string     $action      Action identifier.
     * @param string     $entity_type Entity type.
     * @param int        $entity_id   Entity ID.
     * @param array|null $old_values  Previous values.
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
