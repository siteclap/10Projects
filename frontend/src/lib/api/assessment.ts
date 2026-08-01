import { apiClient } from '@/lib/api/client';
import type { Question, AssessmentSession } from '@/lib/types/assessment';
import type { RecommendationResult } from '@/lib/types/recommendation';

/**
 * Response from POST /assessment/start
 */
interface StartSessionResponse {
  session_uuid: string;
  current_question: Question;
  phase: number;
}

/**
 * Response from POST /assessment/answer
 *
 * Either returns the next question in the current phase, or signals
 * phase completion with recommendation results.
 */
interface SubmitAnswerResponse {
  next_question?: Question;
  phase_complete?: boolean;
  recommendations?: RecommendationResult[];
  accuracy?: number;
}

/**
 * Response from POST /assessment/advance
 */
interface AdvancePhaseResponse {
  question: Question;
  phase: number;
}

/**
 * Start a new assessment session.
 *
 * Optionally pass the visitor ID (for anonymous tracking) and a pre-selected
 * city to skip the city question.
 *
 * @param visitorId  Anonymous visitor identifier from tp_visitor_id cookie
 * @param city       Pre-selected city slug (e.g. "navi-mumbai")
 * @returns          Session UUID, the first question, and the starting phase
 */
export async function startSession(
  visitorId?: string,
  city?: string
): Promise<StartSessionResponse> {
  const body: Record<string, unknown> = {};
  if (visitorId) body.visitor_id = visitorId;
  if (city) body.city = city;

  return apiClient<StartSessionResponse>('assessment/start', {
    method: 'POST',
    body,
  });
}

/**
 * Submit an answer for the current question in the assessment.
 *
 * The API either returns the next question or marks the phase as complete
 * with recommendation results.
 *
 * @param sessionUuid  Session identifier
 * @param questionKey  The key of the question being answered
 * @param value        The answer value (string, array, number, or object)
 * @returns            Next question or phase completion with recommendations
 */
export async function submitAnswer(
  sessionUuid: string,
  questionKey: string,
  value: unknown
): Promise<SubmitAnswerResponse> {
  return apiClient<SubmitAnswerResponse>('assessment/answer', {
    method: 'POST',
    body: {
      session_uuid: sessionUuid,
      question_key: questionKey,
      value,
    },
  });
}

/**
 * Advance the session to the next phase.
 *
 * Called after the user chooses to continue answering questions
 * to improve accuracy (e.g. from 72% to 89% to 96%).
 *
 * @param sessionUuid  Session identifier
 * @returns            First question of the next phase and the new phase number
 */
export async function advancePhase(
  sessionUuid: string
): Promise<AdvancePhaseResponse> {
  return apiClient<AdvancePhaseResponse>('assessment/advance', {
    method: 'POST',
    body: { session_uuid: sessionUuid },
  });
}

/**
 * Retrieve an existing assessment session for resumption.
 *
 * Used when the user returns and has a session UUID stored in localStorage.
 *
 * @param uuid  Session identifier
 * @returns     Full session state including answers and current position
 */
export async function getSession(uuid: string): Promise<AssessmentSession> {
  return apiClient<AssessmentSession>(`assessment/session/${uuid}`, {
    method: 'GET',
  });
}
