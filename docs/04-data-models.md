# Data Models & Database Schema

## Overview

10Projects uses a hybrid data strategy:
- **WordPress Custom Post Types (CPTs):** For editorial content that editors manage (projects, developers, locations, guides)
- **Custom Database Tables:** For high-volume transactional data (leads, AI sessions, scores, analytics, partner routing)

This avoids the wp_postmeta performance bottleneck while keeping content manageable through WordPress admin.

---

## WordPress Custom Post Types

### CPT 1: `tp_project` (Project)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_tp_rera_number` | string | RERA registration number |
| `_tp_rera_phase` | string | RERA phase if applicable |
| `_tp_developer_id` | int | Linked developer CPT ID |
| `_tp_latitude` | float | Project latitude |
| `_tp_longitude` | float | Project longitude |
| `_tp_address` | string | Full address |
| `_tp_launch_date` | date | Launch date |
| `_tp_rera_registration_date` | date | RERA registration date |
| `_tp_construction_start` | date | Construction start date |
| `_tp_construction_stage` | string | new_launch/foundation/structure/finishing/ready |
| `_tp_construction_progress` | int | Percentage 0-100 |
| `_tp_promised_possession` | date | Developer promised date |
| `_tp_rera_possession` | date | RERA registered date |
| `_tp_expected_possession` | date | Our estimated date |
| `_tp_total_towers` | int | Number of towers |
| `_tp_total_floors` | int | Max floors |
| `_tp_total_units` | int | Total units |
| `_tp_open_space_ratio` | float | Open space percentage |
| `_tp_density_rating` | string | low/medium/high |
| `_tp_maintenance_estimate` | int | Monthly per sq ft |
| `_tp_parking_info` | string | Parking details |
| `_tp_water_source` | string | Water infrastructure |
| `_tp_power_backup` | string | Power backup details |
| `_tp_legal_confidence` | int | Score 0-100 |
| `_tp_possession_confidence` | int | Score 0-100 |
| `_tp_bank_approved` | string[] | List of approved banks |
| `_tp_litigation_status` | string | none/minor/major |
| `_tp_railway_distance_km` | float | Nearest railway station |
| `_tp_metro_distance_km` | float | Nearest metro station |
| `_tp_highway_distance_km` | float | Nearest highway |
| `_tp_airport_distance_km` | float | Nearest airport |
| `_tp_school_distance_km` | float | Nearest school |
| `_tp_hospital_distance_km` | float | Nearest hospital |
| `_tp_mall_distance_km` | float | Nearest mall |
| `_tp_employment_hub_km` | float | Nearest office park |
| `_tp_micro_market_price` | int | Current micro-market avg price/sqft |
| `_tp_rental_range_min` | int | Monthly rental min |
| `_tp_rental_range_max` | int | Monthly rental max |
| `_tp_vacancy_risk` | string | low/medium/high |
| `_tp_appreciation_score` | int | 0-100 |
| `_tp_rental_yield_pct` | float | Estimated rental yield |
| `_tp_highlights` | text | JSON array of highlights |
| `_tp_pros` | text | JSON array of pros |
| `_tp_cons` | text | JSON array of cons |
| `_tp_risks` | text | JSON array of risks |
| `_tp_best_for` | text | JSON array — who should buy |
| `_tp_not_for` | text | JSON array — who should avoid |
| `_tp_verification_checklist` | text | JSON |
| `_tp_sources` | text | JSON array of sources |
| `_tp_last_verified` | date | Last verification date |
| `_tp_reviewed_by` | string | Reviewer name |
| `_tp_status` | string | active/paused/sold_out/delisted |
| `_tp_sponsored` | boolean | Is sponsored listing |
| `_tp_sponsor_label` | string | Sponsor label text |

**Configurations stored in custom table** (see below — one project has many configurations)

### CPT 2: `tp_developer` (Developer)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_tp_dev_established_year` | int | Year established |
| `_tp_dev_headquarters` | string | HQ location |
| `_tp_dev_website` | string | Official website |
| `_tp_dev_total_projects` | int | Total projects |
| `_tp_dev_completed_projects` | int | Completed count |
| `_tp_dev_ongoing_projects` | int | Ongoing count |
| `_tp_dev_delayed_projects` | int | Delayed count |
| `_tp_dev_ontime_rate` | float | On-time delivery % |
| `_tp_dev_avg_delay_months` | int | Average delay in months |
| `_tp_dev_litigation_flags` | string | none/minor/major |
| `_tp_dev_financial_risk` | string | low/medium/high |
| `_tp_dev_reputation_score` | int | 0-100 |
| `_tp_dev_tier` | string | tier_1/tier_2/tier_3/new |
| `_tp_dev_delivery_history` | text | JSON array of delivered projects |
| `_tp_dev_customer_rating` | float | Average rating |
| `_tp_dev_overview` | text | Editorial overview |

