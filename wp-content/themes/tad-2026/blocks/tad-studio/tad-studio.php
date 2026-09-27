<?php
/**
 * Studio block: about panel + numbered values.
 *
 * @package TAD
 */

$eyebrow  = tad_field( 'eyebrow' );
$heading  = tad_field( 'heading' );
$body     = tad_field( 'body' );
$values   = array_values( array_filter( (array) tad_field( 'values', array() ), static function ( $row ) { return ! empty( $row['text'] ); } ) );
$numbered = tad_toggle( 'numbered', true );
$title_id = tad_block_dom_id( $block );
$extra    = $heading ? array( 'aria-labelledby' => $title_id ) : array();
?>
<section <?php echo tad_section_attrs( $block, 'tad-studio', $extra ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wrap studio">
		<div class="glass studio__panel reveal">
			<?php if ( $eyebrow ) : ?>
				<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 id="<?php echo esc_attr( $title_id ); ?>"><?php echo tad_kses_inline( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<?php endif; ?>
			<?php if ( $body ) : ?>
				<div class="studio__body"><?php echo wp_kses_post( $body ); ?></div>
			<?php endif; ?>
		</div>

		<?php if ( $values ) : ?>
			<ul class="studio__facts reveal" role="list">
				<?php foreach ( $values as $i => $value ) : ?>
					<li class="fact glass">
						<?php if ( $numbered ) : ?>
							<strong aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></strong>
						<?php endif; ?>
						<span><?php echo esc_html( $value['text'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
