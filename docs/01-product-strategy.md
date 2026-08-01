# 10Projects.com — Product Strategy & Architecture

## 1. Product Strategy Summary

### Vision
10Projects.com is an AI-powered real estate discovery platform that eliminates listing overload. Instead of showing customers hundreds of properties, the platform uses an intelligent AI advisor to understand each buyer deeply, then recommends exactly 10 best-fit projects — ranked, scored, and explained.

### Core Value Proposition
**For Customers:** "Tell us what you need. Our AI will find the 10 projects that fit you best."
**For Partners:** "Receive pre-qualified, intent-scored leads with complete buyer profiles."

### Business Model
- **Revenue Source:** Selling qualified, verified property leads to channel partners, developers, mandate firms, and project sales teams.
- **Pricing Models:** Pay-per-lead, credit-based plans, monthly subscriptions, hybrid models, exclusive vs. shared leads.
- **Customer-Facing Positioning:** Trusted AI property decision assistant — never a lead-selling website from the customer's perspective.

### Competitive Differentiation

| Factor | Traditional Portals | 10Projects.com |
|--------|-------------------|----------------|
| Discovery | Browse hundreds of listings | AI finds your top 10 |
| Experience | Filter-based search | Conversational AI advisor |
| Information | Promotional listings | Balanced pros, cons, risks |
| Lead Quality | Form fills, low intent | Profiled, scored, qualified |
| Transparency | Developer-biased | Fit Score methodology public |
| Content | Thin listing pages | Deep analysis pages |

### Market Entry
- **Phase 1:** Navi Mumbai (Kharghar, Panvel, Taloja, Nerul, Ulwe, Airoli, Vashi, Ghansoli, Seawoods, Kamothe, Dronagiri, New Panvel, Belapur, Sanpada, Upper Kharghar)
- **Phase 2:** Mumbai MMR (Thane, Kalyan-Dombivli, Mira-Bhayandar, Vasai-Virar)
- **Phase 3:** Pune
- **Phase 4:** Other Tier-1 Indian cities

### Success Metrics (Year 1)
- 3,000+ monthly leads (all capture levels combined)
- 75%+ Quick Match completion rate (3 questions, 30 sec)
- 50%+ Phase 2 completion rate (from Phase 1 completers)
- 40%+ OTP conversion (from result viewers)
- 30+ leads per 1,000 visitors (multi-level capture)
- Lead quality score > 7/10 (partner feedback)
- 15%+ site-visit conversion from qualified leads
- <2.5s LCP on mobile
- 500+ indexed project/location pages

> **CRO Audit Note:** Original "60% assessment completion rate" was unrealistic
> for a 25-question, 5-phase flow. Restructured to 3 phases with instant results
> after Phase 1. Also added multi-level capture (email, WhatsApp, exit-intent)
> to capture the 70% of organic traffic that never starts assessment.

---

## 2. Customer Personas

### Persona 1: First-Time Home Buyer (Rahul & Priya)
- **Age:** 28–35
- **Income:** ₹12–25 LPA combined
- **Budget:** ₹60L–₹1.2Cr
- **Profile:** Married, possibly first child, both working
- **Motivation:** First home for family, end-use
- **Pain Points:** Overwhelmed by options, confused by builder promises, unsure about loan eligibility, scared of delays
- **Decision Style:** Research-heavy, seeks trusted advice, risk-averse
- **Timeline:** 3–6 months
- **Preferred Channels:** Google search, WhatsApp, Instagram
- **Key Questions:** "Can I afford this?", "Will possession happen on time?", "Is this location good for families?"

### Persona 2: Upgrade Buyer (Sandeep)
- **Age:** 35–48
- **Income:** ₹25–50 LPA
- **Budget:** ₹1.2Cr–₹2.5Cr
- **Profile:** Family of 4, owns 1 BHK/2 BHK, wants upgrade
- **Motivation:** More space, better lifestyle, children's school proximity
- **Pain Points:** Needs to sell existing property or manage two EMIs, wants established developer
- **Decision Style:** Experienced, knows locations, wants data-backed comparison
- **Timeline:** 3–12 months
- **Key Questions:** "Which project gives best value for 3 BHK?", "Which developer has best track record?", "What's the resale potential?"

