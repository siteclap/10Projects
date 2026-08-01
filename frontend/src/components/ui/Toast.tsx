'use client';

import { useEffect, useState, type ReactNode } from 'react';
import { cn } from '@/lib/utils/cn';
import { useToast, type ToastItem } from '@/lib/hooks/use-toast';

const borderColorMap: Record<string, string> = {
  info: 'border-l-info',
  success: 'border-l-success',
  error: 'border-l-danger',
};

const iconMap: Record<string, ReactNode> = {
  info: (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-info shrink-0" aria-hidden="true">
      <circle cx="12" cy="12" r="10" />
      <path d="M12 16v-4" />
      <path d="M12 8h.01" />
    </svg>
  ),
  success: (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-success shrink-0" aria-hidden="true">
      <circle cx="12" cy="12" r="10" />
      <path d="m9 12 2 2 4-4" />
    </svg>
  ),
  error: (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-danger shrink-0" aria-hidden="true">
      <circle cx="12" cy="12" r="10" />
      <path d="m15 9-6 6" />
      <path d="m9 9 6 6" />
    </svg>
  ),
};

function ToastNotification({
  item,
  onDismiss,
}: {
  item: ToastItem;
  onDismiss: (id: string) => void;
}) {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    // Trigger fade-in on mount
    const raf = requestAnimationFrame(() => setVisible(true));
    return () => cancelAnimationFrame(raf);
  }, []);

  function handleDismiss() {
    setVisible(false);
    // Wait for animation to finish before removing
    setTimeout(() => onDismiss(item.id), 200);
  }

  return (
    <div
      role="alert"
      className={cn(
        'flex items-center gap-md rounded-sm border border-gray-200 border-l-4 bg-white px-lg py-md shadow-dropdown transition-all duration-200',
        borderColorMap[item.type] ?? 'border-l-info',
        visible
          ? 'translate-x-0 opacity-100'
          : 'translate-x-4 opacity-0'
      )}
    >
      {iconMap[item.type]}
      <p className="flex-1 text-sm text-gray-800">{item.message}</p>
      <button
        type="button"
        onClick={handleDismiss}
        className="shrink-0 rounded-sm p-xs text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
        aria-label="Dismiss notification"
      >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
          <path d="M18 6 6 18" />
          <path d="m6 6 12 12" />
        </svg>
      </button>
    </div>
  );
}

export function ToastContainer() {
  const { toasts, dismiss } = useToast();

  if (toasts.length === 0) return null;

  return (
    <div
      aria-live="polite"
      aria-label="Notifications"
      className="fixed bottom-xl right-xl z-[80] flex w-[360px] max-w-[calc(100vw-32px)] flex-col gap-sm"
    >
      {toasts.map((item) => (
        <ToastNotification key={item.id} item={item} onDismiss={dismiss} />
      ))}
    </div>
  );
}
