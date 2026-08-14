import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import './globals.css';
import { wpFetchSiteSettings } from '@/lib/wp-api';

const inter = Inter({
  subsets: ['latin'],
  display: 'swap',
});

export const metadata: Metadata = {
  title: {
    default: 'LeadMAAXX — Maximum Growth Solutions',
    template: '%s | LeadMAAXX',
  },
  description:
    "India's first AI-powered real estate platform. Our scoring engine analyses 150+ projects across 20 categories to find the 10 best-fit matches for your lifestyle, budget, and priorities.",
  metadataBase: new URL(
    process.env.NEXT_PUBLIC_SITE_URL || 'https://leadmaaxx.com'
  ),
  openGraph: {
    type: 'website',
    locale: 'en_IN',
    siteName: 'LeadMAAXX',
  },
  twitter: {
    card: 'summary_large_image',
  },
  robots: {
    index: true,
    follow: true,
  },
};

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const settings = await wpFetchSiteSettings();

  const cssVars = {
    '--brand-primary': settings.colors.primary,
    '--brand-primary-dark': settings.colors.primary_dark,
    '--brand-accent': settings.colors.accent,
    '--hero-bg': settings.colors.hero_bg,
  } as React.CSSProperties;

  return (
    <html lang="en" className={`${inter.className} h-full antialiased`} style={cssVars}>
      <head>
        <script
          dangerouslySetInnerHTML={{
            __html: `(function(){
  // Anti-copy deterrent — blocks casual right-click, view-source, and devtools shortcuts
  // Does NOT prevent determined developers (they can always use browser devtools)
  document.addEventListener('contextmenu',function(e){
    var t=e.target;
    if(t&&(t.tagName==='INPUT'||t.tagName==='TEXTAREA'||t.tagName==='SELECT'||t.isContentEditable))return;
    e.preventDefault();
  });
  document.addEventListener('keydown',function(e){
    // Block Ctrl+U (view source), Ctrl+S (save), Ctrl+Shift+I (devtools), F12
    if((e.ctrlKey||e.metaKey)&&e.key==='u'){e.preventDefault();return;}
    if((e.ctrlKey||e.metaKey)&&e.key==='s'){e.preventDefault();return;}
    if((e.ctrlKey||e.metaKey)&&e.shiftKey&&e.key==='I'){e.preventDefault();return;}
    if(e.key==='F12'){e.preventDefault();return;}
  });
  // Block image dragging
  document.addEventListener('dragstart',function(e){
    if(e.target&&e.target.tagName==='IMG')e.preventDefault();
  });
})();`,
          }}
        />
      </head>
      <body className="min-h-full flex flex-col">{children}</body>
    </html>
  );
}