### Persona 3: Pure Investor (Meera)
- **Age:** 30–55
- **Income:** ₹30–80 LPA
- **Budget:** ₹50L–₹3Cr
- **Profile:** Already owns property, looking for capital appreciation or rental income
- **Motivation:** Wealth creation, portfolio diversification, early-stage entry
- **Pain Points:** Needs credible appreciation data, worried about exit liquidity, wants to compare locations
- **Decision Style:** Numbers-driven, compares multiple options, higher risk tolerance
- **Timeline:** Immediate to 6 months
- **Key Questions:** "What's the appreciation potential?", "Is this location emerging?", "Can I flip in 2–3 years?"

### Persona 4: NRI Buyer (Vikram)
- **Age:** 35–50
- **Budget:** ₹1Cr–₹5Cr
- **Profile:** Working abroad, wants property in India for parents, retirement, or investment
- **Motivation:** Asset creation in India, family housing, retirement planning
- **Pain Points:** Cannot visit sites easily, trust issues, needs reliable on-ground partner, unclear on NRI loan rules
- **Decision Style:** Relies heavily on digital research, needs video/virtual tours, wants reputed developers
- **Timeline:** 6–12 months
- **Key Questions:** "Can I trust this developer from abroad?", "What's the NRI loan process?", "Will the property be well-maintained?"

### Persona 5: Parent Buyer (Suresh & Kavita)
- **Age:** 50–65
- **Budget:** ₹40L–₹1.5Cr
- **Profile:** Buying for adult children or for retirement
- **Motivation:** Secure investment for next generation, downsizing, senior-friendly living
- **Pain Points:** Limited digital literacy, needs simple interface, values in-person guidance
- **Decision Style:** Conservative, prefers known developers, wants ready or near-ready possession
- **Timeline:** 1–6 months
- **Key Questions:** "Is this safe for seniors?", "Is possession guaranteed?", "How far is the hospital?"

---

## 3. Primary Customer Journeys

### Journey A: AI-First Discovery (Primary Flow — RESTRUCTURED)

```
Landing Page → Select BHK chip → CTA "Find My 10 Projects"
    → Phase 1 Quick Match (3 questions, 30 sec): Config + Location + Budget
    → INSTANT TOP 5 results shown (no login needed)
        → "Want better accuracy? 4 more questions" →
    → Phase 2 Better Match (4 questions, 45 sec): Purpose + Timeline + Funding + Priorities
    → REFINED TOP 10 shown (still no login)
        → "Almost perfect — 3 more?" →
    → Phase 3 Perfect Match (3-4 questions, 30 sec): EMI + Possession + Must-haves
    → FULL TOP 10 with scores
    → Trust Shield + Phone + OTP → Full analysis unlocked
    → Contact preferences (WhatsApp/Phone/Email, time, scope)
    → Full Results Page
        → Project Click → Detail Page
        → Compare → Comparison Page
        → Save → Dashboard
        → "WhatsApp: Get Best Price" → Lead
        → "Book Site Visit" → Lead
        → "Talk to Advisor" → Lead
    → Auto-delivery: Results sent to WhatsApp
```

### Journey B: Organic Search Discovery

```
Google Search → Project Detail Page / Location Page
    → Read project analysis (pros, cons, risks, suitability)
    → CTA: "See if this project fits your needs"
    → AI Assessment (pre-filled with current project context)
    → Top 10 Results (current project may or may not rank)
    → Conversion actions
```

### Journey C: Comparison Seeker

```
Google Search → "Project A vs Project B" Comparison Page
    → Detailed comparison with Fit Score context
    → CTA: "Find which one fits you better"
    → AI Assessment → Top 10 → Conversion
```

### Journey D: Location Explorer

```
Google Search → Location Page (e.g., "Best projects in Kharghar")
    → Location analysis, top projects, investment outlook
    → CTA: "Get personalized recommendations for Kharghar"
    → AI Assessment → Top 10 → Conversion
```

### Journey E: Return Visitor

```
Direct/Email/WhatsApp → Login
    → Dashboard → Previous recommendations
    → Re-run assessment (requirements changed)
    → New Top 10 → Conversion
```

