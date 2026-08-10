'use client';

import { useEffect } from 'react';
import Link from 'next/link';
import { cn } from '@/lib/utils/cn';
import { Button } from '@/components/ui/Button';
import { ROUTES } from '@/lib/constants/routes';

const navLinks = [
  { label: 'Projects', href: ROUTES.PROJECTS },
  { label: 'Locations', href: ROUTES.CITY },
  { label: 'Developers', href: ROUTES.DEVELOPERS },
  { label: 'Guides', href: ROUTES.GUIDES },
  { label: 'How It Works', href: ROUTES.METHODOLOGY },
  { label: 'EMI Calculator', href: '/emi-calculator' },
  { label: 'About', href: '/about' },
] as const;

interface MobileNavProps {
  open: boolean;
  onClose: () => void;
}

export function MobileNav({ open, onClose }: MobileNavProps) {
  useEffect(() => {
    if (open) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }

    return () => {
      document.body.style.overflow = '';
    };
  }, [open]);

  useEffect(() => {
    function handleKeyDown(e: KeyboardEvent) {
      if (e.key === 'Escape') {
        onClose();
      }
    }

    if (open) {
      document.addEventListener('keydown', handleKeyDown);
    }

    return () => document.removeEventListener('keydown', handleKeyDown);
  }, [open, onClose]);

  return (
    <>
      {/* Backdrop */}
      <div
        className={cn(
          'fixed inset-0 z-[55] bg-black/40 backdrop-blur-sm transition-opacity duration-300',
          open ? 'opacity-100' : 'pointer-events-none opacity-0'
        )}
        onClick={onClose}
        aria-hidden="true"
      />

      {/* Drawer */}
      <div
        role="dialog"
        aria-modal="true"
        aria-label="Mobile navigation"
        className={cn(
          'fixed right-0 top-0 z-[60] flex h-full w-[300px] max-w-[85vw] flex-col bg-white shadow-dropdown transition-transform duration-300 ease-in-out',
          open ? 'translate-x-0' : 'translate-x-full'
        )}
      >
        {/* Header */}
        <div className="flex h-[64px] items-center justify-between border-b border-gray-200 px-lg">
          <Link
            href={ROUTES.HOME}
            className="flex items-center gap-sm no-underline hover:no-underline"
            onClick={onClose}
            aria-label="10Projects home"
          >
            <img
              src="/logo.svg"
              alt=""
              width={32}
              height={32}
              className="h-[32px] w-[32px]"
            />
            <span className="text-h4 text-gray-900">Projects</span>
          </Link>

          <button
            type="button"
            className="flex h-[40px] w-[40px] items-center justify-center rounded-sm text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700"
            onClick={onClose}
            aria-label="Close menu"
          >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              aria-hidden="true"
            >
              <path d="M18 6 6 18" />
              <path d="m6 6 12 12" />
            </svg>
          </button>
        </div>

        {/* City selector */}
        <div className="border-b border-gray-200 px-lg py-lg">
          <button
            type="button"
            className="flex w-full items-center gap-sm rounded-sm border border-gray-200 px-md py-sm text-sm font-medium text-gray-700 transition-colors hover:border-gray-300 hover:bg-gray-50"
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
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            Navi Mumbai
            <svg
              width="14"
              height="14"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="ml-auto"
              aria-hidden="true"
            >
              <path d="m6 9 6 6 6-6" />
            </svg>
          </button>
        </div>

        {/* Nav links */}
        <nav className="flex-1 overflow-y-auto px-lg py-lg" aria-label="Mobile navigation">
          <ul className="flex flex-col gap-xs">
            {navLinks.map((link) => (
              <li key={link.href + link.label}>
                <Link
                  href={link.href}
                  className="flex rounded-sm px-md py-sm text-base font-medium text-gray-700 no-underline transition-colors hover:bg-gray-50 hover:text-gray-900 hover:no-underline"
                  onClick={onClose}
                >
                  {link.label}
                </Link>
              </li>
            ))}
          </ul>
        </nav>

        {/* Bottom actions */}
        <div className="border-t border-gray-200 px-lg py-lg">
          <div className="flex flex-col gap-sm">
            <Button variant="ghost" size="md" className="w-full justify-center" asChild>
              <Link href="/login" onClick={onClose}>
                Login
              </Link>
            </Button>
            <Button variant="primary" size="md" className="w-full justify-center" asChild>
              <Link href={ROUTES.ASSESSMENT} onClick={onClose}>
                Find My 10
              </Link>
            </Button>
          </div>
        </div>
      </div>
    </>
  );
}
