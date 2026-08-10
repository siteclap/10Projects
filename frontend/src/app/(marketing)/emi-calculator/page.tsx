import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';
import { EmiCalculator } from '@/components/widgets/EmiCalculator';

export const metadata: Metadata = {
  title: 'EMI Calculator — Home Loan EMI Calculator | 10Projects',
  description:
    'Calculate your home loan EMI instantly. Free EMI calculator with adjustable loan amount, interest rate, and tenure for properties in Navi Mumbai.',
};

export default function EmiCalculatorPage() {
  return (
    <Container>
      <div className="mx-auto max-w-narrow py-4xl">
        <h1 className="text-h1 text-gray-900">Home Loan EMI Calculator</h1>
        <p className="mt-md text-base text-gray-500">
          Calculate your monthly EMI for any property. Adjust the loan amount,
          interest rate, and tenure to plan your home purchase.
        </p>

        <div className="mt-3xl">
          <EmiCalculator defaultPrice={7500000} />
        </div>

        {/* Info section */}
        <div className="mt-3xl border-t border-gray-100 pt-3xl">
          <h2 className="text-h3 text-gray-900">How is EMI Calculated?</h2>
          <p className="mt-lg text-sm leading-relaxed text-gray-600">
            EMI (Equated Monthly Instalment) is calculated using the formula:
            EMI = P × r × (1 + r)^n / ((1 + r)^n – 1), where P is the
            principal loan amount, r is the monthly interest rate, and n is the
            loan tenure in months. The calculator assumes a standard reducing
            balance method used by most Indian banks.
          </p>

          <h2 className="mt-2xl text-h3 text-gray-900">
            Current Home Loan Interest Rates
          </h2>
          <div className="mt-lg overflow-x-auto rounded-md border border-gray-200">
            <table className="w-full text-left text-sm">
              <thead>
                <tr className="border-b border-gray-200 bg-gray-50">
                  <th className="px-xl py-lg font-semibold text-gray-700">Bank</th>
                  <th className="px-xl py-lg font-semibold text-gray-700">Interest Rate</th>
                  <th className="px-xl py-lg font-semibold text-gray-700">Processing Fee</th>
                </tr>
              </thead>
              <tbody className="text-gray-600">
                <tr className="border-b border-gray-100">
                  <td className="px-xl py-lg font-medium text-gray-900">SBI</td>
                  <td className="px-xl py-lg">8.50% onwards</td>
                  <td className="px-xl py-lg">0.35% of loan amount</td>
                </tr>
                <tr className="border-b border-gray-100">
                  <td className="px-xl py-lg font-medium text-gray-900">HDFC</td>
                  <td className="px-xl py-lg">8.75% onwards</td>
                  <td className="px-xl py-lg">0.50% of loan amount</td>
                </tr>
                <tr className="border-b border-gray-100">
                  <td className="px-xl py-lg font-medium text-gray-900">ICICI</td>
                  <td className="px-xl py-lg">8.75% onwards</td>
                  <td className="px-xl py-lg">0.50% of loan amount</td>
                </tr>
                <tr className="border-b border-gray-100">
                  <td className="px-xl py-lg font-medium text-gray-900">Axis Bank</td>
                  <td className="px-xl py-lg">8.75% onwards</td>
                  <td className="px-xl py-lg">1% of loan amount</td>
                </tr>
                <tr>
                  <td className="px-xl py-lg font-medium text-gray-900">Kotak Mahindra</td>
                  <td className="px-xl py-lg">8.70% onwards</td>
                  <td className="px-xl py-lg">0.50% of loan amount</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p className="mt-md text-caption text-gray-400">
            * Rates are indicative and may vary. Please check with the respective bank for latest rates.
          </p>
        </div>
      </div>
    </Container>
  );
}