### CPT 3: `tp_location` (Location)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_tp_loc_city` | string | Parent city |
| `_tp_loc_type` | string | city/location/micro_location |
| `_tp_loc_parent_id` | int | Parent location CPT ID |
| `_tp_loc_latitude` | float | Center latitude |
| `_tp_loc_longitude` | float | Center longitude |
| `_tp_loc_avg_price_sqft` | int | Avg price per sq ft |
| `_tp_loc_price_trend_3yr` | float | 3-year CAGR % |
| `_tp_loc_total_projects` | int | Active project count |
| `_tp_loc_rental_demand` | string | low/medium/high |
| `_tp_loc_liveability_score` | int | 0-100 |
| `_tp_loc_investment_score` | int | 0-100 |
| `_tp_loc_risk_score` | int | 0-100 |
| `_tp_loc_water_issues` | text | Water concern details |
| `_tp_loc_traffic_issues` | text | Traffic concern details |
| `_tp_loc_environmental` | text | Environmental concerns |
| `_tp_loc_infrastructure` | text | JSON — planned infrastructure |
| `_tp_loc_supply_pipeline` | int | Upcoming units count |
| `_tp_loc_resale_liquidity` | string | low/medium/high |
| `_tp_loc_overview` | text | Editorial overview |
| `_tp_loc_who_should_consider` | text | Suitability text |
| `_tp_loc_who_should_avoid` | text | Avoid text |
| `_tp_loc_commute_details` | text | JSON — commute info |
| `_tp_loc_schools` | text | JSON array of nearby schools |
| `_tp_loc_hospitals` | text | JSON array of nearby hospitals |
| `_tp_loc_offices` | text | JSON array of nearby offices |

### CPT 4: `tp_guide` (Property Guide)

Standard WordPress post with categories:
- `guide_type` taxonomy: comparison, budget_guide, config_guide, location_guide, buyer_type, investment_guide

### CPT 5: `tp_market_report` (Market Report)

Standard WordPress post with:
| Meta Key | Type | Description |
|----------|------|-------------|
| `_tp_report_city` | string | City |
| `_tp_report_quarter` | string | e.g., "Q2 2026" |
| `_tp_report_data` | text | JSON data for charts |

### CPT 6: `tp_review` (Customer Review)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_tp_review_project_id` | int | Linked project ID |
| `_tp_review_rating` | float | 1-5 rating |
| `_tp_review_buyer_type` | string | end_user/investor |
| `_tp_review_verified` | boolean | Verified buyer |
| `_tp_review_source` | string | Source of review |

### CPT 7: `tp_infrastructure` (Infrastructure Update)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_tp_infra_type` | string | metro/airport/highway/railway/other |
| `_tp_infra_status` | string | planned/approved/under_construction/completed |
| `_tp_infra_expected_completion` | date | Expected date |
| `_tp_infra_impact_radius_km` | float | Impact area |
| `_tp_infra_latitude` | float | Location |
| `_tp_infra_longitude` | float | Location |
| `_tp_infra_affected_locations` | int[] | Location CPT IDs |

---

## WordPress Taxonomies

### Taxonomy: `tp_city`
Hierarchical. Examples: Navi Mumbai, Mumbai, Pune

### Taxonomy: `tp_location_area`
Hierarchical (parent = city). Examples: Kharghar, Panvel, Taloja

### Taxonomy: `tp_micro_location`
Hierarchical (parent = location). Examples: Sector 35 Kharghar, Upper Kharghar

### Taxonomy: `tp_configuration`
Flat. Examples: 1 BHK, 1.5 BHK, 2 BHK, 2.5 BHK, 3 BHK, 3.5 BHK, 4 BHK, 5 BHK, Penthouse, Duplex

### Taxonomy: `tp_budget_range`
Flat. Examples: Under ₹50L, ₹50L–₹75L, ₹75L–₹1Cr, ₹1Cr–₹1.5Cr, ₹1.5Cr–₹2Cr, ₹2Cr–₹3Cr, ₹3Cr+

