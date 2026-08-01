<?php
/**
 * Title: Popular Locations
 * Slug: tenprojects/popular-locations
 * Categories: tenprojects-homepage
 * Keywords: locations, grid, cities
 * Block Types: core/group
 */
?>

<!-- wp:group {"className":"section section--alt","layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group section section--alt">
    <!-- wp:heading {"level":2,"className":"section__title"} -->
    <h2 class="wp-block-heading section__title">Popular Locations</h2>
    <!-- /wp:heading -->

    <!-- wp:shortcode -->
    [tp_locations_grid count="8"]
    <!-- /wp:shortcode -->
</div>
<!-- /wp:group -->
