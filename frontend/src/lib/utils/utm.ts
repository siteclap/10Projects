/**
 * UTM parameter capture and retrieval.
 *
 * Captures UTM parameters from the URL on first visit and stores them
 * in a cookie for 30 days. Used for attribution tracking and lead routing.
 *
 * Ported from the WordPress theme's `analytics.js` UTM capture logic.
 */

const COOKIE_NAME = 'tp_utm';
const COOKIE_DAYS = 30;

const UTM_KEYS = [
  'utm_source',
  'utm_medium',
  'utm_campaign',
  'utm_term',
  'utm_content',
] as const;

/**
 * Set a cookie with an expiration date.
 */
function setCookie(name: string, value: string, days: number): void {
  const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
  document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires};path=/;SameSite=Lax`;
}

/**
 * Read a cookie value by name.
 */
function getCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp(`(^| )${name}=([^;]+)`));
  return match ? decodeURIComponent(match[2]) : null;
}

/**
 * Capture UTM parameters from the current URL and store them in a cookie.
 *
 * Should be called once on page load. Only writes the cookie when at least
 * one UTM parameter is present in the URL query string.
 *
 * Safe to call on the server — returns early if `window` is unavailable.
 */
export function captureUtmParams(): void {
  if (typeof window === 'undefined') return;

  const params = new URLSearchParams(window.location.search);
  const utmData: Record<string, string> = {};
  let hasUtm = false;

  for (const key of UTM_KEYS) {
    const value = params.get(key);
    if (value) {
      utmData[key] = value;
      hasUtm = true;
    }
  }

  if (hasUtm) {
    setCookie(COOKIE_NAME, JSON.stringify(utmData), COOKIE_DAYS);
  }
}

/**
 * Retrieve stored UTM parameters from the cookie.
 *
 * @returns  Object with UTM keys and values, or empty object if no UTM data
 */
export function getUtmParams(): Record<string, string> {
  if (typeof window === 'undefined') return {};

  const raw = getCookie(COOKIE_NAME);
  if (!raw) return {};

  try {
    return JSON.parse(raw) as Record<string, string>;
  } catch {
    return {};
  }
}