### Taxonomy: `tp_construction_stage`
Flat. Examples: New Launch, Under Construction, Nearing Completion, Ready Possession

### Taxonomy: `tp_possession_year`
Flat. Examples: 2026, 2027, 2028, 2029, 2030, 2031+

### Taxonomy: `tp_property_type`
Flat. Examples: Residential Apartment, Villa, Row House, Commercial Office, Commercial Shop, Plot

### Taxonomy: `tp_buyer_type`
Flat. Examples: First-time Buyer, Upgrade Buyer, Investor, NRI, Senior

### Taxonomy: `tp_amenity`
Flat. Examples: Swimming Pool, Gym, Clubhouse, Garden, Jogging Track, Sports Court, Kids Play, Senior Area, Pet Area, EV Charging, Library, Theatre, Yoga Room

### Taxonomy: `tp_guide_type`
Flat. Examples: Comparison, Budget Guide, Configuration Guide, Location Guide, Buyer Type Guide, Investment Guide

---

## Custom Database Tables

### Table: `tp_project_configurations`

```sql
CREATE TABLE tp_project_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    configuration VARCHAR(20) NOT NULL,
    carpet_area_min DECIMAL(10,2),
    carpet_area_max DECIMAL(10,2),
    built_up_area_min DECIMAL(10,2),
    built_up_area_max DECIMAL(10,2),
    price_min BIGINT UNSIGNED,
    price_max BIGINT UNSIGNED,
    price_per_sqft INT UNSIGNED,
    floor_availability VARCHAR(255),
    inventory_status ENUM('available','limited','sold_out','waitlist') DEFAULT 'available',
    unit_count INT UNSIGNED,
    views_available VARCHAR(255),
    facing VARCHAR(255),
    balconies TINYINT UNSIGNED DEFAULT 0,
    bathrooms TINYINT UNSIGNED DEFAULT 1,
    parking_included TINYINT UNSIGNED DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_project (project_id),
    INDEX idx_config (configuration),
    INDEX idx_price (price_min, price_max),
    INDEX idx_area (carpet_area_min, carpet_area_max),
    INDEX idx_status (inventory_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_customers`

```sql
CREATE TABLE tp_customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wp_user_id BIGINT UNSIGNED NULL,
    name VARCHAR(255),
    phone VARCHAR(20) NOT NULL,
    phone_verified TINYINT(1) DEFAULT 0,
    email VARCHAR(255),
    email_verified TINYINT(1) DEFAULT 0,
    city VARCHAR(100),
    auth_method ENUM('otp','google','email_otp','password') DEFAULT 'otp',
    login_token VARCHAR(255),
    token_expires_at DATETIME,
    profile_data JSON,
    consent_lead_sharing TINYINT(1) DEFAULT 0,
    consent_marketing TINYINT(1) DEFAULT 0,
    consent_whatsapp TINYINT(1) DEFAULT 0,
    consent_collected_at DATETIME,
    utm_source VARCHAR(255),
    utm_medium VARCHAR(255),
    utm_campaign VARCHAR(255),
    utm_content VARCHAR(255),
    utm_term VARCHAR(255),
    first_visit_url TEXT,
    referrer TEXT,
    device_type VARCHAR(50),
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE INDEX idx_phone (phone),
    INDEX idx_wp_user (wp_user_id),
    INDEX idx_email (email),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_customer_requirements`

```sql
CREATE TABLE tp_customer_requirements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(255) DEFAULT 'My Requirements',
    is_active TINYINT(1) DEFAULT 1,
    purpose ENUM('end_use','investment','both'),
    property_type VARCHAR(50),
    configuration JSON,
    config_flexible TINYINT(1) DEFAULT 0,
    city VARCHAR(100),
    construction_stage JSON,
    preferred_locations JSON,
    alternative_locations JSON,
    location_flexible TINYINT(1) DEFAULT 0,
    workplace_location VARCHAR(255),
    max_commute_minutes INT,
    commute_mode VARCHAR(50),
    proximity_requirements JSON,
    budget_comfortable BIGINT UNSIGNED,
    budget_maximum BIGINT UNSIGNED,
    funding_type ENUM('self','loan','mix'),
    down_payment BIGINT UNSIGNED,
    monthly_emi_comfort INT UNSIGNED,
    loan_preapproved ENUM('yes','no','in_process'),
    existing_emi INT UNSIGNED DEFAULT 0,
    payment_plan_preference VARCHAR(50),
    purchase_timeline_months INT,
    possession_preference VARCHAR(50),
    max_possession_year INT,
    family_adults TINYINT UNSIGNED,
    family_children TINYINT UNSIGNED,
    children_ages JSON,
    family_seniors TINYINT UNSIGNED,
    pets VARCHAR(50),
    risk_tolerance ENUM('low','balanced','high') DEFAULT 'balanced',
    developer_preference VARCHAR(50),
    specific_developer VARCHAR(255),
    priorities JSON,
    deal_breakers JSON,
    carpet_area_min INT UNSIGNED,
    carpet_area_max INT UNSIGNED,
    amenity_preferences JSON,
    additional_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_active (is_active),
    FOREIGN KEY (customer_id) REFERENCES tp_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_ai_sessions`

