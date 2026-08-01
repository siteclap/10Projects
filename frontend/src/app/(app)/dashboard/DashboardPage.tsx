'use client';

import Link from 'next/link';
import { useAuth } from '@/components/auth/AuthProvider';
import { Container } from '@/components/layout/Container';
import { Section } from '@/components/layout/Section';
import { Card } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { DashboardSummary } from '@/components/dashboard/DashboardSummary';
import { ROUTES } from '@/lib/constants/routes';

export function DashboardPage() {
  const { isAuthenticated, customer } = useAuth();

  if (!isAuthenticated) {
    return (
      <Section>
        <Container size="narrow">
          <Card className="flex flex-col items-center justify-center px-lg py-16 text-center">
            <div className="flex h-[72px] w-[72px] items-center justify-center rounded-full bg-gray-100">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" className="text-gray-400" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
            </div>
            <h1 className="mt-6 text-xl font-bold text-gray-900">
              Sign in to access your dashboard
            </h1>
            <p className="mt-2 max-w-md text-sm text-gray-500">
              View your saved projects, assessment history, scheduled site visits,
              and personalised recommendations all in one place.
            </p>
            <div className="mt-6 flex flex-col items-center gap-3 sm:flex-row">
              <Button variant="primary" size="lg" asChild>
                <Link href={ROUTES.ASSESSMENT}>Start Free Assessment</Link>
              </Button>
              <Button variant="secondary" size="lg" asChild>
                <Link href={ROUTES.CITY}>Browse Projects</Link>
              </Button>
            </div>
          </Card>
        </Container>
      </Section>
    );
  }

  return (
    <Section>
      <Container>
        <div className="space-y-2">
          <h1 className="text-2xl font-bold text-gray-900">
            Welcome back{customer?.full_name ? `, ${customer.full_name.split(' ')[0]}` : ''}
          </h1>
          <p className="text-sm text-gray-500">
            Here is an overview of your property search activity.
          </p>
        </div>

        <div className="mt-8">
          <DashboardSummary />
        </div>
      </Container>
    </Section>
  );
}
