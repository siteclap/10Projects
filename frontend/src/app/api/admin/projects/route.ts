import { type NextRequest } from 'next/server';
import { prisma } from '@/lib/db';
import { getServerSession } from '@/lib/auth';

export async function GET(request: NextRequest) {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const { searchParams } = request.nextUrl;
  const search = searchParams.get('search') || '';
  const published = searchParams.get('published');

  // Build the where clause
  const where: Record<string, unknown> = {
    tenantId: session.user.tenantId,
  };

  if (search) {
    where.OR = [
      { title: { contains: search, mode: 'insensitive' } },
      { developer: { contains: search, mode: 'insensitive' } },
      { slug: { contains: search, mode: 'insensitive' } },
    ];
  }

  if (published === 'true') {
    where.published = true;
  } else if (published === 'false') {
    where.published = false;
  }

  const projects = await prisma.project.findMany({
    where,
    include: {
      location: true,
      tags: {
        include: {
          tag: true,
        },
      },
    },
    orderBy: { createdAt: 'desc' },
  });

  return Response.json(projects);
}

export async function POST(request: NextRequest) {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  let body: Record<string, unknown>;

  try {
    body = await request.json();
  } catch {
    return Response.json({ error: 'Invalid JSON body' }, { status: 400 });
  }

  const {
    title,
    slug,
    developer,
    locationId,
    constructionStage,
    expectedPossession,
    reraNumber,
    landParcel,
    floors,
    description,
    highlights,
    thumbnail,
    priceMin,
    priceMax,
    amenities,
    pros,
    cons,
    published,
    featured,
    configurations,
    tagIds,
  } = body as {
    title: string;
    slug: string;
    developer: string;
    locationId?: string;
    constructionStage?: string;
    expectedPossession?: string;
    reraNumber?: string;
    landParcel?: string;
    floors?: string;
    description?: string;
    highlights?: string;
    thumbnail?: string;
    priceMin?: number;
    priceMax?: number;
    amenities?: string[];
    pros?: string[];
    cons?: string[];
    published?: boolean;
    featured?: boolean;
    configurations?: Array<{
      configType: string;
      carpetAreaSqft: number;
      basePrice: number;
      totalPrice: number;
      inventoryTotal?: number;
      inventoryAvailable?: number;
    }>;
    tagIds?: string[];
  };

  if (!title || !slug || !developer) {
    return Response.json(
      { error: 'Missing required fields: title, slug, developer' },
      { status: 400 }
    );
  }

  // Check for duplicate slug within tenant
  const existing = await prisma.project.findUnique({
    where: {
      tenantId_slug: {
        tenantId: session.user.tenantId,
        slug,
      },
    },
  });

  if (existing) {
    return Response.json(
      { error: 'A project with this slug already exists' },
      { status: 409 }
    );
  }

  const project = await prisma.project.create({
    data: {
      tenantId: session.user.tenantId,
      title,
      slug,
      developer,
      locationId: locationId || null,
      constructionStage: constructionStage || null,
      expectedPossession: expectedPossession || null,
      reraNumber: reraNumber || null,
      landParcel: landParcel || null,
      floors: floors || null,
      description: description || null,
      highlights: highlights || null,
      thumbnail: thumbnail || null,
      priceMin: priceMin || 0,
      priceMax: priceMax || 0,
      amenities: amenities || [],
      pros: pros || [],
      cons: cons || [],
      published: published ?? false,
      featured: featured ?? false,
      configurations: configurations?.length
        ? {
            create: configurations.map((c) => ({
              configType: c.configType,
              carpetAreaSqft: c.carpetAreaSqft,
              basePrice: c.basePrice,
              totalPrice: c.totalPrice,
              inventoryTotal: c.inventoryTotal ?? 0,
              inventoryAvailable: c.inventoryAvailable ?? 0,
            })),
          }
        : undefined,
      tags: tagIds?.length
        ? {
            create: tagIds.map((tagId) => ({ tagId })),
          }
        : undefined,
    },
    include: {
      configurations: true,
      location: true,
      tags: { include: { tag: true } },
    },
  });

  return Response.json(project, { status: 201 });
}
