'use client';

import { createContext, useContext, useState, useCallback, type ReactNode } from 'react';

type LeadFormIntent = 'best_price' | 'site_visit' | 'brochure' | 'callback' | 'floor_plan';

interface LeadFormContextValue {
  isOpen: boolean;
  intent: LeadFormIntent;
  openForm: (intent?: LeadFormIntent) => void;
  closeForm: () => void;
}

const LeadFormContext = createContext<LeadFormContextValue | null>(null);

export function LeadFormProvider({
  children,
}: {
  children: ReactNode;
}) {
  const [isOpen, setIsOpen] = useState(false);
  const [intent, setIntent] = useState<LeadFormIntent>('best_price');

  const openForm = useCallback((newIntent: LeadFormIntent = 'best_price') => {
    setIntent(newIntent);
    setIsOpen(true);
  }, []);

  const closeForm = useCallback(() => {
    setIsOpen(false);
  }, []);

  return (
    <LeadFormContext.Provider value={{ isOpen, intent, openForm, closeForm }}>
      {children}
    </LeadFormContext.Provider>
  );
}

export function useLeadForm() {
  const context = useContext(LeadFormContext);
  if (!context) {
    throw new Error('useLeadForm must be used within a LeadFormProvider');
  }
  return context;
}
