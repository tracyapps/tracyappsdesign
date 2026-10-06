<?php
/**
 * Accent section: full-width glass panel with animated wavy edges.
 * Everything placed inside it (Work Grid, Contact, ...) sits on top of the glass.
 *
 * @package TAD
 */

$top      = tad_toggle( 'top_transition', true );
$bottom   = tad_toggle( 'bottom_transition', true );
$override = tad_toggle( 'override_settings', false ) ? tad_field( 'accent_override', array() ) : null;
$s        = tad_accent_settings( $override );

$classes  = 'tad-accent';
$classes .= $top ? ' has-top' : '';
$classes .= $bottom ? ' has-bottom' : '';
$classes .= $s['animate'] ? '' : ' is-still';
$classes .= ! empty( $is_preview ) ? ' is-preview' : '';

$extra = array(
	'style'         => tad_accent_root_style( $s ),
	'data-acc'      => '',
	'data-acc-mode' => $s['parallax_mode'],
);

$template = array( array( 'acf/tad-work-grid' ) );
?>
<div <?php echo tad_block_attrs( $block, $classes, $extra, ! empty( $is_preview ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="tad-accent__body" aria-hidden="true"></div>

	<?php if ( $top ) { echo tad_accent_edge( 'top', $s ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<div class="tad-accent__content">
		<?php if ( ! empty( $is_preview ) ) : ?>
			<InnerBlocks
				template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
			/>
		<?php else : ?>
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</div>

	<?php if ( $bottom ) { echo tad_accent_edge( 'bottom', $s ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
