'use client';

import { useState, useEffect } from 'react';
import { cn } from '@/lib/utils/cn';
import { useLeadForm } from './LeadFormContext';

const intentConfig = {
  best_price: {
    title: 'Get Best Price',
    subtitle: 'Our advisor will share the best available deal',
    cta: 'Get Best Price',
    fields: ['name', 'phone', 'email'] as const,
  },
  site_visit: {
    title: 'Book Free Site Visit',
    subtitle: 'Visit the property with our expert advisor',
    cta: 'Book Site Visit',
    fields: ['name', 'phone'] as const,
  },
  brochure: {
    title: 'Download Brochure',
    subtitle: 'Get floor plans, pricing & project details',
    cta: 'Download Now',
    fields: ['name', 'phone', 'email'] as const,
  },
  callback: {
    title: 'Request Callback',
    subtitle: 'We\'ll call you within 5 minutes',
    cta: 'Request Callback',
    fields: ['name', 'phone'] as const,
  },
  floor_plan: {
    title: 'Download Floor Plan',
    subtitle: 'Get detailed floor plans with dimensions & vastu direction',
    cta: 'Download Floor Plan',
    fields: ['name', 'phone'] as const,
  },
};

interface LeadFormModalProps {
  projectTitle: string;
}

export function LeadFormModal({ projectTitle }: LeadFormModalProps) {
  const { isOpen, intent, closeForm } = useLeadForm();
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [agreed, setAgreed] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  const config = intentConfig[intent];
  const hasEmail = (config.fields as readonly string[]).includes('email');

  // Lock body scroll when open
  useEffect(() => {
    if (!isOpen) return;
    const original = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => {
      document.body.style.overflow = original;
    };
  }, [isOpen]);

  // Close on Escape
  useEffect(() => {
    if (!isOpen) return;
    function handleKey(e: KeyboardEvent) {
      if (e.key === 'Escape') closeForm();
    }
    document.addEventListener('keydown', handleKey);
    return () => document.removeEventListener('keydown', handleKey);
  }, [isOpen, closeForm]);

  // Reset form on close
  useEffect(() => {
    if (!isOpen) {
      setSubmitted(false);
    }
  }, [isOpen]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!name.trim() || !phone.trim()) return;

    setSubmitting(true);

    try {
      const apiUrl = process.env.NEXT_PUBLIC_WP_API_URL || '/wp-json/tenprojects/v1';
      await fetch(`${apiUrl}/public/leads`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: name.trim(),
          phone: phone.trim(),
          email: email.trim() || undefined,
          lead_type: intent,
          source_page: typeof window !== 'undefined' ? window.location.href : '',
          honeypot: '',
        }),
      });

      setSubmitted(true);
      setName('');
      setPhone('');
      setEmail('');
      setAgreed(false);
    } catch {
      // Silently fail — still show success to not block user
      setSubmitted(true);
    } finally {
      setSubmitting(false);
    }
  }

  if (!isOpen) return null;

  return (
    <div
      className="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-lg backdrop-blur-sm"
      onClick={closeForm}
      role="dialog"
      aria-modal="true"
      aria-label={config.title}
    >
      <div
        className="relative w-full max-w-[420px] overflow-hidden rounded-lg bg-white shadow-xl"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Close button */}
        <button
          type="button"
          onClick={closeForm}
          className="absolute right-md top-md z-10 flex h-[32px] w-[32px] items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
          aria-label="Close"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
          </svg>
        </button>

        {submitted ? (
          /* Success state */
          <div className="flex flex-col items-center p-3xl text-center">
            <div className="flex h-[56px] w-[56px] items-center justify-center rounded-full bg-success/10">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" className="text-success" aria-hidden="true">
                <path d="M20 6 9 17l-5-5" />
              </svg>
            </div>
            <h3 className="mt-xl text-h3 font-semibold text-gray-900">Thank You!</h3>
            <p className="mt-sm text-sm text-gray-500">
              Our property advisor will contact you within 5 minutes regarding{' '}
              <span className="font-medium text-gray-700">{projectTitle}</span>.
            </p>
            <button
              type="button"
              onClick={closeForm}
              className="mt-xl inline-flex h-[40px] items-center justify-center rounded-sm bg-brand-primary px-xl text-sm font-medium text-white transition-colors hover:bg-brand-primary-dark"
            >
              Done
            </button>
          </div>
        ) : (
          /* Form */
          <>
            {/* Header */}
            <div className="bg-brand-primary px-xl pb-lg pt-xl">
              <h3 className="text-base font-semibold text-white">{config.title}</h3>
              <p className="mt-xs text-sm text-white/80">{config.subtitle}</p>
              <p className="mt-sm text-caption text-white/60">{projectTitle}</p>
            </div>

            <form onSubmit={handleSubmit} className="p-xl">
              <div className="flex flex-col gap-md">
                {/* Name */}
                <div>
                  <input
                    type="text"
                    placeholder="Your Name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    required
                    className="h-[44px] w-full rounded-sm border border-gray-300 px-lg text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                  />
                </div>

                {/* Phone */}
                <div className="flex items-center">
                  <span className="flex h-[44px] items-center rounded-l-sm border border-r-0 border-gray-300 bg-gray-50 px-md text-sm text-gray-500">
                    +91
                  </span>
                  <input
                    type="tel"
                    placeholder="Mobile Number"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value.replace(/\D/g, '').slice(0, 10))}
                    required
                    pattern="[0-9]{10}"
                    className="h-[44px] w-full rounded-r-sm border border-gray-300 px-lg text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                  />
                </div>

                {/* Email (conditional) */}
                {hasEmail && (
                  <div>
                    <input
                      type="email"
                      placeholder="Email Address"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      className="h-[44px] w-full rounded-sm border border-gray-300 px-lg text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                    />
                  </div>
                )}
              </div>

              {/* Consent */}
              <label className="mt-lg flex items-start gap-sm cursor-pointer">
                <input
                  type="checkbox"
                  checked={agreed}
                  onChange={(e) => setAgreed(e.target.checked)}
                  className="mt-[3px] h-[16px] w-[16px] shrink-0 rounded border-gray-300 text-brand-primary accent-brand-primary"
                />
                <span className="text-caption leading-snug text-gray-500">
                  I agree to be contacted by LeadMAAXX. We respect your privacy and will never spam you.
                </span>
              </label>

              {/* Submit */}
              <button
                type="submit"
                disabled={submitting || !name.trim() || phone.length < 10}
                className={cn(
                  'mt-xl flex h-[48px] w-full items-center justify-center rounded-sm text-base font-semibold text-white transition-colors',
                  submitting || !name.trim() || phone.length < 10
                    ? 'cursor-not-allowed bg-gray-300'
                    : 'bg-brand-primary hover:bg-brand-primary-dark'
                )}
              >
                {submitting ? (
                  <span className="flex items-center gap-sm">
                    <svg className="h-[18px] w-[18px] animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" className="opacity-25" />
                      <path d="M4 12a8 8 0 018-8" stroke="currentColor" strokeWidth="3" strokeLinecap="round" className="opacity-75" />
                    </svg>
                    Submitting...
                  </span>
                ) : (
                  config.cta
                )}
              </button>

              {/* Trust signals */}
              <div className="mt-lg flex items-center justify-center gap-lg">
                <div className="flex items-center gap-xs">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-success" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    <path d="m9 12 2 2 4-4" />
                  </svg>
                  <span className="text-caption text-gray-500">Verified</span>
                </div>
                <div className="flex items-center gap-xs">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-brand-primary" aria-hidden="true">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                  </svg>
                  <span className="text-caption text-gray-500">Secure</span>
                </div>
                <div className="flex items-center gap-xs">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-brand-primary" aria-hidden="true">
                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0" />
                    <line x1="12" x2="12" y1="2" y2="12" />
                  </svg>
                  <span className="text-caption text-gray-500">No Spam</span>
                </div>
              </div>

              {/* Social proof */}
              <p className="mt-md text-center text-caption text-gray-400">
                1,200+ buyers contacted this project this month
              </p>
            </form>
          </>
        )}
      </div>
    </div>
  );
}
