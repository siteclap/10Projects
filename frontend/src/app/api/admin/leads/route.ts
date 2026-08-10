import { type NextRequest } from 'next/server';
import { prisma } from '@/lib/db';
import { getServerSession } from '@/lib/auth';
import { type LeadStatus } from '@/generated/prisma/client';

export async function GET(request: NextRequest) {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const { searchParams } = request.nextUrl;
  const page = parseInt(searchParams.get('page') || '1', 10);
  const limit = parseInt(searchParams.get('limit') || '20', 10);
  const status = searchParams.get('status') as LeadStatus | null;
  const search = searchParams.get('search') || '';

  const skip = (page - 1) * limit;

  // Build the where clause
  const where: Record<string, unknown> = {
    tenantId: session.user.tenantId,
  };

  if (status) {
    where.status = status;
  }

  if (search) {
    where.OR = [
      { name: { contains: search, mode: 'insensitive' } },
      { phone: { contains: search, mode: 'insensitive' } },
      { email: { contains: search, mode: 'insensitive' } },
    ];
  }

  const [leads, total] = await Promise.all([
    prisma.lead.findMany({
      where,
      include: {
        project: {
          select: { title: true },
        },
      },
      orderBy: { createdAt: 'desc' },
      skip,
      take: limit,
    }),
    prisma.lead.count({ where }),
  ]);

  return Response.json({
    leads,
    pagination: {
      page,
      limit,
      total,
      totalPages: Math.ceil(total / limit),
    },
  });
}

export async function POST(request: NextRequest) {
  // This endpoint is used by frontend forms (no auth required for lead creation)
  let body: Record<string, unknown>;

  try {
    body = await request.json();
  } catch {
    return Response.json({ error: 'Invalid JSON body' }, { status: 400 });
  }

  const {
    tenantId,
    projectId,
    name,
    phone,
    email,
    intent,
    source,
  } = body as {
    tenantId: string;
    projectId?: string;
    name: string;
    phone: string;
    email?: string;
    intent: string;
    source?: string;
  };

  if (!tenantId || !name || !phone || !intent) {
    return Response.json(
      { error: 'Missing required fields: tenantId, name, phone, intent' },
      { status: 400 }
    );
  }

  // Verify tenant exists
  const tenant = await prisma.tenant.findUnique({
    where: { id: tenantId },
  });

  if (!tenant) {
    return Response.json({ error: 'Invalid tenant' }, { status: 400 });
  }

  // If projectId is provided, verify it belongs to the tenant
  if (projectId) {
    const project = await prisma.project.findUnique({
      where: { id: projectId },
    });

    if (!project || project.tenantId !== tenantId) {
      return Response.json({ error: 'Invalid project' }, { status: 400 });
    }
  }

  const lead = await prisma.lead.create({
    data: {
      tenantId,
      projectId: projectId || null,
      name,
      phone,
      email: email || null,
      intent,
      source: source || null,
      status: 'NEW',
    },
    include: {
      project: {
        select: { title: true },
      },
    },
  });

  return Response.json(lead, { status: 201 });
}
