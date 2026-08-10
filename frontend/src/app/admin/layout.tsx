'use client';

import { type ReactNode } from 'react';
import { SessionProvider, useSession } from 'next-auth/react';
import { useRouter, usePathname } from 'next/navigation';
import { useEffect } from 'react';
import { AdminSidebar } from '@/components/admin/AdminSidebar';

/**
 * Inner layout that checks authentication.
 * Redirects to /admin/login if not authenticated.
 */
function AdminAuthGuard({ children }: { children: ReactNode }) {
  const { status } = useSession();
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    if (status === 'unauthenticated' && pathname !== '/admin/login') {
      router.replace('/admin/login');
    }
  }, [status, router, pathname]);

  // Login page does not use the sidebar/topbar shell
  if (pathname === '/admin/login') {
    return <>{children}</>;
  }

  // Show loading state while checking auth
  if (status === 'loading') {
    return (
      <div className="flex h-screen items-center justify-center bg-gray-50">
        <div className="flex flex-col items-center gap-lg">
          <div className="h-[40px] w-[40px] animate-spin rounded-full border-[3px] border-gray-200 border-t-brand-primary" />
          <p className="text-sm text-gray-500">Loading...</p>
        </div>
      </div>
    );
  }

  // If not authenticated, show nothing (redirect will happen)
  if (status === 'unauthenticated') {
    return null;
  }

  return (
    <div className="flex min-h-screen bg-gray-50">
      {/* Fixed sidebar */}
      <AdminSidebar />

      {/* Main content area, offset by sidebar width */}
      <main className="ml-[260px] flex-1">
        {children}
      </main>
    </div>
  );
}

/**
 * Admin layout wraps all /admin/* routes with SessionProvider and auth guard.
 */
export default function AdminLayout({ children }: { children: ReactNode }) {
  return (
    <SessionProvider>
      <AdminAuthGuard>{children}</AdminAuthGuard>
    </SessionProvider>
  );
}
