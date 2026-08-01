# Design System & UI/UX Specification

## Brand Identity

### Brand Name
**10Projects** (always written as "10Projects" — numeral + word, no space)

### Tagline
- Primary: "You need one right home — not hundreds of listings."
- Functional: "Your 10 best-fit projects, ranked by AI."
- Action: "Stop browsing. Start deciding."

### Brand Personality
Intelligent, honest, helpful, direct, data-driven, calm, premium — never salesy.

---

## Colour System

### Primary Palette

```
Brand Primary:       #1A56DB (Royal Blue — trust, intelligence)
Brand Primary Dark:  #1E40AF
Brand Primary Light: #3B82F6
Brand Accent:        #F59E0B (Amber Gold — premium, value, scores)
Brand Accent Dark:   #D97706
```

### Neutral Palette

```
Gray 900:  #111827  (Primary text)
Gray 700:  #374151  (Secondary text)
Gray 500:  #6B7280  (Muted text, labels)
Gray 300:  #D1D5DB  (Borders)
Gray 200:  #E5E7EB  (Dividers)
Gray 100:  #F3F4F6  (Backgrounds)
Gray 50:   #F9FAFB  (Subtle backgrounds)
White:     #FFFFFF  (Card backgrounds)
```

### Semantic Colours

```
Success:    #10B981  (Verified, positive — green)
Warning:    #D97706  (Trade-off, caution — dark amber)
Danger:     #EF4444  (Risk, concern — red)
Info:       #3B82F6  (Information — blue)

Fit Score (Amber Gold scale):
90-100:  #F59E0B  (Excellent — gold)
80-89:   #F59E0B at 80% opacity  (Very Good — gold)
70-79:   #D97706  (Good — dark amber)
60-69:   #9CA3AF  (Moderate — gray)
Below:   #9CA3AF at 60% opacity  (Weak — faded gray)
```

### Background Colours

```
Page Background:     #F9FAFB
Card Background:     #FFFFFF
Hero Background:     #111827 (dark navy — brand differentiator)
Section Alt:         #F3F4F6
Dark Section:        #111827 (for contrast sections)
```

---

## Typography

### Font Stack

```
Headings:  'Inter', -apple-system, system-ui, sans-serif
Body:      'Inter', -apple-system, system-ui, sans-serif
Monospace: 'JetBrains Mono', 'Fira Code', monospace (for prices, numbers)
```

Use system fonts as fallback. Self-host Inter (400, 500, 600, 700 weights only).

### Scale

```
Display:     48px / 56px line-height / 700 weight  (Hero headline)
H1:          36px / 44px / 700                      (Page titles)
H2:          28px / 36px / 600                      (Section heads)
H3:          22px / 30px / 600                      (Subsection heads)
H4:          18px / 26px / 600                      (Card titles)
Body Large:  18px / 28px / 400                      (Intro text)
Body:        16px / 24px / 400                      (Default text)
Body Small:  14px / 20px / 400                      (Secondary text)
Caption:     12px / 16px / 500                      (Labels, tags)
Price:       24px / 32px / 700 / monospace          (Prices)
Score:       32px / 40px / 700 / monospace          (Fit scores)
```

### Mobile Scale (multiply by 0.875)
```
Display:   36px
H1:        28px
H2:        24px
H3:        20px
H4:        17px
Body:      16px (unchanged)
```

---

## Spacing System

```
4px   — xs (tight spacing, icon gaps)
8px   — sm (within components)
12px  — md (between related elements)
16px  — lg (padding inside cards)
24px  — xl (between sections)
32px  — 2xl (section padding)
48px  — 3xl (major section gaps)
64px  — 4xl (hero padding)
96px  — 5xl (page section spacing)
```

---

## Component Library

### 1. Buttons

