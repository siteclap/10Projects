'use client';

import { useRef, useEffect, useState, useCallback } from 'react';
import { useAssessment } from '@/lib/hooks/use-assessment';
import { useAuth } from '@/components/auth/AuthProvider';
import type { AuthToken } from '@/lib/types/customer';
import { AccuracyBar } from './AccuracyBar';
import { ChatMessage } from './ChatMessage';
import { ChatChips } from './ChatChips';
import { ChatCards } from './ChatCards';
import { ChatTextarea } from './ChatTextarea';
import { AnalysingDots } from './AnalysingDots';
import { ResultsPreview } from './ResultsPreview';
import { LoginGate } from '@/components/auth/LoginGate';
import { Button } from '@/components/ui/Button';
import { cn } from '@/lib/utils/cn';

export function ChatContainer() {
  const {
    sessionUuid,
    phase,
    currentQuestion,
    answeredCount,
    totalQuestions,
    messages,
    isProcessing,
    phaseComplete,
    recommendations,
    accuracy,
    error,
    startSession,
    submitAnswer,
    advancePhase,
  } = useAssessment();

  const { isAuthenticated, login } = useAuth();

  const messagesEndRef = useRef<HTMLDivElement>(null);
  const scrollContainerRef = useRef<HTMLDivElement>(null);
  const [showLoginGate, setShowLoginGate] = useState(false);

  // Auto-scroll to bottom on new messages
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages, isProcessing, phaseComplete, showLoginGate]);

  // Determine if login gate should show: after phase 1 results, user not authenticated
  useEffect(() => {
    if (phaseComplete && phase === 1 && !isAuthenticated) {
      setShowLoginGate(true);
    } else {
      setShowLoginGate(false);
    }
  }, [phaseComplete, phase, isAuthenticated]);

  // Handle auth success from login gate
  const handleVerified = useCallback(
    (token: AuthToken) => {
      login(token);
      setShowLoginGate(false);
    },
    [login]
  );

  // Handle answer submission from input components
  const handleAnswer = useCallback(
    (key: string, value: unknown, displayText: string) => {
      submitAnswer(key, value, displayText);
    },
    [submitAnswer]
  );

  // Session not started — show start button
  if (!sessionUuid) {
    return (
      <div className="flex flex-1 flex-col items-center justify-center gap-xl px-lg py-3xl">
        <div className="flex flex-col items-center gap-md text-center">
          <div className="flex h-[64px] w-[64px] items-center justify-center rounded-full bg-brand-primary-bg">
            <svg
              width="32"
              height="32"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-brand-primary"
              aria-hidden="true"
            >
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
          </div>

          <h2 className="text-h3 text-gray-900">
            Find Your Perfect Property Match
          </h2>
          <p className="max-w-[320px] text-sm text-gray-500">
            Answer a few questions and our AI will find the 10 best-fit projects
            for your needs, budget, and lifestyle.
          </p>
        </div>

        <Button size="lg" onClick={startSession} loading={isProcessing}>
          Start AI Assessment
        </Button>

        {error && (
          <p className="text-sm text-danger" role="alert">
            {error}
          </p>
        )}
      </div>
    );
  }

  return (
    <div className="flex h-full flex-col">
      {/* Top: Accuracy bar */}
      <div className="border-b border-gray-100 px-lg py-md">
        <AccuracyBar
          accuracy={accuracy}
          answeredCount={answeredCount}
          totalQuestions={totalQuestions}
        />
        <p className="mt-xs text-caption text-gray-400">
          Phase {phase} of 3
        </p>
      </div>

      {/* Middle: Scrollable messages area */}
      <div
        ref={scrollContainerRef}
        className="flex-1 overflow-y-auto px-lg py-lg"
      >
        <div className="flex flex-col gap-lg">
          {messages.map((msg) => (
            <ChatMessage
              key={msg.id}
              role={msg.role}
              content={msg.content}
              type={msg.type}
            />
          ))}

          {/* Processing indicator */}
          {isProcessing && (
            <div className="animate-[fadeUp_0.3s_ease-out]">
              <AnalysingDots />
            </div>
          )}

          {/* Results preview after phase completion */}
          {phaseComplete && recommendations && !showLoginGate && (
            <ResultsPreview
              recommendations={recommendations}
              accuracy={accuracy}
              phase={phase}
              onContinue={phase < 3 ? advancePhase : undefined}
            />
          )}

          {/* Login gate after phase 1 results */}
          {showLoginGate && (
            <div className="animate-[fadeUp_0.3s_ease-out]">
              <LoginGate
                onVerified={handleVerified}
                message="Verify your phone to see full results"
              />
              {recommendations && recommendations.length > 0 && (
                <div className="mt-md">
                  <ResultsPreview
                    recommendations={recommendations}
                    accuracy={accuracy}
                    phase={phase}
                    onContinue={
                      isAuthenticated && phase < 3 ? advancePhase : undefined
                    }
                  />
                </div>
              )}
            </div>
          )}

          {/* Error message */}
          {error && !isProcessing && (
            <div className="animate-[fadeUp_0.3s_ease-out] rounded-sm border border-danger-light bg-danger-light p-md">
              <p className="text-sm text-danger">{error}</p>
            </div>
          )}

          {/* Scroll anchor */}
          <div ref={messagesEndRef} />
        </div>
      </div>

      {/* Bottom: Current question input */}
      {currentQuestion && !isProcessing && !phaseComplete && (
        <div
          className={cn(
            'border-t border-gray-100 px-lg py-lg',
            'animate-[fadeUp_0.3s_ease-out]'
          )}
        >
          {currentQuestion.type === 'chips' && (
            <ChatChips question={currentQuestion} onAnswer={handleAnswer} />
          )}

          {currentQuestion.type === 'cards' && (
            <ChatCards question={currentQuestion} onAnswer={handleAnswer} />
          )}

          {currentQuestion.type === 'textarea' && (
            <ChatTextarea question={currentQuestion} onAnswer={handleAnswer} />
          )}
        </div>
      )}
    </div>
  );
}
