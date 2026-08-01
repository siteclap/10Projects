# SEO & GEO/AI-Search Architecture

## SEO Architecture

### URL Structure

```
/                                                    Homepage
/navi-mumbai/                                        City page
/navi-mumbai/kharghar/                               Location page
/navi-mumbai/kharghar/skyline-heights/                Project page
/developers/                                         All developers
/developers/godrej-properties/                       Developer page
/compare/skyline-heights-vs-green-valley/            Comparison page
/guides/best-2bhk-in-kharghar/                       Guide page
/guides/best-projects-under-1-crore-navi-mumbai/     Guide page
/market-insights/navi-mumbai-price-trends-q2-2026/   Market report
/tools/emi-calculator/                               Tool page
/methodology/                                        Methodology page
```

### Title Tag Templates

| Page Type | Template |
|-----------|----------|
| Homepage | `10Projects — Find the 10 Best-Fit Projects for You` |
| City | `Best New Projects in {City} (2026) — 10Projects` |
| Location | `Best Projects in {Location}, {City} — Prices, Reviews, Analysis` |
| Project | `{Project Name}, {Location} — Price, Reviews, Pros & Cons — 10Projects` |
| Developer | `{Developer Name} Projects — Track Record, Reviews, Analysis` |
| Comparison | `{Project A} vs {Project B} — Detailed Comparison — 10Projects` |
| Guide | `{Guide Title} — 10Projects` |
| Config Guide | `Best {Config} in {Location} — Top Projects, Prices, Analysis` |
| Budget Guide | `Best Projects Under ₹{Budget} in {City} — 10Projects` |

### Meta Description Templates

| Page Type | Template |
|-----------|----------|
| Project | `{Project Name} in {Location} by {Developer}. {Config} from ₹{Price}. Possession {Year}. RERA: {Number}. Read honest pros, cons, risk analysis & AI suitability score.` |
| Location | `Explore {Count} projects in {Location}, {City}. Average price ₹{Avg}/sq ft. Compare prices, developers, possession dates. Get AI-matched recommendations.` |
| Comparison | `Compare {Project A} vs {Project B} — price, carpet area, developer, possession, amenities, risk, and AI fit score. Find which suits you better.` |

### Structured Data (JSON-LD)

#### Project Page Schema
```json
{
  "@context": "https://schema.org",
  "@type": "RealEstateListing",
  "name": "Skyline Heights",
  "description": "2 BHK and 3 BHK apartments in Kharghar...",
  "url": "https://10projects.com/navi-mumbai/kharghar/skyline-heights/",
  "image": "https://10projects.com/images/skyline-heights.webp",
  "datePosted": "2026-01-15",
  "dateModified": "2026-07-28",
  "offers": {
    "@type": "AggregateOffer",
    "lowPrice": 8800000,
    "highPrice": 15500000,
    "priceCurrency": "INR"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": 19.0330,
    "longitude": 73.0680
  },
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Kharghar",
    "addressRegion": "Navi Mumbai",
    "addressCountry": "IN"
  },
  "author": {
    "@type": "Organization",
    "name": "10Projects.com"
  },
  "review": {
    "@type": "Review",
    "author": {"@type": "Person", "name": "10Projects Editorial"},
    "reviewBody": "Skyline Heights offers good value...",
    "datePublished": "2026-07-28"
  }
}
```

#### FAQ Schema (on every project/location page)
```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "What is the price range of Skyline Heights?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Skyline Heights prices range from ₹88 lakh to ₹1.55 crore..."
      }
    }
  ]
}
```

#### BreadcrumbList Schema
```json
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://10projects.com/"},
    {"@type": "ListItem", "position": 2, "name": "Navi Mumbai", "item": "https://10projects.com/navi-mumbai/"},
    {"@type": "ListItem", "position": 3, "name": "Kharghar", "item": "https://10projects.com/navi-mumbai/kharghar/"},
    {"@type": "ListItem", "position": 4, "name": "Skyline Heights"}
  ]
}
```

#### Organization Schema (homepage)
```json
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "10Projects.com",
  "url": "https://10projects.com",
  "logo": "https://10projects.com/logo.png",
  "description": "AI-powered real estate discovery platform...",
  "sameAs": ["social media URLs"]
}
```

