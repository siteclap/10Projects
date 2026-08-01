# Lead Routing, Conversion Tracking & Security

## Lead Routing Logic

### Routing Flow

```
Lead Created
    ↓
Duplicate Check (phone + 30-day window)
    ↓ (if unique)
Quality Scoring
    ↓
Classification (researching/warm/qualified/etc.)
    ↓
Match Routing Rules
    ↓
Select Best Partner(s)
    ↓
Check Partner Capacity (daily/monthly limits)
    ↓
Check Partner Availability (business hours)
    ↓
Assign Lead
    ↓
Deduct Credits
    ↓
Notify Partner (SMS + Email + Dashboard)
    ↓
Start SLA Timer
```

### Routing Rule Conditions

Each routing rule is a JSON condition set:

```json
{
  "conditions": {
    "cities": ["navi_mumbai"],
    "locations": ["kharghar", "panvel"],
    "budget_min": 5000000,
    "budget_max": 15000000,
    "configurations": ["2_bhk", "3_bhk"],
    "lead_types": ["site_visit", "best_price", "advisor"],
    "purchase_timeline_max_months": 6,
    "funding_types": ["loan", "mix"],
    "quality_score_min": 50,
    "classifications": ["qualified", "site_visit_ready"],
    "purposes": ["end_use", "both"],
    "exclusivity_required": false
  }
}
```

### Partner Selection Algorithm

```
1. Find all active routing rules matching lead conditions
2. Sort matching rules by priority (lower = higher priority)
3. For each matching rule:
   a. Check partner is active
   b. Check partner has credits remaining
   c. Check daily limit not reached
   d. Check monthly limit not reached
   e. Check current time is within business hours
   f. If exclusive lead: check partner accepts exclusives
4. Select top N partners (1 for exclusive, up to 3 for shared)
5. Prefer partners with:
   - Higher rating
   - Better response times
   - Higher conversion rates
   - Lower rejection rates
6. Create lead assignments
7. Deduct credits
8. Send notifications
```

### Lead Quality Score (0-100)

```
Completeness (max 30):
  - Phone verified: +10
  - Email provided: +5
  - Full requirement profile: +15

Intent Signals (max 40):
  - Site visit requested: +15
  - Best price requested: +10
  - Advisor requested: +5
  - Assessment completed: +5
  - Multiple projects viewed: +3
  - Comparison created: +2

Engagement (max 20):
  - Time spent on assessment: +5 (if > 3 min)
  - Return visit: +5
  - Saved projects: +5
  - Report downloaded: +5

Profile (max 10):
  - Purchase timeline < 3 months: +5
  - Loan pre-approved: +3
  - Clear budget range: +2
```

### Lead Classification Rules

```
"site_visit_ready":
  quality_score >= 70 AND site_visit_intent == true

"qualified":
  quality_score >= 60 AND purchase_timeline <= 6 months
  AND budget provided AND phone verified

"warm":
  quality_score >= 40 AND assessment completed

"researching":
  assessment started but timeline > 6 months OR quality < 40

"investor":
  purpose == "investment"

"nri":
  phone starts with non-Indian code OR self-declared

"high_budget":
  budget_maximum >= 20000000

"loan_dependent":
  funding_type == "loan" AND loan_preapproved == "no"

"long_term_nurture":
  timeline == "researching" OR purchase_timeline > 12 months
```

---

## Conversion Tracking Plan

### Event Taxonomy

#### Funnel Events (Ordered)

