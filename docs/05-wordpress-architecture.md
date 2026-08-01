# WordPress Architecture & Technical Specification

## Architecture Overview

```
┌────────────────────────────────────────────────────────────┐
│                     NGINX / CDN                             │
│              (Static cache, GZIP, WebP, SSL)                │
├────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────────┐  ┌───────────────────────────┐   │
│  │   WordPress Core     │  │   WordPress REST API       │   │
│  │   (Server-rendered   │  │   (AJAX endpoints for      │   │
│  │    SEO pages)        │  │    AI chat, scoring,       │   │
│  │                      │  │    leads, comparisons)     │   │
│  └──────────────────────┘  └───────────────────────────┘   │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              10Projects AI Matcher Plugin             │   │
│  │                                                        │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌─────────┐ │   │
│  │  │   CPT    │ │    AI    │ │ Scoring  │ │  Lead   │ │   │
│  │  │ Manager  │ │ Service  │ │ Engine   │ │ Router  │ │   │
│  │  └──────────┘ └──────────┘ └──────────┘ └─────────┘ │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌─────────┐ │   │
│  │  │Analytics │ │ Customer │ │ Consent  │ │Notific. │ │   │
│  │  │ Tracker  │ │  Auth    │ │ Manager  │ │ Service │ │   │
│  │  └──────────┘ └──────────┘ └──────────┘ └─────────┘ │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌──────────────────────┐  ┌───────────────────────────┐   │
│  │  10Projects Theme    │  │     Custom DB Tables       │   │
│  │  (Block theme,       │  │  (Leads, Scores, Sessions, │   │
│  │   minimal JS,        │  │   Analytics, Partners,     │   │
│  │   server-rendered)   │  │   Routing rules)           │   │
│  └──────────────────────┘  └───────────────────────────┘   │
│                                                              │
├────────────────────────────────────────────────────────────┤
│              MySQL / MariaDB + Object Cache (Redis)         │
└────────────────────────────────────────────────────────────┘
```

---

## Theme Structure

```
themes/tenprojects/
├── style.css                          # Theme header + minimal base styles
├── theme.json                         # Block theme configuration (colors, fonts, spacing)
├── functions.php                      # Theme setup, enqueues, support
├── screenshot.png
│
├── assets/
│   ├── css/
│   │   ├── global.css                 # Base typography, reset, variables
│   │   ├── components.css             # Reusable component styles
│   │   ├── homepage.css               # Homepage-specific
│   │   ├── project-detail.css         # Project page
│   │   ├── location.css               # Location pages
│   │   ├── results.css                # Recommendation results
│   │   ├── comparison.css             # Comparison page
│   │   ├── dashboard.css              # Customer dashboard
│   │   └── ai-chat.css                # AI assessment interface
│   ├── js/
│   │   ├── ai-chat.js                 # AI assessment (loaded lazily)
│   │   ├── comparison.js              # Comparison functionality
│   │   ├── project-actions.js         # Save, share, site visit
│   │   ├── analytics.js               # Event tracking
│   │   └── utils.js                   # Shared utilities
│   ├── images/
│   │   └── (brand assets, icons as SVG)
│   └── fonts/
│       └── (self-hosted fonts if needed)
│
├── templates/
│   ├── front-page.html                # Homepage template
│   ├── single-tp_project.html         # Project detail
│   ├── single-tp_developer.html       # Developer profile
│   ├── single-tp_location.html        # Location page
│   ├── single-tp_guide.html           # Guide page
│   ├── archive-tp_project.html        # Projects listing
│   ├── archive-tp_location.html       # Locations listing
│   ├── page-start.html                # AI assessment page
│   ├── page-results.html              # Results page
│   ├── page-compare.html              # Comparison page
│   ├── page-dashboard.html            # Customer dashboard
│   ├── page-methodology.html          # Methodology page
│   ├── 404.html                       # 404 page
│   └── index.html                     # Fallback
│
├── parts/
│   ├── header.html                    # Site header
│   ├── footer.html                    # Site footer
│   ├── project-card.html              # Reusable project card
│   ├── location-card.html             # Reusable location card
│   ├── fit-score-badge.html           # Fit score display
│   ├── cta-section.html               # CTA blocks
│   ├── breadcrumbs.html               # Breadcrumb navigation
│   └── trust-bar.html                 # Trust indicators
│
├── patterns/
│   ├── hero.php                       # Homepage hero pattern
│   ├── how-it-works.php               # Steps section
│   ├── popular-locations.php          # Location cards grid
│   ├── testimonials.php               # Reviews carousel
│   ├── market-insights.php            # Latest insights
│   └── final-cta.php                  # Bottom CTA section
│
└── inc/
    ├── template-functions.php         # Template helper functions
    ├── structured-data.php            # Schema.org JSON-LD
    ├── seo.php                        # Title, meta, OG tags
    └── performance.php                # Preload, lazy load, critical CSS
```

