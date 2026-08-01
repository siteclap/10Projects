# Project Scoring Methodology & Fit Score

## Overview

The 10Projects Fit Score is a deterministic, weighted scoring system that evaluates every eligible project against a customer's specific requirements. The AI does not decide rankings — it explains them.

## Architecture

```
Customer Profile → Hard Filters → Eligible Projects → Scoring Engine → Ranked Top 10
                                                            ↑
                                                     Weight Profiles
                                                    (based on purpose,
                                                     risk, priorities)
```

## Step 1: Hard Filters (Eliminate Before Scoring)

These are pass/fail criteria. Projects that fail ANY hard filter are excluded entirely.

| Filter | Logic |
|--------|-------|
| City | Project must be in customer's selected city |
| Configuration | Project must offer customer's selected config |
| Max Budget | Minimum unit price must be ≤ customer's max budget |
| Min Carpet Area | Available carpet area must be ≥ customer's minimum |
| Possession Year | Expected possession must be ≤ customer's max year |
| Construction Stage | Must match customer's selected stages |
| Property Type | Must match customer's selected type |
| RERA Status | If customer requires RERA → must have valid RERA |
| Inventory | Must have available inventory (not sold out) |
| Deal-breakers | Must not match any customer deal-breaker conditions |
| Geography | Must be in customer's preferred OR alternative locations (if location_flexible=false) |

**Result:** Reduced set of eligible projects (typically 20–80 from 150+)

## Step 2: Scoring Categories

Each eligible project is scored across 20 categories. Each category produces a score from 0 to 100.

### Category 1: Budget Fit (0–100)

```
IF project_min_price ≤ budget_comfortable:
    score = 100
ELIF project_min_price ≤ budget_comfortable * 1.1:
    score = 85
ELIF project_min_price ≤ budget_maximum:
    score = 70 - ((project_min_price - budget_comfortable) / (budget_maximum - budget_comfortable)) * 30
ELIF project_min_price ≤ budget_maximum * 1.05:
    score = 40
ELSE:
    score = 0 (hard filter should catch this)
```

### Category 2: Location Fit (0–100)

```
IF project_location IN preferred_locations:
    base = 100
ELIF project_location IN alternative_locations:
    base = 70
ELIF location_flexible AND same_city:
    base = 40
ELSE:
    base = 0

Adjust for micro-location desirability:
    +/- based on infrastructure score of micro-location
```

### Category 3: Configuration Fit (0–100)

```
IF exact_match:
    score = 100
ELIF project has multiple configs and one matches:
    score = 90
ELIF config_flexible AND within budget:
    score = 60
ELSE:
    score = 0
```

### Category 4: Carpet Area Fit (0–100)

```
IF carpet_area_min ≤ project_carpet ≤ carpet_area_max:
    score = 100
ELIF project_carpet within 10% of range:
    score = 80
ELIF project_carpet within 20% of range:
    score = 60
ELSE:
    score = 30
```

### Category 5: Possession Fit (0–100)

```
IF possession_preference == "ready" AND project_ready:
    score = 100
IF possession_year ≤ customer_max_year:
    years_early = customer_max_year - possession_year
    score = 80 + (years_early * 5, max 20)
IF possession_year > customer_max_year:
    years_late = possession_year - customer_max_year
    score = max(0, 60 - years_late * 20)

Adjust for developer's delivery track record:
    IF developer_avg_delay > 1 year: -15
    IF developer_avg_delay > 2 years: -30
```

### Category 6: Funding/EMI Fit (0–100)

```
estimated_emi = calculate_emi(project_price - down_payment, 8.5%, 20_years)

IF estimated_emi ≤ emi_comfort:
    score = 100
ELIF estimated_emi ≤ emi_comfort * 1.15:
    score = 75
ELIF estimated_emi ≤ emi_comfort * 1.3:
    score = 50
ELSE:
    score = 25

IF loan_preapproved == "yes":
    score += 5 (cap at 100)
IF existing_emi > 0:
    total_emi_ratio = (estimated_emi + existing_emi) / estimated_income
    IF ratio > 0.5: score -= 20
```

### Category 7: Commute Fit (0–100)

