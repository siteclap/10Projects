/**
 * Anonymous visitor ID generation and persistence.
 *
 * Generates a unique visitor identifier on first visit and stores it
 * in a cookie for 365 days. Used for analytics and pre-auth session tracking.
 *
 * Ported from the WordPress theme's `TP.getVisitorId`.
 */

const COOKIE_NAME = 'tp_visitor_id';
const COOKIE_DAYS = 365;

/**
 * Set a cookie with an expiration date.
 */
function setCookie(name: string, value: string, days: number): void {
  const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
  document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires};path=/;SameSite=Lax;Secure`;
}

/**
 * Read a cookie value by name.
 */
function getCookie(name: string): string | null {
  if (typeof document === 'undefined') return null;
  const match = document.cookie.match(new RegExp(`(^| )${name}=([^;]+)`));
  return match ? decodeURIComponent(match[2]) : null;
}

/**
 * Generate a unique visitor ID string.
 *
 * Format: `v_<base36_timestamp>_<random_6chars>`
 * Example: `v_lx8k2f_a3b7c9`
 */
function generateVisitorId(): string {
  const timestamp = Date.now().toString(36);
  const random = Math.random().toString(36).substring(2, 8);
  return `v_${timestamp}_${random}`;
}

/**
 * Get or create an anonymous visitor ID.
 *
 * On first call, generates a new ID and persists it in a cookie.
 * On subsequent calls, returns the existing ID from the cookie.
 *
 * Safe to call on the server — returns a new ID each time (not persisted).
 *
 * @returns  Visitor ID string
 */
export function getVisitorId(): string {
  const existing = getCookie(COOKIE_NAME);
  if (existing) return existing;

  const id = generateVisitorId();

  if (typeof document !== 'undefined') {
    setCookie(COOKIE_NAME, id, COOKIE_DAYS);
  }

  return id;
}
