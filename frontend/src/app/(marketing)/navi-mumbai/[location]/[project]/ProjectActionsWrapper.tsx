'use client';

import { ProjectActions } from '@/components/project/ProjectActions';
import { ToastProvider } from '@/lib/hooks/use-toast';
import { ToastContainer } from '@/components/ui/Toast';
import { CompareFloatingBar } from '@/components/compare/CompareFloatingBar';

interface ProjectActionsWrapperProps {
  projectId: number;
  projectTitle: string;
  projectUrl: string;
}

export function ProjectActionsWrapper({
  projectId,
  projectTitle,
  projectUrl,
}: ProjectActionsWrapperProps) {
  return (
    <ToastProvider>
      <ProjectActions
        projectId={projectId}
        projectTitle={projectTitle}
        projectUrl={projectUrl}
      />
      <CompareFloatingBar />
      <ToastContainer />
    </ToastProvider>
  );
}