```
Primary Button:
┌──────────────────────────┐
│   Find My 10 Projects    │  bg: #1A56DB, text: white
└──────────────────────────┘  border-radius: 8px
                              padding: 12px 24px
                              font: 16px/600
                              hover: #1E40AF
                              focus: ring #3B82F6

Secondary Button:
┌──────────────────────────┐
│   Compare Projects       │  bg: white, border: #D1D5DB
└──────────────────────────┘  text: #374151
                              hover: bg #F3F4F6

Ghost Button:
  Explore All Projects →      text: #1A56DB, no bg
                              hover: underline

Danger Button:
┌──────────────────────────┐
│   Remove                 │  bg: #FEE2E2, text: #EF4444
└──────────────────────────┘  hover: bg #FECACA

Small Button:               padding: 8px 16px, font: 14px
Large Button:               padding: 16px 32px, font: 18px
Icon Button:                40px × 40px circle

Disabled:                   opacity: 0.5, cursor: not-allowed
Loading:                    spinner icon, text "Processing..."
```

### 2. Cards

```
Project Card:
┌──────────────────────────────────────────────┐
│ [Project Image — 16:9 ratio]                 │
│                                              │
│ ┌──────┐  Skyline Heights          #1 94%   │
│ │ LOGO │  ABC Realty                ██████   │
│ └──────┘  Kharghar, Navi Mumbai     Fit     │
│                                              │
│ EMI from ₹56,000/month*                      │
│ 2 BHK • 680 sq ft • All-in: ₹88L–₹98L      │
│ Possession: Dec 2028                         │
│                                              │
│ ✓ Within budget  ✓ 25 min commute           │
│ ✓ Established developer                     │
│ ⚠ Possession 2+ years away                  │
│                                              │
│ RERA: P520XXXXX                              │
│                                              │
│ [View Details]  [Compare]  [♡ Save]         │
└──────────────────────────────────────────────┘

bg: white
border: 1px solid #E5E7EB
border-radius: 12px
shadow: 0 1px 3px rgba(0,0,0,0.08)
hover shadow: 0 4px 12px rgba(0,0,0,0.12)
padding: 0 (image flush) + 20px (content)
```

```
Location Card:
┌──────────────────────┐
│ [Location Image]     │
│                      │
│ Kharghar             │
│ 42 projects          │
│ Avg ₹8,500/sq ft     │
│ ↑ 8.2% (3yr CAGR)   │
│                      │
│ [Explore →]          │
└──────────────────────┘
```

### 3. Fit Score Badge (Amber Gold)

```
Circle badge:
    ┌─────┐
    │ 94  │    Size: 56px circle
    │ FIT │    Font: 24px score, 10px label
    └─────┘    Color: Amber Gold (#F59E0B) bg, white text
               Border: 2px white
               90+: full gold, 80-89: 80% gold, 70-79: dark amber, <70: gray

Inline badge:
    94% Fit  ████████████░░   Gold progress bar + number

Small badge:
    94%  ← Gold text, no background
```

### 4. Chips / Tags

```
Selected:     bg: #1A56DB, text: white, rounded-full
Unselected:   bg: #F3F4F6, text: #374151, border: #D1D5DB
Risk tag:     bg: #FEF3C7, text: #92400E (dark amber — distinct from brand gold)
RERA tag:     bg: #D1FAE5, text: #065F46 (green)
New Launch:   bg: #DBEAFE, text: #1E40AF (blue)
```

### 5. Progress Indicator

```
Assessment Progress (3 phases):
[●●○] Phase 2 of 3 — Better Match

───●────●────○───
Quick    Better  Perfect
Match    Match   Match

Phase indicators include Match Accuracy %:
"Match Accuracy: 82%" → grows as phases complete
```

### 6. Form Elements

```
Text Input:
┌──────────────────────────────┐
│ ₹ 95,00,000                 │  border: #D1D5DB
└──────────────────────────────┘  focus border: #1A56DB
  Comfortable budget                  border-radius: 8px
                                      padding: 12px 16px

Slider:
  ₹20K ────────●──────── ₹1L
  Label below handle: ₹65,000

Radio Cards:
  ┌──────────┐  ┌──────────┐  ┌──────────┐
  │ ○ Safety │  │ ● Balance│  │ ○ Growth │
  │   First  │  │   Buyer  │  │  Seeker  │
  └──────────┘  └──────────┘  └──────────┘
  Selected: blue border, light blue bg

Checkbox:
  [✓] Swimming Pool     [✓] Gym     [ ] Clubhouse
```

