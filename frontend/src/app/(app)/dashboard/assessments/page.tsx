import type { Metadata } from 'next';
import { AssessmentsPage } from './AssessmentsPage';

export const metadata: Metadata = {
  title: 'Assessment History — 10Projects',
  robots: { index: false, follow: false },
};

export default function Page() {
  return <AssessmentsPage />;
}
