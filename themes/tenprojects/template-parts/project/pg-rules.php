<?php
/**
 * PG House Rules Section — Smoking, Drinking, Guests, Curfew
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

$post_id     = $args['post_id'] ?? get_the_ID();
$pg_smoking  = tp_get_meta( $post_id, 'pg_smoking' );
$pg_drinking = tp_get_meta( $post_id, 'pg_drinking' );
$pg_guests   = tp_get_meta( $post_id, 'pg_guests' );
$pg_curfew   = tp_get_meta( $post_id, 'pg_curfew' );

$rules = array(
	array( 'label' => 'Smoking',  'value' => $pg_smoking ),
	array( 'label' => 'Drinking', 'value' => $pg_drinking ),
	array( 'label' => 'Guests',   'value' => $pg_guests ),
	array( 'label' => 'Curfew',   'value' => $pg_curfew ),
);

$active_rules = array_filter( $rules, function( $r ) { return ! empty( $r['value'] ); } );
if ( empty( $active_rules ) ) {
	return;
}
?>

<section class="tp-section" id="rules">
	<h2>House Rules</h2>

	<div class="tp-pg-rules">
		<?php foreach ( $active_rules as $rule ) :
			$is_no = strtolower( $rule['value'] ) === 'no';
		?>
			<div class="tp-pg-rule">
				<span class="tp-pg-rule__icon <?php echo $is_no ? 'tp-pg-rule__icon--no' : 'tp-pg-rule__icon--yes'; ?>">
					<?php if ( $is_no ) : ?>
						<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
					<?php else : ?>
						<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
					<?php endif; ?>
				</span>
				<div class="tp-pg-rule__content">
					<span class="tp-pg-rule__label"><?php echo esc_html( $rule['label'] ); ?></span>
					<span class="tp-pg-rule__value"><?php echo esc_html( $rule['value'] ); ?></span>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
