import Link from 'next/link';
import { ROUTES } from '@/lib/constants/routes';
import type { SiteSettings } from '@/lib/types/site-settings';

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
  { label: 'Disclaimer', href: '/disclaimer' },
] as const;

/* ---------- Social Icons ---------- */

function FacebookIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
    </svg>
  );
}

function InstagramIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
      <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
      <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
    </svg>
  );
}

function LinkedInIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z" />
      <rect width="4" height="12" x="2" y="9" />
      <circle cx="4" cy="4" r="2" />
    </svg>
  );
}

function YouTubeIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19.1c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.43z" />
      <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="white" />
    </svg>
  );
}

function XIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
    </svg>
  );
}

function WhatsAppIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
    </svg>
  );
}

const socialIconMap: Record<string, { icon: React.ReactNode; label: string; urlPrefix?: string }> = {
  facebook:  { icon: <FacebookIcon />,  label: 'Facebook' },
  instagram: { icon: <InstagramIcon />, label: 'Instagram' },
  linkedin:  { icon: <LinkedInIcon />,  label: 'LinkedIn' },
  youtube:   { icon: <YouTubeIcon />,   label: 'YouTube' },
  twitter:   { icon: <XIcon />,         label: 'X' },
  whatsapp:  { icon: <WhatsAppIcon />,  label: 'WhatsApp', urlPrefix: 'https://wa.me/' },
};

/* ---------- Subcomponents ---------- */

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

/* ---------- Footer ---------- */

interface FooterProps {
  logoUrl?: string;
  settings?: SiteSettings;
}

export function Footer({ logoUrl, settings }: FooterProps) {
  const siteName = settings?.site_name || '10Projects';
  const about = settings?.about || `You need one right home. Not hundreds of options. We find the 10 best-fit projects for you using AI-powered analysis.`;
  const logo = logoUrl || settings?.logo_dark || settings?.logo_light || '/logo.png';
  const phone = settings?.phone || '';
  const email = settings?.email || '';
  const address = settings?.address || '';
  const reraAgent = settings?.rera_agent || '';
  const reraLegalName = settings?.rera_legal_name || '';
  const social = settings?.social;

  // Build social links from settings, filtering out empty ones.
  const activeSocials = social
    ? Object.entries(social)
        .filter(([, url]) => url)
        .map(([key, url]) => {
          const info = socialIconMap[key];
          if (!info) return null;
          // For WhatsApp, convert phone number to wa.me link if it's not already a URL.
          const href =
            key === 'whatsapp' && !url.startsWith('http')
              ? `https://wa.me/${url.replace(/[^0-9]/g, '')}`
              : url;
          return { key, href, icon: info.icon, label: info.label };
        })
        .filter(Boolean)
    : [];

  return (
    <footer className="bg-gray-900 text-white" role="contentinfo">
      <div className="mx-auto max-w-container px-lg py-3xl md:px-2xl">
        <div className="grid grid-cols-1 gap-3xl sm:grid-cols-2 lg:grid-cols-4">
          {/* Brand column */}
          <div>
            <Link
              href={ROUTES.HOME}
              className="mb-lg inline-flex items-center gap-sm no-underline hover:no-underline"
              aria-label={`${siteName} home`}
            >
              <img
                src={logo}
                alt={siteName}
                height={40}
                className="h-[40px] w-auto brightness-0 invert"
              />
            </Link>

            {about && (
              <p className="mb-xl text-sm leading-relaxed text-gray-400">
                {about}
              </p>
            )}

            {/* Contact details */}
            {(phone || email || address) && (
              <div className="mb-xl flex flex-col gap-sm text-sm text-gray-400">
                {phone && (
                  <a href={`tel:${phone}`} className="transition-colors hover:text-white no-underline hover:no-underline">
                    {phone}
                  </a>
                )}
                {email && (
                  <a href={`mailto:${email}`} className="transition-colors hover:text-white no-underline hover:no-underline">
                    {email}
                  </a>
                )}
                {address && <p className="text-gray-500">{address}</p>}
              </div>
            )}

            {/* Social icons */}
            {activeSocials.length > 0 && (
              <div className="flex items-center gap-md">
                {activeSocials.map((s) =>
                  s ? (
                    <a
                      key={s.key}
                      href={s.href}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="flex h-[36px] w-[36px] items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-800 hover:text-white"
                      aria-label={s.label}
                    >
                      {s.icon}
                    </a>
                  ) : null
                )}
              </div>
            )}
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
              &copy; {new Date().getFullYear()} {siteName}. All rights reserved.
            </p>
            <p className="text-caption text-gray-500">
              {reraAgent
                ? `RERA Agent: ${reraLegalName ? reraLegalName + ' | ' : ''}${reraAgent}. `
                : ''}
              All project information is sourced from RERA and developer websites.
              Verify independently before making any decisions.
            </p>
          </div>
        </div>
      </div>
    </footer>
  );
}
