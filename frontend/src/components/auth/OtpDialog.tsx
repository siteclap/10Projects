'use client';

import { useState, useRef, useEffect, useCallback } from 'react';
import type { AuthToken } from '@/lib/types/customer';
import { sendOtp, verifyOtp } from '@/lib/api/auth';
import { cn } from '@/lib/utils/cn';
import { Button } from '@/components/ui/Button';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface OtpDialogProps {
  open: boolean;
  onSuccess: (token: AuthToken) => void;
  onClose: () => void;
}

type Step = 'phone' | 'otp';

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const RESEND_COOLDOWN = 30; // seconds

// ---------------------------------------------------------------------------
// Component
// ---------------------------------------------------------------------------

export function OtpDialog({ open, onSuccess, onClose }: OtpDialogProps) {
  const [step, setStep] = useState<Step>('phone');
  const [phone, setPhone] = useState('');
  const [otp, setOtp] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [resendTimer, setResendTimer] = useState(0);
  const [remainingAttempts, setRemainingAttempts] = useState<number | null>(
    null
  );

  const phoneInputRef = useRef<HTMLInputElement>(null);
  const otpInputRef = useRef<HTMLInputElement>(null);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);
  const dialogRef = useRef<HTMLDialogElement>(null);

  // Focus management
  useEffect(() => {
    if (!open) return;
    if (step === 'phone') {
      phoneInputRef.current?.focus();
    } else {
      otpInputRef.current?.focus();
    }
  }, [open, step]);

  // Open/close native dialog
  useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) return;

    if (open) {
      if (!dialog.open) dialog.showModal();
    } else {
      if (dialog.open) dialog.close();
    }
  }, [open]);

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

  // Reset state when dialog closes
  useEffect(() => {
    if (!open) {
      setStep('phone');
      setPhone('');
      setOtp('');
      setError(null);
      setLoading(false);
      setResendTimer(0);
      setRemainingAttempts(null);
    }
  }, [open]);

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
      const result = await sendOtp(phone);
      setRemainingAttempts(result.remaining_attempts);
      setStep('otp');
      setResendTimer(RESEND_COOLDOWN);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Failed to send OTP. Try again.'
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
      onSuccess(token);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Invalid OTP. Please try again.'
      );
    } finally {
      setLoading(false);
    }
  }, [phone, otp, onSuccess]);

  const handleResend = useCallback(async () => {
    if (resendTimer > 0) return;

    setLoading(true);
    setError(null);
    setOtp('');

    try {
      const result = await sendOtp(phone);
      setRemainingAttempts(result.remaining_attempts);
      setResendTimer(RESEND_COOLDOWN);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Failed to resend OTP.'
      );
    } finally {
      setLoading(false);
    }
  }, [phone, resendTimer]);

  const handleBack = useCallback(() => {
    setStep('phone');
    setOtp('');
    setError(null);
  }, []);

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

  // Handle backdrop click / Escape
  const handleDialogClick = useCallback(
    (e: React.MouseEvent<HTMLDialogElement>) => {
      if (e.target === dialogRef.current) {
        onClose();
      }
    },
    [onClose]
  );

  if (!open) return null;

  return (
    <dialog
      ref={dialogRef}
      className="fixed inset-0 z-50 m-auto w-full max-w-[400px] rounded-lg border-none bg-white p-0 shadow-hero backdrop:bg-gray-900/50"
      onClick={handleDialogClick}
      onCancel={onClose}
    >
      <div className="p-xl">
        {/* Header */}
        <div className="mb-xl flex items-center justify-between">
          <h2 className="text-h4 text-gray-900">
            {step === 'phone' ? 'Verify Your Phone' : 'Enter OTP'}
          </h2>
          <button
            type="button"
            onClick={onClose}
            className="flex h-[32px] w-[32px] items-center justify-center rounded-sm text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
            aria-label="Close dialog"
          >
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              aria-hidden="true"
            >
              <path d="M18 6 6 18" />
              <path d="m6 6 12 12" />
            </svg>
          </button>
        </div>

        {/* Phone Step */}
        {step === 'phone' && (
          <div className="flex flex-col gap-lg" onKeyDown={handleKeyDown}>
            <div className="flex flex-col gap-xs">
              <label
                htmlFor="otp-phone"
                className="text-sm font-medium text-gray-700"
              >
                Mobile Number
              </label>
              <div className="flex items-center gap-sm">
                <span className="flex h-[40px] items-center rounded-sm border border-gray-300 bg-gray-50 px-md text-sm text-gray-500">
                  +91
                </span>
                <input
                  ref={phoneInputRef}
                  id="otp-phone"
                  type="tel"
                  inputMode="numeric"
                  autoComplete="tel-national"
                  placeholder="Enter 10-digit number"
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
                />
              </div>
            </div>

            {error && (
              <p className="text-sm text-danger" role="alert">
                {error}
              </p>
            )}

            <Button
              onClick={handleSendOtp}
              loading={loading}
              disabled={phone.length !== 10}
              className="w-full"
            >
              Send OTP
            </Button>

            <p className="text-center text-caption text-gray-400">
              We&apos;ll send a one-time code. No spam ever.
            </p>
          </div>
        )}

        {/* OTP Step */}
        {step === 'otp' && (
          <div className="flex flex-col gap-lg" onKeyDown={handleKeyDown}>
            <p className="text-sm text-gray-600">
              Enter the 6-digit code sent to{' '}
              <span className="font-medium text-gray-900">+91 {phone}</span>
            </p>

            <div className="flex flex-col gap-xs">
              <label
                htmlFor="otp-code"
                className="text-sm font-medium text-gray-700"
              >
                OTP Code
              </label>
              <input
                ref={otpInputRef}
                id="otp-code"
                type="text"
                inputMode="numeric"
                autoComplete="one-time-code"
                placeholder="Enter 6-digit code"
                value={otp}
                onChange={handleOtpChange}
                maxLength={6}
                className={cn(
                  'h-[40px] w-full rounded-sm border px-md text-center text-h4 tracking-[0.3em] text-gray-900 placeholder:text-sm placeholder:tracking-normal placeholder:text-gray-400',
                  'transition-colors duration-150',
                  'focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-1',
                  error
                    ? 'border-danger focus:ring-danger'
                    : 'border-gray-300 hover:border-gray-400'
                )}
              />
            </div>

            {error && (
              <p className="text-sm text-danger" role="alert">
                {error}
              </p>
            )}

            <Button
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
                onClick={handleBack}
                className="text-sm text-gray-500 transition-colors hover:text-gray-700"
              >
                Change number
              </button>

              <button
                type="button"
                onClick={handleResend}
                disabled={resendTimer > 0}
                className={cn(
                  'text-sm transition-colors',
                  resendTimer > 0
                    ? 'cursor-not-allowed text-gray-400'
                    : 'text-brand-primary hover:text-brand-primary-dark'
                )}
              >
                {resendTimer > 0 ? `Resend in ${resendTimer}s` : 'Resend OTP'}
              </button>
            </div>

            {remainingAttempts !== null && remainingAttempts <= 2 && (
              <p className="text-center text-caption text-warning">
                {remainingAttempts} attempt{remainingAttempts !== 1 ? 's' : ''}{' '}
                remaining
              </p>
            )}
          </div>
        )}
      </div>
    </dialog>
  );
}
