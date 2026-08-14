<?php
/**
 * Sidebar — Premium Lead Form + Share + Trust
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id   = $args['post_id'] ?? get_the_ID();
$price_min = $args['price_min'] ?? intval( tp_get_meta( $post_id, 'price_display_min' ) );
$price_max = $args['price_max'] ?? intval( tp_get_meta( $post_id, 'price_display_max' ) );
$offers    = $args['offers'] ?? tp_parse_json_meta( $post_id, 'offers' );
$phone     = tp_get_meta( $post_id, 'phone' );
$wa_number = $phone ? preg_replace( '/[^0-9]/', '', $phone ) : '919999999999';
$wa_msg    = rawurlencode( 'Hi, I am interested in ' . get_the_title( $post_id ) );
$title     = get_the_title( $post_id );
$url       = get_permalink( $post_id );
$cb_avatar = get_template_directory_uri() . '/assets/images/nidhi-avatar.png';
?>

<!-- Lead Form Card — Premium -->
<div class="tp-sidebar-card tp-sidebar-card--form tp-lf">
	<!-- Gradient Header -->
	<div class="tp-lf__header">
		<div class="tp-lf__header-badge">
			<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
			Instant Connect
		</div>
		<h3 class="tp-lf__header-title">Get the Best Deal</h3>
		<p class="tp-lf__header-sub">on <strong><?php echo esc_html( $title ); ?></strong></p>
	</div>

	<!-- Advisor Strip -->
	<div class="tp-lf__advisor">
		<img src="<?php echo esc_url( $cb_avatar ); ?>" alt="Nidhi Joshi" width="40" height="40" class="tp-lf__advisor-img">
		<div class="tp-lf__advisor-info">
			<div class="tp-lf__advisor-name">Nidhi Joshi</div>
			<div class="tp-lf__advisor-role">
				<span class="tp-lf__online-dot"></span>
				Home Advisor &middot; Online
			</div>
		</div>
	</div>

	<!-- Form -->
	<form class="tp-lead-form js-lead-form" novalidate
		data-ajax-url="<?php echo esc_url( admin_url('admin-ajax.php') ); ?>">
		<input type="hidden" name="action" value="tp_sidebar_lead">
		<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'tp_sidebar_lead_nonce' ); ?>">
		<input type="hidden" name="project_id" value="<?php echo esc_attr( $post_id ); ?>">
		<input type="hidden" name="project_name" value="<?php echo esc_attr( $title ); ?>">
		<input type="hidden" name="page_url" value="<?php echo esc_url( $url ); ?>">

		<div class="tp-lf__field">
			<span class="tp-lf__field-icon">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			</span>
			<input type="text" name="name" placeholder="Your Name" required autocomplete="name" class="tp-lead-form__input tp-lf__input">
		</div>

		<div class="tp-lf__field">
			<span class="tp-lf__field-icon tp-lf__field-icon--prefix">+91</span>
			<input type="tel" name="phone" placeholder="Mobile Number" required pattern="[0-9]{10}" maxlength="10" inputmode="numeric" autocomplete="tel" class="tp-lead-form__input tp-lf__input tp-lf__input--phone">
		</div>

		<div class="tp-lf__field">
			<span class="tp-lf__field-icon">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>
			</span>
			<input type="email" name="email" placeholder="Email (optional)" autocomplete="email" class="tp-lead-form__input tp-lf__input">
		</div>

		<button type="submit" class="tp-lf__submit tp-lead-form__submit">
			<span class="tp-lf__submit-text">Get Best Price</span>
			<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
		</button>

		<div class="tp-lead-form__status"></div>
	</form>

	<!-- Trust Badges Inline -->
	<div class="tp-lf__trust-row">
		<span>
			<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
			No spam
		</span>
		<span>
			<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
			Zero brokerage
		</span>
		<span>
			<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
			RERA verified
		</span>
	</div>

	<!-- Divider -->
	<div class="tp-lf__divider">
		<span>or connect instantly</span>
	</div>

	<!-- WhatsApp Button -->
	<a href="https://wa.me/<?php echo esc_attr( $wa_number ); ?>?text=<?php echo esc_attr( $wa_msg ); ?>" class="tp-lf__wa-btn" target="_blank" rel="noopener">
		<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
		Chat on WhatsApp
	</a>

	<!-- Social Proof -->
	<div class="tp-lf__social-proof">
		<div class="tp-lf__avatars">
			<span class="tp-lf__avatar-dot" style="background:#4f46e5;">R</span>
			<span class="tp-lf__avatar-dot" style="background:#0891b2;">A</span>
			<span class="tp-lf__avatar-dot" style="background:#059669;">S</span>
		</div>
		<span>23+ people enquired today</span>
	</div>
</div>

<!-- Share via WhatsApp (phone-gated) -->
<?php $brochure_url = add_query_arg( 'brochure', '1', $url ); ?>
<div class="tp-sidebar-card tp-sidebar-card--share">
	<div class="tp-share-label">Share this project</div>

	<!-- Initial share button -->
	<button type="button" class="tp-share-wa-btn js-share-trigger">
		<svg width="20" height="20" fill="#fff" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
		Share on WhatsApp
	</button>

	<!-- Phone capture (hidden initially) -->
	<form class="tp-share-phone-form js-share-form" style="display:none;" novalidate
		data-share-url="https://wa.me/?text=<?php echo rawurlencode( $title . "\n\n" . $brochure_url ); ?>"
		data-ajax-url="<?php echo esc_url( admin_url('admin-ajax.php') ); ?>">
		<input type="hidden" name="action" value="tp_sidebar_lead">
		<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'tp_sidebar_lead_nonce' ); ?>">
		<input type="hidden" name="project_id" value="<?php echo esc_attr( $post_id ); ?>">
		<input type="hidden" name="project_name" value="<?php echo esc_attr( $title ); ?>">
		<input type="hidden" name="page_url" value="<?php echo esc_url( $url ); ?>">
		<input type="hidden" name="lead_source" value="share_brochure">
		<div class="tp-share-phone-form__label">Enter your number to share the brochure</div>
		<div class="tp-lf__field">
			<span class="tp-lf__field-icon tp-lf__field-icon--prefix">+91</span>
			<input type="tel" name="phone" placeholder="Mobile Number" required pattern="[0-9]{10}" maxlength="10" inputmode="numeric" autocomplete="tel" class="tp-lead-form__input tp-lf__input tp-lf__input--phone js-share-phone">
		</div>
		<button type="submit" class="tp-share-wa-btn js-share-submit">
			<svg width="20" height="20" fill="#fff" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
			Share Now
		</button>
		<div class="tp-share-phone-form__status"></div>
	</form>

	<div class="tp-share-hint">Shares a detailed project brochure via WhatsApp</div>
</div>

<script>
/* Lead form — handles multiple instances */
document.querySelectorAll('.js-lead-form').forEach(function(form){
	var ajaxUrl = form.getAttribute('data-ajax-url');

	form.addEventListener('submit', function(e){
		e.preventDefault();
		var btn = form.querySelector('.tp-lead-form__submit');
		var status = form.querySelector('.tp-lead-form__status');
		var nameInput = form.querySelector('input[name="name"]');
		var phoneInput = form.querySelector('input[name="phone"]');
		var name = nameInput.value.trim();
		var phone = phoneInput.value.replace(/\D/g,'');

		/* Validation with visible message */
		if (!name) {
			nameInput.closest('.tp-lf__field').classList.add('is-error');
			status.className = 'tp-lead-form__status is-error';
			status.textContent = 'Please enter your name';
			return;
		}
		if (phone.length < 10) {
			phoneInput.closest('.tp-lf__field').classList.add('is-error');
			status.className = 'tp-lead-form__status is-error';
			status.textContent = 'Please enter a valid 10-digit mobile number';
			return;
		}

		btn.disabled = true;
		btn.querySelector('.tp-lf__submit-text').textContent = 'Submitting...';
		status.textContent = '';

		var data = new FormData(form);
		fetch(ajaxUrl, { method: 'POST', body: data })
			.then(function(r){ return r.json(); })
			.then(function(res){
				if (res.success) {
					/* Replace form with success state */
					var card = form.closest('.tp-lf');
					var successHtml = '<div class="tp-lf__success">' +
						'<div class="tp-lf__success-icon"><svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>' +
						'<h4>Thank You!</h4>' +
						'<p>Our expert will call you within 15 minutes with the best deal.</p>' +
						'</div>';
					form.innerHTML = successHtml;
					if (status) status.style.display = 'none';
				} else {
					status.className = 'tp-lead-form__status is-error';
					status.textContent = 'Something went wrong. Please try again.';
					btn.disabled = false;
					btn.querySelector('.tp-lf__submit-text').textContent = 'Get Best Price';
				}
			})
			.catch(function(){
				status.className = 'tp-lead-form__status is-error';
				status.textContent = 'Network error. Please try again.';
				btn.disabled = false;
				btn.querySelector('.tp-lf__submit-text').textContent = 'Get Best Price';
			});
	});

	form.querySelectorAll('.tp-lf__input').forEach(function(inp){
		inp.addEventListener('input', function(){
			var field = inp.closest('.tp-lf__field');
			if (field) field.classList.remove('is-error');
			var st = form.querySelector('.tp-lead-form__status');
			if (st && st.classList.contains('is-error')) { st.textContent = ''; st.className = 'tp-lead-form__status'; }
		});
	});
});

