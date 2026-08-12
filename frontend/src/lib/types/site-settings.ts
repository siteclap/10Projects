export interface SiteSettings {
  site_name: string;
  logo_light: string;
  logo_dark: string;
  favicon: string;
  hero_desktop: string;
  hero_mobile: string;
  phone: string;
  email: string;
  address: string;
  rera_agent: string;
  rera_legal_name: string;
  about: string;
  colors: {
    primary: string;
    primary_dark: string;
    accent: string;
    hero_bg: string;
  };
  social: {
    facebook: string;
    instagram: string;
    linkedin: string;
    youtube: string;
    twitter: string;
    whatsapp: string;
  };
}