| Event Name | Trigger | Properties |
|------------|---------|------------|
| `page_view` | Page load | page_type, page_url, referrer |
| `assessment_started` | User clicks "Find My 10" | source (hero/quick_start/cta) |
| `assessment_phase_completed` | Phase finished | phase_number, time_spent |
| `assessment_question_answered` | Each answer | question_id, answer_value |
| `assessment_question_skipped` | Skip clicked | question_id |
| `assessment_abandoned` | Exit without completing | phase_reached, completion_rate |
| `assessment_completed` | All phases done | total_time, completion_rate |
| `contact_requested` | Phone input shown | trigger_point |
| `otp_sent` | OTP dispatched | channel (sms) |
| `otp_verified` | OTP confirmed | attempts |
| `otp_failed` | OTP wrong/expired | attempts |
| `results_viewed` | Full top 10 shown | projects_count |
| `project_clicked` | Project card clicked | project_id, rank, fit_score |
| `project_viewed` | Project detail loaded | project_id, source |
| `comparison_started` | Compare button clicked | project_ids |
| `comparison_viewed` | Comparison page loaded | project_ids |
| `project_saved` | Save clicked | project_id |
| `report_downloaded` | PDF downloaded | requirement_id |
| `whatsapp_clicked` | WhatsApp CTA clicked | project_id, page_type |
| `callback_requested` | Callback form submitted | project_id |
| `site_visit_requested` | Site visit form submitted | project_id |
| `best_price_requested` | Best price form submitted | project_id |
| `advisor_requested` | Talk to advisor clicked | page_type |
| `emi_calculated` | EMI calculator used | loan_amount, tenure |
| `share_clicked` | Share button clicked | content_type, channel |
| `login_completed` | User logged in | method |
| `requirement_updated` | Requirement changed | fields_changed |
| `assessment_rerun` | Re-run clicked | requirement_id |

#### Admin/Backend Events

| Event Name | Trigger | Properties |
|------------|---------|------------|
| `lead_created` | Lead saved | lead_type, quality_score |
| `lead_assigned` | Routed to partner | partner_id, assignment_type |
| `lead_accepted` | Partner accepts | partner_id, response_time |
| `lead_rejected` | Partner rejects | partner_id, reason |
| `lead_contacted` | First contact | partner_id, method |
| `site_visit_confirmed` | Visit confirmed | project_id |
| `site_visit_completed` | Visit done | project_id, rating |
| `booking_reported` | Booking recorded | project_id |

### UTM Parameter Handling

Capture on first visit, store in session/cookie:
- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_content`
- `utm_term`
- `referrer` (full referrer URL)
- `landing_page` (first page URL)
- `gclid` (Google Ads)
- `fbclid` (Facebook)

Attach to every lead and analytics event.

### Dashboard Metrics

#### Traffic Dashboard
- Total visitors (daily, weekly, monthly)
- Traffic by source (organic, direct, paid, social, referral)
- Top landing pages
- Bounce rate by page type
- Geographic distribution

#### Lead Funnel Dashboard
- Assessment started → Completed → Contact submitted → OTP verified → Results viewed
- Conversion rates between each step
- Drop-off points
- Average completion time
- Funnel by traffic source

#### Lead Quality Dashboard
- Leads by classification
- Average quality score
- Leads by location demand
- Leads by budget range
- Leads by configuration
- Leads by purchase timeline
- Lead source performance

#### Revenue Dashboard
- Total leads generated
- Leads assigned
- Cost per lead (by source)
- Revenue per lead
- Partner acceptance rate
- Partner response time
- Site visit conversion
- Booking conversion

---

## Security Architecture

### Authentication Security

```
OTP Flow:
1. Rate limit: max 3 OTP requests per phone per 15 minutes
2. OTP valid for 5 minutes
3. Max 3 verification attempts
4. 30-minute lockout after 3 failed attempts
5. OTP stored as hashed value
6. Different OTP for each request

Session:
- JWT token with 24-hour expiry
- Refresh token with 30-day expiry
- HTTP-only secure cookie
- Token rotation on refresh
```

### API Security

```
WordPress REST API:
- Nonce verification for logged-in users
- Bearer token for customer API
- Rate limiting per endpoint:
  - Assessment: 10 requests/minute
  - OTP send: 3 requests/15 minutes per IP
  - OTP verify: 5 requests/5 minutes per IP
  - General API: 60 requests/minute per IP
  - Admin API: 120 requests/minute

Input Sanitization:
- All text: sanitize_text_field()
- HTML content: wp_kses_post()
- Email: sanitize_email()
- Phone: regex validation + sanitize
- Numeric: intval() / floatval()
- JSON: json_decode + schema validation
- URLs: esc_url()

