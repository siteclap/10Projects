import { ApiError } from '@/lib/types/api';
import type { ApiResponse, PaginatedResponse } from '@/lib/types/api';

const BASE_URL = process.env.NEXT_PUBLIC_WP_API_URL || 'http://localhost:8080/wp-json/tenprojects/v1';

interface ApiClientOptions extends Omit<RequestInit, 'body'> {
  body?: Record<string, unknown> | FormData;
  params?: Record<string, string | number | boolean | undefined>;
  token?: string;
  next?: NextFetchRequestConfig;
}

/**
 * Read the `tp_auth_token` cookie from `document.cookie` (client-side only).
 */
function getAuthTokenFromCookie(): string | null {
  if (typeof document === 'undefined') return null;
  const match = document.cookie.match(/(^| )tp_auth_token=([^;]+)/);
  return match ? decodeURIComponent(match[2]) : null;
}

/**
 * Build the full URL with query parameters.
 */
function buildUrl(endpoint: string, params?: Record<string, string | number | boolean | undefined>): string {
  const url = new URL(endpoint.startsWith('/') ? endpoint.slice(1) : endpoint, BASE_URL.endsWith('/') ? BASE_URL : BASE_URL + '/');

  if (params) {
    for (const [key, value] of Object.entries(params)) {
      if (value !== undefined) {
        url.searchParams.set(key, String(value));
      }
    }
  }

  return url.toString();
}

/**
 * Sleep for a given number of milliseconds.
 */
function sleep(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * Core API client that unwraps the WordPress REST API envelope.
 *
 * On `{ success: true, data: T }` returns `T`.
 * On `{ success: false }` throws `ApiError` with code, message, and status.
 * Handles 429 rate-limit responses with automatic retry.
 *
 * @param endpoint  Relative endpoint path (e.g. `projects`, `assessment/start`)
 * @param options   Fetch options extended with `params`, `token`, and `next`
 * @returns         Unwrapped response data of type `T`
 */
export async function apiClient<T>(endpoint: string, options: ApiClientOptions = {}): Promise<T> {
  const { body, params, token, next: nextOption, ...fetchOptions } = options;

  const url = buildUrl(endpoint, params);

  const headers: Record<string, string> = {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...(fetchOptions.headers as Record<string, string> || {}),
  };

  // Set auth token from explicit param, cookie, or skip
  const authToken = token || getAuthTokenFromCookie();
  if (authToken) {
    headers['X-TP-Auth-Token'] = authToken;
  }

  // Handle body serialization
  let serializedBody: BodyInit | undefined;
  if (body) {
    if (body instanceof FormData) {
      serializedBody = body;
      // Do not set Content-Type for FormData — browser sets it with boundary
    } else {
      headers['Content-Type'] = 'application/json';
      serializedBody = JSON.stringify(body);
    }
  }

  const fetchConfig: RequestInit & { next?: NextFetchRequestConfig } = {
    ...fetchOptions,
    headers,
    body: serializedBody,
  };

  if (nextOption) {
    fetchConfig.next = nextOption;
  }

  let response: Response;

  try {
    response = await fetch(url, fetchConfig);
  } catch (error) {
    throw new ApiError(
      error instanceof Error ? error.message : 'Network request failed',
      'NETWORK_ERROR',
      0
    );
  }

  // Handle 429 rate limiting with retry
  if (response.status === 429) {
    const retryAfter = response.headers.get('Retry-After');
    const waitMs = retryAfter ? parseInt(retryAfter, 10) * 1000 : 2000;

    await sleep(Math.min(waitMs, 30000));

    // Single retry — do not loop indefinitely
    return apiClient<T>(endpoint, options);
  }

  // Handle non-JSON responses (500, 502, etc.)
  const contentType = response.headers.get('Content-Type') || '';
  if (!contentType.includes('application/json')) {
    if (!response.ok) {
      throw new ApiError(
        `Server returned ${response.status} ${response.statusText}`,
        'SERVER_ERROR',
        response.status
      );
    }
    // Some endpoints may return 204 No Content
    return undefined as T;
  }

  const json: ApiResponse<T> = await response.json();

  if (!json.success) {
    throw new ApiError(
      json.message,
      json.code,
      response.status,
      json.data
    );
  }

  return json.data;
}

/**
 * Paginated API client that extracts pagination from response headers.
 *
 * WordPress exposes pagination via `X-WP-Total` and `X-WP-TotalPages` headers.
 * Falls back to the response envelope if headers are absent.
 *
 * @param endpoint  Relative endpoint path
 * @param options   Fetch options extended with `params`, `token`, and `next`
 * @returns         Object with `data`, `total`, and `totalPages`
 */
export async function apiClientPaginated<T>(
  endpoint: string,
  options: ApiClientOptions = {}
): Promise<PaginatedResponse<T>> {
  const { body, params, token, next: nextOption, ...fetchOptions } = options;

  const url = buildUrl(endpoint, params);

  const headers: Record<string, string> = {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...(fetchOptions.headers as Record<string, string> || {}),
  };

  const authToken = token || getAuthTokenFromCookie();
  if (authToken) {
    headers['X-TP-Auth-Token'] = authToken;
  }

  let serializedBody: BodyInit | undefined;
  if (body) {
    if (body instanceof FormData) {
      serializedBody = body;
    } else {
      headers['Content-Type'] = 'application/json';
      serializedBody = JSON.stringify(body);
    }
  }

  const fetchConfig: RequestInit & { next?: NextFetchRequestConfig } = {
    ...fetchOptions,
    headers,
    body: serializedBody,
  };

  if (nextOption) {
    fetchConfig.next = nextOption;
  }

  let response: Response;

  try {
    response = await fetch(url, fetchConfig);
  } catch (error) {
    throw new ApiError(
      error instanceof Error ? error.message : 'Network request failed',
      'NETWORK_ERROR',
      0
    );
  }

  // Handle 429 rate limiting with retry
  if (response.status === 429) {
    const retryAfter = response.headers.get('Retry-After');
    const waitMs = retryAfter ? parseInt(retryAfter, 10) * 1000 : 2000;

    await sleep(Math.min(waitMs, 30000));

    return apiClientPaginated<T>(endpoint, options);
  }

  const contentType = response.headers.get('Content-Type') || '';
  if (!contentType.includes('application/json')) {
    if (!response.ok) {
      throw new ApiError(
        `Server returned ${response.status} ${response.statusText}`,
        'SERVER_ERROR',
        response.status
      );
    }
    return { data: undefined as T, total: 0, totalPages: 0 };
  }

  const json: ApiResponse<T> = await response.json();

  if (!json.success) {
    throw new ApiError(
      json.message,
      json.code,
      response.status,
      json.data
    );
  }

  const total = parseInt(response.headers.get('X-WP-Total') || '0', 10);
  const totalPages = parseInt(response.headers.get('X-WP-TotalPages') || '0', 10);

  return {
    data: json.data,
    total,
    totalPages,
  };
}
