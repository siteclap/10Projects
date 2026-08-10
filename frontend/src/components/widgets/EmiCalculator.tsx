'use client';

import { useState } from 'react';
import { calculateEMI, calculateTotalInterest, calculateTotalPayable } from '@/lib/utils/calculate-emi';
import { formatPrice } from '@/lib/utils/format-price';

const DOWN_PAYMENT_PCT = 20;

interface EmiCalculatorProps {
  defaultPrice?: number;
}

function formatInputPrice(value: number): string {
  if (value >= 10000000) return `${(value / 10000000).toFixed(2)} Cr`;
  if (value >= 100000) return `${(value / 100000).toFixed(2)} L`;
  return value.toLocaleString('en-IN');
}

function DonutChart({ principal, interest }: { principal: number; interest: number }) {
  const total = principal + interest;
  if (total === 0) return null;
  const principalPct = principal / total;
  const radius = 54;
  const circumference = 2 * Math.PI * radius;
  const principalArc = circumference * principalPct;
  const interestArc = circumference - principalArc;

  return (
    <svg width="140" height="140" viewBox="0 0 140 140" className="shrink-0">
      <circle cx="70" cy="70" r={radius} fill="none" stroke="#E5E7EB" strokeWidth="16" />
      {/* Principal arc */}
      <circle
        cx="70"
        cy="70"
        r={radius}
        fill="none"
        stroke="#4B1CB0"
        strokeWidth="16"
        strokeDasharray={`${principalArc} ${interestArc}`}
        strokeDashoffset={circumference * 0.25}
        strokeLinecap="round"
        className="transition-all duration-500"
      />
      {/* Interest arc */}
      <circle
        cx="70"
        cy="70"
        r={radius}
        fill="none"
        stroke="#F59E0B"
        strokeWidth="16"
        strokeDasharray={`${interestArc} ${principalArc}`}
        strokeDashoffset={circumference * 0.25 - principalArc}
        strokeLinecap="round"
        className="transition-all duration-500"
      />
      {/* Center text */}
      <text x="70" y="64" textAnchor="middle" className="fill-gray-400 text-[10px]">
        EMI/mo
      </text>
      <text x="70" y="80" textAnchor="middle" className="fill-gray-900 text-[13px] font-semibold">
        {formatPrice(Math.round(calculateEMI(principal / 0.8, 8.5, 20)))}
      </text>
    </svg>
  );
}

