'use client';

import type { Question } from '@/lib/types/assessment';
import { ChatChips } from '@/components/assessment/ChatChips';
import { ChatCards } from '@/components/assessment/ChatCards';
import { ChatTextarea } from '@/components/assessment/ChatTextarea';

interface HeroChatStepProps {
  question: Question;
  onAnswer: (key: string, value: unknown, displayText: string) => void;
}

export function HeroChatStep({ question, onAnswer }: HeroChatStepProps) {
  const isMulti = question.multi_select === true;

  return (
    <div className="animate-[fadeUp_0.3s_ease-out]">
      {/* AI question text */}
      <p className="mb-md text-base font-medium text-white">{question.text}</p>

      {/* Hint for multi-select */}
      {isMulti && (
        <p className="mb-lg text-sm text-gray-400">Select all that apply</p>
      )}

      {/* Answer input based on question type */}
      {question.type === 'chips' && (
        <ChatChips question={question} onAnswer={onAnswer} variant="dark" />
      )}
      {question.type === 'cards' && (
        <ChatCards question={question} onAnswer={onAnswer} variant="dark" />
      )}
      {question.type === 'textarea' && (
        <ChatTextarea question={question} onAnswer={onAnswer} />
      )}
    </div>
  );
}
