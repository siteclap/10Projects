import { prisma } from '@/lib/db';
import { getServerSession } from '@/lib/auth';

export async function GET() {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const leads = await prisma.lead.findMany({
    where: { tenantId: session.user.tenantId },
    include: {
      project: {
        select: { title: true },
      },
    },
    orderBy: { createdAt: 'desc' },
  });

  // Build CSV content
  const headers = [
    'ID',
    'Name',
    'Phone',
    'Email',
    'Intent',
    'Status',
    'Project',
    'Source',
    'Notes',
    'Created At',
    'Updated At',
  ];

  const rows = leads.map((lead) => [
    lead.id,
    escapeCsvField(lead.name),
    escapeCsvField(lead.phone),
    escapeCsvField(lead.email || ''),
    escapeCsvField(lead.intent),
    lead.status,
    escapeCsvField(lead.project?.title || ''),
    escapeCsvField(lead.source || ''),
    escapeCsvField(lead.notes || ''),
    lead.createdAt.toISOString(),
    lead.updatedAt.toISOString(),
  ]);

  const csv = [
    headers.join(','),
    ...rows.map((row) => row.join(',')),
  ].join('\n');

  const filename = `leads-export-${new Date().toISOString().split('T')[0]}.csv`;

  return new Response(csv, {
    status: 200,
    headers: {
      'Content-Type': 'text/csv; charset=utf-8',
      'Content-Disposition': `attachment; filename="${filename}"`,
    },
  });
}

/**
 * Escape a field for CSV output.
 * Wraps in double quotes if the field contains commas, quotes, or newlines.
 */
function escapeCsvField(field: string): string {
  if (field.includes(',') || field.includes('"') || field.includes('\n')) {
    return `"${field.replace(/"/g, '""')}"`;
  }
  return field;
}
