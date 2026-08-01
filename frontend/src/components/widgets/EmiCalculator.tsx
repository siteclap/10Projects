'use client';

import { useState, useEffect, useRef } from 'react';
import { cn } from '@/lib/utils/cn';
import { calculateEMI, calculateTotalInterest, calculateTotalPayable } from '@/lib/utils/calculate-emi';
import { formatPrice } from '@/lib/utils/format-price';

const LTV_DEFAULT = 0.8;
const DEBOUNCE_MS = 200;

interface EmiCalculatorProps {
  defaultPrice?: number;
}

function useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const timer = setTimeout(() => setDebouncedValue(value), delay);
    return () => clearTimeout(timer);
  }, [value, delay]);

  return debouncedValue;
}

export function EmiCalculator({ defaultPrice = 8500000 }: EmiCalculatorProps) {
  const [price, setPrice] = useState(defaultPrice);
  const [rate, setRate] = useState(8.5);
  const [tenure, setTenure] = useState(20);

  const debouncedPrice = useDebounce(price, DEBOUNCE_MS);
  const debouncedRate = useDebounce(rate, DEBOUNCE_MS);
  const debouncedTenure = useDebounce(tenure, DEBOUNCE_MS);

  const emi = calculateEMI(debouncedPrice, debouncedRate, debouncedTenure);
  const totalInterest = calculateTotalInterest(debouncedPrice, debouncedRate, debouncedTenure);
  const totalPayable = calculateTotalPayable(debouncedPrice, debouncedRate, debouncedTenure);
  const loanAmount = debouncedPrice * LTV_DEFAULT;

  return (
    <div className="rounded-md border border-gray-200 bg-white p-xl shadow-card">
      <h3 className="text-h4 text-gray-900">EMI Calculator</h3>
      <p className="mt-xs text-sm text-gray-500">
        Loan of {formatPrice(Math.round(loanAmount))} (80% LTV)
      </p>

      <div className="mt-xl flex flex-col gap-xl">
        {/* Property Price */}
        <div>
          <div className="flex items-center justify-between">
            <label htmlFor="emi-price" className="text-sm font-medium text-gray-700">
              Property Price
            </label>
            <span className="text-sm font-semibold text-gray-900 tabular-nums">
              {formatPrice(price)}
            </span>
          </div>
          <input
            id="emi-price"
            type="range"
            min={1000000}
            max={100000000}
            step={500000}
            value={price}
            onChange={(e) => setPrice(Number(e.target.value))}
            className="mt-sm w-full accent-brand-primary"
          />
          <div className="mt-xs flex justify-between text-caption text-gray-400">
            <span>10 L</span>
            <span>10 Cr</span>
          </div>
        </div>

        {/* Interest Rate */}
        <div>
          <div className="flex items-center justify-between">
            <label htmlFor="emi-rate" className="text-sm font-medium text-gray-700">
              Interest Rate
            </label>
            <span className="text-sm font-semibold text-gray-900 tabular-nums">
              {rate.toFixed(1)}%
            </span>
          </div>
          <input
            id="emi-rate"
            type="range"
            min={5}
            max={15}
            step={0.1}
            value={rate}
            onChange={(e) => setRate(Number(e.target.value))}
            className="mt-sm w-full accent-brand-primary"
          />
          <div className="mt-xs flex justify-between text-caption text-gray-400">
            <span>5%</span>
            <span>15%</span>
          </div>
        </div>

        {/* Tenure */}
        <div>
          <div className="flex items-center justify-between">
            <label htmlFor="emi-tenure" className="text-sm font-medium text-gray-700">
              Loan Tenure
            </label>
            <span className="text-sm font-semibold text-gray-900 tabular-nums">
              {tenure} yrs
            </span>
          </div>
          <input
            id="emi-tenure"
            type="range"
            min={5}
            max={30}
            step={1}
            value={tenure}
            onChange={(e) => setTenure(Number(e.target.value))}
            className="mt-sm w-full accent-brand-primary"
          />
          <div className="mt-xs flex justify-between text-caption text-gray-400">
            <span>5 yrs</span>
            <span>30 yrs</span>
          </div>
        </div>
      </div>

      {/* Results */}
      <div className="mt-xl border-t border-gray-200 pt-xl">
        <div className="flex flex-col gap-md">
          <div className="flex items-center justify-between">
            <span className="text-sm text-gray-600">Monthly EMI</span>
            <span className="text-price text-brand-primary">
              {formatPrice(Math.round(emi))}
            </span>
          </div>
          <div className="flex items-center justify-between">
            <span className="text-sm text-gray-600">Total Interest</span>
            <span className="text-sm font-semibold text-gray-900 tabular-nums">
              {formatPrice(Math.round(totalInterest))}
            </span>
          </div>
          <div className="flex items-center justify-between">
            <span className="text-sm text-gray-600">Total Payable</span>
            <span className="text-sm font-semibold text-gray-900 tabular-nums">
              {formatPrice(Math.round(totalPayable))}
            </span>
          </div>
        </div>
      </div>
    </div>
  );
}
