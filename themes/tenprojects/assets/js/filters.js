/**
 * 10Projects — Filter Bar v2.
 *
 * Dropdown toggles, dual-range budget slider,
 * URL-based filter navigation, chip removal.
 */
(function () {
	'use strict';

	var bar = document.getElementById('tp-filter-bar');
	if (!bar) return;

	/* ────────────────────────────────────────────
	 * 1. Dropdown toggle
	 * ──────────────────────────────────────────── */

	var drops = bar.querySelectorAll('.tp-fd');

	function closeAll() {
		drops.forEach(function (d) { d.classList.remove('is-open'); });
	}

	drops.forEach(function (drop) {
		var btn = drop.querySelector('.tp-fd__btn');
		if (!btn) return;

		btn.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var wasOpen = drop.classList.contains('is-open');
			closeAll();
			if (!wasOpen) {
				drop.classList.add('is-open');
			}
		});
	});

	// Close on click outside.
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.tp-fd')) {
			closeAll();
		}
	});

	// Close on Escape.
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') closeAll();
	});

	// Prevent panel clicks from closing.
	bar.querySelectorAll('.tp-fd__panel').forEach(function (panel) {
		panel.addEventListener('click', function (e) {
			e.stopPropagation();
		});
	});

	/* ────────────────────────────────────────────
	 * 2. Budget range slider
	 * ──────────────────────────────────────────── */

	var rangeMin = document.getElementById('tp-range-min');
	var rangeMax = document.getElementById('tp-range-max');
	var rangeFill = document.getElementById('tp-range-fill');
	var valMin = document.getElementById('tp-range-val-min');
	var valMax = document.getElementById('tp-range-val-max');

	function formatINR(amount) {
		amount = parseInt(amount, 10);
		if (amount <= 0) return '\u20B90';
		if (amount >= 10000000) {
			var cr = amount / 10000000;
			return '\u20B9' + (cr % 1 === 0 ? cr.toFixed(0) : cr.toFixed(1)) + ' Cr';
		}
		if (amount >= 100000) {
			var l = amount / 100000;
			return '\u20B9' + (l % 1 === 0 ? l.toFixed(0) : l.toFixed(1)) + 'L';
		}
		return '\u20B9' + amount.toLocaleString('en-IN');
	}

	function updateSlider() {
		if (!rangeMin || !rangeMax) return;
		var min = parseInt(rangeMin.value, 10);
		var max = parseInt(rangeMax.value, 10);

		// Prevent crossover.
		if (min > max) {
			var tmp = min; min = max; max = tmp;
			rangeMin.value = min;
			rangeMax.value = max;
		}

		// Update fill bar.
		var total = parseInt(rangeMax.max, 10);
		var leftPct = (min / total) * 100;
		var rightPct = (max / total) * 100;
		if (rangeFill) {
			rangeFill.style.left = leftPct + '%';
			rangeFill.style.width = (rightPct - leftPct) + '%';
		}

		// Update labels.
		if (valMin) valMin.textContent = formatINR(min);
		if (valMax) valMax.textContent = formatINR(max);
	}

	if (rangeMin && rangeMax) {
		rangeMin.addEventListener('input', updateSlider);
		rangeMax.addEventListener('input', updateSlider);
		updateSlider(); // Initial paint.
	}

	/* ────────────────────────────────────────────
	 * 3. Apply — build URL and navigate
	 * ──────────────────────────────────────────── */

	function buildUrl() {
		var params = new URLSearchParams(window.location.search);

		// BHK (checkboxes).
		var bhks = [];
		bar.querySelectorAll('input[name="_bhk[]"]:checked').forEach(function (c) {
			bhks.push(c.value);
		});
		if (bhks.length) params.set('bhk', bhks.join(','));
		else params.delete('bhk');

		// Status (radio).
		var status = bar.querySelector('input[name="_status"]:checked');
		if (status && status.value) params.set('status', status.value);
		else params.delete('status');

		// Budget (range slider).
		if (rangeMin && rangeMax) {
			var bMin = parseInt(rangeMin.value, 10);
			var bMax = parseInt(rangeMax.value, 10);
			if (bMin > 0) params.set('budget_min', bMin);
			else params.delete('budget_min');
			if (bMax < 75000000) params.set('budget_max', bMax);
			else params.delete('budget_max');
		}

		// Sort (radio).
		var sort = bar.querySelector('input[name="_sort"]:checked');
		if (sort && sort.value) params.set('sort', sort.value);
		else params.delete('sort');

		// RERA (checkbox).
		var rera = bar.querySelector('input[name="_rera"]');
		if (rera && rera.checked) params.set('rera', '1');
		else params.delete('rera');

		// Remove old budget param if present.
		params.delete('budget');

		// Clean empty values.
		var toDel = [];
		params.forEach(function (v, k) { if (!v) toDel.push(k); });
		toDel.forEach(function (k) { params.delete(k); });

		var qs = params.toString();
		window.location.href = window.location.pathname + (qs ? '?' + qs : '');
	}

	bar.querySelectorAll('.tp-fd__apply').forEach(function (btn) {
		btn.addEventListener('click', function () {
			buildUrl();
		});
	});

	/* ────────────────────────────────────────────
	 * 4. RERA toggle auto-applies
	 * ──────────────────────────────────────────── */

	var reraToggle = bar.querySelector('input[name="_rera"]');
	if (reraToggle) {
		reraToggle.addEventListener('change', function () {
			buildUrl();
		});
	}

	/* ────────────────────────────────────────────
	 * 5. BHK chip visual toggle
	 * ──────────────────────────────────────────── */

	bar.querySelectorAll('.tp-fd__chip input[type="checkbox"]').forEach(function (cb) {
		cb.addEventListener('change', function () {
			cb.closest('.tp-fd__chip').classList.toggle('is-on', cb.checked);
		});
	});

	/* ────────────────────────────────────────────
	 * 6. Reset buttons inside panels
	 * ──────────────────────────────────────────── */

	bar.querySelectorAll('.tp-fd__reset').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var filter = btn.dataset.clear;
			var panel = btn.closest('.tp-fd__panel');

			if (filter === 'bhk') {
				panel.querySelectorAll('input[type="checkbox"]').forEach(function (c) { c.checked = false; });
				panel.querySelectorAll('.tp-fd__chip').forEach(function (c) { c.classList.remove('is-on'); });
			} else if (filter === 'budget') {
				if (rangeMin) rangeMin.value = 0;
				if (rangeMax) rangeMax.value = 75000000;
				updateSlider();
			} else {
				var first = panel.querySelector('input[type="radio"][value=""]');
				if (first) {
					first.checked = true;
					panel.querySelectorAll('.tp-fd__opt').forEach(function (o) { o.classList.remove('is-on'); });
					first.closest('.tp-fd__opt').classList.add('is-on');
				}
			}
		});
	});

	/* ────────────────────────────────────────────
	 * 7. Radio visual toggle
	 * ──────────────────────────────────────────── */

	bar.querySelectorAll('.tp-fd__opt input[type="radio"]').forEach(function (radio) {
		radio.addEventListener('change', function () {
			var parent = radio.closest('.tp-fd__body');
			parent.querySelectorAll('.tp-fd__opt').forEach(function (o) { o.classList.remove('is-on'); });
			radio.closest('.tp-fd__opt').classList.add('is-on');
		});
	});

	/* ────────────────────────────────────────────
	 * 8. Remove All Filters
	 * ──────────────────────────────────────────── */

	var clearAll = document.getElementById('tp-clear-all');
	if (clearAll) {
		clearAll.addEventListener('click', function () {
			var params = new URLSearchParams(window.location.search);
			['budget', 'budget_min', 'budget_max', 'bhk', 'status', 'sort', 'rera'].forEach(function (k) {
				params.delete(k);
			});
			var qs = params.toString();
			window.location.href = window.location.pathname + (qs ? '?' + qs : '');
		});
	}

})();