### Journey F: WhatsApp-First (NEW — India's #1 channel)

```
WhatsApp link (from homepage, ad, or referral)
    → WhatsApp bot starts assessment
    → 3 questions via chat (BHK, location, budget)
    → Bot sends top 3 results in WhatsApp
    → "See all 10 with detailed analysis" → Link to web results
    → Phone captured automatically from WhatsApp = LEAD
```

### Journey G: Browser → Micro-Capture → Nurture (NEW — 70% of organic traffic)

```
Google Search → Project/Location page → Reads content
    → After 30 sec: bottom slide-up "Get price updates" [email]
    → OR: WhatsApp CTA clicked (phone captured)
    → OR: Views 3+ pages → "Want us to rank these for you?"
    → Micro-lead captured → Nurture email/WhatsApp sequence
    → Eventually starts Quick Match → Full lead
```

### Journey H: Human Advisor (NEW — non-digital personas)

```
Homepage → "Prefer to talk to someone?" → Callback form
    → Name + Phone + "What are you looking for?" (text area)
    → Internal team calls, qualifies manually
    → Lead entered into system
    → Optional: advisor enters requirements → system generates top 10
```

---

## 4. Sitemap

```
/                                       Homepage
├── /start/                             AI Assessment Entry
├── /results/                           Personalized Results (auth required)
│   └── /results/{session-id}/          Specific Result Set
│
├── /projects/                          All Projects Browse
│   └── /city/location/project-name/    Individual Project Page
│
├── /locations/                         All Locations
│   ├── /navi-mumbai/                   City Page
│   │   ├── /navi-mumbai/kharghar/      Location Page
│   │   ├── /navi-mumbai/panvel/
│   │   ├── /navi-mumbai/taloja/
│   │   └── ...
│   └── /mumbai/                        (Future)
│
├── /developers/                        All Developers
│   └── /developers/{developer-slug}/   Developer Profile
│
├── /compare/                           Comparison Landing
│   └── /compare/{project-a}-vs-{project-b}/  Comparison Page
│
├── /guides/                            Property Guides
│   ├── /guides/best-2bhk-in-kharghar/
│   ├── /guides/best-projects-under-1-crore-navi-mumbai/
│   ├── /guides/best-projects-near-navi-mumbai-airport/
│   └── ...
│
├── /market-insights/                   Market Reports & Insights
│
├── /tools/                             Buyer Tools
│   ├── /tools/emi-calculator/
│   ├── /tools/affordability-checker/
│   └── /tools/stamp-duty-calculator/
│
├── /account/                           Customer Account (auth required)
│   ├── /account/dashboard/
│   ├── /account/requirements/
│   ├── /account/saved/
│   ├── /account/comparisons/
│   ├── /account/site-visits/
│   ├── /account/reports/
│   └── /account/settings/
│
├── /methodology/                       Fit Score & Ranking Methodology
├── /about/                             About 10Projects
├── /privacy-policy/                    Privacy Policy
├── /terms/                             Terms of Use
├── /disclaimer/                        Disclaimers
├── /contact/                           Contact
└── /sitemap.xml                        XML Sitemap
```

---

## 5. Information Architecture

### Content Hierarchy

```
Level 0: Homepage
    │
Level 1: Primary Sections
    ├── AI Assessment Flow
    ├── Projects
    ├── Locations
    ├── Developers
    ├── Guides & Comparisons
    ├── Market Insights
    ├── Tools
    └── Customer Account
    │
Level 2: City/Category Pages
    ├── /navi-mumbai/ (city landing)
    ├── /projects/ (all projects)
    ├── /developers/ (all developers)
    └── /guides/ (all guides)
    │
Level 3: Location/Entity Pages
    ├── /navi-mumbai/kharghar/ (location detail)
    ├── /developers/godrej-properties/ (developer detail)
    └── /guides/best-2bhk-kharghar/ (guide detail)
    │
Level 4: Individual Pages
    └── /navi-mumbai/kharghar/project-name/ (project detail)
```

### Navigation Structure

**Primary Nav:**
- Find My 10 Projects (CTA button)
- Projects (dropdown: By City, By Config, By Budget, New Launches)
- Locations (dropdown: Cities → Locations)
- Guides
- Market Insights

