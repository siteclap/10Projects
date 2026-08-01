# AI Assessment Flow & Complete Question Bank

> **CRO AUDIT v2:** Restructured based on behavioral audit (see `09-behavioral-cro-audit.md`).
> Changes: 5 phases → 3 phases, 25 questions → 11 questions, drag-to-rank removed,
> instant results after Phase 1, Trust Shield before OTP, WhatsApp as parallel path,
> exit-intent capture for abandoners.

## Assessment Flow Architecture

### Core Design Principles (Behavioral CRO)

1. **Instant gratification:** Show results after 3 questions (30 seconds), not after 25
2. **Endowed progress:** "Match accuracy: 72% → 89% → 96%" — users build something, not fill a form
3. **Zeigarnik effect:** Partial results create tension that drives completion
4. **Zero drag interactions:** All mobile-first, tap-only UI
5. **Reciprocity before gate:** Give real value (10 project names, 1 full analysis) BEFORE asking for phone

### Conversation Structure

3-phase progressive approach. Phase 1 is required (3 questions, 30 seconds). Phases 2 and 3 are optional but encouraged by showing improved accuracy. Show results after EVERY phase.

### Progress Indicator

```
BEFORE (wrong — counts steps, feels like a form):
[●●●○○] Step 3 of 5 — Budget & Funding

AFTER (right — shows VALUE of continuing):
Match Accuracy: ████████░░░░ 72%
"Answer 4 more questions to reach ~89%"
```

### Phase Breakdown

```
Phase 1: Quick Match (3 questions, 30 seconds) — REQUIRED
    → Configuration + Location + Budget
    → INSTANT RESULTS: Top 5 shown, 72% accuracy
    → CTA: "4 more questions to improve to 89%"

Phase 2: Better Match (4 questions, 45 seconds) — ENCOURAGED
    → Purpose + Timeline + Funding + Top 3 Priorities
    → REFINED RESULTS: Top 10, 89% accuracy
    → CTA: "Almost perfect — 3 more for 96%?"

Phase 3: Perfect Match (3-4 questions, 30 seconds) — OPTIONAL
    → EMI comfort + Possession tolerance + Must-haves + Free notes
    → FINAL RESULTS: Full Fit Scores, 96% accuracy
```

### Total Time: 30 seconds (Phase 1 only) to 2 minutes (all 3 phases)

---

## Complete Question Bank

### Phase 1: Quick Match (3 questions — REQUIRED)

**Q1: Configuration**
```
"What are you looking for?"

UI: Large tap chips (single row on mobile, horizontal scroll)
[1 BHK] [2 BHK] [3 BHK] [4 BHK+]

Auto-advance on selection (no "Continue" button needed).

Skip: No (required)
Maps to: configuration[]
```

**Why this is Q1 (not Purpose):** Configuration is the easiest decision. Everyone knows their BHK. Starting with the easiest question gets commitment (Foot-in-the-Door technique). Purpose/investment is a heavier question — defer to Phase 2.

**Q2: Location**
```
"Where in Navi Mumbai?"

UI: Tap chips — 2 rows, most popular first
Row 1: [Kharghar] [Panvel] [Taloja] [Ulwe] [Nerul]
Row 2: [Seawoods] [Airoli] [Vashi] [Kamothe] [More ▾]
       [Not sure — show all areas]

Multi-select: Yes
Auto-advance after 2-second pause (or tap Continue).

Skip: Yes (defaults to all locations)
Maps to: preferred_locations[]
```

**Q3: Budget**
```
"What's your budget?"

UI: Chip selection — ONE TAP (not dual slider, not text input)
[Under ₹50L] [₹50L–₹75L] [₹75L–₹1Cr]
[₹1Cr–₹1.5Cr] [₹1.5Cr–₹2Cr] [₹2Cr+]

Single select. Auto-advance.

Skip: No (required)
Maps to: budget_range (maps to budget_comfortable and budget_maximum internally)
```

**Why simple chips, not dual sliders:** A ₹50L-₹75L chip takes 1 tap. A dual slider with "comfortable" and "maximum" requires reading labels, dragging two handles on a mobile screen, and understanding the difference. Same data, 5x faster.

### → INSTANT RESULTS AFTER PHASE 1

