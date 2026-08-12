/**
 * 10Projects — Project Detail Page JS
 * Lightbox, Mobile Gallery Swipe, Amenity Toggle
 */

(function () {
	'use strict';

	/* ─── Lightbox ────────────────────────────────────────── */

	let currentIndex = 0;
	const images = window.galleryImages || [];

	window.openLightbox = function (index) {
		currentIndex = index;
		const lb = document.getElementById('lightbox');
		const img = document.getElementById('lightboxImg');
		const counter = document.getElementById('lightboxIndex');
		if (!lb || !img) return;
		img.src = images[currentIndex];
		counter.textContent = currentIndex + 1;
		lb.classList.add('active');
		document.body.style.overflow = 'hidden';
	};

	window.closeLightbox = function () {
		const lb = document.getElementById('lightbox');
		if (!lb) return;
		lb.classList.remove('active');
		document.body.style.overflow = '';
	};

	window.navLightbox = function (dir) {
		currentIndex = (currentIndex + dir + images.length) % images.length;
		const img = document.getElementById('lightboxImg');
		const counter = document.getElementById('lightboxIndex');
		if (img) img.src = images[currentIndex];
		if (counter) counter.textContent = currentIndex + 1;
	};

	// Close lightbox on Escape key.
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') window.closeLightbox();
		if (e.key === 'ArrowLeft') window.navLightbox(-1);
		if (e.key === 'ArrowRight') window.navLightbox(1);
	});

	// Close lightbox on background click.
	const lb = document.getElementById('lightbox');
	if (lb) {
		lb.addEventListener('click', function (e) {
			if (e.target === lb) window.closeLightbox();
		});
	}

	/* ─── Mobile Gallery Swipe ────────────────────────────── */

	const track = document.getElementById('galleryTrack');
	const indexEl = document.getElementById('galleryIndex');

	if (track && images.length > 0) {
		let startX = 0;
		let currentSlide = 0;
		const totalSlides = images.length;

		function goToSlide(n) {
			currentSlide = Math.max(0, Math.min(n, totalSlides - 1));
			track.style.transform = 'translateX(-' + (currentSlide * 100) + '%)';
			if (indexEl) indexEl.textContent = currentSlide + 1;
		}

		track.addEventListener('touchstart', function (e) {
			startX = e.touches[0].clientX;
		}, { passive: true });

		track.addEventListener('touchend', function (e) {
			const diff = startX - e.changedTouches[0].clientX;
			if (Math.abs(diff) > 50) {
				goToSlide(currentSlide + (diff > 0 ? 1 : -1));
			}
		}, { passive: true });
	}

	/* ─── Amenity Toggle ──────────────────────────────────── */

	window.toggleAmenities = function (btn) {
		const hidden = document.querySelectorAll('[data-hidden-amenity]');
		const showing = !hidden[0]?.classList.contains('hidden');

		hidden.forEach(function (el) {
			el.classList.toggle('hidden');
		});

		btn.innerHTML = showing
			? 'See All ' + (document.querySelectorAll('.tp-amenity-pill').length) + ' Amenities <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>'
			: 'Show Less <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 15l7-7 7 7"/></svg>';
	};

	/* ─── Section Nav — Sticky + Active Tracking ─────────── */

	const sectionNav = document.getElementById('sectionNav');
	if (sectionNav) {
		const navLinks = sectionNav.querySelectorAll('.tp-section-nav__link');
		const sectionIds = Array.from(navLinks).map(function (l) { return l.dataset.section; });
		const headerHeight = 64;

		// Make nav sticky on scroll past its natural position.
		const navTop = sectionNav.offsetTop;

		function updateNav() {
			// Sticky behavior.
			if (window.scrollY > navTop - headerHeight) {
				sectionNav.classList.add('is-stuck');
			} else {
				sectionNav.classList.remove('is-stuck');
			}

			// Active link tracking.
			let activeId = sectionIds[0];
			for (let i = sectionIds.length - 1; i >= 0; i--) {
				const el = document.getElementById(sectionIds[i]);
				if (el && el.getBoundingClientRect().top <= headerHeight + 60) {
					activeId = sectionIds[i];
					break;
				}
			}

			navLinks.forEach(function (l) {
				l.classList.toggle('active', l.dataset.section === activeId);
			});
		}

		window.addEventListener('scroll', updateNav, { passive: true });
		updateNav();

		// Smooth scroll on click.
		navLinks.forEach(function (link) {
			link.addEventListener('click', function (e) {
				e.preventDefault();
				const target = document.getElementById(link.dataset.section);
				if (target) {
					const y = target.getBoundingClientRect().top + window.scrollY - headerHeight - 50;
					window.scrollTo({ top: y, behavior: 'smooth' });
				}
			});
		});
	}
	/* ─── Interactive EMI Calculator ─────────────────────── */

	var calc = document.getElementById('tp-calc');
	if (calc) {
		var propSlider   = document.getElementById('tc-prop');
		var dpSlider     = document.getElementById('tc-dp');
		var rateSlider   = document.getElementById('tc-rate');
		var tenureSlider = document.getElementById('tc-tenure');

		var propInput    = document.getElementById('tc-prop-val');
		var dpInput      = document.getElementById('tc-dp-val');
		var rateInput    = document.getElementById('tc-rate-val');
		var tenureInput  = document.getElementById('tc-tenure-val');

		var emiEl      = document.getElementById('tc-emi');
		var loanEl     = document.getElementById('tc-loan');
		var interestEl = document.getElementById('tc-interest');
		var totalEl    = document.getElementById('tc-total');

		function formatINR(num) {
			// Indian numbering: 12,34,567
			var s = Math.round(num).toString();
			var lastThree = s.slice(-3);
			var rest = s.slice(0, -3);
			if (rest.length > 0) {
				lastThree = ',' + lastThree;
			}
			return '₹' + rest.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree;
		}

		function formatShort(num) {
			if (num >= 10000000) return '₹' + (num / 10000000).toFixed(2) + ' Cr';
			if (num >= 100000) return '₹' + (num / 100000).toFixed(1) + ' L';
			return formatINR(num);
		}

		function updateCalc() {
			var propVal = parseFloat(propSlider.value);
			var dpPct   = parseFloat(dpSlider.value);
			var rate    = parseFloat(rateSlider.value);
			var years   = parseInt(tenureSlider.value);

			var loanAmt = propVal * (1 - dpPct / 100);
			var r = (rate / 100) / 12;
			var n = years * 12;
			var emi = 0;

			if (r > 0 && loanAmt > 0) {
				emi = loanAmt * r * Math.pow(1 + r, n) / (Math.pow(1 + r, n) - 1);
			}

			var totalPay  = emi * n;
			var totalInt  = totalPay - loanAmt;

			// Update displays
			propInput.value   = formatShort(propVal).replace('₹', '');
			dpInput.value     = dpPct;
			rateInput.value   = rate.toFixed(1);
			tenureInput.value = years;

			emiEl.textContent      = formatINR(emi) + '/mo';
			loanEl.textContent     = formatShort(loanAmt);
			interestEl.textContent = formatShort(totalInt > 0 ? totalInt : 0);
			totalEl.textContent    = formatShort(totalPay > 0 ? totalPay : 0);

			// Update slider track fill
			updateTrackFill(propSlider);
			updateTrackFill(dpSlider);
			updateTrackFill(rateSlider);
			updateTrackFill(tenureSlider);
		}

		function updateTrackFill(slider) {
			var pct = ((slider.value - slider.min) / (slider.max - slider.min)) * 100;
			slider.style.background = 'linear-gradient(to right, var(--brand-primary) 0%, var(--brand-primary) ' + pct + '%, var(--gray-200) ' + pct + '%, var(--gray-200) 100%)';
		}

		// Slider events
		[propSlider, dpSlider, rateSlider, tenureSlider].forEach(function (s) {
			s.addEventListener('input', updateCalc);
		});

		// Input field → slider sync
		propInput.addEventListener('change', function () {
			var v = parseFloat(this.value.replace(/[^\d.]/g, ''));
			// Detect if user typed in Lakhs or Crores
			if (this.value.toLowerCase().indexOf('cr') > -1) v = v * 10000000;
			else if (this.value.toLowerCase().indexOf('l') > -1) v = v * 100000;
			else if (v < 1000) v = v * 100000; // Assume Lakhs if small number
			if (v >= 1000000 && v <= 100000000) {
				propSlider.value = v;
				updateCalc();
			}
		});

		dpInput.addEventListener('change', function () {
			var v = parseFloat(this.value);
			if (v >= 5 && v <= 75) {
				dpSlider.value = v;
				updateCalc();
			}
		});

		rateInput.addEventListener('change', function () {
			var v = parseFloat(this.value);
			if (v >= 5 && v <= 15) {
				rateSlider.value = v;
				updateCalc();
			}
		});

		tenureInput.addEventListener('change', function () {
			var v = parseInt(this.value);
			if (v >= 5 && v <= 30) {
				tenureSlider.value = v;
				updateCalc();
			}
		});

		// Initial calculation
		updateCalc();
	}
})();
