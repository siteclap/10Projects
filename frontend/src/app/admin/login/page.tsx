'use client';

import { useState, type FormEvent } from 'react';
import { signIn } from 'next-auth/react';
import { useRouter } from 'next/navigation';

export default function AdminLoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const result = await signIn('credentials', {
        email,
        password,
        redirect: false,
      });

      if (result?.error) {
        setError('Invalid email or password. Please try again.');
      } else if (result?.ok) {
        router.replace('/admin');
      }
    } catch {
      setError('An unexpected error occurred. Please try again.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-50">
      <div className="w-full max-w-[400px] px-lg">
        {/* Logo */}
        <div className="mb-3xl text-center">
          <div className="mb-lg inline-flex items-center gap-sm">
            <div className="flex h-[40px] w-[40px] items-center justify-center rounded-md bg-brand-primary">
              <span className="text-h4 font-bold text-white">10</span>
            </div>
            <span className="text-h3 font-bold text-gray-900">Projects</span>
          </div>
          <p className="text-sm text-gray-500">
            Sign in to your admin account
          </p>
        </div>

        {/* Login card */}
        <div className="rounded-md border border-gray-200 bg-white p-2xl shadow-card">
          <form onSubmit={handleSubmit} className="flex flex-col gap-xl">
            {/* Error message */}
            {error && (
              <div className="rounded-sm border border-danger bg-danger-light px-lg py-md text-sm text-danger">
                {error}
              </div>
            )}

            {/* Email */}
            <div className="flex flex-col gap-sm">
              <label
                htmlFor="email"
                className="text-sm font-medium text-gray-700"
              >
                Email Address
              </label>
              <input
                id="email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                autoComplete="email"
                placeholder="admin@10projects.com"
                className="h-[44px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 transition-colors focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-1 hover:border-gray-400"
              />
            </div>

            {/* Password */}
            <div className="flex flex-col gap-sm">
              <label
                htmlFor="password"
                className="text-sm font-medium text-gray-700"
              >
                Password
              </label>
              <input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                autoComplete="current-password"
                placeholder="Enter your password"
                className="h-[44px] w-full rounded-sm border border-gray-300 px-md text-sm text-gray-900 placeholder:text-gray-400 transition-colors focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-1 hover:border-gray-400"
              />
            </div>

            {/* Submit */}
            <button
              type="submit"
              disabled={loading}
              className="flex h-[44px] w-full items-center justify-center rounded-sm bg-brand-primary text-sm font-semibold text-white transition-colors hover:bg-brand-primary-dark focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none"
            >
              {loading ? (
                <svg
                  className="h-[20px] w-[20px] animate-spin"
                  viewBox="0 0 24 24"
                  fill="none"
                  aria-hidden="true"
                >
                  <circle
                    className="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    strokeWidth="4"
                  />
                  <path
                    className="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  />
                </svg>
              ) : (
                'Sign In'
              )}
            </button>
          </form>
        </div>

        <p className="mt-xl text-center text-caption text-gray-400">
          10Projects Admin Panel
        </p>
      </div>
    </div>
  );
}