Output Escaping:
- HTML: esc_html()
- Attributes: esc_attr()
- URLs: esc_url()
- JavaScript: esc_js() / wp_json_encode()

Database:
- All queries via $wpdb->prepare()
- No raw SQL interpolation
- Parameterised queries only
```

### Data Protection

```
Encrypted at rest:
- API keys (AI providers, SMS providers)
- OTP codes (hashed)
- Login tokens (hashed)

Not stored:
- Full credit card numbers
- Passwords in plaintext
- Raw AI API keys in database

Access controls:
- WordPress capabilities for all admin actions
- Row-level security for partner data
- Customer can only access own data
- Partner can only access assigned leads

Data retention:
- Analytics events: 24 months
- AI conversation logs: 12 months
- Abandoned sessions: 90 days
- Deleted accounts: PII removed within 30 days
- Audit logs: 36 months
- Consent logs: permanent (legal requirement)

GDPR-like compliance:
- Data export (JSON/CSV) on request
- Account deletion on request
- Consent tracking for each purpose
- Clear privacy policy
- Cookie consent
```

### Bot Protection

```
Assessment flow:
- Honeypot fields
- Time-based validation (< 5 sec = bot)
- CAPTCHA on OTP (after 2 attempts)
- Session fingerprinting

Forms:
- WordPress nonces
- Honeypot fields
- Rate limiting
- Referrer validation
```

---

## Performance Plan

### Performance Budgets

| Resource | Budget |
|----------|--------|
| Total page weight (homepage) | < 500 KB |
| Total page weight (project page) | < 800 KB |
| JavaScript (initial) | < 50 KB |
| JavaScript (total with lazy) | < 150 KB |
| CSS | < 40 KB |
| Fonts | < 50 KB (2 fonts max) |
| Hero image | < 100 KB |
| Third-party scripts | < 30 KB initial |

### Loading Strategy

```
Critical Path (blocking):
1. Inline critical CSS (above-the-fold)
2. HTML content (server-rendered)
3. Brand font (preloaded, font-display: swap)

Deferred:
4. Full CSS (loaded async)
5. Analytics script (delayed 3 seconds)
6. Non-critical JavaScript

Lazy Loaded:
7. AI chat widget (on CTA click or scroll to section)
8. Google Maps (on scroll into view)
9. Below-fold images
10. Video embeds
11. Social sharing widgets
12. Third-party review widgets

Prefetched:
- DNS prefetch for AI API domain
- DNS prefetch for CDN
- Preconnect for Google Fonts (if used)
```

### Server-Side Optimization

```
WordPress:
- Object cache (Redis)
- Full-page cache (server level or plugin)
- Opcode cache (OPcache)
- Database query optimization (indexed queries)
- Avoid N+1 queries
- Batch meta queries

Server:
- GZIP/Brotli compression
- HTTP/2 or HTTP/3
- CDN for static assets
- Image CDN with on-the-fly resize
- SSL/TLS 1.3
- Keep-alive connections

