/**
 * 10Projects — Client-side analytics tracking.
 *
 * Sends events to POST /tenprojects/v1/analytics/track.
 * Deferred loading — does not block page render.
 *
 * Tracks:
 *  - page_view (auto on load)
 *  - assessment_started, project_clicked, share_clicked, etc. via event delegation
 *  - scroll_depth at 25%, 50%, 75%, 100% thresholds
 *  - UTM parameters captured on first visit and stored in a cookie
 *  - session duration on unload
 *
 * @package TenProjects
 * @since   1.0.0
 */
(function () {
	'use strict';

	/* -----------------------------------------------------------------------
	 * Configuration
	 * ----------------------------------------------------------------------- */

	var API_URL = (window.tenprojectsData && window.tenprojectsData.restUrl)
		? window.tenprojectsData.restUrl + 'analytics/track'
		: '/wp-json/tenprojects/v1/analytics/track';

	var NONCE = (window.tenprojectsData && window.tenprojectsData.nonce) || '';

	/* Session ID — persisted per tab session via sessionStorage. */
	var SESSION_KEY = 'tp_session_id';
	var SESSION_ID = (function () {
		var id = sessionStorage.getItem(SESSION_KEY);
		if (!id) {
			id = generateUUID();
			sessionStorage.setItem(SESSION_KEY, id);
		}
		return id;
	})();

	/* Page load time for session duration. */
	var PAGE_LOAD_TIME = Date.now();

	/* Scroll depth tracking state. */
	var scrollThresholds = { 25: false, 50: false, 75: false, 100: false };
	var scrollDebounceTimer = null;

	/* -----------------------------------------------------------------------
	 * UTM Capture
	 * ----------------------------------------------------------------------- */

	(function captureUTM() {
		var params = new URLSearchParams(window.location.search);
		var utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
		var hasUTM = false;
		var utmData = {};

		utmKeys.forEach(function (key) {
			var val = params.get(key);
			if (val) {
				utmData[key] = val;
				hasUTM = true;
			}
		});

		if (hasUTM) {
			// Store in a cookie that lasts 30 days.
			var expires = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString();
			document.cookie = 'tp_utm=' + encodeURIComponent(JSON.stringify(utmData)) +
				';expires=' + expires + ';path=/;SameSite=Lax';
		}
	})();

	/**
	 * Read stored UTM data from cookie.
	 *
	 * @returns {Object} UTM parameters or empty object.
	 */
	function getStoredUTM() {
		var match = document.cookie.match(/tp_utm=([^;]+)/);
		if (match) {
			try {
				return JSON.parse(decodeURIComponent(match[1]));
			} catch (e) {
				return {};
			}
		}
		return {};
	}

	/* -----------------------------------------------------------------------
	 * Page Type Detection
	 * ----------------------------------------------------------------------- */

	/**
	 * Detect the current page type from body classes or URL.
	 *
	 * @returns {string} Page type identifier.
	 */
	function detectPageType() {
		var body = document.body;
		if (!body) return 'unknown';

		var classes = body.className;

		if (classes.indexOf('home') !== -1) return 'homepage';
		if (classes.indexOf('single-tp_project') !== -1) return 'project';
		if (classes.indexOf('single-tp_developer') !== -1) return 'developer';
		if (classes.indexOf('single-tp_location') !== -1) return 'location';
		if (classes.indexOf('single-tp_guide') !== -1) return 'guide';
		if (classes.indexOf('post-type-archive-tp_project') !== -1) return 'project_archive';
		if (classes.indexOf('post-type-archive-tp_location') !== -1) return 'location_archive';

		// Check for specific page slugs.
		if (classes.indexOf('page-template-page-start') !== -1 || window.location.pathname.indexOf('/start') !== -1) return 'assessment';
		if (classes.indexOf('page-template-page-results') !== -1 || window.location.pathname.indexOf('/results') !== -1) return 'results';
		if (classes.indexOf('page-template-page-compare') !== -1 || window.location.pathname.indexOf('/compare') !== -1) return 'compare';
		if (classes.indexOf('page-template-page-dashboard') !== -1 || window.location.pathname.indexOf('/dashboard') !== -1) return 'dashboard';
		if (window.location.pathname.indexOf('/methodology') !== -1) return 'methodology';

		if (classes.indexOf('page') !== -1) return 'page';
		if (classes.indexOf('archive') !== -1) return 'archive';

		return 'unknown';
	}

	/* -----------------------------------------------------------------------
	 * Core: Send Event
	 * ----------------------------------------------------------------------- */

	/**
	 * Send an analytics event to the REST API.
	 *
	 * Uses navigator.sendBeacon when available for reliability on page unload,
	 * otherwise falls back to fetch.
	 *
	 * @param {string} eventName  Event name.
	 * @param {Object} properties Additional properties.
	 * @param {boolean} useBeacon Force sendBeacon (for unload events).
	 */
	function trackEvent(eventName, properties, useBeacon) {
		var utm = getStoredUTM();
		var payload = {
			event_name: eventName,
			properties: Object.assign({}, properties || {}, utm),
			page_url: window.location.href,
			referrer: document.referrer || '',
			session_id: SESSION_ID,
		};

		var body = JSON.stringify(payload);

		if (useBeacon && navigator.sendBeacon) {
			var blob = new Blob([body], { type: 'application/json' });
			navigator.sendBeacon(API_URL, blob);
			return;
		}

		// Use fetch with keepalive for page unload scenarios.
		var headers = {
			'Content-Type': 'application/json',
		};
		if (NONCE) {
			headers['X-WP-Nonce'] = NONCE;
		}

		fetch(API_URL, {
			method: 'POST',
			headers: headers,
			body: body,
			keepalive: true,
			credentials: 'same-origin',
		}).catch(function () {
			// Silently fail — analytics should never break the user experience.
		});
	}

	/* -----------------------------------------------------------------------
	 * Auto-Track: Page View
	 * ----------------------------------------------------------------------- */

	trackEvent('page_view', {
		page_type: detectPageType(),
		page_title: document.title,
	});

	/* -----------------------------------------------------------------------
	 * Event Delegation: Click-Based Events
	 * ----------------------------------------------------------------------- */

	document.addEventListener('click', function (e) {
		var target = e.target.closest('[data-tp-track]');

		// Explicit data-tp-track attribute.
		if (target) {
			var eventName = target.getAttribute('data-tp-track');
			var eventData = {};

			try {
				var raw = target.getAttribute('data-tp-track-props');
				if (raw) eventData = JSON.parse(raw);
			} catch (err) {
				// Ignore parse errors.
			}

			trackEvent(eventName, eventData);
			return;
		}

		// Assessment CTA clicks.
		if (e.target.closest('a[href="/start"], a[href*="/start"]')) {
			trackEvent('cta_clicked', { cta_type: 'start_assessment', cta_text: e.target.textContent.trim() });
			return;
		}

		// Project card clicks.
		var projectCard = e.target.closest('.project-card a, .project-card__image');
		if (projectCard) {
			var card = e.target.closest('.project-card');
			var projectId = card ? card.getAttribute('data-project-id') : '';
			trackEvent('project_clicked', {
				project_id: projectId,
				source: detectPageType(),
			});
			return;
		}

		// Save/unsave project.
		var saveBtn = e.target.closest('.project-card__save, [data-action="save-project"]');
		if (saveBtn) {
			var isSaved = saveBtn.classList.contains('project-card__save--active');
			trackEvent(isSaved ? 'project_unsaved' : 'project_saved', {
				project_id: saveBtn.getAttribute('data-project-id') || '',
			});
			return;
		}

		// Share buttons.
		var shareBtn = e.target.closest('.guide-share-btn, [data-action="share"], #tp-compare-share');
		if (shareBtn) {
			trackEvent('share_clicked', {
				platform: shareBtn.getAttribute('data-platform') || 'unknown',
				page_type: detectPageType(),
			});
			return;
		}

		// Callback / WhatsApp.
		if (e.target.closest('#tp-advisor-callback, #tp-mobile-callback, [data-action="callback"]')) {
			trackEvent('callback_requested', { source: detectPageType() });
			return;
		}
		if (e.target.closest('#tp-advisor-whatsapp, #tp-mobile-whatsapp, .btn--whatsapp')) {
			trackEvent('whatsapp_clicked', { source: detectPageType() });
			return;
		}

		// Site visit scheduling.
		if (e.target.closest('#tp-schedule-visit, [data-action="schedule-visit"]')) {
			trackEvent('site_visit_scheduled', { source: detectPageType() });
			return;
		}

		// Location card clicks.
		var locationCard = e.target.closest('.location-card a');
		if (locationCard) {
			trackEvent('location_clicked', {
				location_name: locationCard.textContent.trim(),
			});
			return;
		}

		// Developer link clicks.
		var devLink = e.target.closest('.developer-profile a, .developer-card a');
		if (devLink) {
			trackEvent('developer_clicked', {
				developer_name: devLink.textContent.trim(),
			});
			return;
		}
	}, true);

	/* -----------------------------------------------------------------------
	 * Scroll Depth Tracking (debounced)
	 * ----------------------------------------------------------------------- */

	function onScroll() {
		if (scrollDebounceTimer) return;

		scrollDebounceTimer = setTimeout(function () {
			scrollDebounceTimer = null;

			var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
			var docHeight = Math.max(
				document.body.scrollHeight,
				document.documentElement.scrollHeight
			);
			var winHeight = window.innerHeight;
			var scrollPercent = Math.round((scrollTop / (docHeight - winHeight)) * 100);

			var thresholds = [25, 50, 75, 100];
			for (var i = 0; i < thresholds.length; i++) {
				var t = thresholds[i];
				if (scrollPercent >= t && !scrollThresholds[t]) {
					scrollThresholds[t] = true;
					trackEvent('scroll_depth', {
						depth: t,
						page_type: detectPageType(),
					});
				}
			}
		}, 250);
	}

	window.addEventListener('scroll', onScroll, { passive: true });

	/* -----------------------------------------------------------------------
	 * Session Duration (on unload)
	 * ----------------------------------------------------------------------- */

	function onUnload() {
		var duration = Math.round((Date.now() - PAGE_LOAD_TIME) / 1000);
		// Only track if user stayed more than 1 second (filter bots/bounces).
		if (duration > 1) {
			trackEvent('session_duration', {
				duration_seconds: duration,
				page_type: detectPageType(),
			}, true);
		}
	}

	// Use both events for maximum browser coverage.
	window.addEventListener('pagehide', onUnload);
	window.addEventListener('beforeunload', onUnload);

	/* -----------------------------------------------------------------------
	 * Public API
	 * ----------------------------------------------------------------------- */

	/**
	 * Expose a global function for custom event tracking from other scripts.
	 *
	 * Usage: window.tp_track('custom_event', { key: 'value' });
	 */
	window.tp_track = trackEvent;

	/* -----------------------------------------------------------------------
	 * Helpers
	 * ----------------------------------------------------------------------- */

	/**
	 * Generate a UUID v4 string.
	 *
	 * @returns {string}
	 */
	function generateUUID() {
		if (window.crypto && window.crypto.randomUUID) {
			return window.crypto.randomUUID();
		}
		// Fallback for older browsers.
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
			var r = (Math.random() * 16) | 0;
			var v = c === 'x' ? r : (r & 0x3) | 0x8;
			return v.toString(16);
		});
	}
})();
