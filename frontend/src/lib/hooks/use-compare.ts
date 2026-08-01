'use client';

import { useState, useCallback, useEffect } from 'react';

const STORAGE_KEY = 'tp_compare_ids';
const MAX_COMPARE = 4;

function getStoredIds(): number[] {
  if (typeof window === 'undefined') return [];
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    if (Array.isArray(parsed)) {
      return parsed.filter((v): v is number => typeof v === 'number').slice(0, MAX_COMPARE);
    }
    return [];
  } catch {
    return [];
  }
}

function persistIds(ids: number[]): void {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
  } catch {
    // Storage full or unavailable — silently fail
  }
}

interface UseCompareReturn {
  ids: number[];
  add: (id: number) => void;
  remove: (id: number) => void;
  toggle: (id: number) => void;
  clear: () => void;
  has: (id: number) => boolean;
}

export function useCompare(): UseCompareReturn {
  const [ids, setIds] = useState<number[]>([]);

  // Hydrate from localStorage on mount
  useEffect(() => {
    setIds(getStoredIds());
  }, []);

  // Listen for storage events from other tabs
  useEffect(() => {
    function handleStorage(e: StorageEvent) {
      if (e.key === STORAGE_KEY) {
        setIds(getStoredIds());
      }
    }
    window.addEventListener('storage', handleStorage);
    return () => window.removeEventListener('storage', handleStorage);
  }, []);

  const add = useCallback((id: number) => {
    setIds((prev) => {
      if (prev.includes(id)) return prev;
      if (prev.length >= MAX_COMPARE) return prev;
      const next = [...prev, id];
      persistIds(next);
      return next;
    });
  }, []);

  const remove = useCallback((id: number) => {
    setIds((prev) => {
      const next = prev.filter((v) => v !== id);
      persistIds(next);
      return next;
    });
  }, []);

  const toggle = useCallback((id: number) => {
    setIds((prev) => {
      let next: number[];
      if (prev.includes(id)) {
        next = prev.filter((v) => v !== id);
      } else {
        if (prev.length >= MAX_COMPARE) return prev;
        next = [...prev, id];
      }
      persistIds(next);
      return next;
    });
  }, []);

  const clear = useCallback(() => {
    setIds([]);
    persistIds([]);
  }, []);

  const has = useCallback((id: number) => ids.includes(id), [ids]);

  return { ids, add, remove, toggle, clear, has };
}
