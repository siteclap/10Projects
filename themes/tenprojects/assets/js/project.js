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
})();
