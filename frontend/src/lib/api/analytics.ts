import type { AnalyticsEvent } from '@/lib/types/analytics';

const BASE_URL = process.env.NEXT_PUBLIC_WP_API_URL || 'http://localhost:8080/wp-json/tenprojects/v1';

/**
 * Build the full analytics endpoint URL.
 */
function getTrackUrl(): string {
  const base = BASE_URL.endsWith('/') ? BASE_URL : BASE_URL + '/';
  return `${base}analytics/track`;
}

/**
 * Track an analytics event.
 *
 * On the client, uses `navigator.sendBeacon` for fire-and-forget delivery
 * that survives page unloads. Falls back to a standard `fetch` POST if
 * `sendBeacon` is unavailable or when running on the server.
 *
 * @param event  The analytics event with name, properties, and session context
 * @returns      Resolves when the event has been dispatched (not necessarily delivered)
 */
export async function trackEvent(event: AnalyticsEvent): Promise<void> {
  const url = getTrackUrl();
  const payload = JSON.stringify(event);

  // Client-side: prefer sendBeacon for reliability during page transitions
  if (
    typeof navigator !== 'undefined' &&
    typeof navigator.sendBeacon === 'function'
  ) {
    const blob = new Blob([payload], { type: 'application/json' });
    const sent = navigator.sendBeacon(url, blob);

    if (sent) return;
    // If sendBeacon fails (e.g. payload too large), fall through to fetch
  }

  // Server-side or sendBeacon fallback: standard fetch POST
  try {
    await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: payload,
      keepalive: true,
    });
  } catch {
    // Analytics failures are non-critical — silently swallow errors
  }
}
