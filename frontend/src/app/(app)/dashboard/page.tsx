import type { Metadata } from 'next';
import { DashboardPage } from './DashboardPage';

export const metadata: Metadata = {
  title: 'My Dashboard — 10Projects',
  robots: { index: false, follow: false },
};

export default function Page() {
  return <DashboardPage />;
}
