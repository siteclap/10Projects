'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { cn } from '@/lib/utils/cn';
import { Button } from '@/components/ui/Button';
import { ROUTES } from '@/lib/constants/routes';
import { MobileNav } from './MobileNav';

const navLinks = [
  { label: 'Locations', href: ROUTES.CITY },
  { label: 'How It Works', href: ROUTES.METHODOLOGY },
  { label: 'About', href: '/about' },
] as const;

export function Header() {
  const [scrolled, setScrolled] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);

  useEffect(() => {
    function handleScroll() {
      setScrolled(window.scrollY > 0);
    }

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <>
      <header
        className={cn(
          'sticky top-0 z-50 h-[64px] w-full bg-white transition-shadow duration-200',
          scrolled && 'shadow-sticky'
        )}
      >
        <div className="mx-auto flex h-full max-w-container items-center justify-between px-lg md:px-2xl">
          {/* Logo */}
          <Link
            href={ROUTES.HOME}
            className="flex items-center gap-xs no-underline hover:no-underline"
            aria-label="10Projects home"
          >
            <span className="flex h-[32px] w-[32px] items-center justify-center rounded-sm bg-brand-primary text-base font-bold text-white">
              10
            </span>
            <span className="text-h4 text-gray-900">Projects</span>
          </Link>

          {/* Center: City selector + Nav (hidden on mobile) */}
          <div className="hidden items-center gap-2xl md:flex">
            {/* City selector */}
            <button
              type="button"
              className="flex items-center gap-xs rounded-full border border-gray-200 px-md py-xs text-sm font-medium text-gray-700 transition-colors hover:border-gray-300 hover:bg-gray-50"
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
                aria-hidden="true"
              >
                <path d="m6 9 6 6 6-6" />
              </svg>
            </button>

            {/* Nav links */}
            <nav aria-label="Main navigation">
              <ul className="flex items-center gap-xl">
                {navLinks.map((link) => (
                  <li key={link.href}>
                    <Link
                      href={link.href}
                      className="text-sm font-medium text-gray-600 no-underline transition-colors hover:text-gray-900 hover:no-underline"
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </nav>
          </div>

          {/* Right: Actions */}
          <div className="flex items-center gap-sm">
            <div className="hidden md:flex md:items-center md:gap-sm">
              <Button variant="ghost" size="sm" asChild>
                <Link href="/login">Login</Link>
              </Button>
              <Button variant="primary" size="sm" asChild>
                <Link href={ROUTES.ASSESSMENT}>Find My 10</Link>
              </Button>
            </div>

            {/* Mobile hamburger */}
            <button
              type="button"
              className="flex h-[40px] w-[40px] items-center justify-center rounded-sm text-gray-700 transition-colors hover:bg-gray-100 md:hidden"
              onClick={() => setMobileOpen(true)}
              aria-label="Open menu"
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
                <line x1="4" x2="20" y1="6" y2="6" />
                <line x1="4" x2="20" y1="12" y2="12" />
                <line x1="4" x2="20" y1="18" y2="18" />
              </svg>
            </button>
          </div>
        </div>
      </header>

      <MobileNav open={mobileOpen} onClose={() => setMobileOpen(false)} />
    </>
  );
}
