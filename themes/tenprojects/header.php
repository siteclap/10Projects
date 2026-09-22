<?php
/**
 * Site Header — Dynamic brand settings from WP Admin.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$site_name  = get_option( 'tp_brand_name', 'LeadMAAXX' );
$logo_light = get_option( 'tp_brand_logo_light', '' );
$logo       = $logo_light ?: get_template_directory_uri() . '/assets/images/logo.png';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="tp-header">
	<div class="tp-header-inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-logo" aria-label="<?php echo esc_attr( $site_name ); ?> home">
			<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" height="48" width="auto" fetchpriority="high" decoding="async">
		</a>

		<nav class="tp-nav">
			<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
			<a href="<?php echo esc_url( home_url( '/careers/' ) ); ?>">Careers</a>
			<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-nav-cta">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
				Search Properties
			</a>
			<button type="button" class="tp-nav-post" id="tp-post-property-btn">
				<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
				Post Property <span class="tp-free-badge">FREE</span>
			</button>
		</nav>

		<button class="tp-hamburger" id="tp-menu-toggle" aria-label="Menu" aria-expanded="false">
			<svg class="tp-icon-menu" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
				<path d="M4 6h16M4 12h16M4 18h16"/>
			</svg>
			<svg class="tp-icon-close" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
				<path d="M18 6 6 18M6 6l12 12"/>
			</svg>
		</button>
	</div>
</header>

<div class="tp-mobile-menu" id="tp-mobile-menu">
	<div class="tp-mobile-menu-inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-mobile-cta">
			<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
			Search Properties
		</a>
		<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
		<a href="<?php echo esc_url( home_url( '/careers/' ) ); ?>">Careers</a>
		<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
		<button type="button" class="tp-mobile-post-btn" id="tp-post-property-btn-mobile">
			<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
			Post Your Property <span class="tp-free-badge">FREE</span>
		</button>
	</div>
</div>

<!-- Post Property Modal -->
<div class="tp-modal-overlay" id="tp-post-modal" style="display:none;">
	<div class="tp-modal">
		<button type="button" class="tp-modal-close" id="tp-modal-close" aria-label="Close">
			<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
		</button>
		<div class="tp-modal-header">
			<div class="tp-modal-icon">
				<svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
			</div>
			<h3>Post Your Property <span class="tp-free-badge" style="font-size:11px;">FREE</span></h3>
			<p>List your property and connect with genuine buyers.</p>
		</div>
		<form id="tp-post-property-form" class="tp-modal-form" novalidate>
			<div class="tp-form-group">
				<label for="tp-pp-name">Full Name</label>
				<input type="text" id="tp-pp-name" name="name" placeholder="Enter your name" required autocomplete="name">
			</div>
			<div class="tp-form-group">
				<label for="tp-pp-phone">Phone Number</label>
				<div class="tp-phone-input">
					<span class="tp-phone-prefix">+91</span>
					<input type="tel" id="tp-pp-phone" name="phone" placeholder="98765 43210" required maxlength="10" pattern="[6-9][0-9]{9}" autocomplete="tel-national">
				</div>
			</div>
			<div class="tp-form-group">
				<label for="tp-pp-location">Property Location</label>
				<input type="text" id="tp-pp-location" name="location" placeholder="e.g. Kharghar, Panvel, Ulwe" required>
			</div>
			<div class="tp-form-group">
				<label>Configuration (BHK)</label>
				<div class="tp-bhk-chips">
					<label class="tp-chip"><input type="radio" name="bhk" value="1 BHK"><span>1 BHK</span></label>
					<label class="tp-chip"><input type="radio" name="bhk" value="2 BHK"><span>2 BHK</span></label>
					<label class="tp-chip"><input type="radio" name="bhk" value="3 BHK" checked><span>3 BHK</span></label>
					<label class="tp-chip"><input type="radio" name="bhk" value="4 BHK"><span>4 BHK</span></label>
					<label class="tp-chip"><input type="radio" name="bhk" value="5+ BHK"><span>5+ BHK</span></label>
					<label class="tp-chip"><input type="radio" name="bhk" value="Plot"><span>Plot</span></label>
					<label class="tp-chip"><input type="radio" name="bhk" value="Commercial"><span>Commercial</span></label>
				</div>
			</div>
			<!-- Honeypot -->
			<input type="text" name="honeypot" style="display:none;" tabindex="-1" autocomplete="off">
			<button type="submit" class="tp-modal-submit" id="tp-pp-submit">
				Submit Property Details
			</button>
			<p class="tp-modal-trust">Your details are safe. We never share without your consent.</p>
		</form>
		<div class="tp-modal-success" id="tp-pp-success" style="display:none;">
			<div class="tp-success-icon">
				<svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
			</div>
			<h3>Property Listed!</h3>
			<p>Our team will review your listing and get back to you within 24 hours.</p>
		</div>
	</div>
</div>

<script>
(function(){
	/* Mobile menu toggle */
	var btn = document.getElementById('tp-menu-toggle');
	var menu = document.getElementById('tp-mobile-menu');
	if(btn&&menu){
		btn.addEventListener('click', function(){
			var open = menu.classList.toggle('active');
			btn.classList.toggle('active', open);
			btn.setAttribute('aria-expanded', open);
			document.body.style.overflow = open ? 'hidden' : '';
		});
		menu.querySelectorAll('a').forEach(function(a){
			a.addEventListener('click', function(){
				menu.classList.remove('active');
				btn.classList.remove('active');
				btn.setAttribute('aria-expanded','false');
				document.body.style.overflow = '';
			});
		});
	}

	/* Post Property Modal */
	var modal = document.getElementById('tp-post-modal');
	var closeBtn = document.getElementById('tp-modal-close');
	var form = document.getElementById('tp-post-property-form');
	var successEl = document.getElementById('tp-pp-success');
	if(!modal) return;

	function openModal(){
		modal.style.display = 'flex';
		document.body.style.overflow = 'hidden';
		/* Close mobile menu if open */
		if(menu && menu.classList.contains('active')){
			menu.classList.remove('active');
			if(btn){ btn.classList.remove('active'); btn.setAttribute('aria-expanded','false'); }
		}
	}
	function closeModal(){
		modal.style.display = 'none';
		document.body.style.overflow = '';
	}

	/* Open triggers */
	document.querySelectorAll('#tp-post-property-btn, #tp-post-property-btn-mobile').forEach(function(b){
		b.addEventListener('click', openModal);
	});

	/* Close triggers */
	if(closeBtn) closeBtn.addEventListener('click', closeModal);
	modal.addEventListener('click', function(e){ if(e.target === modal) closeModal(); });
	document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && modal.style.display === 'flex') closeModal(); });

	/* Form submit */
	if(form) form.addEventListener('submit', function(e){
		e.preventDefault();
		var submitBtn = document.getElementById('tp-pp-submit');
		var name = document.getElementById('tp-pp-name').value.trim();
		var phone = document.getElementById('tp-pp-phone').value.replace(/\D/g,'');
		var location = document.getElementById('tp-pp-location').value.trim();
		var bhkEl = form.querySelector('input[name="bhk"]:checked');
		var bhk = bhkEl ? bhkEl.value : '';
		var honeypot = form.querySelector('input[name="honeypot"]').value;

		/* Validate */
		if(!name || name.length < 2){ alert('Please enter your name.'); return; }
		if(phone.length !== 10 || !/^[6-9]/.test(phone)){ alert('Please enter a valid 10-digit phone number.'); return; }
		if(!location){ alert('Please enter the property location.'); return; }

		submitBtn.disabled = true;
		submitBtn.textContent = 'Submitting...';

		fetch('<?php echo esc_url( home_url( '/wp-json/tenprojects/v1/public/leads' ) ); ?>', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
			body: JSON.stringify({
				name: name,
				phone: phone,
				lead_type: 'post_property',
				location: location,
				bhk: bhk,
				honeypot: honeypot,
				source_page: window.location.href
			})
		})
		.then(function(r){ return r.json(); })
		.then(function(data){
			form.style.display = 'none';
			successEl.style.display = 'block';
			setTimeout(function(){ closeModal(); form.style.display = ''; successEl.style.display = 'none'; form.reset(); submitBtn.disabled = false; submitBtn.textContent = 'Submit Property Details'; }, 4000);
		})
		.catch(function(){
			alert('Something went wrong. Please try again.');
			submitBtn.disabled = false;
			submitBtn.textContent = 'Submit Property Details';
		});
	});
})();
</script>
