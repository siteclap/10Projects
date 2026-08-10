import { type NextRequest } from 'next/server';
import { prisma } from '@/lib/db';
import { getServerSession } from '@/lib/auth';
import { type LeadStatus } from '@/generated/prisma/client';

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

  const lead = await prisma.lead.findUnique({
    where: { id },
    include: {
      project: {
        select: {
          id: true,
          title: true,
          slug: true,
          developer: true,
          thumbnail: true,
          priceMin: true,
          priceMax: true,
        },
      },
    },
  });

  if (!lead) {
    return Response.json({ error: 'Lead not found' }, { status: 404 });
  }

  if (lead.tenantId !== session.user.tenantId) {
    return Response.json({ error: 'Forbidden' }, { status: 403 });
  }

  return Response.json(lead);
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

  // Verify lead belongs to tenant
  const existing = await prisma.lead.findUnique({
    where: { id },
  });

  if (!existing) {
    return Response.json({ error: 'Lead not found' }, { status: 404 });
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

  const { status, notes } = body as {
    status?: LeadStatus;
    notes?: string;
  };

  const updateData: Record<string, unknown> = {};

  if (status !== undefined) updateData.status = status;
  if (notes !== undefined) updateData.notes = notes;

  const lead = await prisma.lead.update({
    where: { id },
    data: updateData,
    include: {
      project: {
        select: {
          id: true,
          title: true,
          slug: true,
          developer: true,
          thumbnail: true,
          priceMin: true,
          priceMax: true,
        },
      },
    },
  });

  return Response.json(lead);
}
