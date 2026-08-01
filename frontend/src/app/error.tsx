'use client';

import { useEffect } from 'react';
import Link from 'next/link';
import { Container } from '@/components/layout/Container';
import { Button } from '@/components/ui/Button';
import { ROUTES } from '@/lib/constants/routes';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <main className="flex-1 flex items-center justify-center py-20">
      <Container size="narrow">
        <div className="text-center">
          <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-danger/10">
            <svg
              className="h-8 w-8 text-danger"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              strokeWidth={1.5}
              stroke="currentColor"
              aria-hidden="true"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
              />
            </svg>
          </div>

          <h1 className="mt-6 text-3xl font-bold text-gray-900 sm:text-4xl">
            Something went wrong
          </h1>

          <p className="mt-4 text-lg text-gray-600">
            {error.message || 'An unexpected error occurred. Please try again.'}
          </p>

          {error.digest && (
            <p className="mt-2 text-sm text-gray-400">
              Error ID: {error.digest}
            </p>
          )}

          <div className="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
            <Button size="lg" onClick={() => reset()}>
              Try Again
            </Button>

            <Button asChild variant="secondary" size="lg">
              <Link href={ROUTES.HOME}>Go Home</Link>
            </Button>
          </div>
        </div>
      </Container>
    </main>
  );
}
