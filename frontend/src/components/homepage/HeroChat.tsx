'use client';

import { useState, useEffect, useRef, useCallback } from 'react';
import { startSession, submitAnswer } from '@/lib/api/assessment';
import { getVisitorId } from '@/lib/utils/visitor-id';
import { HeroChatStep } from './HeroChatStep';
import { AnalysingDots } from '@/components/assessment/AnalysingDots';
import type { Question } from '@/lib/types/assessment';

const TOTAL_STEPS = 3;

/**
 * Mock questions used when the API is unavailable (local dev without WP backend).
 * These mirror the Phase 1 questions from the assessment flow.
 */
const MOCK_QUESTIONS: Question[] = [
  {
    key: 'budget',
    text: "What's your budget range?",
    type: 'chips',
    options: [
      { value: '30-50L', label: '₹30–50 Lakh' },
      { value: '50-80L', label: '₹50–80 Lakh' },
      { value: '80L-1.2Cr', label: '₹80L–1.2 Cr' },
      { value: '1.2-2Cr', label: '₹1.2–2 Cr' },
      { value: '2Cr+', label: '₹2 Cr+' },
    ],
  },
  {
    key: 'location',
    text: 'Which locations interest you?',
    type: 'chips',
    options: [
      { value: 'kharghar', label: 'Kharghar' },
      { value: 'panvel', label: 'Panvel' },
      { value: 'ulwe', label: 'Ulwe' },
      { value: 'vashi', label: 'Vashi' },
      { value: 'airoli', label: 'Airoli' },
      { value: 'ghansoli', label: 'Ghansoli' },
      { value: 'taloja', label: 'Taloja' },
      { value: 'dombivli', label: 'Dombivli' },
    ],
    multi_select: true,
  },
  {
    key: 'config',
    text: 'What configuration are you looking for?',
    type: 'chips',
    options: [
      { value: '1bhk', label: '1 BHK' },
      { value: '2bhk', label: '2 BHK' },
      { value: '3bhk', label: '3 BHK' },
      { value: '4bhk+', label: '4 BHK+' },
    ],
  },
];

interface HeroChatProps {
  selectedCity: string;
  onClose: () => void;
  onComplete: (sessionUuid: string) => void;
}

