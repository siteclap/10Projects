import { cn } from '@/lib/utils/cn';

interface ChatMessageProps {
  role: 'ai' | 'user';
  content: string;
  type?: 'question' | 'answer' | 'info';
}

export function ChatMessage({ role, content, type }: ChatMessageProps) {
  const isAi = role === 'ai';

  return (
    <div
      className={cn(
        'flex w-full animate-[fadeUp_0.3s_ease-out]',
        isAi ? 'justify-start' : 'justify-end'
      )}
    >
      <div
        className={cn(
          'max-w-[85%] px-lg py-md text-sm leading-relaxed',
          isAi
            ? 'rounded-md rounded-tl-none bg-gray-100 text-gray-800'
            : 'rounded-md rounded-tr-none bg-brand-primary text-white',
          type === 'info' && isAi && 'bg-brand-primary-bg text-brand-primary'
        )}
      >
        {content}
      </div>
    </div>
  );
}
