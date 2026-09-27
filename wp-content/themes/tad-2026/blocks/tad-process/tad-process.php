<?php
/**
 * Process block: numbered steps.
 *
 * @package TAD
 */

$steps = array_values( array_filter( (array) tad_field( 'steps', array() ), static function ( $row ) { return ! empty( $row['title'] ); } ) );
?>
<section <?php echo tad_section_attrs( $block, 'tad-process' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wrap">
		<?php tad_section_head( $block ); ?>

		<?php if ( $steps ) : ?>
			<ol class="steps" role="list">
				<?php foreach ( $steps as $i => $step ) : ?>
					<li class="step glass reveal">
						<span class="step__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<h3><?php echo esc_html( $step['title'] ); ?></h3>
						<?php if ( ! empty( $step['text'] ) ) : ?>
							<p><?php echo esc_html( $step['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
</section>
