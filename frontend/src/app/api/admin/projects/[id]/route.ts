import { type NextRequest } from 'next/server';
import { prisma } from '@/lib/db';
import { getServerSession } from '@/lib/auth';

type RouteContext = { params: Promise<{ id: string }> };

export async function GET(
  _request: NextRequest,
  context: RouteContext
) {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const { id } = await context.params;

  const project = await prisma.project.findUnique({
    where: { id },
    include: {
      configurations: true,
      tags: { include: { tag: true } },
      gallery: { orderBy: { sortOrder: 'asc' } },
      location: true,
    },
  });

  if (!project) {
    return Response.json({ error: 'Project not found' }, { status: 404 });
  }

  if (project.tenantId !== session.user.tenantId) {
    return Response.json({ error: 'Forbidden' }, { status: 403 });
  }

  return Response.json(project);
}

export async function PUT(
  request: NextRequest,
  context: RouteContext
) {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const { id } = await context.params;

  // Verify project belongs to tenant
  const existing = await prisma.project.findUnique({
    where: { id },
  });

  if (!existing) {
    return Response.json({ error: 'Project not found' }, { status: 404 });
  }

  if (existing.tenantId !== session.user.tenantId) {
    return Response.json({ error: 'Forbidden' }, { status: 403 });
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
  } = body as {
    title?: string;
    slug?: string;
    developer?: string;
    locationId?: string | null;
    constructionStage?: string | null;
    expectedPossession?: string | null;
    reraNumber?: string | null;
    landParcel?: string | null;
    floors?: string | null;
    description?: string | null;
    highlights?: string | null;
    thumbnail?: string | null;
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
  };

  // Build update data with only provided fields
  const updateData: Record<string, unknown> = {};

  if (title !== undefined) updateData.title = title;
  if (slug !== undefined) updateData.slug = slug;
  if (developer !== undefined) updateData.developer = developer;
  if (locationId !== undefined) updateData.locationId = locationId;
  if (constructionStage !== undefined) updateData.constructionStage = constructionStage;
  if (expectedPossession !== undefined) updateData.expectedPossession = expectedPossession;
  if (reraNumber !== undefined) updateData.reraNumber = reraNumber;
  if (landParcel !== undefined) updateData.landParcel = landParcel;
  if (floors !== undefined) updateData.floors = floors;
  if (description !== undefined) updateData.description = description;
  if (highlights !== undefined) updateData.highlights = highlights;
  if (thumbnail !== undefined) updateData.thumbnail = thumbnail;
  if (priceMin !== undefined) updateData.priceMin = priceMin;
  if (priceMax !== undefined) updateData.priceMax = priceMax;
  if (amenities !== undefined) updateData.amenities = amenities;
  if (pros !== undefined) updateData.pros = pros;
  if (cons !== undefined) updateData.cons = cons;
  if (published !== undefined) updateData.published = published;
  if (featured !== undefined) updateData.featured = featured;

  // If configurations are provided, delete old ones and create new ones
  if (configurations !== undefined) {
    await prisma.configuration.deleteMany({
      where: { projectId: id },
    });

    updateData.configurations = {
      create: configurations.map((c) => ({
        configType: c.configType,
        carpetAreaSqft: c.carpetAreaSqft,
        basePrice: c.basePrice,
        totalPrice: c.totalPrice,
        inventoryTotal: c.inventoryTotal ?? 0,
        inventoryAvailable: c.inventoryAvailable ?? 0,
      })),
    };
  }

  const project = await prisma.project.update({
    where: { id },
    data: updateData,
    include: {
      configurations: true,
      tags: { include: { tag: true } },
      gallery: { orderBy: { sortOrder: 'asc' } },
      location: true,
    },
  });

  return Response.json(project);
}

export async function DELETE(
  _request: NextRequest,
  context: RouteContext
) {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const { id } = await context.params;

  // Verify project belongs to tenant
  const existing = await prisma.project.findUnique({
    where: { id },
  });

  if (!existing) {
    return Response.json({ error: 'Project not found' }, { status: 404 });
  }

  if (existing.tenantId !== session.user.tenantId) {
    return Response.json({ error: 'Forbidden' }, { status: 403 });
  }

  await prisma.project.delete({
    where: { id },
  });

  return Response.json({ success: true });
}