---

## Plugin Structure

```
plugins/tenprojects-ai-matcher/
├── tenprojects-ai-matcher.php         # Plugin bootstrap
├── composer.json                      # PHP dependencies
├── package.json                       # JS build dependencies
├── readme.txt                         # WordPress plugin readme
│
├── includes/
│   ├── class-plugin.php               # Main plugin class
│   ├── class-activator.php            # Activation hooks
│   ├── class-deactivator.php          # Deactivation hooks
│   │
│   ├── cpt/                           # Custom Post Types
│   │   ├── class-project-cpt.php
│   │   ├── class-developer-cpt.php
│   │   ├── class-location-cpt.php
│   │   ├── class-guide-cpt.php
│   │   ├── class-review-cpt.php
│   │   ├── class-infrastructure-cpt.php
│   │   └── class-market-report-cpt.php
│   │
│   ├── taxonomy/                      # Taxonomies
│   │   ├── class-city-taxonomy.php
│   │   ├── class-location-taxonomy.php
│   │   ├── class-configuration-taxonomy.php
│   │   ├── class-budget-range-taxonomy.php
│   │   ├── class-construction-stage-taxonomy.php
│   │   ├── class-possession-year-taxonomy.php
│   │   ├── class-property-type-taxonomy.php
│   │   ├── class-buyer-type-taxonomy.php
│   │   └── class-amenity-taxonomy.php
│   │
│   ├── database/                      # Database management
│   │   ├── class-db-manager.php       # Schema creation & migrations
│   │   ├── class-db-seeder.php        # Seed/test data
│   │   └── migrations/               # Versioned migrations
│   │       ├── 001-create-customers.php
│   │       ├── 002-create-requirements.php
│   │       ├── 003-create-sessions.php
│   │       ├── 004-create-scores.php
│   │       ├── 005-create-recommendations.php
│   │       ├── 006-create-leads.php
│   │       ├── 007-create-partners.php
│   │       ├── 008-create-assignments.php
│   │       ├── 009-create-routing-rules.php
│   │       ├── 010-create-analytics.php
│   │       ├── 011-create-consent.php
│   │       ├── 012-create-audit.php
│   │       ├── 013-create-notifications.php
│   │       ├── 014-create-site-visits.php
│   │       ├── 015-create-saved-projects.php
│   │       ├── 016-create-comparisons.php
│   │       └── 017-create-configurations.php
│   │
│   ├── services/                      # Business logic services
│   │   ├── class-ai-service.php       # AI provider abstraction
│   │   ├── class-ai-provider-claude.php
│   │   ├── class-ai-provider-openai.php
│   │   ├── class-ai-provider-gemini.php
│   │   ├── class-scoring-engine.php   # Fit Score calculation
│   │   ├── class-recommendation-engine.php
│   │   ├── class-lead-service.php     # Lead creation & management
│   │   ├── class-lead-router.php      # Lead routing logic
│   │   ├── class-lead-qualifier.php   # Lead scoring & classification
│   │   ├── class-customer-service.php # Customer CRUD
│   │   ├── class-auth-service.php     # OTP, Google, login
│   │   ├── class-otp-service.php      # SMS OTP provider
│   │   ├── class-consent-service.php  # Consent management
│   │   ├── class-analytics-service.php
│   │   ├── class-notification-service.php
│   │   ├── class-comparison-service.php
│   │   ├── class-report-service.php   # PDF report generation
│   │   ├── class-import-service.php   # CSV bulk import
│   │   └── class-export-service.php   # Lead export
│   │
│   ├── api/                           # REST API endpoints
│   │   ├── class-api-base.php         # Base controller
│   │   ├── class-assessment-api.php   # AI assessment endpoints
│   │   ├── class-recommendation-api.php
│   │   ├── class-project-api.php
│   │   ├── class-customer-api.php
│   │   ├── class-auth-api.php
│   │   ├── class-lead-api.php
│   │   ├── class-comparison-api.php
│   │   ├── class-saved-projects-api.php
│   │   ├── class-site-visit-api.php
│   │   ├── class-analytics-api.php
│   │   └── class-partner-api.php
│   │
│   ├── admin/                         # WordPress admin
│   │   ├── class-admin.php            # Admin menu & pages
│   │   ├── class-project-admin.php    # Project management
│   │   ├── class-lead-admin.php       # Lead management
│   │   ├── class-partner-admin.php    # Partner management
│   │   ├── class-analytics-admin.php  # Analytics dashboard
│   │   ├── class-settings-admin.php   # Plugin settings
│   │   ├── class-import-admin.php     # CSV import interface
│   │   └── class-scoring-admin.php    # Scoring weight config
│   │
│   ├── blocks/                        # Gutenberg blocks
│   │   ├── ai-assessment/             # AI chat block
│   │   ├── project-card/              # Project card block
│   │   ├── fit-score/                 # Fit score display
│   │   ├── comparison-table/          # Comparison block
│   │   ├── location-grid/             # Location cards
│   │   ├── emi-calculator/            # EMI tool block
│   │   ├── cta-assessment/            # Assessment CTA
│   │   └── trust-indicators/          # Trust bar block
│   │
│   ├── shortcodes/                    # Legacy shortcode support
│   │   └── class-shortcodes.php
│   │
│   └── helpers/                       # Utility classes
│       ├── class-sanitizer.php        # Input sanitization
│       ├── class-validator.php        # Data validation
│       ├── class-rate-limiter.php     # Rate limiting
│       ├── class-cache-helper.php     # Cache abstraction
│       ├── class-emi-calculator.php   # EMI calculation
│       └── class-geo-helper.php       # Distance calculations
│
├── admin/                             # Admin assets
│   ├── css/
│   │   └── admin.css
│   ├── js/
│   │   ├── admin.js
│   │   ├── lead-manager.js
│   │   └── scoring-config.js
│   └── views/
│       ├── dashboard.php
│       ├── leads.php
│       ├── partners.php
│       ├── analytics.php
│       ├── settings.php
│       └── import.php
│
├── public/                            # Frontend assets (built)
│   ├── css/
│   └── js/
│
├── languages/                         # i18n
│   └── tenprojects-ai-matcher.pot
│
└── tests/                             # PHPUnit tests
    ├── test-scoring-engine.php
    ├── test-lead-service.php
    ├── test-ai-service.php
    └── bootstrap.php
```

