import { revalidatePath } from 'next/cache';
import { type NextRequest } from 'next/server';

/**
 * CPT-to-path mapping for ISR revalidation.
 *
 * When a WordPress CPT is saved, this handler resolves the
 * corresponding Next.js paths that need revalidation.
 */

type CptType = 'tp_project' | 'tp_developer' | 'tp_location' | 'tp_guide';

interface RevalidateBody {
  secret: string;
  type: CptType;
  slug: string;
  path?: string;
}

/** Derive the Next.js paths that correspond to a given CPT + slug. */
function getPathsForCpt(type: CptType, slug: string, path?: string): string[] {
  const paths: string[] = [];

  // If a specific path was provided, always revalidate it.
  if (path) {
    paths.push(path);
  }

  switch (type) {
    case 'tp_project':
      // Project pages live at /navi-mumbai/[location]/[project].
      // The slug alone is not enough to construct the full path,
      // so rely on the explicit `path` parameter for the detail page.
      // Always revalidate parent archive pages.
      paths.push('/navi-mumbai');
      paths.push('/projects');
      paths.push('/');
      break;

    case 'tp_location':
      paths.push(`/navi-mumbai/${slug}`);
      paths.push('/navi-mumbai');
      paths.push('/');
      break;

    case 'tp_developer':
      paths.push(`/developers/${slug}`);
      paths.push('/developers');
      paths.push('/');
      break;

    case 'tp_guide':
      paths.push(`/guides/${slug}`);
      paths.push('/guides');
      break;
  }

  // Deduplicate paths.
  return [...new Set(paths)];
}

export async function POST(request: NextRequest) {
  let body: RevalidateBody;

  try {
    body = await request.json();
  } catch {
    return Response.json(
      { revalidated: false, message: 'Invalid JSON body' },
      { status: 400 }
    );
  }

  const { secret, type, slug, path } = body;

  // Validate revalidation secret.
  const expectedSecret = process.env.REVALIDATE_SECRET;

  if (!expectedSecret || secret !== expectedSecret) {
    return Response.json(
      { revalidated: false, message: 'Invalid secret' },
      { status: 401 }
    );
  }

  // Validate required fields.
  if (!type || !slug) {
    return Response.json(
      { revalidated: false, message: 'Missing required fields: type, slug' },
      { status: 400 }
    );
  }

  const validTypes: CptType[] = [
    'tp_project',
    'tp_developer',
    'tp_location',
    'tp_guide',
  ];

  if (!validTypes.includes(type)) {
    return Response.json(
      {
        revalidated: false,
        message: `Invalid type. Must be one of: ${validTypes.join(', ')}`,
      },
      { status: 400 }
    );
  }

  const paths = getPathsForCpt(type, slug, path);

  for (const p of paths) {
    revalidatePath(p);
  }

  return Response.json({
    revalidated: true,
    paths,
    now: Date.now(),
  });
}
