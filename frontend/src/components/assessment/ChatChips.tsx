'use client';

import { useState, useCallback } from 'react';
import type { Question } from '@/lib/types/assessment';
import { cn } from '@/lib/utils/cn';
import { Button } from '@/components/ui/Button';

interface ChatChipsProps {
  question: Question;
  onAnswer: (key: string, value: unknown, displayText: string) => void;
  variant?: 'light' | 'dark';
}

export function ChatChips({ question, onAnswer, variant = 'light' }: ChatChipsProps) {
  const isDark = variant === 'dark';
  const [selected, setSelected] = useState<string[]>([]);
  const isMulti = question.multi_select === true;

  const handleSelect = useCallback(
    (optionValue: string, optionLabel: string) => {
      if (isMulti) {
        setSelected((prev) =>
          prev.includes(optionValue)
            ? prev.filter((v) => v !== optionValue)
            : [...prev, optionValue]
        );
      } else {
        // Single select: submit immediately
        onAnswer(question.key, optionValue, optionLabel);
      }
    },
    [isMulti, onAnswer, question.key]
  );

  const handleContinue = useCallback(() => {
    if (selected.length === 0) return;

    const selectedLabels = (question.options ?? [])
      .filter((opt) => selected.includes(opt.value))
      .map((opt) => opt.label);

    onAnswer(question.key, selected, selectedLabels.join(', '));
  }, [selected, question.key, question.options, onAnswer]);

  if (!question.options || question.options.length === 0) return null;

  return (
    <div className="flex flex-col gap-md">
      <div
        className="flex flex-wrap gap-sm"
        role="listbox"
        aria-multiselectable={isMulti}
        aria-label={question.text}
      >
        {question.options.map((option) => {
          const isSelected = selected.includes(option.value);

          return (
            <button
              key={option.value}
              type="button"
              role="option"
              aria-selected={isSelected}
              onClick={() => handleSelect(option.value, option.label)}
              className={cn(
                'inline-flex items-center gap-xs rounded-full font-medium cursor-pointer',
                'transition-all duration-150',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary',
                isDark
                  ? 'px-xl py-md text-sm focus-visible:ring-offset-gray-900'
                  : 'px-lg py-sm text-sm focus-visible:ring-offset-2',
                isSelected
                  ? isDark
                    ? 'border border-brand-primary bg-brand-primary text-white shadow-md'
                    : 'bg-brand-primary text-white'
                  : isDark
                    ? 'border border-gray-600 bg-gray-800/60 text-gray-200 hover:border-brand-primary/50 hover:bg-gray-700/80 hover:text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              )}
            >
              {option.icon && (
                <span className="text-base" aria-hidden="true">
                  {option.icon}
                </span>
              )}
              {option.label}
            </button>
          );
        })}
      </div>

      {isMulti && (
        <Button
          size="sm"
          onClick={handleContinue}
          disabled={selected.length === 0}
          className="self-start"
        >
          Continue
        </Button>
      )}
    </div>
  );
}
