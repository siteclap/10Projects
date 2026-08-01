/**
 * Price formatting utilities for the Indian real estate market.
 *
 * Uses the Indian numbering system: Lakh (1,00,000) and Crore (1,00,00,000).
 * Ported from the WordPress theme's `TP.formatPrice`.
 */

const CRORE = 10_000_000;
const LAKH = 100_000;

/**
 * Format a price amount using the Indian numbering system.
 *
 * @param amount  Price in rupees (e.g. 85_00_000 for 85 lakhs)
 * @returns       Formatted string like "85 L", "1.25 Cr", or "Price on request"
 */
export function formatPrice(amount: number | undefined | null): string {
  if (!amount || amount <= 0) return 'Price on request';

  if (amount >= CRORE) {
    const cr = (amount / CRORE).toFixed(2).replace(/\.00$/, '');
    return `\u20B9${cr} Cr`;
  }

  if (amount >= LAKH) {
    const l = (amount / LAKH).toFixed(2).replace(/\.00$/, '');
    return `\u20B9${l} L`;
  }

  return `\u20B9${amount.toLocaleString('en-IN')}`;
}

/**
 * Format a price range using the Indian numbering system.
 *
 * @param min  Minimum price in rupees
 * @param max  Maximum price in rupees
 * @returns    Formatted range like "85 L - 1.25 Cr" or single price if min === max
 */
export function formatPriceRange(min: number, max: number): string {
  if (!min && !max) return 'Price on request';
  if (!min) return formatPrice(max);
  if (!max) return formatPrice(min);
  if (min === max) return formatPrice(min);

  // Strip the rupee symbol from the first price to avoid duplication
  const minFormatted = formatPrice(min).replace('\u20B9', '');
  const maxFormatted = formatPrice(max);

  return `\u20B9${minFormatted} - ${maxFormatted}`;
}
