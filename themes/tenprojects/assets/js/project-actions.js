/**
 * 10Projects — Project Page & Result Page Actions
 *
 * Handles save/bookmark, share, compare, EMI calculator,
 * sticky tabs, gallery lightbox, and toast notifications.
 * Depends on utils.js (TenProjects global namespace).
 *
 * @package TenProjects
 * @since 1.0.0
 */
(function () {
  'use strict';

  const TP = window.TenProjects;
  if (!TP) return;

  // ── Toast Notifications ───────────────────────────────
  const toastContainer = (() => {
    const el = document.createElement('div');
    el.className = 'toast-container';
    document.body.appendChild(el);
    return el;
  })();

  function showToast(message, type) {
    type = type || 'info';
    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.textContent = message;
    toastContainer.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('toast--visible'));
    setTimeout(() => {
      toast.classList.remove('toast--visible');
      toast.addEventListener('transitionend', () => toast.remove(), { once: true });
      setTimeout(() => toast.remove(), 400); // fallback
    }, 3000);
  }

  TP.showToast = showToast; // expose globally

  // ── 1. Save / Bookmark Project ────────────────────────
  function initSaveButtons() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-save');
      if (!btn) return;
      e.preventDefault();
      const isSaved = btn.classList.contains('btn-save--active');
      isSaved && btn.dataset.savedId
        ? unsaveProject(btn, btn.dataset.savedId)
        : saveProject(btn, btn.dataset.projectId);
    });
  }

  async function saveProject(btn, projectId) {
    btn.disabled = true;
    try {
      const res = await TP.api('saved-projects', {
        method: 'POST', body: { project_id: projectId },
      });
      btn.classList.add('btn-save--active');
      btn.dataset.savedId = res.id || '';
      btn.setAttribute('aria-label', 'Remove from saved');
      showToast('Project saved!', 'success');
    } catch (err) {
      showToast('Could not save project. Please sign in first.', 'error');
    }
    btn.disabled = false;
  }

  async function unsaveProject(btn, savedId) {
    btn.disabled = true;
    try {
      await TP.api(`saved-projects/${savedId}`, { method: 'DELETE' });
      btn.classList.remove('btn-save--active');
      btn.dataset.savedId = '';
      btn.setAttribute('aria-label', 'Save project');
      showToast('Removed from saved.', 'info');
    } catch (err) {
      showToast('Could not remove project.', 'error');
    }
    btn.disabled = false;
  }

  // ── 2. Share Project ──────────────────────────────────
  function initShareButtons() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-share');
      if (!btn) return;
      e.preventDefault();
      const title = btn.dataset.title || document.title;
      const url = btn.dataset.url || window.location.href;
      if (navigator.share) {
        navigator.share({ title, url }).catch(() => {});
      } else {
        copyToClipboard(url);
      }
    });
  }

  function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(
        () => showToast('Link copied to clipboard!', 'success'),
        () => fallbackCopy(text)
      );
    } else {
      fallbackCopy(text);
    }
  }

  function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;left:-9999px;opacity:0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); showToast('Link copied to clipboard!', 'success'); }
    catch (err) { showToast('Could not copy link.', 'error'); }
    ta.remove();
  }

  // ── 3. Compare Projects ───────────────────────────────
  const MAX_COMPARE = 4;
  const COMPARE_KEY = 'tp_compare_ids';

  function getCompareList() {
    try { return JSON.parse(localStorage.getItem(COMPARE_KEY)) || []; }
    catch (e) { return []; }
  }

  function setCompareList(ids) {
    localStorage.setItem(COMPARE_KEY, JSON.stringify(ids));
    updateCompareBar(ids);
    updateCompareButtons(ids);
  }

  function initCompare() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-compare');
      if (!btn) return;
      e.preventDefault();
      toggleCompare(btn.dataset.projectId);
    });

    // Floating comparison bar
    const bar = document.createElement('div');
    bar.className = 'compare-bar';
    bar.id = 'compare-bar';
    bar.innerHTML = `<span class="compare-bar__count"></span>
      <button type="button" class="compare-bar__clear">Clear</button>
      <button type="button" class="compare-bar__go">Compare Now</button>`;
    document.body.appendChild(bar);
    bar.querySelector('.compare-bar__clear').addEventListener('click', () => setCompareList([]));
    bar.querySelector('.compare-bar__go').addEventListener('click', submitCompare);

    // Restore state
    const existing = getCompareList();
    if (existing.length) updateCompareBar(existing);
    updateCompareButtons(existing);
  }

  function toggleCompare(projectId) {
    const list = getCompareList();
    const idx = list.indexOf(projectId);
    if (idx > -1) {
      list.splice(idx, 1);
      showToast('Removed from comparison.', 'info');
    } else {
      if (list.length >= MAX_COMPARE) {
        showToast(`You can compare up to ${MAX_COMPARE} projects.`, 'error');
        return;
      }
      list.push(projectId);
      showToast('Added to comparison.', 'success');
    }
    setCompareList(list);
  }

  function updateCompareBar(ids) {
    const bar = document.getElementById('compare-bar');
    if (!bar) return;
    if (ids.length >= 2) {
      bar.classList.add('compare-bar--visible');
      bar.querySelector('.compare-bar__count').textContent = `${ids.length} projects selected`;
    } else {
      bar.classList.remove('compare-bar--visible');
    }
  }

  function updateCompareButtons(ids) {
    document.querySelectorAll('.btn-compare').forEach((btn) => {
      const active = ids.indexOf(btn.dataset.projectId) > -1;
      btn.classList.toggle('btn-compare--active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  async function submitCompare() {
    const ids = getCompareList();
    if (ids.length < 2) return;
    try {
      const res = await TP.api('comparisons', {
        method: 'POST', body: { project_ids: ids },
      });
      window.location.href = res.redirect_url || `/compare/?ids=${ids.join(',')}`;
    } catch (err) {
      showToast('Could not start comparison.', 'error');
    }
  }

  // ── 4. EMI Calculator Widget ──────────────────────────
  function initEmiCalculator() {
    const widget = document.querySelector('.emi-calculator');
    if (!widget) return;
    const $loan    = widget.querySelector('[data-emi="loan"]');
    const $rate    = widget.querySelector('[data-emi="rate"]');
    const $tenure  = widget.querySelector('[data-emi="tenure"]');
    const $result  = widget.querySelector('[data-emi="result"]');
    const $interest = widget.querySelector('[data-emi="interest"]');
    const $payable = widget.querySelector('[data-emi="payable"]');
    if (!$loan || !$rate || !$tenure) return;

    const calculate = () => {
      const principal = parseFloat($loan.value) || 0;
      const rate = parseFloat($rate.value) || 8.5;
      const tenure = parseFloat($tenure.value) || 20;
      // Widget loan field = actual loan (80% of price already).
      // calculateEMI applies 80% internally, so pass principal / 0.8.
      const emi = TP.calculateEMI(principal / 0.8, rate, tenure);
      const totalPaid = emi * tenure * 12;
      if ($result)   $result.textContent   = TP.formatPrice(Math.round(emi));
      if ($interest) $interest.textContent = TP.formatPrice(Math.round(totalPaid - principal));
      if ($payable)  $payable.textContent  = TP.formatPrice(Math.round(totalPaid));
    };

    const debouncedCalc = TP.debounce(calculate, 200);
    [$loan, $rate, $tenure].forEach((el) => el.addEventListener('input', debouncedCalc));
    calculate();
  }

  // ── 5. Project Tabs (Sticky Scroll) ───────────────────
  function initProjectTabs() {
    const tabBar = document.querySelector('.project-tabs');
    if (!tabBar) return;
    const tabs = tabBar.querySelectorAll('[data-tab-target]');
    if (!tabs.length) return;

    tabs.forEach((tab) => {
      tab.addEventListener('click', (e) => {
        e.preventDefault();
        const target = document.getElementById(tab.dataset.tabTarget);
        if (!target) return;
        const top = target.getBoundingClientRect().top + window.scrollY - tabBar.offsetHeight - 16;
        window.scrollTo({ top, behavior: 'smooth' });
      });
    });

    const sections = Array.from(tabs)
      .map((t) => document.getElementById(t.dataset.tabTarget))
      .filter(Boolean);

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        tabs.forEach((t) => t.classList.remove('project-tab--active'));
        const active = tabBar.querySelector(`[data-tab-target="${entry.target.id}"]`);
        if (active) active.classList.add('project-tab--active');
      });
    }, { rootMargin: `-${tabBar.offsetHeight + 20}px 0px -60% 0px`, threshold: 0 });

    sections.forEach((sec) => observer.observe(sec));
  }

  // ── 6. Gallery Lightbox ───────────────────────────────
  let lightboxEl = null;
  let galleryImages = [];
  let currentIdx = 0;

  function initGallery() {
    const gallery = document.querySelector('.project-gallery');
    if (!gallery) return;
    const thumbs = gallery.querySelectorAll('[data-gallery-src]');
    if (!thumbs.length) return;

    galleryImages = Array.from(thumbs).map((t) => ({
      src: t.dataset.gallerySrc, alt: t.getAttribute('alt') || '',
    }));
    thumbs.forEach((thumb, i) => {
      thumb.addEventListener('click', () => openLightbox(i));
      thumb.style.cursor = 'pointer';
    });
    buildLightbox();
  }

  function buildLightbox() {
    lightboxEl = document.createElement('div');
    lightboxEl.className = 'lightbox';
    lightboxEl.setAttribute('role', 'dialog');
    lightboxEl.setAttribute('aria-modal', 'true');
    lightboxEl.innerHTML = `<div class="lightbox__overlay"></div>
      <div class="lightbox__content">
        <button type="button" class="lightbox__close" aria-label="Close gallery">&times;</button>
        <button type="button" class="lightbox__prev" aria-label="Previous image">&#8249;</button>
        <img class="lightbox__img" src="" alt="" />
        <button type="button" class="lightbox__next" aria-label="Next image">&#8250;</button>
        <span class="lightbox__counter"></span>
      </div>`;
    document.body.appendChild(lightboxEl);

    lightboxEl.querySelector('.lightbox__close').addEventListener('click', closeLightbox);
    lightboxEl.querySelector('.lightbox__overlay').addEventListener('click', closeLightbox);
    lightboxEl.querySelector('.lightbox__prev').addEventListener('click', () => navigate(-1));
    lightboxEl.querySelector('.lightbox__next').addEventListener('click', () => navigate(1));
    document.addEventListener('keydown', (e) => {
      if (!lightboxEl.classList.contains('lightbox--open')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') navigate(-1);
      if (e.key === 'ArrowRight') navigate(1);
    });
  }

  function openLightbox(idx) {
    currentIdx = idx;
    updateLightboxImage();
    lightboxEl.classList.add('lightbox--open');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    lightboxEl.classList.remove('lightbox--open');
    document.body.style.overflow = '';
  }

  function navigate(dir) {
    currentIdx = (currentIdx + dir + galleryImages.length) % galleryImages.length;
    updateLightboxImage();
  }

  function updateLightboxImage() {
    const img = lightboxEl.querySelector('.lightbox__img');
    const ctr = lightboxEl.querySelector('.lightbox__counter');
    const item = galleryImages[currentIdx];
    img.src = item.src;
    img.alt = item.alt;
    ctr.textContent = `${currentIdx + 1} / ${galleryImages.length}`;
  }

  // ── Boot ──────────────────────────────────────────────
  function init() {
    initSaveButtons();
    initShareButtons();
    initCompare();
    initEmiCalculator();
    initProjectTabs();
    initGallery();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
