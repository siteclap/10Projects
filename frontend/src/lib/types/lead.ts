export interface Lead {
  id: number;
  customer_id: number;
  lead_type:
    | 'ai_recommendation'
    | 'site_visit'
    | 'best_price'
    | 'advisor'
    | 'callback'
    | 'whatsapp'
    | 'comparison'
    | 'organic';
  project_id: number | null;
  status:
    | 'new'
    | 'contacted'
    | 'qualified'
    | 'site_visit_scheduled'
    | 'site_visit_done'
    | 'negotiating'
    | 'booked'
    | 'lost'
    | 'invalid';
  quality_score: number;
  classification: string;
  message: string | null;
  preferred_time: string | null;
  source_page: string | null;
  created_at: string;
}

export interface CreateLeadParams {
  lead_type: Lead['lead_type'];
  project_id?: number;
  message?: string;
  preferred_time?: string;
  source_page?: string;
}
