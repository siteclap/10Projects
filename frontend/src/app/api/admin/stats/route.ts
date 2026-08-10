import { prisma } from '@/lib/db';
import { getServerSession } from '@/lib/auth';

export async function GET() {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const tenantId = session.user.tenantId;

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const [totalProjects, totalLeads, newLeadsToday, publishedPages] =
    await Promise.all([
      prisma.project.count({ where: { tenantId } }),
      prisma.lead.count({ where: { tenantId } }),
      prisma.lead.count({
        where: { tenantId, createdAt: { gte: today } },
      }),
      prisma.page.count({ where: { tenantId, published: true } }),
    ]);

  return Response.json({
    totalProjects,
    totalLeads,
    newLeadsToday,
    publishedPages,
  });
}