### 7. Trust Indicators

```
Trust Bar (below hero):
────────────────────────────────────────────
✓ RERA Verified  ✓ Unbiased  ✓ Transparent
  Data              Rankings     Methodology
────────────────────────────────────────────
```

### 8. CTA Sections

```
Contextual CTA (after project analysis):
┌──────────────────────────────────────────────┐
│                                              │
│  Interested in Skyline Heights?              │
│                                              │
│  [Request Best Price]  [Book Site Visit]     │
│                                              │
│  Or [Talk to an Advisor] for guidance        │
│                                              │
└──────────────────────────────────────────────┘
```

### 9. Comparison Table

```
┌──────────┬───────────────┬───────────────┬───────────────┐
│ Factor   │ Skyline Hts   │ Green Valley  │ Palm Residncy │
├──────────┼───────────────┼───────────────┼───────────────┤
│ Fit Score│ 94%  ████████ │ 89%  ███████  │ 82%  ██████  │
│ Price    │ ₹88L–₹98L    │ ₹75L–₹85L    │ ₹92L–₹1.1Cr │
│ Area     │ 680 sq ft     │ 620 sq ft     │ 720 sq ft    │
│ Possess. │ Dec 2028      │ Mar 2029      │ Jun 2028     │
│ Developer│ ★★★★☆ 85%    │ ★★★☆☆ 70%    │ ★★★★★ 92%   │
│ Railway  │ 1.2 km ✓      │ 2.5 km ⚠     │ 0.8 km ✓     │
│ Risk     │ Medium        │ Medium-High   │ Low          │
└──────────┴───────────────┴───────────────┴───────────────┘

Best cells: green background
Worst cells: light red background
```

---

## Page Layouts

### Homepage Layout (Desktop)

```
┌─────────────────────────────────────────────────────┐
│ HEADER (sticky, 64px)                                │
│ Logo    Nav     Login   [Find My 10] CTA button     │
├─────────────────────────────────────────────────────┤
│                                                      │
│ HERO (480px height, #111827 dark navy bg)            │
│                                                      │
│    [Left 60%]              [Right 40%]               │
│    Find the 10              Results mockup            │
│    projects that            (gold Fit Score           │
│    fit you best.             badges visible)          │
│                                  #FFFFFF text        │
│    You need one right home                           │
│    — not hundreds of listings.   #D1D5DB subtitle   │
│                                                      │
│    [Find My 10 Projects →]       #1A56DB bg, white  │
│    "30 sec • 3 questions • Free"                    │
│                                                      │
│    12,847 buyers matched this month  #9CA3AF muted  │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ SOCIAL PROOF BAR (white bg, 3-column)               │
│ 12,847 buyers matched │ 156 projects │ 94% accuracy │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ HOW IT WORKS (3 steps horizontal)                    │
│ 1. Answer 3 Qs  2. See Top 5 Instantly  3. Refine  │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ EXAMPLE RESULTS (2-3 project cards in a row)         │
│ "See what personalised results look like"            │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ WHY ONLY 10 (2 column: text left, visual right)     │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ POPULAR LOCATIONS (4 cards in row, scrollable)      │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ TRUST SECTION (icons + text)                         │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ TESTIMONIALS (carousel, 1-2 visible)                │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ MARKET INSIGHTS (3 cards)                           │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ FINAL CTA (dark bg, centered)                       │
│ "Ready to find your best-fit projects?"              │
│ [Find My 10 Projects]                               │
│                                                      │
├─────────────────────────────────────────────────────┤
│ FOOTER                                               │
│ Links, locations, legal, social                      │
└─────────────────────────────────────────────────────┘
```