```sql
CREATE TABLE tp_ai_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_uuid VARCHAR(36) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NULL,
    requirement_id BIGINT UNSIGNED NULL,
    status ENUM('in_progress','completed','abandoned') DEFAULT 'in_progress',
    current_phase TINYINT UNSIGNED DEFAULT 1,
    completion_rate DECIMAL(3,2) DEFAULT 0.00,
    answers JSON,
    ai_provider VARCHAR(50),
    ai_model VARCHAR(100),
    conversation_log JSON,
    customer_profile_json JSON,
    total_tokens_used INT UNSIGNED DEFAULT 0,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    abandoned_at DATETIME NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_uuid (session_uuid),
    INDEX idx_started (started_at),
    FOREIGN KEY (customer_id) REFERENCES tp_customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_project_scores`

```sql
CREATE TABLE tp_project_scores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requirement_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    config_id BIGINT UNSIGNED NULL,
    total_fit_score INT UNSIGNED NOT NULL,
    budget_fit INT UNSIGNED DEFAULT 0,
    location_fit INT UNSIGNED DEFAULT 0,
    config_fit INT UNSIGNED DEFAULT 0,
    carpet_area_fit INT UNSIGNED DEFAULT 0,
    possession_fit INT UNSIGNED DEFAULT 0,
    emi_fit INT UNSIGNED DEFAULT 0,
    commute_fit INT UNSIGNED DEFAULT 0,
    lifestyle_fit INT UNSIGNED DEFAULT 0,
    developer_reliability INT UNSIGNED DEFAULT 0,
    construction_stage_fit INT UNSIGNED DEFAULT 0,
    legal_confidence INT UNSIGNED DEFAULT 0,
    resale_liquidity INT UNSIGNED DEFAULT 0,
    rental_potential INT UNSIGNED DEFAULT 0,
    appreciation_drivers INT UNSIGNED DEFAULT 0,
    risk_compatibility INT UNSIGNED DEFAULT 0,
    infrastructure_potential INT UNSIGNED DEFAULT 0,
    family_suitability INT UNSIGNED DEFAULT 0,
    urgency_match INT UNSIGNED DEFAULT 0,
    inventory_availability INT UNSIGNED DEFAULT 0,
    proximity_score INT UNSIGNED DEFAULT 0,
    weight_profile VARCHAR(50),
    weights_used JSON,
    ranking INT UNSIGNED,
    match_reasons JSON,
    trade_offs JSON,
    ai_explanation TEXT,
    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_requirement (requirement_id),
    INDEX idx_project (project_id),
    INDEX idx_score (total_fit_score DESC),
    INDEX idx_ranking (requirement_id, ranking),
    FOREIGN KEY (requirement_id) REFERENCES tp_customer_requirements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_recommendations`

```sql
CREATE TABLE tp_recommendations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    requirement_id BIGINT UNSIGNED NOT NULL,
    session_id BIGINT UNSIGNED NULL,
    projects_analysed INT UNSIGNED,
    ai_confidence DECIMAL(3,2),
    results JSON,
    requirement_summary TEXT,
    share_token VARCHAR(64) UNIQUE,
    viewed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_requirement (requirement_id),
    INDEX idx_share (share_token),
    FOREIGN KEY (customer_id) REFERENCES tp_customers(id) ON DELETE CASCADE,
    FOREIGN KEY (requirement_id) REFERENCES tp_customer_requirements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_saved_projects`

