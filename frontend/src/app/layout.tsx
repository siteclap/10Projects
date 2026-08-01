import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import './globals.css';

const inter = Inter({
  subsets: ['latin'],
  display: 'swap',
});

export const metadata: Metadata = {
  title: {
    default: '10Projects — Find the 10 Best-Fit Projects for You',
    template: '%s | 10Projects',
  },
  description:
    "India's first AI-powered real estate platform. Our scoring engine analyses 150+ projects across 20 categories to find the 10 best-fit matches for your lifestyle, budget, and priorities.",
  metadataBase: new URL(
    process.env.NEXT_PUBLIC_SITE_URL || 'https://10projects.com'
  ),
  openGraph: {
    type: 'website',
    locale: 'en_IN',
    siteName: '10Projects',
  },
  twitter: {
    card: 'summary_large_image',
  },
  robots: {
    index: true,
    follow: true,
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className={`${inter.className} h-full antialiased`}>
      <body className="min-h-full flex flex-col">{children}</body>
    </html>
  );
}