### AI Assessment Interface (3-Phase Flow)

```
PHASE 1 — QUICK MATCH (3 questions, 30 sec):
┌─────────────────────────────────────────────────────┐
│ HEADER (minimal — logo only, no full nav)           │
│ [← Back]  10Projects  [Save & Exit]                │
├─────────────────────────────────────────────────────┤
│                                                      │
│ Progress: [●○○] Quick Match — 30 sec, 3 questions   │
│ Match Accuracy: 72%                                  │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│  ┌───────────────────────────────────────────┐      │
│  │                                           │      │
│  │  What are you looking for?                │      │
│  │                                           │      │
│  │  [1 BHK] [2 BHK] [3 BHK] [3+ BHK]      │      │
│  │                                           │      │
│  │  Where?                                   │      │
│  │  [Kharghar] [Panvel] [Ulwe] [Vashi]      │      │
│  │  [Airoli] [Ghansoli] [More ▼]            │      │
│  │                                           │      │
│  │  Budget range?                            │      │
│  │  [₹30-50L] [₹50-75L] [₹75L-1Cr]        │      │
│  │  [₹1-1.5Cr] [₹1.5-2Cr] [₹2Cr+]        │      │
│  │                                           │      │
│  │  [See My Top 5 →]                        │      │
│  │  "30 sec • 3 questions • Free"           │      │
│  │                                           │      │
│  └───────────────────────────────────────────┘      │
│                                                      │
└─────────────────────────────────────────────────────┘

After Phase 1: INSTANT TOP 5 RESULTS shown.
Then encouraged to continue:

PHASE 2 — BETTER MATCH (4 questions):
  Purpose → Timeline → Funding → Top 3 Priorities
  Results update live → Refined Top 10 with Fit Scores

PHASE 3 — PERFECT MATCH (3-4 questions):
  EMI comfort → Possession year → Must-haves → Notes
  Final scores + full analysis

Centered, max-width 640px
Clean white background
No distractions
Keyboard navigation support
Mobile: full-screen tap-only experience
Results shown after EVERY phase (not just at end)
```

### Results Page

```
┌─────────────────────────────────────────────────────┐
│ HEADER                                               │
├─────────────────────────────────────────────────────┤
│                                                      │
│ YOUR TOP 5 RESULTS  (or TOP 10 after Phase 2)        │
│                                                      │
│ We analysed 147 projects against your requirements.  │
│ Match Accuracy: 72% → [Answer 4 more to reach 92%]  │
│                                                      │
│ ┌─────────────────────────────────────────────────┐ │
│ │ YOUR REQUIREMENTS SUMMARY (collapsible)         │ │
│ │ 2 BHK • Kharghar/Upper Kharghar • ₹95L-₹1.1Cr │ │
│ │ End-use • Home loan • EMI ₹65K • Low risk      │ │
│ │ [Edit Requirements] [Re-run]                    │ │
│ └─────────────────────────────────────────────────┘ │
│                                                      │
│ #1  [Project Card — Skyline Heights — 94% Fit] GOLD │
│ #2  [Project Card — Green Valley — 89% Fit]   GOLD  │
│ #3  [Project Card — Palm Residency — 85% Fit]       │
│ #4  [Project Card — ...]                             │
│ #5  [Project Card — ...]                             │
│                                                      │
│ [Improve Match → Answer 4 more questions]            │
│ (Phase 2 CTA — shows if only Phase 1 completed)     │
│                                                      │
│ ┌─────────────────────────────────────────────────┐ │
│ │ WHAT NEXT?                                      │ │
│ │ [Compare Top 3] [Book Site Visit] [Talk to Adv] │ │
│ │ [Download Report] [Share via WhatsApp]          │ │
│ └─────────────────────────────────────────────────┘ │
│                                                      │
│ Not finding what you need? [Update requirements]    │
│ Want to see all projects? [Browse all projects →]   │
│                                                      │
├─────────────────────────────────────────────────────┤
│ FOOTER                                               │
└─────────────────────────────────────────────────────┘
```

