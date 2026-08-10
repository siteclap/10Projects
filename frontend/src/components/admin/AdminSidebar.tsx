'use client';

import { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { cn } from '@/lib/utils/cn';

interface NavItem {
  label: string;
  href: string;
  icon: React.ReactNode;
  children?: { label: string; href: string }[];
}

const navItems: NavItem[] = [
  {
    label: 'Dashboard',
    href: '/admin',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <rect x="3" y="3" width="7" height="7" />
        <rect x="14" y="3" width="7" height="7" />
        <rect x="3" y="14" width="7" height="7" />
        <rect x="14" y="14" width="7" height="7" />
      </svg>
    ),
  },
  {
    label: 'Landing Pages',
    href: '/admin/projects',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
        <polyline points="14 2 14 8 20 8" />
      </svg>
    ),
    children: [
      { label: 'All Projects', href: '/admin/projects' },
      { label: 'Add New', href: '/admin/projects/new' },
      { label: 'Project Tags', href: '/admin/tags' },
      { label: 'Locations', href: '/admin/locations' },
    ],
  },
  {
    label: 'Brand Setting',
    href: '/admin/branding',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <circle cx="13.5" cy="6.5" r="2.5" />
        <circle cx="17.5" cy="15.5" r="2.5" />
        <circle cx="8.5" cy="15.5" r="2.5" />
        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2" />
      </svg>
    ),
  },
  {
    label: 'Media',
    href: '/admin/media',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
        <circle cx="8.5" cy="8.5" r="1.5" />
        <polyline points="21 15 16 10 5 21" />
      </svg>
    ),
  },
  {
    label: 'Leads',
    href: '/admin/leads',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
      </svg>
    ),
  },
  {
    label: 'Settings',
    href: '/admin/settings',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <circle cx="12" cy="12" r="3" />
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
      </svg>
    ),
  },
];

export function AdminSidebar() {
  const pathname = usePathname();
  const [expandedMenus, setExpandedMenus] = useState<Record<string, boolean>>({
    'Landing Pages': true,
  });

  function toggleMenu(label: string) {
    setExpandedMenus((prev) => ({
      ...prev,
      [label]: !prev[label],
    }));
  }

  function isActive(href: string): boolean {
    if (href === '/admin') {
      return pathname === '/admin';
    }
    return pathname === href || pathname.startsWith(href + '/');
  }

  function isParentActive(item: NavItem): boolean {
    if (isActive(item.href)) return true;
    if (item.children) {
      return item.children.some((child) => isActive(child.href));
    }
    return false;
  }

  return (
    <aside className="fixed left-0 top-0 z-40 flex h-screen w-[260px] flex-col bg-[#1e293b]">
      {/* Logo area */}
      <div className="flex items-center gap-sm px-xl py-lg border-b border-white/10">
        <span className="text-h4 font-bold text-white tracking-heading">
          10Projects
        </span>
        <span className="rounded-sm bg-brand-primary px-sm py-xs text-caption font-medium text-white">
          Admin
        </span>
      </div>

      {/* Navigation */}
      <nav className="flex-1 overflow-y-auto py-lg" aria-label="Admin navigation">
        <ul className="flex flex-col gap-xs">
          {navItems.map((item) => {
            const hasChildren = item.children && item.children.length > 0;
            const expanded = expandedMenus[item.label] ?? false;
            const parentActive = isParentActive(item);

            return (
              <li key={item.label}>
                {hasChildren ? (
                  <>
                    {/* Parent with sub-menu */}
                    <button
                      type="button"
                      onClick={() => toggleMenu(item.label)}
                      className={cn(
                        'flex w-full items-center gap-md px-xl py-sm text-sm font-medium transition-colors duration-150',
                        parentActive
                          ? 'border-l-[3px] border-brand-primary bg-white/10 text-white'
                          : 'border-l-[3px] border-transparent text-gray-300 hover:bg-white/5 hover:text-white'
                      )}
                    >
                      <span className="flex-shrink-0">{item.icon}</span>
                      <span className="flex-1 text-left">{item.label}</span>
                      <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className={cn(
                          'flex-shrink-0 transition-transform duration-200',
                          expanded && 'rotate-90'
                        )}
                        aria-hidden="true"
                      >
                        <polyline points="9 18 15 12 9 6" />
                      </svg>
                    </button>

                    {/* Sub-menu */}
                    {expanded && (
                      <ul className="mt-xs">
                        {item.children!.map((child) => (
                          <li key={child.href}>
                            <Link
                              href={child.href}
                              className={cn(
                                'block py-sm pl-[56px] pr-xl text-sm no-underline transition-colors duration-150 hover:no-underline',
                                isActive(child.href)
                                  ? 'text-white font-medium bg-white/5'
                                  : 'text-gray-400 hover:text-white hover:bg-white/5'
                              )}
                            >
                              {child.label}
                            </Link>
                          </li>
                        ))}
                      </ul>
                    )}
                  </>
                ) : (
                  /* Regular nav item */
                  <Link
                    href={item.href}
                    className={cn(
                      'flex items-center gap-md px-xl py-sm text-sm font-medium no-underline transition-colors duration-150 hover:no-underline',
                      isActive(item.href)
                        ? 'border-l-[3px] border-brand-primary bg-white/10 text-white'
                        : 'border-l-[3px] border-transparent text-gray-300 hover:bg-white/5 hover:text-white'
                    )}
                  >
                    <span className="flex-shrink-0">{item.icon}</span>
                    <span>{item.label}</span>
                  </Link>
                )}
              </li>
            );
          })}
        </ul>
      </nav>

      {/* Footer */}
      <div className="border-t border-white/10 px-xl py-lg">
        <p className="text-caption text-gray-500">
          10Projects Admin v1.0
        </p>
      </div>
    </aside>
  );
}
