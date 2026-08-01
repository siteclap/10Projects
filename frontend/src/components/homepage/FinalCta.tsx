import Link from 'next/link';
import { Button } from '@/components/ui/Button';
import { ROUTES } from '@/lib/constants/routes';

export function FinalCta() {
  return (
    <section className="bg-gradient-to-br from-brand-primary to-brand-primary-dark py-4xl">
      <div className="mx-auto max-w-container px-lg text-center md:px-2xl">
        <h2 className="text-h1 text-white">
          Ready to find your best-fit projects?
        </h2>
        <p className="mx-auto mt-lg max-w-narrow text-body-lg text-white/80">
          Answer a few quick questions and our AI will match you with the 10 best
          projects in Navi Mumbai. No sign-up required.
        </p>
        <div className="mt-2xl">
          <Button
            variant="secondary"
            size="lg"
            className="border-white/20 bg-white font-semibold text-brand-primary hover:bg-gray-50"
            asChild
          >
            <Link href={ROUTES.ASSESSMENT}>
              Find My 10 Best-Fit Projects
              <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="ml-xs"
                aria-hidden="true"
              >
                <path d="M5 12h14" />
                <path d="m12 5 7 7-7 7" />
              </svg>
            </Link>
          </Button>
        </div>
      </div>
    </section>
  );
}