export function EmiCalculator({ defaultPrice = 8500000 }: EmiCalculatorProps) {
  const [price, setPrice] = useState(defaultPrice);
  const [downPaymentPct, setDownPaymentPct] = useState(DOWN_PAYMENT_PCT);
  const [rate, setRate] = useState(8.5);
  const [tenure, setTenure] = useState(20);

  const downPayment = Math.round(price * (downPaymentPct / 100));
  const loanAmount = price - downPayment;
  const emi = calculateEMI(loanAmount, rate, tenure);
  const totalInterest = calculateTotalInterest(loanAmount, rate, tenure);
  const totalPayable = calculateTotalPayable(loanAmount, rate, tenure);

  // Donut chart values
  const principalPct = totalPayable > 0 ? loanAmount / totalPayable : 0;
  const radius = 54;
  const circumference = 2 * Math.PI * radius;
  const principalArc = circumference * principalPct;
  const interestArc = circumference - principalArc;

  return (
    <div className="rounded-lg border border-gray-200 bg-white shadow-card">
      {/* Header */}
      <div className="p-xl pb-0">
        <h3 className="text-base font-semibold text-gray-900">Home Loan Calculator</h3>
        <p className="mt-xs text-sm text-gray-500">
          Starting from <span className="font-semibold text-brand-primary">{formatPrice(Math.round(emi))}/month</span>
        </p>
      </div>

      <div className="flex flex-col gap-xl p-xl lg:flex-row">
        {/* Left — Inputs */}
        <div className="flex-1 rounded-lg bg-gray-50 p-lg">
          <p className="text-caption text-gray-500">
            Explore the cost of your home by adjusting the details
          </p>

          <div className="mt-lg grid grid-cols-2 gap-md">
            {/* Property Price */}
            <div>
              <label htmlFor="emi-price" className="text-caption font-medium text-gray-600">
                Property Price
              </label>
              <div className="relative mt-xs">
                <span className="absolute left-md top-1/2 -translate-y-1/2 text-sm text-gray-400">&#8377;</span>
                <input
                  id="emi-price"
                  type="number"
                  min={100000}
                  max={500000000}
                  step={100000}
                  value={price}
                  onChange={(e) => {
                    const num = Number(e.target.value);
                    if (!isNaN(num) && num >= 0) setPrice(num);
                  }}
                  className="h-[40px] w-full rounded-md border border-gray-200 bg-white pl-[28px] pr-md text-sm font-medium text-gray-900 tabular-nums focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary [&::-webkit-inner-spin-button]:opacity-100"
                />
                <span className="absolute right-md top-1/2 -translate-y-1/2 text-[10px] text-gray-400">
                  {formatInputPrice(price)}
                </span>
              </div>
            </div>

            {/* Down Payment */}
            <div>
              <label htmlFor="emi-down" className="text-caption font-medium text-gray-600">
                Down Payment
              </label>
              <div className="relative mt-xs">
                <input
                  id="emi-down"
                  type="number"
                  min={5}
                  max={90}
                  value={downPaymentPct}
                  onChange={(e) => setDownPaymentPct(Math.min(90, Math.max(5, Number(e.target.value))))}
                  className="h-[40px] w-full rounded-md border border-gray-200 bg-white px-md pr-[36px] text-sm font-medium text-gray-900 tabular-nums focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                />
                <span className="absolute right-md top-1/2 -translate-y-1/2 text-sm text-gray-400">%</span>
              </div>
            </div>
          </div>

          {/* Loan Amount */}
          <div className="mt-md">
            <label className="text-caption font-medium text-gray-600">
              Loan Amount after down payment
            </label>
            <div className="relative mt-xs">
              <span className="absolute left-md top-1/2 -translate-y-1/2 text-sm text-gray-400">&#8377;</span>
              <div className="flex h-[40px] w-full items-center justify-between rounded-md border border-gray-200 bg-gray-100 pl-[28px] pr-md text-sm font-medium text-gray-900 tabular-nums">
                <span>{loanAmount.toLocaleString('en-IN')}</span>
                <span className="text-[10px] text-gray-400">{formatInputPrice(loanAmount)}</span>
              </div>
            </div>
          </div>

          <div className="mt-md grid grid-cols-2 gap-md">
            {/* Tenure */}
            <div>
              <label htmlFor="emi-tenure" className="text-caption font-medium text-gray-600">
                Loan Tenure
              </label>
              <div className="relative mt-xs">
                <input
                  id="emi-tenure"
                  type="number"
                  min={1}
                  max={30}
                  value={tenure}
                  onChange={(e) => setTenure(Math.min(30, Math.max(1, Number(e.target.value))))}
                  className="h-[40px] w-full rounded-md border border-gray-200 bg-white px-md pr-[40px] text-sm font-medium text-gray-900 tabular-nums focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                />
                <span className="absolute right-md top-1/2 -translate-y-1/2 text-sm text-gray-400">Years</span>
              </div>
            </div>

            {/* Interest Rate */}
            <div>
              <label htmlFor="emi-rate" className="text-caption font-medium text-gray-600">
                Rate of Interest
              </label>
              <div className="relative mt-xs">
                <input
                  id="emi-rate"
                  type="number"
                  min={1}
                  max={20}
                  step={0.1}
                  value={rate}
                  onChange={(e) => setRate(Math.min(20, Math.max(1, Number(e.target.value))))}
                  className="h-[40px] w-full rounded-md border border-gray-200 bg-white px-md pr-[28px] text-sm font-medium text-gray-900 tabular-nums focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary"
                />
                <span className="absolute right-md top-1/2 -translate-y-1/2 text-sm text-gray-400">%</span>
              </div>
            </div>
          </div>
        </div>

        {/* Right — Donut Chart */}
        <div className="flex flex-col items-center justify-center gap-lg lg:w-[200px]">
          <svg width="160" height="160" viewBox="0 0 160 160" aria-hidden="true">
            {/* Background circle */}
            <circle cx="80" cy="80" r={radius} fill="none" stroke="#F3F4F6" strokeWidth="18" />
            {/* Principal arc */}
            <circle
              cx="80"
              cy="80"
              r={radius}
              fill="none"
              stroke="#4B1CB0"
              strokeWidth="18"
              strokeDasharray={`${principalArc} ${interestArc}`}
              strokeDashoffset={circumference * 0.25}
              strokeLinecap="round"
              className="transition-all duration-500"
            />
            {/* Interest arc */}
            <circle
              cx="80"
              cy="80"
              r={radius}
              fill="none"
              stroke="#F59E0B"
              strokeWidth="18"
              strokeDasharray={`${interestArc} ${principalArc}`}
              strokeDashoffset={circumference * 0.25 - principalArc}
              strokeLinecap="round"
              className="transition-all duration-500"
            />
            {/* Center text */}
            <text x="80" y="72" textAnchor="middle" className="fill-gray-400 text-[10px]">
              EMI/mo
            </text>
            <text x="80" y="90" textAnchor="middle" className="fill-gray-900 text-[15px] font-bold">
              {formatPrice(Math.round(emi))}
            </text>
          </svg>

          {/* Legend */}
          <div className="flex flex-col gap-sm text-sm">
            <div className="flex items-center gap-sm">
              <span className="h-[10px] w-[10px] rounded-full bg-brand-primary" />
              <span className="text-caption text-gray-600">Principal: {formatPrice(Math.round(loanAmount))}</span>
            </div>
            <div className="flex items-center gap-sm">
              <span className="h-[10px] w-[10px] rounded-full bg-accent" />
              <span className="text-caption text-gray-600">Interest: {formatPrice(Math.round(totalInterest))}</span>
            </div>
          </div>

          <div className="w-full border-t border-gray-100 pt-md text-center">
            <p className="text-[10px] uppercase tracking-wider text-gray-400">Total Amount</p>
            <p className="text-sm font-bold text-gray-900 tabular-nums">{formatPrice(Math.round(totalPayable))}</p>
          </div>
        </div>
      </div>
    </div>
  );
}
