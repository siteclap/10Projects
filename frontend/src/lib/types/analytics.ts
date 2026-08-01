export type AnalyticsEventName =
  | 'page_view'
  | 'assessment_started'
  | 'assessment_completed'
  | 'assessment_phase_completed'
  | 'assessment_question_answered'
  | 'assessment_abandoned'
  | 'contact_requested'
  | 'otp_sent'
  | 'otp_verified'
  | 'results_viewed'
  | 'project_clicked'
  | 'project_viewed'
  | 'project_saved'
  | 'project_unsaved'
  | 'comparison_started'
  | 'comparison_viewed'
  | 'share_clicked'
  | 'whatsapp_clicked'
  | 'callback_requested'
  | 'site_visit_requested'
  | 'best_price_requested'
  | 'advisor_requested'
  | 'emi_calculated'
  | 'report_downloaded'
  | 'scroll_depth'
  | 'session_duration'
  | 'cta_clicked'
  | 'location_clicked'
  | 'developer_clicked';

export interface AnalyticsEvent {
  event_name: AnalyticsEventName;
  properties?: Record<string, string | number | boolean>;
  page_url?: string;
  referrer?: string;
  session_id?: string;
}

export interface UtmParams {
  utm_source?: string;
  utm_medium?: string;
  utm_campaign?: string;
  utm_content?: string;
  utm_term?: string;
  gclid?: string;
  fbclid?: string;
  referrer?: string;
  landing_page?: string;
}