export function HeroChat({ selectedCity, onClose, onComplete }: HeroChatProps) {
  const [step, setStep] = useState(0);
  const [currentQuestion, setCurrentQuestion] = useState<Question | null>(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [usingMock, setUsingMock] = useState(false);

  const sessionUuidRef = useRef<string | null>(null);
  const mockIndexRef = useRef(0);
  const mountedRef = useRef(true);

  useEffect(() => {
    mountedRef.current = true;

    async function init() {
      try {
        const visitorId = getVisitorId();
        const result = await startSession(visitorId, selectedCity);

        if (!mountedRef.current) return;

        sessionUuidRef.current = result.session_uuid;
        setCurrentQuestion(result.current_question);
        setStep(1);
      } catch {
        if (!mountedRef.current) return;
        setUsingMock(true);
        sessionUuidRef.current = `mock_${Date.now().toString(36)}`;
        mockIndexRef.current = 0;
        setCurrentQuestion(MOCK_QUESTIONS[0]);
        setStep(1);
      }
    }

    init();

    return () => {
      mountedRef.current = false;
    };
  }, [selectedCity]);

  const handleAnswer = useCallback(
    async (_key: string, value: unknown, _displayText: string) => {
      const uuid = sessionUuidRef.current;
      if (!uuid || !currentQuestion || isProcessing) return;

      setIsProcessing(true);
      setError(null);

      if (usingMock) {
        await new Promise((r) => setTimeout(r, 500));
        if (!mountedRef.current) return;

        const nextIndex = mockIndexRef.current + 1;

        if (nextIndex >= MOCK_QUESTIONS.length) {
          try {
            localStorage.setItem('tp_session_uuid', uuid);
          } catch {
            // localStorage might be unavailable
          }
          setIsProcessing(false);
          onComplete(uuid);
        } else {
          mockIndexRef.current = nextIndex;
          setCurrentQuestion(MOCK_QUESTIONS[nextIndex]);
          setStep((prev) => prev + 1);
          setIsProcessing(false);
        }
        return;
      }

      try {
        const result = await submitAnswer(uuid, currentQuestion.key, value);

        if (!mountedRef.current) return;

        if (result.phase_complete) {
          try {
            localStorage.setItem('tp_session_uuid', uuid);
          } catch {
            // localStorage might be unavailable
          }
          onComplete(uuid);
        } else if (result.next_question) {
          setCurrentQuestion(result.next_question);
          setStep((prev) => prev + 1);
        }
      } catch {
        if (!mountedRef.current) return;
        setError('Something went wrong. Please try again.');
      } finally {
        if (mountedRef.current) {
          setIsProcessing(false);
        }
      }
    },
    [currentQuestion, isProcessing, onComplete, usingMock]
  );

  // Loading state
  if (step === 0 && !error) {
    return (
      <div className="rounded-xl border border-gray-700/50 bg-gray-800/40 px-xl py-2xl backdrop-blur-sm">
        <div className="flex items-center justify-center">
          <AnalysingDots text="Starting your assessment..." className="[&_span]:text-gray-400" />
        </div>
      </div>
    );
  }

  // Error state
  if (error) {
    return (
      <div className="rounded-xl border border-gray-700/50 bg-gray-800/40 px-xl py-2xl text-center backdrop-blur-sm">
        <p className="mb-lg text-sm text-red-400">{error}</p>
        <button
          type="button"
          onClick={onClose}
          className="rounded-full bg-brand-primary px-xl py-sm text-sm font-medium text-white transition-colors hover:bg-brand-primary-dark"
        >
          Go Back
        </button>
      </div>
    );
  }

  return (
    <div className="rounded-xl border border-gray-700/50 bg-gray-800/40 p-xl backdrop-blur-sm">
      {/* Header row: step indicator + close */}
      <div className="mb-xl flex items-center justify-between">
        <div className="flex items-center gap-md">
          {/* AI icon */}
          <div className="flex h-[32px] w-[32px] items-center justify-center rounded-full bg-brand-primary/20">
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-brand-primary-light"
              aria-hidden="true"
            >
              <path d="M12 2a4 4 0 0 1 4 4v1a1 1 0 0 0 1 1h1a4 4 0 0 1 0 8h-1a1 1 0 0 0-1 1v1a4 4 0 0 1-8 0v-1a1 1 0 0 0-1-1H6a4 4 0 0 1 0-8h1a1 1 0 0 0 1-1V6a4 4 0 0 1 4-4z" />
            </svg>
          </div>
          <span className="text-sm font-medium text-gray-300">
            Step {step} of {TOTAL_STEPS}
          </span>
        </div>

        {/* Close button */}
        <button
          type="button"
          onClick={onClose}
          className="flex h-[36px] w-[36px] items-center justify-center rounded-full text-gray-500 transition-colors hover:bg-gray-700/50 hover:text-gray-300"
          aria-label="Close assessment"
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
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
          </svg>
        </button>
      </div>

      {/* Progress bar */}
      <div className="mb-xl flex gap-[4px]">
        {Array.from({ length: TOTAL_STEPS }, (_, i) => (
          <div
            key={i}
            className={`h-[3px] flex-1 rounded-full transition-all duration-300 ${
              i < step ? 'bg-brand-primary' : 'bg-gray-700'
            }`}
          />
        ))}
      </div>

      {/* Current question */}
      {currentQuestion && !isProcessing && (
        <HeroChatStep
          key={currentQuestion.key}
          question={currentQuestion}
          onAnswer={handleAnswer}
        />
      )}

      {/* Processing state */}
      {isProcessing && (
        <div className="flex items-center justify-center py-xl">
          <AnalysingDots text="Processing..." className="[&_span]:text-gray-400" />
        </div>
      )}
    </div>
  );
}