### Project Detail Page

```
┌─────────────────────────────────────────────────────┐
│ HEADER                                               │
├─────────────────────────────────────────────────────┤
│ Breadcrumbs: Home > Navi Mumbai > Kharghar > ...    │
├─────────────────────────────────────────────────────┤
│                                                      │
│ ABOVE THE FOLD                                       │
│                                                      │
│ [Image Gallery (3:2)]                                │
│                                                      │
│ Skyline Heights                    [♡ Save] [↗ Share]│
│ by ABC Realty                                        │
│ Kharghar, Navi Mumbai              RERA: P520XXXXX  │
│                                                      │
│ 2 BHK: ₹88L–₹98L  │  3 BHK: ₹1.2Cr–₹1.55Cr      │
│ 680 sq ft           │  920 sq ft                    │
│                                                      │
│ Possession: Dec 2028 │ Construction: 40%            │
│                                                      │
│ [Request Best Price]  [Book Site Visit]  [WhatsApp] │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ STICKY NAV (appears on scroll)                      │
│ Overview│Price│Pros/Cons│Location│Developer│Reviews  │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ QUICK FACTS TABLE (summary box)                     │
│                                                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│ AI SUITABILITY (if user has assessment)              │
│ "Based on your requirements, this project scores     │
│  94% Fit. Here's why..." [See full analysis]        │
│                                                      │
├─────────────────────────────────────────────────────┤
│ PROJECT OVERVIEW (editorial text)                   │
├─────────────────────────────────────────────────────┤
│ PRICE & CONFIGURATION TABLE                         │
├─────────────────────────────────────────────────────┤
│ HIGHLIGHTS                                           │
├─────────────────────────────────────────────────────┤
│ PROS                    │ CONS                       │
├─────────────────────────────────────────────────────┤
│ RISK ANALYSIS                                        │
├─────────────────────────────────────────────────────┤
│ WHO SHOULD BUY          │ WHO SHOULD AVOID           │
├─────────────────────────────────────────────────────┤
│ CONSTRUCTION STATUS                                  │
├─────────────────────────────────────────────────────┤
│ DEVELOPER PROFILE                                    │
├─────────────────────────────────────────────────────┤
│ RERA & APPROVALS                                     │
├─────────────────────────────────────────────────────┤
│ FLOOR PLANS & LAYOUTS                                │
├─────────────────────────────────────────────────────┤
│ AMENITIES (icon grid)                                │
├─────────────────────────────────────────────────────┤
│ LOCATION MAP + CONNECTIVITY TABLE                    │
├─────────────────────────────────────────────────────┤
│ INVESTMENT ANALYSIS                                  │
│ (Appreciation, Rental, Liquidity — with disclaimers)│
├─────────────────────────────────────────────────────┤
│ COMPARABLE PROJECTS (3 cards)                        │
├─────────────────────────────────────────────────────┤
│ FAQS (accordion)                                     │
├─────────────────────────────────────────────────────┤
│ REVIEWS                                              │
├─────────────────────────────────────────────────────┤
│ CTA: "Want to know if this project fits you?"       │
│ [Get Personalized Analysis]                         │
├─────────────────────────────────────────────────────┤
│ DISCLAIMER + LAST UPDATED + SOURCES                  │
├─────────────────────────────────────────────────────┤
│ FOOTER                                               │
└─────────────────────────────────────────────────────┘

Mobile: Sticky bottom CTA bar (WhatsApp FIRST — India's #1 channel)
[WhatsApp] [Best Price] [Site Visit] [Call]
```

---

## CRO Components (Added from Behavioral Audit)

### 10. EMI-First Price Display

