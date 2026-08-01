import type { Metadata } from 'next';
import { SavedProjectsPage } from './SavedProjectsPage';

export const metadata: Metadata = {
  title: 'Saved Projects — 10Projects',
  robots: { index: false, follow: false },
};

export default function Page() {
  return <SavedProjectsPage />;
}
