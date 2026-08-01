<?php
/**
 * Title: Homepage Hero
 * Slug: tenprojects/hero
 * Categories: tenprojects-homepage
 * Keywords: hero, search, home
 * Block Types: core/group
 */
?>

<!-- wp:group {"className":"hero","layout":{"type":"default"}} -->
<div class="wp-block-group hero">
    <!-- wp:group {"className":"container","layout":{"type":"default"}} -->
    <div class="wp-block-group container">
        <!-- wp:html -->
        <div class="hero__inner">
            <div class="hero__badge">
                <span class="hero__badge-dot"></span>
                AI-powered property matching
            </div>

            <h1 class="hero__headline">
                Find your dream home,<br>
                <span>intelligently.</span>
            </h1>
            <p class="hero__subtitle">
                India's first AI-powered real estate platform. Tell us what you need — we'll analyse 150+ projects and find the 10 that actually match.
            </p>

            <div class="hero__search">
                <input type="text" class="hero__search-input" placeholder="Search by location, project, or describe your ideal home..." aria-label="Search properties">
                <a href="/start" class="hero__search-btn" aria-label="Start AI assessment">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </a>
                <p class="hero__search-hint">No sign-up required &bull; Free forever</p>
            </div>

            <div class="city-chips">
                <a href="/navi-mumbai/" class="city-chip city-chip--active">Navi Mumbai</a>
                <a href="/mumbai/" class="city-chip">Mumbai</a>
                <a href="/thane/" class="city-chip">Thane</a>
                <a href="/pune/" class="city-chip">Pune</a>
                <a href="/panvel/" class="city-chip">Panvel</a>
            </div>

            <div class="hero__stats">
                <div class="hero__stat">
                    <div class="hero__stat-number">156</div>
                    <div class="hero__stat-label">Projects Analysed</div>
                </div>
                <div class="hero__stat">
                    <div class="hero__stat-number">12,847</div>
                    <div class="hero__stat-label">Buyers Matched</div>
                </div>
                <div class="hero__stat">
                    <div class="hero__stat-number">94%</div>
                    <div class="hero__stat-label">Said "Accurate"</div>
                </div>
            </div>
        </div>
        <!-- /wp:html -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->
