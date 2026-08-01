<?php
/**
 * Shortcodes for 10Projects theme integration.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Shortcodes;

defined( 'ABSPATH' ) || exit;

class Shortcodes {

    /**
     * Register all shortcodes.
     */
    public function register() {
        add_shortcode( 'tp_project_carousel', array( $this, 'project_carousel' ) );
        add_shortcode( 'tp_locations_grid', array( $this, 'locations_grid' ) );
        add_shortcode( 'tp_developer_logos', array( $this, 'developer_logos' ) );
        add_shortcode( 'tp_trust_bar', array( $this, 'trust_bar' ) );
        add_shortcode( 'tp_fit_score', array( $this, 'fit_score' ) );
    }

    /**
     * [tp_project_carousel type="ai_picked" count="8"]
     */
    public function project_carousel( $atts ) {
        $atts = shortcode_atts( array(
            'type'  => 'ai_picked',
            'count' => 8,
            'city'  => '',
        ), $atts, 'tp_project_carousel' );

        $args = array(
            'post_type'      => 'tp_project',
            'post_status'    => 'publish',
            'posts_per_page' => absint( $atts['count'] ),
            'meta_query'     => array(
                array(
                    'key'     => '_tp_status',
                    'value'   => 'active',
                    'compare' => '=',
                ),
            ),
        );

        // Sort by type.
        if ( $atts['type'] === 'fast_selling' ) {
            $args['meta_key'] = '_tp_total_units';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'ASC';
        }

        // City filter.
        if ( $atts['city'] ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'tp_city',
                    'field'    => 'slug',
                    'terms'    => sanitize_title( $atts['city'] ),
                ),
            );
        }

        $query = new \WP_Query( $args );

        if ( ! $query->have_posts() ) {
            return '<p class="text-muted text-center">No projects available at the moment.</p>';
        }

        ob_start();
        echo '<div class="project-scroll">';
        while ( $query->have_posts() ) {
            $query->the_post();
            if ( function_exists( 'tp_project_card' ) ) {
                echo tp_project_card( get_the_ID() );
            }
        }
        echo '</div>';
        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * [tp_locations_grid count="8"]
     */
    public function locations_grid( $atts ) {
        $atts = shortcode_atts( array(
            'count' => 8,
            'city'  => '',
        ), $atts, 'tp_locations_grid' );

        $args = array(
            'post_type'      => 'tp_location',
            'post_status'    => 'publish',
            'posts_per_page' => absint( $atts['count'] ),
            'meta_key'       => '_tp_loc_total_projects',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        );

        if ( $atts['city'] ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'tp_city',
                    'field'    => 'slug',
                    'terms'    => sanitize_title( $atts['city'] ),
                ),
            );
        }

        $query = new \WP_Query( $args );

        if ( ! $query->have_posts() ) {
            return '';
        }

        ob_start();
        echo '<div class="location-grid">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();
            $avg_price = get_post_meta( $id, '_tp_loc_avg_price_sqft', true );
            $projects  = get_post_meta( $id, '_tp_loc_total_projects', true );
            $growth    = get_post_meta( $id, '_tp_loc_price_trend_3yr', true );
            ?>
            <a href="<?php the_permalink(); ?>" class="location-card">
                <div class="location-card__image">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'tp-location-thumb' ); ?>
                    <?php else : ?>
                        <?php the_title(); ?>
                    <?php endif; ?>
                </div>
                <div class="location-card__body">
                    <div class="location-card__name"><?php the_title(); ?></div>
                    <div class="location-card__stats">
                        <?php if ( $projects ) : ?>
                            <?php echo absint( $projects ); ?> Projects
                        <?php endif; ?>
                        <?php if ( $avg_price ) : ?>
                            &bull; Avg <?php echo esc_html( function_exists( 'tp_format_price' ) ? tp_format_price( $avg_price, '/sqft' ) : '₹' . number_format( $avg_price ) . '/sqft' ); ?>
                        <?php endif; ?>
                    </div>
                    <?php if ( $growth ) : ?>
                        <div class="location-card__growth">↑ <?php echo esc_html( number_format( $growth, 1 ) ); ?>% (3yr)</div>
                    <?php endif; ?>
                </div>
            </a>
            <?php
        }
        echo '</div>';
        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * [tp_developer_logos count="8"]
     */
    public function developer_logos( $atts ) {
        $atts = shortcode_atts( array(
            'count' => 8,
        ), $atts, 'tp_developer_logos' );

        $query = new \WP_Query( array(
            'post_type'      => 'tp_developer',
            'post_status'    => 'publish',
            'posts_per_page' => absint( $atts['count'] ),
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        if ( ! $query->have_posts() ) {
            return '';
        }

        ob_start();
        echo '<div class="developer-logos">';
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<div class="developer-logo">';
            if ( has_post_thumbnail() ) {
                the_post_thumbnail( 'tp-developer-logo' );
            } else {
                echo esc_html( get_the_title() );
            }
            echo '</div>';
        }
        echo '</div>';
        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * [tp_trust_bar]
     */
    public function trust_bar( $atts ) {
        ob_start();
        ?>
        <div class="trust-bar">
            <div class="trust-item">
                <div class="trust-item__icon">🔍</div>
                <div class="trust-item__title">100% Honest Analysis</div>
                <div class="trust-item__desc">Every project gets the same unbiased evaluation. No sponsored rankings, no hidden promotions.</div>
            </div>
            <div class="trust-item">
                <div class="trust-item__icon">🏛️</div>
                <div class="trust-item__title">RERA Verified</div>
                <div class="trust-item__desc">We verify RERA registration, developer track records, and legal clearances before listing any project.</div>
            </div>
            <div class="trust-item">
                <div class="trust-item__icon">🤖</div>
                <div class="trust-item__title">AI-Powered, Human-Verified</div>
                <div class="trust-item__desc">Our AI analyses 20+ parameters per project. Property experts verify data and add on-ground insights.</div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * [tp_fit_score score="87"]
     */
    public function fit_score( $atts ) {
        $atts = shortcode_atts( array(
            'score'  => 0,
            'static' => 'true',
        ), $atts, 'tp_fit_score' );

        if ( function_exists( 'tp_fit_score_badge' ) ) {
            return tp_fit_score_badge( absint( $atts['score'] ), $atts['static'] === 'true' );
        }

        return '';
    }
}
