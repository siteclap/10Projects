<?php
/**
 * Server-side render for EMI Calculator block.
 *
 * @package TenProjects
 * @since 1.0.0
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$default_amount = absint( $attributes['default_amount'] ?? 7500000 );
$default_rate   = floatval( $attributes['default_rate'] ?? 8.5 );
$default_tenure = absint( $attributes['default_tenure'] ?? 20 );

// Enqueue the project-actions.js which handles calculator logic.
wp_enqueue_script(
    'tp-project-actions',
    get_theme_file_uri( 'assets/js/project-actions.js' ),
    array(),
    TP_PLUGIN_VERSION,
    true
);

$defaults_json = wp_json_encode( array(
    'amount' => $default_amount,
    'rate'   => $default_rate,
    'tenure' => $default_tenure,
) );

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'wp-block-tenprojects-emi-calculator',
) );

// Calculate initial EMI for server-rendered display.
$loan_amount  = $default_amount * 0.8;
$monthly_rate = ( $default_rate / 100 ) / 12;
$total_months = $default_tenure * 12;

if ( $monthly_rate > 0 ) {
    $emi = $loan_amount * $monthly_rate * pow( 1 + $monthly_rate, $total_months ) / ( pow( 1 + $monthly_rate, $total_months ) - 1 );
} else {
    $emi = $loan_amount / $total_months;
}

$total_payable = $emi * $total_months;
$total_interest = $total_payable - $loan_amount;

?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="emi-calculator-widget" data-defaults='<?php echo esc_attr( $defaults_json ); ?>'>
        <h3><?php esc_html_e( 'EMI Calculator', 'tenprojects' ); ?></h3>
        <div class="emi-calc__inputs">
            <label>
                <?php esc_html_e( 'Loan Amount', 'tenprojects' ); ?>
                <span class="emi-calc__value" data-for="amount"><?php echo esc_html( function_exists( 'tp_format_price' ) ? tp_format_price( $default_amount ) : '₹' . number_format( $default_amount ) ); ?></span>
                <input type="range" min="500000" max="50000000" step="100000" class="emi-calc__amount" value="<?php echo esc_attr( $default_amount ); ?>">
            </label>
            <label>
                <?php esc_html_e( 'Interest Rate', 'tenprojects' ); ?>
                <span class="emi-calc__value" data-for="rate"><?php echo esc_html( $default_rate . '%' ); ?></span>
                <input type="range" min="6" max="15" step="0.1" class="emi-calc__rate" value="<?php echo esc_attr( $default_rate ); ?>">
            </label>
            <label>
                <?php esc_html_e( 'Tenure (years)', 'tenprojects' ); ?>
                <span class="emi-calc__value" data-for="tenure"><?php echo esc_html( $default_tenure . ' yrs' ); ?></span>
                <input type="range" min="5" max="30" step="1" class="emi-calc__tenure" value="<?php echo esc_attr( $default_tenure ); ?>">
            </label>
        </div>
        <div class="emi-calc__result">
            <div class="emi-calc__emi"><?php echo '₹' . esc_html( number_format( round( $emi ) ) ) . '/mo'; ?></div>
            <div class="emi-calc__details">
                <?php
                printf(
                    /* translators: 1: Total interest amount, 2: Total payable amount */
                    esc_html__( 'Total Interest: %1$s | Total Payable: %2$s', 'tenprojects' ),
                    '₹' . number_format( round( $total_interest ) ),
                    '₹' . number_format( round( $total_payable ) )
                );
                ?>
            </div>
        </div>
    </div>
</div>