---

## User Roles & Capabilities

### Role: `tp_super_admin` (extends Administrator)

All WordPress capabilities plus:
- `manage_tp_settings` — Plugin settings, API keys, scoring weights
- `manage_tp_partners` — CRUD partners, billing, subscriptions
- `view_tp_analytics` — Full analytics access
- `export_tp_leads` — Export leads
- `manage_tp_routing` — Lead routing rules
- `delete_tp_data` — Delete customer data (GDPR)

### Role: `tp_project_manager`

- `edit_tp_projects` — Add/edit projects
- `edit_tp_developers` — Add/edit developers
- `edit_tp_locations` — Add/edit locations
- `import_tp_projects` — CSV import
- `manage_tp_inventory` — Update prices, inventory, possession
- `view_tp_leads` — View leads (no export)

### Role: `tp_content_editor`

- `edit_tp_guides` — Create/edit guides
- `edit_tp_reviews` — Manage reviews
- `edit_tp_market_reports` — Create market reports
- `edit_tp_infrastructure` — Infrastructure updates
- `edit_tp_project_editorial` — Edit project pros, cons, overview (not pricing)

### Role: `tp_data_analyst`

- `view_tp_analytics` — Full analytics
- `view_tp_leads` — View leads (no PII)
- `export_tp_analytics` — Export analytics data
- `view_tp_scoring` — View scoring data

### Role: `tp_sales_manager`

- `view_tp_leads` — Full lead access
- `manage_tp_lead_status` — Update lead status
- `assign_tp_leads` — Manual lead assignment
- `view_tp_partners` — View partner info
- `export_tp_leads` — Export leads
- `manage_tp_site_visits` — Manage site visits

### Role: `tp_partner` (external)

- `view_own_tp_leads` — View assigned leads only
- `update_own_tp_lead_status` — Update status on own leads
- `view_own_tp_billing` — View own billing
- `manage_own_tp_profile` — Edit own profile

### Role: `tp_support_agent`

- `view_tp_customers` — View customer profiles
- `view_tp_sessions` — View AI conversations
- `manage_tp_site_visits` — Handle site visit requests
- `view_tp_leads` — View leads

---

## REST API Endpoints

### Namespace: `tenprojects/v1`

#### Assessment API
```
POST   /assessment/start              Start new AI assessment session
POST   /assessment/{id}/answer        Submit answer(s) for current phase
GET    /assessment/{id}/status         Get session status & progress
POST   /assessment/{id}/skip           Skip current question
POST   /assessment/{id}/complete       Complete assessment
GET    /assessment/{id}/preview        Get partial results preview
```

