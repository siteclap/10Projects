'use client';

import { useCallback } from 'react';
import { cn } from '@/lib/utils/cn';
import { useToast } from '@/lib/hooks/use-toast';

interface ShareButtonProps {
  title: string;
  url: string;
  text?: string;
  className?: string;
}

export function ShareButton({
  title,
  url,
  text,
  className,
}: ShareButtonProps) {
  const { toast } = useToast();

  const handleShare = useCallback(async () => {
    const shareData = {
      title,
      text: text ?? `Check out ${title} on 10Projects`,
      url,
    };

    try {
      if (navigator.share) {
        await navigator.share(shareData);
      } else {
        await navigator.clipboard.writeText(url);
        toast('Link copied to clipboard', 'success');
      }
    } catch (error) {
      if (error instanceof Error && error.name !== 'AbortError') {
        try {
          await navigator.clipboard.writeText(url);
          toast('Link copied to clipboard', 'success');
        } catch {
          toast('Unable to share link', 'error');
        }
      }
    }
  }, [title, text, url, toast]);

  return (
    <button
      type="button"
      onClick={handleShare}
      className={cn(
        'inline-flex items-center gap-xs rounded-sm border border-gray-200 bg-white px-md py-sm text-sm font-medium text-gray-600 transition-colors hover:bg-gray-50',
        className
      )}
      aria-label={`Share ${title}`}
    >
      <svg
        width="16"
        height="16"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
        aria-hidden="true"
      >
        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
        <polyline points="16 6 12 2 8 6" />
        <line x1="12" x2="12" y1="2" y2="15" />
      </svg>
      Share
    </button>
  );
}