### Internal Linking Engine

#### From Project Pages → Link To:
- Location page (breadcrumb + body)
- Developer page
- Other projects by same developer
- Other projects in same location
- Other projects in same budget range
- Other projects with same configuration
- Comparison with top 2 alternatives
- Location investment guide
- Configuration guide for the area
- Infrastructure updates affecting the area
- EMI calculator (with pre-filled values)

#### From Location Pages → Link To:
- All projects in location
- Top 10 projects in location
- Sub-locations / micro-locations
- Parent city page
- Budget guides (e.g., "Under ₹1Cr in Kharghar")
- Configuration guides (e.g., "Best 2 BHK in Kharghar")
- Comparison pages (e.g., "Kharghar vs Panvel")
- Infrastructure updates
- Market insights
- Nearby location pages

#### From Guide Pages → Link To:
- All mentioned projects
- All mentioned locations
- Related guides
- Assessment CTA

### XML Sitemaps

Generate separate sitemaps:
1. `sitemap-projects.xml` — All active project pages
2. `sitemap-locations.xml` — All city, location, micro-location pages
3. `sitemap-developers.xml` — All developer pages
4. `sitemap-guides.xml` — All guide pages
5. `sitemap-comparisons.xml` — All comparison pages
6. `sitemap-pages.xml` — Static pages
7. `sitemap-images.xml` — Image sitemap
8. `sitemap-index.xml` — Master sitemap index

### Index Control

**Index (allow):**
- All project pages (active)
- All location pages
- All developer pages
- All guides
- Pre-built comparison pages
- Market reports
- Tool pages
- Methodology page

**NoIndex:**
- Search/filter result pages
- User-generated comparison URLs
- Customer dashboard pages
- Results pages
- Paginated archives beyond page 1
- Tag archives
- Date archives
- Author archives (unless editorial)
- Sold-out project pages (consider 301 → location)

### Image Optimisation

- All images served as WebP (AVIF where supported)
- Responsive images with srcset (320w, 640w, 960w, 1280w)
- Lazy loading for below-fold images
- Width and height attributes on all images (prevent CLS)
- Descriptive alt text: `"{Project Name} - {View Description} in {Location}"`
- Image sitemap submission

### Core Web Vitals Targets

| Metric | Target | Strategy |
|--------|--------|----------|
| LCP | < 2.5s | Critical CSS inline, preload hero image, server cache |
| INP | < 200ms | Minimal JS, defer non-critical, no heavy frameworks |
| CLS | < 0.1 | Image dimensions, font-display: swap, no dynamic inserts above fold |

---

## GEO / AI-Search Optimisation

### Content Structure for AI Extraction

Every project and location page should include clearly structured, extractable content blocks.

#### Answer Blocks Pattern

```html
<section class="tp-answer-block" data-question="Is Skyline Heights suitable for end-users?">
  <h3>Is Skyline Heights suitable for end-users?</h3>
  <p>Skyline Heights is well-suited for end-users looking for 2 BHK homes in
     Kharghar within ₹88–98 lakh. The project is 1.2 km from Kharghar railway
     station and offers family-friendly amenities. However, buyers who need
     immediate possession should note that the expected completion is December 2028.</p>
</section>
```

#### Summary Box Pattern

```html
<div class="tp-summary-box" role="complementary">
  <h3>Quick Facts — Skyline Heights</h3>
  <table>
    <tr><td>Developer</td><td>ABC Realty (15 years, 20 projects delivered)</td></tr>
    <tr><td>Location</td><td>Kharghar, Navi Mumbai</td></tr>
    <tr><td>Configuration</td><td>2 BHK (680 sq ft), 3 BHK (920 sq ft)</td></tr>
    <tr><td>Price Range</td><td>₹88 lakh – ₹1.55 crore</td></tr>
    <tr><td>Possession</td><td>December 2028 (expected)</td></tr>
    <tr><td>RERA</td><td>P52000XXXXX</td></tr>
    <tr><td>Construction</td><td>40% complete</td></tr>
    <tr><td>10Projects Score</td><td>Varies by buyer profile</td></tr>
  </table>
  <p><small>Last verified: July 2026. Prices and availability subject to change.</small></p>
</div>
```

