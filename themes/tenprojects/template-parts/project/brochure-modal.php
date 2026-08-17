<?php
/**
 * Brochure Download Modal — Lead-gated PDF trigger
 * Include once per project page. JS handles open/close.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id      = $args['post_id'] ?? get_the_ID();
$title        = get_the_title( $post_id );
$brochure_url = add_query_arg( 'brochure', '1', get_permalink( $post_id ) );
$url          = get_permalink( $post_id );
?>

<!-- ══════════════════════════════════════════════
     DOWNLOAD BROCHURE MODAL
     Trigger via: <button class="js-brochure-open">Download Brochure</button>
══════════════════════════════════════════════ -->
<div id="tp-brochure-modal" class="tp-bm" role="dialog" aria-modal="true" aria-label="Download Brochure" hidden>

	<!-- Backdrop -->
	<div class="tp-bm__backdrop js-brochure-close"></div>

	<!-- Modal Panel -->
	<div class="tp-bm__panel">

		<!-- Close -->
		<button class="tp-bm__close js-brochure-close" aria-label="Close">
			<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
				<path d="M6 18L18 6M6 6l12 12"/>
			</svg>
		</button>

		<!-- LEFT — Benefits -->
		<div class="tp-bm__left">
			<div class="tp-bm__left-badge">Free Download</div>
			<h2 class="tp-bm__left-title">
				Download<br>Brochure
			</h2>
			<p class="tp-bm__left-sub">Get complete details of<br><strong><?php echo esc_html( $title ); ?></strong></p>

			<ul class="tp-bm__benefits">
				<li class="tp-bm__benefit">
					<span class="tp-bm__benefit-icon">
						<svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
							<path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
						</svg>
					</span>
					<div>
						<div class="tp-bm__benefit-title">Get Instant Call Back</div>
						<div class="tp-bm__benefit-sub">Expert calls within 15 min</div>
					</div>
				</li>
				<li class="tp-bm__benefit">
					<span class="tp-bm__benefit-icon">
						<svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
							<path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
						</svg>
					</span>
					<div>
						<div class="tp-bm__benefit-title">Book Free Site Visit</div>
						<div class="tp-bm__benefit-sub">We arrange pickup &amp; drop</div>
					</div>
				</li>
				<li class="tp-bm__benefit">
					<span class="tp-bm__benefit-icon">
						<svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
							<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
						</svg>
					</span>
					<div>
						<div class="tp-bm__benefit-title">Get Best Price</div>
						<div class="tp-bm__benefit-sub">Zero brokerage, best deal</div>
					</div>
				</li>
			</ul>

			<!-- Decorative circles -->
			<div class="tp-bm__deco tp-bm__deco--1" aria-hidden="true"></div>
			<div class="tp-bm__deco tp-bm__deco--2" aria-hidden="true"></div>
		</div>

		<!-- RIGHT — Form -->
		<div class="tp-bm__right">
			<h3 class="tp-bm__form-title">
				Register Here &amp; Avail The <span class="tp-bm__form-title--accent">Best Offer!!</span>
			</h3>

			<!-- Form -->
			<form class="tp-bm__form js-brochure-form" novalidate
				data-brochure-url="<?php echo esc_url( $brochure_url ); ?>"
				data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
				<input type="hidden" name="action"       value="tp_sidebar_lead">
				<input type="hidden" name="nonce"        value="<?php echo wp_create_nonce( 'tp_sidebar_lead_nonce' ); ?>">
				<input type="hidden" name="project_id"   value="<?php echo esc_attr( $post_id ); ?>">
				<input type="hidden" name="project_name" value="<?php echo esc_attr( $title ); ?>">
				<input type="hidden" name="page_url"     value="<?php echo esc_url( $url ); ?>">
				<input type="hidden" name="lead_source"  value="brochure_download">

				<div class="tp-bm__field">
					<label class="tp-bm__label" for="bm-name">
						Name <span class="tp-bm__req">*</span>
					</label>
					<div class="tp-bm__input-wrap">
						<svg class="tp-bm__input-icon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
							<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
						</svg>
						<input id="bm-name" type="text" name="name" placeholder="Enter your full name"
							required autocomplete="name" class="tp-bm__input">
					</div>
					<div class="tp-bm__error" data-field="name"></div>
				</div>

				<div class="tp-bm__field">
					<label class="tp-bm__label" for="bm-email">Email Address</label>
					<div class="tp-bm__input-wrap">
						<svg class="tp-bm__input-icon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
							<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/>
						</svg>
						<input id="bm-email" type="email" name="email" placeholder="Enter your email (optional)"
							autocomplete="email" class="tp-bm__input">
					</div>
				</div>

				<div class="tp-bm__field">
					<label class="tp-bm__label" for="bm-phone">
						Mobile Number <span class="tp-bm__req">*</span>
					</label>
					<div class="tp-bm__input-wrap">
						<span class="tp-bm__prefix">+91</span>
						<input id="bm-phone" type="tel" name="phone" placeholder="10-digit mobile number"
							required pattern="[0-9]{10}" maxlength="10" inputmode="numeric"
							autocomplete="tel" class="tp-bm__input tp-bm__input--phone">
					</div>
					<div class="tp-bm__error" data-field="phone"></div>
				</div>

				<div class="tp-bm__status js-bm-status"></div>

				<button type="submit" class="tp-bm__submit js-bm-submit">
					<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
					</svg>
					<span class="js-bm-btn-text">Download Now</span>
				</button>

				<!-- Trust line -->
				<p class="tp-bm__privacy">
					<svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
					</svg>
					Your number is safe. No spam, ever.
				</p>
			</form>

			<!-- Social Proof -->
			<div class="tp-bm__social-proof">
				<div class="tp-bm__avatars">
					<span style="background:#4f46e5;">R</span>
					<span style="background:#0891b2;">A</span>
					<span style="background:#059669;">S</span>
					<span style="background:#d97706;">P</span>
					<span style="background:#dc2626;">K</span>
				</div>
				<span class="tp-bm__proof-text">3,790+ Customers Downloaded Already</span>
			</div>
		</div>

	</div><!-- /.tp-bm__panel -->
</div><!-- /#tp-brochure-modal -->


<style>
/* ═══════════════════════════════════════════
   BROCHURE MODAL
═══════════════════════════════════════════ */
.tp-bm {
	position: fixed; inset: 0; z-index: 9999;
	display: flex; align-items: center; justify-content: center;
	padding: 16px;
}
.tp-bm[hidden] { display: none; }

