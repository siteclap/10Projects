'use client';

import { AdminTopbar } from '@/components/admin/AdminTopbar';
import { ProjectForm } from '@/components/admin/ProjectForm';

export default function NewProjectPage() {
  return (
    <>
      <AdminTopbar title="Add New Project" />
      <div className="p-2xl">
        <ProjectForm />
      </div>
    </>
  );
}