```
commute_time = calculate_commute(project_location, workplace, commute_mode)

IF commute_time ≤ max_commute * 0.6:
    score = 100
ELIF commute_time ≤ max_commute:
    score = 100 - ((commute_time - max_commute*0.6) / (max_commute*0.4)) * 40
ELIF commute_time ≤ max_commute * 1.2:
    score = 40
ELSE:
    score = 20

IF commute_mode == "train" AND project_near_station:
    score += 10
```

### Category 8: Lifestyle Fit (0–100)

```
matched_amenities = intersection(customer_preferences, project_amenities)
score = (len(matched_amenities) / len(customer_preferences)) * 100

Bonus:
IF "gated_security" matched: +5
IF "pet_area" matched AND customer_has_pets: +10
IF "senior_area" matched AND family_seniors > 0: +10
IF "kids_play" matched AND family_children > 0: +10
```

### Category 9: Developer Reliability (0–100)

```
base = developer_reputation_score (from database, 0-100)

Factors:
- Years in business: 10+ years = +10, 5-10 = +5, <5 = 0
- Completed projects: 10+ = +10, 5-10 = +5, <5 = 0
- On-time delivery rate: >80% = +15, 50-80% = +5, <50% = -20
- Active litigation: none = +5, minor = 0, major = -20
- Brand premium: tier-1 = +10, tier-2 = +5, new = 0

IF customer risk_tolerance == "low":
    Apply 1.2x multiplier to developer reliability impact
```

### Category 10: Construction Stage Suitability (0–100)

```
Mapping based on customer purpose + risk:

End-user + Low-risk:
    Ready = 100, Near ready = 85, Under construction (50%+) = 70,
    Under construction (<50%) = 40, New launch = 25

End-user + Balanced:
    Ready = 90, Near ready = 90, Under construction = 75,
    New launch = 55

Investor + High-risk:
    New launch = 100, Early construction = 90,
    Under construction = 70, Ready = 50

Investor + Balanced:
    New launch = 85, Under construction = 80,
    Ready = 65
```

### Category 11: Legal & Approval Confidence (0–100)

```
RERA registered: +30
Commencement certificate: +15
Environment clearance: +10
Bank approved (3+ banks): +15
No litigation: +15
Clear title: +15

Missing RERA: -40
Pending approvals: -20
Active litigation: -30
```

### Category 12: Resale Liquidity (0–100)

```
Factors:
- Location demand (transaction volume): high = 30, medium = 20, low = 10
- Developer brand: tier-1 = 20, tier-2 = 15, new = 5
- Configuration demand: 2BHK = 25, 1BHK = 20, 3BHK = 15, 4BHK = 10
- Micro-market maturity: established = 15, developing = 10, emerging = 5
- Price competitiveness: below market = 10, at market = 5, above = 0
```

### Category 13: Rental Potential (0–100)

```
estimated_rental = location_rental_rate * carpet_area
rental_yield = (estimated_rental * 12) / project_price * 100

IF yield > 4%: score = 100
IF yield 3-4%: score = 80
IF yield 2-3%: score = 60
IF yield 1-2%: score = 40
IF yield < 1%: score = 20

Adjust for:
- Vacancy risk: low = +10, high = -15
- Corporate hub proximity: near = +10
- Transit connectivity: good = +10
```

### Category 14: Appreciation Drivers (0–100)

```
Infrastructure catalysts within 5 km:
- Metro line (planned/under construction): +20
- Airport: +15
- Highway/expressway: +10
- Railway improvement: +10
- Commercial SEZ: +10
- Government initiative: +5

Supply-demand dynamics:
- Low supply: +15
- High demand: +10
- Price below micro-market average: +10

Historical trend:
- 3-year CAGR > 10%: +15
- 3-year CAGR 5-10%: +10
- 3-year CAGR < 5%: +5
```

### Category 15: Risk Profile Compatibility (0–100)

