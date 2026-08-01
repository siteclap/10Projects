<?php
/**
 * EMI Calculator — Indian real estate formulas.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Helpers;

defined( 'ABSPATH' ) || exit;

class EMI_Calculator {

    /** @var float Default interest rate (%). */
    private const DEFAULT_RATE = 8.5;

    /** @var int Default loan tenure in years. */
    private const DEFAULT_TENURE = 20;

    /** @var float Default LTV ratio. */
    private const DEFAULT_LTV = 0.80;

    /**
     * Calculate monthly EMI.
     *
     * @param int   $property_price Property price in rupees.
     * @param float $rate           Annual interest rate (%).
     * @param int   $tenure         Loan tenure in years.
     * @param float $ltv            Loan-to-value ratio (0-1).
     * @return int Monthly EMI in rupees.
     */
    public static function calculate( $property_price, $rate = null, $tenure = null, $ltv = null ) {
        $rate   = $rate ?? self::DEFAULT_RATE;
        $tenure = $tenure ?? self::DEFAULT_TENURE;
        $ltv    = $ltv ?? self::DEFAULT_LTV;

        $loan_amount  = $property_price * $ltv;
        $monthly_rate = ( $rate / 100 ) / 12;
        $total_months = $tenure * 12;

        if ( $monthly_rate <= 0 ) {
            return intval( $loan_amount / $total_months );
        }

        $emi = $loan_amount * $monthly_rate * pow( 1 + $monthly_rate, $total_months )
             / ( pow( 1 + $monthly_rate, $total_months ) - 1 );

        return intval( round( $emi ) );
    }

    /**
     * Get full EMI breakdown.
     *
     * @param int   $property_price Property price.
     * @param float $rate           Interest rate.
     * @param int   $tenure         Tenure in years.
     * @param float $ltv            LTV ratio.
     * @return array EMI breakdown.
     */
    public static function breakdown( $property_price, $rate = null, $tenure = null, $ltv = null ) {
        $rate   = $rate ?? self::DEFAULT_RATE;
        $tenure = $tenure ?? self::DEFAULT_TENURE;
        $ltv    = $ltv ?? self::DEFAULT_LTV;

        $loan_amount  = $property_price * $ltv;
        $down_payment = $property_price - $loan_amount;
        $monthly_emi  = self::calculate( $property_price, $rate, $tenure, $ltv );
        $total_months = $tenure * 12;
        $total_paid   = $monthly_emi * $total_months;
        $total_interest = $total_paid - $loan_amount;

        return array(
            'property_price'  => $property_price,
            'down_payment'    => intval( $down_payment ),
            'loan_amount'     => intval( $loan_amount ),
            'interest_rate'   => $rate,
            'tenure_years'    => $tenure,
            'monthly_emi'     => $monthly_emi,
            'total_interest'  => intval( $total_interest ),
            'total_payment'   => intval( $total_paid ),
            'ltv_ratio'       => $ltv,
        );
    }

    /**
     * Calculate stamp duty and registration charges.
     * Maharashtra rates (2026).
     *
     * @param int    $property_price Property price.
     * @param string $city           City (affects rates).
     * @param string $buyer_gender   Gender ('male', 'female').
     * @return array Charges breakdown.
     */
    public static function stamp_duty( $property_price, $city = 'navi-mumbai', $buyer_gender = 'male' ) {
        // Maharashtra stamp duty rates (2026).
        $navi_mumbai_cities = array( 'navi-mumbai', 'panvel', 'kharghar', 'vashi' );
        $mumbai_cities      = array( 'mumbai', 'thane' );

        if ( in_array( $city, $navi_mumbai_cities, true ) ) {
            // Navi Mumbai: 6% stamp duty (5% for women).
            $stamp_rate = ( $buyer_gender === 'female' ) ? 5.0 : 6.0;
        } elseif ( in_array( $city, $mumbai_cities, true ) ) {
            // Mumbai: 6% stamp duty (5% for women).
            $stamp_rate = ( $buyer_gender === 'female' ) ? 5.0 : 6.0;
        } else {
            // Other Maharashtra: 7% (6% for women).
            $stamp_rate = ( $buyer_gender === 'female' ) ? 6.0 : 7.0;
        }

        // Registration: 1% (capped at ₹30,000).
        $stamp_duty   = intval( $property_price * $stamp_rate / 100 );
        $registration = min( intval( $property_price * 0.01 ), 30000 );

        // GST on under-construction (5% for affordable, 12% for luxury; simplified to 5%).
        $gst = 0; // Applied only for under-construction — handled separately.

        return array(
            'stamp_duty'          => $stamp_duty,
            'stamp_duty_rate'     => $stamp_rate,
            'registration'        => $registration,
            'gst'                 => $gst,
            'total_extra'         => $stamp_duty + $registration + $gst,
            'total_with_property' => $property_price + $stamp_duty + $registration + $gst,
        );
    }

    /**
     * Calculate affordable EMI range based on income.
     *
     * @param int   $monthly_income Monthly income.
     * @param float $emi_ratio      Max EMI-to-income ratio (default 0.40 = 40%).
     * @return int Maximum affordable EMI.
     */
    public static function affordable_emi( $monthly_income, $emi_ratio = 0.40 ) {
        return intval( $monthly_income * $emi_ratio );
    }

    /**
     * Calculate maximum loan amount from affordable EMI.
     *
     * @param int   $max_emi Max monthly EMI.
     * @param float $rate    Interest rate.
     * @param int   $tenure  Tenure in years.
     * @return int Maximum loan amount.
     */
    public static function max_loan_from_emi( $max_emi, $rate = null, $tenure = null ) {
        $rate   = $rate ?? self::DEFAULT_RATE;
        $tenure = $tenure ?? self::DEFAULT_TENURE;

        $monthly_rate = ( $rate / 100 ) / 12;
        $total_months = $tenure * 12;

        if ( $monthly_rate <= 0 ) {
            return $max_emi * $total_months;
        }

        $max_loan = $max_emi * ( pow( 1 + $monthly_rate, $total_months ) - 1 )
                  / ( $monthly_rate * pow( 1 + $monthly_rate, $total_months ) );

        return intval( round( $max_loan ) );
    }
}