```sql
CREATE TABLE tp_saved_projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    notes TEXT,
    sort_order INT DEFAULT 0,
    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_customer_project (customer_id, project_id),
    FOREIGN KEY (customer_id) REFERENCES tp_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_comparisons`

```sql
CREATE TABLE tp_comparisons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NULL,
    project_ids JSON NOT NULL,
    comparison_data JSON,
    share_token VARCHAR(64) UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_leads`

```sql
CREATE TABLE tp_leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_uuid VARCHAR(36) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NOT NULL,
    requirement_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,
    lead_type ENUM('ai_recommendation','site_visit','best_price','callback',
                   'advisor','whatsapp','report_download','emi_check',
                   'organic','direct') NOT NULL,
    lead_source VARCHAR(100),
    lead_medium VARCHAR(100),
    lead_campaign VARCHAR(255),
    customer_name VARCHAR(255),
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(255),
    customer_city VARCHAR(100),
    location_preference JSON,
    budget_comfortable BIGINT UNSIGNED,
    budget_maximum BIGINT UNSIGNED,
    configuration JSON,
    funding_type VARCHAR(20),
    down_payment BIGINT UNSIGNED,
    emi_comfort INT UNSIGNED,
    purchase_timeline VARCHAR(50),
    possession_preference VARCHAR(50),
    risk_profile VARCHAR(20),
    purpose VARCHAR(20),
    top_matched_projects JSON,
    engagement_score INT UNSIGNED DEFAULT 0,
    conversation_summary TEXT,
    ai_lead_summary TEXT,
    questions_asked JSON,
    site_visit_intent TINYINT(1) DEFAULT 0,
    loan_required TINYINT(1) DEFAULT 0,
    quality_score INT UNSIGNED DEFAULT 0,
    readiness_score INT UNSIGNED DEFAULT 0,
    classification ENUM('researching','warm','qualified','site_visit_ready',
                        'negotiation_ready','loan_dependent','investor',
                        'end_user','nri','high_budget','long_term_nurture')
                  DEFAULT 'researching',
    duplicate_of BIGINT UNSIGNED NULL,
    is_duplicate TINYINT(1) DEFAULT 0,
    consent_contact TINYINT(1) DEFAULT 0,
    consent_share TINYINT(1) DEFAULT 0,
    consent_timestamp DATETIME,
    status ENUM('new','assigned','contacted','site_visit_scheduled',
                'site_visit_done','negotiating','booked','lost','invalid',
                'duplicate','refunded') DEFAULT 'new',
    utm_source VARCHAR(255),
    utm_medium VARCHAR(255),
    utm_campaign VARCHAR(255),
    utm_content VARCHAR(255),
    utm_term VARCHAR(255),
    landing_page TEXT,
    referrer TEXT,
    device_type VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_project (project_id),
    INDEX idx_status (status),
    INDEX idx_type (lead_type),
    INDEX idx_classification (classification),
    INDEX idx_quality (quality_score DESC),
    INDEX idx_created (created_at),
    INDEX idx_phone (customer_phone),
    INDEX idx_duplicate (duplicate_of),
    FOREIGN KEY (customer_id) REFERENCES tp_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_partners`

```sql
CREATE TABLE tp_partners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wp_user_id BIGINT UNSIGNED NULL,
    company_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    partner_type ENUM('channel_partner','broker','mandate_firm',
                      'developer','sales_team') NOT NULL,
    rera_number VARCHAR(100),
    cities JSON,
    locations JSON,
    projects JSON,
    budget_range_min BIGINT UNSIGNED,
    budget_range_max BIGINT UNSIGNED,
    configurations JSON,
    lead_types JSON,
    subscription_plan VARCHAR(50),
    lead_credits INT DEFAULT 0,
    monthly_lead_limit INT DEFAULT 0,
    daily_lead_limit INT DEFAULT 0,
    exclusivity TINYINT(1) DEFAULT 0,
    response_sla_hours INT DEFAULT 24,
    is_active TINYINT(1) DEFAULT 1,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_leads_received INT DEFAULT 0,
    total_leads_converted INT DEFAULT 0,
    conversion_rate DECIMAL(5,2) DEFAULT 0.00,
    avg_response_minutes INT DEFAULT 0,
    business_hours_start TIME DEFAULT '09:00:00',
    business_hours_end TIME DEFAULT '21:00:00',
    business_days JSON DEFAULT '["mon","tue","wed","thu","fri","sat"]',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (partner_type),
    INDEX idx_active (is_active),
    INDEX idx_wp_user (wp_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_lead_assignments`

