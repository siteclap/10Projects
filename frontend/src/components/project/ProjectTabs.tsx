'use client';

import { useCallback } from 'react';
import { cn } from '@/lib/utils/cn';
import { useScrollSpy } from '@/lib/hooks/use-scroll-spy';

interface Tab {
  id: string;
  label: string;
}

interface ProjectTabsProps {
  tabs: Tab[];
}

const HEADER_HEIGHT = 64;
const TAB_BAR_HEIGHT = 48;
const SCROLL_OFFSET = HEADER_HEIGHT + TAB_BAR_HEIGHT + 16;

export function ProjectTabs({ tabs }: ProjectTabsProps) {
  const sectionIds = tabs.map((tab) => tab.id);
  const activeId = useScrollSpy(sectionIds, HEADER_HEIGHT + TAB_BAR_HEIGHT);

  const scrollToSection = useCallback((id: string) => {
    const element = document.getElementById(id);
    if (!element) return;

    const top = element.getBoundingClientRect().top + window.scrollY - SCROLL_OFFSET;
    window.scrollTo({ top, behavior: 'smooth' });
  }, []);

  return (
    <div className="sticky top-[64px] z-40 border-b border-gray-200 bg-white shadow-sticky">
      <nav
        className="scrollbar-hide mx-auto flex max-w-container items-stretch gap-0 overflow-x-auto px-lg md:px-2xl"
        aria-label="Page sections"
      >
        {tabs.map((tab) => {
          const isActive = activeId === tab.id;

          return (
            <button
              key={tab.id}
              type="button"
              onClick={() => scrollToSection(tab.id)}
              className={cn(
                'relative shrink-0 whitespace-nowrap px-lg py-md text-sm font-medium transition-colors',
                'hover:text-brand-primary focus-visible:outline-none focus-visible:text-brand-primary',
                isActive
                  ? 'font-semibold text-brand-primary'
                  : 'text-gray-500'
              )}
              aria-current={isActive ? 'true' : undefined}
            >
              {tab.label}
              {isActive && (
                <span className="absolute bottom-0 left-0 right-0 h-[2px] bg-brand-primary" />
              )}
            </button>
          );
        })}
      </nav>
    </div>
  );
}
