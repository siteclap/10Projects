'use client';

import { useState, useCallback, useRef, useEffect } from 'react';
import type { Question } from '@/lib/types/assessment';
import { cn } from '@/lib/utils/cn';

interface ChatTextareaProps {
  question: Question;
  onAnswer: (key: string, value: unknown, displayText: string) => void;
}

export function ChatTextarea({ question, onAnswer }: ChatTextareaProps) {
  const [value, setValue] = useState('');
  const textareaRef = useRef<HTMLTextAreaElement>(null);

  // Auto-focus on mount
  useEffect(() => {
    textareaRef.current?.focus();
  }, []);

  // Auto-resize textarea
  useEffect(() => {
    const textarea = textareaRef.current;
    if (!textarea) return;
    textarea.style.height = 'auto';
    textarea.style.height = `${Math.min(textarea.scrollHeight, 120)}px`;
  }, [value]);

  const handleSubmit = useCallback(() => {
    const trimmed = value.trim();
    if (!trimmed) return;
    onAnswer(question.key, trimmed, trimmed);
    setValue('');
  }, [value, question.key, onAnswer]);

  const handleKeyDown = useCallback(
    (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleSubmit();
      }
    },
    [handleSubmit]
  );

  const isEmpty = value.trim().length === 0;

  return (
    <div className="flex items-end gap-sm rounded-md border border-gray-200 bg-white p-sm shadow-card">
      <textarea
        ref={textareaRef}
        value={value}
        onChange={(e) => setValue(e.target.value)}
        onKeyDown={handleKeyDown}
        placeholder={question.text}
        rows={1}
        className={cn(
          'flex-1 resize-none border-none bg-transparent px-sm py-xs text-sm text-gray-900 placeholder:text-gray-400',
          'focus:outline-none'
        )}
        aria-label={question.text}
      />

      <button
        type="button"
        onClick={handleSubmit}
        disabled={isEmpty}
        className={cn(
          'flex h-[36px] w-[36px] shrink-0 items-center justify-center rounded-full transition-colors duration-150',
          'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2',
          isEmpty
            ? 'cursor-not-allowed bg-gray-100 text-gray-400'
            : 'cursor-pointer bg-brand-primary text-white hover:bg-brand-primary-dark'
        )}
        aria-label="Send message"
      >
        <svg
          width="18"
          height="18"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
          aria-hidden="true"
        >
          <path d="m5 12 7-7 7 7" />
          <path d="M12 19V5" />
        </svg>
      </button>
    </div>
  );
}
