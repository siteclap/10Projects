/**
 * Customer types for authentication and profile management.
 */

export interface Customer {
  id: number;
  uuid: string;
  full_name: string;
  email: string;
  phone: string;
  phone_verified: boolean;
  preferred_contact: 'whatsapp' | 'call' | 'email';
  created_at: string;
}

export interface AuthToken {
  token: string;
  expires_at: string;
  customer: Customer;
}
