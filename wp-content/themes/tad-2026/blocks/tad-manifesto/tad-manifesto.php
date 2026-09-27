<?php
/**
 * Manifesto block: one big pull quote.
 *
 * @package TAD
 */

$quote = tad_field( 'quote' );
$cite  = tad_field( 'cite' );

if ( ! $quote ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="eyebrow" style="padding:1rem">' . esc_html__( 'Manifesto: add a quote in the block settings.', 'tad' ) . '</p>';
	}
	return;
}
?>
<section <?php echo tad_section_attrs( $block, 'manifesto' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wrap">
		<figure class="glass manifesto__inner reveal" style="margin-inline:0">
			<blockquote><p><?php echo esc_html( $quote ); ?></p></blockquote>
			<?php if ( $cite ) : ?>
				<figcaption><cite><?php echo esc_html( $cite ); ?></cite></figcaption>
			<?php endif; ?>
		</figure>
	</div>
</section>
