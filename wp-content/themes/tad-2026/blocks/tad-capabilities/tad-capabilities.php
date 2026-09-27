<?php
/**
 * Capabilities block.
 *
 * @package TAD
 */

$items = (array) tad_field( 'items', array() );
$cols  = in_array( (string) tad_field( 'columns', '3' ), array( '2', '3', '4' ), true ) ? (string) tad_field( 'columns', '3' ) : '3';
?>
<section <?php echo tad_section_attrs( $block, 'tad-capabilities' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wrap">
		<?php tad_section_head( $block ); ?>

		<?php if ( $items ) : ?>
			<ul class="caps caps--cols-<?php echo esc_attr( $cols ); ?>" role="list">
				<?php foreach ( $items as $item ) : ?>
					<?php if ( empty( $item['title'] ) ) { continue; } ?>
					<li class="cap glass reveal">
						<span class="cap__icon"><?php echo tad_icon_svg( $item['icon'] ?? 'brand' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<h3><?php echo esc_html( $item['title'] ); ?></h3>
						<?php if ( ! empty( $item['text'] ) ) : ?>
							<p><?php echo esc_html( $item['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
