/**
 * 10Projects — Shared Utility Functions
 *
 * @package TenProjects
 * @since 1.0.0
 */

(function () {
  'use strict';

  const TP = window.TenProjects = window.TenProjects || {};

  /**
   * Format price in Indian number system.
   * @param {number} amount - Amount in rupees.
   * @returns {string} Formatted price.
   */
  TP.formatPrice = function (amount) {
    if (!amount || amount <= 0) return 'Price on request';

    if (amount >= 10000000) {
      const cr = (amount / 10000000).toFixed(2).replace(/\.00$/, '');
      return '\u20B9' + cr + ' Cr';
    }
    if (amount >= 100000) {
      const l = (amount / 100000).toFixed(2).replace(/\.00$/, '');
      return '\u20B9' + l + ' L';
    }
    return '\u20B9' + amount.toLocaleString('en-IN');
  };

  /**
   * Calculate EMI.
   * @param {number} principal - Loan amount (80% of property price).
   * @param {number} rate - Annual interest rate (default 8.5).
   * @param {number} tenure - Years (default 20).
   * @returns {number} Monthly EMI.
   */
  TP.calculateEMI = function (principal, rate, tenure) {
    rate = rate || 8.5;
    tenure = tenure || 20;
    const loanAmount = principal * 0.8;
    const monthlyRate = (rate / 100) / 12;
    const months = tenure * 12;

    if (monthlyRate <= 0) return loanAmount / months;

    return loanAmount * monthlyRate * Math.pow(1 + monthlyRate, months) /
      (Math.pow(1 + monthlyRate, months) - 1);
  };

  /**
   * REST API helper.
   * @param {string} endpoint - API endpoint path.
   * @param {Object} options - Fetch options.
   * @returns {Promise<Object>} Response data.
   */
  TP.api = async function (endpoint, options) {
    const defaults = {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.tenprojectsData?.nonce || '',
      },
    };

    const config = Object.assign({}, defaults, options);
    if (config.body && typeof config.body === 'object') {
      config.body = JSON.stringify(config.body);
    }

    const baseUrl = window.tenprojectsData?.restUrl || '/wp-json/tenprojects/v1/';
    const response = await fetch(baseUrl + endpoint, config);

    if (!response.ok) {
      const error = await response.json().catch(() => ({}));
      throw new Error(error.message || 'API request failed');
    }

    return response.json();
  };

  /**
   * Debounce function.
   * @param {Function} fn - Function to debounce.
   * @param {number} delay - Delay in ms.
   * @returns {Function} Debounced function.
   */
  TP.debounce = function (fn, delay) {
    let timer;
    return function (...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), delay);
    };
  };

  /**
   * Cookie helpers.
   */
  TP.cookie = {
    set: function (name, value, days) {
      const d = new Date();
      d.setTime(d.getTime() + (days || 365) * 24 * 60 * 60 * 1000);
      document.cookie = name + '=' + encodeURIComponent(value) +
        ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
    },
    get: function (name) {
      const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
      return match ? decodeURIComponent(match[2]) : null;
    },
    remove: function (name) {
      document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
    },
  };

  /**
   * Generate or retrieve anonymous visitor ID.
   * @returns {string} Visitor ID.
   */
  TP.getVisitorId = function () {
    let id = TP.cookie.get('tp_visitor_id');
    if (!id) {
      id = 'v_' + Date.now().toString(36) + '_' + Math.random().toString(36).substring(2, 8);
      TP.cookie.set('tp_visitor_id', id, 365);
    }
    return id;
  };

})();
