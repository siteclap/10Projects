'use client';

import Link from 'next/link';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { Button } from '@/components/ui/Button';
import { AssessmentHistory } from '@/components/dashboard/AssessmentHistory';
import { ROUTES } from '@/lib/constants/routes';

export function AssessmentsPage() {
  return (
    <Section>
      <Container>
        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div className="space-y-1">
            <nav className="text-sm text-gray-400">
              <Link
                href={ROUTES.DASHBOARD}
                className="no-underline transition-colors hover:text-gray-600 hover:no-underline"
              >
                Dashboard
              </Link>
              <span className="mx-2">/</span>
              <span className="text-gray-900">Assessments</span>
            </nav>
            <h1 className="text-2xl font-bold text-gray-900">Assessment History</h1>
            <p className="text-sm text-gray-500">
              Your past AI assessments and their personalised project matches.
            </p>
          </div>

          <Button variant="primary" size="md" asChild>
            <Link href={ROUTES.ASSESSMENT}>New Assessment</Link>
          </Button>
        </div>

        <div className="mt-8">
          <AssessmentHistory />
        </div>
      </Container>
    </Section>
  );
}
