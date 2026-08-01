/**
 * 10Projects — AI Assessment Chat Interface
 *
 * Lazy-loaded on the assessment page only.
 * Depends on utils.js (TenProjects global namespace).
 *
 * @package TenProjects
 * @since 1.0.0
 */
(function () {
  'use strict';

  const TP = window.TenProjects;
  if (!TP) return;

  // ── State ──────────────────────────────────────────────
  const state = {
    sessionUuid: null, currentQuestion: null, phaseInfo: null,
    answeredCount: 0, totalQuestions: 0,
    selectedChips: [], rankedCards: [], isProcessing: false,
  };

  // ── DOM refs (set once in init) ────────────────────────
  let $messages, $inputArea, $accuracyFill, $accuracyLabel;

  function cacheDom() {
    $messages      = document.querySelector('.chat-messages');
    $inputArea     = document.querySelector('.chat-input-area');
    $accuracyFill  = document.querySelector('.accuracy-fill');
    $accuracyLabel = document.querySelector('.accuracy-label');
  }

  // ── Helpers ────────────────────────────────────────────
  function getUtmParams() {
    const params = new URLSearchParams(window.location.search);
    const utms = {};
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'].forEach((k) => {
      if (params.get(k)) utms[k] = params.get(k);
    });
    return utms;
  }

  function scrollToBottom() {
    if ($messages) $messages.scrollTo({ top: $messages.scrollHeight, behavior: 'smooth' });
  }

  function saveState() {
    try {
      localStorage.setItem('tp_session_uuid', state.sessionUuid || '');
      localStorage.setItem('tp_chat_state', JSON.stringify({
        answeredCount: state.answeredCount,
        totalQuestions: state.totalQuestions,
        phaseInfo: state.phaseInfo,
      }));
    } catch (e) { /* storage full */ }
  }

  function loadState() {
    state.sessionUuid = localStorage.getItem('tp_session_uuid') || null;
    try {
      const s = JSON.parse(localStorage.getItem('tp_chat_state'));
      if (s) {
        state.answeredCount  = s.answeredCount || 0;
        state.totalQuestions = s.totalQuestions || 0;
        state.phaseInfo      = s.phaseInfo || null;
      }
    } catch (e) { /* corrupt */ }
  }

  // ── Message rendering ─────────────────────────────────
  function addMessage(text, sender) {
    const msg = document.createElement('div');
    msg.className = `chat-message chat-message--${sender}`;
    const content = document.createElement('div');
    content.className = 'chat-message__content';
    content.innerHTML = text;
    msg.appendChild(content);
    $messages.appendChild(msg);
    scrollToBottom();
    return content;
  }

  function showAnalysingDots() {
    return addMessage(
      '<span class="analyzing-dots"><span class="analyzing-dot"></span>' +
      '<span class="analyzing-dot"></span><span class="analyzing-dot"></span></span>',
      'ai'
    );
  }

  function removeAnalysingDots() {
    const dots = $messages.querySelector('.analyzing-dots');
    if (dots) { const m = dots.closest('.chat-message'); if (m) m.remove(); }
  }

  // ── Progress bar ──────────────────────────────────────
  function updateProgress() {
    if (!$accuracyFill || !state.totalQuestions) return;
    const pct = Math.min(Math.round((state.answeredCount / state.totalQuestions) * 100), 100);
    $accuracyFill.style.width = pct + '%';
    if ($accuracyLabel) $accuracyLabel.textContent = pct + '% accuracy';
  }

  // ── Question router ───────────────────────────────────
  function renderQuestion(qData) {
    state.currentQuestion = qData;
    state.selectedChips = [];
    state.rankedCards = [];
    if (qData.prompt) addMessage(qData.prompt, 'ai');
    $inputArea.innerHTML = '';
    if (qData.type === 'chips')         renderChips(qData);
    else if (qData.type === 'cards')    renderCards(qData);
    else if (qData.type === 'textarea') renderTextarea(qData);
    scrollToBottom();
  }

  // ── Chips ─────────────────────────────────────────────
  function renderChips(q) {
    const multi = q.multi_select === true;
    const wrap = document.createElement('div');
    wrap.className = 'chat-chips';

    q.options.forEach((opt) => {
      const chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'chat-chip';
      chip.textContent = opt.label || opt;
      chip.dataset.value = opt.value ?? opt;
      chip.addEventListener('click', () => {
        if (state.isProcessing) return;
        if (multi) {
          chip.classList.toggle('chat-chip--selected');
          const val = chip.dataset.value;
          const idx = state.selectedChips.indexOf(val);
          idx > -1 ? state.selectedChips.splice(idx, 1) : state.selectedChips.push(val);
        } else {
          submitAnswer(chip.dataset.value, chip.textContent);
        }
      });
      wrap.appendChild(chip);
    });
    $inputArea.appendChild(wrap);

    if (multi) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'chat-chip chat-chip--continue';
      btn.textContent = 'Continue';
      btn.addEventListener('click', () => {
        if (state.isProcessing || !state.selectedChips.length) return;
        const labels = state.selectedChips.map((v) => {
          const c = wrap.querySelector(`[data-value="${v}"]`);
          return c ? c.textContent : v;
        });
        submitAnswer(state.selectedChips, labels.join(', '));
      });
      $inputArea.appendChild(btn);
    }
  }

  // ── Cards (with tap-to-rank for priorities) ───────────
  function renderCards(q) {
    const isPriority = q.rank === true;
    const maxRank = q.max_rank || 3;
    const wrap = document.createElement('div');
    wrap.className = 'chat-cards';

    q.options.forEach((opt) => {
      const card = document.createElement('div');
      card.className = 'chat-card';
      card.dataset.value = opt.value ?? opt.label ?? opt;
      let inner = '';
      if (opt.icon) inner += `<span class="chat-card__icon">${opt.icon}</span>`;
      inner += `<span class="chat-card__label">${opt.label || opt}</span>`;
      if (opt.description) inner += `<span class="chat-card__desc">${opt.description}</span>`;
      if (isPriority) inner += '<span class="chat-card__rank"></span>';
      card.innerHTML = inner;
      card.addEventListener('click', () => {
        if (state.isProcessing) return;
        isPriority ? handleRankTap(card, wrap, maxRank) : submitAnswer(card.dataset.value, opt.label || opt);
      });
      wrap.appendChild(card);
    });
    $inputArea.appendChild(wrap);

    if (isPriority) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'chat-chip chat-chip--continue';
      btn.textContent = 'Confirm Ranking';
      btn.addEventListener('click', () => {
        if (state.isProcessing || state.rankedCards.length < maxRank) return;
        const labels = state.rankedCards.map((v, i) => `#${i + 1} ${v}`);
        submitAnswer(state.rankedCards, labels.join(', '));
      });
      $inputArea.appendChild(btn);
    }
  }

  function handleRankTap(card, wrap, maxRank) {
    const val = card.dataset.value;
    const idx = state.rankedCards.indexOf(val);
    if (idx > -1) {
      state.rankedCards.splice(idx, 1);
      card.classList.remove('chat-chip--selected');
    } else {
      if (state.rankedCards.length >= maxRank) return;
      state.rankedCards.push(val);
      card.classList.add('chat-chip--selected');
    }
    // Re-number all rank labels
    wrap.querySelectorAll('.chat-card').forEach((c) => {
      const r = c.querySelector('.chat-card__rank');
      if (!r) return;
      const ri = state.rankedCards.indexOf(c.dataset.value);
      r.textContent = ri > -1 ? '#' + (ri + 1) : '';
    });
  }

  // ── Textarea ──────────────────────────────────────────
  function renderTextarea(q) {
    const wrap = document.createElement('div');
    wrap.className = 'chat-textarea-wrap';
    const ta = document.createElement('textarea');
    ta.className = 'chat-textarea';
    ta.placeholder = q.placeholder || 'Type your answer...';
    ta.rows = 3;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'chat-chip chat-chip--continue';
    btn.textContent = 'Send';
    btn.addEventListener('click', () => {
      const val = ta.value.trim();
      if (state.isProcessing || !val) return;
      submitAnswer(val, val);
    });
    ta.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); btn.click(); }
    });
    wrap.appendChild(ta);
    wrap.appendChild(btn);
    $inputArea.appendChild(wrap);
    ta.focus();
  }

  // ── Answer submission ─────────────────────────────────
  async function submitAnswer(value, displayText) {
    if (state.isProcessing) return;
    state.isProcessing = true;
    addMessage(displayText, 'user');
    $inputArea.innerHTML = '';
    showAnalysingDots();

    try {
      const res = await TP.api('assessment/answer', {
        method: 'POST',
        body: { session_uuid: state.sessionUuid, question_key: state.currentQuestion.key, value },
      });
      removeAnalysingDots();
      state.answeredCount++;
      if (res.total_questions) state.totalQuestions = res.total_questions;
      updateProgress();
      saveState();
      if (res.next_question) renderQuestion(res.next_question);
      else if (res.phase_complete) handlePhaseComplete(res);
    } catch (err) {
      removeAnalysingDots();
      addMessage('Something went wrong. Please try again.', 'ai');
    }
    state.isProcessing = false;
  }

  // ── Phase complete / results preview ──────────────────
  function handlePhaseComplete(res) {
    const el = addMessage(
      `<span class="analyzing-projects">Analysing ${res.total_projects || 500}+ projects...</span>`, 'ai'
    );
    setTimeout(() => { el.innerHTML = ''; renderResultsPreview(el, res); }, 1500);
  }

  function renderResultsPreview(container, res) {
    const results = res.results || [];
    const accuracy = res.accuracy || 0;
    let html = `<div class="results-preview">`;
    html += `<p class="results-preview__heading">Top ${results.length} matches (${accuracy}% accuracy):</p>`;
    results.forEach((p, i) => {
      html += `<div class="result-card-inline">
        <span class="result-card-inline__rank">${i + 1}</span>
        <span class="result-card-inline__name">${p.name}</span>
        <span class="result-card-inline__score">${p.score}% fit</span>
      </div>`;
    });
    html += '</div>';

    if (res.next_phase) {
      const rem = res.next_phase.questions_remaining || 0;
      const target = res.next_phase.accuracy_target || accuracy + 15;
      html += `<div class="follow-up-chips">
        <button type="button" class="chat-chip chat-chip--primary" data-action="advance">
          Answer ${rem} more questions to improve to ${target}%</button></div>`;
    } else {
      html += `<div class="follow-up-chips">
        <button type="button" class="chat-chip chat-chip--primary" data-action="show-results">
          Show full details</button></div>`;
    }
    container.innerHTML = html;
    scrollToBottom();

    const advBtn = container.querySelector('[data-action="advance"]');
    if (advBtn) advBtn.addEventListener('click', handleAdvance);
    const showBtn = container.querySelector('[data-action="show-results"]');
    if (showBtn) showBtn.addEventListener('click', showLeadGate);

    state.phaseInfo = res.phase_info || state.phaseInfo;
    saveState();
  }

  // ── Phase advancement ─────────────────────────────────
  async function handleAdvance() {
    if (state.isProcessing) return;
    state.isProcessing = true;
    addMessage('I\'d like to improve my results!', 'user');
    showAnalysingDots();
    try {
      const res = await TP.api('assessment/advance', {
        method: 'POST', body: { session_uuid: state.sessionUuid },
      });
      removeAnalysingDots();
      if (res.question_key && res.question_data) {
        res.question_data.key = res.question_key;
        renderQuestion(res.question_data);
      }
    } catch (err) {
      removeAnalysingDots();
      addMessage('Could not load next questions. Please try again.', 'ai');
    }
    state.isProcessing = false;
  }

  // ── Lead gate (OTP flow) ──────────────────────────────
  function showLeadGate() {
    $inputArea.innerHTML = '';
    addMessage(`<div class="lead-gate">
      <p class="lead-gate__heading">Enter your phone to view full details</p>
      <div class="lead-gate__phone-row">
        <span class="lead-gate__prefix">+91</span>
        <input type="tel" class="lead-gate__phone" maxlength="10"
               placeholder="10-digit mobile number" inputmode="numeric"
               pattern="[0-9]{10}" autocomplete="tel-national" />
      </div>
      <button type="button" class="lead-gate__btn" data-action="send-otp">Send OTP</button>
      <p class="lead-gate__trust">We respect your privacy. No spam, ever.</p>
    </div>`, 'ai');
    scrollToBottom();

    const sendBtn = $messages.querySelector('[data-action="send-otp"]');
    const phoneIn = $messages.querySelector('.lead-gate__phone');
    if (!sendBtn || !phoneIn) return;

    sendBtn.addEventListener('click', () => handleSendOtp(phoneIn, sendBtn));
    phoneIn.addEventListener('keydown', (e) => { if (e.key === 'Enter') sendBtn.click(); });
    phoneIn.addEventListener('input', () => {
      phoneIn.value = phoneIn.value.replace(/\D/g, '').slice(0, 10);
    });
    phoneIn.focus();
  }

  async function handleSendOtp(phoneIn, sendBtn) {
    const phone = phoneIn.value.trim();
    if (!/^\d{10}$/.test(phone)) { phoneIn.classList.add('input--error'); return; }
    phoneIn.classList.remove('input--error');
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending...';
    try {
      await TP.api('auth/send-otp', {
        method: 'POST', body: { phone: '+91' + phone, session_uuid: state.sessionUuid },
      });
      showOtpInput(phone);
    } catch (err) {
      sendBtn.disabled = false;
      sendBtn.textContent = 'Send OTP';
      addMessage('Could not send OTP. Please check the number and try again.', 'ai');
    }
  }

  function showOtpInput(phone) {
    const gate = $messages.querySelector('.lead-gate');
    if (!gate) return;
    gate.innerHTML = `<p class="lead-gate__heading">Enter the 6-digit OTP sent to +91 ${phone}</p>
      <input type="text" class="lead-gate__otp" maxlength="6" placeholder="______"
             inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" />
      <button type="button" class="lead-gate__btn" data-action="verify-otp">Verify</button>
      <p class="lead-gate__trust">We respect your privacy. No spam, ever.</p>`;
    const otpIn = gate.querySelector('.lead-gate__otp');
    const verBtn = gate.querySelector('[data-action="verify-otp"]');
    if (!otpIn || !verBtn) return;
    otpIn.addEventListener('input', () => { otpIn.value = otpIn.value.replace(/\D/g, '').slice(0, 6); });
    verBtn.addEventListener('click', () => handleVerifyOtp(phone, otpIn, verBtn));
    otpIn.addEventListener('keydown', (e) => { if (e.key === 'Enter') verBtn.click(); });
    otpIn.focus();
  }

  async function handleVerifyOtp(phone, otpIn, verBtn) {
    const otp = otpIn.value.trim();
    if (otp.length !== 6) { otpIn.classList.add('input--error'); return; }
    otpIn.classList.remove('input--error');
    verBtn.disabled = true;
    verBtn.textContent = 'Verifying...';
    try {
      const res = await TP.api('auth/verify-otp', {
        method: 'POST',
        body: { phone: '+91' + phone, otp, session_uuid: state.sessionUuid },
      });
      window.location.href = res.redirect_url || `/results/${state.sessionUuid}/`;
    } catch (err) {
      verBtn.disabled = false;
      verBtn.textContent = 'Verify';
      otpIn.value = '';
      addMessage('Invalid OTP. Please try again.', 'ai');
    }
  }

  // ── Session init ──────────────────────────────────────
  async function resumeSession(uuid) {
    showAnalysingDots();
    try {
      const res = await TP.api(`assessment/session/${uuid}`, { method: 'GET' });
      removeAnalysingDots();
      if (res.status === 'completed') { showLeadGate(); return; }
      state.answeredCount  = res.answered_count || 0;
      state.totalQuestions = res.total_questions || 0;
      state.phaseInfo      = res.phase_info || null;
      updateProgress();
      if (res.current_question) {
        addMessage('Welcome back! Let\'s continue where you left off.', 'ai');
        renderQuestion(res.current_question);
      }
    } catch (err) {
      removeAnalysingDots();
      localStorage.removeItem('tp_session_uuid');
      localStorage.removeItem('tp_chat_state');
      state.sessionUuid = null;
      startNewSession();
    }
  }

  async function startNewSession() {
    showAnalysingDots();
    try {
      const res = await TP.api('assessment/start', {
        method: 'POST',
        body: { visitor_id: TP.getVisitorId(), utm: getUtmParams() },
      });
      removeAnalysingDots();
      state.sessionUuid   = res.session_uuid;
      state.totalQuestions = res.total_questions || 0;
      state.phaseInfo     = res.phase_info || null;
      state.answeredCount = 0;
      saveState();
      updateProgress();
      if (res.first_question) renderQuestion(res.first_question);
    } catch (err) {
      removeAnalysingDots();
      addMessage('Unable to start the assessment. Please refresh and try again.', 'ai');
    }
  }

  // ── Boot ──────────────────────────────────────────────
  function init() {
    cacheDom();
    if (!$messages) return;
    loadState();
    state.sessionUuid ? resumeSession(state.sessionUuid) : startNewSession();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