```
Transition animation: "Analysing 150+ projects..."
(1.5 second branded animation — NOT actual wait time)

RESULTS SCREEN:
┌──────────────────────────────────────────────────┐
│ Match Accuracy: ████████░░░░ 72%                  │
│                                                    │
│ We found matches for "2 BHK in Kharghar,          │
│ ₹75L–₹1Cr"                                        │
│                                                    │
│ Your Top 5 (quick match):                          │
│                                                    │
│ #1  Skyline Heights, Kharghar        ~90% Fit     │
│     EMI from ₹56K/mo • ₹88L–₹98L • Dec 2028     │
│     ✓ Within budget  ✓ Near station               │
│                                                    │
│ #2  Green Valley, Panvel             ~84% Fit     │
│     EMI from ₹48K/mo • ₹75L–₹85L • Mar 2029     │
│                                                    │
│ #3  Palm Residency, Kharghar         ~80% Fit     │
│     EMI from ₹59K/mo • ₹92L–₹1.1Cr              │
│                                                    │
│ #4  Sunrise Towers, Taloja           ~76% Fit     │
│ #5  Metro Heights, Ulwe              ~72% Fit     │
│                                                    │
│ ┌────────────────────────────────────────────────┐ │
│ │ Want more accurate results?                    │ │
│ │ Answer 4 more questions to reach ~89% match.  │ │
│ │                                                │ │
│ │ [Improve My Results →] ← Primary              │ │
│ │ [These are good enough]  ← Secondary          │ │
│ └────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────┘

Notes:
- Scores shown as "~90%" (tilde = approximate, because partial data)
- #1 gets full mini-card. #2-#3 get one-liners. #4-#5 name + score.
- EMI shown as primary price (not lakh amount)
- If user taps "These are good enough" → go to Lead Gate
- If user taps "Improve My Results" → Phase 2
```

---

### Phase 2: Better Match (4 questions — ENCOURAGED)

**Q4: Purpose**
```
"Are you looking for a home or an investment?"

UI: Card selection (3 cards, icons)
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│   Home       │  │  Investment  │  │    Both      │
│ To live in   │  │ For returns  │  │ Live now,    │
│              │  │              │  │ sell later   │
└──────────────┘  └──────────────┘  └──────────────┘

Auto-advance.
Skip: Yes (defaults to "both")
Maps to: purpose
```

**Q5: Purchase Timeline**
```
"When are you planning to buy?"

UI: Chip selection
[ASAP] [1–3 months] [3–6 months] [6–12 months] [Just researching]

Auto-advance.
Skip: Yes (defaults to "3-6 months")
Maps to: purchase_timeline_months
```

**Q6: Funding**
```
"How are you funding this?"

UI: Chip selection
[Home Loan] [Self-funded] [Mix of both]

Auto-advance.
Skip: Yes (defaults to "home_loan")
Maps to: funding_type
```

**Q7: Top 3 Priorities**
```
"What matters most? Pick your top 3."

UI: Visual card grid — TAP to select (numbered automatically)
6 cards, tap-only, NO dragging.

For End-users:
┌─────────┐ ┌─────────┐ ┌─────────┐
│ ₹       │ │ 📍      │ │ 🏗️      │
│ Best    │ │ Best    │ │ Trusted │
│ Price   │ │ Location│ │Developer│
│         │ │ ● #1    │ │ ● #2   │
└─────────┘ └─────────┘ └─────────┘
┌─────────┐ ┌─────────┐ ┌─────────┐
│ 🔑      │ │ 👨‍👩‍👧‍👦     │ │ 🛡️      │
│ Early   │ │ Family  │ │ Low     │
│ Possess.│ │ Friendly│ │ Risk    │
│ ● #3    │ │         │ │         │
└─────────┘ └─────────┘ └─────────┘

For Investors (shown if purpose == investment):
┌─────────┐ ┌─────────┐ ┌─────────┐
│ ₹       │ │ 📈      │ │ 🏠      │
│ Entry   │ │ High    │ │ Rental  │
│ Price   │ │ Growth  │ │ Income  │
└─────────┘ └─────────┘ └─────────┘
┌─────────┐ ┌─────────┐ ┌─────────┐
│ 📍      │ │ 🔄      │ │ 🏗️      │
│ Emerging│ │ Quick   │ │ Trusted │
│ Location│ │ Exit    │ │ Brand   │
└─────────┘ └─────────┘ └─────────┘

Tap first = priority #1, second = #2, third = #3.
Tap selected card again to deselect.

Skip: Yes (system uses default weights)
Maps to: priorities[{factor, rank}]
```

