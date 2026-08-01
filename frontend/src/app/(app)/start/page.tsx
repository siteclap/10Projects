import type { Metadata } from 'next';
import { ChatContainer } from '@/components/assessment/ChatContainer';

export const metadata: Metadata = {
  title: 'AI Property Matcher',
  description:
    'Answer a few questions and our AI will find the 10 best-fit real estate projects for your needs, budget, and lifestyle.',
  robots: {
    index: false,
    follow: false,
  },
};

export default function AssessmentPage() {
  return (
    <div className="flex flex-1 flex-col">
      <ChatContainer />
    </div>
  );
}