#### Pros and Cons Pattern

```html
<section class="tp-pros-cons">
  <div class="tp-pros">
    <h3>Advantages</h3>
    <ul>
      <li>Competitive pricing compared to Kharghar average</li>
      <li>Strong railway connectivity (1.2 km to station)</li>
      <li>Established developer with 85% on-time delivery</li>
    </ul>
  </div>
  <div class="tp-cons">
    <h3>Concerns</h3>
    <ul>
      <li>Possession 2+ years away — construction at 40%</li>
      <li>High density — 500+ units in the project</li>
      <li>Limited green space compared to neighbouring projects</li>
    </ul>
  </div>
</section>
```

#### Comparison Table Pattern

```html
<table class="tp-comparison-table">
  <caption>Skyline Heights vs Green Valley — Key Differences</caption>
  <thead>
    <tr><th>Factor</th><th>Skyline Heights</th><th>Green Valley</th></tr>
  </thead>
  <tbody>
    <tr><td>Price (2 BHK)</td><td>₹88–98L</td><td>₹75–85L</td></tr>
    <tr><td>Carpet Area</td><td>680 sq ft</td><td>620 sq ft</td></tr>
    <tr><td>Possession</td><td>Dec 2028</td><td>Mar 2029</td></tr>
    <tr><td>Developer Track Record</td><td>85% on time</td><td>70% on time</td></tr>
    <tr><td>Railway Distance</td><td>1.2 km</td><td>2.5 km</td></tr>
  </tbody>
</table>
```

### GEO Content Guidelines

1. **Factual precision:** No vague marketing copy. Use specific numbers, distances, dates.
2. **Source attribution:** Every claim should cite RERA, government data, or verified source.
3. **Date stamping:** Every page shows "Last verified: [date]" and "Reviewed by: [name]."
4. **Balanced view:** Always include advantages AND disadvantages.
5. **Question-answer format:** Use Q&A blocks for common queries.
6. **Structured data:** Every data point in semantic HTML (tables, lists, definition lists).
7. **No hidden promotional content:** Sponsored content clearly labelled.
8. **Regular updates:** Stale pages lose AI citation credibility.
9. **Methodology transparency:** Link to `/methodology/` from every scored page.
10. **Direct language:** Write for extraction — short sentences, clear claims, no ambiguity.

### Methodology Page Content (for AI trust)

The `/methodology/` page should include:

1. **How we collect data:** Sources (RERA portals, developer filings, site visits, market data)
2. **How we score projects:** Overview of 20 scoring categories (without exact formulas)
3. **How we rank results:** Weight profiles for different buyer types
4. **How we verify information:** Verification workflow, update frequency
5. **How we handle sponsored projects:** Separate section, never mixed into organic ranking
6. **How we calculate projections:** Conservative, base, optimistic — with disclaimers
7. **Our editorial standards:** Review process, author credentials
8. **Our limitations:** What we cannot guarantee, what buyers should verify independently
9. **How to report errors:** Contact form for corrections
10. **Version history:** When methodology was last updated

### Content Calendar (SEO Pages to Build)

#### Priority 1 (Launch)
- 15 location pages (all Navi Mumbai locations)
- 1 city page (Navi Mumbai)
- All active project pages (50-150)
- 10 developer pages
- 5 budget guides ("Best under ₹50L/₹75L/₹1Cr/₹1.5Cr/₹2Cr in Navi Mumbai")
- 5 config guides ("Best 1BHK/2BHK/3BHK/4BHK in Navi Mumbai")
- Methodology page
- EMI calculator page

#### Priority 2 (Month 1-2)
- 15 location-config guides ("Best 2BHK in Kharghar," etc.)
- 10 comparison pages (top project matchups)
- 5 location comparison pages ("Kharghar vs Panvel," etc.)
- 5 buyer-type guides ("Best for first-time buyers," "Best for investors," etc.)
- 3 infrastructure articles (Airport, Metro, Highway impact)

#### Priority 3 (Month 3-6)
- 10 new micro-location pages
- 20 additional comparison pages
- Monthly market reports
- NRI guide
- Loan guide
- Stamp duty calculator
- Project search intent pages ("new launch in Kharghar," "ready possession Panvel")
