import { writeFile, mkdir } from 'fs/promises';
import { join } from 'path';
import { type NextRequest } from 'next/server';
import { getServerSession } from '@/lib/auth';

export async function POST(request: NextRequest) {
  const session = await getServerSession();

  if (!session?.user?.tenantId) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const tenantId = session.user.tenantId;

  let formData: FormData;

  try {
    formData = await request.formData();
  } catch {
    return Response.json({ error: 'Invalid form data' }, { status: 400 });
  }

  const file = formData.get('file') as File | null;

  if (!file) {
    return Response.json(
      { error: 'No file provided. Send a file with the key "file".' },
      { status: 400 }
    );
  }

  // Validate file size (max 10MB)
  const maxSize = 10 * 1024 * 1024;
  if (file.size > maxSize) {
    return Response.json(
      { error: 'File size exceeds 10MB limit' },
      { status: 400 }
    );
  }

  // Validate file type
  const allowedTypes = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif',
    'image/svg+xml',
    'application/pdf',
  ];

  if (!allowedTypes.includes(file.type)) {
    return Response.json(
      { error: `File type "${file.type}" is not allowed. Allowed: ${allowedTypes.join(', ')}` },
      { status: 400 }
    );
  }

  // Generate a unique filename to prevent collisions
  const ext = file.name.split('.').pop() || 'bin';
  const timestamp = Date.now();
  const safeName = file.name
    .replace(/\.[^/.]+$/, '') // remove extension
    .replace(/[^a-zA-Z0-9-_]/g, '-') // sanitize
    .toLowerCase()
    .slice(0, 50); // limit length
  const filename = `${safeName}-${timestamp}.${ext}`;

  // Create the upload directory if it doesn't exist
  const uploadDir = join(process.cwd(), 'public', 'uploads', tenantId);

  try {
    await mkdir(uploadDir, { recursive: true });
  } catch {
    // Directory may already exist, that's fine
  }

  // Write the file
  const filePath = join(uploadDir, filename);
  const bytes = await file.arrayBuffer();
  const buffer = Buffer.from(bytes);

  try {
    await writeFile(filePath, buffer);
  } catch {
    return Response.json(
      { error: 'Failed to save file' },
      { status: 500 }
    );
  }

  // Return the public URL
  const url = `/uploads/${tenantId}/${filename}`;

  return Response.json(
    {
      url,
      filename,
      size: file.size,
      mimeType: file.type,
    },
    { status: 201 }
  );
}
