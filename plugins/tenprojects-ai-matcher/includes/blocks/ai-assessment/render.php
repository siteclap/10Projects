<?php
/**
 * Server-side render for AI Assessment block.
 *
 * @package TenProjects
 * @since 1.0.0
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

// Enqueue AI chat assets.
wp_enqueue_style(
    'tp-ai-chat',
    get_theme_file_uri( 'assets/css/ai-chat.css' ),
    array(),
    TP_PLUGIN_VERSION
);

wp_enqueue_script(
    'tp-ai-chat',
    get_theme_file_uri( 'assets/js/ai-chat.js' ),
    array(),
    TP_PLUGIN_VERSION,
    true
);

wp_localize_script( 'tp-ai-chat', 'tpChat', array(
    'restUrl' => esc_url_raw( rest_url( 'tenprojects/v1/' ) ),
    'nonce'   => wp_create_nonce( 'wp_rest' ),
) );

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'chat-page',
) );
?>
<div <?php echo $wrapper_attributes; ?>>

    <!-- Chat Header with Accuracy -->
    <div class="chat-header">
        <div class="container container--assessment chat-header__inner">
            <div class="chat-header__title"><?php esc_html_e( 'AI Property Matcher', 'tenprojects' ); ?></div>
            <div class="chat-header__accuracy">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span id="tp-accuracy-label"><?php esc_html_e( 'Match Accuracy:', 'tenprojects' ); ?> <strong>0%</strong></span>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="container container--assessment">
        <div class="assessment-progress">
            <div class="accuracy-bar">
                <div id="tp-accuracy-fill" class="accuracy-fill" style="width:0%"></div>
            </div>
            <div class="accuracy-phases">
                <span class="accuracy-phase accuracy-phase--active" data-phase="1"><?php esc_html_e( 'Basics', 'tenprojects' ); ?></span>
                <span class="accuracy-phase" data-phase="2"><?php esc_html_e( 'Lifestyle', 'tenprojects' ); ?></span>
                <span class="accuracy-phase" data-phase="3"><?php esc_html_e( 'Finance', 'tenprojects' ); ?></span>
                <span class="accuracy-phase" data-phase="4"><?php esc_html_e( 'Location', 'tenprojects' ); ?></span>
                <span class="accuracy-phase" data-phase="5"><?php esc_html_e( 'Priorities', 'tenprojects' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Chat Messages Area -->
    <div class="chat-messages">
        <div class="container container--assessment">
            <div id="tp-chat-messages">
                <!-- AI Welcome Message -->
                <div class="chat-message chat-message--ai">
                    <div class="chat-message__avatar">10</div>
                    <div class="chat-message__bubble">
                        <p><?php esc_html_e( "Hi! I'm your AI property matcher. I'll find your top 10 best-fit projects in under 2 minutes.", 'tenprojects' ); ?></p>
                        <p><?php esc_html_e( "Let's start with the basics. What type of property are you looking for?", 'tenprojects' ); ?></p>
                    </div>
                </div>

                <!-- Initial Option Chips -->
                <div class="chat-chips" data-question="property_type">
                    <button class="chip" data-value="apartment"><?php esc_html_e( 'Apartment', 'tenprojects' ); ?></button>
                    <button class="chip" data-value="villa"><?php esc_html_e( 'Villa / Row House', 'tenprojects' ); ?></button>
                    <button class="chip" data-value="plot"><?php esc_html_e( 'Plot / Land', 'tenprojects' ); ?></button>
                    <button class="chip" data-value="penthouse"><?php esc_html_e( 'Penthouse', 'tenprojects' ); ?></button>
                    <button class="chip" data-value="studio"><?php esc_html_e( 'Studio', 'tenprojects' ); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Input Area (sticky bottom) -->
    <div class="chat-input">
        <div class="container container--assessment">
            <div id="tp-chat-input" class="chat-input__inner">
                <textarea class="chat-input__field" id="tp-chat-field" placeholder="<?php esc_attr_e( 'Type your answer or pick an option above...', 'tenprojects' ); ?>" rows="1" aria-label="<?php esc_attr_e( 'Your answer', 'tenprojects' ); ?>"></textarea>
                <button class="chat-input__send" id="tp-chat-send" aria-label="<?php esc_attr_e( 'Send message', 'tenprojects' ); ?>" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </div>
            <div class="chat-input__hint">
                <span><?php esc_html_e( 'Your data is encrypted and never shared without consent', 'tenprojects' ); ?></span>
            </div>
        </div>
    </div>

</div>
