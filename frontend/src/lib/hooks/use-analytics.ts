'use client';

import { useEffect, useRef, useCallback } from 'react';
import { usePathname } from 'next/navigation';
import { trackEvent } from '@/lib/api/analytics';
import type { AnalyticsEventName, UtmParams } from '@/lib/types/analytics';

// ---------------------------------------------------------------------------
// Session ID management
// ---------------------------------------------------------------------------

const SESSION_ID_KEY = 'tp_session_id';

function getSessionId(): string {
  if (typeof sessionStorage === 'undefined') return '';

  let sessionId = sessionStorage.getItem(SESSION_ID_KEY);
  if (!sessionId) {
    sessionId = `${Date.now()}-${Math.random().toString(36).slice(2, 11)}`;
    sessionStorage.setItem(SESSION_ID_KEY, sessionId);
  }
  return sessionId;
}

// ---------------------------------------------------------------------------
// UTM capture
// ---------------------------------------------------------------------------

const UTM_STORAGE_KEY = 'tp_utm_params';

function captureUtmParams(): void {
  if (typeof window === 'undefined') return;

  // Only capture on first load — do not overwrite
  if (sessionStorage.getItem(UTM_STORAGE_KEY)) return;

  const params = new URLSearchParams(window.location.search);
  const utmKeys: Array<keyof UtmParams> = [
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_content',
    'utm_term',
    'gclid',
    'fbclid',
  ];

  const utm: UtmParams = {};
  let hasParams = false;

  for (const key of utmKeys) {
    const value = params.get(key);
    if (value) {
      utm[key] = value;
      hasParams = true;
    }
  }

  utm.referrer = document.referrer || undefined;
  utm.landing_page = window.location.pathname;

  if (hasParams || utm.referrer) {
    sessionStorage.setItem(UTM_STORAGE_KEY, JSON.stringify(utm));
  }
}

function getStoredUtmParams(): UtmParams | null {
  if (typeof sessionStorage === 'undefined') return null;
  const raw = sessionStorage.getItem(UTM_STORAGE_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw) as UtmParams;
  } catch {
    return null;
  }
}

// ---------------------------------------------------------------------------
// Page type detection
// ---------------------------------------------------------------------------

function detectPageType(pathname: string): string {
  if (pathname === '/') return 'homepage';
  if (pathname === '/start') return 'assessment';
  if (pathname.startsWith('/results/')) return 'results';
  if (pathname === '/compare') return 'comparison';
  if (pathname === '/dashboard') return 'dashboard';
  if (pathname.startsWith('/dashboard/')) return 'dashboard';
  if (pathname.startsWith('/developers/')) return 'developer';
  if (pathname === '/developers') return 'developers';
  if (pathname.startsWith('/guides/')) return 'guide';
  if (pathname === '/guides') return 'guides';
  if (pathname === '/methodology') return 'methodology';
  if (pathname === '/navi-mumbai') return 'city';

  // /navi-mumbai/{location}/{project}
  const projectMatch = pathname.match(/^\/navi-mumbai\/[^/]+\/[^/]+$/);
  if (projectMatch) return 'project';

  // /navi-mumbai/{location}
  const locationMatch = pathname.match(/^\/navi-mumbai\/[^/]+$/);
  if (locationMatch) return 'location';

  return 'other';
}

// ---------------------------------------------------------------------------
// Scroll depth thresholds
// ---------------------------------------------------------------------------

const SCROLL_THRESHOLDS = [25, 50, 75, 100] as const;
const SCROLL_TRACKED_KEY = 'tp_scroll_tracked';

function getTrackedThresholds(): Set<number> {
  if (typeof sessionStorage === 'undefined') return new Set();
  const raw = sessionStorage.getItem(SCROLL_TRACKED_KEY);
  if (!raw) return new Set();
  try {
    return new Set(JSON.parse(raw) as number[]);
  } catch {
    return new Set();
  }
}

function saveTrackedThreshold(threshold: number): void {
  if (typeof sessionStorage === 'undefined') return;
  const tracked = getTrackedThresholds();
  tracked.add(threshold);
  sessionStorage.setItem(SCROLL_TRACKED_KEY, JSON.stringify([...tracked]));
}

// ---------------------------------------------------------------------------
// Hook
// ---------------------------------------------------------------------------

/**
 * Analytics tracking hook for client-side event collection.
 *
 * Provides a `track` function for manual event dispatch and automatically
 * handles page views, scroll depth, session duration, and UTM capture.
 *
 * @returns Object with `track` function for manual event dispatch
 */