**Secondary Nav:**
- Login / My Account
- Saved Projects
- Compare

**Footer Nav:**
- About, Methodology, Privacy, Terms, Disclaimer, Contact
- City links, Popular location links, Popular project links
- Social links

### Data Relationships

```
City (1) ──→ (N) Locations
Location (1) ──→ (N) Micro-locations
Location (1) ──→ (N) Projects
Developer (1) ──→ (N) Projects
Project (1) ──→ (N) Configurations
Project (1) ──→ (N) Reviews
Project (N) ←──→ (N) Amenities
Customer (1) ──→ (N) Requirement Profiles
Customer (1) ──→ (N) AI Sessions
AI Session (1) ──→ (N) Recommendations
Customer (N) ←──→ (N) Saved Projects
Lead (1) ──→ (N) Lead Assignments
Partner (1) ──→ (N) Lead Assignments
```

---

## 6. Homepage Wireframe (CRO-OPTIMISED)

> **Changes from audit:** Quick Start section REMOVED (caused choice paralysis).
> BHK chip integrated into hero (pre-commits user with 1 tap). Social proof
> numbers added. Human advisor path added. WhatsApp entry added.

```
┌─────────────────────────────────────────────────────────┐
│ HEADER                                                   │
│ [Logo: 10Projects]    Projects ▾  Locations ▾  Guides   │
│                                    [Login] [Find My 10] │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ HERO SECTION                                             │
│                                                          │
│    Find the 10 projects                                  │
│    that fit you best.                                    │
│                                                          │
│    Join 12,847 smart buyers who stopped browsing         │
│    and started deciding.                                 │
│                                                          │
│    I'm looking for:                                      │
│    [1 BHK] [2 BHK●] [3 BHK] [4 BHK+]                  │
│                                                          │
│    ┌──────────────────────────────────┐                  │
│    │   [Find My 10 Projects →]       │ ← Primary CTA   │
│    └──────────────────────────────────┘                  │
│    30 seconds • 3 questions • Free                       │
│                                                          │
│    Explore All Projects →              ← Secondary      │
│    Prefer WhatsApp? Chat with our advisor →              │
│                                                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ SOCIAL PROOF BAR                                         │
│ 12,847 buyers   │ 156 projects  │ 94% said              │
│ matched this    │ analysed      │ "accurate"             │
│ month           │               │                        │
│                                                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ HOW IT WORKS                                             │
│                                                          │
│ ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐    │
│ │  Step 1  │  │  Step 2  │  │  Step 3  │  │  Step 4  │  │
│ │  Tell    │  │   We     │  │  Get     │  │  Take    │  │
│ │  Us Your │  │  Analyse │  │  Your    │  │  Action  │  │
│ │  Needs   │  │  Market  │  │  Top 10  │  │          │  │
│ │ (3 min)  │  │(100+ pr) │  │ Ranked   │  │ Visit/   │  │
│ │          │  │          │  │ & Scored │  │ Compare  │  │
│ └─────────┘  └─────────┘  └─────────┘  └─────────┘    │
│                                                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ EXAMPLE RESULTS PREVIEW                                  │
│                                                          │
│ "Here's what Rahul's top 10 looked like"                │
│                                                          │
│ ┌───────────────────┐ ┌───────────────────┐             │
│ │ #1  Project Alpha  │ │ #2  Project Beta   │            │
│ │ Kharghar | 2 BHK   │ │ Panvel | 2 BHK     │           │
│ │ ₹82L–₹95L         │ │ ₹68L–₹78L          │           │
│ │ Fit: 94%           │ │ Fit: 89%            │           │
│ │ ✓ Budget match     │ │ ✓ Great ROI         │           │
│ │ ✓ Railway 800m     │ │ ✓ Early-stage price │           │
│ │ ⚠ Possession 2028 │ │ ⚠ Commute 45 min   │           │
│ └───────────────────┘ └───────────────────┘             │
│                                                          │
│ [Get your personalized top 10 →]                        │
│                                                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ WHY ONLY 10?                                             │
│                                                          │
│ Traditional portals show 500+ results.                   │
│ You end up confused, not confident.                      │
│                                                          │
│ We analyse every project against YOUR priorities:        │
│                                                          │
│ Budget Fit │ Location │ Risk │ Commute │ Lifestyle      │
│ Possession │ Developer│ ROI  │ EMI Fit │ Family Fit     │
│                                                          │
│ Then we rank the 10 that fit you best.                   │
│ With honest pros, cons, and risks.                       │
│                                                          │
│ [Read our scoring methodology →]                        │
│                                                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ POPULAR LOCATIONS                                        │
│                                                          │
│ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐       │
│ │Kharghar │ │ Panvel  │ │ Taloja  │ │ Ulwe    │       │
│ │42 proj  │ │ 35 proj │ │ 28 proj │ │ 22 proj │       │
│ │₹65L avg │ │₹52L avg │ │₹45L avg │ │₹55L avg │       │
│ └─────────┘ └─────────┘ └─────────┘ └─────────┘       │
│                                                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ TRUST INDICATORS                                         │
│                                                          │
│ "Every project includes honest pros, cons & risks"       │
│                                                          │
│ ✓ RERA-verified data          ✓ Unbiased rankings       │
│ ✓ No hidden promotions        ✓ Transparent methodology │
│ ✓ Developer track records     ✓ Risk assessments        │
│                                                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ BUYER TESTIMONIALS                                       │
│                                                          │
│ "I was comparing 50 projects. 10Projects gave me         │
│  clarity in 5 minutes. Booked my first home."            │
│  — Rahul, Kharghar buyer                                 │
│                                                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ LATEST MARKET INSIGHTS                                   │
│                                                          │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐     │
│ │ Navi Mumbai  │ │ New Airport  │ │ Metro Update │     │
│ │ Price Trends │ │ Impact on    │ │ Connectivity │     │
│ │ Q2 2026      │ │ Property     │ │ Changes      │     │
│ └──────────────┘ └──────────────┘ └──────────────┘     │
│                                                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ FINAL CTA                                                │
│                                                          │
│    Ready to find your best-fit projects?                 │
│                                                          │
│    ┌──────────────────────────────────┐                  │
│    │   [Find My 10 Projects]          │                  │
│    └──────────────────────────────────┘                  │
│                                                          │
│    Takes 3-5 minutes. Completely free.                   │
│                                                          │
├─────────────────────────────────────────────────────────┤
│ FOOTER                                                   │
│                                                          │
│ About │ Methodology │ Privacy │ Terms │ Disclaimer      │
│ Contact │ Careers                                        │
│                                                          │
│ Cities: Navi Mumbai │ Mumbai (coming soon)               │
│ Locations: Kharghar │ Panvel │ Taloja │ Ulwe │ ...      │
│ Popular: Best 2BHK Kharghar │ Under ₹1Cr │ ...         │
│                                                          │
│ © 2026 10Projects.com                                    │
│ RERA disclaimer text                                     │
└─────────────────────────────────────────────────────────┘
```

