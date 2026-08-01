/**
 * EMI calculation utilities for Indian home loans.
 *
 * Defaults:
 *   - Interest rate: 8.5% p.a.
 *   - Tenure: 20 years
 *   - LTV (Loan-to-Value): 80% — banks typically finance 80% of property value
 *
 * Ported from the WordPress theme's `TP.calculateEMI`.
 */

const DEFAULT_RATE = 8.5;
const DEFAULT_TENURE = 20;
const LTV_RATIO = 0.8;

/**
 * Calculate the monthly EMI for a home loan.
 *
 * @param principal  Property price in rupees (LTV of 80% is applied automatically)
 * @param rate       Annual interest rate as a percentage (default: 8.5)
 * @param tenure     Loan tenure in years (default: 20)
 * @returns          Monthly EMI amount in rupees
 */
export function calculateEMI(
  principal: number,
  rate: number = DEFAULT_RATE,
  tenure: number = DEFAULT_TENURE
): number {
  const loanAmount = principal * LTV_RATIO;
  const monthlyRate = rate / 100 / 12;
  const months = tenure * 12;

  if (monthlyRate <= 0) {
    return loanAmount / months;
  }

  const compoundFactor = Math.pow(1 + monthlyRate, months);
  return (loanAmount * monthlyRate * compoundFactor) / (compoundFactor - 1);
}

/**
 * Calculate the total amount payable over the loan tenure.
 *
 * @param principal  Property price in rupees
 * @param rate       Annual interest rate as a percentage (default: 8.5)
 * @param tenure     Loan tenure in years (default: 20)
 * @returns          Total payable amount (EMI x months)
 */
export function calculateTotalPayable(
  principal: number,
  rate: number = DEFAULT_RATE,
  tenure: number = DEFAULT_TENURE
): number {
  const emi = calculateEMI(principal, rate, tenure);
  const months = tenure * 12;
  return emi * months;
}

/**
 * Calculate the total interest payable over the loan tenure.
 *
 * @param principal  Property price in rupees
 * @param rate       Annual interest rate as a percentage (default: 8.5)
 * @param tenure     Loan tenure in years (default: 20)
 * @returns          Total interest amount (total payable minus loan amount)
 */
export function calculateTotalInterest(
  principal: number,
  rate: number = DEFAULT_RATE,
  tenure: number = DEFAULT_TENURE
): number {
  const totalPayable = calculateTotalPayable(principal, rate, tenure);
  const loanAmount = principal * LTV_RATIO;
  return totalPayable - loanAmount;
}
