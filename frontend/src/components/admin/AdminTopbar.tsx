'use client';

import { useSession, signOut } from 'next-auth/react';

interface AdminTopbarProps {
  title: string;
}

export function AdminTopbar({ title }: AdminTopbarProps) {
  const { data: session } = useSession();

  return (
    <header className="sticky top-0 z-30 flex h-[64px] items-center justify-between border-b border-gray-200 bg-white px-2xl">
      {/* Left: Page title */}
      <h1 className="text-h3 text-gray-900">{title}</h1>

      {/* Right: User info + Logout */}
      <div className="flex items-center gap-lg">
        {session?.user && (
          <>
            <div className="flex items-center gap-sm">
              {/* Avatar placeholder */}
              <div className="flex h-[32px] w-[32px] items-center justify-center rounded-full bg-brand-primary text-caption font-bold text-white">
                {session.user.name
                  ? session.user.name.charAt(0).toUpperCase()
                  : 'A'}
              </div>
              <div className="hidden flex-col sm:flex">
                <span className="text-sm font-medium text-gray-900">
                  {session.user.name || 'Admin'}
                </span>
                <span className="text-caption text-gray-500">
                  {(session.user as { role?: string }).role || 'Administrator'}
                </span>
              </div>
            </div>
            <span className="hidden rounded-full bg-brand-primary-pale px-sm py-xs text-caption font-medium text-brand-primary sm:inline-flex">
              {(session.user as { role?: string }).role || 'Admin'}
            </span>
          </>
        )}

        <button
          type="button"
          onClick={() => signOut({ callbackUrl: '/admin/login' })}
          className="flex items-center gap-xs rounded-sm border border-gray-200 px-md py-xs text-sm font-medium text-gray-600 transition-colors hover:bg-gray-50 hover:text-gray-900"
        >
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
          >
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" x2="9" y1="12" y2="12" />
          </svg>
          Logout
        </button>
      </div>
    </header>
  );
}