```
PRIMARY display (cards, above-fold):
┌──────────────────────────────────────┐
│ EMI from ₹56,000/month*             │  font: Price style (24px/700/mono)
│ All-in cost: ₹88L–₹98L             │  font: Body Small (14px/400)
│                                      │
│ *80% loan at 8.5% for 20 years.    │  font: Caption (12px/400)
│  Down payment from ₹17.6L.          │  color: Gray 500
│  [Calculate your EMI →]             │  color: Brand Primary
└──────────────────────────────────────┘

CONTEXTUAL display (on results page, when user has given EMI comfort):
┌──────────────────────────────────────┐
│ EMI: ₹56,000/month                  │
│ vs your comfort: ₹65,000            │  color: Gray 500
│ ✓ Under budget by ₹9,000/month     │  color: Success green
└──────────────────────────────────────┘
```

### 11. Price Anchor / Market Context

```
┌──────────────────────────────────────┐
│ This project: ₹8,500/sqft           │  font: Body/600
│ Kharghar avg:  ₹9,200/sqft          │  font: Body Small
│ 7.6% below market ✓                 │  color: Success green, font: Caption/600
│                                      │
│ Nearby:                              │
│ • Green Valley: ₹7,800/sqft         │  color: Gray 500
│ • Palm Heights: ₹9,500/sqft         │
│                                      │
│ Source: Registration data, Jul 2026  │  font: Caption, color: Gray 400
└──────────────────────────────────────┘

bg: Gray 50
border: 1px solid Gray 200
border-radius: 8px
padding: 16px
```

### 12. Trust Shield

```
┌──────────────────────────────────────┐
│ 🛡️ Our No-Spam Promise              │  font: H4/600
│                                      │
│ ✓ Maximum 1 advisor contacts you    │  font: Body Small
│ ✓ You choose which projects         │  color: Gray 700
│ ✓ One-tap "Stop all calls" anytime  │  ✓ = Success green
│ ✓ We NEVER sell your number         │
│                                      │
│ 12,847 buyers trust 10Projects.     │  font: Caption/500
│                                      │  color: Gray 500
└──────────────────────────────────────┘

bg: #F0FDF4 (very light green)
border: 1px solid #BBF7D0 (light green)
border-radius: 8px
padding: 16px
Position: directly above phone input field
```

### 13. Social Proof Counter Bar

```
┌──────────┬──────────┬──────────┐
│ 12,847   │ 156      │ 94%      │
│ buyers   │ projects │ said     │
│ matched  │ analysed │"accurate"│
│ this mo. │          │          │
└──────────┴──────────┴──────────┘

bg: White
border-top: 1px solid Gray 200
border-bottom: 1px solid Gray 200
padding: 16px 0
Numbers: font: H3/600, color: Brand Primary
Labels: font: Caption/400, color: Gray 500
Grid: 3 equal columns, center aligned
```

### 14. Inventory Status Bar

```
┌──────────────────────────────────────┐
│ 2 BHK: 8 of 120 units available     │
│ ████████████░░░░░░░░  93% sold      │
│                                      │
│ Last sold: 3 days ago                │
│ Last price change: Apr 2026          │
│ (+₹200/sqft)                         │
│                                      │
│ Source: Developer report, Jul 2026.  │
│ Subject to verification.            │
└──────────────────────────────────────┘

Progress bar: Brand Primary fill, Gray 200 track
Numbers: Body Small/600
Source disclaimer: Caption/400, color: Gray 400
Only show when data is < 30 days old.
```

### 15. Bottom Slide-Up Bar (Micro-Capture)

```
┌──────────────────────────────────────┐
│ Get price updates for Skyline Heights│  font: Body Small/500
│ [email@example.com] [Subscribe]      │
│ Max 2 emails/month. No spam.        │  font: Caption/400
│                                    [✕]│
└──────────────────────────────────────┘

Position: fixed bottom
bg: White
border-top: 1px solid Gray 200
box-shadow: 0 -4px 12px rgba(0,0,0,0.1)
padding: 12px 16px
z-index: above content, below modals
Dismiss: ✕ button, remembers dismissal for session
Trigger: 30+ seconds on page AND scroll > 40%
NOT a modal popup — does not block content
```