### Mobile Wireframe (CRO-OPTIMISED)

```
┌─────────────────────┐
│ [≡] 10Projects [👤] │
├─────────────────────┤
│                     │
│ Find the 10         │
│ projects that       │
│ fit you best.       │
│                     │
│ 12,847 buyers       │
│ matched this month. │
│                     │
│ I'm looking for:    │
│ [1BHK] [2BHK●]     │
│ [3BHK] [4BHK+]     │
│                     │
│ ┌─────────────────┐ │
│ │ Find My 10      │ │
│ │ Projects →      │ │
│ └─────────────────┘ │
│ 30 sec • 3 Qs • Free│
│                     │
│ [Chat on WhatsApp →]│
│                     │
│ ─── How It Works ── │
│ [Step cards scroll] │
│                     │
│ ─ Example Results ─ │
│ "See what Priya's   │
│  top 10 looked like"│
│ [Result preview]    │
│                     │
│ ─── Popular ─────── │
│ [Location chips]    │
│                     │
│ ── Prefer to talk?──│
│ [Request Callback]  │
│ A human advisor will│
│ call you.           │
│                     │
│ ┌─────────────────┐ │
│ │ Find My 10 →    │ │ ← Sticky bottom CTA
│ └─────────────────┘ │
└─────────────────────┘
```
