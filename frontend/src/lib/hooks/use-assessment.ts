'use client';

import { useState, useCallback, useEffect, useRef } from 'react';
import type { Question } from '@/lib/types/assessment';
import type { RecommendationResult } from '@/lib/types/recommendation';
import {
  startSession as apiStartSession,
  submitAnswer as apiSubmitAnswer,
  advancePhase as apiAdvancePhase,
  getSession as apiGetSession,
} from '@/lib/api/assessment';
import { getVisitorId } from '@/lib/utils/visitor-id';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

export interface ChatMessage {
  id: string;
  role: 'ai' | 'user';
  content: string;
  type?: 'question' | 'answer' | 'info';
  timestamp: number;
}

interface AssessmentState {
  sessionUuid: string | null;
  phase: number;
  currentQuestion: Question | null;
  answeredCount: number;
  totalQuestions: number;
  messages: ChatMessage[];
  isProcessing: boolean;
  phaseComplete: boolean;
  recommendations: RecommendationResult[] | null;
  accuracy: number;
  error: string | null;
}

export interface UseAssessmentReturn extends AssessmentState {
  startSession: () => Promise<void>;
  submitAnswer: (
    questionKey: string,
    value: unknown,
    displayText: string
  ) => Promise<void>;
  advancePhase: () => Promise<void>;
  resumeSession: (uuid: string) => Promise<void>;
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const STORAGE_KEY = 'tp_session_uuid';

/** Phase-to-accuracy mapping. */
const ACCURACY_MAP: Record<number, number> = {
  1: 72,
  2: 89,
  3: 96,
};

/** Total questions per phase. Phase 1: 3, Phase 2: 4, Phase 3: 4. */
const QUESTIONS_PER_PHASE: Record<number, number> = {
  1: 3,
  2: 4,
  3: 4,
};

const TOTAL_ALL_QUESTIONS = 11; // 3 + 4 + 4

const WELCOME_MESSAGE =
  "Hi! I'm your AI property matcher. I'll ask you a few quick questions to find the best-fit projects for you. Let's get started!";

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function generateMessageId(): string {
  return `msg_${Date.now()}_${Math.random().toString(36).substring(2, 8)}`;
}

function createMessage(
  role: 'ai' | 'user',
  content: string,
  type?: 'question' | 'answer' | 'info'
): ChatMessage {
  return {
    id: generateMessageId(),
    role,
    content,
    type,
    timestamp: Date.now(),
  };
}

function saveSessionToStorage(uuid: string | null): void {
  if (typeof window === 'undefined') return;
  if (uuid) {
    localStorage.setItem(STORAGE_KEY, uuid);
  } else {
    localStorage.removeItem(STORAGE_KEY);
  }
}

function loadSessionFromStorage(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(STORAGE_KEY);
}

/**
 * Calculate the running answered count across all phases.
 * When resuming mid-phase, we compute based on phase and question index.
 */
function computeAnsweredCount(phase: number, indexInPhase: number): number {
  let count = 0;
  for (let p = 1; p < phase; p++) {
    count += QUESTIONS_PER_PHASE[p] ?? 0;
  }
  count += indexInPhase;
  return count;
}

// ---------------------------------------------------------------------------
// Initial state
// ---------------------------------------------------------------------------

const initialState: AssessmentState = {
  sessionUuid: null,
  phase: 1,
  currentQuestion: null,
  answeredCount: 0,
  totalQuestions: TOTAL_ALL_QUESTIONS,
  messages: [],
  isProcessing: false,
  phaseComplete: false,
  recommendations: null,
  accuracy: ACCURACY_MAP[1],
  error: null,
};

// ---------------------------------------------------------------------------
// Hook
// ---------------------------------------------------------------------------

export function useAssessment(): UseAssessmentReturn {
  const [state, setState] = useState<AssessmentState>(initialState);
  const mountedRef = useRef(true);
  const initRef = useRef(false);

  // Cleanup on unmount
  useEffect(() => {
    mountedRef.current = true;
    return () => {
      mountedRef.current = false;
    };
  }, []);

  // Restore session on mount
  useEffect(() => {
    if (initRef.current) return;
    initRef.current = true;

    const savedUuid = loadSessionFromStorage();
    if (savedUuid) {
      // Attempt to resume the saved session silently.
      // If it fails (expired, etc.) we just start fresh when the user clicks.
      apiGetSession(savedUuid)
        .then((session) => {
          if (!mountedRef.current) return;

          const phase = session.phase;
          const answeredCount = computeAnsweredCount(
            phase,
            session.current_question_index
          );

          setState((prev) => ({
            ...prev,
            sessionUuid: session.uuid,
            phase,
            answeredCount,
            accuracy: ACCURACY_MAP[phase] ?? 72,
            messages: [
              createMessage('ai', WELCOME_MESSAGE, 'info'),
              createMessage(
                'ai',
                "Welcome back! Let's continue where you left off.",
                'info'
              ),
            ],
          }));
        })
        .catch(() => {
          // Session expired or invalid — clear storage
          saveSessionToStorage(null);
        });
    }
  }, []);

  // -------------------------------------------------------------------
  // startSession
  // -------------------------------------------------------------------
  const startSession = useCallback(async () => {
    setState((prev) => ({
      ...prev,
      isProcessing: true,
      error: null,
      messages: [createMessage('ai', WELCOME_MESSAGE, 'info')],
      phaseComplete: false,
      recommendations: null,
    }));

    try {
      const visitorId = getVisitorId();
      const result = await apiStartSession(visitorId);

      if (!mountedRef.current) return;

      saveSessionToStorage(result.session_uuid);

      setState((prev) => ({
        ...prev,
        sessionUuid: result.session_uuid,
        phase: result.phase,
        currentQuestion: result.current_question,
        answeredCount: 0,
        accuracy: ACCURACY_MAP[result.phase] ?? 72,
        isProcessing: false,
        messages: [
          ...prev.messages,
          createMessage('ai', result.current_question.text, 'question'),
        ],
      }));
    } catch (err) {
      if (!mountedRef.current) return;

      const message =
        err instanceof Error ? err.message : 'Failed to start assessment';

      setState((prev) => ({
        ...prev,
        isProcessing: false,
        error: message,
        messages: [
          ...prev.messages,
          createMessage(
            'ai',
            'Sorry, something went wrong. Please try again.',
            'info'
          ),
        ],
      }));
    }
  }, []);

  // -------------------------------------------------------------------
  // submitAnswer
  // -------------------------------------------------------------------
  const submitAnswer = useCallback(
    async (questionKey: string, value: unknown, displayText: string) => {
      setState((prev) => {
        if (!prev.sessionUuid) return prev;

        return {
          ...prev,
          isProcessing: true,
          error: null,
          currentQuestion: null,
          messages: [
            ...prev.messages,
            createMessage('user', displayText, 'answer'),
          ],
        };
      });

      // Read sessionUuid from state via a promise-friendly pattern
      const sessionUuid = state.sessionUuid;
      if (!sessionUuid) return;

      try {
        const result = await apiSubmitAnswer(sessionUuid, questionKey, value);

        if (!mountedRef.current) return;

        if (result.phase_complete) {
          setState((prev) => {
            const newAnswered = prev.answeredCount + 1;
            return {
              ...prev,
              answeredCount: newAnswered,
              isProcessing: false,
              phaseComplete: true,
              recommendations: result.recommendations ?? null,
              accuracy: result.accuracy ?? ACCURACY_MAP[prev.phase] ?? 72,
              currentQuestion: null,
              messages: [
                ...prev.messages,
                createMessage(
                  'ai',
                  prev.phase === 1
                    ? "Great! I've found your initial matches. Let me show you a quick preview."
                    : prev.phase === 2
                      ? "Excellent! Your results are getting more precise. Here's your updated top 10."
                      : "Perfect! I now have a comprehensive understanding of your needs. Here are your final top 10 matches.",
                  'info'
                ),
              ],
            };
          });
        } else if (result.next_question) {
          setState((prev) => ({
            ...prev,
            answeredCount: prev.answeredCount + 1,
            isProcessing: false,
            currentQuestion: result.next_question!,
            messages: [
              ...prev.messages,
              createMessage('ai', result.next_question!.text, 'question'),
            ],
          }));
        }
      } catch (err) {
        if (!mountedRef.current) return;

        const message =
          err instanceof Error ? err.message : 'Failed to submit answer';

        setState((prev) => ({
          ...prev,
          isProcessing: false,
          error: message,
          messages: [
            ...prev.messages,
            createMessage(
              'ai',
              'Sorry, I had trouble processing that. Please try again.',
              'info'
            ),
          ],
        }));
      }
    },
    [state.sessionUuid]
  );

  // -------------------------------------------------------------------
  // advancePhase
  // -------------------------------------------------------------------
  const advancePhase = useCallback(async () => {
    const sessionUuid = state.sessionUuid;
    if (!sessionUuid) return;

    setState((prev) => ({
      ...prev,
      isProcessing: true,
      error: null,
      phaseComplete: false,
      recommendations: null,
    }));

    try {
      const result = await apiAdvancePhase(sessionUuid);

      if (!mountedRef.current) return;

      setState((prev) => ({
        ...prev,
        phase: result.phase,
        currentQuestion: result.question,
        accuracy: ACCURACY_MAP[result.phase] ?? prev.accuracy,
        isProcessing: false,
        messages: [
          ...prev.messages,
          createMessage(
            'ai',
            result.phase === 2
              ? "Let's refine your matches. I have a few more questions about your preferences."
              : "Almost there! These last questions will help me find your perfect match.",
            'info'
          ),
          createMessage('ai', result.question.text, 'question'),
        ],
      }));
    } catch (err) {
      if (!mountedRef.current) return;

      const message =
        err instanceof Error
          ? err.message
          : 'Failed to advance to next phase';

      setState((prev) => ({
        ...prev,
        isProcessing: false,
        error: message,
        messages: [
          ...prev.messages,
          createMessage(
            'ai',
            'Sorry, something went wrong. Please try again.',
            'info'
          ),
        ],
      }));
    }
  }, [state.sessionUuid]);

  // -------------------------------------------------------------------
  // resumeSession
  // -------------------------------------------------------------------
  const resumeSession = useCallback(async (uuid: string) => {
    setState((prev) => ({
      ...prev,
      isProcessing: true,
      error: null,
      messages: [createMessage('ai', WELCOME_MESSAGE, 'info')],
    }));

    try {
      const session = await apiGetSession(uuid);

      if (!mountedRef.current) return;

      const phase = session.phase;
      const answeredCount = computeAnsweredCount(
        phase,
        session.current_question_index
      );

      saveSessionToStorage(session.uuid);

      setState((prev) => ({
        ...prev,
        sessionUuid: session.uuid,
        phase,
        answeredCount,
        accuracy: ACCURACY_MAP[phase] ?? 72,
        isProcessing: false,
        messages: [
          ...prev.messages,
          createMessage(
            'ai',
            "Welcome back! Let's continue where you left off.",
            'info'
          ),
        ],
      }));
    } catch (err) {
      if (!mountedRef.current) return;

      // Session expired or invalid — allow fresh start
      saveSessionToStorage(null);

      const message =
        err instanceof Error
          ? err.message
          : 'Failed to resume session';

      setState((prev) => ({
        ...prev,
        isProcessing: false,
        error: message,
        sessionUuid: null,
        messages: [
          ...prev.messages,
          createMessage(
            'ai',
            "Your previous session has expired. Let's start fresh!",
            'info'
          ),
        ],
      }));
    }
  }, []);

  return {
    ...state,
    startSession,
    submitAnswer,
    advancePhase,
    resumeSession,
  };
}
