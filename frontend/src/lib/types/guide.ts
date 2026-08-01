export interface Guide {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  content: string;
  thumbnail: string | null;
  category: string;
  published_at: string;
  reading_time: number;
  author: string;
}

export interface GuideCard {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  thumbnail: string | null;
  category: string;
  published_at: string;
  reading_time: number;
}
