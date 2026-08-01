'use client';

import Link from 'next/link';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { SiteVisitsList } from '@/components/dashboard/SiteVisitsList';
import { ROUTES } from '@/lib/constants/routes';

export function SiteVisitsPage() {
  return (
    <Section>
      <Container>
        <div className="space-y-1">
          <nav className="text-sm text-gray-400">
            <Link
              href={ROUTES.DASHBOARD}
              className="no-underline transition-colors hover:text-gray-600 hover:no-underline"
            >
              Dashboard
            </Link>
            <span className="mx-2">/</span>
            <span className="text-gray-900">Site Visits</span>
          </nav>
          <h1 className="text-2xl font-bold text-gray-900">Site Visits</h1>
          <p className="text-sm text-gray-500">
            Your scheduled and completed project site visits.
          </p>
        </div>

        <div className="mt-8">
          <SiteVisitsList />
        </div>
      </Container>
    </Section>
  );
}
