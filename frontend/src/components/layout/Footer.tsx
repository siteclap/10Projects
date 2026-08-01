import Link from 'next/link';
import { ROUTES } from '@/lib/constants/routes';

const locationLinks = [
  { label: 'Kharghar', href: ROUTES.LOCATION('kharghar') },
  { label: 'Panvel', href: ROUTES.LOCATION('panvel') },
  { label: 'Ulwe', href: ROUTES.LOCATION('ulwe') },
  { label: 'Vashi', href: ROUTES.LOCATION('vashi') },
  { label: 'Airoli', href: ROUTES.LOCATION('airoli') },
  { label: 'Ghansoli', href: ROUTES.LOCATION('ghansoli') },
] as const;

const exploreLinks = [
  { label: 'How It Works', href: ROUTES.METHODOLOGY },
  { label: 'Scoring Methodology', href: ROUTES.METHODOLOGY },
  { label: 'Buyer Guides', href: ROUTES.GUIDES },
  { label: 'EMI Calculator', href: '/emi-calculator' },
  { label: 'Blog', href: '/blog' },
] as const;

const companyLinks = [
  { label: 'About Us', href: '/about' },
  { label: 'Contact', href: '/contact' },
  { label: 'Partner With Us', href: '/partners' },
  { label: 'Privacy Policy', href: '/privacy' },
  { label: 'Terms of Service', href: '/terms' },
] as const;

const socialLinks = [
  {
    label: 'Facebook',
    href: 'https://facebook.com/10projects',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
      </svg>
    ),
  },
  {
    label: 'Instagram',
    href: 'https://instagram.com/10projects',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
      </svg>
    ),
  },
  {
    label: 'LinkedIn',
    href: 'https://linkedin.com/company/10projects',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z" />
        <rect width="4" height="12" x="2" y="9" />
        <circle cx="4" cy="4" r="2" />
      </svg>
    ),
  },
  {
    label: 'YouTube',
    href: 'https://youtube.com/@10projects',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19.1c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.43z" />
        <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="white" />
      </svg>
    ),
  },
  {
    label: 'X',
    href: 'https://x.com/10projects',
    icon: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
      </svg>
    ),
  },
] as const;

function FooterLinkColumn({
  title,
  links,
}: {
  title: string;
  links: ReadonlyArray<{ label: string; href: string }>;
}) {
  return (
    <div>
      <h3 className="mb-lg text-sm font-semibold uppercase tracking-wider text-gray-400">
        {title}
      </h3>
      <ul className="flex flex-col gap-md">
        {links.map((link) => (
          <li key={link.href + link.label}>
            <Link
              href={link.href}
              className="text-sm text-gray-400 no-underline transition-colors hover:text-white hover:no-underline"
            >
              {link.label}
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
}

export function Footer() {
  return (
    <footer className="bg-gray-900 text-white" role="contentinfo">
      <div className="mx-auto max-w-container px-lg py-3xl md:px-2xl">
        <div className="grid grid-cols-1 gap-3xl sm:grid-cols-2 lg:grid-cols-4">
          {/* Brand column */}
          <div>
            <Link
              href={ROUTES.HOME}
              className="mb-lg inline-flex items-center gap-xs no-underline hover:no-underline"
              aria-label="10Projects home"
            >
              <span className="flex h-[32px] w-[32px] items-center justify-center rounded-sm bg-brand-primary text-base font-bold text-white">
                10
              </span>
              <span className="text-h4 text-white">Projects</span>
            </Link>
            <p className="mb-xl text-sm leading-relaxed text-gray-400">
              You need one right home. Not hundreds of options. We find the 10
              best-fit projects for you using AI-powered analysis.
            </p>
            <div className="flex items-center gap-md">
              {socialLinks.map((social) => (
                <a
                  key={social.label}
                  href={social.href}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex h-[36px] w-[36px] items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-800 hover:text-white"
                  aria-label={social.label}
                >
                  {social.icon}
                </a>
              ))}
            </div>
          </div>

          {/* Location links */}
          <FooterLinkColumn title="Locations" links={locationLinks} />

          {/* Explore links */}
          <FooterLinkColumn title="Explore" links={exploreLinks} />

          {/* Company links */}
          <FooterLinkColumn title="Company" links={companyLinks} />
        </div>
      </div>

      {/* Bottom bar */}
      <div className="border-t border-gray-800">
        <div className="mx-auto max-w-container px-lg py-lg md:px-2xl">
          <div className="flex flex-col items-center gap-sm text-center md:flex-row md:justify-between">
            <p className="text-caption text-gray-500">
              &copy; {new Date().getFullYear()} 10Projects. All rights reserved.
            </p>
            <p className="text-caption text-gray-500">
              Disclaimer: All project information is sourced from RERA and
              developer websites. Verify independently before making any
              decisions.
            </p>
          </div>
        </div>
      </div>
    </footer>
  );
}