.tp-bm__backdrop {
	position: absolute; inset: 0;
	background: rgba(0,0,0,0.55);
	backdrop-filter: blur(3px);
	-webkit-backdrop-filter: blur(3px);
	cursor: pointer;
}

.tp-bm__panel {
	position: relative;
	display: grid;
	grid-template-columns: 1fr 1fr;
	max-width: 820px;
	width: 100%;
	border-radius: 20px;
	overflow: hidden;
	box-shadow: 0 24px 64px rgba(0,0,0,0.28);
	animation: tp-bm-in 0.25s cubic-bezier(0.34,1.56,0.64,1) both;
	max-height: 90vh;
	overflow-y: auto;
}
@keyframes tp-bm-in {
	from { opacity: 0; transform: scale(0.93) translateY(12px); }
	to   { opacity: 1; transform: scale(1) translateY(0); }
}

/* Close */
.tp-bm__close {
	position: absolute; top: 14px; right: 14px; z-index: 10;
	width: 32px; height: 32px; border-radius: 50%;
	background: rgba(255,255,255,0.15); border: none; cursor: pointer;
	display: flex; align-items: center; justify-content: center;
	color: #fff; transition: background 0.15s;
}
.tp-bm__close:hover { background: rgba(255,255,255,0.28); }

/* ── LEFT PANEL ── */
.tp-bm__left {
	background: linear-gradient(155deg, #1A56DB 0%, #1432a0 100%);
	color: #fff;
	padding: 40px 32px 40px;
	position: relative;
	overflow: hidden;
	display: flex; flex-direction: column; gap: 0;
}

.tp-bm__left-badge {
	display: inline-flex; align-items: center; gap: 6px;
	background: rgba(255,255,255,0.15);
	border: 1px solid rgba(255,255,255,0.25);
	border-radius: 100px;
	padding: 4px 14px;
	font-size: 12px; font-weight: 600;
	color: #fff; margin-bottom: 18px;
	width: fit-content;
	letter-spacing: 0.3px;
}

.tp-bm__left-title {
	font-size: 32px; font-weight: 800;
	line-height: 1.15; color: #fff;
	margin-bottom: 10px;
	letter-spacing: -0.5px;
}

.tp-bm__left-sub {
	font-size: 13px; color: rgba(255,255,255,0.8);
	margin-bottom: 32px; line-height: 1.5;
}
.tp-bm__left-sub strong { color: #fff; }

/* Benefits */
.tp-bm__benefits {
	list-style: none; display: flex; flex-direction: column; gap: 20px;
	position: relative; z-index: 1;
}

.tp-bm__benefit {
	display: flex; align-items: flex-start; gap: 14px;
}

.tp-bm__benefit-icon {
	width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
	background: rgba(255,255,255,0.15);
	border: 1px solid rgba(255,255,255,0.2);
	display: flex; align-items: center; justify-content: center;
	color: #fff;
}

.tp-bm__benefit-title {
	font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 2px;
}
.tp-bm__benefit-sub {
	font-size: 12px; color: rgba(255,255,255,0.72); line-height: 1.4;
}

/* Decorative circles */
.tp-bm__deco {
	position: absolute; border-radius: 50%;
	background: rgba(255,255,255,0.06); pointer-events: none;
}
.tp-bm__deco--1 { width: 220px; height: 220px; bottom: -60px; right: -60px; }
.tp-bm__deco--2 { width: 120px; height: 120px; top: -30px; right: 20px; }

/* ── RIGHT PANEL ── */
.tp-bm__right {
	background: #fff;
	padding: 40px 32px 32px;
	display: flex; flex-direction: column; gap: 0;
}

.tp-bm__form-title {
	font-size: 18px; font-weight: 700; color: #111827;
	line-height: 1.4; margin-bottom: 24px;
}
.tp-bm__form-title--accent { color: #ef4444; }

/* Fields */
.tp-bm__field { margin-bottom: 14px; }

.tp-bm__label {
	display: block; font-size: 13px; font-weight: 600;
	color: #374151; margin-bottom: 6px;
}
.tp-bm__req { color: #ef4444; }

.tp-bm__input-wrap {
	position: relative; display: flex; align-items: center;
}

.tp-bm__input-icon {
	position: absolute; left: 12px; color: #9ca3af; pointer-events: none;
	flex-shrink: 0;
}

.tp-bm__prefix {
	position: absolute; left: 0;
	height: 100%; display: flex; align-items: center;
	padding: 0 12px;
	font-size: 13px; font-weight: 600; color: #374151;
	border-right: 1px solid #e5e7eb;
	background: #f9fafb;
	border-radius: 10px 0 0 10px;
	z-index: 1;
}

.tp-bm__input {
	width: 100%; height: 46px;
	border: 1.5px solid #e5e7eb;
	border-radius: 10px;
	padding: 0 14px 0 38px;
	font-size: 14px; color: #111827;
	background: #f9fafb;
	outline: none;
	transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
	font-family: inherit;
}
.tp-bm__input--phone { padding-left: 58px; }

.tp-bm__input::placeholder { color: #9ca3af; }
.tp-bm__input:focus {
	border-color: #1A56DB;
	background: #fff;
	box-shadow: 0 0 0 3px rgba(26,86,219,0.1);
}

.tp-bm__field.is-error .tp-bm__input { border-color: #ef4444; background: #fff5f5; }
.tp-bm__error {
	font-size: 12px; color: #ef4444; margin-top: 4px; min-height: 16px;
}

/* Status */
.tp-bm__status {
	font-size: 13px; min-height: 20px; margin-bottom: 4px;
}
.tp-bm__status.is-error   { color: #ef4444; }
.tp-bm__status.is-success { color: #10B981; }

/* Submit */
.tp-bm__submit {
	width: 100%; height: 50px;
	background: linear-gradient(135deg, #1A56DB 0%, #1432a0 100%);
	color: #fff; border: none; border-radius: 12px;
	font-size: 15px; font-weight: 700; cursor: pointer;
	display: flex; align-items: center; justify-content: center; gap: 8px;
	font-family: inherit;
	transition: opacity 0.15s, transform 0.1s, box-shadow 0.15s;
	box-shadow: 0 4px 16px rgba(26,86,219,0.3);
	margin-bottom: 10px;
}
.tp-bm__submit:hover { opacity: 0.92; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,86,219,0.38); }
.tp-bm__submit:active { transform: translateY(0); }
.tp-bm__submit:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

/* Privacy */
.tp-bm__privacy {
	font-size: 11px; color: #9ca3af;
	display: flex; align-items: center; gap: 5px;
	justify-content: center; margin-bottom: 16px;
}

/* Social Proof */
.tp-bm__social-proof {
	display: flex; align-items: center; gap: 10px;
	padding-top: 14px;
	border-top: 1px solid #f3f4f6;
	margin-top: auto;
}
.tp-bm__avatars {
	display: flex;
}
.tp-bm__avatars span {
	width: 28px; height: 28px; border-radius: 50%;
	border: 2px solid #fff;
	display: flex; align-items: center; justify-content: center;
	font-size: 11px; font-weight: 700; color: #fff;
	margin-left: -8px;
}
.tp-bm__avatars span:first-child { margin-left: 0; }
.tp-bm__proof-text {
	font-size: 12px; color: #6b7280; font-weight: 500;
}

/* Success state */
.tp-bm__success {
	text-align: center; padding: 24px 16px;
	display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.tp-bm__success h4 { font-size: 20px; font-weight: 700; color: #111827; }
.tp-bm__success p { font-size: 14px; color: #6b7280; line-height: 1.6; }
.tp-bm__success-check {
	width: 56px; height: 56px; border-radius: 50%;
	background: #D1FAE5;
	display: flex; align-items: center; justify-content: center;
	color: #10B981; margin-bottom: 4px;
}

/* ── RESPONSIVE ── */
@media (max-width: 640px) {
	.tp-bm__panel { grid-template-columns: 1fr; max-height: 95vh; }
	.tp-bm__left { padding: 28px 24px 24px; }
	.tp-bm__left-title { font-size: 24px; }
	.tp-bm__benefits { gap: 14px; }
	.tp-bm__right { padding: 24px; }
	.tp-bm__close { top: 10px; right: 10px; }
}
</style>


<script>
(function () {
	var modal   = document.getElementById('tp-brochure-modal');
	var form    = modal ? modal.querySelector('.js-brochure-form') : null;
	var btnText = modal ? modal.querySelector('.js-bm-btn-text') : null;
	var submit  = modal ? modal.querySelector('.js-bm-submit') : null;
	var status  = modal ? modal.querySelector('.js-bm-status') : null;
	if (!modal) return;

	/* ── Open ── */
	function openModal() {
		modal.removeAttribute('hidden');
		document.body.style.overflow = 'hidden';
		var first = modal.querySelector('input:not([type="hidden"])');
		if (first) setTimeout(function(){ first.focus(); }, 60);
	}

	/* ── Close ── */
	function closeModal() {
		modal.setAttribute('hidden', '');
		document.body.style.overflow = '';
	}

	/* All "open" triggers on the page */
	document.querySelectorAll('.js-brochure-open').forEach(function(btn){
		btn.addEventListener('click', openModal);
	});

	/* Close triggers */
	modal.querySelectorAll('.js-brochure-close').forEach(function(el){
		el.addEventListener('click', closeModal);
	});

	/* Escape key */
	document.addEventListener('keydown', function(e){
		if (e.key === 'Escape' && !modal.hasAttribute('hidden')) closeModal();
	});

	/* Clear errors on input */
	if (form) {
		form.querySelectorAll('.tp-bm__input').forEach(function(inp){
			inp.addEventListener('input', function(){
				var field = inp.closest('.tp-bm__field');
				if (field) {
					field.classList.remove('is-error');
					var err = field.querySelector('.tp-bm__error');
					if (err) err.textContent = '';
				}
				if (status) { status.textContent = ''; status.className = 'tp-bm__status js-bm-status'; }
			});
		});
	}

	/* ── Submit ── */
	if (form) {
		form.addEventListener('submit', function(e){
			e.preventDefault();

			var nameInput  = form.querySelector('input[name="name"]');
			var phoneInput = form.querySelector('input[name="phone"]');
			var name       = nameInput ? nameInput.value.trim() : '';
			var phone      = phoneInput ? phoneInput.value.replace(/\D/g,'') : '';
			var brochureUrl = form.getAttribute('data-brochure-url');
			var ajaxUrl     = form.getAttribute('data-ajax-url');
			var valid = true;

			/* Validate */
			if (!name) {
				var nf = nameInput.closest('.tp-bm__field');
				nf.classList.add('is-error');
				nf.querySelector('.tp-bm__error').textContent = 'Please enter your name';
				valid = false;
			}
			if (phone.length < 10) {
				var pf = phoneInput.closest('.tp-bm__field');
				pf.classList.add('is-error');
				pf.querySelector('.tp-bm__error').textContent = 'Please enter a valid 10-digit mobile number';
				valid = false;
			}
			if (!valid) return;

			/* Submit */
			submit.disabled = true;
			if (btnText) btnText.textContent = 'Downloading...';
			if (status)  { status.textContent = ''; status.className = 'tp-bm__status js-bm-status'; }

			var data = new FormData(form);
			fetch(ajaxUrl, { method: 'POST', body: data })
				.then(function(r){ return r.json(); })
				.then(function(res){
					/* Open brochure regardless of lead save result */
					window.open(brochureUrl, '_blank');

					/* Show success state */
					var right = form.closest('.tp-bm__right');
					right.innerHTML =
						'<div class="tp-bm__success">' +
						  '<div class="tp-bm__success-check">' +
						    '<svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>' +
						  '</div>' +
						  '<h4>Brochure Opening!</h4>' +
						  '<p>Our expert will also call you shortly<br>with the best available offer.</p>' +
						  '<button class="tp-bm__submit" onclick="document.getElementById(\'tp-brochure-modal\').setAttribute(\'hidden\',\'\');document.body.style.overflow=\'\'" style="max-width:200px;margin-top:8px;">Close</button>' +
						'</div>';
				})
				.catch(function(){
					/* Even on error, open brochure */
					window.open(brochureUrl, '_blank');
					closeModal();
				});
		});
	}
})();
</script>
