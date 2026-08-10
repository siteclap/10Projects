import NextAuth from 'next-auth';
import Credentials from 'next-auth/providers/credentials';
import { compare } from 'bcryptjs';
import { prisma } from '@/lib/db';

import type { Role } from '@/generated/prisma/client';

// Extend the built-in session/JWT types to include our custom fields
declare module 'next-auth' {
  interface Session {
    user: {
      id: string;
      email: string;
      name: string;
      role: Role;
      tenantId: string | null;
      superAdmin: boolean;
    };
  }

  interface User {
    id: string;
    email: string;
    name: string;
    role: Role;
    tenantId: string | null;
    superAdmin: boolean;
  }
}

declare module '@auth/core/jwt' {
  interface JWT {
    id: string;
    role: Role;
    tenantId: string | null;
    superAdmin: boolean;
  }
}

export const { handlers, auth, signIn, signOut } = NextAuth({
  providers: [
    Credentials({
      name: 'credentials',
      credentials: {
        email: { label: 'Email', type: 'email' },
        password: { label: 'Password', type: 'password' },
      },
      async authorize(credentials) {
        if (!credentials?.email || !credentials?.password) {
          return null;
        }

        const email = credentials.email as string;
        const password = credentials.password as string;

        const user = await prisma.user.findUnique({
          where: { email },
        });

        if (!user) {
          return null;
        }

        const isPasswordValid = await compare(password, user.password);

        if (!isPasswordValid) {
          return null;
        }

        return {
          id: user.id,
          email: user.email,
          name: user.name,
          role: user.role,
          tenantId: user.tenantId,
          superAdmin: user.superAdmin,
        };
      },
    }),
  ],
  session: {
    strategy: 'jwt',
  },
  pages: {
    signIn: '/admin/login',
  },
  callbacks: {
    async jwt({ token, user }) {
      if (user) {
        token.id = user.id as string;
        token.role = user.role;
        token.tenantId = user.tenantId;
        token.superAdmin = user.superAdmin;
      }
      return token;
    },
    async session({ session, token }) {
      session.user.id = token.id;
      session.user.role = token.role;
      session.user.tenantId = token.tenantId;
      session.user.superAdmin = token.superAdmin;
      return session;
    },
  },
});

/**
 * Helper to get the authenticated session for API routes.
 * Returns the session with user data including tenantId,
 * or null if not authenticated.
 */
export async function getServerSession() {
  const session = await auth();

  if (!session?.user) {
    return null;
  }

  return session;
}