/* Share phone gate — handles multiple sidebar instances */
document.querySelectorAll('.js-share-trigger').forEach(function(trigger){
	var card = trigger.closest('.tp-sidebar-card--share');
	if (!card) return;
	var phoneForm = card.querySelector('.js-share-form');
	if (!phoneForm) return;

	trigger.addEventListener('click', function(){
		trigger.style.display = 'none';
		phoneForm.style.display = 'block';
		phoneForm.querySelector('.js-share-phone').focus();
	});

	phoneForm.querySelector('.js-share-phone').addEventListener('input', function(){
		var field = this.closest('.tp-lf__field');
		if (field) field.classList.remove('is-error');
	});

	phoneForm.addEventListener('submit', function(e){
		e.preventDefault();
		var phoneInput = phoneForm.querySelector('.js-share-phone');
		var phone = phoneInput.value.replace(/\D/g,'');
		var btn = phoneForm.querySelector('.js-share-submit');
		var status = phoneForm.querySelector('.tp-share-phone-form__status');
		var shareUrl = phoneForm.getAttribute('data-share-url');
		var ajaxUrl = phoneForm.getAttribute('data-ajax-url');

		if (phone.length < 10) { phoneInput.closest('.tp-lf__field').classList.add('is-error'); return; }

		btn.disabled = true;
		btn.textContent = 'Sharing...';

		var data = new FormData(phoneForm);
		fetch(ajaxUrl, { method: 'POST', body: data })
			.then(function(r){ return r.json(); })
			.then(function(){
				window.open(shareUrl, '_blank');
				status.className = 'tp-share-phone-form__status is-success';
				status.textContent = 'Brochure shared!';
				btn.disabled = false;
				btn.textContent = 'Share Again';
			})
			.catch(function(){
				window.open(shareUrl, '_blank');
				btn.disabled = false;
				btn.textContent = 'Share Again';
			});
	});
});
</script>