### → REFINED RESULTS AFTER PHASE 2

```
Match Accuracy: ████████████░░ 89%  (+17% improved)

Your Top 10 (better match):

#1  Skyline Heights, Kharghar         94% Fit
    [Full card with EMI, reasons, trade-off]

#2  Green Valley, Panvel              89% Fit
    [Summary card]

#3  Palm Residency, Kharghar          85% Fit
...
#10 Lakeview Towers, Nerul            64% Fit

[Almost perfect — answer 3 more for 96%? →]
[Show me full details for these →]
```

---

### Phase 3: Perfect Match (3-4 questions — OPTIONAL)

**Q8: EMI Comfort (if funding = loan or mix)**
```
"What monthly EMI are you comfortable with?"

UI: Chip selection
[₹20–30K] [₹30–50K] [₹50–70K] [₹70K–1L] [₹1L+]

Skip: Yes (system estimates from budget and standard loan terms)
Maps to: monthly_emi_comfort
```

**Q9: Possession Tolerance**
```
"How long can you wait for possession?"

UI: Chip selection
[Ready now] [1–2 years] [2–3 years] [3–5 years] [Can wait for value]

Skip: Yes (defaults to 3-5 years)
Maps to: max_possession_year (calculated from current year + selection)
```

**Q10: Must-Haves**
```
"Any must-haves? Select all that apply."

UI: Chip selection, multi-select
[Near railway station] [Near school] [Near hospital]
[Parking included] [Established developer only]
[RERA registered only] [Low density] [Gated community]
[Nothing specific]

Skip: Yes
Maps to: deal_breakers[] + amenity_preferences[]
```

**Q11: Anything Else (optional)**
```
"Anything else we should know?"

UI: Text area with example prompts
"I need east-facing flat"
"Ground floor for elderly parents"
"I have a property to sell first"

Skip: Yes (most users will skip)
Maps to: additional_notes
```

### → FINAL RESULTS

```
Match Accuracy: ████████████████ 96%

Full Fit Scores with detailed explanations.
→ Proceed to Lead Gate
```

---

## Lead Gate (Redesigned for Trust)

### What User Sees for Free (NO phone required)

After ANY phase completion:
- All 10 project names
- All 10 fit scores
- Rank #1: Full card (image, EMI, price, location, 2 match reasons, 1 trade-off)
- Ranks #2-#5: Summary line (name, location, EMI, score)
- Ranks #6-#10: Name + score only

### What Requires Phone + OTP

- Full detailed cards for all 10 projects
- "Why it fits you" AI explanations
- Trade-off analysis for each
- Compare feature
- Save to dashboard
- Download PDF report
- WhatsApp delivery of results

### Trust Shield (BEFORE Phone Input)

```
┌──────────────────────────────────────────────────┐
│ To see full analysis and save your results:       │
│                                                    │
│ 📱 [+91 __________]                               │
│ [Send OTP →]                                       │
│                                                    │
│ 🛡️ Our No-Spam Promise:                           │
│ ✓ Maximum 1 advisor will contact you              │
│ ✓ You choose which projects to discuss            │
│ ✓ One-tap "Stop all contact" anytime              │
│ ✓ We NEVER sell your number                       │
│                                                    │
│ Unlike other portals, we don't share your          │
│ number with 10 brokers. One verified advisor.      │
│                                                    │
│ 12,847 buyers verified this month.                │
└──────────────────────────────────────────────────┘
```

### Post-OTP: Contact Preferences (Immediate)

```
Verified! Your full results are ready.

Before we show them — how would you like to be contacted?

Preferred method:   (●) WhatsApp  ( ) Phone  ( ) Email
Preferred time:     [10 AM – 1 PM ▼]
Contact me about:   [✓] My top projects  [ ] Loan offers  [ ] New launches

[Show My Results →]
```

