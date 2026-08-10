'use client';

import { useState, useCallback } from 'react';
import type { Question } from '@/lib/types/assessment';
import { cn } from '@/lib/utils/cn';
import { Button } from '@/components/ui/Button';

interface ChatCardsProps {
  question: Question;
  onAnswer: (key: string, value: unknown, displayText: string) => void;
  variant?: 'light' | 'dark';
}

export function ChatCards({ question, onAnswer, variant = 'light' }: ChatCardsProps) {
  const isDark = variant === 'dark';
  const [selected, setSelected] = useState<string[]>([]);
  const [rankings, setRankings] = useState<Record<string, number>>({});

  const isRanking = typeof question.max_rank === 'number' && question.max_rank > 0;
  const isMulti = question.multi_select === true;
  const maxRank = question.max_rank ?? 0;

  const handleCardClick = useCallback(
    (optionValue: string, optionLabel: string) => {
      if (isRanking) {
        // Ranking mode: assign ranks on tap
        setRankings((prev) => {
          const existing = prev[optionValue];
          if (existing) {
            // Remove this card's rank and shift others down
            const removedRank = existing;
            const updated: Record<string, number> = {};
            for (const [key, rank] of Object.entries(prev)) {
              if (key === optionValue) continue;
              if (rank > removedRank) {
                updated[key] = rank - 1;
              } else {
                updated[key] = rank;
              }
            }
            return updated;
          }

          // Count current rankings
          const currentCount = Object.keys(prev).length;
          if (currentCount >= maxRank) return prev;

          return { ...prev, [optionValue]: currentCount + 1 };
        });
      } else if (isMulti) {
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
    [isRanking, isMulti, maxRank, onAnswer, question.key]
  );

  const handleConfirm = useCallback(() => {
    if (isRanking) {
      const rankedCount = Object.keys(rankings).length;
      if (rankedCount < maxRank) return;

      // Build ranked value as ordered array
      const sorted = Object.entries(rankings)
        .sort(([, a], [, b]) => a - b)
        .map(([key]) => key);

      const rankedLabels = sorted
        .map((key) => {
          const opt = (question.options ?? []).find((o) => o.value === key);
          return opt?.label ?? key;
        })
        .join(', ');

      onAnswer(question.key, sorted, rankedLabels);
    } else if (isMulti) {
      if (selected.length === 0) return;

      const selectedLabels = (question.options ?? [])
        .filter((opt) => selected.includes(opt.value))
        .map((opt) => opt.label);

      onAnswer(question.key, selected, selectedLabels.join(', '));
    }
  }, [
    isRanking,
    isMulti,
    rankings,
    maxRank,
    selected,
    question.key,
    question.options,
    onAnswer,
  ]);

  if (!question.options || question.options.length === 0) return null;

  const canConfirm = isRanking
    ? Object.keys(rankings).length >= maxRank
    : isMulti
      ? selected.length > 0
      : false;

  return (
    <div className="flex flex-col gap-md">
      <div
        className="grid grid-cols-2 gap-sm"
        role="listbox"
        aria-multiselectable={isMulti || isRanking}
        aria-label={question.text}
      >
        {question.options.map((option) => {
          const isSelected = isRanking
            ? option.value in rankings
            : selected.includes(option.value);
          const rank = isRanking ? rankings[option.value] : undefined;

          return (
            <button
              key={option.value}
              type="button"
              role="option"
              aria-selected={isSelected}
              onClick={() => handleCardClick(option.value, option.label)}
              className={cn(
                'relative flex flex-col items-center gap-xs rounded-md border p-md text-center',
                'transition-all duration-150 cursor-pointer',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary',
                isDark ? 'focus-visible:ring-offset-gray-900' : 'focus-visible:ring-offset-2',
                isSelected
                  ? isDark
                    ? 'border-brand-primary bg-brand-primary/10 shadow-card'
                    : 'border-brand-primary bg-brand-primary-bg shadow-card'
                  : isDark
                    ? 'border-gray-700 bg-gray-800/60 hover:border-gray-600'
                    : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-card'
              )}
            >
              {/* Rank badge */}
              {rank !== undefined && (
                <span className="absolute -right-[6px] -top-[6px] flex h-[24px] w-[24px] items-center justify-center rounded-full bg-brand-primary text-caption font-bold text-white">
                  #{rank}
                </span>
              )}

              {option.icon && (
                <span className="text-h2" aria-hidden="true">
                  {option.icon}
                </span>
              )}

              <span
                className={cn(
                  'text-sm font-medium',
                  isSelected
                    ? 'text-brand-primary'
                    : isDark ? 'text-gray-200' : 'text-gray-800'
                )}
              >
                {option.label}
              </span>

              {option.description && (
                <span className={cn('text-caption', isDark ? 'text-gray-400' : 'text-gray-500')}>
                  {option.description}
                </span>
              )}
            </button>
          );
        })}
      </div>

      {(isMulti || isRanking) && (
        <Button
          size="sm"
          onClick={handleConfirm}
          disabled={!canConfirm}
          className="self-start"
        >
          {isRanking ? 'Confirm Ranking' : 'Continue'}
        </Button>
      )}
    </div>
  );
}
