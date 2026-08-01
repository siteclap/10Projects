'use client';

import { useState, useRef, useEffect, useCallback } from 'react';
import type { AuthToken } from '@/lib/types/customer';
import { sendOtp, verifyOtp } from '@/lib/api/auth';
import { cn } from '@/lib/utils/cn';
import { Button } from '@/components/ui/Button';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface LoginGateProps {
  onVerified: (token: AuthToken) => void;
  message?: string;
}

type GateStep = 'phone' | 'otp';

const RESEND_COOLDOWN = 30;

// ---------------------------------------------------------------------------
// Component
// ---------------------------------------------------------------------------

export function LoginGate({
  onVerified,
  message = 'Verify your phone to see full results',
}: LoginGateProps) {
  const [step, setStep] = useState<GateStep>('phone');
  const [phone, setPhone] = useState('');
  const [otp, setOtp] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [resendTimer, setResendTimer] = useState(0);

  const phoneInputRef = useRef<HTMLInputElement>(null);
  const otpInputRef = useRef<HTMLInputElement>(null);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

  // Focus management
  useEffect(() => {
    if (step === 'phone') {
      phoneInputRef.current?.focus();
    } else {
      otpInputRef.current?.focus();
    }
  }, [step]);

  // Resend countdown timer
  useEffect(() => {
    if (resendTimer <= 0) {
      if (timerRef.current) clearInterval(timerRef.current);
      return;
    }

    timerRef.current = setInterval(() => {
      setResendTimer((prev) => {
        if (prev <= 1) {
          if (timerRef.current) clearInterval(timerRef.current);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => {
      if (timerRef.current) clearInterval(timerRef.current);
    };
  }, [resendTimer]);

  // -------------------------------------------------------------------
  // Handlers
  // -------------------------------------------------------------------

  const handlePhoneChange = useCallback(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      const value = e.target.value.replace(/\D/g, '').slice(0, 10);
      setPhone(value);
      setError(null);
    },
    []
  );

  const handleOtpChange = useCallback(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      const value = e.target.value.replace(/\D/g, '').slice(0, 6);
      setOtp(value);
      setError(null);
    },
    []
  );

  const handleSendOtp = useCallback(async () => {
    if (phone.length !== 10) {
      setError('Please enter a valid 10-digit mobile number');
      return;
    }

    setLoading(true);
    setError(null);

    try {
      await sendOtp(phone);
      setStep('otp');
      setResendTimer(RESEND_COOLDOWN);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Failed to send OTP.'
      );
    } finally {
      setLoading(false);
    }
  }, [phone]);

  const handleVerifyOtp = useCallback(async () => {
    if (otp.length !== 6) {
      setError('Please enter the 6-digit code');
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const token = await verifyOtp(phone, otp);
      onVerified(token);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Invalid OTP. Try again.'
      );
    } finally {
      setLoading(false);
    }
  }, [phone, otp, onVerified]);

  const handleResend = useCallback(async () => {
    if (resendTimer > 0) return;

    setLoading(true);
    setError(null);
    setOtp('');

    try {
      await sendOtp(phone);
      setResendTimer(RESEND_COOLDOWN);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Failed to resend OTP.'
      );
    } finally {
      setLoading(false);
    }
  }, [phone, resendTimer]);

  const handleKeyDown = useCallback(
    (e: React.KeyboardEvent) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        if (step === 'phone') {
          handleSendOtp();
        } else {
          handleVerifyOtp();
        }
      }
    },
    [step, handleSendOtp, handleVerifyOtp]
  );

  return (
    <div className="mx-auto w-full max-w-[360px]">
      <div className="rounded-md border border-gray-200 bg-white p-lg shadow-card">
        {/* Header with lock icon */}
        <div className="mb-lg flex items-center gap-sm">
          <div className="flex h-[36px] w-[36px] items-center justify-center rounded-full bg-brand-primary-bg">
            <svg
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-brand-primary"
              aria-hidden="true"
            >
              <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
              <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
          </div>
          <p className="text-sm font-medium text-gray-700">{message}</p>
        </div>

        {/* Phone Step */}
        {step === 'phone' && (
          <div className="flex flex-col gap-md" onKeyDown={handleKeyDown}>
            <div className="flex items-center gap-sm">
              <span className="flex h-[40px] items-center rounded-sm border border-gray-300 bg-gray-50 px-md text-sm text-gray-500">
                +91
              </span>
              <input
                ref={phoneInputRef}
                type="tel"
                inputMode="numeric"
                autoComplete="tel-national"
                placeholder="10-digit number"
                value={phone}
                onChange={handlePhoneChange}
                className={cn(
                  'h-[40px] flex-1 rounded-sm border px-md text-sm text-gray-900 placeholder:text-gray-400',
                  'transition-colors duration-150',
                  'focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-1',
                  error
                    ? 'border-danger focus:ring-danger'
                    : 'border-gray-300 hover:border-gray-400'
                )}
                aria-label="Mobile number"
              />
            </div>

            {error && (
              <p className="text-caption text-danger" role="alert">
                {error}
              </p>
            )}

            <Button
              size="sm"
              onClick={handleSendOtp}
              loading={loading}
              disabled={phone.length !== 10}
              className="w-full"
            >
              Send OTP
            </Button>

            <p className="text-center text-caption text-gray-400">
              No spam. Quick verification only.
            </p>
          </div>
        )}

        {/* OTP Step */}
        {step === 'otp' && (
          <div className="flex flex-col gap-md" onKeyDown={handleKeyDown}>
            <p className="text-caption text-gray-500">
              Code sent to +91 {phone}
            </p>

            <input
              ref={otpInputRef}
              type="text"
              inputMode="numeric"
              autoComplete="one-time-code"
              placeholder="6-digit code"
              value={otp}
              onChange={handleOtpChange}
              maxLength={6}
              className={cn(
                'h-[40px] w-full rounded-sm border px-md text-center text-base tracking-[0.3em] text-gray-900 placeholder:text-sm placeholder:tracking-normal placeholder:text-gray-400',
                'transition-colors duration-150',
                'focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-1',
                error
                  ? 'border-danger focus:ring-danger'
                  : 'border-gray-300 hover:border-gray-400'
              )}
              aria-label="OTP code"
            />

            {error && (
              <p className="text-caption text-danger" role="alert">
                {error}
              </p>
            )}

            <Button
              size="sm"
              onClick={handleVerifyOtp}
              loading={loading}
              disabled={otp.length !== 6}
              className="w-full"
            >
              Verify
            </Button>

            <div className="flex items-center justify-between">
              <button
                type="button"
                onClick={() => {
                  setStep('phone');
                  setOtp('');
                  setError(null);
                }}
                className="text-caption text-gray-500 transition-colors hover:text-gray-700"
              >
                Change number
              </button>
              <button
                type="button"
                onClick={handleResend}
                disabled={resendTimer > 0}
                className={cn(
                  'text-caption transition-colors',
                  resendTimer > 0
                    ? 'cursor-not-allowed text-gray-400'
                    : 'text-brand-primary hover:text-brand-primary-dark'
                )}
              >
                {resendTimer > 0
                  ? `Resend in ${resendTimer}s`
                  : 'Resend OTP'}
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
