'use client';

import {
  createContext,
  useContext,
  useState,
  useCallback,
  useEffect,
  type ReactNode,
} from 'react';
import type { Customer, AuthToken } from '@/lib/types/customer';
import { logout as apiLogout } from '@/lib/api/auth';

// ---------------------------------------------------------------------------
// Cookie helpers
// ---------------------------------------------------------------------------

const AUTH_COOKIE = 'tp_auth_token';

function setAuthCookie(token: string, expiresAt: string): void {
  const expires = new Date(expiresAt).toUTCString();
  document.cookie = `${AUTH_COOKIE}=${encodeURIComponent(token)};expires=${expires};path=/;SameSite=Lax`;
}

function clearAuthCookie(): void {
  document.cookie = `${AUTH_COOKIE}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;SameSite=Lax`;
}

function getAuthCookie(): string | null {
  if (typeof document === 'undefined') return null;
  const match = document.cookie.match(/(^| )tp_auth_token=([^;]+)/);
  return match ? decodeURIComponent(match[2]) : null;
}

// ---------------------------------------------------------------------------
// Customer storage (sessionStorage for the profile object)
// ---------------------------------------------------------------------------

const CUSTOMER_KEY = 'tp_customer';

function saveCustomer(customer: Customer): void {
  if (typeof window === 'undefined') return;
  sessionStorage.setItem(CUSTOMER_KEY, JSON.stringify(customer));
}

function loadCustomer(): Customer | null {
  if (typeof window === 'undefined') return null;
  const raw = sessionStorage.getItem(CUSTOMER_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw) as Customer;
  } catch {
    return null;
  }
}

function clearCustomer(): void {
  if (typeof window === 'undefined') return;
  sessionStorage.removeItem(CUSTOMER_KEY);
}

// ---------------------------------------------------------------------------
// Context
// ---------------------------------------------------------------------------

interface AuthContextValue {
  isAuthenticated: boolean;
  customer: Customer | null;
  token: string | null;
  login: (authToken: AuthToken) => void;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------

interface AuthProviderProps {
  children: ReactNode;
}

export function AuthProvider({ children }: AuthProviderProps) {
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [token, setToken] = useState<string | null>(null);

  // Restore auth state from cookie + sessionStorage on mount
  useEffect(() => {
    const savedToken = getAuthCookie();
    if (savedToken) {
      const savedCustomer = loadCustomer();
      if (savedCustomer) {
        setToken(savedToken);
        setCustomer(savedCustomer);
      } else {
        // Token exists but no customer data — clear stale cookie
        clearAuthCookie();
      }
    }
  }, []);

  const login = useCallback((authToken: AuthToken) => {
    setAuthCookie(authToken.token, authToken.expires_at);
    saveCustomer(authToken.customer);
    setToken(authToken.token);
    setCustomer(authToken.customer);
  }, []);

  const logout = useCallback(async () => {
    const currentToken = token || getAuthCookie();

    // Clear local state immediately for responsive UI
    setToken(null);
    setCustomer(null);
    clearAuthCookie();
    clearCustomer();

    // Invalidate token server-side (fire and forget)
    if (currentToken) {
      try {
        await apiLogout(currentToken);
      } catch {
        // Token invalidation failure is non-critical
      }
    }
  }, [token]);

  const value: AuthContextValue = {
    isAuthenticated: token !== null && customer !== null,
    customer,
    token,
    login,
    logout,
  };

  return <AuthContext value={value}>{children}</AuthContext>;
}

// ---------------------------------------------------------------------------
// Hook
// ---------------------------------------------------------------------------

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