```sql
CREATE TABLE tp_lead_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    partner_id BIGINT UNSIGNED NOT NULL,
    assignment_type ENUM('exclusive','shared') DEFAULT 'shared',
    credits_charged INT DEFAULT 1,
    status ENUM('sent','opened','accepted','rejected','expired',
                'contacted','site_visit','booked','disputed',
                'refund_requested','refunded') DEFAULT 'sent',
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    opened_at DATETIME NULL,
    accepted_at DATETIME NULL,
    first_contact_at DATETIME NULL,
    contact_attempts INT DEFAULT 0,
    site_visit_at DATETIME NULL,
    booking_at DATETIME NULL,
    rejection_reason TEXT,
    dispute_reason TEXT,
    refund_eligible TINYINT(1) DEFAULT 0,
    partner_notes TEXT,
    response_time_minutes INT NULL,
    INDEX idx_lead (lead_id),
    INDEX idx_partner (partner_id),
    INDEX idx_status (status),
    INDEX idx_sent (sent_at),
    FOREIGN KEY (lead_id) REFERENCES tp_leads(id) ON DELETE CASCADE,
    FOREIGN KEY (partner_id) REFERENCES tp_partners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_lead_routing_rules`

```sql
CREATE TABLE tp_lead_routing_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    partner_id BIGINT UNSIGNED NOT NULL,
    rule_name VARCHAR(255),
    priority INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    conditions JSON NOT NULL,
    max_leads_per_day INT DEFAULT 0,
    max_leads_per_month INT DEFAULT 0,
    current_daily_count INT DEFAULT 0,
    current_monthly_count INT DEFAULT 0,
    last_reset_daily DATE,
    last_reset_monthly DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_partner (partner_id),
    INDEX idx_active (is_active),
    INDEX idx_priority (priority),
    FOREIGN KEY (partner_id) REFERENCES tp_partners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_consent_log`

```sql
CREATE TABLE tp_consent_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    consent_type ENUM('lead_sharing','marketing','whatsapp','data_processing',
                      'terms','privacy_policy') NOT NULL,
    granted TINYINT(1) NOT NULL,
    consent_text TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_type (consent_type),
    FOREIGN KEY (customer_id) REFERENCES tp_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_analytics_events`

```sql
CREATE TABLE tp_analytics_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    session_id VARCHAR(64),
    customer_id BIGINT UNSIGNED NULL,
    visitor_id VARCHAR(64),
    page_url TEXT,
    referrer TEXT,
    event_data JSON,
    utm_source VARCHAR(255),
    utm_medium VARCHAR(255),
    utm_campaign VARCHAR(255),
    device_type VARCHAR(50),
    browser VARCHAR(100),
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event (event_name),
    INDEX idx_session (session_id),
    INDEX idx_customer (customer_id),
    INDEX idx_visitor (visitor_id),
    INDEX idx_created (created_at),
    INDEX idx_page (page_url(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_notifications`

```sql
CREATE TABLE tp_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient_type ENUM('customer','partner','admin') NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    channel ENUM('email','sms','whatsapp','push','in_app') NOT NULL,
    notification_type VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    body TEXT,
    data JSON,
    status ENUM('pending','sent','delivered','failed','read') DEFAULT 'pending',
    sent_at DATETIME NULL,
    read_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipient (recipient_type, recipient_id),
    INDEX idx_status (status),
    INDEX idx_type (notification_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_audit_log`

```sql
CREATE TABLE tp_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    user_type ENUM('admin','customer','partner','system') NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id BIGINT UNSIGNED,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id, user_type),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table: `tp_site_visits`

```sql
CREATE TABLE tp_site_visits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED NULL,
    partner_id BIGINT UNSIGNED NULL,
    requested_date DATE,
    requested_time_slot VARCHAR(50),
    confirmed_date DATE NULL,
    confirmed_time VARCHAR(50) NULL,
    status ENUM('requested','confirmed','completed','cancelled','no_show') DEFAULT 'requested',
    customer_notes TEXT,
    partner_notes TEXT,
    feedback_rating TINYINT UNSIGNED NULL,
    feedback_text TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_project (project_id),
    INDEX idx_status (status),
    INDEX idx_date (requested_date),
    FOREIGN KEY (customer_id) REFERENCES tp_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
