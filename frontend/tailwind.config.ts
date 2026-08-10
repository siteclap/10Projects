import type { Config } from 'tailwindcss';

const config: Config = {
  content: [
    './src/**/*.{ts,tsx,mdx}',
  ],
  theme: {
    fontFamily: {
      sans: [
        'Inter',
        '-apple-system',
        'BlinkMacSystemFont',
        'Segoe UI',
        'system-ui',
        'sans-serif',
      ],
    },
    fontSize: {
      caption: ['12px', { lineHeight: '16px', fontWeight: '500' }],
      sm: ['14px', { lineHeight: '20px', fontWeight: '400' }],
      base: ['16px', { lineHeight: '24px', fontWeight: '400' }],
      'body-lg': ['18px', { lineHeight: '28px', fontWeight: '400' }],
      h4: ['18px', { lineHeight: '26px', fontWeight: '600' }],
      h3: ['22px', { lineHeight: '30px', fontWeight: '600' }],
      h2: ['28px', { lineHeight: '36px', fontWeight: '700' }],
      h1: ['36px', { lineHeight: '44px', fontWeight: '700' }],
      display: ['48px', { lineHeight: '56px', fontWeight: '800' }],
      price: ['24px', { lineHeight: '32px', fontWeight: '700' }],
      score: ['32px', { lineHeight: '40px', fontWeight: '700' }],
    },
    spacing: {
      '0': '0px',
      px: '1px',
      xs: '4px',
      sm: '8px',
      md: '12px',
      lg: '16px',
      xl: '24px',
      '2xl': '32px',
      '3xl': '48px',
      '4xl': '64px',
      '5xl': '96px',
    },
    borderRadius: {
      none: '0px',
      sm: '8px',
      md: '12px',
      lg: '16px',
      xl: '20px',
      full: '9999px',
    },
    boxShadow: {
      none: 'none',
      xs: '0 1px 2px rgba(0,0,0,0.05)',
      card: '0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04)',
      hover: '0 10px 25px rgba(0,0,0,0.08), 0 4px 10px rgba(0,0,0,0.04)',
      dropdown: '0 4px 16px rgba(0,0,0,0.12)',
      sticky: '0 4px 12px rgba(0,0,0,0.06)',
      hero: '0 20px 60px rgba(0,0,0,0.15)',
    },
    maxWidth: {
      container: '1280px',
      narrow: '768px',
      assessment: '640px',
    },
    extend: {
      colors: {
        brand: {
          primary: '#4B1CB0',
          'primary-dark': '#3B1490',
          'primary-light': '#7C3AED',
          'primary-pale': '#EDE9FE',
          'primary-bg': '#F5F3FF',
        },
        accent: {
          DEFAULT: '#F59E0B',
          dark: '#D97706',
          light: '#FDE68A',
          pale: '#FEF3C7',
        },
        success: {
          DEFAULT: '#10B981',
          light: '#D1FAE5',
          bg: '#F0FDF4',
        },
        warning: {
          DEFAULT: '#D97706',
          bg: '#FEF3C7',
        },
        danger: {
          DEFAULT: '#EF4444',
          light: '#FEE2E2',
        },
        info: '#7C3AED',
        gray: {
          50: '#F9FAFB',
          100: '#F3F4F6',
          200: '#E5E7EB',
          300: '#D1D5DB',
          400: '#9CA3AF',
          500: '#6B7280',
          600: '#4B5563',
          700: '#374151',
          800: '#1F2937',
          900: '#111827',
        },
        section: {
          alt: '#F8FAFC',
        },
      },
      letterSpacing: {
        display: '-0.02em',
        heading: '-0.01em',
      },
      fontVariantNumeric: {
        tabular: 'tabular-nums',
      },
    },
  },
  plugins: [],
};

export default config;
