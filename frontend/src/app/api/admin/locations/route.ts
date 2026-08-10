import { type NextRequest } from 'next/server';
import { prisma } from '@/lib/db';
import { getServerSession } from '@/lib/auth';

export async function GET() {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const locations = await prisma.location.findMany({
    where: { tenantId: session.user.tenantId },
    include: {
      _count: {
        select: { projects: true },
      },
    },
    orderBy: { name: 'asc' },
  });

  return Response.json(locations);
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

  const { name, slug } = body as { name: string; slug: string };

  if (!name || !slug) {
    return Response.json(
      { error: 'Missing required fields: name, slug' },
      { status: 400 }
    );
  }

  // Check for duplicate slug within tenant
  const existing = await prisma.location.findUnique({
    where: {
      tenantId_slug: {
        tenantId: session.user.tenantId,
        slug,
      },
    },
  });

  if (existing) {
    return Response.json(
      { error: 'A location with this slug already exists' },
      { status: 409 }
    );
  }

  const location = await prisma.location.create({
    data: {
      tenantId: session.user.tenantId,
      name,
      slug,
    },
    include: {
      _count: {
        select: { projects: true },
      },
    },
  });

  return Response.json(location, { status: 201 });
}

export async function DELETE(request: NextRequest) {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const { searchParams } = request.nextUrl;
  const id = searchParams.get('id');

  if (!id) {
    return Response.json(
      { error: 'Missing required query param: id' },
      { status: 400 }
    );
  }

  // Verify location belongs to tenant
  const existing = await prisma.location.findUnique({
    where: { id },
  });

  if (!existing) {
    return Response.json({ error: 'Location not found' }, { status: 404 });
  }

  if (existing.tenantId !== session.user.tenantId) {
    return Response.json({ error: 'Forbidden' }, { status: 403 });
  }

  await prisma.location.delete({
    where: { id },
  });

  return Response.json({ success: true });
}
