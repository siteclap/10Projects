/**
 * 10Projects — Nearby Projects (Geolocation).
 *
 * Detects user location via browser API, finds nearest
 * Navi Mumbai location, renders project cards on homepage.
 */
(function () {
	'use strict';

	var section = document.getElementById('tp-nearby');
	if (!section) return;

	var grid      = document.getElementById('tp-nearby-grid');
	var locSpan   = document.getElementById('tp-nearby-location');
	var seeAll    = document.getElementById('tp-nearby-link');
	var CACHE_KEY = 'tp_nearby';
	var CACHE_TTL = 30 * 60 * 1000; // 30 minutes.

	/**
	 * Show the section with a fade-in.
	 */
	function showSection() {
		section.style.display = '';
		section.style.opacity = '0';
		section.offsetHeight; // Force reflow.
		section.style.transition = 'opacity 0.4s ease';
		section.style.opacity = '1';
	}

	/**
	 * Render project cards into the grid.
	 */
	function renderCards(data) {
		locSpan.textContent = data.location_name;
		seeAll.href = data.location_url;

		var html = '';
		data.projects.forEach(function (p) {
			var imgHtml = p.thumb
				? '<img src="' + p.thumb + '" alt="' + escHtml(p.title) + '" loading="lazy">'
				: '<div class="tp-card__img-placeholder"><svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div>';

			var devBadge = p.developer
				? '<span class="tp-card__badge-dev">' + escHtml(p.developer) + '</span>'
				: '';

			var stageBadge = p.stage
				? '<span class="tp-card__badge-stage' + (p.stage === 'Ready to Move' ? ' tp-card__badge-stage--ready' : '') + '">' + escHtml(p.stage) + '</span>'
				: '';

			var configHtml = p.configs
				? '<div class="tp-card__config">' + escHtml(p.configs) + '</div>'
				: '';

			html += '<a href="' + p.url + '" class="tp-card">'
				+ '<div class="tp-card__img">' + imgHtml + devBadge + stageBadge + '</div>'
				+ '<div class="tp-card__body">'
				+   '<div class="tp-card__title">' + escHtml(p.title) + '</div>'
				+   '<div class="tp-card__location">'
				+     '<svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
				+     escHtml(data.location_name) + ', Navi Mumbai'
				+   '</div>'
				+   '<div class="tp-card__footer">'
				+     '<div class="tp-card__price">' + escHtml(p.price) + '</div>'
				+     configHtml
				+   '</div>'
				+ '</div>'
				+ '</a>';
		});

		grid.innerHTML = html;
		showSection();
	}

	/**
	 * Escape HTML entities.
	 */
	function escHtml(str) {
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
	}

	/**
	 * Fetch nearby projects via AJAX.
	 */
	function fetchNearby(lat, lng) {
		var fd = new FormData();
		fd.append('action', 'tp_nearby');
		fd.append('lat', lat);
		fd.append('lng', lng);

		fetch(tpNearby.ajax_url, { method: 'POST', body: fd })
			.then(function (res) { return res.json(); })
			.then(function (data) {
				if (data.found && data.projects && data.projects.length) {
					renderCards(data);
					// Cache the result.
					try {
						sessionStorage.setItem(CACHE_KEY, JSON.stringify({
							ts: Date.now(),
							data: data
						}));
					} catch (e) {}
				}
			})
			.catch(function () {
				// Silently fail — section stays hidden.
			});
	}

	/**
	 * Check cache first, then use geolocation.
	 */
	function init() {
		// Check sessionStorage cache.
		try {
			var cached = sessionStorage.getItem(CACHE_KEY);
			if (cached) {
				var parsed = JSON.parse(cached);
				if (parsed.ts && (Date.now() - parsed.ts) < CACHE_TTL && parsed.data && parsed.data.found) {
					renderCards(parsed.data);
					return;
				}
			}
		} catch (e) {}

		// Request geolocation.
		if (!navigator.geolocation) return;

		navigator.geolocation.getCurrentPosition(
			function (pos) {
				fetchNearby(pos.coords.latitude, pos.coords.longitude);
			},
			function () {
				// Permission denied or error — section stays hidden.
			},
			{ timeout: 8000, maximumAge: 300000 }
		);
	}

	init();
})();
