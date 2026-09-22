<?php
/**
 * Chatbot Widget — Clean Lead Capture with avatar
 * 2 questions + phone. Full-screen on mobile.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;
$cb_avatar = get_template_directory_uri() . '/assets/images/nidhi-avatar.png';
$cb_project = '';
if ( ! empty( $GLOBALS['tp_lp_post_id'] ) ) {
	$cb_project = get_post_meta( $GLOBALS['tp_lp_post_id'], '_tp_lp_project_name', true );
}
$cb_greeting = $cb_project
	? 'Hi! I\'m Nidhi from ' . esc_html( $cb_project ) . '. How can I help you?'
	: 'Hi! I\'m Nidhi. Need help finding your home?';
?>

<!-- Chatbot Floating Trigger -->
<div class="tp-cb-trigger" id="tp-cb-trigger">
	<div class="tp-cb-trigger__greeting" id="tp-cb-greeting">
		<span><?php echo $cb_greeting; ?></span>
		<button class="tp-cb-trigger__close-greeting" aria-label="Close" onclick="document.getElementById('tp-cb-greeting').style.display='none'">
			<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
	</div>
	<button class="tp-cb-trigger__btn" id="tp-cb-open" aria-label="Chat with Nidhi">
		<img class="tp-cb-trigger__icon tp-cb-trigger__icon--chat tp-cb-trigger__photo" src="<?php echo esc_url( $cb_avatar ); ?>" alt="Nidhi Joshi" width="68" height="68">
		<svg class="tp-cb-trigger__icon tp-cb-trigger__icon--close" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		<span class="tp-cb-trigger__online"></span>
	</button>
</div>

<!-- Chatbot Panel -->
<div class="tp-cb-panel" id="tp-cb-panel">
	<!-- Header: Avatar + Name + Close -->
	<div class="tp-cb-header">
		<div class="tp-cb-header__avatar">
			<img src="<?php echo esc_url( $cb_avatar ); ?>" alt="Nidhi Joshi" width="40" height="40">
		</div>
		<div class="tp-cb-header__info">
			<div class="tp-cb-header__name">Nidhi Joshi</div>
			<div class="tp-cb-header__role">Home Advisor</div>
		</div>
		<button class="tp-cb-header__close" id="tp-cb-close" aria-label="Close chat">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
	</div>

	<!-- Chat Messages -->
	<div class="tp-cb-messages" id="tp-cb-messages"></div>
</div>