This takes 5 seconds and gives the user control. Reduces post-call complaints by 40-60%.

---

## Exit-Intent Capture (For Abandoners)

### Scenario: User Abandons Mid-Assessment

Trigger: Back button, tab switch, or 30 sec inactivity.

```
┌──────────────────────────────────────────────────┐
│ Your results are almost ready!                    │
│ You've answered 5 of 7 questions.                 │
│                                                    │
│ [Show Results With What I Have] ← Primary        │
│                                                    │
│ No time now?                                      │
│ [Get a link on WhatsApp to continue later →]     │
│  📱 [+91 __________]                              │
│                                                    │
│ [Leave without saving]                            │
└──────────────────────────────────────────────────┘

"Show Results With What I Have" → Run scoring with partial data,
  show rough top 5, then encourage completing later.

"WhatsApp link" → Captures phone (LEAD), sends resume link.
  Auto-follow-up in 4 hours: "Your top 10 are waiting.
  Tap to continue: [link]"
```

### Scenario: User Browsing Project Pages, No Assessment

Trigger: 60+ seconds on page + scroll past 40%. Bottom slide-up (NOT popup).

```
┌──────────────────────────────────────────────────┐
│ Does Skyline Heights fit YOUR budget?             │
│ [Find Out in 30 Seconds →]                       │
│                                                  ✕│
└──────────────────────────────────────────────────┘
```

### Scenario: User Viewed 3+ Project Pages

Trigger: 3rd project page view in session.

```
┌──────────────────────────────────────────────────┐
│ You've viewed 3 projects. Want us to rank them   │
│ against YOUR requirements?                        │
│ [Yes, rank my options →]                     [✕] │
└──────────────────────────────────────────────────┘
```

---

## WhatsApp Assessment Path (Parallel Channel)

For users who prefer WhatsApp over web forms.

### Entry Points
- Homepage: "Prefer WhatsApp? [Chat with our advisor →]"
- Project page: WhatsApp CTA with pre-filled context
- Exit-intent: "Continue on WhatsApp later"

### WhatsApp Bot Flow
```
Bot: "Hi! I'll help find your top 10 projects in Navi Mumbai.
     What BHK are you looking for?"
User: "2 BHK"
Bot: "Great! Which areas? Tap to select:
     1. Kharghar  2. Panvel  3. Taloja  4. Ulwe
     5. Other areas  6. Not sure"
User: "1"
Bot: "Budget range?
     1. Under ₹50L  2. ₹50L–₹75L  3. ₹75L–₹1Cr
     4. ₹1Cr–₹1.5Cr  5. ₹1.5Cr+"
User: "3"
Bot: "Analysing... Found 42 matching projects!
     Your top 3:
     #1 Skyline Heights — ~90% Fit — EMI ₹56K/mo
     #2 Green Valley — ~84% Fit — EMI ₹48K/mo
     #3 Palm Residency — ~80% Fit — EMI ₹59K/mo

     Want to see all 10 with detailed analysis?
     Tap: https://10projects.com/results/abc123"
```

Phone captured automatically from WhatsApp. Full lead created.

---

## AI Conversation Engine Behaviour

### Conversation Rules

1. **Show results fast:** After Phase 1 (3 questions), show instant results. NEVER make the user answer all questions before seeing anything.
2. **Context-awareness:** Adapt Phase 2/3 questions based on Phase 1 answers and purpose.
3. **Smart defaults:** Pre-fill reasonable defaults so users can skip. Budget chip "₹75L–₹1Cr" auto-maps to comfortable=₹75L, max=₹1Cr.
4. **Auto-advance:** Single-select chips advance automatically (no "Continue" button). Saves one tap per question.
5. **Validation:** Gently catch conflicts. "That budget may be tight for 3 BHK in Kharghar. Projects start around ₹1.2Cr. Want to see 2 BHK options or try Taloja/Kamothe?"
6. **Save state:** Every answer saved immediately. Cookie-based session for anonymous users. User can return and resume.
7. **No "back" confusion:** Allow changing answers by tapping a different chip — previous answer deselects.

### Adaptive Question Logic

