<?php
/**
 * Filter Bar — Housing.com-style dark navy bar with dropdowns + budget slider.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

// Current filter values from URL.
$cur_bhk        = isset( $_GET['bhk'] ) ? array_filter( array_map( 'trim', explode( ',', sanitize_text_field( $_GET['bhk'] ) ) ) ) : array();
$cur_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
$cur_budget_min = isset( $_GET['budget_min'] ) ? intval( $_GET['budget_min'] ) : 0;
$cur_budget_max = isset( $_GET['budget_max'] ) ? intval( $_GET['budget_max'] ) : 75000000;
$cur_sort       = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : '';
$cur_rera       = ! empty( $_GET['rera'] );

// Has active filters?
$has_filters = ! empty( $cur_bhk ) || $cur_status || $cur_budget_min > 0 || $cur_budget_max < 75000000 || $cur_rera || $cur_sort;

// Options.
$bhk_options = array( '1' => '1 BHK', '1.5' => '1.5 BHK', '2' => '2 BHK', '2.5' => '2.5 BHK', '3' => '3 BHK', '4+' => '4+ BHK' );

$statuses = array(
	'new-launch'         => 'New Launch',
	'under-construction' => 'Under Construction',
	'nearing-completion' => 'Nearing Completion',
	'ready-to-move'      => 'Ready to Move',
);

// Display labels for active filters.
$bhk_label = '';
if ( ! empty( $cur_bhk ) ) {
	$bhk_label = count( $cur_bhk ) <= 2
		? implode( ', ', array_map( function( $b ) { return $b . ' BHK'; }, $cur_bhk ) )
		: count( $cur_bhk ) . ' Selected';
}

$status_label = $cur_status && isset( $statuses[ $cur_status ] ) ? $statuses[ $cur_status ] : '';

$budget_label = '';
if ( $cur_budget_min > 0 || $cur_budget_max < 75000000 ) {
	$budget_label = tp_format_price( $cur_budget_min ) . ' – ' . tp_format_price( $cur_budget_max );
}

// Chevron SVG.
$chev = '<svg class="tp-fd__chev" width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2.5 3.75L5 6.25L7.5 3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>

<div class="tp-filter-bar" id="tp-filter-bar">
	<div class="tp-container">
		<div class="tp-fb__row">

			<!-- Filter icon -->
			<div class="tp-fb__icon">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
			</div>
			<div class="tp-fb__divider"></div>

			<!-- Configuration (BHK) -->
			<div class="tp-fd" data-filter="bhk">
				<button type="button" class="tp-fd__btn <?php echo ! empty( $cur_bhk ) ? 'is-active' : ''; ?>">
					<span class="tp-fd__btn-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></span>
					<span><?php echo $bhk_label ?: 'Configuration'; ?></span> <?php echo $chev; ?>
				</button>
				<div class="tp-fd__panel">
					<div class="tp-fd__head">Select Configuration</div>
					<div class="tp-fd__body tp-fd__body--chips">
						<?php foreach ( $bhk_options as $val => $label ) : ?>
							<label class="tp-fd__chip <?php echo in_array( (string) $val, $cur_bhk, true ) ? 'is-on' : ''; ?>">
								<input type="checkbox" name="_bhk[]" value="<?php echo esc_attr( $val ); ?>" <?php checked( in_array( (string) $val, $cur_bhk, true ) ); ?>>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="tp-fd__foot">
						<button type="button" class="tp-fd__reset" data-clear="bhk">Reset</button>
						<button type="button" class="tp-fd__apply">Apply</button>
					</div>
				</div>
			</div>

			<!-- Possession Status -->
			<div class="tp-fd" data-filter="status">
				<button type="button" class="tp-fd__btn <?php echo $cur_status ? 'is-active' : ''; ?>">
					<span class="tp-fd__btn-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></span>
					<span><?php echo $status_label ?: 'Possession Status'; ?></span> <?php echo $chev; ?>
				</button>
				<div class="tp-fd__panel">
					<div class="tp-fd__head">Possession Status</div>
					<div class="tp-fd__body">
						<label class="tp-fd__opt <?php echo ! $cur_status ? 'is-on' : ''; ?>">
							<input type="radio" name="_status" value="" <?php checked( $cur_status, '' ); ?>>
							<span>Any</span>
						</label>
						<?php foreach ( $statuses as $val => $label ) : ?>
							<label class="tp-fd__opt <?php echo $cur_status === $val ? 'is-on' : ''; ?>">
								<input type="radio" name="_status" value="<?php echo esc_attr( $val ); ?>" <?php checked( $cur_status, $val ); ?>>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="tp-fd__foot">
						<button type="button" class="tp-fd__reset" data-clear="status">Reset</button>
						<button type="button" class="tp-fd__apply">Apply</button>
					</div>
				</div>
			</div>

			<!-- Budget (Range Slider) -->
			<div class="tp-fd tp-fd--budget" data-filter="budget">
				<button type="button" class="tp-fd__btn <?php echo $budget_label ? 'is-active' : ''; ?>">
					<span class="tp-fd__btn-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
					<span><?php echo $budget_label ?: 'Budget'; ?></span> <?php echo $chev; ?>
				</button>
				<div class="tp-fd__panel tp-fd__panel--wide">
					<div class="tp-fd__head">Budget Range</div>
					<div class="tp-fd__body">
						<div class="tp-range" id="tp-range">
							<div class="tp-range__track">
								<div class="tp-range__fill" id="tp-range-fill"></div>
							</div>
							<input type="range" class="tp-range__input" id="tp-range-min" min="0" max="75000000" step="500000" value="<?php echo esc_attr( $cur_budget_min ); ?>">
							<input type="range" class="tp-range__input" id="tp-range-max" min="0" max="75000000" step="500000" value="<?php echo esc_attr( $cur_budget_max ); ?>">
						</div>
						<div class="tp-range__labels">
							<span class="tp-range__val" id="tp-range-val-min"><?php echo esc_html( tp_format_price( $cur_budget_min ) ?: '₹0' ); ?></span>
							<span class="tp-range__sep">—</span>
							<span class="tp-range__val" id="tp-range-val-max"><?php echo esc_html( tp_format_price( $cur_budget_max ) ); ?></span>
						</div>
					</div>
					<div class="tp-fd__foot">
						<button type="button" class="tp-fd__reset" data-clear="budget">Reset</button>
						<button type="button" class="tp-fd__apply">Apply</button>
					</div>
				</div>
			</div>

			<!-- Sort -->
			<div class="tp-fd tp-fd--sort" data-filter="sort">
				<button type="button" class="tp-fd__btn <?php echo $cur_sort ? 'is-active' : ''; ?>">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
					<span>
						<?php
						$sort_labels = array( '' => 'Sort', 'price-asc' => 'Price ↑', 'price-desc' => 'Price ↓', 'newest' => 'Newest' );
						echo esc_html( isset( $sort_labels[ $cur_sort ] ) ? $sort_labels[ $cur_sort ] : 'Sort' );
						?>
					</span>
					<?php echo $chev; ?>
				</button>
				<div class="tp-fd__panel tp-fd__panel--right">
					<div class="tp-fd__body">
						<?php
						$sort_options = array(
							''           => 'Relevance',
							'price-asc'  => 'Price: Low to High',
							'price-desc' => 'Price: High to Low',
							'newest'     => 'Newest First',
						);
						foreach ( $sort_options as $val => $label ) : ?>
							<label class="tp-fd__opt <?php echo $cur_sort === $val ? 'is-on' : ''; ?>">
								<input type="radio" name="_sort" value="<?php echo esc_attr( $val ); ?>" <?php checked( $cur_sort, $val ); ?>>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="tp-fd__foot">
						<button type="button" class="tp-fd__apply">Apply</button>
					</div>
				</div>
			</div>

			<!-- RERA Toggle -->
			<label class="tp-fb__rera <?php echo $cur_rera ? 'is-active' : ''; ?>">
				<input type="checkbox" name="_rera" value="1" <?php checked( $cur_rera ); ?>>
				<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
				<span>RERA</span>
			</label>

			<!-- Remove Filters -->
			<?php if ( $has_filters ) : ?>
				<button type="button" class="tp-fb__clear" id="tp-clear-all">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
					<span>Remove Filters</span>
				</button>
			<?php endif; ?>

		</div>
	</div>
</div>