export function useAnalytics() {
  const pathname = usePathname();
  const sessionStartRef = useRef<number>(Date.now());
  const scrollHandlerRef = useRef<(() => void) | null>(null);

  /**
   * Track a named event with optional properties.
   *
   * Automatically attaches the current page URL, referrer, and session ID.
   */
  const track = useCallback(
    (eventName: AnalyticsEventName, properties?: Record<string, string | number | boolean>) => {
      const sessionId = getSessionId();
      const utm = getStoredUtmParams();

      const mergedProperties: Record<string, string | number | boolean> = {
        ...properties,
      };

      // Attach UTM params if available
      if (utm) {
        if (utm.utm_source) mergedProperties.utm_source = utm.utm_source;
        if (utm.utm_medium) mergedProperties.utm_medium = utm.utm_medium;
        if (utm.utm_campaign) mergedProperties.utm_campaign = utm.utm_campaign;
        if (utm.utm_content) mergedProperties.utm_content = utm.utm_content;
        if (utm.utm_term) mergedProperties.utm_term = utm.utm_term;
        if (utm.gclid) mergedProperties.gclid = utm.gclid;
        if (utm.fbclid) mergedProperties.fbclid = utm.fbclid;
      }

      trackEvent({
        event_name: eventName,
        properties: Object.keys(mergedProperties).length > 0 ? mergedProperties : undefined,
        page_url: typeof window !== 'undefined' ? window.location.href : undefined,
        referrer: typeof document !== 'undefined' ? document.referrer || undefined : undefined,
        session_id: sessionId || undefined,
      });
    },
    []
  );

  // Capture UTM params on first mount
  useEffect(() => {
    captureUtmParams();
  }, []);

  // Auto-track page_view on pathname change
  useEffect(() => {
    const pageType = detectPageType(pathname);

    track('page_view', {
      page_type: pageType,
      page_path: pathname,
    });

    // Reset scroll tracking for new page
    if (typeof sessionStorage !== 'undefined') {
      sessionStorage.removeItem(SCROLL_TRACKED_KEY);
    }
  }, [pathname, track]);

  // Scroll depth tracking
  useEffect(() => {
    function handleScroll() {
      const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
      if (scrollHeight <= 0) return;

      const scrollPercent = Math.round((window.scrollY / scrollHeight) * 100);
      const tracked = getTrackedThresholds();

      for (const threshold of SCROLL_THRESHOLDS) {
        if (scrollPercent >= threshold && !tracked.has(threshold)) {
          saveTrackedThreshold(threshold);
          track('scroll_depth', {
            depth: threshold,
            page_path: pathname,
          });
        }
      }
    }

    // Clean up previous handler
    if (scrollHandlerRef.current) {
      window.removeEventListener('scroll', scrollHandlerRef.current);
    }

    // Throttle scroll events to ~100ms intervals
    let ticking = false;
    const throttledHandler = () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        handleScroll();
        ticking = false;
      });
    };

    scrollHandlerRef.current = throttledHandler;
    window.addEventListener('scroll', throttledHandler, { passive: true });

    return () => {
      window.removeEventListener('scroll', throttledHandler);
      scrollHandlerRef.current = null;
    };
  }, [pathname, track]);

  // Session duration tracking on unload
  useEffect(() => {
    function handleUnload() {
      const durationMs = Date.now() - sessionStartRef.current;
      const durationSeconds = Math.round(durationMs / 1000);

      const sessionId = getSessionId();
      const payload = JSON.stringify({
        event_name: 'session_duration',
        properties: {
          duration_seconds: durationSeconds,
          page_path: pathname,
        },
        page_url: window.location.href,
        session_id: sessionId || undefined,
      });

      // Use sendBeacon for reliable delivery during page unload
      const BASE_URL = process.env.NEXT_PUBLIC_WP_API_URL || 'http://localhost:8080/wp-json/tenprojects/v1';
      const base = BASE_URL.endsWith('/') ? BASE_URL : BASE_URL + '/';
      const url = `${base}analytics/track`;

      if (navigator.sendBeacon) {
        const blob = new Blob([payload], { type: 'application/json' });
        navigator.sendBeacon(url, blob);
      }
    }

    window.addEventListener('pagehide', handleUnload);
    window.addEventListener('beforeunload', handleUnload);

    return () => {
      window.removeEventListener('pagehide', handleUnload);
      window.removeEventListener('beforeunload', handleUnload);
    };
  }, [pathname]);

  return { track };
}
