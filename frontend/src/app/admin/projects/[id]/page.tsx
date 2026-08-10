'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import { AdminTopbar } from '@/components/admin/AdminTopbar';
import { ProjectForm, type ProjectFormData } from '@/components/admin/ProjectForm';

export default function EditProjectPage() {
  const params = useParams();
  const projectId = params.id as string;

  const [projectData, setProjectData] = useState<Partial<ProjectFormData> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    async function fetchProject() {
      try {
        const res = await fetch(`/api/admin/projects/${projectId}`);
        if (!res.ok) {
          throw new Error('Project not found');
        }
        const data = await res.json();
        setProjectData(data);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to load project');
      } finally {
        setLoading(false);
      }
    }

    fetchProject();
  }, [projectId]);

  if (loading) {
    return (
      <>
        <AdminTopbar title="Edit Project" />
        <div className="p-2xl">
          <div className="flex flex-col gap-xl">
            {/* Title skeleton */}
            <div className="h-[48px] w-full animate-pulse rounded-sm bg-gray-200" />
            {/* Form skeleton */}
            <div className="flex gap-2xl">
              <div className="flex flex-1 flex-col gap-xl" style={{ flex: '7' }}>
                <div className="h-[300px] animate-pulse rounded-md bg-gray-200" />
                <div className="h-[200px] animate-pulse rounded-md bg-gray-200" />
              </div>
              <div className="flex flex-col gap-xl" style={{ flex: '3' }}>
                <div className="h-[150px] animate-pulse rounded-md bg-gray-200" />
                <div className="h-[200px] animate-pulse rounded-md bg-gray-200" />
              </div>
            </div>
          </div>
        </div>
      </>
    );
  }

  if (error) {
    return (
      <>
        <AdminTopbar title="Edit Project" />
        <div className="p-2xl">
          <div className="rounded-md border border-danger bg-danger-light px-xl py-lg text-sm text-danger">
            {error}
          </div>
        </div>
      </>
    );
  }

  return (
    <>
      <AdminTopbar title="Edit Project" />
      <div className="p-2xl">
        {projectData && (
          <ProjectForm
            initialData={{ ...projectData, id: projectId }}
            isEditing
          />
        )}
      </div>
    </>
  );
}
