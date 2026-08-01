'use client';

import Link from 'next/link';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { SavedProjectsList } from '@/components/dashboard/SavedProjectsList';
import { ROUTES } from '@/lib/constants/routes';

export function SavedProjectsPage() {
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
            <span className="text-gray-900">Saved Projects</span>
          </nav>
          <h1 className="text-2xl font-bold text-gray-900">Saved Projects</h1>
          <p className="text-sm text-gray-500">
            Projects you have bookmarked for comparison or future reference.
          </p>
        </div>

        <div className="mt-8">
          <SavedProjectsList />
        </div>
      </Container>
    </Section>
  );
}
