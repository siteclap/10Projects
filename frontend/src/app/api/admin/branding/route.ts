import { type NextRequest } from 'next/server';
import { prisma } from '@/lib/db';
import { getServerSession } from '@/lib/auth';

export async function GET() {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const tenantId = session.user.tenantId;

  const [branding, contact, social, seo] = await Promise.all([
    prisma.tenantBranding.findUnique({ where: { tenantId } }),
    prisma.tenantContact.findUnique({ where: { tenantId } }),
    prisma.tenantSocial.findUnique({ where: { tenantId } }),
    prisma.tenantSeo.findUnique({ where: { tenantId } }),
  ]);

  return Response.json({
    branding,
    contact,
    social,
    seo,
  });
}

export async function PUT(request: NextRequest) {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const tenantId = session.user.tenantId;

  let body: Record<string, unknown>;

  try {
    body = await request.json();
  } catch {
    return Response.json({ error: 'Invalid JSON body' }, { status: 400 });
  }

  const { section, ...data } = body as {
    section: 'branding' | 'contact' | 'social' | 'seo';
    [key: string]: unknown;
  };

  if (!section) {
    return Response.json(
      { error: 'Missing required field: section (branding | contact | social | seo)' },
      { status: 400 }
    );
  }

  let result: unknown;

  switch (section) {
    case 'branding': {
      const {
        logoUrl,
        faviconUrl,
        tagline,
        primaryColor,
        primaryDark,
        primaryLight,
        accentColor,
        heroHeading,
        heroSubheading,
        ctaText,
        footerText,
      } = data as {
        logoUrl?: string;
        faviconUrl?: string;
        tagline?: string;
        primaryColor?: string;
        primaryDark?: string;
        primaryLight?: string;
        accentColor?: string;
        heroHeading?: string;
        heroSubheading?: string;
        ctaText?: string;
        footerText?: string;
      };

      result = await prisma.tenantBranding.upsert({
        where: { tenantId },
        update: {
          ...(logoUrl !== undefined && { logoUrl }),
          ...(faviconUrl !== undefined && { faviconUrl }),
          ...(tagline !== undefined && { tagline }),
          ...(primaryColor !== undefined && { primaryColor }),
          ...(primaryDark !== undefined && { primaryDark }),
          ...(primaryLight !== undefined && { primaryLight }),
          ...(accentColor !== undefined && { accentColor }),
          ...(heroHeading !== undefined && { heroHeading }),
          ...(heroSubheading !== undefined && { heroSubheading }),
          ...(ctaText !== undefined && { ctaText }),
          ...(footerText !== undefined && { footerText }),
        },
        create: {
          tenantId,
          logoUrl,
          faviconUrl,
          tagline,
          primaryColor: primaryColor || '#4B1CB0',
          primaryDark: primaryDark || '#3B1490',
          primaryLight: primaryLight || '#7C3AED',
          accentColor: accentColor || '#F59E0B',
          heroHeading,
          heroSubheading,
          ctaText: ctaText || 'Find My 10',
          footerText,
        },
      });
      break;
    }

    case 'contact': {
      const { phone, whatsapp, email, address, city, state } = data as {
        phone?: string;
        whatsapp?: string;
        email?: string;
        address?: string;
        city?: string;
        state?: string;
      };

      result = await prisma.tenantContact.upsert({
        where: { tenantId },
        update: {
          ...(phone !== undefined && { phone }),
          ...(whatsapp !== undefined && { whatsapp }),
          ...(email !== undefined && { email }),
          ...(address !== undefined && { address }),
          ...(city !== undefined && { city }),
          ...(state !== undefined && { state }),
        },
        create: {
          tenantId,
          phone,
          whatsapp,
          email,
          address,
          city,
          state,
        },
      });
      break;
    }

    case 'social': {
      const { facebook, instagram, linkedin, youtube, twitter } = data as {
        facebook?: string;
        instagram?: string;
        linkedin?: string;
        youtube?: string;
        twitter?: string;
      };

      result = await prisma.tenantSocial.upsert({
        where: { tenantId },
        update: {
          ...(facebook !== undefined && { facebook }),
          ...(instagram !== undefined && { instagram }),
          ...(linkedin !== undefined && { linkedin }),
          ...(youtube !== undefined && { youtube }),
          ...(twitter !== undefined && { twitter }),
        },
        create: {
          tenantId,
          facebook,
          instagram,
          linkedin,
          youtube,
          twitter,
        },
      });
      break;
    }

    case 'seo': {
      const { siteTitle, metaDescription, ogImageUrl, gaTrackingId } = data as {
        siteTitle?: string;
        metaDescription?: string;
        ogImageUrl?: string;
        gaTrackingId?: string;
      };

      result = await prisma.tenantSeo.upsert({
        where: { tenantId },
        update: {
          ...(siteTitle !== undefined && { siteTitle }),
          ...(metaDescription !== undefined && { metaDescription }),
          ...(ogImageUrl !== undefined && { ogImageUrl }),
          ...(gaTrackingId !== undefined && { gaTrackingId }),
        },
        create: {
          tenantId,
          siteTitle,
          metaDescription,
          ogImageUrl,
          gaTrackingId,
        },
      });
      break;
    }

    default:
      return Response.json(
        { error: 'Invalid section. Must be one of: branding, contact, social, seo' },
        { status: 400 }
      );
  }

  return Response.json(result);
}
