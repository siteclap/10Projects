import Link from 'next/link';
import { ROUTES } from '@/lib/constants/routes';
import { AuthProvider } from '@/components/auth/AuthProvider';
import { ToastProvider } from '@/lib/hooks/use-toast';
import { ToastContainer } from '@/components/ui/Toast';

export default function AppLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <AuthProvider>
      <ToastProvider>
      <div className="flex min-h-dvh flex-col">
        {/* Minimal header */}
        <header className="sticky top-0 z-50 border-b border-gray-100 bg-white">
          <div className="mx-auto flex h-[56px] max-w-assessment items-center justify-between px-lg">
            {/* Logo */}
            <Link
              href={ROUTES.HOME}
              className="flex items-center gap-xs no-underline hover:no-underline"
              aria-label="10Projects home"
            >
              <span className="flex h-[28px] w-[28px] items-center justify-center rounded-sm bg-brand-primary text-sm font-bold text-white">
                10
              </span>
              <span className="text-sm font-semibold text-gray-900">
                Projects
              </span>
            </Link>

            {/* Back to home link */}
            <Link
              href={ROUTES.HOME}
              className="flex items-center gap-xs text-sm text-gray-400 no-underline transition-colors hover:text-gray-600 hover:no-underline"
            >
              <svg
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <path d="m12 19-7-7 7-7" />
                <path d="M19 12H5" />
              </svg>
              Back to Home
            </Link>
          </div>
        </header>

        {/* Content */}
        <main className="flex w-full flex-1 flex-col">
          {children}
        </main>
        <ToastContainer />
      </div>
      </ToastProvider>
    </AuthProvider>
  );
}