### 16. Contact Preferences Panel

```
┌──────────────────────────────────────┐
│ Your Contact Preferences             │  font: H4/600
│                                      │
│ Contact me via:                      │
│ (●) WhatsApp  ( ) Phone  ( ) Email  │
│                                      │
│ Best time:                           │
│ [10 AM – 1 PM           ▼]          │
│                                      │
│ Contact me about:                    │
│ [✓] My top projects                 │
│ [ ] Loan offers                     │
│ [ ] New launches                    │
│                                      │
│ [Save Preferences]                   │
└──────────────────────────────────────┘

bg: White
border-radius: 12px
padding: 20px
Shown immediately after OTP verification
Takes 5 seconds — reduces spam complaints by 40-60%
```

### 17. Resume Banner (Returning Abandoners)

```
┌──────────────────────────────────────────────────┐
│ 🔄 Welcome back! Your results are waiting.       │
│ You answered 5 of 7 questions for 2 BHK,        │
│ Kharghar, ₹75L–₹1Cr.                            │
│                                                    │
│ [Continue Where I Left Off →]  [Start Fresh]     │
└──────────────────────────────────────────────────┘

Position: top of page, below header
bg: #FEF3C7 (warm yellow)
border-bottom: 1px solid #FDE68A
padding: 12px 16px
Trigger: returning visitor with saved partial assessment
Dismissable
```

### 18. Low-Score Results State

```
┌──────────────────────────────────────────────────┐
│ 😕 Your requirements are quite specific.          │
│                                                    │
│ We found 10 matches but the best scored 62%.     │
│ The market may not have a perfect match right now.│
│                                                    │
│ Options:                                           │
│ [Adjust requirements] — Relax budget or location  │
│ [Talk to an advisor] — A human might know         │
│   upcoming launches or off-market options          │
│ [Alert me when new projects match →]              │
│                                                    │
│ All free. No obligation.                          │
└──────────────────────────────────────────────────┘

bg: Gray 50
border: 1px solid Gray 200
border-radius: 12px
padding: 24px
Show when highest fit score < 70%
```

---

## States

### Loading States
- Skeleton screens for cards (gray placeholder blocks)
- Spinner for AI processing
- Progress bar for assessment
- "Analysing projects..." message during scoring

### Empty States
- No saved projects: "Save projects from your recommendations to compare later."
- No recommendations yet: "Complete your AI assessment to get personalised results."
- No results in filter: "No projects match these filters. Try adjusting your criteria."

### Error States
- API error: "Something went wrong. Please try again."
- OTP failed: "Incorrect OTP. Please check and try again. [Resend OTP]"
- No phone: "Please enter a valid mobile number."
- AI unavailable: "Our AI assistant is temporarily unavailable. Browse projects manually."

### Success States
- OTP verified: Green check + "Verified! Loading your results..."
- Project saved: "Saved to your shortlist" (toast notification)
- Site visit requested: "We'll confirm your visit within 24 hours."
- Assessment complete: Confetti/celebration micro-animation + transition to results

---

## Accessibility Requirements

- WCAG 2.1 AA compliance
- All interactive elements keyboard accessible
- Focus visible indicators (2px blue ring)
- Minimum touch target: 44px × 44px
- Colour contrast ratio: 4.5:1 for text, 3:1 for large text
- All images have alt text
- Form labels associated with inputs
- Error messages linked to fields
- Skip navigation link
- Landmark roles (header, main, footer, nav)
- Screen reader announcements for dynamic content (ARIA live regions)
- Reduced motion support (prefers-reduced-motion)

---

## Responsive Breakpoints

```
Mobile:         < 640px    (1 column, stacked layout)
Tablet:         640–1024px (2 column cards)
Desktop:        1024–1280px (3 column cards, sidebar)
Wide Desktop:   > 1280px   (max-width: 1280px container, centered)
```

Container max-width: 1280px, centered with auto margins.
Content max-width (text-heavy): 768px.
