'use client';

import { useState } from 'react';

export function FinalCta() {
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!name.trim() || phone.length < 10) return;

    setSubmitting(true);
    // Simulate API call
    setTimeout(() => {
      setSubmitting(false);
      setSubmitted(true);
      setName('');
      setPhone('');
    }, 1000);
  }

  return (
    <section className="bg-gradient-to-br from-brand-primary to-brand-primary-dark py-4xl">
      <div className="mx-auto max-w-container px-lg md:px-2xl">
        <div className="mx-auto max-w-narrow">
          {submitted ? (
            /* Success state */
            <div className="flex flex-col items-center text-center">
              <div className="flex h-[56px] w-[56px] items-center justify-center rounded-full bg-white/20">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" className="text-white" aria-hidden="true">
                  <path d="M20 6 9 17l-5-5" />
                </svg>
              </div>
              <h2 className="mt-xl text-h2 text-white">Thank you!</h2>
              <p className="mt-sm text-sm text-white/70">
                Our property advisor will contact you within 5 minutes.
              </p>
            </div>
          ) : (
            <>
              <div className="text-center">
                <h2 className="text-h1 text-white">
                  Ready to find your best-fit projects?
                </h2>
                <p className="mx-auto mt-lg max-w-[560px] text-body-lg text-white/80">
                  Share your details and our property advisor will help you find
                  the best projects in Navi Mumbai.
                </p>
              </div>

              {/* Inline lead capture form */}
              <form
                onSubmit={handleSubmit}
                className="mt-2xl flex flex-col gap-md sm:flex-row sm:items-center"
              >
                {/* Name */}
                <input
                  type="text"
                  placeholder="Your Name"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  required
                  className="h-[52px] flex-1 rounded-sm bg-white/10 px-xl text-sm text-white placeholder:text-white/50 backdrop-blur-sm border border-white/20 focus:border-white/40 focus:outline-none focus:ring-1 focus:ring-white/30"
                />

                {/* Phone */}
                <div className="flex h-[52px] flex-1 items-center overflow-hidden rounded-sm border border-white/20 bg-white/10 backdrop-blur-sm focus-within:border-white/40 focus-within:ring-1 focus-within:ring-white/30">
                  <span className="flex h-full items-center border-r border-white/20 px-md text-sm text-white/60">
                    +91
                  </span>
                  <input
                    type="tel"
                    placeholder="Mobile Number"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value.replace(/\D/g, '').slice(0, 10))}
                    required
                    pattern="[0-9]{10}"
                    className="h-full w-full bg-transparent px-lg text-sm text-white placeholder:text-white/50 focus:outline-none"
                  />
                </div>

                {/* Submit */}
                <button
                  type="submit"
                  disabled={submitting || !name.trim() || phone.length < 10}
                  className="h-[52px] shrink-0 rounded-sm bg-white px-2xl text-sm font-semibold text-brand-primary transition-colors hover:bg-white/90 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                >
                  {submitting ? (
                    <span className="flex items-center gap-sm">
                      <svg className="h-[16px] w-[16px] animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" className="opacity-25" />
                        <path d="M4 12a8 8 0 018-8" stroke="currentColor" strokeWidth="3" strokeLinecap="round" className="opacity-75" />
                      </svg>
                      Submitting...
                    </span>
                  ) : (
                    'Get My Top 10 Projects'
                  )}
                </button>
              </form>

              {/* Trust signals */}
              <div className="mt-xl flex flex-wrap items-center justify-center gap-xl">
                <div className="flex items-center gap-xs">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-white/50" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    <path d="m9 12 2 2 4-4" />
                  </svg>
                  <span className="text-caption text-white/50">No Spam</span>
                </div>
                <div className="flex items-center gap-xs">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-white/50" aria-hidden="true">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                  </svg>
                  <span className="text-caption text-white/50">100% Secure</span>
                </div>
                <div className="flex items-center gap-xs">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-white/50" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <path d="m9 12 2 2 4-4" />
                  </svg>
                  <span className="text-caption text-white/50">Free Forever</span>
                </div>
              </div>
            </>
          )}
        </div>
      </div>
    </section>
  );
}