Database:
- Proper indexes on all custom tables (defined in schema)
- Query monitoring
- Slow query log
- Regular OPTIMIZE TABLE
```

---

## Development Roadmap

### Phase 1: Foundation & MVP (Weeks 1-8)

**Week 1-2: Setup & Infrastructure**
- WordPress installation and configuration
- Theme scaffold (block theme)
- Plugin scaffold
- Database migration system
- Create all custom tables
- Register all CPTs and taxonomies
- Basic admin interface
- Development environment

**Week 3-4: Content & SEO Foundation**
- Project detail page template
- Location page template
- Developer page template
- SEO meta tags system
- Structured data implementation
- XML sitemaps
- Internal linking engine
- Breadcrumbs
- Seed data (20-30 test projects)

**Week 5-6: AI Assessment & Scoring**
- AI service abstraction layer
- Assessment flow UI (5 phases)
- Assessment REST API endpoints
- Customer profile generation
- Scoring engine implementation
- Recommendation generation
- Results page
- Contact gate (OTP)

**Week 7-8: Lead Capture & Admin**
- Lead creation service
- Lead admin view
- Basic lead routing
- WhatsApp CTA integration
- Callback form
- Site visit request
- Analytics event tracking
- Homepage design & implementation
- Mobile responsive testing
- Performance optimization
- Security audit

**MVP Deliverables:**
- Working homepage
- AI assessment flow (basic)
- Top 10 results page
- 30+ project detail pages
- 15 location pages
- Lead capture (phone, OTP)
- Admin: manage projects, view leads
- Core SEO implemented
- Mobile responsive
- < 2.5s LCP

### Phase 2: Customer Intelligence (Weeks 9-14)

- Customer login (OTP, Google)
- Customer dashboard
- Saved projects
- Project comparison (up to 4)
- Multiple requirement profiles
- Downloadable PDF report
- Enhanced AI conversation (context-aware follow-ups)
- EMI calculator tool
- Loan eligibility estimator
- Advanced scoring (all 20 categories)
- Risk profiling
- AI recommendation explanations
- Comparison pages (SEO)
- Guide pages (SEO)
- Budget and config guide pages

### Phase 3: Lead Marketplace (Weeks 15-20)

- Partner registration
- Partner dashboard
- Lead routing engine (full)
- Lead credit system
- Billing integration
- Lead acceptance/rejection
- Partner SLA tracking
- Lead dispute system
- Partner performance reports
- Developer dashboard (basic)
- Lead export (CSV)

### Phase 4: Scale & Automation (Weeks 21-30)

- Multi-city expansion framework
- Bulk CSV project import
- Automated data update alerts
- CRM integration (Zoho/Salesforce webhooks)
- WhatsApp automation (template messages)
- Call tracking integration
- Multilingual support (Hindi)
- Personalised email nurture sequences
- A/B testing framework
- Advanced analytics dashboards
- Market report generation
- AI voice advisor (research phase)

---

## Risks & Mitigation

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| AI hallucination (wrong prices/RERA) | High | Medium | Ground AI in verified data only; never let AI generate facts |
| Low assessment completion rate | High | Medium | A/B test flow length; progressive disclosure; skip options |
| Slow page speed with AI | Medium | Medium | Lazy-load AI; server-side rendering for SEO pages |
| Lead quality complaints from partners | High | Medium | Quality scoring; SLA; refund policy; verification steps |
| Data accuracy decay | High | High | Automated stale-data alerts; verification workflow; last-verified dates |
| AI API costs | Medium | Low | Cache AI responses; batch scoring; use smaller models where possible |
| SEO competition with established portals | Medium | High | Focus on long-tail, location-specific, balanced content; GEO optimization |
| WordPress performance at scale | Medium | Medium | Custom tables; object cache; CDN; page cache; optimized queries |
| Regulatory risk (RERA compliance) | High | Low | Clear disclaimers; link to official RERA; never claim verified without source |
| Customer trust in AI recommendations | High | Medium | Transparent methodology; show scoring; balanced pros/cons |
| Partner adoption | High | Medium | Free trial credits; prove lead quality; response time SLA |
| Duplicate lead disputes | Medium | High | Phone-based dedup; 30-day window; clear dispute resolution |

---

## Recommended MVP Scope

### Include in MVP
1. Homepage with hero + quick start
2. AI assessment (5 phases, core questions only)
3. Scoring engine (top 15 categories)
4. Top 10 results page
5. Project detail page (full SEO structure)
6. Location pages (15 Navi Mumbai locations)
7. OTP-based contact capture
8. Lead storage and admin view
9. Basic lead routing (manual + auto by location)
10. WhatsApp CTA
11. Callback request
12. Site visit request
13. SEO foundations (meta, schema, sitemaps, internal links)
14. Mobile responsive
15. Performance optimized (< 2.5s LCP)
16. Security hardened
17. 30+ real project listings
18. Analytics event tracking
19. Admin: project CRUD, lead view, basic settings

### Defer to Phase 2+
- Customer login and dashboard
- Project comparison
- PDF reports
- Guide pages
- Comparison SEO pages
- Partner dashboard
- Billing system
- CRM integration
- Multi-city
- Multilingual
- Advanced analytics dashboards
- A/B testing
- WhatsApp automation
- Call tracking
