'use client';

import { useState, useEffect, useRef } from 'react';

/**
 * Tracks which section is currently visible in the viewport.
 *
 * Uses IntersectionObserver with a configurable rootMargin to account for
 * sticky headers and tab bars. Returns the id of the currently active section.
 *
 * @param sectionIds - Array of DOM element ids to observe
 * @param offset - Pixel offset from top for rootMargin (default: 140)
 * @returns The id of the section currently in view
 */
export function useScrollSpy(sectionIds: string[], offset: number = 140): string {
  const [activeId, setActiveId] = useState<string>(sectionIds[0] ?? '');
  const observerRef = useRef<IntersectionObserver | null>(null);

  useEffect(() => {
    if (sectionIds.length === 0) return;

    const elements = sectionIds
      .map((id) => document.getElementById(id))
      .filter((el): el is HTMLElement => el !== null);

    if (elements.length === 0) return;

    observerRef.current?.disconnect();

    const visibleSections = new Map<string, IntersectionObserverEntry>();

    observerRef.current = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (entry.isIntersecting) {
            visibleSections.set(entry.target.id, entry);
          } else {
            visibleSections.delete(entry.target.id);
          }
        }

        if (visibleSections.size > 0) {
          // Pick the section that appears first in the sectionIds order
          const firstVisible = sectionIds.find((id) => visibleSections.has(id));
          if (firstVisible) {
            setActiveId(firstVisible);
          }
        }
      },
      {
        rootMargin: `-${offset}px 0px -40% 0px`,
        threshold: 0,
      }
    );

    for (const el of elements) {
      observerRef.current.observe(el);
    }

    return () => {
      observerRef.current?.disconnect();
    };
  }, [sectionIds, offset]);

  return activeId;
}
