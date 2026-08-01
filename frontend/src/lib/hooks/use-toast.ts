'use client';

import {
  createContext,
  useContext,
  useState,
  useCallback,
  useRef,
  useEffect,
  createElement,
  type ReactNode,
} from 'react';

export type ToastType = 'info' | 'success' | 'error';

export interface ToastItem {
  id: string;
  message: string;
  type: ToastType;
}

interface ToastContextValue {
  toasts: ToastItem[];
  toast: (message: string, type?: ToastType) => void;
  dismiss: (id: string) => void;
}

const MAX_TOASTS = 5;
const AUTO_DISMISS_MS = 3000;

const ToastContext = createContext<ToastContextValue | null>(null);

let toastCounter = 0;

function generateToastId(): string {
  toastCounter += 1;
  return `toast-${toastCounter}-${Date.now()}`;
}

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<ToastItem[]>([]);
  const timersRef = useRef<Map<string, ReturnType<typeof setTimeout>>>(new Map());

  const dismiss = useCallback((id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
    const timer = timersRef.current.get(id);
    if (timer) {
      clearTimeout(timer);
      timersRef.current.delete(id);
    }
  }, []);

  const toast = useCallback(
    (message: string, type: ToastType = 'info') => {
      const id = generateToastId();
      const newToast: ToastItem = { id, message, type };

      setToasts((prev) => {
        const updated = [...prev, newToast];
        // Keep only the last MAX_TOASTS items
        if (updated.length > MAX_TOASTS) {
          const removed = updated.shift();
          if (removed) {
            const timer = timersRef.current.get(removed.id);
            if (timer) {
              clearTimeout(timer);
              timersRef.current.delete(removed.id);
            }
          }
        }
        return updated;
      });

      const timer = setTimeout(() => {
        dismiss(id);
      }, AUTO_DISMISS_MS);

      timersRef.current.set(id, timer);
    },
    [dismiss]
  );

  // Cleanup all timers on unmount
  useEffect(() => {
    return () => {
      for (const timer of timersRef.current.values()) {
        clearTimeout(timer);
      }
      timersRef.current.clear();
    };
  }, []);

  return createElement(
    ToastContext.Provider,
    { value: { toasts, toast, dismiss } },
    children
  );
}

export function useToast(): ToastContextValue {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast must be used within a ToastProvider');
  }
  return context;
}
