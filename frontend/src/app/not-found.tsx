import Link from 'next/link';
import { Container } from '@/components/layout/Container';
import { Button } from '@/components/ui/Button';
import { ROUTES } from '@/lib/constants/routes';

export default function NotFound() {
  return (
    <main className="flex-1 flex items-center justify-center py-20">
      <Container size="narrow">
        <div className="text-center">
          <p className="text-8xl font-bold text-brand-primary sm:text-9xl">
            404
          </p>

          <h1 className="mt-6 text-3xl font-bold text-gray-900 sm:text-4xl">
            Page not found
          </h1>

          <p className="mt-4 text-lg text-gray-600">
            The page you&apos;re looking for doesn&apos;t exist or has been
            moved.
          </p>

          <div className="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
            <Button asChild size="lg">
              <Link href={ROUTES.HOME}>Go Home</Link>
            </Button>

            <Button asChild variant="secondary" size="lg">
              <Link href={ROUTES.ASSESSMENT}>Start Assessment</Link>
            </Button>
          </div>
        </div>
      </Container>
    </main>
  );
}
