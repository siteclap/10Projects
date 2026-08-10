'use client';

import { useState, useCallback } from 'react';
import { cn } from '@/lib/utils/cn';
import { useToast } from '@/lib/hooks/use-toast';
import { useCompare } from '@/lib/hooks/use-compare';

interface ProjectActionsProps {
  projectId: number | string;
  projectTitle: string;
  projectUrl: string;
}

export function ProjectActions({
  projectId,
  projectTitle,
  projectUrl,
}: ProjectActionsProps) {
  const [saved, setSaved] = useState(false);
  const { toast } = useToast();
  const { toggle, has } = useCompare();
  const isCompared = has(projectId);

  const handleSave = useCallback(() => {
    setSaved((prev) => {
      const next = !prev;
      toast(
        next ? `${projectTitle} saved to your list` : `${projectTitle} removed from saved`,
        next ? 'success' : 'info'
      );
      return next;
    });
  }, [projectTitle, toast]);

  const handleShare = useCallback(async () => {
    const shareData = {
      title: projectTitle,
      text: `Check out ${projectTitle} on 10Projects`,
      url: projectUrl,
    };

    try {
      if (navigator.share) {
        await navigator.share(shareData);
      } else {
        await navigator.clipboard.writeText(projectUrl);
        toast('Link copied to clipboard', 'success');
      }
    } catch (error) {
      // User cancelled share or clipboard failed
      if (error instanceof Error && error.name !== 'AbortError') {
        try {
          await navigator.clipboard.writeText(projectUrl);
          toast('Link copied to clipboard', 'success');
        } catch {
          toast('Unable to share link', 'error');
        }
      }
    }
  }, [projectTitle, projectUrl, toast]);

  const handleCompare = useCallback(() => {
    toggle(projectId);
    if (isCompared) {
      toast('Removed from comparison', 'info');
    } else {
      toast('Added to comparison', 'success');
    }
  }, [projectId, isCompared, toggle, toast]);

  const handleWhatsApp = useCallback(() => {
    const message = encodeURIComponent(
      `Hi, I'm interested in ${projectTitle}. ${projectUrl}`
    );
    window.open(`https://wa.me/919876543210?text=${message}`, '_blank');
  }, [projectTitle, projectUrl]);

  return (
    <div className="flex items-center gap-sm">
      {/* Save */}
      <button
        type="button"
        onClick={handleSave}
        className={cn(
          'flex items-center gap-xs rounded-sm border px-md py-sm text-sm font-medium transition-colors',
          saved
            ? 'border-danger/20 bg-danger-light text-danger'
            : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
        )}
        aria-label={saved ? 'Remove from saved' : 'Save project'}
      >
        <svg
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill={saved ? 'currentColor' : 'none'}
          stroke="currentColor"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
          aria-hidden="true"
        >
          <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
        </svg>
        Save
      </button>

      {/* Share */}
      <button
        type="button"
        onClick={handleShare}
        className="flex items-center gap-xs rounded-sm border border-gray-200 bg-white px-md py-sm text-sm font-medium text-gray-600 transition-colors hover:bg-gray-50"
        aria-label="Share project"
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

      {/* Compare */}
      <button
        type="button"
        onClick={handleCompare}
        className={cn(
          'flex items-center gap-xs rounded-sm border px-md py-sm text-sm font-medium transition-colors',
          isCompared
            ? 'border-brand-primary/20 bg-brand-primary-bg text-brand-primary'
            : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
        )}
        aria-label={isCompared ? 'Remove from comparison' : 'Add to comparison'}
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
          <line x1="18" x2="18" y1="20" y2="10" />
          <line x1="12" x2="12" y1="20" y2="4" />
          <line x1="6" x2="6" y1="20" y2="14" />
        </svg>
        Compare
      </button>

      {/* WhatsApp */}
      <button
        type="button"
        onClick={handleWhatsApp}
        className="flex items-center gap-xs rounded-sm bg-[#25D366] px-md py-sm text-sm font-medium text-white transition-colors hover:bg-[#20BD5A]"
        aria-label="Contact via WhatsApp"
      >
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
        </svg>
        WhatsApp
      </button>
    </div>
  );
}
