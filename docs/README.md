# 10Projects.com — Architecture Documentation

## Document Index

| # | Document | Contents |
|---|----------|----------|
| 01 | [Product Strategy](01-product-strategy.md) | Vision, personas (5), customer journeys (8 — incl. WhatsApp-first, browser micro-capture, human advisor), sitemap, IA, homepage wireframe (CRO-optimised) |
| 02 | [AI Assessment Flow](02-ai-assessment-flow.md) | **CRO v2:** 3-phase flow (not 5), 11 questions (not 25), instant results after 30 sec, tap-only priority selection, Trust Shield, exit-intent capture, WhatsApp bot path |
| 03 | [Scoring Methodology](03-scoring-methodology.md) | 20 scoring categories with formulas, weight profiles (end-user/investor), priority adjustment, sample Fit Score calculation (Rahul + Skyline Heights = 94%) |
| 04 | [Data Models](04-data-models.md) | 7 WordPress CPTs, 9 taxonomies, 15+ custom database tables with full SQL CREATE statements |
| 05 | [WordPress Architecture](05-wordpress-architecture.md) | Theme file structure, plugin file structure (60+ files), 8 user roles with capabilities, REST API endpoints (40+), caching strategy |
| 06 | [SEO & GEO Architecture](06-seo-geo-architecture.md) | URL structure, title/meta templates, JSON-LD schemas, internal linking engine, XML sitemaps, GEO content patterns, AI-search optimization, content calendar |
| 07 | [Lead Routing & Conversion](07-lead-routing-conversion.md) | Routing algorithm, quality scoring, classification, conversion tracking (40+ events), security architecture, performance plan, 4-phase development roadmap, risk matrix |
| 08 | [Design System](08-design-system.md) | **CRO v2:** Colors, typography, spacing, 18 components (9 original + 9 CRO additions: EMI-first price, price anchor, Trust Shield, social proof bar, inventory status, micro-capture bar, contact preferences, resume banner, low-score state) |
| **09** | **[Behavioral CRO Audit](09-behavioral-cro-audit.md)** | **15 critical findings with fixes — assessment restructure, lead gate redesign, anti-spam trust signals, EMI-first display, WhatsApp-primary strategy, exit-intent capture, social proof, project page reorder, scarcity signals, human advisor escape hatch. Projected 3.3x improvement in leads per visitor.** |

## Architecture Summary

### What This Platform Does
1. Customer visits → 3-question Quick Match (30 sec) → Instant top 5 results
2. Optional: 4-7 more questions → Refined top 10 with Fit Scores
3. Trust Shield + OTP → Full analysis, comparisons, save
4. Multi-level capture for ALL visitor types (not just AI completers)
5. Lead qualified, classified, routed to partners
6. SEO pages drive organic traffic → micro-capture + AI funnel

### Conversion Flow (After CRO Audit)
```
100 visitors →
  Path A: 40 Quick Match → 35 see results → 20 give phone = 18 leads
  Path B: 30 browse pages → 6 email capture → 3 WhatsApp = 9 micro-leads
  Path C: 10 WhatsApp assessment → 8 complete = 8 leads
  Path D: 5 request callback → 4 connect = 4 leads
  Total: ~33 leads per 100 visitors (vs ~10 before audit)
```

### Tech Stack
- **CMS:** WordPress 6.x (block theme)
- **Plugin:** Custom "10Projects AI Matcher"
- **AI:** Provider-agnostic (Claude / OpenAI / Gemini)
- **Database:** MySQL custom tables + WordPress CPTs
- **Cache:** Redis object cache + full-page cache
- **Frontend:** Minimal JS, server-rendered HTML, lazy-loaded AI chat
- **Performance Target:** LCP < 2.5s, INP < 200ms, CLS < 0.1

### Next Step
Review all documents (especially the CRO audit in Doc 09). Once approved, proceed to Phase 1 code generation:
1. WordPress theme scaffold
2. Plugin bootstrap with database migrations
3. CPT and taxonomy registration
4. Project detail page template (CRO-optimised section order)
5. AI assessment flow (3-phase with instant results)
6. Scoring engine (handles partial data from Phase 1)
7. Multi-level lead capture system (OTP + email + WhatsApp + callback)
8. Homepage (with social proof, single CTA, WhatsApp entry)