```
IF purpose == "investment" THEN
    Phase 2 Q7: Show investor priority cards (entry price, appreciation, rental, etc.)
    Phase 3: Skip EMI question if self-funded. Add "exit timeline" question.
    Scoring: Use investor weight profile.

IF purpose == "end_use" THEN
    Phase 2 Q7: Show end-user priority cards (location, developer, family, etc.)
    Phase 3: Include EMI. Add must-have amenities.
    Scoring: Use end-user weight profile.

IF budget < location_average THEN
    After Q3: Inline suggestion — "Your budget works well in Taloja and
    Kamothe. Want to include those?" [Yes, add them] [No, keep my locations]

IF purchase_timeline == "just_researching" THEN
    After results: Reduce urgency CTAs. Show "Save and compare" as primary.
    Lead classification: "long_term_nurture" (don't route to partners immediately).

IF only Phase 1 completed (partial profile) THEN
    Scoring: Use only budget_fit, location_fit, config_fit (3 categories).
    Show "~" approximate scores. Label: "Quick match — improve with more details."
```

### Output: Customer Profile JSON

The profile is progressively built. Partial profiles (Phase 1 only) are valid and scorable.

```json
{
  "customer_id": "uuid",
  "assessment_id": "uuid",
  "completed_at": "2026-08-01T10:30:00Z",
  "phases_completed": 3,
  "match_accuracy": 0.96,

  "phase_1": {
    "configuration": ["2_bhk"],
    "preferred_locations": ["kharghar", "upper_kharghar"],
    "budget_range": "75l_1cr",
    "budget_comfortable": 7500000,
    "budget_maximum": 10000000
  },

  "phase_2": {
    "purpose": "end_use",
    "purchase_timeline_months": 3,
    "funding_type": "home_loan",
    "priorities": [
      {"factor": "location_convenience", "rank": 1},
      {"factor": "developer_reputation", "rank": 2},
      {"factor": "low_risk", "rank": 3}
    ]
  },

  "phase_3": {
    "monthly_emi_comfort": 65000,
    "max_possession_year": 2029,
    "must_haves": ["near_railway", "rera_registered", "parking"],
    "additional_notes": "Prefer east-facing. Need covered parking."
  },

  "derived": {
    "risk_tolerance": "low",
    "config_flexible": false,
    "city": "navi_mumbai",
    "property_type": "residential_apartment",
    "weight_profile": "end_user_low_risk"
  },

  "contact": {
    "phone": "+919876543210",
    "phone_verified": true,
    "name": "Rahul Sharma",
    "email": "rahul@example.com",
    "preferred_contact": "whatsapp",
    "preferred_time": "10am_1pm",
    "contact_about": ["top_projects"]
  }
}
```

---

## Detailed Question Reference (For Scoring Engine)

The original 25-question bank remains available for the scoring engine to use as data points, even though users only answer 7-11 questions. Missing data points are handled as follows:

| Original Data Point | How It's Captured |
|---------------------|-------------------|
| Configuration | Phase 1 Q1 (required) |
| Location | Phase 1 Q2 (required) |
| Budget | Phase 1 Q3 (required) |
| Purpose | Phase 2 Q4 (or default: "both") |
| Timeline | Phase 2 Q5 (or default: "3-6 months") |
| Funding | Phase 2 Q6 (or default: "home_loan") |
| Priorities | Phase 2 Q7 (or default weights by purpose) |
| EMI comfort | Phase 3 Q8 (or calculated from budget + standard loan terms) |
| Possession tolerance | Phase 3 Q9 (or default: "3-5 years") |
| Must-haves | Phase 3 Q10 (or no hard filters on these) |
| Additional notes | Phase 3 Q11 (or none) |
| Commute | NOT asked — inferred from location workplace proximity data |
| Family composition | NOT asked — inferred from config (2BHK = likely family) |
| Risk profile | NOT asked — derived from priorities + developer preference |
| Developer preference | Captured via priorities or must-haves |
| Carpet area | NOT asked — inferred from config + budget |
| Payment plan | NOT asked — not relevant for scoring |
| Amenities | Partially via must-haves (Phase 3 Q10) |

**Design principle:** Ask 11 questions, score across 20 categories. Fill gaps with smart defaults and statistical inference. The scoring engine handles missing data gracefully — it doesn't need perfect input to produce useful rankings.
