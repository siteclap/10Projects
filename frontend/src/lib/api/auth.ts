import { apiClient } from '@/lib/api/client';
import type { AuthToken } from '@/lib/types/customer';

/**
 * Response from POST /auth/otp/send
 */
interface SendOtpResponse {
  message: string;
  remaining_attempts: number;
}

/**
 * Send a one-time password to the given phone number.
 *
 * The API rate-limits OTP sends per phone number. The response includes
 * the remaining attempts so the UI can show appropriate messaging.
 *
 * @param phone  10-digit Indian mobile number (without +91 prefix)
 * @returns      Success message and remaining send attempts
 */
export async function sendOtp(phone: string): Promise<SendOtpResponse> {
  return apiClient<SendOtpResponse>('auth/otp/send', {
    method: 'POST',
    body: { phone },
  });
}

/**
 * Verify the OTP code sent to the given phone number.
 *
 * On success, returns an auth token and customer profile. The token
 * should be stored in a cookie for subsequent authenticated requests.
 *
 * @param phone  10-digit Indian mobile number (without +91 prefix)
 * @param otp    6-digit OTP code
 * @returns      Auth token with expiry and customer profile
 */
export async function verifyOtp(
  phone: string,
  otp: string
): Promise<AuthToken> {
  return apiClient<AuthToken>('auth/otp/verify', {
    method: 'POST',
    body: { phone, otp },
  });
}

/**
 * Log out the current user by invalidating the auth token server-side.
 *
 * @param token  The auth token to invalidate
 */
export async function logout(token: string): Promise<void> {
  return apiClient<void>('auth/logout', {
    method: 'POST',
    token,
  });
}
