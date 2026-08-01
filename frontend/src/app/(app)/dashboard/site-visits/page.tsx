import type { Metadata } from 'next';
import { SiteVisitsPage } from './SiteVisitsPage';

export const metadata: Metadata = {
  title: 'Site Visits — 10Projects',
  robots: { index: false, follow: false },
};

export default function Page() {
  return <SiteVisitsPage />;
}