```
project_risk_level = calculated from (developer risk + legal risk +
    construction risk + market risk + location risk)

IF customer_risk == "low" AND project_risk == "low": 100
IF customer_risk == "low" AND project_risk == "medium": 50
IF customer_risk == "low" AND project_risk == "high": 10
IF customer_risk == "balanced" AND project_risk == "medium": 100
IF customer_risk == "balanced" AND project_risk == "low": 90
IF customer_risk == "balanced" AND project_risk == "high": 40
IF customer_risk == "high" AND project_risk == "high": 80
IF customer_risk == "high" AND project_risk == "medium": 90
IF customer_risk == "high" AND project_risk == "low": 70
```

### Category 16: Infrastructure Potential (0–100)

Based on planned/upcoming infrastructure within 5 km radius and expected impact on property values.

### Category 17: Family Suitability (0–100)

Scored based on school proximity, hospital proximity, park access, safety ratings, community profile — only for end-use buyers.

### Category 18: Urgency Match (0–100)

How well the project's current status matches the buyer's purchase timeline. E.g., new-launch offers match "researching" timeline well but not "need immediately."

### Category 19: Inventory Availability (0–100)

```
IF floor/unit choice available: 100
IF limited inventory: 70
IF last few units: 50
IF waitlist only: 20
```

### Category 20: Proximity Score (0–100)

Composite score of distances to customer's specified proximity requirements (station, school, hospital, etc.).

---

## Step 3: Weight Profiles

Weights determine how much each category contributes to the final Fit Score. Weights are adjusted based on the customer's purpose, risk profile, and stated priorities.

### Default Weight Sets (Total = 100%)

#### End-User Weights

| Category | Weight |
|----------|--------|
| Budget Fit | 15% |
| Location Fit | 12% |
| Configuration Fit | 8% |
| Carpet Area Fit | 5% |
| Possession Fit | 10% |
| EMI Fit | 8% |
| Commute Fit | 8% |
| Lifestyle Fit | 5% |
| Developer Reliability | 8% |
| Construction Stage | 3% |
| Legal Confidence | 5% |
| Resale Liquidity | 2% |
| Rental Potential | 1% |
| Appreciation Drivers | 2% |
| Risk Compatibility | 3% |
| Infrastructure | 1% |
| Family Suitability | 5% |
| Urgency Match | 2% |
| Inventory Availability | 2% |
| Proximity Score | 5% |

#### Investor Weights

| Category | Weight |
|----------|--------|
| Budget Fit | 12% |
| Location Fit | 8% |
| Configuration Fit | 5% |
| Carpet Area Fit | 3% |
| Possession Fit | 3% |
| EMI Fit | 5% |
| Commute Fit | 0% |
| Lifestyle Fit | 0% |
| Developer Reliability | 8% |
| Construction Stage | 8% |
| Legal Confidence | 5% |
| Resale Liquidity | 10% |
| Rental Potential | 8% |
| Appreciation Drivers | 12% |
| Risk Compatibility | 5% |
| Infrastructure | 5% |
| Family Suitability | 0% |
| Urgency Match | 3% |
| Inventory Availability | 3% |
| Proximity Score | 0% |

### Priority-Based Weight Adjustment

When a customer provides ranked priorities, the system adjusts weights:

```
For each priority in customer's top 5:
    Rank 1: multiply corresponding category weight by 1.5
    Rank 2: multiply by 1.3
    Rank 3: multiply by 1.2
    Rank 4: multiply by 1.1
    Rank 5: multiply by 1.05

Re-normalise all weights to total 100%
```

---

## Step 4: Final Score Calculation

```
Fit Score = Σ (category_score × category_weight)

Final Fit Score = round to nearest integer (0-100)
```

### Score Interpretation

| Score Range | Label | Meaning |
|-------------|-------|---------|
| 90–100 | Excellent Fit | Matches almost all criteria strongly |
| 80–89 | Very Good Fit | Strong match with minor trade-offs |
| 70–79 | Good Fit | Solid match, some compromises |
| 60–69 | Moderate Fit | Decent option, notable trade-offs |
| 50–59 | Partial Fit | Some criteria met, significant gaps |
| Below 50 | Weak Fit | Not recommended (should not appear in top 10) |

---

## Sample Fit Score Calculation