#### Recommendation API
```
POST   /recommendations/generate      Generate top 10 for requirement
GET    /recommendations/{id}          Get recommendation results
GET    /recommendations/{id}/project/{pid}  Get detailed project analysis
POST   /recommendations/{id}/refresh   Re-run with updated requirements
```

#### Auth API
```
POST   /auth/otp/send                 Send OTP to phone
POST   /auth/otp/verify               Verify OTP
POST   /auth/google                   Google OAuth login
GET    /auth/me                       Get current user
POST   /auth/logout                   Logout
```

#### Customer API
```
GET    /customer/profile              Get customer profile
PUT    /customer/profile              Update profile
GET    /customer/requirements         List requirement profiles
POST   /customer/requirements         Create requirement profile
PUT    /customer/requirements/{id}    Update requirement
DELETE /customer/requirements/{id}    Delete requirement
GET    /customer/saved-projects       List saved projects
POST   /customer/saved-projects       Save a project
DELETE /customer/saved-projects/{id}  Remove saved project
GET    /customer/recommendations      List past recommendations
GET    /customer/site-visits          List site visits
POST   /customer/delete-account       Request account deletion
POST   /customer/export-data          Request data export
```

#### Project API
```
GET    /projects                      List projects (filterable)
GET    /projects/{id}                 Get project detail
GET    /projects/{id}/configurations  Get configurations
GET    /projects/{id}/scores/{req_id} Get fit score for requirement
```

#### Comparison API
```
POST   /comparisons                   Create comparison
GET    /comparisons/{id}              Get comparison
GET    /comparisons/{token}           Get comparison by share token
```

#### Lead API (Internal/Admin)
```
POST   /leads                         Create lead
GET    /leads                          List leads (admin)
GET    /leads/{id}                     Get lead detail
PUT    /leads/{id}/status              Update lead status
POST   /leads/{id}/assign              Assign to partner
GET    /leads/export                   Export leads CSV
```

#### Site Visit API
```
POST   /site-visits                   Request site visit
GET    /site-visits/{id}              Get visit details
PUT    /site-visits/{id}              Update visit
POST   /site-visits/{id}/feedback     Submit feedback
```

#### Analytics API
```
POST   /analytics/event               Track event
GET    /analytics/dashboard            Dashboard stats (admin)
GET    /analytics/funnel               Funnel data (admin)
```

#### Partner API
```
GET    /partner/leads                  Get assigned leads
PUT    /partner/leads/{id}/status      Update lead status
GET    /partner/billing                Billing info
GET    /partner/performance            Performance stats
PUT    /partner/profile                Update profile
```

### API Security

All endpoints use:
- WordPress nonces for authenticated requests
- Bearer token auth for customer-facing APIs
- Rate limiting (configurable per endpoint)
- Input sanitization via `sanitize_*` WordPress functions
- Output escaping
- Capability checks
- IP-based rate limiting for OTP endpoints
- CORS configuration

---

## WordPress Settings (Admin)

### General Settings
- Site name, logo, brand colours
- Default city
- Contact information
- Social media links

### AI Settings
- Active AI provider (Claude/OpenAI/Gemini)
- API key (encrypted)
- Model selection
- Temperature setting
- Max tokens
- System prompt template
- Fallback provider

### Scoring Settings
- Weight profiles (end-user, investor)
- Custom weight overrides
- Hard filter configuration
- Score threshold for top 10

### Lead Settings
- Default lead routing rules
- Auto-assignment toggle
- Duplicate detection window
- Lead expiry time
- Default partner SLA

### OTP Settings
- SMS provider (MSG91/Twilio)
- API key (encrypted)
- OTP validity (minutes)
- Max attempts
- Rate limit

### Notification Settings
- Email templates
- WhatsApp templates
- SMS templates
- Notification triggers

### Performance Settings
- Cache duration
- Image sizes
- Lazy loading config
- CDN URL
- Analytics script delay

---

## Caching Strategy

### Page Cache (Full-page)
- Homepage: 5 minutes
- Project pages: 15 minutes
- Location pages: 15 minutes
- Guide pages: 30 minutes
- Static pages: 60 minutes
- AI assessment pages: NO CACHE
- Results pages: NO CACHE (private)
- Dashboard pages: NO CACHE (private)

### Object Cache (Redis)
- Project data: 10 minutes
- Developer data: 30 minutes
- Location data: 30 minutes
- Configuration data: 10 minutes
- Scoring weights: 60 minutes
- Taxonomy terms: 60 minutes

### Transient Cache
- Expensive queries: 15 minutes
- API responses: 5 minutes
- Aggregated stats: 30 minutes

### Cache Invalidation
- Project updated → clear project page + related location page
- Price updated → clear project + search results
- Inventory updated → clear project page
- New project added → clear location + city page
