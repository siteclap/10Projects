/**
 * Assessment types for the AI-powered property matching flow.
 *
 * The assessment is a multi-phase questionnaire that builds a buyer profile
 * used by the scoring engine to rank projects.
 */

export interface QuestionOption {
  value: string;
  label: string;
  icon?: string;
  description?: string;
}

export interface Question {
  key: string;
  text: string;
  type: 'chips' | 'cards' | 'textarea';
  options?: QuestionOption[];
  multi_select?: boolean;
  max_rank?: number;
}

export interface AssessmentAnswer {
  question_key: string;
  value: string | string[] | number | Record<string, unknown>;
}

export interface AssessmentSession {
  uuid: string;
  phase: number;
  current_question_index: number;
  total_questions: number;
  answers: AssessmentAnswer[];
  created_at: string;
}
