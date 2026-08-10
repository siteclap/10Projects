'use client';

import type { ReactNode } from 'react';
import { LeadFormProvider } from '@/components/lead/LeadFormContext';
import { LeadFormModal } from '@/components/lead/LeadFormModal';

interface LeadFormWrapperProps {
  projectTitle: string;
  children: ReactNode;
}

export function LeadFormWrapper({ projectTitle, children }: LeadFormWrapperProps) {
  return (
    <LeadFormProvider>
      {children}
      <LeadFormModal projectTitle={projectTitle} />
    </LeadFormProvider>
  );
}