### Customer: Rahul (End-User)
- Purpose: End-use
- Config: 2 BHK
- Budget: Comfortable ₹95L, Max ₹1.1Cr
- Location: Kharghar, Upper Kharghar
- Commute: Vashi, Train, Max 45 min
- EMI comfort: ₹65,000
- Down payment: ₹25L
- Timeline: 3 months
- Possession: By 2029
- Risk: Low
- Developer: Established preferred
- Priorities: Budget > Commute > Developer > Possession > School

### Project: "Skyline Heights, Kharghar"
- Developer: ABC Realty (15 years, 20 projects delivered, 85% on time)
- Config: 2 BHK, 680 sq ft
- Price: ₹88L–₹98L
- Possession: Dec 2028
- RERA: Registered
- Construction: 40% complete
- Railway: 1.2 km from Kharghar station
- Commute to Vashi: 25 min by train
- Amenities: Pool, Gym, Kids play, Garden, Gated
- Rental yield estimate: 2.8%
- Infrastructure: Metro planned nearby
- Litigation: None

### Calculation:

| Category | Score | Weight (adjusted) | Weighted |
|----------|-------|--------------------|----------|
| Budget Fit | 100 (₹88L < ₹95L comfort) | 18.5% (priority #1 boost) | 18.5 |
| Location Fit | 100 (Kharghar = preferred) | 12% | 12.0 |
| Config Fit | 100 (exact 2 BHK) | 8% | 8.0 |
| Carpet Area Fit | 90 (680 within 550-800) | 5% | 4.5 |
| Possession Fit | 85 (2028 < max 2029, dev reliable) | 10% | 8.5 |
| EMI Fit | 95 (EMI ~₹56K < ₹65K comfort) | 8% | 7.6 |
| Commute Fit | 100 (25 min < 45 min max) | 9.5% (priority #2) | 9.5 |
| Lifestyle Fit | 80 (4/5 amenities matched) | 5% | 4.0 |
| Developer Reliability | 85 (established, good record) | 9% (priority #3) | 7.7 |
| Construction Stage | 65 (40% - moderate for low-risk) | 3% | 2.0 |
| Legal Confidence | 90 (RERA + bank approved) | 5% | 4.5 |
| Resale Liquidity | 75 (Kharghar good demand) | 2% | 1.5 |
| Rental Potential | 60 (2.8% yield - decent) | 1% | 0.6 |
| Appreciation | 70 (metro catalyst, good location) | 2% | 1.4 |
| Risk Compatibility | 75 (low risk customer, medium project) | 3% | 2.3 |
| Infrastructure | 70 (metro planned) | 1% | 0.7 |
| Family Suitability | 80 (school 2km, hospital 3km) | 5% | 4.0 |
| Urgency Match | 70 (3-month timeline, UC project) | 2% | 1.4 |
| Inventory | 85 (units available) | 2% | 1.7 |
| Proximity | 85 (1.2km station, school nearby) | 5.5% (priority #5) | 4.7 |
| **TOTAL** | | **100%** | **94.6** |

### Result: Fit Score = 94 (Excellent Fit)

### AI-Generated Explanation:

> **Why Skyline Heights ranked #1 for you:**
>
> This project matches your budget comfortably at ₹88–98L against your ₹95L target. It's in your preferred location of Kharghar with a 25-minute train commute to Vashi — well within your 45-minute limit.
>
> **Strongest matches:** Budget (within comfort zone), Location (Kharghar), Commute (25 min to Vashi), Developer (15-year track record, 85% on-time delivery)
>
> **Trade-off to consider:** Construction is 40% complete. With your preference for established developers, this reduces possession risk, but you should verify construction progress on site.
>
> **Estimated EMI:** ₹56,000/month (well within your ₹65,000 comfort level)
>
> **What to verify before booking:** Visit site to inspect construction quality. Verify RERA timeline. Check sample flat if available. Confirm bank loan approval for this project.

---

## Transparency & Methodology Page

The public `/methodology/` page must explain:

1. How Fit Score works (categories and logic, without exact formulas)
2. How hard filters work
3. How weights adjust by buyer type
4. How AI explanations are generated
5. How sponsored projects are handled (separate section, clearly labelled)
6. How project data is sourced and verified
7. How often data is updated
8. Limitations of the scoring system
9. How customers can report inaccuracies
