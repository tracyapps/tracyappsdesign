<?php
/**
 * Ticker block.
 *
 * @package TAD
 */

$items     = array_filter( array_map( static function ( $row ) { return trim( (string) ( $row['text'] ?? '' ) ); }, (array) tad_field( 'items', array() ) ) );
$sep       = tad_field( 'separator', '✦' );
$speed     = max( 10, min( 400, (int) tad_field( 'speed', 40 ) ) );
$direction = 'right' === tad_field( 'direction', 'left' ) ? 'right' : 'left';
$pause     = tad_toggle( 'pause_on_hover', true );
$decor     = tad_toggle( 'decorative', true );

if ( ! $items ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="eyebrow" style="padding:1rem">' . esc_html__( 'Ticker: add keywords in the block settings.', 'tad' ) . '</p>';
	}
	return;
}

$style = sprintf( '--marquee-duration:%ds;--ticker-sep:"%s";', $speed, esc_attr( addcslashes( $sep, '"\\' ) ) );
$attrs = $decor ? ' aria-hidden="true"' : ' role="region" aria-label="' . esc_attr__( 'Services', 'tad' ) . '"';
?>
<div <?php echo tad_block_attrs( $block, 'ticker' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="marquee" data-direction="<?php echo esc_attr( $direction ); ?>" data-pause-hover="<?php echo $pause ? 'true' : 'false'; ?>" style="<?php echo esc_attr( $style ); ?>">
		<ul class="marquee__track">
			<?php foreach ( $items as $item ) : ?>
				<li class="ticker__item"><?php echo esc_html( $item ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
