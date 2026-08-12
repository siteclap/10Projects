/**
 * Chatbot Widget — Conversational Lead Capture
 * 6 flows: Pricing, Amenities, Directions, Site Visit, Online Presentation, Callback
 * Feels like live chat with typing dots and message animations.
 */
(function () {
	'use strict';

	var trigger  = document.getElementById('tp-cb-trigger');
	var panel    = document.getElementById('tp-cb-panel');
	var msgs     = document.getElementById('tp-cb-messages');
	var openBtn  = document.getElementById('tp-cb-open');
	var closeBtn = document.getElementById('tp-cb-close');
	if (!trigger || !panel) return;

	var answers = {};
	var isOpen = false;
	var started = false;
	var typingEl = null;

	// Project data from PHP
	var projectTitle = tpChatbot.page_title || '';
	var configs = tpChatbot.configs || []; // e.g. ['1 BHK', '2 BHK', '3 BHK']
	var isProjectPage = !!projectTitle;

	// ── Helpers ──

	function esc(s) {
		var d = document.createElement('div');
		d.textContent = s;
		return d.innerHTML;
	}

	function scroll() {
		setTimeout(function () { msgs.scrollTop = msgs.scrollHeight; }, 60);
	}

	function addBot(text, isHtml) {
		var d = document.createElement('div');
		d.className = 'tp-cb-msg tp-cb-msg--bot tp-cb-msg--in';
		d.innerHTML = '<div class="tp-cb-msg__bubble">' + (isHtml ? text : esc(text)) + '</div>';
		msgs.appendChild(d);
		scroll();
	}

	function addUser(text) {
		var d = document.createElement('div');
		d.className = 'tp-cb-msg tp-cb-msg--user tp-cb-msg--in';
		d.innerHTML = '<div class="tp-cb-msg__bubble">' + esc(text) + '</div>';
		msgs.appendChild(d);
		scroll();
	}

	function showTyping() {
		if (typingEl) return;
		typingEl = document.createElement('div');
		typingEl.className = 'tp-cb-typing';
		typingEl.innerHTML = '<span></span><span></span><span></span>';
		msgs.appendChild(typingEl);
		scroll();
	}

	function hideTyping() {
		if (typingEl) { typingEl.remove(); typingEl = null; }
	}

	function typeThenDo(fn, delay) {
		showTyping();
		setTimeout(function () { hideTyping(); fn(); }, delay || 900);
	}

	function addChips(labels, onSelect) {
		var wrap = document.createElement('div');
		wrap.className = 'tp-cb-chips tp-cb-msg--in';
		labels.forEach(function (label) {
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'tp-cb-chip';
			btn.textContent = label;
			btn.addEventListener('click', function () {
				wrap.querySelectorAll('.tp-cb-chip').forEach(function (c) {
					c.disabled = true;
					c.classList.remove('is-selected');
				});
				btn.classList.add('is-selected');
				addUser(label);
				setTimeout(function () { onSelect(label); }, 300);
			});
			wrap.appendChild(btn);
		});
		msgs.appendChild(wrap);
		scroll();
	}

	// ── Phone Capture ──

	function askPhone(context, extraMeta) {
		typeThenDo(function () {
			var msg = context || 'Please provide your phone number for details.';
			addBot(msg);

			var wrap = document.createElement('div');
			wrap.className = 'tp-cb-phone-capture tp-cb-msg--in';
			wrap.innerHTML =
				'<div class="tp-cb-phone-row">' +
					'<span class="tp-cb-phone-prefix">+91</span>' +
					'<input type="tel" class="tp-cb-phone-input" placeholder="Enter mobile number" maxlength="10" inputmode="numeric" autocomplete="tel">' +
					'<button type="button" class="tp-cb-phone-send" aria-label="Send">' +
						'<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>' +
					'</button>' +
				'</div>' +
				'<div class="tp-cb-phone-trust">' +
					'<svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>' +
					' No spam, we promise' +
				'</div>';
			msgs.appendChild(wrap);
			scroll();

			var inp = wrap.querySelector('.tp-cb-phone-input');
			var sendBtn = wrap.querySelector('.tp-cb-phone-send');
			setTimeout(function () { inp.focus(); }, 100);

			function doSubmit() {
				var phone = inp.value.replace(/\D/g, '');
				if (phone.length < 10) {
					inp.classList.add('is-error');
					inp.placeholder = 'Enter 10-digit number';
					inp.focus();
					setTimeout(function () { inp.classList.remove('is-error'); }, 1500);
					return;
				}
				inp.disabled = true;
				sendBtn.disabled = true;
				addUser('+91 ' + phone);
				submitLead(phone, extraMeta || {});
			}

			sendBtn.addEventListener('click', doSubmit);
			inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') doSubmit(); });
		});
	}

	function submitLead(phone, meta) {
		var data = new FormData();
		data.append('action', 'tp_chatbot_lead');
		data.append('nonce', tpChatbot.nonce);
		data.append('name', answers.name || '');
		data.append('phone', phone);
		data.append('config', meta.config || answers.config || '');
		data.append('budget', meta.budget || answers.budget || '');
		data.append('location', meta.location || '');
		data.append('timeline', meta.timeline || answers.timeline || '');
		data.append('page_url', window.location.href);
		// Extra fields
		if (meta.flow) data.append('flow', meta.flow);
		if (meta.visited) data.append('visited', meta.visited);
		if (meta.date) data.append('presentation_date', meta.date);
		if (meta.time) data.append('presentation_time', meta.time);

		showTyping();
		fetch(tpChatbot.ajax_url, { method: 'POST', body: data })
			.then(function (r) { return r.json(); })
			.then(function () {
				hideTyping();
				var capture = msgs.querySelector('.tp-cb-phone-capture:last-of-type');
				if (capture) capture.style.display = 'none';
				showThankYou(meta.thankMsg);
			})
			.catch(function () {
				hideTyping();
				addBot('Something went wrong. Please try again.');
			});
	}

	function showThankYou(customMsg) {
		addBot(customMsg || 'Thank you! 🎉 Our team will contact you shortly.');

		// WhatsApp CTA
		var waMsg = encodeURIComponent('Hi, I am interested in ' + (projectTitle || 'a property in Navi Mumbai'));
		var waDiv = document.createElement('div');
		waDiv.className = 'tp-cb-msg tp-cb-msg--bot tp-cb-msg--in';
		waDiv.innerHTML = '<div class="tp-cb-msg__bubble"><a href="https://wa.me/919999999999?text=' + waMsg + '" target="_blank" rel="noopener" class="tp-cb-wa-link"><svg width="16" height="16" fill="#25D366" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg> Chat on WhatsApp instead</a></div>';
		msgs.appendChild(waDiv);
		scroll();
	}

	// ── Config Chips (project-specific) ──

	function askConfig(callback) {
		var chipLabels = configs.length > 0 ? configs : ['1 BHK', '2 BHK', '3 BHK', '4+ BHK'];
		typeThenDo(function () {
			addBot('Select the configuration you are looking for:');
			addChips(chipLabels, function (selected) {
				answers.config = selected;
				callback(selected);
			});
		});
	}

	function askVisited(callback) {
		typeThenDo(function () {
			addBot('Great! Have you ever visited ' + (projectTitle || 'this project') + ' before?');
			addChips(['Yes', 'No'], function (selected) {
				answers.visited = selected;
				callback(selected);
			});
		});
	}

	// ════════════════════════════════════
	// ── FLOW 1: Pricing & Plan ──
	// ════════════════════════════════════
	function flowPricing() {
		askVisited(function (visited) {
			askConfig(function (config) {
				askPhone(
					'Great choice! Please provide your phone number and we\'ll share the detailed pricing for ' + config + '.',
					{ flow: 'pricing', config: config, visited: visited }
				);
			});
		});
	}

	// ════════════════════════════════════
	// ── FLOW 2: Amenities ──
	// ════════════════════════════════════
	function flowAmenities() {
		askConfig(function (config) {
			askPhone(
				'I\'ll share the complete amenities list for ' + config + '. Please provide your phone number.',
				{ flow: 'amenities', config: config }
			);
		});
	}

	// ════════════════════════════════════
	// ── FLOW 3: Get Direction ──
	// ════════════════════════════════════
	function flowDirection() {
		askVisited(function (visited) {
			askPhone(
				'I\'ll send you the exact location and directions. Please provide your phone number.',
				{ flow: 'direction', visited: visited }
			);
		});
	}

	// ════════════════════════════════════
	// ── FLOW 4: Book Site Visit ──
	// ════════════════════════════════════
	function flowSiteVisit() {
		askVisited(function (visited) {
			askPhone(
				'Our team will arrange a free site visit with cab pickup. Please provide your phone number to confirm.',
				{ flow: 'site_visit', visited: visited,
				  thankMsg: 'Your site visit has been booked! 🚗 Our team will call you to confirm the date and arrange cab pickup.' }
			);
		});
	}

	// ════════════════════════════════════
	// ── FLOW 5: Online Presentation ──
	// ════════════════════════════════════
	function flowPresentation() {
		typeThenDo(function () {
			addBot('Please select a suitable date for your online presentation:');

			// Date picker
			var wrap = document.createElement('div');
			wrap.className = 'tp-cb-date-picker tp-cb-msg--in';
			var today = new Date();
			// Show next 7 days
			for (var i = 1; i <= 7; i++) {
				var d = new Date(today);
				d.setDate(today.getDate() + i);
				var dayName = d.toLocaleDateString('en-IN', { weekday: 'short' });
				var dateStr = d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
				var fullDate = d.toISOString().split('T')[0];

				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'tp-cb-date-btn';
				btn.setAttribute('data-date', fullDate);
				btn.innerHTML = '<span class="tp-cb-date-btn__day">' + dayName + '</span><span class="tp-cb-date-btn__date">' + dateStr + '</span>';
				(function(b, fd, ds) {
					b.addEventListener('click', function () {
						wrap.querySelectorAll('.tp-cb-date-btn').forEach(function (x) { x.disabled = true; x.classList.remove('is-selected'); });
						b.classList.add('is-selected');
						addUser(ds);
						answers.presDate = fd;
						setTimeout(function () { askPresentationTime(fd); }, 300);
					});
				})(btn, fullDate, dayName + ', ' + dateStr);
				wrap.appendChild(btn);
			}
			msgs.appendChild(wrap);
			scroll();
		});
	}

	function askPresentationTime(date) {
		typeThenDo(function () {
			addBot('Select a convenient time slot:');
			var slots = ['10:00 AM', '11:00 AM', '12:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM', '6:00 PM'];
			addChips(slots, function (time) {
				answers.presTime = time;
				askPhone(
					'I\'ve booked a presentation slot for you. Please provide your phone number to complete the registration.',
					{ flow: 'presentation', date: date, time: time,
					  thankMsg: 'Your online presentation is confirmed! 🎥 We\'ll send you the meeting link shortly.' }
				);
			});
		});
	}

	// ════════════════════════════════════
	// ── FLOW 6: Get a Callback ──
	// ════════════════════════════════════
	function flowCallback() {
		askPhone(
			'Sure! Please provide your phone number and our expert will call you right away.',
			{ flow: 'callback',
			  thankMsg: 'Got it! Our expert will call you within the next few minutes. 📞' }
		);
	}

	// ── Main Menu ──

	function showMainMenu() {
		typeThenDo(function () {
			addBot('How can I help you today? Please select an option:');
			var options = [
				{ label: '💰 Pricing & Plans', action: flowPricing },
				{ label: '🏊 Amenities', action: flowAmenities },
				{ label: '📍 Get Direction', action: flowDirection },
				{ label: '🏠 Book Site Visit', action: flowSiteVisit },
				{ label: '🖥️ Online Presentation', action: flowPresentation },
				{ label: '📞 Get a Callback', action: flowCallback }
			];

			var wrap = document.createElement('div');
			wrap.className = 'tp-cb-menu tp-cb-msg--in';
			options.forEach(function (opt) {
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'tp-cb-menu-btn';
				btn.textContent = opt.label;
				btn.addEventListener('click', function () {
					wrap.querySelectorAll('.tp-cb-menu-btn').forEach(function (b) { b.disabled = true; b.classList.remove('is-selected'); });
					btn.classList.add('is-selected');
					addUser(opt.label);
					setTimeout(function () { opt.action(); }, 300);
				});
				wrap.appendChild(btn);
			});
			msgs.appendChild(wrap);
			scroll();
		}, 700);
	}

	// ── Start Chat ──

	function startChat() {
		if (started) return;
		started = true;
		var greeting = projectTitle
			? "Hi! 👋 I'm Nidhi from " + projectTitle + ". How can I assist you today?"
			: "Hi! 👋 I'm Nidhi. Looking for a home in Navi Mumbai? Let me help you!";
		addBot(greeting);
		showMainMenu();
	}

	// ── Open / Close ──

	function openChat() {
		isOpen = true;
		panel.classList.add('is-open');
		trigger.classList.add('is-open');
		var greeting = document.getElementById('tp-cb-greeting');
		if (greeting) greeting.style.display = 'none';
		if (!started) startChat();
	}

	function closeChat() {
		isOpen = false;
		panel.classList.remove('is-open');
		trigger.classList.remove('is-open');
	}

	openBtn.addEventListener('click', function () {
		if (isOpen) closeChat(); else openChat();
	});

	closeBtn.addEventListener('click', closeChat);

	// Show greeting bubble after 3s
	setTimeout(function () {
		var greeting = document.getElementById('tp-cb-greeting');
		if (greeting) greeting.classList.add('is-visible');
	}, 3000);

	// Auto-open after 15s
	setTimeout(function () {
		if (!isOpen) openChat();
	}, 15000);
})();
